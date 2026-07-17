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
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Members</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['name']); ?></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Committee Members Directory</p>
        </div>
        <div class="flex gap-2">
            <a href="add-member.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer inline-flex items-center text-sm font-medium transition shadow-sm">
                <i class="bi bi-plus-circle mr-2"></i>Add Member
            </a>
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition">
                <i class="bi bi-arrow-left mr-2"></i>Back to Overview
            </a>
        </div>
    </div>

    <!-- Navigation Tabs (Updated to match view.php) -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6 overflow-x-auto">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agenda"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-card-checklist mr-1"></i>Agenda
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
                <a href="documents.php?id=<?php echo $id; ?>&subtab=ordinances"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition inline-flex items-center gap-1">
                    <i class="bi bi-bank2"></i>Ordinances
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">API</span>
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=feedback"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-chat-right-text mr-1"></i>Feedback
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
        </div>
    </div>

    <!-- Members Table/List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($members)): ?>
            <div class="p-16 text-center">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-people text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">No Members Assigned</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">Assign members to begin managing this committee.</p>
                <a href="add-member.php?committee_id=<?php echo $id; ?>"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg inline-flex items-center gap-2 text-sm font-medium transition shadow-sm">
                    <i class="bi bi-plus-circle"></i> Add Member
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 uppercase tracking-wider font-semibold text-xs">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Position</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($members as $member): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full flex items-center justify-center font-bold mr-3">
                                            <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-950 dark:text-white block"><?php echo htmlspecialchars($member['name']); ?></span>
                                            <span class="text-xs text-gray-400">ID: #<?php echo $member['member_id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-red-100/80 text-red-800 dark:bg-red-900/30 dark:text-red-300 rounded-full text-xs font-semibold">
                                        <?php echo htmlspecialchars($member['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($member['position'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center gap-1.5">
                                        <a href="edit-member.php?committee_id=<?php echo $id; ?>&member_id=<?php echo $member['member_id']; ?>"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Edit Member">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="POST" class="inline"
                                            onsubmit="return confirm('Remove this member from the committee?');">
                                            <input type="hidden" name="delete_member" value="<?php echo $member['member_id']; ?>">
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Delete Member">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div> <!-- Closing container-fluid -->
</div> <!-- Closing module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>