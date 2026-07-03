<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Start a new session
session_start();

// Redirect to login
header('Location: ../auth/login.php');
exit();
?>
