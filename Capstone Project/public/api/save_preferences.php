<?php
require_once __DIR__ . '/../../config/session_config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Allowed keys
$allowedKeys = ['email', 'meetings', 'tasks', 'system'];
$prefs = [];

foreach ($allowedKeys as $key) {
    $prefs[$key] = isset($data[$key]) ? (bool) $data[$key] : false;
}

$prefsJson = json_encode($prefs);

$stmt = $conn->prepare("UPDATE users SET notification_preferences = ? WHERE user_id = ?");
$stmt->bind_param("si", $prefsJson, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
?>