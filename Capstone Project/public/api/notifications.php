<?php
require_once __DIR__ . '/../../config/session_config.php';
require_once __DIR__ . '/../../app/helpers/NotificationHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'mark_all_read') {
    $result = markAllNotificationsRead($userId);
    echo json_encode(['success' => $result]);
    exit();
}

if ($action === 'mark_read') {
    $id = $_GET['id'] ?? 0;
    $result = markNotificationRead($id);
    echo json_encode(['success' => $result]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
