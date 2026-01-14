<?php
/**
 * ULTRA-SIMPLE SESSION CONFIGURATION
 * 
 * ONLY ONE LOGOUT TRIGGER: 8 hours of inactivity
 * NO logout on browser close
 * NO logout on refresh
 * NO logout on navigation
 * NO session regeneration
 */

// Prevent multiple session starts
if (session_status() === PHP_SESSION_NONE) {

    // Session lasts 30 days
    ini_set('session.gc_maxlifetime', 2592000); // 30 days
    ini_set('session.cookie_lifetime', 2592000); // 30 days

    // Configure persistent session cookie
    session_set_cookie_params([
        'lifetime' => 2592000,  // 30 days - survives browser close
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Start session
    session_start();
}

// ONLY check for inactivity timeout
if (isset($_SESSION['user_id'])) {

    // Initialize last activity if not set
    if (!isset($_SESSION['LAST_ACTIVITY'])) {
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    // Check if inactive for more than 8 hours
    $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];
    $timeout = 28800; // 8 hours in seconds

    if ($inactive_time > $timeout) {
        // ONLY logout trigger: 8 hours of inactivity
        session_unset();
        session_destroy();

        // Restart session for redirect message
        session_start();
        $_SESSION['timeout_message'] = 'Session expired due to inactivity.';

        // Redirect to login
        if (php_sapi_name() !== 'cli') {
            header('Location: /auth/login.php');
            exit();
        }
    }

    // Update activity timestamp on EVERY request
    $_SESSION['LAST_ACTIVITY'] = time();
}
?>