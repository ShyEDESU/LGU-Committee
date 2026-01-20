<?php
// Suppress all errors to prevent output corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get all meetings for calendar
$meetings = getAllMeetings();

// Convert to JSON for calendar
$calendarEvents = [];
foreach ($meetings as $meeting) {
    $calendarEvents[] = [
        'id' => $meeting['id'],
        'title' => $meeting['title'],
        'start' => $meeting['date'] . 'T' . $meeting['time_start'],
        'end' => $meeting['date'] . 'T' . ($meeting['time_end'] ?? $meeting['time_start']),
        'committee' => $meeting['committee_name'],
        'location' => $meeting['venue'],
        'status' => $meeting['status'],
        'url' => 'view.php?id=' . $meeting['id'],
        'backgroundColor' => $meeting['status'] === 'Scheduled' ? '#dc2626' :
            ($meeting['status'] === 'Completed' ? '#16a34a' : '#6b7280'),
        'borderColor' => $meeting['status'] === 'Scheduled' ? '#dc2626' :
            ($meeting['status'] === 'Completed' ? '#16a34a' : '#6b7280')
    ];
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Calendar';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meeting Calendar</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">View all scheduled committee meetings</p>
            </div>
            <div class="flex gap-2">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    <i class="bi bi-list mr-2"></i>List View
                </a>
                <a href="schedule.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="bi bi-plus-circle mr-2"></i>Schedule Meeting
                </a>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex items-center gap-6">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</span>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-red-600 rounded"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Scheduled</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-green-600 rounded"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-gray-600 rounded"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Cancelled</span>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
                hour12: true
            },
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
                hour12: true
            },
            events: <?php echo json_encode($calendarEvents); ?>,
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            eventDidMount: function (info) {
                // Add tooltip
                info.el.title = info.event.extendedProps.committee + '\n' +
                    info.event.extendedProps.location + '\n' +
                    'Status: ' + info.event.extendedProps.status;
            },
            height: 'auto',
            themeSystem: 'standard',
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List'
            },
            views: {
                dayGridMonth: {
                    titleFormat: { year: 'numeric', month: 'long' }
                },
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' }
                }
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            allDaySlot: false,
            nowIndicator: true,
            navLinks: true,
            editable: false,
            dayMaxEvents: true,
            moreLinkText: 'more'
        });

        calendar.render();
    });
</script>

<?php include '../../includes/footer.php'; ?>