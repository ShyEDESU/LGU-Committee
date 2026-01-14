<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$meetingId = $_POST['meeting_id'] ?? 0;
$newStatus = $_POST['new_status'] ?? '';

// Validate input
if (empty($meetingId) || empty($newStatus)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate meeting exists
$meeting = getMeetingById($meetingId);
if (!$meeting) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Meeting not found']);
    exit();
}

// Update the meeting status
$result = updateMeeting($meetingId, ['agenda_status' => $newStatus]);

if ($result) {
    // Update session activity to keep user logged in
    $_SESSION['LAST_ACTIVITY'] = time();

    echo json_encode([
        'success' => true,
        'message' => "Agenda status changed to: $newStatus",
        'new_status' => $newStatus
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update agenda status'
    ]);
}
