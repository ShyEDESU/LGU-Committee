<?php
/**
 * Migration script to add missing columns to the reports table.
 */
require_once __DIR__ . '/config/database.php';

echo "Starting migration: Adding missing columns to 'reports' table...\n";

$queries = [
    "ALTER TABLE `reports` ADD COLUMN IF NOT EXISTS `document_id` INT NULL AFTER `committee_id`",
    "ALTER TABLE `reports` ADD COLUMN IF NOT EXISTS `meeting_id` INT NULL AFTER `document_id`"
];

foreach ($queries as $query) {
    echo "Executing: $query\n";
    if ($conn->query($query)) {
        echo "Successfully executed.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

// Add foreign keys
$foreignKeys = [
    "ALTER TABLE `reports` ADD CONSTRAINT `fk_reports_document` FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`) ON DELETE SET NULL",
    "ALTER TABLE `reports` ADD CONSTRAINT `fk_reports_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE SET NULL"
];

foreach ($foreignKeys as $fkQuery) {
    echo "Executing FK: $fkQuery\n";
    if ($conn->query($fkQuery)) {
        echo "FK added successfully.\n";
    } else {
        if (strpos($conn->error, "Duplicate key name") !== false || strpos($conn->error, "Foreign key constraint already exists") !== false || strpos($conn->error, "already exists") !== false) {
            echo "FK already exists.\n";
        } else {
            echo "Note: FK might already exist or: " . $conn->error . "\n";
        }
    }
}

echo "Migration completed.\n";
?>
