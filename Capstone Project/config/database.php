<?php
/**
 * Database Configuration
 * 
 * This file contains database connection settings.
 * Update these values based on your local environment.
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'legislative_cms');
define('DB_PORT', 3306);

// Create mysqli connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Define base directory for includes
define('BASE_DIR', dirname(dirname(__FILE__)));
define('APP_DIR', BASE_DIR . '/app');
define('CONFIG_DIR', BASE_DIR . '/config');
define('PUBLIC_DIR', BASE_DIR . '/public');

?>