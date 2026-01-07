<?php
require_once __DIR__ . '/../../../config/session_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Tracking';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Referrals</a></li>
            <li class="breadcrumb-item active">Tracking</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Referral Tracking</h1>
            <p class="text-gray-600 dark:text-gray-400">Track referral progress and status</p>
        </div>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 mb-6">
        <div class="flex items-center">
            <i class="bi bi-info-circle text-yellow-700 text-2xl mr-3"></i>
            <div>
                <h3 class="text-lg font-semibold text-yellow-800">Coming Soon</h3>
                <p class="text-yellow-700">This feature is currently under development and will be available soon.</p>
                <p class="text-sm text-yellow-600 mt-2">Expected features: Timeline view, status history, progress
                    tracking, and analytics.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline View</h3>
                <i class="bi bi-clock-history text-3xl text-blue-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Visual timeline of referral progress from submission to
                completion</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status History</h3>
                <i class="bi bi-list-check text-3xl text-green-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Complete history of status changes with timestamps and
                users</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Analytics</h3>
                <i class="bi bi-graph-up text-3xl text-purple-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Processing time, bottlenecks, and performance metrics
            </p>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>