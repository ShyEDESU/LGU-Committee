<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get deadline filters
$filter = $_GET['filter'] ?? 'all';
$allItems = getAllActionItems();
$overdueItems = getOverdueActionItems();
$upcoming7 = getUpcomingActionItems(7);
$upcoming14 = getUpcomingActionItems(14);
$upcoming30 = getUpcomingActionItems(30);

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Items Deadlines';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Deadlines</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track and manage action item deadlines</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <a href="assign.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assign
        </a>
        <a href="progress.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Progress
        </a>
        <a href="deadlines.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
        <a href="reports.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-text"></i> Reports
        </a>
        <a href="history.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 p-6">
        <p class="text-sm text-red-600 dark:text-red-400">Overdue</p>
        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">
            <?php echo count($overdueItems); ?>
        </p>
    </div>
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-6">
        <p class="text-sm text-yellow-600 dark:text-yellow-400">Next 7 Days</p>
        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
            <?php echo count($upcoming7); ?>
        </p>
    </div>
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6">
        <p class="text-sm text-blue-600 dark:text-blue-400">Next 14 Days</p>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
            <?php echo count($upcoming14); ?>
        </p>
    </div>
    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 p-6">
        <p class="text-sm text-green-600 dark:text-green-400">Next 30 Days</p>
        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
            <?php echo count($upcoming30); ?>
        </p>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="?filter=overdue"
            class="px-4 py-2 <?php echo $filter === 'overdue' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> rounded-lg transition">
            <i class="bi bi-exclamation-triangle"></i> Overdue
        </a>
        <a href="?filter=7days"
            class="px-4 py-2 <?php echo $filter === '7days' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> rounded-lg transition">
            <i class="bi bi-calendar-week"></i> Next 7 Days
        </a>
        <a href="?filter=14days"
            class="px-4 py-2 <?php echo $filter === '14days' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> rounded-lg transition">
            <i class="bi bi-calendar2-week"></i> Next 14 Days
        </a>
        <a href="?filter=30days"
            class="px-4 py-2 <?php echo $filter === '30days' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> rounded-lg transition">
            <i class="bi bi-calendar-month"></i> Next 30 Days
        </a>
        <a href="?filter=all"
            class="px-4 py-2 <?php echo $filter === 'all' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> rounded-lg transition">
            <i class="bi bi-list"></i> All Items
        </a>
    </div>
</div>

<!-- Items List -->
<?php
$displayItems = $allItems;
switch ($filter) {
    case 'overdue':
        $displayItems = $overdueItems;
        break;
    case '7days':
        $displayItems = $upcoming7;
        break;
    case '14days':
        $displayItems = $upcoming14;
        break;
    case '30days':
        $displayItems = $upcoming30;
        break;
    default:
        $displayItems = $allItems;
        break;
}

// Sort by due date
usort($displayItems, function ($a, $b) {
    $dateA = $a['due_date'] ?? '9999-12-31';
    $dateB = $b['due_date'] ?? '9999-12-31';
    return strcmp($dateA, $dateB);
});
?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Task
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Assigned To
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Due Date
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Priority
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($displayItems as $item):
                    $isOverdue = !empty($item['due_date']) && $item['due_date'] < date('Y-m-d') && ($item['status'] ?? '') !== 'Done';
                    ?>
                    <tr
                        class="<?php echo $isOverdue ? 'bg-red-50 dark:bg-red-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if ($isOverdue): ?>
                                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 mr-2"></i>
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </div>
                                    <?php if (($item['progress'] ?? 0) > 0): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo ($item['progress'] ?? 0); ?>% complete
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($item['assigned_to_name'] ?? 'Unassigned'); ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if (!empty($item['due_date'])): ?>
                                <span
                                    class="<?php echo $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-white'; ?>">
                                    <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 dark:text-gray-500">No deadline</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php echo ($item['status'] ?? '') === 'Done' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    (($item['status'] ?? '') === 'In Progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'); ?>">
                                <?php echo htmlspecialchars($item['status'] ?? 'To Do'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php echo ($item['priority'] ?? '') === 'High' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                    (($item['priority'] ?? '') === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'); ?>">
                                <?php echo htmlspecialchars($item['priority'] ?? 'Medium'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="view.php?id=<?php echo $item['id']; ?>"
                                class="text-blue-600 dark:text-blue-400 hover:underline">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($displayItems)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Items Found</h3>
                            <p class="text-gray-600 dark:text-gray-400">No action items match the selected filter</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>