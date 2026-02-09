<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['committee_id'] ?? 0;
$memberId = $_GET['member_id'] ?? 0;

$committee = getCommitteeById($committeeId);
if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Find the member to edit from database
$members = getCommitteeMembers($committeeId);
$member = null;
foreach ($members as $m) {
    if ($m['member_id'] == $memberId) {
        $member = $m;
        break;
    }
}

if (!$member) {
    $_SESSION['error_message'] = 'Member not found';
    header('Location: members.php?id=' . $committeeId);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'position' => $_POST['position'] ?? 'Member',
        'join_date' => $_POST['join_date'] ?? $member['join_date']
    ];

    $success = updateCommitteeMember($memberId, $data);

    if ($success) {
        $_SESSION['success_message'] = 'Member updated successfully';
        header('Location: members.php?id=' . $committeeId);
        exit();
    } else {
        $_SESSION['error_message'] = 'Failed to update member';
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Member';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $committeeId; ?>" class="text-red-600">
                    <?php echo htmlspecialchars($committee['name']); ?>
                </a></li>
            <li class="breadcrumb-item"><a href="members.php?id=<?php echo $committeeId; ?>"
                    class="text-red-600">Members</a></li>
            <li class="breadcrumb-item active">Edit Member</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <p class="text-red-700"><?php echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Member</h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <a href="members.php?id=<?php echo $committeeId; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back to Members
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Member Name (Read-only) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Member Name
                    </label>
                    <input type="text" value="<?php echo htmlspecialchars($member['name']); ?>" disabled
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed">
                    <p class="text-sm text-gray-500 mt-1">Member name cannot be changed. Remove and re-add to change
                        user.</p>
                </div>

                <!-- Position in Committee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Position in Committee <span class="text-red-600">*</span>
                    </label>
                    <select name="position" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="Member" <?php echo $member['position'] === 'Member' ? 'selected' : ''; ?>>Member
                        </option>
                        <option value="Chairperson" <?php echo $member['position'] === 'Chairperson' ? 'selected' : ''; ?>>Chairperson</option>
                        <option value="Vice-Chairperson" <?php echo $member['position'] === 'Vice-Chairperson' ? 'selected' : ''; ?>>Vice-Chairperson</option>
                        <option value="Secretary" <?php echo $member['position'] === 'Secretary' ? 'selected' : ''; ?>>
                            Secretary</option>
                        <option value="Ex-Officio" <?php echo $member['position'] === 'Ex-Officio' ? 'selected' : ''; ?>>
                            Ex-Officio</option>
                    </select>
                </div>

                <!-- Join Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Join Date
                    </label>
                    <input type="date" name="join_date"
                        value="<?php echo htmlspecialchars($member['join_date'] ?? date('Y-m-d')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- User Info (Read-only) -->
                <div class="md:col-span-2 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="font-semibold mb-2">User Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Department:</span>
                            <span class="ml-2"><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Job Position:</span>
                            <span class="ml-2"><?php echo htmlspecialchars($member['user_position'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="members.php?id=<?php echo $committeeId; ?>"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-check-circle mr-2"></i>Update Member
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