<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $itemId = $_POST['item_id'];
    if (deleteActionItem($itemId)) {
        header('Location: index.php?deleted=1');
        exit();
    }
}

// Handle status updates via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $itemId = $_POST['item_id'];
    $newStatus = $_POST['new_status'];
    updateActionItem($itemId, ['status' => $newStatus]);
    header('Location: index.php?updated=1');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Items';
include '../../includes/header.php';

// Get all action items from session
$actionItems = getAllActionItems();

// Filter action items for non-admins
$userRole = $_SESSION['user_role'] ?? 'User';
$userId = $_SESSION['user_id'];
if ($userRole !== 'Admin' && $userRole !== 'Super Admin') {
    require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
    $actionItems = array_filter($actionItems, function ($item) use ($userId) {
        // Assigned to check
        if (($item['assigned_to'] ?? '') == $userId)
            return true;

        // Committee check
        if (isset($item['committee_id'])) {
            $committeeId = $item['committee_id'];
            $committee = getCommitteeById($committeeId);
            if ($committee) {
                // Leadership check
                $isLeadership = (
                    $userId == ($committee['chair_id'] ?? 0) ||
                    $userId == ($committee['vice_chair_id'] ?? 0) ||
                    $userId == ($committee['secretary_id'] ?? 0)
                );
                if ($isLeadership)
                    return true;

                // Membership check
                if (isCommitteeMember($committeeId, $userId))
                    return true;
            }
        }

        return false;
    });
}


// Apply filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$assigneeFilter = $_GET['assignee'] ?? '';

if ($search || $statusFilter || $priorityFilter || $assigneeFilter) {
    $actionItems = array_filter($actionItems, function ($item) use ($search, $statusFilter, $priorityFilter, $assigneeFilter) {
        $matchesSearch = empty($search) ||
            stripos($item['title'], $search) !== false ||
            stripos($item['description'] ?? '', $search) !== false;
        $matchesStatus = empty($statusFilter) || $item['status'] === $statusFilter;
        $matchesPriority = empty($priorityFilter) || $item['priority'] === $priorityFilter;
        $matchesAssignee = empty($assigneeFilter) || stripos($item['assigned_to'], $assigneeFilter) !== false;

        return $matchesSearch && $matchesStatus && $matchesPriority && $matchesAssignee;
    });
}

// Calculate statistics
$stats = getActionItemStatistics();
$todoItems = getActionItemsByStatus('To Do');
$inProgressItems = getActionItemsByStatus('In Progress');
$doneItems = getActionItemsByStatus('Done');
?>

