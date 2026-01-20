<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get statistics
$stats = getActionItemStatistics();
$allItems = getAllActionItems();

// Group by assignee
$byAssignee = [];
foreach ($allItems as $item) {
    $assigneeName = $item['assigned_to_name'] ?? 'Unassigned';
    if (!isset($byAssignee[$assigneeName])) {
        $byAssignee[$assigneeName] = ['total' => 0, 'done' => 0, 'progress' => 0];
    }
    $byAssignee[$assigneeName]['total']++;
    if (($item['status'] ?? '') === 'Done') {
        $byAssignee[$assigneeName]['done']++;
    }
    $byAssignee[$assigneeName]['progress'] += ($item['progress'] ?? 0);
}

// Calculate averages
foreach ($byAssignee as $assigneeName => &$data) {
    $data['avg_progress'] = $data['total'] > 0 ? round($data['progress'] / $data['total'], 1) : 0;
    $data['completion_rate'] = $data['total'] > 0 ? round(($data['done'] / $data['total']) * 100, 1) : 0;
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Progress Tracking';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Progress Tracking</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor action item completion and progress</p>
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
        <a href="progress.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-graph-up"></i> Progress
        </a>
        <a href="deadlines.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
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

<!-- Overall Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Average Progress</p>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo $stats['avg_progress']; ?>%</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</p>
        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo $stats['completion_rate']; ?>%
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo $stats['by_status']['Done']; ?> / <?php echo $stats['total']; ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
            <?php echo $stats['by_status']['In Progress']; ?>
        </p>
    </div>
</div>

<!-- Progress by Status -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-pie-chart mr-2"></i>Progress by Status
        </h2>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">To Do</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_status']['To Do']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-gray-500 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_status']['To Do'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">In Progress</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_status']['In Progress']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-blue-600 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_status']['In Progress'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Done</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_status']['Done']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-green-600 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_status']['Done'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-bar-chart mr-2"></i>Progress by Priority
        </h2>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">High Priority</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_priority']['High']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-red-600 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_priority']['High'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Medium Priority</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_priority']['Medium']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-yellow-600 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_priority']['Medium'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Low Priority</span>
                    <span
                        class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['by_priority']['Low']; ?>
                        items</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-green-600 h-4 rounded-full"
                        style="width: <?php echo $stats['total'] > 0 ? ($stats['by_priority']['Low'] / $stats['total']) * 100 : 0; ?>%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress by Assignee -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            <i class="bi bi-people mr-2"></i>Progress by Assignee
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Assignee
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Total Items
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Completed
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Avg Progress
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Completion Rate
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($byAssignee as $assignee => $data): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($assignee); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <?php echo $data['total']; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <?php echo $data['done']; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: <?php echo $data['avg_progress']; ?>%"></div>
                                </div>
                                <span
                                    class="text-sm text-gray-900 dark:text-white"><?php echo $data['avg_progress']; ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php echo $data['completion_rate'] >= 75 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    ($data['completion_rate'] >= 50 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'); ?>">
                                <?php echo $data['completion_rate']; ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($byAssignee)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Data</h3>
                            <p class="text-gray-600 dark:text-gray-400">No action items to display</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>