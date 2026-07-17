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

    // Check if inactive for more than 10 minutes
    $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];
    $timeout = 600; // 10 minutes in seconds

    if ($inactive_time > $timeout) {
        // Inactivity logout
        session_unset();
        session_destroy();

        // Restart session for redirect message
        session_start();
        $_SESSION['timeout_message'] = 'Session expired due to inactivity.';

        // Redirect to login - try to find it relatively or go back to landing
        if (php_sapi_name() !== 'cli') {
            // Find base path
            $currentPath = $_SERVER['PHP_SELF'];
            $isInPublic = strpos($currentPath, '/public/') !== false || strpos($currentPath, '/auth/') !== false;
            $redirectPath = $isInPublic ? '../auth/login.php' : 'auth/login.php';

            // If we are deep in pages:
            if (strpos($currentPath, '/pages/') !== false) {
                $redirectPath = '../../../auth/login.php';
            }

            header("Location: $redirectPath");
            exit();
        }
    }

    // Update activity timestamp on EVERY request
    $_SESSION['LAST_ACTIVITY'] = time();

    // ── OTP SECURITY INTERACTION GUARD ─────────────────────────────────────
    // Only enforce OTP for sessions that started the new OTP-aware login flow.
    // Sessions where otp_pending is TRUE have not yet completed verification.
    // Existing sessions (no otp_pending flag) are treated as already verified.
    if (isset($_SESSION['user_id']) && isset($_SESSION['otp_pending']) && $_SESSION['otp_pending'] === true) {
        $currentPath = $_SERVER['PHP_SELF'];
        
        // Allow verify-otp.php, login.php, logout.php, and AuthController.php to bypass the guard
        $isAuthRequest = strpos($currentPath, 'verify-otp.php') !== false || 
                         strpos($currentPath, 'login.php') !== false || 
                         strpos($currentPath, 'logout') !== false || 
                         strpos($currentPath, 'AuthController.php') !== false;

        if (!$isAuthRequest) {
            $isInPublic = strpos($currentPath, '/public/') !== false || strpos($currentPath, '/auth/') !== false;
            $redirectPath = $isInPublic ? '../auth/verify-otp.php' : 'auth/verify-otp.php';

            if (strpos($currentPath, '/pages/') !== false) {
                $redirectPath = '../../../auth/verify-otp.php';
            }

            header("Location: $redirectPath");
            exit();
        }
    }
}
?>