<?php
/**
 * Migration script to add missing columns to the tasks table.
 */
require_once __DIR__ . '/config/database.php';

echo "Starting migration: Adding missing columns to 'tasks' table...\n";

$queries = [
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `task_type` VARCHAR(50) DEFAULT 'action_item' AFTER `assigned_to`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) DEFAULT 'General' AFTER `task_type`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `related_meeting_id` INT NULL AFTER `category`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `agenda_item_id` INT NULL AFTER `related_meeting_id`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `related_document_id` INT NULL AFTER `agenda_item_id`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `referral_id` INT NULL AFTER `related_document_id`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `tags` TEXT NULL AFTER `due_date`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `estimated_hours` DECIMAL(10,2) DEFAULT 0.00 AFTER `tags`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `actual_hours` DECIMAL(10,2) DEFAULT 0.00 AFTER `estimated_hours`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `notes` TEXT NULL AFTER `actual_hours`",
    "ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `is_recurring` TINYINT(1) DEFAULT 0 AFTER `notes`"
];

foreach ($queries as $query) {
    echo "Executing: $query\n";
    if ($conn->query($query)) {
        echo "Successfully executed.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

// Also add foreign keys if they don't exist
$foreignKeys = [
    "ALTER TABLE `tasks` ADD CONSTRAINT `fk_tasks_meeting` FOREIGN KEY (`related_meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE SET NULL",
    "ALTER TABLE `tasks` ADD CONSTRAINT `fk_tasks_agenda` FOREIGN KEY (`agenda_item_id`) REFERENCES `agenda_items`(`item_id`) ON DELETE SET NULL",
    "ALTER TABLE `tasks` ADD CONSTRAINT `fk_tasks_document` FOREIGN KEY (`related_document_id`) REFERENCES `legislative_documents`(`document_id`) ON DELETE SET NULL",
    "ALTER TABLE `tasks` ADD CONSTRAINT `fk_tasks_referral` FOREIGN KEY (`referral_id`) REFERENCES `referrals`(`referral_id`) ON DELETE SET NULL"
];

foreach ($foreignKeys as $fkQuery) {
    echo "Executing FK: $fkQuery\n";
    if ($conn->query($fkQuery)) {
        echo "FK added successfully.\n";
    } else {
        // Suppress errors about already existing FKs
        if (strpos($conn->error, "Duplicate key name") !== false || strpos($conn->error, "Foreign key constraint already exists") !== false) {
            echo "FK already exists.\n";
        } else {
            echo "Note: FK might already exist or: " . $conn->error . "\n";
        }
    }
}

echo "Migration completed.\n";
?>