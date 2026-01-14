<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get action item ID if specified
$itemId = $_GET['id'] ?? null;
$item = $itemId ? getActionItemById($itemId) : null;

// Get all action items for general history
$allItems = getAllActionItems();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $item ? 'Action Item History' : 'Action Items History';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                <?php echo $item ? 'Action Item History' : 'Action Items History'; ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo $item ? htmlspecialchars($item['title']) : 'Track all action item activities'; ?>
            </p>
        </div>
        <a href="<?php echo $item ? 'view.php?id=' . $item['id'] : 'index.php'; ?>"
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
        <a href="deadlines.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
        <a href="reports.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-text"></i> Reports
        </a>
        <a href="history.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Statistics Dashboard -->>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count($allItems); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
            <?php echo count(getActionItemsByStatus('Done')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
            <?php echo count(getActionItemsByStatus('In Progress')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Overdue</p>
        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">
            <?php echo count(getOverdueActionItems()); ?>
        </p>
    </div>
</div>

<!-- Activity Timeline -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
        <i class="bi bi-clock-history mr-2"></i>Activity Timeline
    </h2>

    <div class="space-y-6">
        <?php
        $displayItems = $item ? [$item] : array_slice($allItems, 0, 20);
        foreach ($displayItems as $activityItem):
            $statusColor = ($activityItem['status'] ?? '') === 'Done' ? 'green' :
                (($activityItem['status'] ?? '') === 'In Progress' ? 'blue' : 'gray');
            ?>
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div
                        class="w-10 h-10 bg-<?php echo $statusColor; ?>-100 dark:bg-<?php echo $statusColor; ?>-900/30 rounded-full flex items-center justify-center">
                        <i
                            class="bi bi-<?php echo ($activityItem['status'] ?? '') === 'Done' ? 'check-circle' : 'circle'; ?> text-<?php echo $statusColor; ?>-600 dark:text-<?php echo $statusColor; ?>-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($activityItem['title']); ?>
                        </p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo !empty($activityItem['created_date']) ? date('M j, Y', strtotime($activityItem['created_date'])) : 'Recently'; ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                        <span>
                            <i class="bi bi-person"></i>
                            <?php echo htmlspecialchars($activityItem['assigned_to']); ?>
                        </span>
                        <span
                            class="px-2 py-1 text-xs rounded-full bg-<?php echo $statusColor; ?>-100 dark:bg-<?php echo $statusColor; ?>-900/30 text-<?php echo $statusColor; ?>-800 dark:text-<?php echo $statusColor; ?>-300">
                            <?php echo htmlspecialchars($activityItem['status'] ?? 'To Do'); ?>
                        </span>
                        <?php if (($activityItem['progress'] ?? 0) > 0): ?>
                            <span>
                                <i class="bi bi-graph-up"></i>
                                <?php echo ($activityItem['progress'] ?? 0); ?>% complete
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($activityItem['completed_date'])): ?>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            <i class="bi bi-check-circle"></i>
                            Completed on
                            <?php echo date('M j, Y g:i A', strtotime($activityItem['completed_date'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex-shrink-0">
                    <a href="view.php?id=<?php echo $activityItem['id']; ?>"
                        class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                        View
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($displayItems)): ?>
            <div class="text-center py-12">
                <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Activity Yet</h3>
                <p class="text-gray-600 dark:text-gray-400">Action item activities will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>