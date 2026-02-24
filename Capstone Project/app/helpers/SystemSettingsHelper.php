<?php
/**
 * SystemSettingsHelper.php
 * Handles fetching and updating global system settings.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/SecurityHelper.php';

/**
 * Get current system settings.
 * Returns defaults if no settings exist in DB.
 */
function getSystemSettings()
{
    global $conn;

    $query = "SELECT * FROM system_settings LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $settings = $result->fetch_assoc();
        // Fallback to auto-detection if DB value is empty
        if (empty($settings['base_url'])) {
            $settings['base_url'] = detectBaseUrl();
        }
        return $settings;
    }

    // Return defaults if table is empty
    return [
        'lgu_name' => 'City of Valenzuela',
        'lgu_address' => 'Poblacion, Valenzuela City',
        'lgu_contact' => '(02) 123-4567',
        'lgu_email' => 'contact@valenzuela.gov.ph',
        'base_url' => detectBaseUrl(),
        'lgu_logo_path' => 'assets/images/logo.png',
        'theme_color' => '#dc2626',
        'timezone' => 'Asia/Manila',
        'auto_backup_enabled' => 1,
        'backup_frequency' => 'daily',
        'maintenance_mode' => 0,
        'session_timeout' => 30,
        'min_password_length' => 8,
        'require_special_chars' => 0,
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_encryption' => 'tls',
        'log_retention_days' => 90,
        'system_title' => 'CMS - Committee Management System',
        'system_acronym' => 'CMS',
        'default_language' => 'en',
        'date_format' => 'M j, Y',
        'time_format' => 'H:i',
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Update system settings.
 * Inserts if no settings exist, otherwise updates the first row.
 */
function updateSystemSettings($data, $updatedBy)
{
    global $conn;

    // Check if entry exists
    $check = $conn->query("SELECT setting_id, smtp_pass FROM system_settings LIMIT 1");
    $exists = ($check && $check->num_rows > 0);

    // Define all parameters in order to ensure perfect mapping
    // Format: ['value' => mixed, 'type' => 's'|'i']
    $params = [
        ['v' => $data['lgu_name'], 't' => 's'],
        ['v' => $data['lgu_address'], 't' => 's'],
        ['v' => $data['lgu_contact'], 't' => 's'],
        ['v' => $data['lgu_email'], 't' => 's'],
        ['v' => $data['base_url'], 't' => 's'],
        ['v' => $data['lgu_logo_path'], 't' => 's'],
        ['v' => $data['theme_color'], 't' => 's'],
        ['v' => $data['timezone'], 't' => 's'],
        ['v' => (int) $data['auto_backup_enabled'], 't' => 'i'],
        ['v' => $data['backup_frequency'], 't' => 's'],
        ['v' => (int) $data['maintenance_mode'], 't' => 'i'],
        ['v' => (int) $data['session_timeout'], 't' => 'i'],
        ['v' => (int) $data['min_password_length'], 't' => 'i'],
        ['v' => (int) $data['require_special_chars'], 't' => 'i'],
        ['v' => $data['smtp_host'], 't' => 's'],
        ['v' => (int) $data['smtp_port'], 't' => 'i'],
        ['v' => $data['smtp_user'], 't' => 's'],
        ['v' => $data['smtp_pass'], 't' => 's'],
        ['v' => $data['smtp_encryption'], 't' => 's'],
        ['v' => (int) $data['log_retention_days'], 't' => 'i'],
        ['v' => $data['system_title'], 't' => 's'],
        ['v' => $data['system_acronym'], 't' => 's'],
        ['v' => $data['default_language'], 't' => 's'],
        ['v' => $data['date_format'], 't' => 's'],
        ['v' => $data['time_format'], 't' => 's'],
        ['v' => (int) $updatedBy, 't' => 'i']
    ];

    if ($exists) {
        $row = $check->fetch_assoc();
        $id = $row['setting_id'];
        $currentPassInDb = $row['smtp_pass'];

        // Proactive Encryption
        if (!empty($data['smtp_pass'])) {
            $decrypted = SecurityHelper::decrypt($data['smtp_pass']);
            if ($decrypted === $data['smtp_pass']) {
                $encrypted = SecurityHelper::encrypt($data['smtp_pass']);
                // The params array is constructed based on the keys. 
                // smtp_pass is the 18th field (index 17).
                $params[17]['v'] = $encrypted;
            }
        }

        $sql = "UPDATE system_settings SET 
            lgu_name = ?, lgu_address = ?, lgu_contact = ?, lgu_email = ?, base_url = ?, lgu_logo_path = ?,
            theme_color = ?, timezone = ?, auto_backup_enabled = ?, backup_frequency = ?, maintenance_mode = ?,
            session_timeout = ?, min_password_length = ?, require_special_chars = ?,
            smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_pass = ?, smtp_encryption = ?,
            log_retention_days = ?, system_title = ?, system_acronym = ?, default_language = ?,
            date_format = ?, time_format = ?, updated_by = ? 
            WHERE setting_id = ?";

        $stmt = $conn->prepare($sql);

        // Build types string and values array
        $types = "";
        $values = [];
        foreach ($params as $p) {
            $types .= $p['t'];
            $values[] = $p['v'];
        }
        $types .= "i"; // for $id
        $values[] = $id;

        $stmt->bind_param($types, ...$values);
    } else {
        // For Insert, encrypt password before creating params
        if (!empty($data['smtp_pass'])) {
            $data['smtp_pass'] = SecurityHelper::encrypt($data['smtp_pass']);
            $params[17]['v'] = $data['smtp_pass'];
        }

        $sql = "INSERT INTO system_settings (
            lgu_name, lgu_address, lgu_contact, lgu_email, base_url, lgu_logo_path, theme_color, timezone, 
            auto_backup_enabled, backup_frequency, maintenance_mode, session_timeout, min_password_length, 
            require_special_chars, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_encryption, 
            log_retention_days, system_title, system_acronym, default_language, date_format, time_format, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $types = "";
        $values = [];
        foreach ($params as $p) {
            $types .= $p['t'];
            $values[] = $p['v'];
        }

        $stmt->bind_param($types, ...$values);
    }

    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Automatically detects the base URL of the application.
 */
function detectBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Get the script name (e.g., /Capstone Project/public/index.php)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Find where /public/ or /app/ or any other known entry path starts
    $entryPoints = ['/public', '/app', '/auth', '/api', '/pages'];
    $basePath = '';

    foreach ($entryPoints as $point) {
        $pos = strpos($scriptName, $point . '/');
        if ($pos !== false) {
            $basePath = substr($scriptName, 0, $pos);
            break;
        }
    }

    // If no entry point found, just use the directory of the current script if it's not root
    if (empty($basePath) && $scriptName !== '/index.php' && !empty($scriptName)) {
        $baseDir = dirname($scriptName);
        if ($baseDir !== '/' && $baseDir !== '\\') {
            $basePath = rtrim($baseDir, '/\\');
            // If it still points to a helper dir, we might need to go up
            if (strpos($basePath, '/app/helpers') !== false) {
                $basePath = str_replace('/app/helpers', '', $basePath);
            }
        }
    }

    // Ensure we don't return backslashes in URL and handle spaces for local dev folders
    $basePath = str_replace('\\', '/', $basePath);

    return $protocol . "://" . $host . rtrim($basePath, '/');
}

?>