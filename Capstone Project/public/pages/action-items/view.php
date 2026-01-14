<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get action item ID
$itemId = $_GET['id'] ?? 0;
$item = getActionItemById($itemId);

if (!$item) {
    header('Location: index.php');
    exit();
}

// Get related items
$committee = !empty($item['committee_id']) ? getCommitteeById($item['committee_id']) : null;
$meeting = !empty($item['meeting_id']) ? getMeetingById($item['meeting_id']) : null;
$referral = !empty($item['referral_id']) ? getReferralById($item['referral_id']) : null;

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Item Details';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['title']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <i class="bi bi-person"></i> Assigned to <?php echo htmlspecialchars($item['assigned_to']); ?>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="edit.php?id=<?php echo $item['id']; ?>"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Task Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-info-circle mr-2"></i>Task Information
            </h2>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full 
                        <?php echo ($item['status'] ?? '') === 'Done' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                            (($item['status'] ?? '') === 'In Progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'); ?>">
                        <?php echo htmlspecialchars($item['status'] ?? 'To Do'); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Priority</p>
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full 
                        <?php echo ($item['priority'] ?? '') === 'High' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                            (($item['priority'] ?? '') === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'); ?>">
                        <?php echo htmlspecialchars($item['priority'] ?? 'Medium'); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Category</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($item['category'] ?? 'General'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Due Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo !empty($item['due_date']) ? date('M j, Y', strtotime($item['due_date'])) : 'Not set'; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Created By</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($item['created_by'] ?? 'System'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Created Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo !empty($item['created_date']) ? date('M j, Y', strtotime($item['created_date'])) : 'N/A'; ?>
                    </p>
                </div>
                <?php if (!empty($item['completed_date'])): ?>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Completed Date</p>
                        <p class="font-semibold text-green-600 dark:text-green-400">
                            <i class="bi bi-check-circle mr-1"></i>
                            <?php echo date('M j, Y g:i A', strtotime($item['completed_date'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Progress</p>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-6">
                        <div class="bg-blue-600 h-6 rounded-full flex items-center justify-center text-white text-sm font-bold transition-all"
                            style="width: <?php echo ($item['progress'] ?? 0); ?>%">
                            <?php echo ($item['progress'] ?? 0); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-card-text mr-2"></i>Description
            </h2>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                <?php echo htmlspecialchars($item['description'] ?? 'No description provided.'); ?></p>
        </div>

        <!-- Time Tracking -->
        <?php if (!empty($item['estimated_hours']) || !empty($item['actual_hours'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-clock-history mr-2"></i>Time Tracking
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Estimated Hours</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo ($item['estimated_hours'] ?? 0); ?>h</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Actual Hours</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo ($item['actual_hours'] ?? 0); ?>h</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Internal Notes -->
        <?php if (!empty($item['notes'])): ?>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-6">
                <h2 class="text-xl font-bold text-yellow-900 dark:text-yellow-300 mb-4">
                    <i class="bi bi-sticky mr-2"></i>Internal Notes
                </h2>
                <p class="text-yellow-800 dark:text-yellow-200 whitespace-pre-wrap">
                    <?php echo htmlspecialchars($item['notes']); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-lightning mr-2"></i>Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="edit.php?id=<?php echo $item['id']; ?>"
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm flex items-center justify-center">
                    <i class="bi bi-pencil mr-2"></i>Edit Details
                </a>
                <?php if (($item['status'] ?? '') !== 'Done'): ?>
                    <form method="POST" action="index.php" class="w-full">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="new_status" value="Done">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition text-sm">
                            <i class="bi bi-check-circle mr-2"></i>Mark Complete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tags -->
        <?php if (!empty($item['tags']) && is_array($item['tags'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-tags mr-2"></i>Tags
                </h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($item['tags'] as $tag): ?>
                        <span
                            class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-sm rounded-full">
                            <?php echo htmlspecialchars($tag); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Related Items -->
        <?php if ($committee || $meeting || $referral): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-link-45deg mr-2"></i>Related Items
                </h3>
                <div class="space-y-3">
                    <?php if ($committee): ?>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Committee</p>
                            <a href="../committee-profiles/view.php?id=<?php echo $committee['id']; ?>"
                                class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                <i class="bi bi-building mr-1"></i><?php echo htmlspecialchars($committee['name']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($meeting): ?>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Meeting</p>
                            <a href="../committee-meetings/view.php?id=<?php echo $meeting['id']; ?>"
                                class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                <i class="bi bi-calendar-event mr-1"></i><?php echo htmlspecialchars($meeting['title']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($referral): ?>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Referral</p>
                            <a href="../referral-management/view.php?id=<?php echo $referral['id']; ?>"
                                class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                <i class="bi bi-file-earmark-text mr-1"></i><?php echo htmlspecialchars($referral['title']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>