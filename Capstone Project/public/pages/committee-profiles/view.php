<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeWithStats($id);  // Get committee with dynamic statistics

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Handle delete
if (isset($_POST['delete'])) {
    deleteCommittee($id);
    $_SESSION['success_message'] = 'Committee deleted successfully';
    header('Location: index.php');
    exit();
}

$members = getCommitteeMembers($id);
$documents = getCommitteeDocuments($id);

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'];
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($committee['name']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700"><?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($committee['name']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($committee['type']); ?> Committee
            </p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to List
            </a>
            <a href="edit.php?id=<?php echo $id; ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-pencil mr-2"></i>Edit
            </a>
            <button onclick="window.print()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-printer mr-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Members
                </a>
                <a href="meetings.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Meetings
                </a>
                <a href="referrals.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Referrals
                </a>
                <a href="action-items.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Action Items
                </a>
                <a href="reports.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Reports
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    Documents
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    History
                </a>
            </nav>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Committee Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Chairperson</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($committee['chair']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Vice-Chairperson</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($committee['vice_chair'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            <?php echo htmlspecialchars($committee['type']); ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <span
                            class="inline-block px-3 py-1 <?php echo $committee['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> rounded-full text-sm">
                            <?php echo htmlspecialchars($committee['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Jurisdiction</p>
                    <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['jurisdiction']); ?>
                    </p>
                </div>

                <?php if (!empty($committee['description'])): ?>
                    <div class="mt-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                        <p class="text-gray-900 dark:text-white">
                            <?php echo nl2br(htmlspecialchars($committee['description'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Members Preview -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Committee Members</h2>
                    <a href="members.php?id=<?php echo $id; ?>" class="text-red-600 hover:text-red-700">View All →</a>
                </div>
                <?php if (empty($members)): ?>
                    <p class="text-gray-500">No members assigned yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($members, 0, 5) as $member): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($member['name']); ?></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo htmlspecialchars($member['role']); ?>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($member['district']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Committee Meetings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Committee Meetings</h2>
                    <a href="../committee-meetings/schedule.php?committee=<?php echo $id; ?>"
                        class="text-red-600 hover:text-red-700">
                        <i class="bi bi-plus-circle mr-1"></i>Schedule New
                    </a>
                </div>
                <?php
                require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
                $allMeetings = getAllMeetings();
                $committeeMeetings = array_filter($allMeetings, function ($m) use ($id) {
                    return $m['committee_id'] == $id;
                });
                ?>
                <?php if (empty($committeeMeetings)): ?>
                    <p class="text-gray-500">No meetings scheduled yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($committeeMeetings, 0, 5) as $meeting): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold"><?php echo htmlspecialchars($meeting['title']); ?></p>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo $meeting['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $meeting['status']; ?>
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <i class="bi bi-calendar mr-2"></i>
                                    <?php echo date('M j, Y', strtotime($meeting['date'])); ?> at
                                    <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <i class="bi bi-geo-alt mr-2"></i>
                                    <?php echo htmlspecialchars($meeting['venue']); ?>
                                </div>
                                <a href="../committee-meetings/view.php?id=<?php echo $meeting['id']; ?>"
                                    class="text-red-600 hover:text-red-700 text-sm mt-2 inline-block">
                                    View Details →
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($committeeMeetings) > 5): ?>
                            <a href="../committee-meetings/index.php"
                                class="block text-center text-red-600 hover:text-red-700 text-sm mt-2">
                                View all <?php echo count($committeeMeetings); ?> meetings →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Committee Referrals -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Assigned Referrals</h2>
                    <a href="../referral-management/create.php?committee=<?php echo $id; ?>"
                        class="text-red-600 hover:text-red-700">
                        <i class="bi bi-plus-circle mr-1"></i>Create New
                    </a>
                </div>
                <?php
                $committeeReferrals = getReferralsByCommittee($id);
                ?>
                <?php if (empty($committeeReferrals)): ?>
                    <p class="text-gray-500">No referrals assigned yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($committeeReferrals, 0, 5) as $referral): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold"><?php echo htmlspecialchars($referral['title']); ?></p>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo $referral['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                                            ($referral['status'] === 'Under Review' ? 'bg-blue-100 text-blue-800' :
                                                ($referral['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800')); ?>">
                                        <?php echo $referral['status']; ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span
                                        class="px-2 py-1 rounded-full <?php echo $referral['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                            ($referral['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                        <?php echo $referral['priority']; ?> Priority
                                    </span>
                                    <?php if (!empty($referral['deadline'])): ?>
                                        <span>
                                            <i class="bi bi-calendar-x mr-1"></i>
                                            Due: <?php echo date('M j, Y', strtotime($referral['deadline'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="../referral-management/view.php?id=<?php echo $referral['id']; ?>"
                                    class="text-red-600 hover:text-red-700 text-sm mt-2 inline-block">
                                    View Details →
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($committeeReferrals) > 5): ?>
                        <a href="../referral-management/index.php?committee=<?php echo $id; ?>"
                            class="text-red-600 hover:text-red-700 text-sm mt-3 inline-block">
                            View All Referrals (<?php echo count($committeeReferrals); ?>) →
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Committee Action Items -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Action Items</h2>
                    <a href="../action-items/create.php?committee_id=<?php echo $id; ?>"
                        class="text-red-600 hover:text-red-700">
                        <i class="bi bi-plus-circle mr-1"></i>Create New
                    </a>
                </div>
                <?php
                require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
                $committeeActionItems = getActionItemsByCommittee($id);
                ?>
                <?php if (empty($committeeActionItems)): ?>
                    <p class="text-gray-500">No action items assigned yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($committeeActionItems, 0, 5) as $actionItem): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold"><?php echo htmlspecialchars($actionItem['title']); ?></p>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo ($actionItem['status'] ?? '') === 'Done' ? 'bg-green-100 text-green-800' :
                                            (($actionItem['status'] ?? '') === 'In Progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'); ?>">
                                        <?php echo htmlspecialchars($actionItem['status'] ?? 'To Do'); ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <span>
                                        <i class="bi bi-person mr-1"></i>
                                        <?php echo htmlspecialchars($actionItem['assigned_to']); ?>
                                    </span>
                                    <span
                                        class="px-2 py-1 rounded-full <?php echo ($actionItem['priority'] ?? '') === 'High' ? 'bg-red-100 text-red-800' :
                                            (($actionItem['priority'] ?? '') === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                        <?php echo htmlspecialchars($actionItem['priority'] ?? 'Medium'); ?> Priority
                                    </span>
                                    <?php if (!empty($actionItem['due_date'])): ?>
                                        <span>
                                            <i class="bi bi-calendar-x mr-1"></i>
                                            Due: <?php echo date('M j, Y', strtotime($actionItem['due_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (($actionItem['progress'] ?? 0) > 0): ?>
                                    <div class="mb-2">
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: <?php echo ($actionItem['progress'] ?? 0); ?>%"></div>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            <?php echo ($actionItem['progress'] ?? 0); ?>% complete
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <a href="../action-items/view.php?id=<?php echo $actionItem['id']; ?>"
                                    class="text-red-600 hover:text-red-700 text-sm mt-2 inline-block">
                                    View Details →
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($committeeActionItems) > 5): ?>
                            <a href="../action-items/index.php?committee_id=<?php echo $id; ?>"
                                class="block text-center text-red-600 hover:text-red-700 text-sm mt-2">
                                View all <?php echo count($committeeActionItems); ?> action items →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Committee Agendas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Committee Agendas</h2>
                    <a href="../agenda-builder/index.php" class="text-red-600 hover:text-red-700">
                        View All →
                    </a>
                </div>
                <?php
                $committeeAgendas = getAgendasByCommittee($id);
                ?>
                <?php if (empty($committeeAgendas)): ?>
                    <p class="text-gray-500">No agendas created yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($committeeAgendas, 0, 5) as $agenda): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold"><?php echo htmlspecialchars($agenda['meeting']['title']); ?></p>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?php
                                        $status = $agenda['meeting']['agenda_status'] ?? 'Draft';
                                        echo $status === 'Draft' ? 'bg-yellow-100 text-yellow-800' :
                                            ($status === 'Approved' ? 'bg-green-100 text-green-800' :
                                                ($status === 'Published' ? 'bg-purple-100 text-purple-800' :
                                                    'bg-gray-100 text-gray-800'));
                                        ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <i class="bi bi-calendar mr-2"></i>
                                    <?php echo date('M j, Y', strtotime($agenda['meeting']['date'])); ?>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <i class="bi bi-list-check mr-2"></i>
                                    <?php echo $agenda['item_count']; ?> items
                                </div>
                                <a href="../agenda-builder/view.php?id=<?php echo $agenda['meeting']['id']; ?>"
                                    class="text-red-600 hover:text-red-700 text-sm mt-2 inline-block">
                                    View Agenda →
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($committeeAgendas) > 5): ?>
                            <a href="../agenda-builder/index.php"
                                class="block text-center text-red-600 hover:text-red-700 text-sm mt-2">
                                View all <?php echo count($committeeAgendas); ?> agendas →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Statistics</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Members</span>
                        <span class="text-2xl font-bold text-blue-600"><?php echo $committee['members_count']; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Meetings Held</span>
                        <span
                            class="text-2xl font-bold text-green-600"><?php echo $committee['meetings_held']; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Pending Referrals</span>
                        <span
                            class="text-2xl font-bold text-orange-600"><?php echo $committee['pending_referrals']; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Agendas</span>
                        <span class="text-2xl font-bold text-purple-600"><?php echo count($committeeAgendas); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="../committee-meetings/schedule.php?committee=<?php echo $id; ?>"
                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center">
                        <i class="bi bi-calendar-plus mr-2"></i>Schedule Meeting
                    </a>
                    <a href="../referral-management/create.php?committee=<?php echo $id; ?>"
                        class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-center">
                        <i class="bi bi-inbox mr-2"></i>New Referral
                    </a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this committee?');"
                        class="mt-4">
                        <button type="submit" name="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            <i class="bi bi-trash mr-2"></i>Delete Committee
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>