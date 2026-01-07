<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$referralId = $_GET['id'] ?? 1;
$referral = getReferralById($referralId);

if (!$referral) {
    $_SESSION['error_message'] = 'Referral not found';
    header('Location: index.php');
    exit();
}

// Get related data
$committee = getCommitteeById($referral['committee_id']);
$reports = getReportsByCommittee($referral['committee_id']);
$relatedReport = null;
foreach ($reports as $report) {
    if (stripos($report['title'], 'referral') !== false) {
        $relatedReport = $report;
        break;
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        updateReferral($referralId, ['status' => $_POST['update_status']] + $referral);
        $_SESSION['success_message'] = 'Status updated successfully';
        header('Location: view.php?id=' . $referralId);
        exit();
    } elseif (isset($_POST['reassign'])) {
        updateReferral($referralId, ['assigned_to' => $_POST['reassign']] + $referral);
        $_SESSION['success_message'] = 'Referral reassigned successfully';
        header('Location: view.php?id=' . $referralId);
        exit();
    } elseif (isset($_POST['delete'])) {
        deleteReferral($referralId);
        $_SESSION['success_message'] = 'Referral deleted successfully';
        header('Location: index.php');
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
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo $referral['type']; ?> -
                <?php echo $referral['committee']; ?> Committee
            </p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

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
        <a href="create.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-plus-lg"></i> Create
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
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <?php echo htmlspecialchars($referral['type'] ?? 'N/A'); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Priority</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <?php echo $referral['priority']; ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <?php echo $referral['status']; ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Deadline</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo date('M j, Y', strtotime($referral['deadline'])); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted By</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($referral['submitted_by'] ?? 'Unknown'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo isset($referral['submitted_date']) ? date('M j, Y', strtotime($referral['submitted_date'])) : 'N/A'; ?>
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                    <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($referral['description']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <button onclick="updateStatus()"
                    class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </button>
                <button onclick="reassign()"
                    class="w-full px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-person-plus"></i> Reassign
                </button>
                <button onclick="addNote()"
                    class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-chat-left-text"></i> Add Note
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateStatus() { alert('Update status'); }
    function reassign() { alert('Reassign referral'); }
    function addNote() { alert('Add note'); }
</script>

<?php include '../../includes/footer.php'; ?>