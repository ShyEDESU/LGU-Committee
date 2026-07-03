<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Deadlines';
include '../../includes/header.php';

// Get all referrals
$allReferrals = getAllReferrals();

// Get overdue referrals
$overdueReferrals = getOverdueReferrals();

// Get upcoming deadlines (next 7, 14, 30 days)
$today = date('Y-m-d');
$next7Days = date('Y-m-d', strtotime('+7 days'));
$next14Days = date('Y-m-d', strtotime('+14 days'));
$next30Days = date('Y-m-d', strtotime('+30 days'));

$upcoming7Days = getReferralsByDeadline($today, $next7Days);
$upcoming14Days = getReferralsByDeadline($today, $next14Days);
$upcoming30Days = getReferralsByDeadline($today, $next30Days);
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Deadlines</h1>
            <p class="text-gray-600 mt-1">Monitor and manage referral deadlines</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back to All Referrals
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-list"></i> All Referrals
        </a>
        <a href="tracking.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-person-plus"></i> Assignment
        </a>
        <a href="deadlines.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Overdue</p>
            <i class="bi bi-exclamation-triangle text-2xl text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count($overdueReferrals); ?>
        </p>
        <p class="text-xs text-red-600 mt-1">Requires immediate attention</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Next 7 Days</p>
            <i class="bi bi-calendar-week text-2xl text-orange-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count($upcoming7Days); ?>
        </p>
        <p class="text-xs text-orange-600 mt-1">Due this week</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Next 14 Days</p>
            <i class="bi bi-calendar2-week text-2xl text-yellow-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count($upcoming14Days); ?>
        </p>
        <p class="text-xs text-yellow-600 mt-1">Due in 2 weeks</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Next 30 Days</p>
            <i class="bi bi-calendar-month text-2xl text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count($upcoming30Days); ?>
        </p>
        <p class="text-xs text-red-600 mt-1">Due this month</p>
    </div>
</div>

<!-- Overdue Referrals Alert -->
<?php if (!empty($overdueReferrals)): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-6 rounded-lg">
        <div class="flex items-start">
            <i class="bi bi-exclamation-triangle-fill text-red-500 text-2xl mr-3"></i>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-red-800 mb-2">
                    <i class="bi bi-exclamation-circle"></i> Overdue Referrals Require Attention
                </h3>
                <p class="text-red-700 mb-4">
                    You have
                    <?php echo count($overdueReferrals); ?> referral(s) that have passed their deadline.
                </p>
                <div class="space-y-2">
                    <?php foreach ($overdueReferrals as $referral):
                        $daysOverdue = floor((strtotime($today) - strtotime($referral['deadline'])) / 86400);
                        ?>
                        <div class="bg-white p-4 rounded-lg border border-red-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($referral['title']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Committee:</span>
                                        <?php echo htmlspecialchars($referral['committee_name']); ?> |
                                        <span class="font-medium">Deadline:</span>
                                        <?php echo date('M j, Y', strtotime($referral['deadline'])); ?>
                                        <span class="text-red-600 font-semibold">(
                                            <?php echo $daysOverdue; ?> days overdue)
                                        </span>
                                    </p>
                                </div>
                                <a href="view.php?id=<?php echo $referral['id']; ?>"
                                    class="ml-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Upcoming Deadlines -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Next 7 Days -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-orange-500 text-white p-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="bi bi-calendar-week mr-2"></i>
                Next 7 Days
            </h3>
        </div>
        <div class="p-4">
            <?php if (empty($upcoming7Days)): ?>
                <p class="text-gray-500 text-center py-8">
                    <i class="bi bi-check-circle text-4xl mb-2"></i><br>
                    No deadlines this week
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($upcoming7Days as $referral):
                        $daysLeft = floor((strtotime($referral['deadline']) - strtotime($today)) / 86400);
                        ?>
                        <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition">
                            <h4 class="font-semibold text-sm text-gray-900 mb-1">
                                <?php echo htmlspecialchars($referral['title']); ?>
                            </h4>
                            <p class="text-xs text-gray-600 mb-2">
                                <?php echo htmlspecialchars($referral['committee_name']); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-orange-600">
                                    <i class="bi bi-clock"></i>
                                    <?php echo $daysLeft; ?> day(s) left
                                </span>
                                <a href="view.php?id=<?php echo $referral['id']; ?>"
                                    class="text-xs text-red-600 hover:text-red-700">
                                    View →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Next 14 Days -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-yellow-500 text-white p-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="bi bi-calendar2-week mr-2"></i>
                Next 14 Days
            </h3>
        </div>
        <div class="p-4">
            <?php if (empty($upcoming14Days)): ?>
                <p class="text-gray-500 text-center py-8">
                    <i class="bi bi-check-circle text-4xl mb-2"></i><br>
                    No deadlines in 2 weeks
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($upcoming14Days as $referral):
                        $daysLeft = floor((strtotime($referral['deadline']) - strtotime($today)) / 86400);
                        ?>
                        <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition">
                            <h4 class="font-semibold text-sm text-gray-900 mb-1">
                                <?php echo htmlspecialchars($referral['title']); ?>
                            </h4>
                            <p class="text-xs text-gray-600 mb-2">
                                <?php echo htmlspecialchars($referral['committee_name']); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-yellow-600">
                                    <i class="bi bi-clock"></i>
                                    <?php echo $daysLeft; ?> day(s) left
                                </span>
                                <a href="view.php?id=<?php echo $referral['id']; ?>"
                                    class="text-xs text-red-600 hover:text-red-700">
                                    View →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Next 30 Days -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-red-500 text-white p-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="bi bi-calendar-month mr-2"></i>
                Next 30 Days
            </h3>
        </div>
        <div class="p-4">
            <?php if (empty($upcoming30Days)): ?>
                <p class="text-gray-500 text-center py-8">
                    <i class="bi bi-check-circle text-4xl mb-2"></i><br>
                    No deadlines this month
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($upcoming30Days as $referral):
                        $daysLeft = floor((strtotime($referral['deadline']) - strtotime($today)) / 86400);
                        ?>
                        <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition">
                            <h4 class="font-semibold text-sm text-gray-900 mb-1">
                                <?php echo htmlspecialchars($referral['title']); ?>
                            </h4>
                            <p class="text-xs text-gray-600 mb-2">
                                <?php echo htmlspecialchars($referral['committee_name']); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-red-600">
                                    <i class="bi bi-clock"></i>
                                    <?php echo $daysLeft; ?> day(s) left
                                </span>
                                <a href="view.php?id=<?php echo $referral['id']; ?>"
                                    class="text-xs text-red-600 hover:text-red-700">
                                    View →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span
            class="font-medium"><?php echo count($overdueReferrals) + count($upcoming14Days) + count($upcoming30Days); ?></span>
        of
        <span
            class="font-medium"><?php echo count($overdueReferrals) + count($upcoming14Days) + count($upcoming30Days); ?></span>
        record(s) with deadlines
    </div>
    <div class="text-sm text-gray-500 italic">
        Module: Deadlines
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>