<?php
/**
 * Database Migration - Reports & Signatures
 * Run this script to update the legislative_cms database schema.
 */

// Define directory constants if not already defined
define('MIGRATION_RUN', true);
require_once __DIR__ . '/../../config/database.php';

echo "Starting Reports & Signatures Schema Migration...\n";

// 1. Alter 'reports' table to add status and recommendation columns
$alterReportsQueries = [
    "ALTER TABLE `reports` ADD COLUMN `recommendation` ENUM('Approve', 'Disapprove', 'Amend') DEFAULT 'Approve' AFTER `report_type`",
    "ALTER TABLE `reports` ADD COLUMN `status` ENUM('Draft', 'Voting', 'Approved', 'Rejected') DEFAULT 'Draft' AFTER `recommendation`"
];

foreach ($alterReportsQueries as $query) {
    try {
        if ($conn->query($query)) {
            echo "Success: Query executed successfully: " . substr($query, 0, 40) . "...\n";
        }
    } catch (Exception $e) {
        // Suppress error if columns already exist
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Notice: Column already exists, skipping.\n";
        } else {
            echo "Error altering 'reports' table: " . $e->getMessage() . "\n";
        }
    }
}

// 2. Create 'report_signatures' table
$createSignaturesTable = "
CREATE TABLE IF NOT EXISTS `report_signatures` (
  `signature_id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `status` ENUM('Approved', 'Dissented', 'Abstained') DEFAULT 'Approved',
  `signed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_report` (`report_id`, `user_id`),
  FOREIGN KEY (`report_id`) REFERENCES `reports`(`report_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($createSignaturesTable)) {
        echo "Success: 'report_signatures' table created or already exists.\n";
    }
} catch (Exception $e) {
    echo "Error creating 'report_signatures' table: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
?>
