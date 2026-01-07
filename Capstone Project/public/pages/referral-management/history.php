<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$referralId = $_GET['id'] ?? 0;
$referral = getReferralById($referralId);

if (!$referral) {
    $_SESSION['error_message'] = 'Referral not found';
    header('Location: index.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral History - ' . $referral['title'];
include '../../includes/header.php';

// Simulated history data (in production, this would come from a history table)
$historyEvents = [
    [
        'date' => $referral['created_date'] ?? date('Y-m-d'),
        'time' => '09:00 AM',
        'action' => 'Created',
        'user' => $referral['created_by'] ?? 'System',
        'details' => 'Referral created and assigned to ' . $referral['committee_name'],
        'icon' => 'plus-circle',
        'color' => 'blue'
    ],
    [
        'date' => $referral['updated_date'] ?? date('Y-m-d'),
        'time' => '10:30 AM',
        'action' => 'Status Changed',
        'user' => $userName,
        'details' => 'Status changed to ' . $referral['status'],
        'icon' => 'arrow-repeat',
        'color' => 'purple'
    ]
];

if (!empty($referral['assigned_to'])) {
    $historyEvents[] = [
        'date' => $referral['updated_date'] ?? date('Y-m-d'),
        'time' => '11:15 AM',
        'action' => 'Assigned',
        'user' => $userName,
        'details' => 'Assigned to ' . $referral['assigned_to'],
        'icon' => 'person-plus',
        'color' => 'green'
    ];
}
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral History</h1>
            <p class="text-gray-600 mt-1">
                <?php echo htmlspecialchars($referral['title']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $referralId; ?>"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back to Referral
        </a>
    </div>
</div>

<!-- Timeline -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-bold mb-6"><i class="bi bi-clock-history mr-2"></i>Activity Timeline</h2>

    <div class="relative">
        <!-- Timeline line -->
        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>

        <!-- Timeline events -->
        <div class="space-y-6">
            <?php foreach ($historyEvents as $event): ?>
                <div class="relative flex items-start">
                    <!-- Timeline dot -->
                    <div
                        class="absolute left-8 -translate-x-1/2 w-4 h-4 rounded-full bg-<?php echo $event['color']; ?>-500 border-4 border-white">
                    </div>

                    <!-- Event content -->
                    <div class="ml-16 flex-1">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center">
                                    <i
                                        class="bi bi-<?php echo $event['icon']; ?> text-<?php echo $event['color']; ?>-500 text-xl mr-2"></i>
                                    <h3 class="font-semibold text-gray-900">
                                        <?php echo $event['action']; ?>
                                    </h3>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($event['date'])); ?> at
                                    <?php echo $event['time']; ?>
                                </span>
                            </div>
                            <p class="text-gray-700 mb-2">
                                <?php echo $event['details']; ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                <i class="bi bi-person-circle mr-1"></i>by
                                <?php echo htmlspecialchars($event['user']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Change Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4"><i class="bi bi-info-circle mr-2"></i>Current Details</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Status:</span>
                <span class="font-semibold">
                    <?php echo $referral['status']; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Priority:</span>
                <span class="font-semibold">
                    <?php echo $referral['priority']; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Assigned To:</span>
                <span class="font-semibold">
                    <?php echo $referral['assigned_to'] ?? 'Unassigned'; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4"><i class="bi bi-calendar mr-2"></i>Important Dates</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Created:</span>
                <span class="font-semibold">
                    <?php echo date('M j, Y', strtotime($referral['created_date'] ?? date('Y-m-d'))); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Last Updated:</span>
                <span class="font-semibold">
                    <?php echo date('M j, Y', strtotime($referral['updated_date'] ?? date('Y-m-d'))); ?>
                </span>
            </div>
            <?php if (!empty($referral['deadline'])): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Deadline:</span>
                    <span class="font-semibold">
                        <?php echo date('M j, Y', strtotime($referral['deadline'])); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4"><i class="bi bi-graph-up mr-2"></i>Activity Stats</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Total Changes:</span>
                <span class="font-semibold">
                    <?php echo count($historyEvents); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Last Action:</span>
                <span class="font-semibold">
                    <?php echo $historyEvents[count($historyEvents) - 1]['action']; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Days Active:</span>
                <span class="font-semibold">
                    <?php
                    $created = strtotime($referral['created_date'] ?? date('Y-m-d'));
                    $today = strtotime(date('Y-m-d'));
                    echo floor(($today - $created) / 86400);
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>