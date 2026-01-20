<?php
/**
 * AJAX Handler - Delete Committee
 */

require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../../../../app/helpers/CommitteeHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get committee ID
$committeeId = intval($_POST['committee_id'] ?? 0);

if ($committeeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid committee ID']);
    exit();
}

// Delete committee
$success = deleteCommittee($committeeId);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Committee deleted successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete committee']);
}
?>