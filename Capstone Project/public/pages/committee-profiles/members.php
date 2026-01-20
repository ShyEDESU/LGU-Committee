<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);
if (!$committee) {
    header('Location: index.php');
    exit();
}

$members = getCommitteeMembers($id);

// Handle member deletion
if (isset($_POST['delete_member'])) {
    $memberIdToDelete = intval($_POST['delete_member']);

    // Get the user_id from the member record
    foreach ($members as $m) {
        if ($m['member_id'] == $memberIdToDelete) {
            $success = removeCommitteeMember($id, $m['user_id']);
            if ($success) {
                $_SESSION['success_message'] = 'Member removed successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to remove member';
            }
            header('Location: members.php?id=' . $id);
            exit();
        }
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Members';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>"
                    class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Members</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($committee['name']); ?></h1>
            <p class="text-gray-600">Committee Members</p>
        </div>
        <div class="flex gap-2">
            <a href="add-member.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer inline-flex items-center">
                <i class="bi bi-plus-circle mr-2"></i>Add Member
            </a>
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agendas"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-journal-text mr-1"></i>Agendas
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=referrals"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-arrow-left-right mr-1"></i>Referrals
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
                <a href="reports.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-file-earmark-text mr-1"></i>Reports
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <?php if (empty($members)): ?>
            <div class="p-12 text-center">
                <i class="bi bi-people text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No members assigned yet</p>
                <a href="add-member.php?committee_id=<?php echo $id; ?>"
                    class="mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg cursor-pointer inline-block">
                    Add First Member
                </a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($members as $member): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                        <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-semibold"><?php echo htmlspecialchars($member['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    <?php echo htmlspecialchars($member['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($member['position'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="edit-member.php?committee_id=<?php echo $id; ?>&member_id=<?php echo $member['member_id']; ?>"
                                        class="text-blue-600 hover:text-blue-700" title="Edit Member">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" class="inline"
                                        onsubmit="return confirm('Remove this member from the committee?');">
                                        <input type="hidden" name="delete_member" value="<?php echo $member['member_id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-700" title="Delete Member">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>