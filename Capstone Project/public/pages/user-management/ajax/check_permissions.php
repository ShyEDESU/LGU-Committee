<?php
/**
 * Check User Permissions
 * Returns what actions current user can perform on target user
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../user_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$targetUserId = $_GET['user_id'] ?? null;

if (!$targetUserId) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit();
}

// Get target user
$targetUser = getUserById($targetUserId);

if (!$targetUser) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Check permissions
$permissions = canEditUser(
    $_SESSION['user_id'],
    $targetUserId,
    $_SESSION['user_role'],
    $targetUser['role_name']
);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'permissions' => $permissions
]);
