<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee History';
include '../../includes/header.php';

// Hardcoded history/timeline data
$history = [
    ['date' => '2024-12-10', 'type' => 'meeting', 'title' => '2025 Budget Review Meeting', 'committee' => 'Finance', 'description' => 'Reviewed and approved the 2025 annual budget proposal'],
    ['date' => '2024-11-25', 'type' => 'document', 'title' => 'Meeting Minutes Uploaded', 'committee' => 'Finance', 'description' => 'November 2024 meeting minutes uploaded by Secretary'],
    ['date' => '2024-11-20', 'type' => 'member', 'title' => 'New Member Added', 'committee' => 'Finance', 'description' => 'Hon. Carlos Mendoza joined as committee member'],
    ['date' => '2024-10-15', 'type' => 'meeting', 'title' => 'Quarterly Financial Report', 'committee' => 'Finance', 'description' => 'Presented Q3 2024 financial performance'],
    ['date' => '2024-09-05', 'type' => 'referral', 'title' => 'Ordinance No. 2024-001 Referred', 'committee' => 'Finance', 'description' => 'Annual budget ordinance referred for review'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee History</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Timeline of committee activities and events</p>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Total Events</p>
                <p class="text-3xl font-bold mt-1"><?php echo count($history); ?></p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-calendar-event text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Meetings</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'meeting')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-calendar-check text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Documents</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'document')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-file-earmark text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Referrals</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'referral')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-inbox text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Timeline -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Activity Timeline</h2>
    <div class="space-y-6">
        <?php foreach ($history as $event): ?>
        <div class="flex">
            <div class="flex flex-col items-center mr-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    <?php echo $event['type'] === 'meeting' ? 'bg-blue-500' : 
                               ($event['type'] === 'document' ? 'bg-green-500' : 
                               ($event['type'] === 'member' ? 'bg-purple-500' : 'bg-orange-500')); ?> text-white">
                    <i class="bi bi-<?php echo $event['type'] === 'meeting' ? 'calendar-check' : 
                                             ($event['type'] === 'document' ? 'file-earmark' : 
                                             ($event['type'] === 'member' ? 'person-plus' : 'inbox')); ?> text-xl"></i>
                </div>
                <?php if ($event !== end($history)): ?>
                <div class="w-0.5 h-full bg-gray-300 dark:bg-gray-600 mt-2"></div>
                <?php endif; ?>
            </div>
            <div class="flex-1 pb-8">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo date('M j, Y', strtotime($event['date'])); ?></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"><?php echo htmlspecialchars($event['description']); ?></p>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        <?php echo $event['committee']; ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
