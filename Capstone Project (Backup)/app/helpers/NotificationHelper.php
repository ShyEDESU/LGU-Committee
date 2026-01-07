<?php
/**
 * Notification Helper
 * Manages user notifications and alerts
 */

class NotificationHelper
{

    /**
     * Get all notifications for a user
     * @param int $userId User ID
     * @return array Array of notifications
     */
    public static function getUserNotifications($userId)
    {
        // In production, this would query the database
        // For now, return dummy data

        $notifications = [
            [
                'id' => 1,
                'user_id' => $userId,
                'type' => 'meeting',
                'title' => 'New Committee Meeting Scheduled',
                'message' => 'Finance Committee meeting scheduled for Dec 15, 2025 at 2:00 PM',
                'link' => '/pages/committee-meetings/view.php?id=1',
                'icon' => 'bi-calendar-event',
                'color' => 'blue',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
                'priority' => 'high'
            ],
            [
                'id' => 2,
                'user_id' => $userId,
                'type' => 'action_item',
                'title' => 'New Action Item Assigned',
                'message' => 'You have been assigned: Review 2025 Budget Proposal',
                'link' => '/pages/action-items/view.php?id=1',
                'icon' => 'bi-check2-square',
                'color' => 'green',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'priority' => 'high'
            ],
            [
                'id' => 3,
                'user_id' => $userId,
                'type' => 'referral',
                'title' => 'New Referral Received',
                'message' => 'Budget Allocation Review has been referred to your committee',
                'link' => '/pages/referral-management/view.php?id=1',
                'icon' => 'bi-inbox',
                'color' => 'orange',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'priority' => 'medium'
            ],
            [
                'id' => 4,
                'user_id' => $userId,
                'type' => 'document',
                'title' => 'Document Approved',
                'message' => 'Committee Report Nov 2025 has been approved',
                'link' => '/pages/committee-reports/view.php?id=1',
                'icon' => 'bi-file-earmark-check',
                'color' => 'green',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'priority' => 'low'
            ],
            [
                'id' => 5,
                'user_id' => $userId,
                'type' => 'deadline',
                'title' => 'Deadline Approaching',
                'message' => 'Action item "Complete Budget Review" is due in 2 days',
                'link' => '/pages/action-items/view.php?id=1',
                'icon' => 'bi-clock',
                'color' => 'red',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'priority' => 'high'
            ],
            [
                'id' => 6,
                'user_id' => $userId,
                'type' => 'system',
                'title' => 'System Update',
                'message' => 'New features have been added to the Committee Management System',
                'link' => '#',
                'icon' => 'bi-info-circle',
                'color' => 'purple',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'priority' => 'low'
            ],
            [
                'id' => 7,
                'user_id' => $userId,
                'type' => 'comment',
                'title' => 'New Comment on Agenda',
                'message' => 'John Doe commented on Q4 Budget Review agenda',
                'link' => '/pages/agenda-builder/view.php?id=1',
                'icon' => 'bi-chat-left-text',
                'color' => 'blue',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'priority' => 'low'
            ],
            [
                'id' => 8,
                'user_id' => $userId,
                'type' => 'meeting',
                'title' => 'Meeting Minutes Published',
                'message' => 'Minutes for Finance Committee meeting are now available',
                'link' => '/pages/committee-meetings/view.php?id=2',
                'icon' => 'bi-file-text',
                'color' => 'blue',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'priority' => 'low'
            ]
        ];

        return $notifications;
    }

    /**
     * Get unread notification count
     * @param int $userId User ID
     * @return int Count of unread notifications
     */
    public static function getUnreadCount($userId)
    {
        $notifications = self::getUserNotifications($userId);
        return count(array_filter($notifications, function ($n) {
            return !$n['is_read'];
        }));
    }

    /**
     * Get recent notifications (for dropdown)
     * @param int $userId User ID
     * @param int $limit Number of notifications to return
     * @return array Array of recent notifications
     */
    public static function getRecentNotifications($userId, $limit = 5)
    {
        $notifications = self::getUserNotifications($userId);
        return array_slice($notifications, 0, $limit);
    }

    /**
     * Mark notification as read
     * @param int $notificationId Notification ID
     * @return bool Success
     */
    public static function markAsRead($notificationId)
    {
        // In production, update database
        // For now, just return true
        return true;
    }

    /**
     * Mark all notifications as read
     * @param int $userId User ID
     * @return bool Success
     */
    public static function markAllAsRead($userId)
    {
        // In production, update database
        // For now, just return true
        return true;
    }

    /**
     * Delete notification
     * @param int $notificationId Notification ID
     * @return bool Success
     */
    public static function deleteNotification($notificationId)
    {
        // In production, delete from database
        // For now, just return true
        return true;
    }

    /**
     * Get notification icon class based on type
     * @param string $type Notification type
     * @return string Icon class
     */
    public static function getIconClass($type)
    {
        $icons = [
            'meeting' => 'bi-calendar-event',
            'action_item' => 'bi-check2-square',
            'referral' => 'bi-inbox',
            'document' => 'bi-file-earmark-check',
            'deadline' => 'bi-clock',
            'system' => 'bi-info-circle',
            'comment' => 'bi-chat-left-text',
            'user' => 'bi-person',
            'approval' => 'bi-check-circle',
            'reminder' => 'bi-bell'
        ];

        return $icons[$type] ?? 'bi-bell';
    }

    /**
     * Get notification color based on type
     * @param string $type Notification type
     * @return string Color class
     */
    public static function getColorClass($type)
    {
        $colors = [
            'meeting' => 'blue',
            'action_item' => 'green',
            'referral' => 'orange',
            'document' => 'green',
            'deadline' => 'red',
            'system' => 'purple',
            'comment' => 'blue',
            'user' => 'gray',
            'approval' => 'green',
            'reminder' => 'yellow'
        ];

        return $colors[$type] ?? 'gray';
    }

    /**
     * Format time ago
     * @param string $datetime Datetime string
     * @return string Formatted time ago
     */
    public static function timeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }

    /**
     * Create a new notification
     * @param array $data Notification data
     * @return bool Success
     */
    public static function createNotification($data)
    {
        // In production, insert into database
        // Required fields: user_id, type, title, message
        // Optional fields: link, priority

        // For now, just return true
        return true;
    }
}
