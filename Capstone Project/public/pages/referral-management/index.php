<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Management';
include '../../includes/header.php';

// Hardcoded referrals data
$referrals = [
    ['id' => 1, 'title' => 'Ordinance No. 2024-001 - Annual Budget Appropriation', 'type' => 'Ordinance', 'committee' => 'Finance', 'deadline' => '2024-12-20', 'status' => 'In Progress', 'priority' => 'High'],
    ['id' => 2, 'title' => 'Resolution No. 2024-045 - Healthcare Facility Upgrade', 'type' => 'Resolution', 'committee' => 'Health', 'deadline' => '2024-12-18', 'status' => 'In Progress', 'priority' => 'High'],
    ['id' => 3, 'title' => 'Ordinance No. 2024-002 - School Building Construction', 'type' => 'Ordinance', 'committee' => 'Education', 'deadline' => '2024-12-25', 'status' => 'Pending', 'priority' => 'Medium'],
    ['id' => 4, 'title' => 'Resolution No. 2024-046 - Road Repair Authorization', 'type' => 'Resolution', 'committee' => 'Infrastructure', 'deadline' => '2024-12-15', 'status' => 'Completed', 'priority' => 'High'],
    ['id' => 5, 'title' => 'Communication - Fire Safety Equipment Request', 'type' => 'Communication', 'committee' => 'Public Safety', 'deadline' => '2024-12-22', 'status' => 'Pending', 'priority' => 'Medium'],
    ['id' => 6, 'title' => 'Ordinance No. 2024-003 - Revenue Code Amendment', 'type' => 'Ordinance', 'committee' => 'Finance', 'deadline' => '2024-12-30', 'status' => 'Pending', 'priority' => 'Low'],
    ['id' => 7, 'title' => 'Resolution No. 2024-047 - Public Health Program', 'type' => 'Resolution', 'committee' => 'Health', 'deadline' => '2024-12-28', 'status' => 'In Progress', 'priority' => 'Medium'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Referral Management</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track ordinances, resolutions, and communications</p>
        </div>
        <a href="create.php" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg"><i class="bi bi-plus-lg"></i> New Referral</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold"><i class="bi bi-list"></i> All Referrals</a>
        <a href="tracking.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-graph-up"></i> Tracking</a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-person-plus"></i> Assignment</a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-calendar-x"></i> Deadlines</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Referrals</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($referrals); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Pending</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($referrals, fn($r) => $r['status'] === 'Pending')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">In Progress</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($referrals, fn($r) => $r['status'] === 'In Progress')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Completed</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($referrals, fn($r) => $r['status'] === 'Completed')); ?>
        </p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($referrals as $referral): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><?php echo $referral['title']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $referral['type']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $referral['committee']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($referral['deadline'])); ?></td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $referral['priority'] === 'High' ? 'bg-red-100 text-red-800' : 
                                   ($referral['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo $referral['priority']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $referral['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' : 
                                   ($referral['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo $referral['status']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="view.php?id=<?php echo $referral['id']; ?>" class="text-cms-red hover:text-cms-dark">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
