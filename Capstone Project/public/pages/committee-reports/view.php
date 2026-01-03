<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$reportId = $_GET['id'] ?? 1;
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Report Details';
include '../../includes/header.php';

$report = [
    'id' => $reportId,
    'title' => 'Quarterly Financial Report - Q4 2024',
    'committee' => 'Finance',
    'date' => '2024-12-10',
    'recommendation' => 'Approve',
    'status' => 'Approved',
    'summary' => 'The Finance Committee has reviewed the Q4 2024 financial performance and finds the results satisfactory.',
    'findings' => 'Revenue targets were exceeded by 12%. Expenditures were within budget. All financial controls are functioning properly.',
    'recommendations_text' => 'The committee recommends approval of the financial statements and continuation of current fiscal policies.',
    'submitted_by' => 'Hon. Maria Santos',
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($report['title']); ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo $report['committee']; ?> Committee</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="downloadPDF()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                <i class="bi bi-download"></i> Download PDF
            </button>
            <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Reports
        </a>
        <a href="draft.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-plus"></i> Draft
        </a>
        <a href="approve.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-check-circle"></i> Approval
        </a>
        <a href="create.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-plus-lg"></i> Create
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Report Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Report Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo date('F j, Y', strtotime($report['date'])); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <?php echo $report['status']; ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recommendation</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <?php echo $report['recommendation']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Executive Summary</h2>
            <p class="text-gray-900 dark:text-white leading-relaxed"><?php echo htmlspecialchars($report['summary']); ?></p>
        </div>

        <!-- Findings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Findings</h2>
            <p class="text-gray-900 dark:text-white leading-relaxed"><?php echo htmlspecialchars($report['findings']); ?></p>
        </div>

        <!-- Recommendations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recommendations</h2>
            <p class="text-gray-900 dark:text-white leading-relaxed"><?php echo htmlspecialchars($report['recommendations_text']); ?></p>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Report Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Report Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Committee</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $report['committee']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted By</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $report['submitted_by']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date Submitted</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($report['date'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <button onclick="editReport()" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-pencil"></i> Edit Report
                </button>
                <button onclick="shareReport()" class="w-full px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-share"></i> Share Report
                </button>
                <button onclick="printReport()" class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadPDF() { alert('Download PDF'); }
    function editReport() { alert('Edit report'); }
    function shareReport() { alert('Share report'); }
    function printReport() { window.print(); }
</script>

<?php include '../../includes/footer.php'; ?>

