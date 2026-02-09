<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_meeting'])) {
    $meetingId = $_POST['meeting_id'];
    if (deleteMeeting($meetingId)) {
        $_SESSION['success_message'] = 'Meeting deleted successfully';
        header('Location: index.php?deleted=1');
        exit();
    }
}

$pageTitle = 'Committee Meetings';
include '../../includes/header.php';

// Get meetings from database
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$filters = [];
if ($search)
    $filters['search'] = $search;
if ($statusFilter)
    $filters['status'] = $statusFilter;

$meetings = getAllMeetings($filters);

// Pagination logic
$itemsPerPage = 10;
$totalMeetings = count($meetings);
$totalPages = ceil($totalMeetings / $itemsPerPage);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;
$paginatedMeetings = array_slice($meetings, $offset, $itemsPerPage);
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
    <div class="flex items-center justify-between">
        <div class="flex gap-2">
            <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
                <i class="bi bi-list"></i> All Meetings
            </a>
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <i class="bi bi-info-circle mr-1"></i>
            Select a meeting to view attendance, minutes, and documents
        </div>
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
            <option value="Ongoing" <?php echo $statusFilter === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
            <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <div class="md:col-span-3 flex justify-end gap-2">
            <a href="index.php"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="bi bi-funnel mr-2"></i> Apply Filters
            </button>
        </div>
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
            <div class="bg-red-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-event text-red-600 dark:text-blue-400 text-2xl"></i>
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
                <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($meetings, fn($m) => $m['status'] === 'Completed')); ?>
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
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php
                    $thisWeek = count(array_filter($meetings, function ($m) {
                        $meetingDate = strtotime($m['date']);
                        $weekStart = strtotime('monday this week');
                        $weekEnd = strtotime('sunday this week 23:59:59');
                        return $meetingDate >= $weekStart && $meetingDate <= $weekEnd;
                    }));
                    echo $thisWeek;
                    ?>
                </p>
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
                        Agenda</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Status</th>
                    <th
                        class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($paginatedMeetings as $meeting): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($meeting['title']); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <?php echo htmlspecialchars($meeting['committee_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo date('M j, Y', strtotime($meeting['date'])); ?><br>
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"><?php echo date('g:i A', strtotime($meeting['time_start'])); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($meeting['venue']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $agendaItems = getAgendaByMeeting($meeting['id']);
                            $hasAgenda = !empty($agendaItems);
                            $agendaStatus = $meeting['agenda_status'] ?? 'None';

                            if ($hasAgenda):
                                if ($agendaStatus === 'Published'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                                        title="Agenda Published">
                                        <i class="bi bi-megaphone mr-1"></i> Published
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                        title="Has Agenda">
                                        <i class="bi bi-check-circle mr-1"></i> <?php echo $agendaStatus; ?>
                                    </span>
                                <?php endif;
                            else: ?>
                                <a href="../agenda-builder/create.php?committee=<?php echo $meeting['committee_id']; ?>&meeting_id=<?php echo $meeting['id']; ?>"
                                    class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50"
                                    title="Create Agenda">
                                    <i class="bi bi-exclamation-triangle mr-1"></i> No Agenda
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php echo $meeting['status'] === 'Scheduled' ? 'bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                ($meeting['status'] === 'Ongoing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                    ($meeting['status'] === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300')); ?>">
                                <?php echo $meeting['status'] ?: 'Unknown'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <a href="view.php?id=<?php echo $meeting['id']; ?>"
                                    class="text-red-600 dark:text-blue-400 hover:text-red-700 dark:hover:text-blue-300 font-semibold"
                                    title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $meeting['id']; ?>"
                                    class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-semibold"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this meeting? This action cannot be undone.');">
                                    <input type="hidden" name="meeting_id" value="<?php echo $meeting['id']; ?>">
                                    <button type="submit" name="delete_meeting" value="1"
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-semibold"
                                        title="Delete">
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

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to
                    <span class="font-medium"><?php echo min($offset + $itemsPerPage, $totalMeetings); ?></span> of
                    <span class="font-medium"><?php echo $totalMeetings; ?></span> results
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>