<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $referralId = $_POST['referral_id'];
    $newStatus = $_POST['new_status'];
    updateReferral($referralId, ['status' => $newStatus]);
    $_SESSION['success_message'] = 'Status updated successfully';
    header('Location: tracking.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Tracking';
include '../../includes/header.php';

// Get all referrals grouped by status
$allReferrals = getAllReferrals();
$pendingReferrals = getReferralsByStatus('Pending');
$underReviewReferrals = getReferralsByStatus('Under Review');
$inCommitteeReferrals = getReferralsByStatus('In Committee');
$approvedReferrals = getReferralsByStatus('Approved');
$rejectedReferrals = getReferralsByStatus('Rejected');
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Tracking</h1>
            <p class="text-gray-600 mt-1">Track referral workflow and status changes</p>
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
        <a href="tracking.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-person-plus"></i> Assignment
        </a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
    </div>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded-lg">
        <p class="text-sm text-gray-600">Pending</p>
        <p class="text-2xl font-bold text-gray-900"><?php echo count($pendingReferrals); ?></p>
    </div>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <p class="text-sm text-blue-600">Under Review</p>
        <p class="text-2xl font-bold text-blue-900"><?php echo count($underReviewReferrals); ?></p>
    </div>
    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
        <p class="text-sm text-purple-600">In Committee</p>
        <p class="text-2xl font-bold text-purple-900"><?php echo count($inCommitteeReferrals); ?></p>
    </div>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-600">Approved</p>
        <p class="text-2xl font-bold text-green-900"><?php echo count($approvedReferrals); ?></p>
    </div>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm text-red-600">Rejected</p>
        <p class="text-2xl font-bold text-red-900"><?php echo count($rejectedReferrals); ?></p>
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <!-- Pending Column -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gray-500 text-white p-3">
            <h3 class="font-bold flex items-center justify-between">
                <span><i class="bi bi-inbox mr-2"></i>Pending</span>
                <span class="bg-gray-600 px-2 py-1 rounded-full text-sm"><?php echo count($pendingReferrals); ?></span>
            </h3>
        </div>
        <div class="p-3 space-y-2 max-h-96 overflow-y-auto">
            <?php if (empty($pendingReferrals)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No pending referrals</p>
            <?php else: ?>
                <?php foreach ($pendingReferrals as $ref): ?>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 hover:shadow-md transition cursor-pointer"
                        onclick="showStatusModal(<?php echo $ref['id']; ?>, '<?php echo htmlspecialchars($ref['title'], ENT_QUOTES); ?>', 'Pending')">
                        <h4 class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($ref['title']); ?></h4>
                        <p class="text-xs text-gray-600 mb-2"><?php echo htmlspecialchars($ref['committee_name']); ?></p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                    ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo $ref['priority']; ?>
                            </span>
                            <?php if (!empty($ref['deadline'])): ?>
                                <span class="text-xs text-gray-500">
                                    <i class="bi bi-calendar-x"></i> <?php echo date('M j', strtotime($ref['deadline'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Under Review Column -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-blue-500 text-white p-3">
            <h3 class="font-bold flex items-center justify-between">
                <span><i class="bi bi-eye mr-2"></i>Under Review</span>
                <span
                    class="bg-blue-600 px-2 py-1 rounded-full text-sm"><?php echo count($underReviewReferrals); ?></span>
            </h3>
        </div>
        <div class="p-3 space-y-2 max-h-96 overflow-y-auto">
            <?php if (empty($underReviewReferrals)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No referrals under review</p>
            <?php else: ?>
                <?php foreach ($underReviewReferrals as $ref): ?>
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-200 hover:shadow-md transition cursor-pointer"
                        onclick="showStatusModal(<?php echo $ref['id']; ?>, '<?php echo htmlspecialchars($ref['title'], ENT_QUOTES); ?>', 'Under Review')">
                        <h4 class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($ref['title']); ?></h4>
                        <p class="text-xs text-gray-600 mb-2"><?php echo htmlspecialchars($ref['committee_name']); ?></p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                    ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo $ref['priority']; ?>
                            </span>
                            <?php if (!empty($ref['deadline'])): ?>
                                <span class="text-xs text-gray-500">
                                    <i class="bi bi-calendar-x"></i> <?php echo date('M j', strtotime($ref['deadline'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- In Committee Column -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-purple-500 text-white p-3">
            <h3 class="font-bold flex items-center justify-between">
                <span><i class="bi bi-people mr-2"></i>In Committee</span>
                <span
                    class="bg-purple-600 px-2 py-1 rounded-full text-sm"><?php echo count($inCommitteeReferrals); ?></span>
            </h3>
        </div>
        <div class="p-3 space-y-2 max-h-96 overflow-y-auto">
            <?php if (empty($inCommitteeReferrals)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No referrals in committee</p>
            <?php else: ?>
                <?php foreach ($inCommitteeReferrals as $ref): ?>
                    <div class="bg-purple-50 p-3 rounded-lg border border-purple-200 hover:shadow-md transition cursor-pointer"
                        onclick="showStatusModal(<?php echo $ref['id']; ?>, '<?php echo htmlspecialchars($ref['title'], ENT_QUOTES); ?>', 'In Committee')">
                        <h4 class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($ref['title']); ?></h4>
                        <p class="text-xs text-gray-600 mb-2"><?php echo htmlspecialchars($ref['committee_name']); ?></p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                    ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo $ref['priority']; ?>
                            </span>
                            <?php if (!empty($ref['deadline'])): ?>
                                <span class="text-xs text-gray-500">
                                    <i class="bi bi-calendar-x"></i> <?php echo date('M j', strtotime($ref['deadline'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approved Column -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-green-500 text-white p-3">
            <h3 class="font-bold flex items-center justify-between">
                <span><i class="bi bi-check-circle mr-2"></i>Approved</span>
                <span
                    class="bg-green-600 px-2 py-1 rounded-full text-sm"><?php echo count($approvedReferrals); ?></span>
            </h3>
        </div>
        <div class="p-3 space-y-2 max-h-96 overflow-y-auto">
            <?php if (empty($approvedReferrals)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No approved referrals</p>
            <?php else: ?>
                <?php foreach ($approvedReferrals as $ref): ?>
                    <div class="bg-green-50 p-3 rounded-lg border border-green-200 hover:shadow-md transition cursor-pointer"
                        onclick="window.location.href='view.php?id=<?php echo $ref['id']; ?>'">
                        <h4 class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($ref['title']); ?></h4>
                        <p class="text-xs text-gray-600 mb-2"><?php echo htmlspecialchars($ref['committee_name']); ?></p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                    ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo $ref['priority']; ?>
                            </span>
                            <?php if (!empty($ref['final_action_date'])): ?>
                                <span class="text-xs text-gray-500">
                                    <i class="bi bi-calendar-check"></i>
                                    <?php echo date('M j', strtotime($ref['final_action_date'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejected Column -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-red-500 text-white p-3">
            <h3 class="font-bold flex items-center justify-between">
                <span><i class="bi bi-x-circle mr-2"></i>Rejected</span>
                <span class="bg-red-600 px-2 py-1 rounded-full text-sm"><?php echo count($rejectedReferrals); ?></span>
            </h3>
        </div>
        <div class="p-3 space-y-2 max-h-96 overflow-y-auto">
            <?php if (empty($rejectedReferrals)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No rejected referrals</p>
            <?php else: ?>
                <?php foreach ($rejectedReferrals as $ref): ?>
                    <div class="bg-red-50 p-3 rounded-lg border border-red-200 hover:shadow-md transition cursor-pointer"
                        onclick="window.location.href='view.php?id=<?php echo $ref['id']; ?>'">
                        <h4 class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($ref['title']); ?></h4>
                        <p class="text-xs text-gray-600 mb-2"><?php echo htmlspecialchars($ref['committee_name']); ?></p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                    ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                <?php echo $ref['priority']; ?>
                            </span>
                            <?php if (!empty($ref['final_action_date'])): ?>
                                <span class="text-xs text-gray-500">
                                    <i class="bi bi-calendar-check"></i>
                                    <?php echo date('M j', strtotime($ref['final_action_date'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
        <h3 class="text-xl font-bold mb-4">Update Referral Status</h3>
        <p id="modalReferralTitle" class="text-gray-600 mb-4"></p>
        <form method="POST">
            <input type="hidden" name="referral_id" id="modalReferralId">
            <input type="hidden" name="update_status" value="1">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                <select name="new_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="Pending">Pending</option>
                    <option value="Under Review">Under Review</option>
                    <option value="In Committee">In Committee</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Deferred">Deferred</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStatusModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showStatusModal(id, title, currentStatus) {
        document.getElementById('statusModal').classList.remove('hidden');
        document.getElementById('modalReferralId').value = id;
        document.getElementById('modalReferralTitle').textContent = title;
        document.querySelector('select[name="new_status"]').value = currentStatus;
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
</script>

<?php include '../../includes/footer.php'; ?>