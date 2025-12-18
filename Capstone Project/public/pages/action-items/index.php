<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Items';
include '../../includes/header.php';

// Hardcoded action items data
$actionItems = [
    ['id' => 1, 'title' => 'Review 2025 Budget Proposal', 'assigned_to' => 'Hon. Maria Santos', 'deadline' => '2024-12-20', 'priority' => 'High', 'status' => 'In Progress', 'progress' => 60],
    ['id' => 2, 'title' => 'Inspect Healthcare Facilities', 'assigned_to' => 'Hon. Juan Dela Cruz', 'deadline' => '2024-12-18', 'priority' => 'High', 'status' => 'In Progress', 'progress' => 80],
    ['id' => 3, 'title' => 'Prepare School Infrastructure Report', 'assigned_to' => 'Hon. Ana Reyes', 'deadline' => '2024-12-25', 'priority' => 'Medium', 'status' => 'To Do', 'progress' => 0],
    ['id' => 4, 'title' => 'Coordinate Road Repair Schedule', 'assigned_to' => 'Hon. Pedro Garcia', 'deadline' => '2024-12-15', 'priority' => 'High', 'status' => 'Done', 'progress' => 100],
    ['id' => 5, 'title' => 'Update Disaster Response Plan', 'assigned_to' => 'Hon. Rosa Martinez', 'deadline' => '2024-12-22', 'priority' => 'Medium', 'status' => 'To Do', 'progress' => 0],
    ['id' => 6, 'title' => 'Draft Revenue Enhancement Ordinance', 'assigned_to' => 'Hon. Maria Santos', 'deadline' => '2024-12-30', 'priority' => 'Low', 'status' => 'To Do', 'progress' => 0],
    ['id' => 7, 'title' => 'Review Public Health Programs', 'assigned_to' => 'Hon. Juan Dela Cruz', 'deadline' => '2024-12-28', 'priority' => 'Medium', 'status' => 'In Progress', 'progress' => 40],
    ['id' => 8, 'title' => 'Finalize Committee Report', 'assigned_to' => 'Hon. Ana Reyes', 'deadline' => '2024-12-16', 'priority' => 'High', 'status' => 'Done', 'progress' => 100],
];

$todoItems = array_filter($actionItems, fn($item) => $item['status'] === 'To Do');
$inProgressItems = array_filter($actionItems, fn($item) => $item['status'] === 'In Progress');
$doneItems = array_filter($actionItems, fn($item) => $item['status'] === 'Done');
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Action Items</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track and manage committee action items</p>
        </div>
        <a href="create.php" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg"><i class="bi bi-plus-lg"></i> New Action Item</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold"><i class="bi bi-kanban"></i> Kanban Board</a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-person-plus"></i> Assign</a>
        <a href="progress.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-graph-up"></i> Progress</a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-calendar-x"></i> Deadlines</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Items</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($actionItems); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">To Do</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($todoItems); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">In Progress</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($inProgressItems); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Done</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($doneItems); ?></p>
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- To Do Column -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>
            To Do (<?php echo count($todoItems); ?>)
        </h3>
        <div class="space-y-3">
            <?php foreach ($todoItems as $item): ?>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border-l-4 border-gray-500">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2"><?php echo $item['title']; ?></h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-person"></i> <?php echo $item['assigned_to']; ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-calendar"></i> <?php echo date('M j', strtotime($item['deadline'])); ?>
                </p>
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    <?php echo $item['priority'] === 'High' ? 'bg-red-100 text-red-800' : 
                               ($item['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                    <?php echo $item['priority']; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
            In Progress (<?php echo count($inProgressItems); ?>)
        </h3>
        <div class="space-y-3">
            <?php foreach ($inProgressItems as $item): ?>
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-l-4 border-blue-500">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2"><?php echo $item['title']; ?></h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-person"></i> <?php echo $item['assigned_to']; ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-calendar"></i> <?php echo date('M j', strtotime($item['deadline'])); ?>
                </p>
                <div class="mb-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $item['progress']; ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?php echo $item['progress']; ?>% complete</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    <?php echo $item['priority'] === 'High' ? 'bg-red-100 text-red-800' : 
                               ($item['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                    <?php echo $item['priority']; ?>
                </span>
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
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2"><?php echo $item['title']; ?></h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-person"></i> <?php echo $item['assigned_to']; ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <i class="bi bi-check-circle text-green-600"></i> Completed
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
