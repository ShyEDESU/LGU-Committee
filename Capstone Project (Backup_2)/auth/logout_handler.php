<?php
/**
 * Logout Handler
 * Handles automatic logout when user closes tab/window
 * Called via JavaScript sendBeacon on beforeunload event
 */

// Start session to access session data
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Return success status
http_response_code(200);
exit();
?>