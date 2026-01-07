<?php
/**
 * Session Configuration
 * Centralized session management for the Committee Management System
 * 
 * This file configures sessions to:
 * - Use session cookies (deleted when browser closes)
 * - Maintain session during normal navigation
 * - Auto-logout when tab/window is closed
 */

// Set ini settings BEFORE session_start to avoid warnings
ini_set('session.gc_maxlifetime', 3600); // 1 hour of inactivity
ini_set('session.cookie_lifetime', 0);   // Session cookie (expires on browser close)

// Session cookie configuration - must be set BEFORE session_start()
session_set_cookie_params([
    'lifetime' => 0,       // Session cookie - deleted on browser close
    'path' => '/',
    'domain' => '',
    'secure' => false,     // Set to true in production with HTTPS
    'httponly' => true,    // Prevent JavaScript access to session cookie
    'samesite' => 'Lax'    // CSRF protection
]);

// Start the session
session_start();

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // Every 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
?>