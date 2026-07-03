<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Professional Lockdown: Only Admins can edit core committee governance (Legal Basis/Name)
$userRole = $_SESSION['user_role'] ?? 'User';
if ($userRole !== 'Admin' && $userRole !== 'Super Admin') {
    $_SESSION['error_message'] = 'Unauthorized Access: Modifying core committee governance is reserved for Administrators.';
    header('Location: view.php?id=' . $id);
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

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
        'is_active' => ($_POST['status'] ?? 'Active') === 'Active' ? 1 : 0
    ];

    $errors = [];
    if (empty($data['name']))
        $errors[] = 'Committee name is required';
    if (empty($data['type']))
        $errors[] = 'Committee type is required';
    if (empty($data['chairperson_id']))
        $errors[] = 'Chairperson is required';
    if (empty($data['secretary_id']))
        $errors[] = 'Secretary is required';
    if (empty($data['creation_authority']))
        $errors[] = 'Legal Basis (Resolution/Ordinance #) is required';

    if (empty($errors)) {
        updateCommittee($id, $data);
        $_SESSION['success_message'] = 'Committee updated successfully!';
        header('Location: view.php?id=' . $id);
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
$pageTitle = 'Edit Committee';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>"
                    class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Committee</h1>
        <a href="view.php?id=<?php echo $id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-x-circle mr-2"></i>Cancel
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <ul class="list-disc list-inside text-red-700">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Committee Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($committee['name']); ?>"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Type *</label>
                    <select name="type" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                        <option value="Standing" <?php echo $committee['type'] === 'Standing' ? 'selected' : ''; ?>>
                            Standing</option>
                        <option value="Special" <?php echo $committee['type'] === 'Special' ? 'selected' : ''; ?>>Special
                        </option>
                        <option value="Ad Hoc" <?php echo $committee['type'] === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc
                        </option>
                    </select>
                </div>

                <!-- Legal Basis -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Legal Basis (Resolution / Ordinance #) *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="creation_authority" id="creation_authority" required
                                value="<?php echo htmlspecialchars($committee['creation_authority'] ?? ''); ?>"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <select id="resolution_selector" onchange="autoFillLegalBasis(this)"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Link to Existing Resolution (Optional)</option>
                                <?php foreach ($approvedResolutions as $reso): ?>
                                    <option value="<?php echo htmlspecialchars($reso['document_number']); ?>"
                                        <?php echo ($committee['creation_authority'] === $reso['document_number']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($reso['document_number'] . ': ' . $reso['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <script>
                    function autoFillLegalBasis(select) {
                        if (select.value) {
                            document.getElementById('creation_authority').value = select.value;
                        }
                    }
                </script>

                <div>
                    <label class="block text-sm font-medium mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                        <option value="Active" <?php echo $committee['status'] === 'Active' ? 'selected' : ''; ?>>Active
                        </option>
                        <option value="Inactive" <?php echo $committee['status'] === 'Inactive' ? 'selected' : ''; ?>>
                            Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Chairperson *</label>
                    <select name="chairperson_id" required <?php echo $isChairman ? 'disabled' : ''; ?>
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white <?php echo $isChairman ? 'bg-gray-100 cursor-not-allowed opacity-75' : ''; ?>">
                        <option value="">Select Chairperson</option>
                        <?php foreach ($eligibleChairs as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo $committee['chairperson_id'] == $user['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isChairman): ?>
                        <input type="hidden" name="chairperson_id" value="<?php echo $committee['chairperson_id']; ?>">
                        <p class="text-[10px] text-gray-500 mt-1"><i class="bi bi-info-circle mr-1"></i>Chairperson
                            selection is locked.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Vice-Chairperson</label>
                    <select name="vice_chair_id"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">Select Vice-Chairperson</option>
                        <?php foreach ($eligibleViceChairs as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo $committee['vice_chair_id'] == $user['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Secretary *</label>
                    <select name="secretary_id" required
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">Select Secretary</option>
                        <?php foreach ($eligibleSecretaries as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo $committee['secretary_id'] == $user['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                (<?php echo htmlspecialchars($user['position'] ?? 'No Position'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Jurisdiction *</label>
                    <textarea name="jurisdiction" required rows="2"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"><?php echo htmlspecialchars($committee['jurisdiction']); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"><?php echo htmlspecialchars($committee['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                <a href="view.php?id=<?php echo $id; ?>" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                    <i class="bi bi-check-circle mr-2"></i>Update Committee
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