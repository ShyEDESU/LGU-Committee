<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Get committee data
$stats = getCommitteeStatistics($id);
$committee = array_merge($committee, $stats);

// Access control for non-admins
$userRole = $_SESSION['user_role'] ?? 'User';
$userId = $_SESSION['user_id'] ?? 0;

if ($userRole !== 'Admin' && $userRole !== 'Super Admin') {
    $isLeadership = (
        $userId == ($committee['chairperson_id'] ?? 0) ||
        $userId == ($committee['vice_chair_id'] ?? 0) ||
        $userId == ($committee['secretary_id'] ?? 0)
    );

    if (!$isLeadership) {
        require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
        if (!isCommitteeMember($id, $userId)) {
            $_SESSION['error_message'] = 'You do not have permission to view this committee';
            header('Location: index.php');
            exit();
        }
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    if (!canDelete($userId, 'committees', $id)) {
        $_SESSION['error_message'] = 'Unauthorized to delete this committee';
        header('Location: index.php');
        exit();
    }
    deleteCommittee($id);
    $_SESSION['success_message'] = 'Committee deleted successfully';
    header('Location: index.php');
    exit();
}

$members = getCommitteeMembers($id);
$documents = getCommitteeDocuments($id);

// Get meetings for this committee
$committeeMeetings = getMeetingsByCommittee($id);
$upcomingMeetings = array_filter($committeeMeetings, function ($m) {
    return strtotime($m['date']) >= strtotime('today') && $m['status'] !== 'Completed';
});
$pastMeetings = array_filter($committeeMeetings, function ($m) {
    return strtotime($m['date']) < strtotime('today') || $m['status'] === 'Completed';
});

// Calculate urgent agendas (upcoming meetings without any agenda items)
$urgentAgendasCount = 0;
foreach ($upcomingMeetings as $m) {
    $agendas = getAgendaItems($m['id']);
    if (empty($agendas)) {
        $urgentAgendasCount++;
    }
}

// Committee Reports
$committeeReports = getAllReports(['committee_id' => $id]);
$votingReportsCount = count(array_filter($committeeReports, function ($r) {
    return $r['status'] === 'Voting';
}));

// Pagination logic
$itemsPerPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'];
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item active">
                <?php echo htmlspecialchars($committee['name']); ?>
            </li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($committee['name']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?php echo htmlspecialchars($committee['type']); ?> Committee
            </p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to List
            </a>
            <?php if (canEdit($userId, 'committees', $id)): ?>
                <a href="edit.php?id=<?php echo $id; ?>"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    <i class="bi bi-pencil mr-2"></i>Edit
                </a>
            <?php endif; ?>
            <button onclick="window.print()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-printer mr-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Members Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Members</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo $committee['member_count']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total members</p>
                </div>
                <div class="bg-red-100 dark:bg-blue-900/30 rounded-lg p-4">
                    <i class="bi bi-people text-red-600 dark:text-blue-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Referrals Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Referrals</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo $committee['referral_count']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total referrals</p>
                </div>
                <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-4">
                    <i class="bi bi-file-earmark-text text-red-600 dark:text-red-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Documents</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo count($documents); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total files</p>
                </div>
                <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-4">
                    <i class="bi bi-folder text-orange-600 dark:text-orange-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Meetings Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meetings</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo count($committeeMeetings); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php echo count($upcomingMeetings); ?> upcoming
                    </p>
                </div>
                <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-4">
                    <i class="bi bi-calendar-event text-purple-600 dark:text-purple-400 text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="<?php echo !isset($_GET['tab']) ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>

                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
        </div>
    </div>


        <!-- Overview Tab Content (existing content) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-bold mb-4">Committee Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Chairperson</p>
                            <p class="font-semibold">
                                <?php echo htmlspecialchars($committee['chair']); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Vice-Chairperson</p>
                            <p class="font-semibold">
                                <?php echo htmlspecialchars($committee['vice_chair'] ?? 'Not Assigned'); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Secretary</p>
                            <p class="font-semibold">
                                <?php echo htmlspecialchars($committee['secretary'] ?? 'Not Assigned'); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                            <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
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
                        <p class="text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($committee['jurisdiction']); ?>
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
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($member['name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($member['role']); ?>
                                        </p>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($member['department'] ?? $member['position'] ?? 'N/A'); ?>
                                    </span>
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
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($meeting['title']); ?>
                                        </p>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full <?php echo $meeting['status'] === 'Scheduled' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
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

                <!-- Committee Reports -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold">Committee Reports</h2>
                        <?php if (canCreate($userId, 'reports') && ($userRole === 'Admin' || $userRole === 'Super Admin' || $userId == $committee['chairperson_id'] || $userId == $committee['vice_chair_id'] || $userId == $committee['secretary_id'])): ?>
                            <a href="../committee-reports/create.php?committee=<?php echo $id; ?>"
                                class="text-red-600 hover:text-red-700">
                                <i class="bi bi-plus-circle mr-1"></i>Draft New
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($committeeReports)): ?>
                        <p class="text-gray-500 text-sm">No reports drafted yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($committeeReports, 0, 5) as $report):
                                $sigs = getReportSignatures($report['report_id']);
                                $activeMembers = getCommitteeMembersForReport($report['committee_id']);
                                
                                $signedCount = 0;
                                foreach ($sigs as $s) {
                                    if ($s['status'] === 'Approved') $signedCount++;
                                }
                                
                                $statusColor = 'gray';
                                if ($report['status'] === 'Draft') $statusColor = 'yellow';
                                elseif ($report['status'] === 'Voting') $statusColor = 'blue';
                                elseif ($report['status'] === 'Approved') $statusColor = 'green';
                                elseif ($report['status'] === 'Rejected') $statusColor = 'red';
                                
                                $recColor = 'green';
                                if ($report['recommendation'] === 'Disapprove') $recColor = 'red';
                                elseif ($report['recommendation'] === 'Amend') $recColor = 'yellow';
                                ?>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-semibold text-sm">
                                            <?php echo htmlspecialchars($report['title']); ?>
                                        </p>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800 dark:bg-<?php echo $statusColor; ?>-900/30 dark:text-<?php echo $statusColor; ?>-200">
                                            <?php echo htmlspecialchars($report['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        <span>
                                            <span class="px-1.5 py-0.5 text-2xs rounded bg-<?php echo $recColor; ?>-100 text-<?php echo $recColor; ?>-800 dark:bg-<?php echo $recColor; ?>-900/30 dark:text-<?php echo $recColor; ?>-200 font-semibold font-sans">
                                                <?php echo htmlspecialchars($report['recommendation']); ?>
                                            </span>
                                        </span>
                                        <span>
                                            <i class="bi bi-pen mr-1"></i>
                                            <?php echo $signedCount; ?> / <?php echo count($activeMembers); ?> signed
                                        </span>
                                    </div>
                                    <a href="../committee-reports/view.php?id=<?php echo $report['report_id']; ?>"
                                        class="text-red-605 hover:text-red-700 text-xs font-semibold mt-2 inline-block">
                                        View Details →
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($committeeReports) > 5): ?>
                                <a href="view.php?id=<?php echo $id; ?>&tab=reports"
                                    class="block text-center text-red-650 hover:text-red-805 text-xs font-semibold mt-2">
                                    View all <?php echo count($committeeReports); ?> reports →
                                </a>
                            <?php endif; ?>
                        </div>
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
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($actionItem['title']); ?>
                                        </p>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full <?php echo ($actionItem['status'] ?? '') === 'Done' ? 'bg-green-100 text-green-800' :
                                                (($actionItem['status'] ?? '') === 'In Progress' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); ?>">
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
                                                Due:
                                                <?php echo date('M j, Y', strtotime($actionItem['due_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($actionItem['progress'] ?? 0) > 0): ?>
                                        <div class="mb-2">
                                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                <div class="bg-red-600 h-2 rounded-full"
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
                                    View all
                                    <?php echo count($committeeActionItems); ?> action items →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Meetings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold">Recent Meetings</h2>
                        <a href="../committee-meetings/index.php" class="text-red-600 hover:text-red-700">
                            View All →
                        </a>
                    </div>
                    <?php if (empty($committeeMeetings)): ?>
                        <p class="text-gray-500">No meetings scheduled yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($committeeMeetings, 0, 5) as $meeting): ?>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="font-semibold text-sm"><?php echo htmlspecialchars($meeting['title'] ?? $meeting['meeting_title'] ?? 'Untitled Meeting'); ?></p>
                                        <span class="px-2 py-1 text-xs rounded-full
                                        <?php
                                        $mStatus = $meeting['status'] ?? 'Scheduled';
                                        echo $mStatus === 'Completed' ? 'bg-green-100 text-green-800' :
                                            ($mStatus === 'Ongoing' ? 'bg-blue-100 text-blue-800' :
                                                ($mStatus === 'Cancelled' ? 'bg-red-100 text-red-800' :
                                                    'bg-yellow-100 text-yellow-800'));
                                        ?>">
                                            <?php echo htmlspecialchars($mStatus); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <i class="bi bi-calendar mr-2"></i>
                                        <?php echo date('M j, Y', strtotime($meeting['date'] ?? $meeting['meeting_date'] ?? 'now')); ?>
                                    </div>
                                    <a href="../committee-meetings/view.php?id=<?php echo $meeting['id'] ?? $meeting['meeting_id']; ?>"
                                        class="text-red-600 hover:text-red-700 text-sm mt-2 inline-block">
                                        View Details →
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Recent Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold">Recent Documents</h2>
                        <a href="documents.php?id=<?php echo $id; ?>" class="text-red-600 hover:text-red-700">
                            View All →
                        </a>
                    </div>
                    <?php if (empty($documents)): ?>
                        <p class="text-gray-500 text-sm">No documents uploaded yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($documents, 0, 5) as $doc): ?>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-between">
                                    <div class="flex items-center overflow-hidden">
                                        <div class="p-2 bg-red-100 dark:bg-blue-900/30 rounded mr-3 flex-shrink-0">
                                            <i class="bi bi-file-earmark-text text-red-600 dark:text-blue-400"></i>
                                        </div>
                                        <div class="truncate">
                                            <p class="font-semibold text-sm truncate">
                                                <?php echo htmlspecialchars($doc['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                <?php echo htmlspecialchars($doc['type']); ?> •
                                                <?php echo date('M d, Y', strtotime($doc['uploaded_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php if (!empty($doc['file_path'])): ?>
                                        <a href="download-document.php?id=<?php echo $doc['id']; ?>"
                                            class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-blue-900/20 rounded flex-shrink-0">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
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
                            <span class="text-2xl font-bold text-red-600">
                                <?php echo $committee['member_count'] ?? 0; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <?php if (canDelete($userId, 'committees', $id)): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this committee?');"
                                class="mt-4">
                                <button type="submit" name="delete"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                    <i class="bi bi-trash mr-2"></i>Delete Committee
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
</div> <!-- Closing container-fluid -->
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>