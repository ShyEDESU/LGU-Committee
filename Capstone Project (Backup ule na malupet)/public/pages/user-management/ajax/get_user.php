<?php
/**
 * Get User Details
 * Returns user details for viewing/editing
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../user_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Super Admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$user = getUserById($userId);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $user
]);
