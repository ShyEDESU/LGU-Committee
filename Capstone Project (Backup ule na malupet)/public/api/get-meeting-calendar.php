<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session_config.php';
require_once __DIR__ . '/../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../app/helpers/DataHelper.php';

$id = $_GET['id'] ?? 0;
$meeting = getMeetingById($id);

if (!$meeting) {
    echo json_encode(['error' => 'Meeting not found']);
    exit();
}

echo json_encode($meeting);
