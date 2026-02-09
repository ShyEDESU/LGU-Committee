<?php
/**
 * Logout fallback script
 * Handles direct navigation to /auth/logout.php
 */
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../app/middleware/SessionManager.php';
require_once __DIR__ . '/../config/database.php';

$sessionManager = new SessionManager($conn);
$sessionManager->logout();

header('Location: ../index.php?logout=success');
exit();
?>