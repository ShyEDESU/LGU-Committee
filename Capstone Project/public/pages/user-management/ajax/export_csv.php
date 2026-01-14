<?php
/**
 * Export Users to CSV
 * Exports user list with current filters applied
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../user_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrator') {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get all users with filters (no pagination for export)
$result = getUsers($search, $roleFilter, '', $statusFilter, 1, 10000);
$users = $result['users'];

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_' . date('Y-m-d_His') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write CSV headers
fputcsv($output, [
    'User ID',
    'Email',
    'First Name',
    'Last Name',
    'Role',
    'Department',
    'Position',
    'Phone',
    'Status',
    'Created At',
    'Last Login'
]);

// Write user data
foreach ($users as $user) {
    fputcsv($output, [
        $user['user_id'],
        $user['email'],
        $user['first_name'],
        $user['last_name'],
        $user['role_name'],
        $user['department'] ?? '',
        $user['position'] ?? '',
        $user['phone'] ?? '',
        ucfirst($user['status']),
        date('Y-m-d H:i:s', strtotime($user['created_at'])),
        $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'Never'
    ]);
}

fclose($output);
exit();
