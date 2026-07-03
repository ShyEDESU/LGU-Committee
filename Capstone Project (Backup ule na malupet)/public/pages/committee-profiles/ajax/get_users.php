<?php
/**
 * AJAX Handler - Get All Users for Committee Selection
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../../app/helpers/UserHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get all users
$users = UserHelper_getAllUsers();

echo json_encode([
    'success' => true,
    'users' => $users
]);
?>