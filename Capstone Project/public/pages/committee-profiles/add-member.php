<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['committee_id'] ?? 0;
$committee = getCommitteeById($committeeId);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Get available users for dropdown (exclude existing members)
$allUsers = getAvailableUsersForCommittee($committeeId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $position = $_POST['position'] ?? 'Member';
    $joinedDate = $_POST['joined_date'] ?? date('Y-m-d');

    if ($userId <= 0) {
        $_SESSION['error_message'] = 'Please select a user';
    } else {
        // Add member to committee
        $success = addCommitteeMember($committeeId, $userId, $position, $joinedDate);

        if ($success) {
            $_SESSION['success_message'] = 'Member added successfully';
            header('Location: members.php?id=' . $committeeId);
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to add member. User may already be a member.';
        }
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Add Member';
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
            <li class="breadcrumb-item active">Add Member</li>
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Member</h1>
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
                <!-- Select User -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select User <span class="text-red-600">*</span>
                    </label>
                    <select name="user_id" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select a user to add as member</option>
                        <?php foreach ($allUsers as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>">
                                <?php echo htmlspecialchars($user['full_name']); ?> -
                                <?php echo htmlspecialchars($user['position'] ?? $user['department'] ?? 'No Position'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Select an existing user from the system to add to this
                        committee</p>
                </div>

                <!-- Position in Committee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Position in Committee <span class="text-red-600">*</span>
                    </label>
                    <select name="position" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="Member" selected>Member</option>
                        <option value="Chairperson">Chairperson</option>
                        <option value="Vice-Chairperson">Vice-Chairperson</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Ex-Officio">Ex-Officio</option>
                    </select>
                </div>

                <!-- Joined Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Joined Date
                    </label>
                    <input type="date" name="joined_date" value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="members.php?id=<?php echo $committeeId; ?>"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-plus-circle mr-2"></i>Add Member
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