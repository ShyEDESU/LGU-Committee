<?php
require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../../../../app/helpers/MailHelper.php';
require_once __DIR__ . '/../../../../app/helpers/SystemSettingsHelper.php';

header('Content-Type: application/json');

// Check if user is admin
$userRoleLower = strtolower($_SESSION['user_role'] ?? 'User');
$isAdmin = ($userRoleLower === 'admin' || $userRoleLower === 'administrator' || $userRoleLower === 'super admin' || $userRoleLower === 'super administrator');

if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['recipient'])) {
    echo json_encode(['success' => false, 'message' => 'Recipient email is required']);
    exit();
}

// Fetch current settings to handle "Leave blank for existing" passwords
$currentSettings = getSystemSettings();

// Temporary settings for testing (using form data)
$testSettings = [
    'host' => $input['smtp_host'] ?: $currentSettings['smtp_host'],
    'port' => $input['smtp_port'] ?: $currentSettings['smtp_port'],
    'user' => $input['smtp_user'] ?: $currentSettings['smtp_user'],
    'pass' => $input['smtp_pass'] ?: $currentSettings['smtp_pass'], // Note: this uses the plain submitted pass
    'encryption' => $input['smtp_encryption'] ?: $currentSettings['smtp_encryption'],
    'from_email' => $currentSettings['lgu_email'] ?? 'noreply@legislative.gov',
    'from_name' => $currentSettings['lgu_name'] ?? 'Legislative Services MS'
];

$to = $input['recipient'];
$subject = "SMTP Test Connection - Legislative CMS";
$body = "
<div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
    <h2 style='color: #dc2626;'>SMTP Connection Successful!</h2>
    <p>This is a test email sent from your <strong>Legislative Management System</strong>.</p>
    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
    <p style='font-size: 14px; color: #666;'>If you are seeing this message, your SMTP settings are configured correctly.</p>
    <p style='font-size: 12px; color: #999; margin-top: 10px;'>Sent at: " . date('Y-m-d H:i:s') . "</p>
</div>";

if (sendMail($to, $subject, $body, $testSettings)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not send email. Check your host, port, and credentials. (Note: Gmail requires an App Password)']);
}
?>