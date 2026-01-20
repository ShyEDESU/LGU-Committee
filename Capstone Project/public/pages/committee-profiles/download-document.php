<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Access denied');
}

$documentId = $_GET['id'] ?? 0;

if (!$documentId) {
    http_response_code(400);
    die('Invalid document ID');
}

// Get document from database
global $conn;
$stmt = $conn->prepare("SELECT content, title FROM legislative_documents WHERE document_id = ?");
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
    http_response_code(404);
    die('Document not found');
}

// Parse file info
$fileInfo = json_decode($document['content'], true);
$filePath = $fileInfo['file_path'] ?? null;

if (!$filePath) {
    http_response_code(404);
    die('File not found');
}

$fullPath = __DIR__ . '/../../../' . $filePath;

if (!file_exists($fullPath)) {
    http_response_code(404);
    die('File not found on server');
}

// Set headers for download
$mimeType = $fileInfo['mime_type'] ?? 'application/octet-stream';
$fileName = $fileInfo['original_name'] ?? basename($filePath);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Output file
readfile($fullPath);
exit();
