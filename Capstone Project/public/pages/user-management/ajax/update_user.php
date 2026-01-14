<?php
/**
 * Update User Handler
 * Handles user updates via AJAX
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../user_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

// Validate data
$errors = validateUserData($data, true);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Update user
$result = updateUser($userId, $data);

header('Content-Type: application/json');
echo json_encode($result);
