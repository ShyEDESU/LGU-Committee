<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_item'])) {
    $itemId = $_POST['item_id'];
    $assignedTo = $_POST['assigned_to'];

    updateActionItem($itemId, ['assigned_to' => $assignedTo]);
    header('Location: assign.php?assigned=1');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Assign Action Items';
include '../../includes/header.php';

// Get all action items
$actionItems = getAllActionItems();

require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
$users = getAllUsers();

// Map users for easy lookup
$userMap = [];
foreach ($users as $user) {
    $userMap[$user['user_id']] = $user['first_name'] . ' ' . $user['last_name'];
}

// Group items by assignment status
$unassignedItems = array_filter($actionItems, function ($item) {
    return empty($item['assigned_to']) || (int) $item['assigned_to'] === 0;
});

$assignedItems = array_filter($actionItems, function ($item) {
    return !empty($item['assigned_to']) && (int) $item['assigned_to'] !== 0;
});

// Group assigned items by person
$itemsByAssignee = [];
foreach ($assignedItems as $item) {
    $assigneeId = (int) $item['assigned_to'];
    $assigneeName = $userMap[$assigneeId] ?? 'Unknown User';

    if (!isset($itemsByAssignee[$assigneeName])) {
        $itemsByAssignee[$assigneeName] = [];
    }
    $itemsByAssignee[$assigneeName][] = $item;
}
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Assign Action Items</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Assign tasks to committee members</p>
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
        <a href="assign.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
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
        <a href="history.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Clarification Box -->
<div class="bg-red-50 dark:bg-blue-900/20 border-l-4 border-red-500 p-4 mb-6">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-red-600 dark:text-blue-400 text-xl mt-1 mr-3"></i>
        <div>
            <h3 class="font-semibold text-red-900 dark:text-blue-300 mb-1">About the Assign Tab</h3>
            <p class="text-red-800 dark:text-blue-300 text-sm">
                This tab helps you <strong>manage WHO does WHAT</strong>. View unassigned items that need someone
                assigned,
                see who is working on what, and reassign tasks as needed. To edit task details (title, description, due
                date),
                use the Edit button on each action item.
            </p>
        </div>
    </div>
</div>

<?php if (isset($_GET['assigned'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-green-800 dark:text-green-300">
            <i class="bi bi-check-circle mr-2"></i>Action item assigned successfully!
        </p>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($actionItems); ?></p>
            </div>
            <div class="bg-red-100 dark:bg-blue-900/30 p-3 rounded-lg">
                <i class="bi bi-list-task text-2xl text-red-600 dark:text-blue-400"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Assigned</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                    <?php echo count($assignedItems); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg">
                <i class="bi bi-person-check text-2xl text-green-600 dark:text-green-400"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Unassigned</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo count($unassignedItems); ?>
                </p>
            </div>
            <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-lg">
                <i class="bi bi-person-x text-2xl text-red-600 dark:text-red-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Unassigned Items -->
<?php if (!empty($unassignedItems)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-exclamation-triangle text-red-600 mr-2"></i>Unassigned Items
            (<?php echo count($unassignedItems); ?>)
        </h2>
        <div class="space-y-3">
            <?php foreach ($unassignedItems as $item): ?>
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            <div class="flex items-center gap-3 mt-2">
                                <span
                                    class="text-xs px-2 py-1 rounded-full <?php echo ($item['priority'] ?? '') === 'High' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                        (($item['priority'] ?? '') === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'); ?>">
                                    <?php echo htmlspecialchars($item['priority'] ?? 'Medium'); ?>
                                </span>
                                <?php if (!empty($item['due_date'])): ?>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <i class="bi bi-calendar-x mr-1"></i>Due:
                                        <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button
                            onclick="showAssignModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>')"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">
                            <i class="bi bi-person-plus mr-1"></i>Assign
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Assigned Items by Person -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
        <i class="bi bi-people mr-2"></i>Assigned Items by Person
    </h2>

    <?php if (empty($itemsByAssignee)): ?>
        <div class="text-center py-12">
            <i class="bi bi-person-x text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Assigned Items</h3>
            <p class="text-gray-600 dark:text-gray-400">All action items are currently unassigned</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($itemsByAssignee as $assignee => $items): ?>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mr-3">
                                <i class="bi bi-person-fill text-red-600 dark:text-red-400"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($assignee); ?>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo count($items); ?> item(s)
                                    assigned</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <?php foreach ($items as $item): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span
                                                class="text-xs px-2 py-1 rounded-full <?php echo ($item['status'] ?? '') === 'Done' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                                    (($item['status'] ?? '') === 'In Progress' ? 'bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300'); ?>">
                                                <?php echo htmlspecialchars($item['status'] ?? 'To Do'); ?>
                                            </span>
                                            <?php if (($item['progress'] ?? 0) > 0): ?>
                                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                                    <?php echo ($item['progress'] ?? 0); ?>% complete
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="view.php?id=<?php echo $item['id']; ?>"
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded text-sm transition">
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Assignment Modal -->
<div id="assignModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md mx-4 w-full">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Assign Action Item</h3>
        <p id="modalItemTitle" class="text-gray-600 dark:text-gray-400 mb-4"></p>
        <form method="POST">
            <input type="hidden" name="item_id" id="modalItemId">
            <input type="hidden" name="assign_item" value="1">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign To</label>
                <select name="assigned_to" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    <option value="">Select a person...</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            (<?php echo htmlspecialchars($user['role_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAssignModal()"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAssignModal(id, title) {
        document.getElementById('assignModal').classList.remove('hidden');
        document.getElementById('modalItemId').value = id;
        document.getElementById('modalItemTitle').textContent = title;
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }
</script>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span class="font-medium"><?php echo count($assignedItems) + count($unassignedItems); ?></span> of
        <span class="font-medium"><?php echo count($assignedItems) + count($unassignedItems); ?></span> item(s) to
        assign
    </div>
    <div class="text-sm text-gray-500 italic">
        Filter: All Items
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>