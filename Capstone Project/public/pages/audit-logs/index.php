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
$pageTitle = 'Audit Logs';

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
    <p class="text-gray-600 mt-1">Track all system activities and changes</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-100">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
            <select id="filterAction" onchange="filterAuditLogs()" class="input-field w-full">
                <option value="">All Actions</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
            <input type="text" id="filterUser" onkeyup="filterAuditLogs()" placeholder="Search by user..." class="input-field w-full">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
            <input type="date" id="filterDate" onchange="filterAuditLogs()" class="input-field w-full">
        </div>
        
        <div class="flex items-end">
            <button onclick="clearFilters()" class="btn-outline w-full">
                <i class="bi bi-x-circle mr-2"></i>Clear Filters
            </button>
        </div>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-200">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody id="auditLogsList" class="divide-y divide-gray-200">
                <!-- Populated by JavaScript -->
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
        <div class="text-sm text-gray-700">
            Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> logs
        </div>
        <div class="flex gap-2">
            <button class="btn-outline px-3 py-1 text-sm">Previous</button>
            <button class="btn-outline px-3 py-1 text-sm">Next</button>
        </div>
    </div>
</div>

<script src="../../assets/js/audit-logs.js"></script>

<?php include '../../includes/footer.php'; ?>
