<?php
require_once __DIR__ . '/../../../../config/session_config.php';
require_once __DIR__ . '/../../../../app/helpers/SystemSettingsHelper.php';

header('Content-Type: application/json');

// Capture any accidental output (warnings/notices) to prevent JSON corruption
ob_start();

// Disable direct error display to browser, log them instead
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Fetch current settings to preserve fields not in the request
$currentSettings = getSystemSettings();

// Map settings, preserving existing values if not provided or empty (for passwords)
$data = [
    'lgu_name' => $input['lgu_name'] ?? $currentSettings['lgu_name'],
    'lgu_address' => $input['lgu_address'] ?? $currentSettings['lgu_address'],
    'lgu_contact' => $input['lgu_contact'] ?? $currentSettings['lgu_contact'],
    'lgu_email' => $input['lgu_email'] ?? $currentSettings['lgu_email'],
    'base_url' => $input['base_url'] ?? $currentSettings['base_url'],
    'timezone' => $input['timezone'] ?? $currentSettings['timezone'],
    'theme_color' => $input['theme_color'] ?? $currentSettings['theme_color'],
    'lgu_logo_path' => $input['lgu_logo_path'] ?? $currentSettings['lgu_logo_path'],
    'auto_backup_enabled' => isset($input['auto_backup_enabled']) ? (int) $input['auto_backup_enabled'] : $currentSettings['auto_backup_enabled'],
    'backup_frequency' => $input['backup_frequency'] ?? $currentSettings['backup_frequency'],
    'maintenance_mode' => isset($input['maintenance_mode']) ? (int) $input['maintenance_mode'] : $currentSettings['maintenance_mode'],
    'session_timeout' => (int) ($input['session_timeout'] ?? $currentSettings['session_timeout']),
    'min_password_length' => (int) ($input['min_password_length'] ?? $currentSettings['min_password_length']),
    'require_special_chars' => isset($input['require_special_chars']) ? (int) $input['require_special_chars'] : $currentSettings['require_special_chars'],
    'smtp_host' => $input['smtp_host'] ?? $currentSettings['smtp_host'],
    'smtp_port' => (int) ($input['smtp_port'] ?? $currentSettings['smtp_port']),
    'smtp_user' => $input['smtp_user'] ?? $currentSettings['smtp_user'],
    // Specialized logic for password: only update if not empty
    'smtp_pass' => (!empty($input['smtp_pass'])) ? $input['smtp_pass'] : $currentSettings['smtp_pass'],
    'smtp_encryption' => $input['smtp_encryption'] ?? $currentSettings['smtp_encryption'],
    'log_retention_days' => (int) ($input['log_retention_days'] ?? $currentSettings['log_retention_days']),
    'system_title' => $input['system_title'] ?? $currentSettings['system_title'],
    'system_acronym' => $input['system_acronym'] ?? $currentSettings['system_acronym'],
    'default_language' => $input['default_language'] ?? $currentSettings['default_language'],
    'date_format' => $input['date_format'] ?? $currentSettings['date_format'],
    'time_format' => $input['time_format'] ?? $currentSettings['time_format']
];

$updatedBy = $_SESSION['user_id'];

try {
    if (updateSystemSettings($data, $updatedBy)) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'message' => 'Database update failed'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
} catch (Error $e) {
    $response = ['success' => false, 'message' => 'System Error: ' . $e->getMessage()];
}

// Get anything that was accidentally printed (warnings, etc.)
$debug = ob_get_clean();
if (!empty($debug)) {
    $response['debug_output'] = $debug;
}

echo json_encode($response);
?>