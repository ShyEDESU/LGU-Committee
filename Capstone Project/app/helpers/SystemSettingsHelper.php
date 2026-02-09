<?php
/**
 * SystemSettingsHelper.php
 * Handles fetching and updating global system settings.
 */

require_once __DIR__ . '/../../config/database.php';

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
        return $result->fetch_assoc();
    }

    // Return defaults if table is empty
    return [
        'lgu_name' => 'City of Valenzuela',
        'lgu_address' => 'Poblacion, Valenzuela City',
        'lgu_contact' => '(02) 123-4567',
        'lgu_email' => 'contact@valenzuela.gov.ph',
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
    $check = $conn->query("SELECT setting_id FROM system_settings LIMIT 1");
    $exists = ($check && $check->num_rows > 0);

    if ($exists) {
        $row = $check->fetch_assoc();
        $id = $row['setting_id'];

        $stmt = $conn->prepare("UPDATE system_settings SET 
            lgu_name = ?, 
            lgu_address = ?, 
            lgu_contact = ?, 
            lgu_email = ?, 
            lgu_logo_path = ?,
            timezone = ?, 
            theme_color = ?,
            auto_backup_enabled = ?, 
            backup_frequency = ?, 
            maintenance_mode = ?,
            session_timeout = ?,
            min_password_length = ?,
            require_special_chars = ?,
            smtp_host = ?,
            smtp_port = ?,
            smtp_user = ?,
            smtp_pass = ?,
            smtp_encryption = ?,
            log_retention_days = ?,
            system_title = ?,
            system_acronym = ?,
            default_language = ?,
            date_format = ?,
            time_format = ?,
            updated_by = ? 
            WHERE setting_id = ?");

        $stmt->bind_param(
            "sssssssisiisiiisssisssssii",
            $data['lgu_name'],
            $data['lgu_address'],
            $data['lgu_contact'],
            $data['lgu_email'],
            $data['lgu_logo_path'],
            $data['timezone'],
            $data['theme_color'],
            $data['auto_backup_enabled'],
            $data['backup_frequency'],
            $data['maintenance_mode'],
            $data['session_timeout'],
            $data['min_password_length'],
            $data['require_special_chars'],
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_user'],
            $data['smtp_pass'],
            $data['smtp_encryption'],
            $data['log_retention_days'],
            $data['system_title'],
            $data['system_acronym'],
            $data['default_language'],
            $data['date_format'],
            $data['time_format'],
            $updatedBy,
            $id
        );
    } else {
        $stmt = $conn->prepare("INSERT INTO system_settings (
            lgu_name, lgu_address, lgu_contact, lgu_email, lgu_logo_path, timezone, theme_color, auto_backup_enabled, backup_frequency, 
            maintenance_mode, session_timeout, min_password_length, require_special_chars, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_encryption, log_retention_days, system_title, system_acronym, default_language, date_format, time_format, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssisiisiiisssisssssi",
            $data['lgu_name'],
            $data['lgu_address'],
            $data['lgu_contact'],
            $data['lgu_email'],
            $data['lgu_logo_path'],
            $data['timezone'],
            $data['theme_color'],
            $data['auto_backup_enabled'],
            $data['backup_frequency'],
            $data['maintenance_mode'],
            $data['session_timeout'],
            $data['min_password_length'],
            $data['require_special_chars'],
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_user'],
            $data['smtp_pass'],
            $data['smtp_encryption'],
            $data['log_retention_days'],
            $data['system_title'],
            $data['system_acronym'],
            $data['default_language'],
            $data['date_format'],
            $data['time_format'],
            $updatedBy
        );
    }

    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
?>