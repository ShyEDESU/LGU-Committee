<?php
/**
 * NotificationHelper.php
 * Handles creating, fetching, and updating user notifications.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Creates a new notification for a specific user.
 * 
 * @param int $userId The ID of the user to notify.
 * @param string $title The notification title.
 * @param string $message The notification message.
 * @param string $type The type of notification (reminder, alert, info, task_assigned, referral_assigned, committee_created).
 * @param string $priority The priority (low, medium, high).
 * @param string|null $link Optional link for the action.
 * @return bool True on success, false on failure.
 */
function createNotification($userId, $title, $message, $type = 'info', $priority = 'medium', $link = null)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, notification_type, priority, action_link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $userId, $title, $message, $type, $priority, $link);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Gets the color associated with a notification type.
 */
function getNotificationColor($type)
{
    switch ($type) {
        case 'alert':
        case 'overdue':
            return 'red';
        case 'task_assigned':
        case 'referral_assigned':
            return 'blue';
        case 'committee_created':
            return 'green';
        case 'deadline':
        case 'deadline_approaching':
            return 'orange';
        case 'comment':
            return 'purple';
        default:
            return 'gray';
    }
}

/**
 * Gets the icon associated with a notification type.
 */
function getNotificationIcon($type)
{
    switch ($type) {
        case 'alert':
        case 'overdue':
            return 'bi-exclamation-octagon';
        case 'task_assigned':
            return 'bi-clipboard-check';
        case 'referral_assigned':
            return 'bi-file-earmark-arrow-right';
        case 'committee_created':
            return 'bi-people';
        case 'deadline':
        case 'deadline_approaching':
            return 'bi-calendar-x';
        case 'comment':
            return 'bi-chat-dots';
        case 'system':
            return 'bi-gear';
        default:
            return 'bi-info-circle';
    }
}

/**
 * Fetches recent notifications for a user with mapped keys.
 * 
 * @param int $userId The ID of the user.
 * @param int $limit Max number of notifications to fetch.
 * @return array Array of notification records.
 */
function getUserNotifications($userId, $limit = 50)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];

    while ($row = $result->fetch_assoc()) {
        // Map database columns to view-friendly keys to prevent PHP warnings
        $notifications[] = [
            'id' => $row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['notification_type'],
            'priority' => $row['priority'] ?? 'medium',
            'is_read' => (bool) $row['is_read'],
            'link' => $row['action_link'] ?? '#',
            'created_at' => $row['created_at'],
            'color' => getNotificationColor($row['notification_type']),
            'icon' => getNotificationIcon($row['notification_type'])
        ];
    }
    $stmt->close();

    return $notifications;
}

/**
 * Gets the count of unread notifications for a user.
 * 
 * @param int $userId The ID of the user.
 * @return int Number of unread notifications.
 */
function getUnreadNotificationCount($userId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int) ($row['unread_count'] ?? 0);
}

/**
 * Marks all notifications as read for a user.
 * 
 * @param int $userId The ID of the user.
 * @return bool True on success, false on failure.
 */
function markAllNotificationsRead($userId)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Marks a single notification as read.
 * 
 * @param int $notificationId The ID of the notification.
 * @return bool True on success, false on failure.
 */
function markNotificationRead($notificationId)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->bind_param("i", $notificationId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Returns a human-readable time difference.
 * 
 * @param string $datetime The datetime string.
 * @return string Time ago string.
 */
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60)
        return "Just now";
    if ($diff < 3600)
        return floor($diff / 60) . " mins ago";
    if ($diff < 86400)
        return floor($diff / 3600) . " hours ago";
    if ($diff < 604800)
        return floor($diff / 86400) . " days ago";

    return date('M j, Y', $time);
}
?>