<?php
// Completely suppress all errors to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$meeting = getMeetingById($id);

if (!$meeting) {
    $_SESSION['error_message'] = 'Meeting not found';
    header('Location: index.php');
    exit();
}

// Handle delete
if (isset($_POST['delete'])) {
    deleteMeeting($id);
    $_SESSION['success_message'] = 'Meeting deleted successfully';
    header('Location: index.php');
    exit();
}

// Handle Status Change
if (isset($_POST['update_status'])) {
    $newStatus = $_POST['update_status'];
    if (changeMeetingStatus($id, $newStatus)) {
        $_SESSION['success_message'] = "Meeting status updated to $newStatus!";
        header("Location: view.php?id=$id");
        exit();
    }
}

// Get related data with error handling
$committee = getCommitteeById($meeting['committee_id']);
if (!$committee) {
    $committee = ['name' => 'Unknown', 'chair' => 'Unknown', 'member_count' => 0];
}

// Get agenda items
$agendaItems = [];
if (function_exists('getAgendaItems')) {
    $agendaItems = getAgendaItems($id) ?? [];
}

// Get attendance records
$attendance = [];
if (function_exists('getAttendanceRecords')) {
    $attendance = getAttendanceRecords($id) ?? [];
}

// Get documents
$documents = [];
if (function_exists('getMeetingDocuments')) {
    $documents = getMeetingDocuments($id) ?? [];
}

// Get minutes
$minutes = null;
if (function_exists('getMeetingMinutes')) {
    $minutes = getMeetingMinutes($id);
}

// Calculate statistics
$hasAgenda = !empty($agendaItems);
$agendaStatus = $meeting['agenda_status'] ?? 'None';

// Get attendance stats
$attendanceStats = [];
if (function_exists('getAttendanceStats')) {
    $attendanceStats = getAttendanceStats($id) ?? [];
}

