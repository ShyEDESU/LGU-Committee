<?php
/**
 * Authentication Check Middleware
 * 
 * This file should be included at the top of every protected page
 * to ensure only authenticated users can access it.
 * 
 * Usage: require_once(__DIR__ . '/../../app/middleware/AuthCheck.php');
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    // Calculate the correct path based on current file location
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $login_url = $protocol . '://' . $host . '/auth/login.php';
    
    header('Location: ' . $login_url);
    exit();
}

// Optional: Check if user session is still valid (optional security measure)
if (!isset($_SESSION['login_time']) || (time() - $_SESSION['login_time'] > 86400)) {
    // Session older than 24 hours - force re-login
    session_destroy();
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $login_url = $protocol . '://' . $host . '/auth/login.php?session_expired=true';
    
    header('Location: ' . $login_url);
    exit();
}

?>
