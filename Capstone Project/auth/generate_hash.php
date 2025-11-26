<?php
/**
 * Password Hash Generator
 * Run this script to generate bcrypt hashes for default passwords
 */

// Generate bcrypt hash for "admin123"
$password = "admin123";
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: " . $password . "\n";
echo "Bcrypt Hash: " . $hash . "\n\n";

// Use this SQL to update your database:
echo "SQL Update Command:\n";
echo "UPDATE users SET password_hash = '" . $hash . "' WHERE email IN ('LGU@admin.com', 'super.admin@legislative-services.gov');\n";

?>
