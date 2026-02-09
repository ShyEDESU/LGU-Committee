<?php
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../app/helpers/NotificationHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to test notifications. Please log in first.");
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Create a test notification
$title = "Test Notification Generated!";
$message = "Hello $userName, this is an automatic test notification triggered at " . date('H:i:s');
$type = "system"; // meeting, action_item, referral, document, deadline, system, comment
$priority = "high"; // low, medium, high
$link = "pages/notifications/index.php";

$success = createNotification($userId, $title, $message, $type, $priority, $link);

if ($success) {
    echo "<h1>Success!</h1>";
    echo "<p>A test notification has been sent to your account.</p>";
    echo "<p><a href='pages/notifications/index.php'>Click here to view your notifications</a></p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Failed to create notification. Please check your database connection.</p>";
}
?>