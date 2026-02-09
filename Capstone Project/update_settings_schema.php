<?php
require_once __DIR__ . '/config/database.php';

/**
 * Script to add advanced configuration columns to system_settings table.
 */

$columns = [
    'maintenance_mode' => "TINYINT(1) DEFAULT 0 AFTER backup_frequency",
    'session_timeout' => "INT DEFAULT 30 AFTER maintenance_mode",
    'min_password_length' => "INT DEFAULT 8 AFTER session_timeout",
    'require_special_chars' => "TINYINT(1) DEFAULT 0 AFTER min_password_length",
    'smtp_host' => "VARCHAR(255) NULL AFTER require_special_chars",
    'smtp_port' => "INT NULL AFTER smtp_host",
    'smtp_user' => "VARCHAR(255) NULL AFTER smtp_port",
    'smtp_pass' => "VARCHAR(255) NULL AFTER smtp_user",
    'smtp_encryption' => "VARCHAR(10) DEFAULT 'tls' AFTER smtp_pass",
    'log_retention_days' => "INT DEFAULT 90 AFTER smtp_encryption",
    'system_title' => "VARCHAR(255) DEFAULT 'CMS' AFTER log_retention_days",
    'system_acronym' => "VARCHAR(20) DEFAULT 'CMS' AFTER system_title",
    'default_language' => "VARCHAR(10) DEFAULT 'en' AFTER system_acronym",
    'date_format' => "VARCHAR(50) DEFAULT 'M j, Y' AFTER default_language",
    'time_format' => "VARCHAR(50) DEFAULT 'H:i' AFTER date_format"
];

foreach ($columns as $column => $definition) {
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM system_settings LIKE '$column'");
    if ($check && $check->num_rows == 0) {
        $sql = "ALTER TABLE system_settings ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "Successfully added column: $column\n";
        } else {
            echo "Error adding column $column: " . $conn->error . "\n";
        }
    } else {
        echo "Column $column already exists.\n";
    }
}

echo "Schema update complete.\n";
?>