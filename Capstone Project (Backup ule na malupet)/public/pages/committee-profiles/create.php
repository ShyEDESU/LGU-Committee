<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Professional Lockdown: Only Admins can create committees
$userRole = $_SESSION['user_role'] ?? 'User';
if ($userRole !== 'Admin' && $userRole !== 'Super Admin') {
    $_SESSION['error_message'] = 'Unauthorized Access: Committee establishment is a Governance action reserved for Administrators.';
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'type' => $_POST['type'] ?? '',
        'chairperson_id' => !empty($_POST['chairperson_id']) ? $_POST['chairperson_id'] : null,
        'vice_chair_id' => !empty($_POST['vice_chair_id']) ? $_POST['vice_chair_id'] : null,
        'secretary_id' => !empty($_POST['secretary_id']) ? $_POST['secretary_id'] : null,
        'jurisdiction' => trim($_POST['jurisdiction'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'creation_authority' => trim($_POST['creation_authority'] ?? ''),
        'status' => $_POST['status'] ?? 'Active'
    ];

    // Validation
    $errors = [];
    if (empty($data['name']))
        $errors[] = 'Committee name is required';
    if (empty($data['type']))
        $errors[] = 'Committee type is required';
    if (empty($data['chairperson_id']))
        $errors[] = 'Chairperson is required';
    if (empty($data['secretary_id']))
        $errors[] = 'Secretary is required';
    if (empty($data['jurisdiction']))
        $errors[] = 'Jurisdiction is required';
    if (empty($data['creation_authority']))
        $errors[] = 'Legal Basis (Resolution/Ordinance #) is required';

    if (empty($errors)) {
        // Convert array to the format expected by createCommittee (mapping chair to chairperson_id etc)
        $createData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'],
            'jurisdiction' => $data['jurisdiction'],
            'chairperson_id' => $data['chairperson_id'],
            'vice_chair_id' => $data['vice_chair_id'],
            'secretary_id' => $data['secretary_id'],
            'creation_authority' => $data['creation_authority'],
            'is_active' => $data['status'] === 'Active' ? 1 : 0
        ];

        $newId = createCommittee($createData);
        $_SESSION['success_message'] = 'Committee created successfully!';
        header('Location: view.php?id=' . $newId);
        exit();
    }
}

require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
$allUsers = UserHelper_getActiveUsers();

$currentUserId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'User';
$isChairman = (stripos($userRole, 'Chairman') !== false && stripos($userRole, 'Vice') === false);

// Filter users by specific roles
$eligibleChairs = array_filter($allUsers, function ($u) {
    return (int) $u['role_id'] === 3; // Committee Chairman
});

$eligibleViceChairs = array_filter($allUsers, function ($u) {
    return (int) $u['role_id'] === 4; // Vice Committee Chairman
});

$eligibleSecretaries = array_filter($allUsers, function ($u) {
    return (int) $u['role_id'] === 5 || (int) $u['role_id'] === 6; // User or Committee Secretary
});

// Fetch approved resolutions for the "Legal Basis" link
$approvedResolutions = getApprovedResolutions();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Committee';
include '../../includes/header.php';
?>

<!-- Page Content -->
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php"
                    class="text-red-600 hover:text-red-700">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600 hover:text-red-700">Committee
                    Profiles</a></li>
            <li class="breadcrumb-item active">Create Committee</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Committee</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new committee to the system</p>
        </div>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition"
            title="Back to List">
            <i class="bi bi-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <i class="bi bi-exclamation-triangle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-red-800 dark:text-red-300">Please correct the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 dark:text-red-400 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <form method="POST" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Committee Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Committee on Finance">
                </div>

                <!-- Committee Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Standing" <?php echo ($_POST['type'] ?? '') === 'Standing' ? 'selected' : ''; ?>>
                            Standing Committee</option>
                        <option value="Special" <?php echo ($_POST['type'] ?? '') === 'Special' ? 'selected' : ''; ?>>
                            Special Committee</option>
                        <option value="Ad Hoc" <?php echo ($_POST['type'] ?? '') === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc
                            Committee</option>
                    </select>
                </div>

                <!-- Legal Basis (Creation Authority) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Legal Basis (Resolution / Ordinance #) <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="creation_authority" id="creation_authority" required
                                value="<?php echo htmlspecialchars($_POST['creation_authority'] ?? ''); ?>"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., SP Resolution No. 2026-101">
                        </div>
                        <div>
                            <select id="resolution_selector" onchange="autoFillLegalBasis(this)"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Link to Existing Resolution (Optional)</option>
                                <?php foreach ($approvedResolutions as $reso): ?>
                                    <option value="<?php echo htmlspecialchars($reso['document_number']); ?>">
                                        <?php echo htmlspecialchars($reso['document_number'] . ': ' . $reso['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Citing the official document that established this
                        committee is mandatory. You can type it manually or select an approved resolution from the
                        system.</p>
                </div>

                <script>
                    function autoFillLegalBasis(select) {
                        if (select.value) {
                            document.getElementById('creation_authority').value = select.value;
                        }
                    }
                </script>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="Active" <?php echo ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>
                            Inactive</option>
                    </select>
                </div>

                <!-- Chairperson -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Chairperson <span class="text-red-500">*</span>
                    </label>
                    <select name="chairperson_id" required <?php echo $isChairman ? 'disabled' : ''; ?>
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white <?php echo $isChairman ? 'bg-gray-100 cursor-not-allowed opacity-75' : ''; ?>">
                        <option value="">Select Chairperson</option>
                        <?php foreach ($eligibleChairs as $user): ?>
                            <?php
                            $selected = '';
                            if (isset($_POST['chairperson_id'])) {
                                $selected = ($_POST['chairperson_id'] == $user['user_id']) ? 'selected' : '';
                            } elseif ($isChairman && $user['user_id'] == $currentUserId) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isChairman): ?>
                        <input type="hidden" name="chairperson_id" value="<?php echo $currentUserId; ?>">
                        <p class="text-[10px] text-gray-500 mt-1"><i class="bi bi-info-circle mr-1"></i>You are
                            auto-selected as the chairperson.</p>
                    <?php endif; ?>
                </div>

                <!-- Vice-Chairperson -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Vice-Chairperson
                    </label>
                    <select name="vice_chair_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Vice-Chairperson</option>
                        <?php foreach ($eligibleViceChairs as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo (isset($_POST['vice_chair_id']) && $_POST['vice_chair_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Secretary -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Secretary <span class="text-red-500">*</span>
                    </label>
                    <select name="secretary_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Secretary</option>
                        <?php foreach ($eligibleSecretaries as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo (isset($_POST['secretary_id']) && $_POST['secretary_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jurisdiction -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jurisdiction <span class="text-red-500">*</span>
                    </label>
                    <textarea name="jurisdiction" required rows="2"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Brief description of committee's jurisdiction"><?php echo htmlspecialchars($_POST['jurisdiction'] ?? ''); ?></textarea>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Detailed description of the committee's role and responsibilities"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="index.php"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    title="Cancel">
                    Cancel
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition"
                    title="Create Committee">
                    <i class="bi bi-check-circle mr-2"></i>Create Committee
                </button>
            </div>
        </form>
    </div>
</div>

</div> <!-- Closing container-fluid and module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>