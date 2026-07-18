<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/XlsxParserHelper.php';
require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
require_once __DIR__ . '/../../../app/helpers/NotificationHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'User';

// Only admins can import committees
if ($userRole !== 'Admin' && $userRole !== 'Super Admin') {
    $_SESSION['error_message'] = 'Unauthorized access to the import tool.';
    header('Location: index.php');
    exit();
}

$importResults = [];
$errors = [];

// Helper function to find or create a user by full name
function findOrCreateTemporaryUser($fullName, $position = 'Member')
{
    global $conn;

    $fullName = trim($fullName);
    if (empty($fullName)) {
        return null;
    }

    // Split name into First Name and Last Name
    $parts = explode(' ', $fullName);
    $lastName = array_pop($parts);
    $firstName = implode(' ', $parts);
    if (empty($firstName)) {
        $firstName = $lastName;
        $lastName = 'User';
    }

    // Search for existing active user with this name
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE first_name = ? AND last_name = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("ss", $firstName, $lastName);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        return $row['user_id'];
    }
    $stmt->close();

    // Generate unique temp email
    $cleanFirst = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName));
    $cleanLast = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastName));
    $baseEmail = "{$cleanFirst}.{$cleanLast}@lgu.temp";
    $email = $baseEmail;

    // Ensure email is unique
    $counter = 1;
    while (true) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $hasEmail = $check->get_result()->num_rows > 0;
        $check->close();
        if (!$hasEmail) {
            break;
        }
        $email = "{$cleanFirst}.{$cleanLast}{$counter}@lgu.temp";
        $counter++;
    }

    // Insert new active user
    $tempPass = password_hash('WelcomeLGU2026!', PASSWORD_DEFAULT);
    $roleId = 3; // Standard Member Role ID

    $stmt_insert = $conn->prepare(
        "INSERT INTO users (email, password_hash, first_name, last_name, role_id, position, is_active, email_verified)
         VALUES (?, ?, ?, ?, ?, ?, 1, 1)"
    );
    $stmt_insert->bind_param("ssssis", $email, $tempPass, $firstName, $lastName, $roleId, $position);
    $stmt_insert->execute();
    $newUserId = $stmt_insert->insert_id;
    $stmt_insert->close();

    // Log the user registration audit log
    if (function_exists('logAuditAction')) {
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'CREATE',
            'users',
            "Import created temporary profile for '{$fullName}' ({$email})"
        );
    }

    return $newUserId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xlsx_file'])) {
    $file = $_FILES['xlsx_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed. Please try again (Error code: {$file['error']}).";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $errors[] = "Only Excel spreadsheet (.xlsx) files are supported. You uploaded a <strong>.{$ext}</strong> file.";
        } else {
            try {
                $reader = new SimpleXlsxReader($file['tmp_name']);
                $rows   = $reader->getRows();

                if (empty($rows)) {
                    $errors[] = "The spreadsheet appears to be empty. Please make sure your file has data starting from Row 2.";
                } else {
                    // Expect header on Row 1, data starts on Row 2
                    // Column 0 (A): Committee Name
                    // Column 1 (B): Committee Type
                    // Column 2 (C): Chairperson Name
                    // Column 3 (D): Vice-Chairperson Name
                    // Column 4 (E): Secretary Name
                    // Column 5 (F): Members List (Comma-separated)
                    // Column 6 (G): Jurisdiction & Description

                    $importedCount = 0;
                    $skippedCount  = 0;

                    // Helper: safely read a cell value, fall back to empty string
                    $cell = fn($row, $col) => trim($row[$col] ?? '');

                    $conn->begin_transaction();

                    foreach ($rows as $rowIndex => $row) {
                        // Skip header row
                        if ($rowIndex === 1) continue;

                        $name         = $cell($row, 0);
                        $type         = $cell($row, 1) ?: 'Standing';
                        $chairName    = $cell($row, 2);
                        $viceChairName= $cell($row, 3);
                        $secName      = $cell($row, 4);
                        $membersString= $cell($row, 5);
                        $jurisdiction = $cell($row, 6) ?: 'No jurisdiction info provided.';

                        // Skip rows with no committee name
                        if (empty($name)) {
                            $skippedCount++;
                            continue;
                        }

                        // Check for duplicate
                        $stmt_check = $conn->prepare("SELECT committee_id FROM committees WHERE committee_name = ? LIMIT 1");
                        $stmt_check->bind_param("s", $name);
                        $stmt_check->execute();
                        $already_exists = $stmt_check->get_result()->num_rows > 0;
                        $stmt_check->close();

                        if ($already_exists) {
                            $errors[] = "Row {$rowIndex}: Committee \"<strong>{$name}</strong>\" already exists — skipped.";
                            $skippedCount++;
                            continue;
                        }

                        // Resolve leadership user IDs (create temp accounts if needed)
                        $chairId    = !empty($chairName)     ? findOrCreateTemporaryUser($chairName,     'Chairperson')     : null;
                        $viceChairId= !empty($viceChairName) ? findOrCreateTemporaryUser($viceChairName, 'Vice-Chairperson'): null;
                        $secretaryId= !empty($secName)       ? findOrCreateTemporaryUser($secName,       'Secretary')       : null;

                        // Create the committee
                        $createData = [
                            'name'           => $name,
                            'type'           => $type,
                            'description'    => 'Imported via Excel spreadsheet on ' . date('Y-m-d H:i:s'),
                            'jurisdiction'   => $jurisdiction,
                            'chairperson_id' => $chairId,
                            'vice_chair_id'  => $viceChairId,
                            'secretary_id'   => $secretaryId,
                            'is_active'      => true
                        ];

                        $committeeId = createCommittee($createData);

                        if ($committeeId) {
                            $importedCount++;

                            // Link leadership to committee_members
                            if ($chairId)     addCommitteeMember($committeeId, $chairId,     'Chairperson');
                            if ($viceChairId) addCommitteeMember($committeeId, $viceChairId, 'Vice-Chairperson');
                            if ($secretaryId) addCommitteeMember($committeeId, $secretaryId, 'Secretary');

                            // Parse and link roster members
                            if (!empty($membersString)) {
                                foreach (explode(',', $membersString) as $mName) {
                                    $mName = trim($mName);
                                    if (!empty($mName)) {
                                        $mId = findOrCreateTemporaryUser($mName, 'Member');
                                        if ($mId) addCommitteeMember($committeeId, $mId, 'Member');
                                    }
                                }
                            }
                        } else {
                            $errors[] = "Row {$rowIndex}: Failed to save committee \"<strong>{$name}</strong>\" to the database.";
                        }
                    }

                    $conn->commit();

                    $importResults = [
                        'success' => true,
                        'imported' => $importedCount,
                        'skipped'  => $skippedCount,
                        'message'  => "Import complete: <strong>{$importedCount}</strong> committee(s) imported successfully."
                                    . ($skippedCount > 0 ? " <strong>{$skippedCount}</strong> row(s) were skipped (empty or duplicate)." : "")
                    ];
                }

            } catch (Exception $e) {
                if (isset($conn) && $conn->connect_errno === 0) {
                    $conn->rollback();
                }
                $errors[] = $e->getMessage();
            }
        }
    }
}


