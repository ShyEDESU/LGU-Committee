<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get statistics
$stats = getActionItemStatistics();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Items Reports';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive action items analytics and insights</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.print()"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
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
        <a href="deadlines.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
        <a href="reports.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-file-text"></i> Reports
        </a>
        <a href="history.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Executive Summary -->
<div class="bg-gradient-to-r from-red-600 to-red-700 rounded-lg shadow-lg p-8 mb-6 text-white">
    <h2 class="text-2xl font-bold mb-4">Executive Summary</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <p class="text-red-100 text-sm mb-1">Total Action Items</p>
            <p class="text-4xl font-bold"><?php echo $stats['total']; ?></p>
        </div>
        <div>
            <p class="text-red-100 text-sm mb-1">Completion Rate</p>
            <p class="text-4xl font-bold"><?php echo $stats['completion_rate']; ?>%</p>
        </div>
        <div>
            <p class="text-red-100 text-sm mb-1">Average Progress</p>
            <p class="text-4xl font-bold"><?php echo $stats['avg_progress']; ?>%</p>
        </div>
        <div>
            <p class="text-red-100 text-sm mb-1">Overdue Items</p>
            <p class="text-4xl font-bold"><?php echo $stats['overdue']; ?></p>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Status Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-pie-chart-fill mr-2"></i>Status Distribution
        </h3>
        <div class="space-y-4">
            <?php foreach ($stats['by_status'] as $status => $count):
                $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                $statusLower = strtolower($status);
                $color = ($statusLower === 'done') ? 'green' :
                    (($statusLower === 'in progress' || $statusLower === 'pending' || $statusLower === 'to do') ? 'blue' : 'gray');
                ?>
                <div>
                    <div class="flex justify-between mb-2">
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($status); ?></span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $count; ?>
                            (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-<?php echo $color; ?>-600 h-3 rounded-full transition-all"
                            style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-bar-chart-fill mr-2"></i>Priority Distribution
        </h3>
        <div class="space-y-4">
            <?php foreach ($stats['by_priority'] as $priority => $count):
                $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                $priorityLower = strtolower($priority);
                $color = ($priorityLower === 'high' || $priorityLower === 'urgent') ? 'red' :
                    (($priorityLower === 'medium' || $priorityLower === 'normal') ? 'yellow' : 'green');
                ?>
                <div>
                    <div class="flex justify-between mb-2">
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($priority); ?>
                            Priority</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $count; ?>
                            (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-<?php echo $color; ?>-600 h-3 rounded-full transition-all"
                            style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Deadline Analysis -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-calendar-check mr-2"></i>Deadline Analysis
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-sm text-red-600 dark:text-red-400 mb-2">Overdue</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400"><?php echo $stats['overdue']; ?></p>
        </div>
        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-sm text-yellow-600 dark:text-yellow-400 mb-2">Next 7 Days</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo $stats['upcoming_7_days']; ?>
            </p>
        </div>
        <div class="text-center p-4 bg-red-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-sm text-red-600 dark:text-blue-400 mb-2">Next 14 Days</p>
            <p class="text-3xl font-bold text-red-600 dark:text-blue-400"><?php echo $stats['upcoming_14_days']; ?></p>
        </div>
        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-sm text-green-600 dark:text-green-400 mb-2">Next 30 Days</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400"><?php echo $stats['upcoming_30_days']; ?>
            </p>
        </div>
    </div>
</div>

<!-- Key Insights -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-lightbulb mr-2"></i>Key Insights
    </h3>
    <div class="space-y-4">
        <?php if ($stats['completion_rate'] >= 75): ?>
            <div class="flex items-start space-x-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-xl"></i>
                <div>
                    <p class="font-semibold text-green-900 dark:text-green-300">Excellent Completion Rate</p>
                    <p class="text-sm text-green-700 dark:text-green-400">Your team has achieved a
                        <?php echo $stats['completion_rate']; ?>% completion rate. Keep up the great work!
                    </p>
                </div>
            </div>
        <?php elseif ($stats['completion_rate'] >= 50): ?>
            <div class="flex items-start space-x-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <i class="bi bi-exclamation-triangle-fill text-yellow-600 dark:text-yellow-400 text-xl"></i>
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-300">Moderate Completion Rate</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400">Current completion rate is
                        <?php echo $stats['completion_rate']; ?>%. Consider reviewing pending items.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="flex items-start space-x-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <i class="bi bi-x-circle-fill text-red-600 dark:text-red-400 text-xl"></i>
                <div>
                    <p class="font-semibold text-red-900 dark:text-red-300">Low Completion Rate</p>
                    <p class="text-sm text-red-700 dark:text-red-400">Completion rate is
                        <?php echo $stats['completion_rate']; ?>%. Immediate attention required.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($stats['overdue'] > 0): ?>
            <div class="flex items-start space-x-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <i class="bi bi-calendar-x-fill text-red-600 dark:text-red-400 text-xl"></i>
                <div>
                    <p class="font-semibold text-red-900 dark:text-red-300">Overdue Items Detected</p>
                    <p class="text-sm text-red-700 dark:text-red-400">There are <?php echo $stats['overdue']; ?> overdue
                        action items requiring immediate attention.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($stats['avg_progress'] >= 70): ?>
            <div class="flex items-start space-x-3 p-4 bg-red-50 dark:bg-blue-900/20 rounded-lg">
                <i class="bi bi-graph-up-arrow text-red-600 dark:text-blue-400 text-xl"></i>
                <div>
                    <p class="font-semibold text-red-900 dark:text-blue-300">Strong Progress</p>
                    <p class="text-sm text-blue-700 dark:text-blue-400">Average progress across all items is
                        <?php echo $stats['avg_progress']; ?>%. Excellent momentum!
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Report Summary: <span class="font-medium"><?php echo $stats['total']; ?></span> action items analyzed
    </div>
    <div class="text-sm text-gray-500 italic">
        Completion Rate: <?php echo $stats['completion_rate']; ?>%
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>