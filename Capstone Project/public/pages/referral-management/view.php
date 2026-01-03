<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$referralId = $_GET['id'] ?? 1;
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Details';
include '../../includes/header.php';

$referral = [
    'id' => $referralId,
    'title' => 'Ordinance No. 2024-001 - Annual Budget Appropriation',
    'type' => 'Ordinance',
    'committee' => 'Finance',
    'deadline' => '2024-12-20',
    'status' => 'In Progress',
    'priority' => 'High',
    'description' => 'Annual budget appropriation for fiscal year 2025 including all departmental allocations and capital expenditures.',
    'submitted_by' => 'Hon. Maria Santos',
    'submitted_date' => '2024-12-01',
    'assigned_date' => '2024-12-02',
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($referral['title']); ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo $referral['type']; ?> - <?php echo $referral['committee']; ?> Committee</p>
        </div>
        <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Referrals
        </a>
        <a href="tracking.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assignment
        </a>
        <a href="create.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
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
                        <?php echo $referral['type']; ?>
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
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $referral['submitted_by']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Submitted Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo date('M j, Y', strtotime($referral['submitted_date'])); ?>
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                    <p class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($referral['description']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <button onclick="updateStatus()" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </button>
                <button onclick="reassign()" class="w-full px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition text-sm">
                    <i class="bi bi-person-plus"></i> Reassign
                </button>
                <button onclick="addNote()" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-sm">
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

