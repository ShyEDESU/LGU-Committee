<?php
/**
 * AJAX Search Users
 * Returns filtered user list based on search and filter criteria
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../user_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Super Admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$search = $data['search'] ?? '';
$roleFilter = $data['role'] ?? '';
$deptFilter = $data['dept'] ?? '';
$statusFilter = $data['status'] ?? '';
$page = $data['page'] ?? 1;

// Get users
$result = getUsers($search, $roleFilter, $deptFilter, $statusFilter, $page);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $result
]);
