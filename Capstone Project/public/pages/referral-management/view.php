<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle quick status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_action'])) {
    $referralId = $_POST['referral_id'];
    $newStatus = $_POST['new_status'];
    $notes = $_POST['final_action'] ?? '';

    $updateData = [
        'status' => $newStatus,
        'notes' => $notes,
        'committee_id' => $_POST['committee_id'] ?? null // Need committee_id for updateReferral
    ];

    updateReferral($referralId, $updateData);
    $_SESSION['success_message'] = 'Referral status updated to ' . $newStatus;
    header('Location: view.php?id=' . $referralId);
    exit();
}

$referralId = $_GET['id'] ?? 0;
$referral = getReferralById($referralId);

if (!$referral) {
    $_SESSION['error_message'] = 'Referral not found';
    header('Location: index.php');
    exit();
}

// Get related data
$committee = getCommitteeById($referral['committee_id']);
$reports = []; // getReportsByCommittee($referral['committee_id']); - Disabling since reports module is removed
$relatedReport = null;
/*
foreach ($reports as $report) {
    if (stripos($report['title'], 'referral') !== false) {
        $relatedReport = $report;
        break;
    }
}
*/

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        updateReferral($referralId, ['status' => $_POST['update_status'], 'committee_id' => $referral['committee_id']] +
            $referral);
        $_SESSION['success_message'] = 'Status updated successfully';
        header('Location: view.php?id=' . $referralId);
        exit();
    } elseif (isset($_POST['reassign'])) {
        updateReferral($referralId, ['assigned_member_id' => $_POST['reassign'], 'committee_id' => $referral['committee_id']] +
            $referral);
        $_SESSION['success_message'] = 'Referral reassigned successfully';
        header('Location: view.php?id=' . $referralId);
        exit();
    } elseif (isset($_POST['archive_referral'])) {
        archiveReferral($referralId);
        $_SESSION['success_message'] = 'Referral has been successfully archived in the legislative records.';
        header('Location: index.php?archived=1');
        exit();
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $referral['title'];
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($referral['title']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($referral['type']); ?> -
                <?php echo htmlspecialchars($referral['committee_name']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="edit.php?id=<?php echo $referralId; ?>"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
        <p class="text-green-800"><?php echo $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?></p>
    </div>
<?php endif; ?>

<!-- Quick Actions for Status Change (for authorized users) -->
<?php if ($referral['status'] !== 'Approved' && $referral['status'] !== 'Rejected'): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-6 rounded-lg">
        <h3 class="font-bold text-red-900 mb-3"><i class="bi bi-lightning-charge-fill mr-2"></i>Quick Actions (Authorized
            Users)</h3>
        <p class="text-sm text-red-800 mb-4">City Hall President, Committee Chairperson, or authorized staff can take
            action:</p>
        <div class="flex flex-wrap gap-3">
            <form method="POST" class="inline">
                <input type="hidden" name="quick_action" value="1">
                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                <input type="hidden" name="committee_id" value="<?php echo $referral['committee_id']; ?>">
                <input type="hidden" name="new_status" value="Approved">
                <input type="hidden" name="final_action" value="Approved by authorized user">
                <button type="submit" onclick="return confirm('Approve this referral?')"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    <i class="bi bi-check-circle-fill mr-2"></i>Approve
                </button>
            </form>

            <form method="POST" class="inline">
                <input type="hidden" name="quick_action" value="1">
                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                <input type="hidden" name="committee_id" value="<?php echo $referral['committee_id']; ?>">
                <input type="hidden" name="new_status" value="Rejected">
                <input type="hidden" name="final_action" value="Rejected by authorized user">
                <button type="submit" onclick="return confirm('Reject this referral?')"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    <i class="bi bi-x-circle-fill mr-2"></i>Reject
                </button>
            </form>

            <form method="POST" class="inline">
                <input type="hidden" name="quick_action" value="1">
                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                <input type="hidden" name="new_status" value="Deferred">
                <button type="submit" onclick="return confirm('Defer this referral?')"
                    class="px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-semibold">
                    <i class="bi bi-pause-circle-fill mr-2"></i>Defer
                </button>
            </form>

            <form method="POST" class="inline">
                <input type="hidden" name="quick_action" value="1">
                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                <input type="hidden" name="new_status" value="Under Review">
                <button type="submit"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    <i class="bi bi-eye-fill mr-2"></i>Move to Review
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Referrals
        </a>
        <a href="tracking.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
        <a href="assign.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assignment
        </a>
        <a href="deadlines.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Referral Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <?php echo htmlspecialchars($referral['type'] ?? 'Communication'); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Priority</p>
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full 
                        <?php echo $referral['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                            ($referral['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo htmlspecialchars($referral['priority']); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full 
                        <?php echo $referral['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                            ($referral['status'] === 'Under Review' ? 'bg-red-100 text-red-800' :
                                ($referral['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800')); ?>">
                        <?php echo htmlspecialchars($referral['status']); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Deadline</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo !empty($referral['deadline']) ? date('M j, Y', strtotime($referral['deadline'])) : 'No deadline'; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Committee</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <a href="../committee-profiles/view.php?id=<?php echo $referral['committee_id']; ?>"
                            class="text-red-600 hover:text-red-700">
                            <?php echo htmlspecialchars($referral['committee_name']); ?>
                        </a>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Assigned To</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($referral['assigned_to'] ?? 'Not assigned'); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted By</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($referral['submitted_by'] ?? 'Unknown'); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date Received</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo isset($referral['date_received']) ? date('M j, Y', strtotime($referral['date_received'])) : 'N/A'; ?>
                    </p>
                </div>

                <?php if (!empty($referral['meeting_id'])):
                    $meeting = getMeetingById($referral['meeting_id']);
                    ?>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Linked Meeting</p>
                        <a href="../committee-meetings/view.php?id=<?php echo $referral['meeting_id']; ?>"
                            class="inline-flex items-center px-4 py-2 bg-red-50 text-blue-700 rounded-lg hover:bg-red-100 transition">
                            <i class="bi bi-calendar-event mr-2"></i>
                            <?php echo htmlspecialchars($meeting['title'] ?? 'Meeting #' . $referral['meeting_id']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                    <p class="text-gray-900 dark:text-white">
                        <?php echo !empty($referral['description']) ? nl2br(htmlspecialchars($referral['description'])) : 'No description provided'; ?>
                    </p>
                </div>

                <?php if (!empty($referral['notes'])): ?>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Notes</p>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <p class="text-gray-900">
                                <?php echo nl2br(htmlspecialchars($referral['notes'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="edit.php?id=<?php echo $referralId; ?>"
                    class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-sm flex items-center justify-center">
                    <i class="bi bi-pencil mr-2"></i> Edit Referral
                </a>
                <button onclick="confirmArchive()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                    <i class="bi bi-archive mr-2"></i> Archive Referral
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Confirmation Modal -->
<div id="archiveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl mx-4">
        <div class="flex items-center mb-4 text-red-600">
            <i class="bi bi-exclamation-triangle-fill text-3xl mr-3"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Confirm Archiving</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Are you sure you want to archive this referral? It will be preserved in the legislative records but removed
            from active deliberation.
        </p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeArchiveModal()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                Cancel
            </button>
            <form method="POST">
                <input type="hidden" name="archive_referral" value="1">
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold">
                    Archive
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmArchive() {
        document.getElementById('archiveModal').classList.remove('hidden');
    }

    function closeArchiveModal() {
        document.getElementById('archiveModal').classList.add('hidden');
    }
</script>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Referral Status: <span class="font-medium"><?php echo htmlspecialchars($referral['status']); ?></span>
    </div>
    <div class="text-sm text-gray-500 italic">
        Last Updated: <?php echo date('M j, Y', strtotime($referral['updated_at'] ?? $referral['created_at'])); ?>
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>