<?php if (isset($_GET['created'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-green-800 dark:text-green-300">
            <i class="bi bi-check-circle mr-2"></i>Action item created successfully!
        </p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="bg-red-50 dark:bg-blue-900/20 border-l-4 border-red-500 p-4 mb-6">
        <p class="text-red-800 dark:text-blue-300">
            <i class="bi bi-check-circle mr-2"></i>Action item updated successfully!
        </p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
        <p class="text-red-800 dark:text-red-300">
            <i class="bi bi-check-circle mr-2"></i>Action item deleted successfully!
        </p>
    </div>
<?php endif; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Action Items</h1>
            <p class="text-gray-600 mt-1">Track and manage committee action items</p>
        </div>
        <?php if (canCreate($userId, 'action_items')): ?>
            <a href="create.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"><i
                    class="bi bi-plus-lg"></i> New Action Item</a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold"><i
                class="bi bi-kanban"></i> Kanban Board</a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-person-plus"></i> Assign</a>
        <a href="progress.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-graph-up"></i> Progress</a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-calendar-x"></i> Deadlines</a>
        <a href="reports.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-file-text"></i> Reports</a>
        <a href="history.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-clock-history"></i> History</a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search action items..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Status</option>
                <option value="To Do" <?php echo $statusFilter === 'To Do' ? 'selected' : ''; ?>>To Do</option>
                <option value="In Progress" <?php echo $statusFilter === 'In Progress' ? 'selected' : ''; ?>>In Progress
                </option>
                <option value="Done" <?php echo $statusFilter === 'Done' ? 'selected' : ''; ?>>Done</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
            <select name="priority"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Priority</option>
                <option value="High" <?php echo $priorityFilter === 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo $priorityFilter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo $priorityFilter === 'Low' ? 'selected' : ''; ?>>Low</option>
            </select>
        </div>
        <div class="flex items-end space-x-2">
            <a href="index.php"
                class="flex-1 px-4 py-2 text-center text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                Clear
            </a>
            <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                Apply
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Items</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($actionItems); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">To Do</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($todoItems); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">In Progress</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($inProgressItems); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Done</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($doneItems); ?></p>
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- To Do Column -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
            <span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>
            To Do (<?php echo count($todoItems); ?>)
        </h3>
        <div class="space-y-3">
            <?php foreach ($todoItems as $item): ?>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border-l-4 border-gray-500">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <i class="bi bi-person"></i>
                        <?php echo htmlspecialchars($item['assigned_to_name'] ?? 'Unassigned'); ?>
                    </p>
                    <?php if (!empty($item['due_date'])): ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <i class="bi bi-calendar"></i> <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between mt-3">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full 
                        <?php
                        $priority = strtolower($item['priority'] ?? 'normal');
                        echo ($priority === 'high' || $priority === 'urgent') ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                            ($priority === 'medium' || $priority === 'normal' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'); ?>">
                            <?php echo htmlspecialchars(ucfirst($item['priority'])); ?>
                        </span>
                        <div class="flex space-x-1">
                            <a href="view.php?id=<?php echo $item['id']; ?>"
                                class="p-1 text-red-600 dark:text-blue-400 hover:bg-red-100 dark:hover:bg-blue-900/20 rounded"
                                title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (canEdit($userId, 'action_items', $item['id'])): ?>
                                <a href="edit.php?id=<?php echo $item['id']; ?>"
                                    class="p-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (canDelete($userId, 'action_items')): ?>
                                <form method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this action item?');">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="delete_item" value="1"
                                        class="p-1 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
            In Progress (<?php echo count($inProgressItems); ?>)
        </h3>
        <div class="space-y-3">
            <?php foreach ($inProgressItems as $item): ?>
                <div class="bg-red-50 dark:bg-blue-900/20 p-4 rounded-lg border-l-4 border-red-500">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <i class="bi bi-person"></i>
                        <?php echo htmlspecialchars($item['assigned_to_name'] ?? 'Unassigned'); ?>
                    </p>
                    <?php if (!empty($item['due_date'])): ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <i class="bi bi-calendar"></i> <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                        </p>
                    <?php endif; ?>
                    <div class="mb-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full"
                                style="width: <?php echo ($item['progress'] ?? 0); ?>%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?php echo ($item['progress'] ?? 0); ?>%
                            complete</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full 
                        <?php
                        $priority = strtolower($item['priority'] ?? 'normal');
                        echo ($priority === 'high' || $priority === 'urgent') ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                            ($priority === 'medium' || $priority === 'normal' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'); ?>">
                            <?php echo htmlspecialchars(ucfirst($item['priority'])); ?>
                        </span>
                        <div class="flex space-x-1">
                            <a href="view.php?id=<?php echo $item['id']; ?>"
                                class="p-1 text-red-600 dark:text-blue-400 hover:bg-red-100 dark:hover:bg-blue-900/20 rounded"
                                title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (canEdit($userId, 'action_items', $item['id'])): ?>
                            <a href="edit.php?id=<?php echo $item['id']; ?>"
                                class="p-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (canDelete($userId, 'action_items')): ?>
                            <form method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this action item?');">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" value="1"
                                    class="p-1 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Done Column -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
            Done (<?php echo count($doneItems); ?>)
        </h3>
        <div class="space-y-3">
            <?php foreach ($doneItems as $item): ?>
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border-l-4 border-green-500">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <i class="bi bi-person"></i>
                        <?php echo htmlspecialchars($item['assigned_to_name'] ?? 'Unassigned'); ?>
                    </p>
                    <p class="text-sm text-green-600 dark:text-green-400 mb-2">
                        <i class="bi bi-check-circle"></i> Completed
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <?php if (!empty($item['completed_at'])): ?>
                                <?php echo date('M j, Y', strtotime($item['completed_at'])); ?>
                            <?php endif; ?>
                        </span>
                        <div class="flex space-x-1">
                            <a href="view.php?id=<?php echo $item['id']; ?>"
                                class="p-1 text-red-600 dark:text-blue-400 hover:bg-red-100 dark:hover:bg-blue-900/20 rounded"
                                title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (canDelete($userId, 'action_items')): ?>
                            <form method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this action item?');">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" value="1"
                                    class="p-1 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Kanban Board: <span
            class="font-medium"><?php echo count($todoItems) + count($inProgressItems) + count($doneItems); ?></span>
        total action items
    </div>
    <div class="text-sm text-gray-500 italic">
        Status: <?php echo count($todoItems); ?> To Do, <?php echo count($inProgressItems); ?> In Progress,
        <?php echo count($doneItems); ?> Done
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>