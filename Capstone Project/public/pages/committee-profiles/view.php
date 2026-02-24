<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

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
    $_SESSION['success_message'] = 'Committee has been archived successfully and preserved in the legislative records.';
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

// Calculate urgent referrals (Pending or High Priority status)
$allReferrals = getAllReferrals();
$committeeReferrals = array_filter($allReferrals, function ($r) use ($id) {
    return ($r['committee_id'] ?? null) == $id;
});
$urgentReferralsCount = count(array_filter($committeeReferrals, function ($r) {
    return $r['status'] === 'Pending' || ($r['priority'] ?? '') === 'High';
}));

// Committee Agendas
$committeeAgendas = getAgendasByCommittee($id);

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

    <?php
    // Check for pending members that need approval
    $pendingMembersCount = count(array_filter($members, function ($m) {
        return ($m['membership_status'] ?? 'Active') === 'Pending';
    }));
    $isChairOrAdmin = ($userRole === 'Admin' || $userRole === 'Super Admin' || $userId == ($committee['chairperson_id'] ?? 0) || $userId == ($committee['vice_chair_id'] ?? 0));

    if ($pendingMembersCount > 0 && $isChairOrAdmin): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 animate-pulse">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-person-exclamation text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 font-bold">
                        ACTION REQUIRED: There are <?php echo $pendingMembersCount; ?> pending member recommendations
                        awaiting your official approval.
                        <a href="members.php?id=<?php echo $id; ?>" class="underline ml-2">Approve Appointments →</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'meetings' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                    <?php if (count($upcomingMeetings) > 0): ?>
                        <span
                            class="ml-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 text-xs font-semibold px-2 py-0.5 rounded-full">
                            <?php echo count($upcomingMeetings); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agendas"
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'agendas' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-journal-text mr-1"></i>Agendas
                    <?php if (count($committeeAgendas) > 0): ?>
                        <span
                            class="ml-2 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 text-xs font-semibold px-2 py-0.5 rounded-full">
                            <?php echo count($committeeAgendas); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=referrals"
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'referrals' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-arrow-left-right mr-1"></i>Referrals
                    <?php if ($urgentReferralsCount > 0): ?>
                        <span
                            class="ml-2 bg-red-100 text-red-800 dark:bg-blue-900 dark:text-blue-300 text-xs font-semibold px-2 py-0.5 rounded-full">
                            <?php echo $urgentReferralsCount; ?>
                        </span>
                    <?php endif; ?>
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

    <?php if (isset($_GET['tab']) && $_GET['tab'] === 'meetings'): ?>
        <!-- Meetings Tab Content -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Committee Meetings</h2>
                <?php if (canEdit($userId, 'committees', $id)): ?>
                    <a href="../committee-meetings/schedule.php?committee=<?php echo $id; ?>"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="bi bi-plus-circle mr-2"></i>Schedule Meeting
                    </a>
                <?php endif; ?>
            </div>

            <!-- Upcoming Meetings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-calendar-check text-green-600 mr-2"></i>Upcoming Meetings
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($upcomingMeetings)): ?>
                        <div class="text-center py-8">
                            <i class="bi bi-calendar-x text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                            <p class="text-gray-500 dark:text-gray-400">No upcoming meetings scheduled</p>
                            <a href="../committee-meetings/schedule.php?committee=<?php echo $id; ?>"
                                class="inline-block mt-4 text-red-600 hover:text-red-700 dark:text-red-400 font-semibold">
                                Schedule a meeting →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($upcomingMeetings as $meeting): ?>
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($meeting['title']); ?>
                                            </h4>
                                            <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                                <p><i
                                                        class="bi bi-calendar3 mr-2"></i><?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                                                </p>
                                                <p><i
                                                        class="bi bi-clock mr-2"></i><?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                                                </p>
                                                <p><i
                                                        class="bi bi-geo-alt mr-2"></i><?php echo htmlspecialchars($meeting['venue']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span
                                                class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-blue-900 dark:text-blue-300">
                                                <?php echo $meeting['status']; ?>
                                            </span>
                                            <a href="../committee-meetings/view.php?id=<?php echo $meeting['id']; ?>"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm font-semibold">
                                                View Details →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Meetings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-clock-history text-gray-600 mr-2"></i>Past Meetings
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($pastMeetings)): ?>
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">No past meetings</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php
                            $totalPastMeetings = count($pastMeetings);
                            $paginatedPastMeetings = array_slice($pastMeetings, $offset, $itemsPerPage);
                            $totalPagesMeetings = ceil($totalPastMeetings / $itemsPerPage);

                            foreach ($paginatedPastMeetings as $meeting):
                                ?>
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($meeting['title']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                                        </p>
                                    </div>
                                    <a href="../committee-meetings/view.php?id=<?php echo $meeting['id']; ?>"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm">
                                        View →
                                    </a>
                                </div>
                            <?php endforeach; ?>

                            <!-- Pagination for Meetings -->
                            <?php if ($totalPagesMeetings > 1): ?>
                                <div
                                    class="flex items-center justify-between mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <div class="text-xs text-gray-500">
                                        Showing
                                        <?php echo $offset + 1; ?>-<?php echo min($offset + $itemsPerPage, $totalPastMeetings); ?>
                                        of <?php echo $totalPastMeetings; ?>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if ($page > 1): ?>
                                            <a href="?id=<?php echo $id; ?>&tab=meetings&page=<?php echo $page - 1; ?>"
                                                class="p-2 border border-gray-300 rounded hover:bg-gray-100 text-xs">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($page < $totalPagesMeetings): ?>
                                            <a href="?id=<?php echo $id; ?>&tab=meetings&page=<?php echo $page + 1; ?>"
                                                class="p-2 border border-gray-300 rounded hover:bg-gray-100 text-xs">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'agendas'): ?>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Meeting Agendas</h2>
                <?php if (canEdit($userId, 'committees', $id)): ?>
                    <a href="../agenda-builder/create.php?committee=<?php echo $id; ?>"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="bi bi-plus-circle mr-2"></i>Create Agenda
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($committeeMeetings)): ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <i class="bi bi-journal-x text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">No meetings scheduled, so no agendas found.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    $totalAgendas = count($committeeMeetings);
                    $paginatedAgendas = array_slice($committeeMeetings, $offset, $itemsPerPage);
                    $totalPagesAgendas = ceil($totalAgendas / $itemsPerPage);

                    foreach ($paginatedAgendas as $meeting): ?>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition">
                            <div
                                class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($meeting['title']); ?>
                                </h3>
                                <span
                                    class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($meeting['date'])); ?></span>
                            </div>
                            <div class="p-4">
                                <?php
                                $agendas = getAgendaItems($meeting['id']);
                                if (empty($agendas)):
                                    ?>
                                    <p class="text-xs text-gray-500 italic">No agenda items defined yet.</p>
                                <?php else: ?>
                                    <ul class="space-y-2">
                                        <?php foreach (array_slice($agendas, 0, 3) as $item): ?>
                                            <li class="text-sm flex items-start gap-2">
                                                <span class="text-red-600">•</span>
                                                <span
                                                    class="text-gray-700 dark:text-gray-300 line-clamp-1"><?php echo htmlspecialchars($item['title']); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (count($agendas) > 3): ?>
                                            <li class="text-xs text-gray-500 italic ml-4">+ <?php echo count($agendas) - 3; ?>
                                                more items...</li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                    <a href="../agenda-builder/view.php?id=<?php echo $meeting['id']; ?>"
                                        class="text-red-600 hover:text-red-700 text-sm font-semibold">
                                        View Full Agenda →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination for Agendas -->
                <?php if ($totalPagesAgendas > 1): ?>
                    <div
                        class="flex items-center justify-between mt-6 bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500">
                            Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $itemsPerPage, $totalAgendas); ?> of
                            <?php echo $totalAgendas; ?> agendas
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?id=<?php echo $id; ?>&tab=agendas&page=<?php echo $page - 1; ?>"
                                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPagesAgendas): ?>
                                <a href="?id=<?php echo $id; ?>&tab=agendas&page=<?php echo $page + 1; ?>"
                                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'referrals'): ?>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Committee Referrals</h2>
                <?php if (canEdit($userId, 'committees', $id)): ?>
                    <a href="../referral-management/create.php?committee=<?php echo $id; ?>"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="bi bi-plus-circle mr-2"></i>New Referral
                    </a>
                <?php endif; ?>
            </div>

            <?php
            $committeeReferrals = array_filter(getAllReferrals(), function ($r) use ($id) {
                return ($r['committee_id'] ?? null) == $id;
            });
            if (empty($committeeReferrals)):
                ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <i class="bi bi-arrow-left-right text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">No referrals assigned to this committee yet.</p>
                </div>
            <?php else: ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Document</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php
                            $totalReferrals = count($committeeReferrals);
                            $paginatedReferrals = array_slice($committeeReferrals, $offset, $itemsPerPage);
                            $totalPagesReferrals = ceil($totalReferrals / $itemsPerPage);

                            foreach ($paginatedReferrals as $ref): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($ref['title']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($ref['document_number'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($ref['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800"><?php echo $ref['status']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="../referral-management/view.php?id=<?php echo $ref['id']; ?>"
                                            class="text-red-600 hover:text-red-900">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Referrals -->
                <?php if ($totalPagesReferrals > 1): ?>
                    <div
                        class="flex items-center justify-between mt-6 bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500">
                            Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $itemsPerPage, $totalReferrals); ?> of
                            <?php echo $totalReferrals; ?> referrals
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?id=<?php echo $id; ?>&tab=referrals&page=<?php echo $page - 1; ?>"
                                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPagesReferrals): ?>
                                <a href="?id=<?php echo $id; ?>&tab=referrals&page=<?php echo $page + 1; ?>"
                                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
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
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Legal Basis (Authority)</p>
                            <span class="font-bold text-gray-900 dark:text-white">
                                <?php
                                $authority = $committee['creation_authority'] ?? 'N/A';
                                $linkedDoc = getDocumentByNumber($authority);
                                if ($linkedDoc): ?>
                                    <a href="../referral-management/view.php?id=<?php echo $linkedDoc['document_id']; ?>"
                                        class="text-red-600 hover:text-red-700 underline" title="View Source Resolution">
                                        <i class="bi bi-file-earmark-check mr-1"></i><?php echo htmlspecialchars($authority); ?>
                                    </a>
                                <?php else: ?>
                                    <i class="bi bi-bank mr-1 text-gray-400"></i><?php echo htmlspecialchars($authority); ?>
                                <?php endif; ?>
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
                                    View all
                                    <?php echo count($committeeMeetings); ?> meetings →
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
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($referral['title']); ?>
                                        </p>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full <?php echo $referral['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                                                ($referral['status'] === 'Under Review' ? 'bg-red-100 text-red-800' :
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
                                                Due:
                                                <?php echo date('M j, Y', strtotime($referral['deadline'])); ?>
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
                                View All Referrals (
                                <?php echo count($committeeReferrals); ?>) →
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

                <!-- Committee Agendas -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold">Committee Agendas</h2>
                        <a href="../agenda-builder/index.php" class="text-red-600 hover:text-red-700">
                            View All →
                        </a>
                    </div>
                    <?php
                    // Already fetched at the top
                    ?>
                    <?php if (empty($committeeAgendas)): ?>
                        <p class="text-gray-500">No agendas created yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($committeeAgendas, 0, 5) as $agenda): ?>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($agenda['meeting']['title']); ?>
                                        </p>
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
                                    View all
                                    <?php echo count($committeeAgendas); ?> agendas →
                                </a>
                            <?php endif; ?>
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
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Meetings Held</span>
                            <span class="text-2xl font-bold text-green-600">
                                <?php echo $committee['meetings_held']; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Pending Referrals</span>
                            <span class="text-2xl font-bold text-orange-600">
                                <?php echo $committee['pending_referrals']; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Agendas</span>
                            <span class="text-2xl font-bold text-purple-600">
                                <?php echo count($committeeAgendas); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <?php if (canEdit($userId, 'committees', $id)): ?>
                            <a href="../committee-meetings/schedule.php?committee=<?php echo $id; ?>"
                                class="block w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-center">
                                <i class="bi bi-calendar-plus mr-2"></i>Schedule Meeting
                            </a>
                            <a href="../referral-management/create.php?committee=<?php echo $id; ?>"
                                class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-center">
                                <i class="bi bi-inbox mr-2"></i>New Referral
                            </a>
                        <?php endif; ?>

                        <?php if (canDelete($userId, 'committees', $id)): ?>
                            <form method="POST"
                                onsubmit="return confirm('Professional Record Preservation: Are you sure you want to ARCHIVE this committee? It will be removed from active status but preserved in the audit archives.');"
                                class="mt-4">
                                <button type="submit" name="delete"
                                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="bi bi-archive mr-2"></i>Archive Committee
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> <!-- Closing container-fluid -->
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>