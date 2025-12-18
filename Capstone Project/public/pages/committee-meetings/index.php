<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Meetings';
include '../../includes/header.php';

// Hardcoded meetings data
$meetings = [
    ['id' => 1, 'committee' => 'Finance', 'title' => '2025 Budget Review', 'date' => '2024-12-15', 'time' => '14:00', 'location' => 'Session Hall', 'status' => 'Scheduled', 'attendees' => 7],
    ['id' => 2, 'committee' => 'Health', 'title' => 'Healthcare Facilities Inspection Report', 'date' => '2024-12-14', 'time' => '10:00', 'location' => 'Conference Room A', 'status' => 'Scheduled', 'attendees' => 5],
    ['id' => 3, 'committee' => 'Education', 'title' => 'School Infrastructure Assessment', 'date' => '2024-12-13', 'time' => '09:00', 'location' => 'Conference Room B', 'status' => 'Held', 'attendees' => 6],
    ['id' => 4, 'committee' => 'Infrastructure', 'title' => 'Road Maintenance Program Review', 'date' => '2024-12-12', 'time' => '15:00', 'location' => 'Session Hall', 'status' => 'Held', 'attendees' => 8],
    ['id' => 5, 'committee' => 'Public Safety', 'title' => 'Disaster Preparedness Planning', 'date' => '2024-12-11', 'time' => '13:00', 'location' => 'Conference Room A', 'status' => 'Held', 'attendees' => 6],
    ['id' => 6, 'committee' => 'Finance', 'title' => 'Revenue Enhancement Measures', 'date' => '2024-12-18', 'time' => '14:00', 'location' => 'Session Hall', 'status' => 'Scheduled', 'attendees' => 7],
    ['id' => 7, 'committee' => 'Health', 'title' => 'Public Health Emergency Response', 'date' => '2024-12-20', 'time' => '10:00', 'location' => 'Conference Room B', 'status' => 'Scheduled', 'attendees' => 5],
    ['id' => 8, 'committee' => 'Education', 'title' => 'Scholarship Program Evaluation', 'date' => '2024-12-22', 'time' => '09:00', 'location' => 'Conference Room A', 'status' => 'Scheduled', 'attendees' => 6],
];

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

if ($search || $statusFilter) {
    $meetings = array_filter($meetings, function($meeting) use ($search, $statusFilter) {
        $matchesSearch = empty($search) || stripos($meeting['title'], $search) !== false || stripos($meeting['committee'], $search) !== false;
        $matchesStatus = empty($statusFilter) || $meeting['status'] === $statusFilter;
        return $matchesSearch && $matchesStatus;
    });
}
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Meetings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Schedule and manage committee meetings</p>
        </div>
        <a href="schedule.php" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
            <i class="bi bi-plus-lg"></i> Schedule Meeting
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
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

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search meetings..." 
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
        </div>
        <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            <option value="">All Status</option>
            <option value="Scheduled" <?php echo $statusFilter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="Held" <?php echo $statusFilter === 'Held' ? 'selected' : ''; ?>>Held</option>
            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button type="submit" class="md:col-span-3 px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
            Apply Filters
        </button>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Total Meetings</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($meetings); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Scheduled</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($meetings, fn($m) => $m['status'] === 'Scheduled')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Held</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($meetings, fn($m) => $m['status'] === 'Held')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">This Week</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">4</p>
    </div>
</div>

<!-- Meetings Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Meeting</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Committee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($meetings as $meeting): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($meeting['title']); ?></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo $meeting['attendees']; ?> attendees</p>
                </td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $meeting['committee']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white">
                    <?php echo date('M j, Y', strtotime($meeting['date'])); ?><br>
                    <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo date('g:i A', strtotime($meeting['time'])); ?></span>
                </td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $meeting['location']; ?></td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $meeting['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-800' : 
                                   ($meeting['status'] === 'Held' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                        <?php echo $meeting['status']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="view.php?id=<?php echo $meeting['id']; ?>" class="text-cms-red hover:text-cms-dark">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
