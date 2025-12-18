<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$itemId = $_GET['id'] ?? 1;
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Item Details';
include '../../includes/header.php';

$item = [
    'id' => $itemId,
    'title' => 'Review 2025 Budget Proposal',
    'assigned_to' => 'Hon. Maria Santos',
    'deadline' => '2024-12-20',
    'priority' => 'High',
    'status' => 'In Progress',
    'progress' => 60,
    'description' => 'Review and provide feedback on the proposed 2025 annual budget including all departmental allocations.',
    'created_date' => '2024-12-01',
    'committee' => 'Finance',
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['title']); ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Assigned to <?php echo $item['assigned_to']; ?></p>
        </div>
        <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assign
        </a>
        <a href="progress.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Progress
        </a>
        <a href="create.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-plus-lg"></i> Create
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Task Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Assigned To</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $item['assigned_to']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Deadline</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo date('M j, Y', strtotime($item['deadline'])); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Priority</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <?php echo $item['priority']; ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <?php echo $item['status']; ?>
                    </span>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Progress</p>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-blue-600 h-4 rounded-full flex items-center justify-center text-white text-xs font-bold" style="width: <?php echo $item['progress']; ?>%">
                            <?php echo $item['progress']; ?>%
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                    <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['description']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <button onclick="updateProgress()" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-graph-up"></i> Update Progress
                </button>
                <button onclick="markComplete()" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-check-circle"></i> Mark Complete
                </button>
                <button onclick="reassign()" class="w-full px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-person-plus"></i> Reassign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateProgress() { alert('Update progress'); }
    function markComplete() { if(confirm('Mark as complete?')) alert('Task completed'); }
    function reassign() { alert('Reassign task'); }
</script>

<?php include '../../includes/footer.php'; ?>
