<?php
require_once __DIR__ . '/../../../config/session_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Voting';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Agenda Builder</a></li>
            <li class="breadcrumb-item active">Voting</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Voting</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage voting and record results</p>
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
                <p class="text-sm text-yellow-600 mt-2">Expected features: Electronic voting, vote recording, and
                    results display.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Electronic Voting</h3>
                <i class="bi bi-hand-thumbs-up text-3xl text-blue-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Conduct electronic votes with real-time results</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vote Recording</h3>
                <i class="bi bi-journal-text text-3xl text-green-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Record individual votes and abstentions</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Results Display</h3>
                <i class="bi bi-bar-chart text-3xl text-purple-500"></i>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Display voting results and statistics</p>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>