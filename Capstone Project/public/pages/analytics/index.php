<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$pageTitle = 'Reports & Analytics';

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
    <p class="text-gray-600 mt-1">View detailed reports and statistics</p>
</div>

<!-- Analytics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Monthly Uploads</h3>
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
            <h3 class="text-lg font-bold text-gray-800">Total Downloads</h3>
            <i class="bi bi-download text-2xl text-green-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">567</p>
        <p class="text-sm text-red-600 mt-2"><i class="bi bi-arrow-down mr-1"></i>3% from last month</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-400">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Documents Over Time</h3>
        <canvas id="documentsOverTimeChart"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-500">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Documents by Status</h3>
        <canvas id="documentsByStatusChart"></canvas>
    </div>
</div>

<!-- Top Contributors -->
<div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-600">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Top Contributors</h3>
    <div class="space-y-3">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:shadow-md transition">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                    <span class="text-red-600 font-bold">AU</span>
                </div>
                <span class="text-gray-800 font-medium">Admin User</span>
            </div>
            <span class="text-gray-600 font-medium">12 documents</span>
        </div>

        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:shadow-md transition">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <span class="text-blue-600 font-bold">JD</span>
                </div>
                <span class="text-gray-800 font-medium">John Doe</span>
            </div>
            <span class="text-gray-600 font-medium">8 documents</span>
        </div>

        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:shadow-md transition">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                    <span class="text-green-600 font-bold">JS</span>
                </div>
                <span class="text-gray-800 font-medium">Jane Smith</span>
            </div>
            <span class="text-gray-600 font-medium">4 documents</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/analytics.js"></script>

<?php include '../../includes/footer.php'; ?>
