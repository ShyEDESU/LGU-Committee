<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/NotificationHelper.php';

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

// Handle emergency meeting request
if (isset($_POST['request_emergency_meeting'])) {
    $emTitle    = trim($_POST['em_title']    ?? '');
    $emReason   = trim($_POST['em_reason']   ?? '');
    $emDate     = trim($_POST['em_date']     ?? '');
    if ($emTitle && $emReason && $emDate) {
        $newReqId = createEmergencyMeetingRequest($id, $emTitle, $emReason, $emDate);
        if ($newReqId) {
            // Notify all admins
            global $conn;
            $adminRes = $conn->query("SELECT u.user_id FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name IN ('Admin','Super Admin') AND u.is_active = 1");
            if ($adminRes) {
                while ($adminRow = $adminRes->fetch_assoc()) {
                    createNotification(
                        $adminRow['user_id'],
                        '🚨 Emergency Meeting Request',
                        "The {$committee['name']} committee has requested an emergency meeting: \"{$emTitle}\".",
                        'alert',
                        'urgent',
                        "pages/committee-profiles/view.php?id={$id}&tab=meetings"
                    );
                }
            }
            $_SESSION['success_message'] = 'Emergency meeting request submitted! The admin has been notified.';
        } else {
            $_SESSION['error_message'] = 'Failed to submit request. Please try again.';
        }
    } else {
        $_SESSION['error_message'] = 'All fields are required for the emergency meeting request.';
    }
    header('Location: view.php?id=' . $id . '&tab=meetings');
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

        <!-- Ordinances Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Ordinances</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">â€”</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-semibold">
                        <i class="bi bi-plug mr-1"></i>Connected via API
                    </p>
                </div>
                <div class="bg-amber-100 dark:bg-amber-900/30 rounded-lg p-4">
                    <i class="bi bi-bank2 text-amber-600 dark:text-amber-400 text-3xl"></i>
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
            <nav class="-mb-px flex space-x-6 overflow-x-auto">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="<?php echo !isset($_GET['tab']) ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'meetings' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agenda"
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'agenda' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
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
                    class="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'feedback' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-chat-right-text mr-1"></i>Feedback
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
        </div>
    </div>    <?php if (isset($_GET['tab']) && $_GET['tab'] === 'meetings'): ?>
        <?php
        $emergencyRequests = getEmergencyMeetingRequests($id);
        $pendingEmCount    = count(array_filter($emergencyRequests, fn($r) => $r['status'] === 'Pending'));
        ?>
        <!-- Synced Meetings Tab Content -->
        <div class="space-y-6">

            <!-- Emergency Meeting Request Banner -->
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-red-100 dark:bg-red-900/40 rounded-xl">
                            <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-red-900 dark:text-red-200">Need an Unscheduled Meeting?</p>
                            <p class="text-xs text-red-700 dark:text-red-400">Submit a request to the Central Scheduler. Requests need admin approval before they appear on the calendar.</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('emergency-modal').classList.remove('hidden')"
                            class="flex-shrink-0 bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition flex items-center gap-2">
                        <i class="bi bi-calendar-plus"></i> Request Emergency Meeting
                    </button>
                </div>
            </div>

            <!-- API Synced Meetings Banner -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-blue-950 dark:text-blue-200">Central Scheduling API Connected</p>
                            <p class="text-xs text-blue-800 dark:text-blue-400">Showing synced calendar feeds for the <?php echo htmlspecialchars($committee['name']); ?>.</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-1 rounded">Last Synced: Just Now</span>
                </div>
            </div>

            <!-- Synced Meetings Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upcoming Hearings &amp; Conferences</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750/30 transition">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Public Hearing</span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-1.5">Review of Proposed City Ordinance No. 2026-089</h4>
                                <p class="text-sm text-gray-500 mt-1">Discussion on traffic management and parking regulations in Barangay Karuhatan.</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                    <span><i class="bi bi-calendar3 mr-1"></i> July 15, 2026</span>
                                    <span><i class="bi bi-clock mr-1"></i> 10:00 AM – 12:00 PM</span>
                                    <span><i class="bi bi-geo-alt mr-1"></i> Session Hall, 2nd Floor, Valenzuela City Hall</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">API Synced</span>
                        </div>
                    </div>
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750/30 transition">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Committee Conference</span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-1.5">Budget Allocation Review for Local Health Services</h4>
                                <p class="text-sm text-gray-500 mt-1">Assessing financial updates for the central city hospital expansion project.</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                    <span><i class="bi bi-calendar3 mr-1"></i> July 22, 2026</span>
                                    <span><i class="bi bi-clock mr-1"></i> 02:00 PM – 04:30 PM</span>
                                    <span><i class="bi bi-geo-alt mr-1"></i> Conference Room A, LGU Annex Bldg</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">API Synced</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Requests History -->
            <?php if (!empty($emergencyRequests)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-clock-history text-red-500"></i> Emergency Meeting Requests
                        <?php if ($pendingEmCount > 0): ?>
                            <span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $pendingEmCount; ?> pending</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($emergencyRequests as $emReq):
                        $emStatus = $emReq['status'];
                        $emBadge = match($emStatus) {
                            'Pending'  => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'Approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                            'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                            default    => 'bg-gray-100 text-gray-700',
                        };
                    ?>
                    <div class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full <?php echo $emBadge; ?>"><?php echo htmlspecialchars($emStatus); ?></span>
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($emReq['title']); ?></p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($emReq['reason']); ?></p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                    <span><i class="bi bi-calendar mr-1"></i><?php echo date('M j, Y g:i A', strtotime($emReq['proposed_date'])); ?></span>
                                    <span><i class="bi bi-person mr-1"></i><?php echo htmlspecialchars($emReq['requested_by_name']); ?></span>
                                    <span><i class="bi bi-clock mr-1"></i><?php echo date('M j, Y', strtotime($emReq['created_at'])); ?></span>
                                </div>
                                <?php if (!empty($emReq['admin_notes'])): ?>
                                    <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-xs text-gray-600 dark:text-gray-400"><strong>Admin note:</strong> <?php echo htmlspecialchars($emReq['admin_notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Emergency Meeting Modal -->
        <div id="emergency-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
                            <i class="bi bi-exclamation-triangle-fill text-red-600 text-xl"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Request Emergency Meeting</h2>
                    </div>
                    <button onclick="document.getElementById('emergency-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="request_emergency_meeting" value="1">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Meeting Topic / Title <span class="text-red-500">*</span></label>
                        <input type="text" name="em_title" required placeholder="e.g., Urgent Review of Flooding Ordinance"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Proposed Date &amp; Time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="em_date" required
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Reason for Urgency <span class="text-red-500">*</span></label>
                        <textarea name="em_reason" rows="3" required placeholder="Briefly explain why this meeting cannot wait for the next scheduled session..."
                                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-3">
                        <p class="text-xs text-amber-800 dark:text-amber-300"><i class="bi bi-info-circle mr-1"></i>This request will be forwarded to the admin for review. It will appear on the schedule only after approval.</p>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('emergency-modal').classList.add('hidden')"
                                class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-bold transition">
                            <i class="bi bi-send mr-2"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'agenda'): ?>
        <!-- Synced Agenda Tab Content -->
        <div class="space-y-6">
            <!-- API Connection Indicator Banner -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-amber-950 dark:text-amber-200">Sanggunian Secretariat Plenary API Connected</p>
                            <p class="text-xs text-amber-800 dark:text-amber-400">Showing active items on the session agendas assigned to this committee.</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 px-2.5 py-1 rounded">Session Feed: Active</span>
                </div>
            </div>

            <!-- Agenda Items List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Active Agenda Items</h3>
                </div>
                <div class="divide-y divide-gray-150 dark:divide-gray-700">
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750/30 transition">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-start gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-850 dark:bg-red-900/30 dark:text-red-300">Agenda #3</span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-1.5">Proposed Ordinance No. 1024 - Public Green Spaces Expansion</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Deliberating the reservation of vacant LGU property for community parks and recreational centers.</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                    <span><i class="bi bi-person mr-1"></i> Sponsor: Hon. Councilor K. Santos</span>
                                    <span><i class="bi bi-tag mr-1"></i> Health & Environment</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Pending Committee Report</span>
                        </div>
                    </div>
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750/30 transition">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-start gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-850 dark:bg-red-900/30 dark:text-red-300">Agenda #7</span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-1.5">Resolution No. 405 - Valenzuela Digital City Hall Integration</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">A resolution authorizing the executive branch to enter into database sync APIs with peripheral city halls.</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                    <span><i class="bi bi-person mr-1"></i> Sponsor: Hon. Chairperson T. Lopez</span>
                                    <span><i class="bi bi-tag mr-1"></i> Information Technology</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Passed Committee Review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'feedback'): ?>
        <!-- Citizen Feedback Tab Content -->
        <div class="space-y-6">
            <!-- API Connection Indicator Banner -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 p-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-purple-950 dark:text-purple-200">Citizen Feedback Portal API Connected</p>
                            <p class="text-xs text-purple-800 dark:text-purple-400">Live streams of concerns and opinions submitted by Valenzuela citizens matching this committee's jurisdiction.</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 px-2.5 py-1 rounded">Feed: Active</span>
                </div>
            </div>

            <!-- Feedback Items -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Public Input Log</h3>
                </div>
                <div class="divide-y divide-gray-150 dark:divide-gray-700">
                    <div class="p-6">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Citizen Juan Dela Cruz</h4>
                                <span class="text-xs text-gray-500 block mt-0.5">Submitted: July 08, 2026 via Valenzuela Citizen App</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 bg-gray-50 dark:bg-gray-750/30 p-3 rounded border border-gray-100 dark:border-gray-700">
                                    "I hope the committee reviews the trash collection schedule in our district. We need more regular sweeps to avoid blocked drains before the rainy season begins."
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Pending Review</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Maria Santos (Barangay Marulas)</h4>
                                <span class="text-xs text-gray-500 block mt-0.5">Submitted: July 05, 2026 via Web Portal</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 bg-gray-50 dark:bg-gray-750/30 p-3 rounded border border-gray-100 dark:border-gray-700">
                                    "Supporting the new resolution for public library funding. It would help a lot of students in our neighborhood who don't have good internet access at home."
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Noted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Overview Tab -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content (left 2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- â‘  Committee Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white mb-5 flex items-center gap-2">
                        <i class="bi bi-info-circle text-red-500"></i> Committee Information
                    </h2>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Chairperson</p>
                            <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['chair']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Vice-Chairperson</p>
                            <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['vice_chair'] ?? 'Not Assigned'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Secretary</p>
                            <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['secretary'] ?? 'Not Assigned'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Committee Type</p>
                            <span class="inline-block px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 rounded-full text-xs font-semibold">
                                <?php echo htmlspecialchars($committee['type']); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</p>
                            <span class="inline-block px-3 py-1 <?php echo $committee['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'; ?> rounded-full text-xs font-semibold">
                                <?php echo htmlspecialchars($committee['status']); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Jurisdiction</p>
                            <p class="text-gray-700 dark:text-gray-300 text-sm"><?php echo htmlspecialchars($committee['jurisdiction']); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($committee['description'])): ?>
                        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Description</p>
                            <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($committee['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- â‘¡ Members Preview -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-people text-red-500"></i> Committee Members
                            <span class="text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-0.5 rounded-full"><?php echo count($members); ?></span>
                        </h2>
                        <a href="members.php?id=<?php echo $id; ?>" class="text-sm text-red-600 hover:text-red-700 font-medium">View &rarr;</a>
                    </div>
                    <?php if (empty($members)): ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="bi bi-people text-3xl block mb-2"></i>
                            <p class="text-sm">No members assigned yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach (array_slice($members, 0, 5) as $member): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-700 dark:text-red-300 font-bold text-sm flex-shrink-0">
                                            <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($member['name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($member['role']); ?></p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400 dark:text-gray-500"><?php echo htmlspecialchars($member['department'] ?? $member['position'] ?? ''); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($members) > 5): ?>
                                <a href="members.php?id=<?php echo $id; ?>" class="block text-center text-red-600 hover:text-red-700 text-xs font-semibold mt-2 py-2 border border-dashed border-red-200 dark:border-red-800 rounded-lg">
                                    + <?php echo count($members) - 5; ?> more members
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- â‘¢ Upcoming Meetings -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-calendar-event text-blue-500"></i> Upcoming Meetings
                            <span class="text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full"><?php echo count($upcomingMeetings); ?></span>
                        </h2>
                        <a href="view.php?id=<?php echo $id; ?>&tab=meetings" class="text-sm text-red-600 hover:text-red-700 font-medium">View &rarr;</a>
                    </div>
                    <?php if (empty($upcomingMeetings)): ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="bi bi-calendar-x text-3xl block mb-2"></i>
                            <p class="text-sm">No upcoming meetings scheduled</p>
                            <a href="view.php?id=<?php echo $id; ?>&amp;tab=meetings" class="text-xs text-red-600 hover:underline mt-1 inline-block">View Meetings tab &rarr;</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($upcomingMeetings, 0, 4) as $meeting): ?>
                                <div class="flex items-start gap-4 p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-800/30">
                                    <div class="text-center bg-white dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm flex-shrink-0 min-w-[52px]">
                                        <p class="text-xs font-bold text-red-600 uppercase"><?php echo date('M', strtotime($meeting['date'])); ?></p>
                                        <p class="text-xl font-black text-gray-900 dark:text-white leading-none"><?php echo date('d', strtotime($meeting['date'])); ?></p>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($meeting['title'] ?? 'Untitled Meeting'); ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <i class="bi bi-clock mr-1"></i><?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                                            &nbsp;&middot;&nbsp;<i class="bi bi-geo-alt mr-1"></i><?php echo htmlspecialchars($meeting['venue'] ?? 'TBD'); ?>
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 flex-shrink-0">
                                        <?php echo htmlspecialchars($meeting['status'] ?? 'Scheduled'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- â‘£ Committee Reports -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-file-text text-green-500"></i> Committee Reports
                            <span class="text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full"><?php echo count($committeeReports); ?></span>
                        </h2>
                        <?php if (canCreate($userId, 'reports') && ($userRole === 'Admin' || $userRole === 'Super Admin' || $userId == $committee['chairperson_id'] || $userId == $committee['vice_chair_id'] || $userId == $committee['secretary_id'])): ?>
                            <a href="../committee-reports/create.php?committee=<?php echo $id; ?>" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                <i class="bi bi-plus-circle mr-1"></i>Draft New
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($committeeReports)): ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="bi bi-file-earmark-text text-3xl block mb-2"></i>
                            <p class="text-sm">No reports drafted yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach (array_slice($committeeReports, 0, 5) as $report):
                                $statusColor = match($report['status'] ?? '') {
                                    'Draft'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'Voting'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'Approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    default    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                };
                            ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($report['title']); ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Recommendation: <span class="font-semibold"><?php echo htmlspecialchars($report['recommendation'] ?? 'N/A'); ?></span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?php echo $statusColor; ?>"><?php echo htmlspecialchars($report['status']); ?></span>
                                        <a href="../committee-reports/view.php?id=<?php echo $report['report_id']; ?>" class="text-red-600 hover:text-red-700 text-xs font-semibold">View &rarr;</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ⑤ Recent Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-folder text-orange-500"></i> Recent Documents
                            <span class="text-xs font-semibold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-2 py-0.5 rounded-full"><?php echo count($documents); ?></span>
                        </h2>
                        <a href="documents.php?id=<?php echo $id; ?>" class="text-sm text-red-600 hover:text-red-700 font-medium">View &rarr;</a>
                    </div>
                    <?php if (empty($documents)): ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="bi bi-folder2-open text-3xl block mb-2"></i>
                            <p class="text-sm">No documents uploaded yet</p>
                            <a href="upload-document.php?committee_id=<?php echo $id; ?>" class="text-xs text-red-600 hover:underline mt-1 inline-block">Upload one now &rarr;</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach (array_slice($documents, 0, 5) as $doc): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg gap-3">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded flex-shrink-0">
                                            <i class="bi bi-file-earmark-text text-red-600 dark:text-red-400"></i>
                                        </div>
                                        <div class="truncate">
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($doc['title']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['type']); ?> &middot; <?php echo date('M d, Y', strtotime($doc['uploaded_date'])); ?></p>
                                        </div>
                                    </div>
                                    <?php if (!empty($doc['file_path'])): ?>
                                        <a href="download-document.php?id=<?php echo $doc['id']; ?>" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded flex-shrink-0" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ⑥ Ordinances (API Integration Notice) -->
                <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/40 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex-shrink-0">
                            <i class="bi bi-bank2 text-amber-600 dark:text-amber-400 text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-base font-bold text-amber-900 dark:text-amber-200">Ordinances</h2>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-300 rounded uppercase tracking-wider">API</span>
                            </div>
                            <p class="text-sm text-amber-800 dark:text-amber-400">
                                Ordinances related to this committee will be pulled from the <strong>City Hall Ordinances System</strong> once the API connection is established. Once connected, all ordinances assigned to this committee will appear here automatically.
                            </p>
                            <a href="documents.php?id=<?php echo $id; ?>&subtab=ordinances" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline mt-3">
                                <i class="bi bi-plug"></i> View Ordinances Tab &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar (right 1/3) -->
            <div class="space-y-5">

                <!-- Committee Statistics -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Committee Statistics</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-people text-red-600 dark:text-red-400"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Members</span>
                            </div>
                            <span class="text-xl font-bold text-red-600"><?php echo $committee['member_count'] ?? count($members); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-calendar-event text-blue-600 dark:text-blue-400"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Meetings</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-bold text-blue-600"><?php echo count($committeeMeetings); ?></span>
                                <p class="text-xs text-blue-400"><?php echo count($upcomingMeetings); ?> upcoming</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-folder text-orange-600 dark:text-orange-400"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Documents</span>
                            </div>
                            <span class="text-xl font-bold text-orange-600"><?php echo $committee['document_count'] ?? count($documents); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-file-text text-green-600 dark:text-green-400"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Reports</span>
                            </div>
                            <span class="text-xl font-bold text-green-600"><?php echo count($committeeReports); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-bank2 text-amber-600 dark:text-amber-400"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Ordinances</span>
                            </div>
                            <span class="text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded">Via API</span>
                        </div>
                    </div>
                </div>

                <!-- System Connections -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">System Connections</h2>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><i class="bi bi-calendar-week"></i> Scheduling System</span>
                            <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded font-semibold">Pending</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><i class="bi bi-bank2"></i> Ordinances System</span>
                            <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded font-semibold">Pending</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><i class="bi bi-chat-dots"></i> Feedback Portal</span>
                            <span class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded font-semibold">Pending</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><i class="bi bi-journal-text"></i> Session Agenda</span>
                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded font-semibold">Pending</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Quick Actions</h2>
                    <div class="space-y-1">
                        <a href="members.php?id=<?php echo $id; ?>" class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                            <i class="bi bi-person-plus text-red-500"></i> Manage Members
                        </a>
                        <a href="upload-document.php?committee_id=<?php echo $id; ?>" class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                            <i class="bi bi-upload text-orange-500"></i> Upload Document
                        </a>
                        <a href="view.php?id=<?php echo $id; ?>&tab=meetings" class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                            <i class="bi bi-calendar-event text-blue-500"></i> View Meetings
                        </a>

                        <?php if (canEdit($userId, 'committees', $id)): ?>
                        <a href="edit.php?id=<?php echo $id; ?>" class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                            <i class="bi bi-pencil text-amber-500"></i> Edit Committee
                        </a>
                        <?php endif; ?>
                        <?php if (canDelete($userId, 'committees', $id)): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this committee? This cannot be undone.');">
                            <button type="submit" name="delete" class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                <i class="bi bi-trash"></i> Delete Committee
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
