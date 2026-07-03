<?php
error_reporting(0);
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

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

require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

// Validate meeting exists
$meeting = getMeetingById($meetingId);
if (!$meeting) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Meeting not found']);
    exit();
}

// Permission Checks based on Role-Based Governance
$userId = $_SESSION['user_id'];

if ($newStatus === 'Under Review') {
    if (!canUpdate($userId, 'agendas', $meetingId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized to submit for review']);
        exit();
    }
} elseif ($newStatus === 'Approved') {
    if (!canApprove($userId, 'agendas', $meetingId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Committee Chairperson or Admin can approve the agenda']);
        exit();
    }
} elseif ($newStatus === 'Published') {
    if (!canPublish($userId, 'agendas', $meetingId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized to publish the agenda']);
        exit();
    }
} elseif ($newStatus === 'Draft' || $newStatus === 'Archived') {
    if (!canUpdate($userId, 'agendas', $meetingId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized to change agenda status']);
        exit();
    }
}

// Prepare meeting update data
$updateData = ['agenda_status' => $newStatus];

// Governance Logic: Legal Notice Timestamping
if ($newStatus === 'Published') {
    // Only set posted_at if it hasn't been set yet (first publication)
    if (empty($meeting['posted_at'])) {
        $updateData['posted_at'] = date('Y-m-d H:i:s');
    } else {
        // If it was already published once, any subsequent publication is an Amendment
        $updateData['is_amended'] = 1;
    }
}

// Update the meeting status and governance attributes
$result = updateMeeting($meetingId, $updateData);

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
