<?php
/**
 * Database Schema Update Script
 * Run this once to add profile_picture column to users table
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Add profile_picture column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) AFTER email";
    $conn->query($sql);
    echo "✅ Successfully added profile_picture column to users table\n";

    // Add bio column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT AFTER position";
    $conn->query($sql);
    echo "✅ Successfully added bio column to users table\n";

    // Add phone column if it doesn't exist  
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email";
    $conn->query($sql);
    echo "✅ Successfully added phone column to users table\n";

    echo "\n✅ Database schema updated successfully!\n";

} catch (Exception $e) {
    echo "❌ Error updating database: " . $e->getMessage() . "\n";
}

$conn->close();
