<?php
/**
 * NotificationHelper.php
 * Handles creating, fetching, and updating user notifications.
 */

require_once __DIR__ . '/../../config/database.php';

// Set timezone to Asia/Manila for accurate notifications
date_default_timezone_set('Asia/Manila');

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

/**
 * Check for approaching deadlines and send reminder notifications.
 * Designed to be called on dashboard/page load. Deduplicates to avoid spamming.
 *
 * @param int $userId The current logged-in user's ID.
 */
function checkAndSendDeadlineReminders($userId)
{
    global $conn;

    if (!$userId) return;

    $today     = date('Y-m-d');
    $in1Day    = date('Y-m-d', strtotime('+1 day'));
    $in3Days   = date('Y-m-d', strtotime('+3 days'));

    // Fetch tasks due within 3 days that are not yet done, assigned to this user
    $sql = "SELECT t.task_id, t.title, t.due_date, t.priority
            FROM tasks t
            WHERE t.assigned_to = ?
              AND t.status NOT IN ('Done', 'Cancelled')
              AND t.due_date BETWEEN ? AND ?
            ORDER BY t.due_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $today, $in3Days);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($task = $result->fetch_assoc()) {
        $taskId  = $task['task_id'];
        $dueDate = $task['due_date'];
        $title   = $task['title'];

        // Calculate days remaining
        $daysLeft = (int) ceil((strtotime($dueDate) - strtotime($today)) / 86400);

        if ($daysLeft < 0) continue; // Already overdue — skip (overdue handled separately)

        // Deduplicate: check if a reminder was already sent in the last 24 hours for this task
        $dedupeCheck = $conn->prepare(
            "SELECT COUNT(*) as cnt FROM notifications
             WHERE user_id = ?
               AND action_link LIKE ?
               AND notification_type = 'deadline'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        $linkPattern = '%action-items/view.php?id=' . $taskId . '%';
        $dedupeCheck->bind_param("is", $userId, $linkPattern);
        $dedupeCheck->execute();
        $dedupeRow = $dedupeCheck->get_result()->fetch_assoc();
        $dedupeCheck->close();

        if ($dedupeRow['cnt'] > 0) continue; // Already notified recently

        // Build the message
        if ($daysLeft === 0) {
            $urgency = 'DUE TODAY';
            $priority = 'urgent';
        } elseif ($daysLeft === 1) {
            $urgency = 'Due Tomorrow';
            $priority = 'high';
        } else {
            $urgency = "Due in {$daysLeft} days";
            $priority = 'medium';
        }

        $notifTitle   = "⏰ Task Deadline Reminder";
        $notifMessage = "{$urgency}: \"{$title}\" is due on " . date('F j, Y', strtotime($dueDate)) . ".";
        $notifLink    = "pages/action-items/view.php?id={$taskId}";

        createNotification($userId, $notifTitle, $notifMessage, 'deadline', $priority, $notifLink);
    }

    $stmt->close();

    // Also check for OVERDUE tasks and send a single alert (deduplicated per task per 24h)
    $overdueSql = "SELECT t.task_id, t.title, t.due_date
                   FROM tasks t
                   WHERE t.assigned_to = ?
                     AND t.status NOT IN ('Done', 'Cancelled')
                     AND t.due_date < ?
                   ORDER BY t.due_date ASC
                   LIMIT 5";

    $stmtO = $conn->prepare($overdueSql);
    $stmtO->bind_param("is", $userId, $today);
    $stmtO->execute();
    $overdueResult = $stmtO->get_result();

    while ($overdueTask = $overdueResult->fetch_assoc()) {
        $taskId = $overdueTask['task_id'];

        // Dedup check
        $dedupeOver = $conn->prepare(
            "SELECT COUNT(*) as cnt FROM notifications
             WHERE user_id = ?
               AND action_link LIKE ?
               AND notification_type = 'alert'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        $linkPattern = '%action-items/view.php?id=' . $taskId . '%';
        $dedupeOver->bind_param("is", $userId, $linkPattern);
        $dedupeOver->execute();
        $dedupeRowO = $dedupeOver->get_result()->fetch_assoc();
        $dedupeOver->close();

        if ($dedupeRowO['cnt'] > 0) continue;

        $overdueDays  = (int) ceil((strtotime($today) - strtotime($overdueTask['due_date'])) / 86400);
        $notifTitle   = "🚨 Overdue Action Item";
        $notifMessage = "\"{$overdueTask['title']}\" was due " . date('F j, Y', strtotime($overdueTask['due_date'])) . " ({$overdueDays} day(s) ago). Please update its status.";
        $notifLink    = "pages/action-items/view.php?id={$taskId}";

        createNotification($userId, $notifTitle, $notifMessage, 'alert', 'urgent', $notifLink);
    }

    $stmtO->close();
}
?>