$pageTitle = 'Import Committees from Excel';
include '../../includes/header.php';
?>


<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committee Profiles</a></li>
            <li class="breadcrumb-item active">Import</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Import Committees</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Bulk upload committees and rosters via Excel spreadsheet (.xlsx)</p>
        </div>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($importResults)): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center space-x-2">
                <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-xl"></i>
                <p class="text-sm text-green-800 dark:text-green-300 font-medium"><?php echo $importResults['message']; ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Card -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Upload Spreadsheet</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-red-500 dark:hover:border-red-400 transition cursor-pointer" onclick="document.getElementById('xlsx_file').click()">
                    <i class="bi bi-file-earmark-excel text-5xl text-green-600 dark:text-green-400 mb-3 block"></i>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">Click here to upload your Excel file</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Supports spreadsheet (.xlsx) formats</p>
                    <input type="file" name="xlsx_file" id="xlsx_file" accept=".xlsx" class="hidden" onchange="updateFileName(this)">
                    <span id="file-name" class="mt-3 block text-xs font-bold text-red-600 dark:text-red-400"></span>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-semibold transition flex items-center space-x-2">
                        <i class="bi bi-cloud-upload"></i>
                        <span>Start Import</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Template Specs Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Spreadsheet Template Specs</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Your spreadsheet sheet columns must align exactly to the specification below (Row 1 serves as the column headers):</p>
            <div class="space-y-3">
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">A</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Committee Name</p>
                        <p class="text-[10px] text-gray-400">e.g., Committee on Health</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">B</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Committee Type</p>
                        <p class="text-[10px] text-gray-400">Standing, Special, or Ad Hoc</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">C</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Chairperson Name</p>
                        <p class="text-[10px] text-gray-400">e.g., Juan Dela Cruz</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">D</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Vice-Chairperson Name</p>
                        <p class="text-[10px] text-gray-400">e.g., Maria Santos</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">E</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Secretary Name</p>
                        <p class="text-[10px] text-gray-400">e.g., Ana Gomez</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">F</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Members List (Comma-separated)</p>
                        <p class="text-[10px] text-gray-400">e.g., Peter Parker, Bruce Wayne, Clark Kent</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold text-gray-800 dark:text-gray-200">G</span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Jurisdiction &amp; Description</p>
                        <p class="text-[10px] text-gray-400">Scope and description details</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 rounded-xl">
                <p class="text-[10px] text-red-800 dark:text-red-400 font-semibold"><i class="bi bi-info-circle mr-1"></i>Any names in Columns C, D, E, or F that do not exist will trigger creation of temporary user credentials with the password "WelcomeLGU2026!".</p>
            </div>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileNameSpan = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileNameSpan.innerText = `Selected File: ${input.files[0].name}`;
    } else {
        fileNameSpan.innerText = '';
    }
}
</script>

</div><!-- /#module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>
