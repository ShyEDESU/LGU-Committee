<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Reports & Recommendations';
include '../../includes/header.php';

// Hardcoded reports data
$reports = [
    ['id' => 1, 'committee' => 'Finance', 'title' => 'Quarterly Financial Report - Q4 2024', 'date' => '2024-12-10', 'recommendation' => 'Approve', 'status' => 'Approved'],
    ['id' => 2, 'committee' => 'Health', 'title' => 'Healthcare Facility Assessment Report', 'date' => '2024-12-08', 'recommendation' => 'Amend', 'status' => 'Submitted'],
    ['id' => 3, 'committee' => 'Education', 'title' => 'School Infrastructure Needs Analysis', 'date' => '2024-12-05', 'recommendation' => 'Approve', 'status' => 'Approved'],
    ['id' => 4, 'committee' => 'Infrastructure', 'title' => 'Road Maintenance Program Report', 'date' => '2024-12-03', 'recommendation' => 'Approve', 'status' => 'Approved'],
    ['id' => 5, 'committee' => 'Public Safety', 'title' => 'Disaster Preparedness Evaluation', 'date' => '2024-12-01', 'recommendation' => 'Amend', 'status' => 'Draft'],
    ['id' => 6, 'committee' => 'Finance', 'title' => 'Revenue Enhancement Recommendations', 'date' => '2024-11-28', 'recommendation' => 'Approve', 'status' => 'Submitted'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Recommendations</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Committee reports and recommendations</p>
        </div>
        <a href="create.php" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg"><i class="bi bi-plus-lg"></i> New Report</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold"><i class="bi bi-list"></i> All Reports</a>
        <a href="draft.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-file-earmark-plus"></i> Draft</a>
        <a href="approve.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-check-circle"></i> Approval</a>
        <a href="archive.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"><i class="bi bi-archive"></i> Archive</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Reports</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($reports); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Draft</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Draft')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Submitted</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Submitted')); ?>
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Approved</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Approved')); ?>
        </p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommendation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($reports as $report): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><?php echo $report['title']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo $report['committee']; ?></td>
                <td class="px-6 py-4 text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($report['date'])); ?></td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $report['recommendation'] === 'Approve' ? 'bg-green-100 text-green-800' : 
                                   ($report['recommendation'] === 'Amend' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                        <?php echo $report['recommendation']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $report['status'] === 'Draft' ? 'bg-gray-100 text-gray-800' : 
                                   ($report['status'] === 'Submitted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo $report['status']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="view.php?id=<?php echo $report['id']; ?>" class="text-cms-red hover:text-cms-dark">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
