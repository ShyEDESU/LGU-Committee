<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 1;
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Details';
include '../../includes/header.php';

// Hardcoded meeting data
$meeting = [
    'id' => $meetingId,
    'title' => '2025 Budget Review',
    'committee' => 'Finance',
    'date' => '2024-12-15',
    'time' => '14:00',
    'location' => 'Session Hall',
    'status' => 'Scheduled',
    'description' => 'Review and discussion of the proposed 2025 annual budget',
    'attendees' => 7,
    'agenda_items' => [
        ['title' => 'Call to Order', 'duration' => '5 min'],
        ['title' => 'Budget Presentation', 'duration' => '30 min'],
        ['title' => 'Discussion and Q&A', 'duration' => '45 min'],
        ['title' => 'Voting', 'duration' => '15 min'],
    ]
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($meeting['title']); ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo $meeting['committee']; ?> Committee</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="editMeeting()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Meetings
        </a>
        <a href="schedule.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-plus"></i> Schedule
        </a>
        <a href="attendance.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-check"></i> Attendance
        </a>
        <a href="minutes.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Minutes
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Meeting Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Meeting Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <i class="bi bi-calendar"></i> <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Time</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <i class="bi bi-clock"></i> <?php echo date('g:i A', strtotime($meeting['time'])); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Location</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <i class="bi bi-geo-alt"></i> <?php echo $meeting['location']; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <?php echo $meeting['status']; ?>
                    </span>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                    <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($meeting['description']); ?></p>
                </div>
            </div>
        </div>

        <!-- Agenda -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Agenda</h2>
            <div class="space-y-3">
                <?php foreach ($meeting['agenda_items'] as $index => $item): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo $index + 1; ?>
                        </span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white"><?php echo $item['title']; ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo $item['duration']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <button onclick="markAttendance()" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-person-check"></i> Mark Attendance
                </button>
                <button onclick="uploadMinutes()" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Minutes
                </button>
                <button onclick="cancelMeeting()" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-x-circle"></i> Cancel Meeting
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Meeting Stats</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Expected Attendees</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $meeting['attendees']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Agenda Items</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo count($meeting['agenda_items']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editMeeting() { alert('Edit meeting'); }
    function markAttendance() { alert('Mark attendance'); }
    function uploadMinutes() { alert('Upload minutes'); }
    function cancelMeeting() { if(confirm('Cancel this meeting?')) alert('Meeting cancelled'); }
</script>

<?php include '../../includes/footer.php'; ?>

