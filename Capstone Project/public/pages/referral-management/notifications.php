<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Initialize notifications if not exists
if (!isset($_SESSION['referral_notifications'])) {
    $_SESSION['referral_notifications'] = [];
}

// Generate notifications based on referrals
function generateReferralNotifications()
{
    $notifications = [];
    $allReferrals = getAllReferrals();
    $today = date('Y-m-d');

    foreach ($allReferrals as $ref) {
        // Overdue notifications
        if (!empty($ref['deadline']) && $ref['deadline'] < $today && $ref['status'] !== 'Approved' && $ref['status'] !== 'Rejected') {
            $daysOverdue = floor((strtotime($today) - strtotime($ref['deadline'])) / 86400);
            $notifications[] = [
                'type' => 'overdue',
                'priority' => 'high',
                'title' => 'Overdue Referral',
                'message' => $ref['title'] . ' is ' . $daysOverdue . ' day(s) overdue',
                'referral_id' => $ref['id'],
                'date' => $today,
                'icon' => 'exclamation-triangle-fill',
                'color' => 'red'
            ];
        }

        // Deadline approaching (7 days)
        if (!empty($ref['deadline'])) {
            $daysUntil = floor((strtotime($ref['deadline']) - strtotime($today)) / 86400);
            if ($daysUntil > 0 && $daysUntil <= 7 && $ref['status'] !== 'Approved' && $ref['status'] !== 'Rejected') {
                $notifications[] = [
                    'type' => 'deadline_approaching',
                    'priority' => 'medium',
                    'title' => 'Deadline Approaching',
                    'message' => $ref['title'] . ' due in ' . $daysUntil . ' day(s)',
                    'referral_id' => $ref['id'],
                    'date' => $today,
                    'icon' => 'calendar-x',
                    'color' => 'orange'
                ];
            }
        }

        // Unassigned high priority
        if (empty($ref['assigned_to']) && $ref['priority'] === 'High') {
            $notifications[] = [
                'type' => 'unassigned',
                'priority' => 'high',
                'title' => 'Unassigned High Priority',
                'message' => $ref['title'] . ' needs assignment',
                'referral_id' => $ref['id'],
                'date' => $today,
                'icon' => 'person-exclamation',
                'color' => 'yellow'
            ];
        }
    }

    return $notifications;
}

$notifications = generateReferralNotifications();
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Notifications';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">Stay updated on referral activities</p>
        </div>
        <a href="../referral-management/index.php"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back to Referrals
        </a>
    </div>
</div>

<!-- Notification Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <?php
    $overdueCount = count(array_filter($notifications, fn($n) => $n['type'] === 'overdue'));
    $approachingCount = count(array_filter($notifications, fn($n) => $n['type'] === 'deadline_approaching'));
    $unassignedCount = count(array_filter($notifications, fn($n) => $n['type'] === 'unassigned'));
    ?>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Overdue</p>
            <i class="bi bi-exclamation-triangle-fill text-2xl text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $overdueCount; ?>
        </p>
        <p class="text-xs text-red-600 mt-1">Requires immediate attention</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Approaching</p>
            <i class="bi bi-calendar-x text-2xl text-orange-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $approachingCount; ?>
        </p>
        <p class="text-xs text-orange-600 mt-1">Due within 7 days</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Unassigned</p>
            <i class="bi bi-person-exclamation text-2xl text-yellow-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $unassignedCount; ?>
        </p>
        <p class="text-xs text-yellow-600 mt-1">High priority items</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Total</p>
            <i class="bi bi-bell-fill text-2xl text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count($notifications); ?>
        </p>
        <p class="text-xs text-red-600 mt-1">Active notifications</p>
    </div>
</div>

<!-- Notifications List -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold"><i class="bi bi-bell mr-2"></i>All Notifications</h2>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="p-8 text-center text-gray-500">
            <i class="bi bi-check-circle text-5xl mb-3 text-green-500"></i>
            <p class="text-lg font-semibold">All caught up!</p>
            <p class="text-sm mt-1">No pending notifications</p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php
            // Sort by priority (high first)
            usort($notifications, function ($a, $b) {
                $priority = ['high' => 3, 'medium' => 2, 'low' => 1];
                return ($priority[$b['priority']] ?? 0) - ($priority[$a['priority']] ?? 0);
            });

            foreach ($notifications as $notif):
                ?>
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-<?php echo $notif['color']; ?>-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-<?php echo $notif['icon']; ?> text-<?php echo $notif['color']; ?>-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($notif['title']); ?>
                                    </h3>
                                    <p class="text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        <i class="bi bi-clock mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($notif['date'])); ?>
                                    </p>
                                </div>
                                <a href="../referral-management/view.php?id=<?php echo $notif['referral_id']; ?>"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                    View Referral
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Settings -->
<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mt-6">
    <h3 class="font-semibold text-red-900 mb-2"><i class="bi bi-gear mr-2"></i>Notification Settings</h3>
    <p class="text-sm text-red-800">Configure your notification preferences in Settings to receive email alerts for
        critical updates.</p>
</div>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>