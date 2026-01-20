<?php
/**
 * AJAX Handler - Update Committee
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

// Get POST data
$data = [
    'name' => trim($_POST['name'] ?? ''),
    'type' => trim($_POST['type'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'jurisdiction' => trim($_POST['jurisdiction'] ?? ''),
    'chairperson_id' => !empty($_POST['chairperson_id']) ? intval($_POST['chairperson_id']) : null,
    'vice_chair_id' => !empty($_POST['vice_chair_id']) ? intval($_POST['vice_chair_id']) : null,
    'secretary_id' => !empty($_POST['secretary_id']) ? intval($_POST['secretary_id']) : null,
    'is_active' => isset($_POST['is_active']) ? (bool) $_POST['is_active'] : true
];

// Validate required fields
if (empty($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Committee name is required']);
    exit();
}

if (empty($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Committee type is required']);
    exit();
}

// Update committee
$success = updateCommittee($committeeId, $data);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Committee updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update committee']);
}
?>