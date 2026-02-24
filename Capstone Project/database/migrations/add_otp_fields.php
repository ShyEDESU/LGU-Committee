<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Migration to add OTP fields to the users table
 */

$sql = "ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS otp_code VARCHAR(10) NULL AFTER password,
        ADD COLUMN IF NOT EXISTS otp_expiry DATETIME NULL AFTER otp_code";

if ($conn->query($sql)) {
    echo "Successfully updated users table with OTP fields.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
?>