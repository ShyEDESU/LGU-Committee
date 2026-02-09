<?php
/**
 * Bulk Delete Users
 * Handles deletion of multiple users at once
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

$userIds = $data['user_ids'] ?? [];

if (empty($userIds) || !is_array($userIds)) {
    echo json_encode(['success' => false, 'message' => 'No users selected']);
    exit();
}

$deleted = 0;
$errors = [];
$skipped = [];

foreach ($userIds as $userId) {
    $result = deleteUser($userId, $_SESSION['user_id']);

    if ($result['success']) {
        $deleted++;
    } else {
        $errors[] = $result['message'];
        $skipped[] = $userId;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'deleted' => $deleted,
    'total' => count($userIds),
    'skipped' => count($skipped),
    'errors' => $errors,
    'message' => "Successfully deleted $deleted of " . count($userIds) . " users"
]);
