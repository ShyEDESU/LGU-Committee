<?php
/**
 * Profile Picture Upload Handler
 * Handles profile picture upload for My Profile module
 */

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database.php';

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

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Handle remove picture
if ($action === 'remove') {
    // Get current picture
    $query = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Delete file if exists
    if ($user && $user['profile_picture']) {
        $filePath = __DIR__ . '/../../../' . $user['profile_picture'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Update database
    $updateQuery = "UPDATE users SET profile_picture = NULL WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $userId);

    if ($updateStmt->execute()) {
        $_SESSION['profile_picture'] = null;
        echo json_encode(['success' => true, 'message' => 'Profile picture removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove picture']);
    }

    $updateStmt->close();
    exit();
}

// Handle upload
if (!isset($_FILES['profile_picture'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['profile_picture'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error occurred: ' . $file['error']]);
    exit();
}

// Validate file type - ONLY JPG and PNG (NO GIF)
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
$fileType = $_FILES['profile_picture']['type'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only JPG and PNG images are allowed. GIF files are not supported.'
    ]);
    exit();
}

// Validate file extension
$fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file extension. Only .jpg, .jpeg, and .png files are allowed.'
    ]);
    exit();
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB in bytes
if ($_FILES['profile_picture']['size'] > $maxSize) {
    echo json_encode([
        'success' => false,
        'message' => 'File size too large. Maximum size is 5MB. Please crop or resize your image.'
    ]);
    exit();
}

// Check image dimensions - if too large, suggest cropping
$imageInfo = getimagesize($_FILES['profile_picture']['tmp_name']);
if ($imageInfo) {
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // If image is larger than 2000x2000, suggest cropping
    if ($width > 2000 || $height > 2000) {
        echo json_encode([
            'success' => false,
            'message' => 'Image is too large (' . $width . 'x' . $height . 'px). Please crop or resize to under 2000x2000 pixels.',
            'needs_crop' => true,
            'dimensions' => ['width' => $width, 'height' => $height]
        ]);
        exit();
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
$uploadDir = __DIR__ . '/../../../public/uploads/profiles/';
$uploadPath = $uploadDir . $filename;
$dbPath = 'uploads/profiles/' . $filename;

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Delete old profile picture
$query = "SELECT profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && $user['profile_picture']) {
    $oldFilePath = __DIR__ . '/../../../' . $user['profile_picture'];
    if (file_exists($oldFilePath)) {
        unlink($oldFilePath);
    }
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Update database
    $updateQuery = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $dbPath, $userId);

    if ($updateStmt->execute()) {
        // Update session
        $_SESSION['profile_picture'] = $dbPath;

        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'picture_url' => '../../' . $dbPath
        ]);
    } else {
        // Delete uploaded file if database update fails
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}

$conn->close();