// Ensure attendanceStats has default values
if (empty($attendanceStats)) {
    $attendanceStats = [
        'total_members' => 0,
        'present' => 0,
        'absent' => 0,
        'excused' => 0,
        'has_quorum' => false,
        'attendance_rate' => 0,
        'quorum_required' => 0
    ];
}
$minutesStatus = $minutes ? ($minutes['status'] ?? 'Draft') : 'None';

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $meeting['title'];
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Meetings</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($meeting['title']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700 dark:text-green-300"><?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?></p>
        </div>
    <?php endif; ?>

    <?php
    // Check for active votes for this meeting
    $activeVotes = getActiveVotesByMeeting($id);
    $userId = $_SESSION['user_id'] ?? 0;
    $isCommitteeMember = isCommitteeMember($meeting['committee_id'] ?? 0, $userId);
    if (!empty($activeVotes) && $isCommitteeMember): ?>
        <div class="bg-blue-600 rounded-lg shadow-lg p-4 mb-6 text-white flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-2 mr-4">
                    <i class="bi bi-hand-thumbs-up-fill text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Active Voting in Progress</h3>
                    <p class="text-blue-100 text-sm">There are active motions that require your vote.</p>
                </div>
            </div>
            <a href="../agenda-builder/member-vote.php?meeting_id=<?php echo $id; ?>"
                class="px-6 py-2 bg-white text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition shadow-sm">
                Vote Now
            </a>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <i class="bi bi-building mr-1"></i><?php echo htmlspecialchars($meeting['committee_name']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <i class="bi bi-arrow-left mr-2"></i>Back to List
            </a>
            <?php
            // Generate Google Calendar link with proper error handling
            try {
                // Meeting data uses these keys: title, description, venue, date, time_start, time_end
                $calendarTitle = urlencode($meeting['title'] ?? 'Committee Meeting');
                $calendarDetails = urlencode(strip_tags($meeting['description'] ?? ''));
                $calendarLocation = urlencode($meeting['venue'] ?? '');

                // Get date and time - handle both formats
                $meetingDate = $meeting['date'] ?? date('Y-m-d');
                $startTime = $meeting['time_start'] ?? '09:00:00';
                $endTime = $meeting['time_end'] ?? '';

                // Remove seconds if present (HH:MM:SS -> HH:MM)
                $startTime = substr($startTime, 0, 5);
                if ($endTime) {
                    $endTime = substr($endTime, 0, 5);
                }

                // Format for Google Calendar (YYYYMMDDTHHMMSS)
                $startDT = strtotime($meetingDate . ' ' . $startTime);
                $startFormatted = date('Ymd\THis', $startDT);

                if (!empty($endTime)) {
                    $endDT = strtotime($meetingDate . ' ' . $endTime);
                    $endFormatted = date('Ymd\THis', $endDT);
                } else {
                    // Default to 2 hours if no end time
                    $endFormatted = date('Ymd\THis', $startDT + (2 * 3600));
                }

                // Build URL
                $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
                    "&text=" . $calendarTitle .
                    "&dates=" . $startFormatted . "/" . $endFormatted .
                    "&details=" . $calendarDetails .
                    "&location=" . $calendarLocation;

            } catch (Exception $e) {
                $googleCalendarUrl = "#";
            }
            ?>
            <a href="<?php echo htmlspecialchars($googleCalendarUrl); ?>" target="_blank"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                <i class="bi bi-calendar-plus mr-2"></i>Add to Calendar
            </a>
            <?php if ($meeting['status'] === 'Completed'): ?>
                <a href="generate-minutes.php?id=<?php echo $id; ?>" target="_blank"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="bi bi-file-earmark-pdf mr-2"></i>Generate Draft Minutes
                </a>
            <?php endif; ?>
            <?php if (in_array($meeting['status'], ['Scheduled', 'Ongoing'])): ?>
                <form method="POST" class="inline">
                    <button type="submit" name="update_status" value="Completed"
                        class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg transition mr-1">
                        <i class="bi bi-check-all mr-2"></i>Complete Meeting
                    </button>
                </form>
            <?php elseif ($meeting['status'] === 'Completed'): ?>
                <form method="POST" class="inline">
                    <button type="submit" name="update_status" value="Ongoing"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition mr-1">
                        <i class="bi bi-arrow-counterclockwise mr-2"></i>Reopen Meeting
                    </button>
                </form>
            <?php endif; ?>

            <a href="edit.php?id=<?php echo $id; ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="bi bi-pencil mr-2"></i>Edit
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Agenda Status -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Agenda</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo $hasAgenda ? count($agendaItems) : 0; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php echo $agendaStatus; ?>
                    </p>
                </div>
                <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                    <i class="bi bi-list-check text-purple-600 dark:text-purple-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Attendance -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Attendance</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo $attendanceStats['present'] ?? 0; ?>/<?php echo $attendanceStats['total_members'] ?? 0; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php echo ($attendanceStats['has_quorum'] ?? false) ? 'Quorum Met' : 'No Quorum'; ?>
                    </p>
                </div>
                <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                    <i class="bi bi-person-check text-green-600 dark:text-green-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Minutes -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Minutes</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo $minutesStatus !== 'None' ? '✓' : '—'; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php echo $minutesStatus; ?>
                    </p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                    <i class="bi bi-file-earmark-text text-blue-600 dark:text-blue-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Documents</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo count($documents); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Files
                    </p>
                </div>
                <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3">
                    <i class="bi bi-folder text-orange-600 dark:text-orange-400 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                    <i class="bi bi-info-circle mr-1"></i>Details
                </a>
                <a href="attendance.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-person-check mr-1"></i>Attendance
                </a>
                <a href="minutes.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-file-earmark-text mr-1"></i>Minutes
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Meeting Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-calendar-event text-red-600 mr-2"></i>Meeting Information
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Date</p>
                        <p class="text-gray-900 dark:text-white font-semibold">
                            <i class="bi bi-calendar3 text-red-600 mr-2"></i>
                            <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Time</p>
                        <p class="text-gray-900 dark:text-white font-semibold">
                            <i class="bi bi-clock text-red-600 mr-2"></i>
                            <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                            <?php if (!empty($meeting['time_end'])): ?>
                                - <?php echo date('g:i A', strtotime($meeting['time_end'])); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Venue</p>
                        <p class="text-gray-900 dark:text-white font-semibold">
                            <i class="bi bi-geo-alt text-red-600 mr-2"></i>
                            <?php echo htmlspecialchars($meeting['venue']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            <?php echo $meeting['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                ($meeting['status'] === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'); ?>">
                            <i class="bi bi-circle-fill mr-2 text-xs"></i>
                            <?php echo htmlspecialchars($meeting['status']); ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($meeting['description'])): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Description</p>
                        <p class="text-gray-900 dark:text-white leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($meeting['description'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Agenda Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-list-check text-red-600 mr-2"></i>Meeting Agenda
                    </h2>
                    <?php if ($hasAgenda): ?>
                        <div class="flex space-x-2">
                            <a href="../agenda-builder/view.php?id=<?php echo $id; ?>"
                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 text-sm font-semibold transition">
                                <i class="bi bi-eye mr-1"></i>View Full
                            </a>
                            <a href="../agenda-builder/items.php?meeting_id=<?php echo $id; ?>"
                                class="text-green-600 hover:text-green-700 dark:text-green-400 text-sm font-semibold transition">
                                <i class="bi bi-pencil mr-1"></i>Edit
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="../agenda-builder/create.php?committee=<?php echo $meeting['committee_id']; ?>&meeting_id=<?php echo $id; ?>"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            <i class="bi bi-plus-circle mr-1"></i>Create Agenda
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($hasAgenda): ?>
                    <div class="space-y-2">
                        <?php
                        $displayItems = array_slice($agendaItems, 0, 5);
                        foreach ($displayItems as $index => $item):
                            ?>
                            <div
                                class="flex items-start p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <span
                                    class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-3">
                                    <?php echo ($index + 1); ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </p>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <span class="flex-shrink-0 text-sm text-gray-500 dark:text-gray-400 ml-3">
                                    <?php echo $item['duration']; ?> min
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($agendaItems) > 5): ?>
                            <div class="text-center pt-2">
                                <a href="../agenda-builder/view.php?id=<?php echo $id; ?>"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm font-semibold transition">
                                    View all <?php echo count($agendaItems); ?> items →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="bi bi-file-earmark-text text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No agenda created yet</p>
                        <a href="../agenda-builder/create.php?committee=<?php echo $meeting['committee_id']; ?>&meeting_id=<?php echo $id; ?>"
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="bi bi-plus-circle mr-2"></i>Create Agenda Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Committee Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-building text-red-600 mr-2"></i>Committee
                </h3>
                <div class="space-y-3">
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </p>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><i class="bi bi-person text-red-600 mr-2"></i>Chair:
                            <?php echo htmlspecialchars($committee['chair']); ?>
                        </p>
                        <p><i class="bi bi-people text-red-600 mr-2"></i><?php echo $committee['member_count'] ?? 0; ?>
                            Members</p>
                    </div>
                    <a href="../committee-profiles/view.php?id=<?php echo $meeting['committee_id']; ?>"
                        class="inline-flex items-center text-red-600 hover:text-red-700 dark:text-red-400 text-sm font-semibold transition mt-2">
                        View Committee Details <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-lightning text-red-600 mr-2"></i>Quick Actions
                </h3>
                <div class="space-y-2">
                    <button onclick="window.print()"
                        class="w-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white px-4 py-2 rounded-lg transition flex items-center justify-center">
                        <i class="bi bi-printer mr-2"></i>Print Details
                    </button>

                    <div class="relative">
                        <button onclick="toggleCalendarDropdown()" id="calendarButton"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-between">
                            <span><i class="bi bi-calendar-plus mr-2"></i>Add to Calendar</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div id="calendarDropdown"
                            class="hidden absolute bottom-full mb-2 w-full bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 z-10">
                            <a href="#" onclick="addToGoogleCalendar(); return false;"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-t-lg transition">
                                <i class="bi bi-google text-red-500 mr-2"></i>Google Calendar
                            </a>
                            <a href="#" onclick="addToOutlook(); return false;"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <i class="bi bi-microsoft text-blue-500 mr-2"></i>Outlook
                            </a>
                            <a href="#" onclick="addToCalendar(); return false;"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-b-lg transition">
                                <i class="bi bi-apple text-gray-700 dark:text-gray-300 mr-2"></i>Apple Calendar (.ics)
                            </a>
                        </div>
                    </div>

                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this meeting?');"
                        class="mt-4">
                        <button type="submit" name="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center">
                            <i class="bi bi-trash mr-2"></i>Delete Meeting
                        </button>
                    </form>
                </div>
            </div>

            <!-- Meeting Meta -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center">
                        <i class="bi bi-calendar-plus text-gray-400 mr-2"></i>
                        <span>Created: <?php echo date('M j, Y', strtotime($meeting['created_date'])); ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-person text-gray-400 mr-2"></i>
                        <span>By: <?php echo htmlspecialchars($meeting['created_by']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Generate calendar URLs in PHP to avoid JSON issues
$calendarTitle = urlencode($meeting['title']);
$calendarDetails = urlencode(strip_tags($meeting['description'] ?? ''));
$calendarLocation = urlencode($meeting['venue']);

// Format dates
$startTime = $meeting['time_start'];
$endTime = $meeting['time_end'] ?? '';

// Ensure time format
if (strlen($startTime) == 5)
    $startTime .= ':00';
if (!empty($endTime) && strlen($endTime) == 5)
    $endTime .= ':00';

$startDT = strtotime($meeting['date'] . ' ' . $startTime);
$endDT = !empty($endTime) ? strtotime($meeting['date'] . ' ' . $endTime) : ($startDT + 7200);

$startFormatted = date('Ymd\THis', $startDT);
$endFormatted = date('Ymd\THis', $endDT);

// Google Calendar URL
$googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
    "&text=" . $calendarTitle .
    "&dates=" . $startFormatted . "/" . $endFormatted .
    "&details=" . $calendarDetails .
    "&location=" . $calendarLocation;

// Outlook URL
$outlookUrl = "https://outlook.live.com/calendar/0/deeplink/compose?" .
    "subject=" . $calendarTitle .
    "&startdt=" . $startFormatted .
    "&enddt=" . $endFormatted .
    "&body=" . $calendarDetails .
    "&location=" . $calendarLocation;

// ICS file content
$icsContent = "BEGIN:VCALENDAR\r\n" .
    "VERSION:2.0\r\n" .
    "PRODID:-//Committee Meeting//EN\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:meeting-" . $id . "@legislative-cms\r\n" .
    "DTSTAMP:" . date('Ymd\THis') . "\r\n" .
    "DTSTART:" . $startFormatted . "\r\n" .
    "DTEND:" . $endFormatted . "\r\n" .
    "SUMMARY:" . str_replace(["\r", "\n", ",", ";"], ["", "\\n", "\\,", "\\;"], $meeting['title']) . "\r\n" .
    "DESCRIPTION:" . str_replace(["\r", "\n", ",", ";"], ["", "\\n", "\\,", "\\;"], $meeting['description'] ?? '') . "\r\n" .
    "LOCATION:" . str_replace(["\r", "\n", ",", ";"], ["", "\\n", "\\,", "\\;"], $meeting['venue']) . "\r\n" .
    "STATUS:CONFIRMED\r\n" .
    "END:VEVENT\r\n" .
    "END:VCALENDAR";

$icsDataUrl = 'data:text/calendar;charset=utf-8,' . rawurlencode($icsContent);
?>

<script>
    function toggleCalendarDropdown() {
        const dropdown = document.getElementById('calendarDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const button = document.getElementById('calendarButton');
        const dropdown = document.getElementById('calendarDropdown');
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    function addToGoogleCalendar() {
        window.open(<?php echo json_encode($googleCalendarUrl); ?>, '_blank');
        toggleCalendarDropdown();
    }

    function addToOutlook() {
        window.open(<?php echo json_encode($outlookUrl); ?>, '_blank');
        toggleCalendarDropdown();
    }

    function addToCalendar() {
        const link = document.createElement('a');
        link.href = <?php echo json_encode($icsDataUrl); ?>;
        link.download = 'meeting-<?php echo $id; ?>.ics';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toggleCalendarDropdown();
    }
</script>

<?php include '../../includes/footer.php'; ?>