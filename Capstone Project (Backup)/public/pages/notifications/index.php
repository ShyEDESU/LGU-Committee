<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$pageTitle = 'Notifications';

// Load NotificationHelper
require_once __DIR__ . '/../../../app/helpers/NotificationHelper.php';

// Get all notifications for the user
$userId = $_SESSION['user_id'];
$allNotifications = NotificationHelper::getUserNotifications($userId);
$unreadCount = NotificationHelper::getUnreadCount($userId);

// Filter notifications based on query parameters
$filterType = $_GET['type'] ?? 'all';
$filterStatus = $_GET['status'] ?? 'all';

$filteredNotifications = $allNotifications;

if ($filterType !== 'all') {
    $filteredNotifications = array_filter($filteredNotifications, function ($n) use ($filterType) {
        return $n['type'] === $filterType;
    });
}

if ($filterStatus === 'unread') {
    $filteredNotifications = array_filter($filteredNotifications, function ($n) {
        return !$n['is_read'];
    });
} elseif ($filterStatus === 'read') {
    $filteredNotifications = array_filter($filteredNotifications, function ($n) {
        return $n['is_read'];
    });
}

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Notifications</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Manage your notifications and alerts
                <?php if ($unreadCount > 0): ?>
                    <span
                        class="ml-2 text-sm bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full"><?php echo $unreadCount; ?>
                        unread</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="markAllAsRead()"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="bi bi-check2-all"></i>
                Mark All Read
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-100">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
            <select name="type" onchange="this.form.submit()"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-600">
                <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>All Types</option>
                <option value="meeting" <?php echo $filterType === 'meeting' ? 'selected' : ''; ?>>Meetings</option>
                <option value="action_item" <?php echo $filterType === 'action_item' ? 'selected' : ''; ?>>Action Items
                </option>
                <option value="referral" <?php echo $filterType === 'referral' ? 'selected' : ''; ?>>Referrals</option>
                <option value="document" <?php echo $filterType === 'document' ? 'selected' : ''; ?>>Documents</option>
                <option value="deadline" <?php echo $filterType === 'deadline' ? 'selected' : ''; ?>>Deadlines</option>
                <option value="system" <?php echo $filterType === 'system' ? 'selected' : ''; ?>>System</option>
                <option value="comment" <?php echo $filterType === 'comment' ? 'selected' : ''; ?>>Comments</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Status</label>
            <select name="status" onchange="this.form.submit()"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-600">
                <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All Notifications</option>
                <option value="unread" <?php echo $filterStatus === 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                <option value="read" <?php echo $filterStatus === 'read' ? 'selected' : ''; ?>>Read Only</option>
            </select>
        </div>

        <div class="flex items-end">
            <a href="index.php"
                class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <i class="bi bi-x-circle"></i>
                Clear Filters
            </a>
        </div>
    </form>
</div>

<!-- Notifications List -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-200">
    <?php if (empty($filteredNotifications)): ?>
        <div class="p-12 text-center">
            <i class="bi bi-bell-slash text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No notifications found</h3>
            <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters or check back later</p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($filteredNotifications as $notification): ?>
                <div
                    class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition <?php echo !$notification['is_read'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''; ?>">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div
                            class="bg-<?php echo $notification['color']; ?>-100 dark:bg-<?php echo $notification['color']; ?>-900/30 rounded-full p-3 flex-shrink-0">
                            <i
                                class="bi <?php echo $notification['icon']; ?> text-<?php echo $notification['color']; ?>-600 dark:text-<?php echo $notification['color']; ?>-400 text-xl"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                                New
                                            </span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-clock"></i>
                                            <?php echo NotificationHelper::timeAgo($notification['created_at']); ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-tag"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $notification['type'])); ?>
                                        </span>
                                        <?php if ($notification['priority']): ?>
                                            <span class="flex items-center gap-1">
                                                <i class="bi bi-flag-fill"></i>
                                                <?php echo ucfirst($notification['priority']); ?> Priority
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 ml-4">
                                    <?php if (!$notification['is_read']): ?>
                                        <button onclick="markAsRead(<?php echo $notification['id']; ?>)"
                                            class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                            title="Mark as read">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteNotification(<?php echo $notification['id']; ?>)"
                                        class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php if ($notification['link'] && $notification['link'] !== '#'): ?>
                                        <a href="<?php echo htmlspecialchars($notification['link']); ?>"
                                            class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                            title="View details">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Settings -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-300">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notification Preferences</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Email Notifications</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications via email</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Meeting Reminders</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get reminded before meetings</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Action Item Alerts</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Alerts for assigned tasks</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">System Updates</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Important system announcements</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                </div>
            </label>
        </div>
    </div>
</div>

<script>
    function markAsRead(notificationId) {
        // In production, make AJAX call to mark as read
        console.log('Marking notification as read:', notificationId);
        // Reload page to reflect changes
        location.reload();
    }

    function markAllAsRead() {
        if (confirm('Mark all notifications as read?')) {
            // In production, make AJAX call to mark all as read
            console.log('Marking all notifications as read');
            // Reload page to reflect changes
            location.reload();
        }
    }

    function deleteNotification(notificationId) {
        if (confirm('Delete this notification?')) {
            // In production, make AJAX call to delete
            console.log('Deleting notification:', notificationId);
            // Reload page to reflect changes
            location.reload();
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>