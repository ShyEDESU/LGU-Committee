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
    $meetings = array_filter($meetings, function ($meeting) use ($search, $statusFilter) {
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
        <a href="schedule.php"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-plus-lg"></i>
            <span>Schedule Meeting</span>
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-list"></i> All Meetings
        </a>
        <a href="schedule.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-plus"></i> Schedule
        </a>
        <a href="attendance.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-check"></i> Attendance
        </a>
        <a href="minutes.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Minutes
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search meetings..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-red-600">
        </div>
        <select name="status"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Status</option>
            <option value="Scheduled" <?php echo $statusFilter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="Held" <?php echo $statusFilter === 'Held' ? 'selected' : ''; ?>>Held</option>
            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button type="submit" class="md:col-span-3 btn-primary">
            <i class="bi bi-funnel mr-2"></i> Apply Filters
        </button>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-fade-in-up animation-delay-100 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Meetings</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($meetings); ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-event text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-fade-in-up animation-delay-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Scheduled</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($meetings, fn($m) => $m['status'] === 'Scheduled')); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-check text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-fade-in-up animation-delay-300 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Held</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($meetings, fn($m) => $m['status'] === 'Held')); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-check-circle text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-fade-in-up animation-delay-400 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">This Week</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">4</p>
            </div>
            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-week text-orange-600 dark:text-orange-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Meetings Table -->
<div
    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden animate-fade-in-up animation-delay-500">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border-b-2 border-red-600">
                <tr>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Meeting</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Committee</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Date & Time</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Location</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Status</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($meetings as $meeting): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($meeting['title']); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo $meeting['attendees']; ?>
                                attendees</p>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <?php echo $meeting['committee']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo date('M j, Y', strtotime($meeting['date'])); ?><br>
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"><?php echo date('g:i A', strtotime($meeting['time'])); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $meeting['location']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php echo $meeting['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                ($meeting['status'] === 'Held' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'); ?>">
                                <?php echo $meeting['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="view.php?id=<?php echo $meeting['id']; ?>"
                                class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-semibold">
                                <i class="bi bi-eye mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>