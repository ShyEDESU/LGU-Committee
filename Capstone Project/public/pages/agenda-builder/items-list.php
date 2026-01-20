<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'All Agenda Items';
include '../../includes/header.php';

// Get all meetings and their agenda items
$meetings = getAllMeetings();
$allItems = [];

foreach ($meetings as $meeting) {
    $items = getAgendaByMeeting($meeting['id']);
    foreach ($items as $item) {
        $allItems[] = array_merge($item, [
            'meeting_title' => $meeting['title'],
            'meeting_id' => $meeting['id'],
            'meeting_date' => $meeting['date'],
            'committee_name' => $meeting['committee_name'],
            'committee_id' => $meeting['committee_id']
        ]);
    }
}

// Filters
$search = $_GET['search'] ?? '';
$committeeFilter = $_GET['committee'] ?? '';

if ($search || $committeeFilter) {
    $allItems = array_filter($allItems, function ($item) use ($search, $committeeFilter) {
        $matchesSearch = empty($search) ||
            stripos($item['title'], $search) !== false ||
            stripos($item['meeting_title'], $search) !== false;
        $matchesCommittee = empty($committeeFilter) || $item['committee_id'] == $committeeFilter;
        return $matchesSearch && $matchesCommittee;
    });
}

$committees = getAllCommittees();
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Agenda Items</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">View all agenda items across all meetings</p>
        </div>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Agendas
        </a>
        <a href="items-list.php"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
            <i class="bi bi-card-list"></i> All Items
        </a>
        <a href="templates.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Templates
        </a>
        <a href="archive.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-archive"></i> Archive
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search items or meetings..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        </div>
        <select name="committee"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Committees</option>
            <?php foreach ($committees as $committee): ?>
                <option value="<?php echo $committee['id']; ?>" <?php echo $committeeFilter == $committee['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($committee['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="md:col-span-3 flex justify-end gap-2">
            <a href="items-list.php"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="bi bi-funnel mr-2"></i> Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($allItems); ?>
                </p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-list-check text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Duration</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo array_sum(array_column($allItems, 'duration')); ?> <span class="text-lg">min</span>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-clock text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Meetings</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_unique(array_column($allItems, 'meeting_id'))); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-event text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($allItems)): ?>
        <div class="p-12 text-center">
            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Items Found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                <?php if ($search || $committeeFilter): ?>
                    Try adjusting your search or filters
                <?php else: ?>
                    No agenda items have been created yet
                <?php endif; ?>
            </p>
            <?php if ($search || $committeeFilter): ?>
                <a href="items-list.php"
                    class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Clear Filters
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border-b-2 border-red-600">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Item</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Meeting</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Committee</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Duration</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($allItems as $item): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </p>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['meeting_title']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    <?php echo htmlspecialchars($item['committee_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo !empty($item['meeting_date']) ? date('M j, Y', strtotime($item['meeting_date'])) : 'Not Set'; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo ($item['duration'] ?? 0); ?> min
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="view.php?id=<?php echo $item['meeting_id']; ?>"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold"
                                        title="View Agenda">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="items.php?meeting_id=<?php echo $item['meeting_id']; ?>"
                                        class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-semibold"
                                        title="Manage Items">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>