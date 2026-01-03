<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Reports & Analytics';
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

<div class="mb-6 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-gray-600 mt-1">Committee reports, recommendations, and system analytics</p>
        </div>
        <a href="create.php" class="btn-primary"><i class="bi bi-plus-lg mr-2"></i> New Report</a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition"><i
                class="bi bi-list mr-2"></i> All Reports</a>
        <a href="draft.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-file-earmark-plus mr-2"></i> Draft</a>
        <a href="approve.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-check-circle mr-2"></i> Approval</a>
        <a href="archive.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-archive mr-2"></i> Archive</a>
    </div>
</div>

<!-- Analytics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Monthly Reports</h3>
            <i class="bi bi-graph-up text-2xl text-red-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">24</p>
        <p class="text-sm text-green-600 mt-2"><i class="bi bi-arrow-up mr-1"></i>12% from last month</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Total Views</h3>
            <i class="bi bi-eye text-2xl text-blue-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">1,234</p>
        <p class="text-sm text-green-600 mt-2"><i class="bi bi-arrow-up mr-1"></i>8% from last month</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Approved Reports</h3>
            <i class="bi bi-check-circle text-2xl text-green-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Approved')); ?>
        </p>
        <p class="text-sm text-gray-600 mt-2">Out of <?php echo count($reports); ?> total</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-400">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Reports Over Time</h3>
        <canvas id="reportsOverTimeChart"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-500">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Reports by Status</h3>
        <canvas id="reportsByStatusChart"></canvas>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Reports</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($reports); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Draft</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Draft')); ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Submitted</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Submitted')); ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Approved</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Approved')); ?>
        </p>
    </div>
</div>

<!-- Reports Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommendation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($reports as $report): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold text-gray-900"><?php echo $report['title']; ?></td>
                    <td class="px-6 py-4 text-gray-900"><?php echo $report['committee']; ?></td>
                    <td class="px-6 py-4 text-gray-900"><?php echo date('M j, Y', strtotime($report['date'])); ?></td>
                    <td class="px-6 py-4">
                        <span
                            class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $report['recommendation'] === 'Approve' ? 'bg-green-100 text-green-800' :
                            ($report['recommendation'] === 'Amend' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo $report['recommendation']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $report['status'] === 'Draft' ? 'bg-gray-100 text-gray-800' :
                            ($report['status'] === 'Submitted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                            <?php echo $report['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="view.php?id=<?php echo $report['id']; ?>"
                            class="text-red-600 hover:text-red-700 font-medium"><i class="bi bi-eye mr-1"></i> View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Reports Over Time Chart
    const ctx1 = document.getElementById('reportsOverTimeChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Reports',
                    data: [5, 8, 6, 10, 9, 12, 11, 14, 13, 16, 15, 18],
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: true } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 5 } }
                }
            }
        });
    }

    // Reports by Status Chart
    const ctx2 = document.getElementById('reportsByStatusChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Approved', 'Submitted', 'Draft'],
                datasets: [{
                    label: 'Reports',
                    data: [<?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Approved')); ?>,
                        <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Submitted')); ?>,
                        <?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Draft')); ?>],
                    backgroundColor: ['#16a34a', '#3b82f6', '#6b7280']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>
