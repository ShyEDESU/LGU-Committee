<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/UserHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$referralId = $_GET['id'] ?? 0;
$referral = getReferralById($referralId);

if (!$referral) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referralData = [
        'committee_id' => $_POST['committee_id'],
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'description' => $_POST['description'],
        'priority' => $_POST['priority'],
        'deadline' => $_POST['deadline'],
        'assigned_member_id' => !empty($_POST['assigned_member_id']) ? $_POST['assigned_member_id'] : null,
        'status' => $_POST['status'],
        'notes' => $_POST['notes'] ?? '',
        'is_public' => isset($_POST['is_public']) ? 1 : 0
    ];

    if (updateReferral($referralId, $referralData)) {
        $_SESSION['success_message'] = 'Referral updated successfully';
        header('Location: view.php?id=' . $referralId);
        exit();
    } else {
        $error = "Failed to update referral. Please try again.";
    }
}

// Get all committees for dropdown
$committees = getAllCommittees();
$users = UserHelper_getAllUsers();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Referral';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Referral</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($referral['title']); ?></p>
        </div>
        <a href="view.php?id=<?php echo $referralId; ?>"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-x-lg"></i> Cancel
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Referrals
        </a>
        <a href="view.php?id=<?php echo $referralId; ?>"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-eye"></i> View
        </a>
        <a href="tracking.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
    </div>
</div>

<form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-heading"></i> Referral Title *
            </label>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($referral['title']); ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-tag"></i> Type *
            </label>
            <select name="type" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Type</option>
                <option value="Ordinance" <?php echo ($referral['type'] ?? '') === 'Ordinance' ? 'selected' : ''; ?>>
                    Ordinance</option>
                <option value="Resolution" <?php echo ($referral['type'] ?? '') === 'Resolution' ? 'selected' : ''; ?>>
                    Resolution</option>
                <option value="Communication" <?php echo ($referral['type'] ?? '') === 'Communication' ? 'selected' : ''; ?>>Communication</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-building"></i> Assign to Committee *
            </label>
            <select name="committee_id" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Committee</option>
                <?php foreach ($committees as $committee): ?>
                    <option value="<?php echo $committee['id']; ?>" <?php echo $referral['committee_id'] == $committee['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-exclamation-triangle"></i> Priority *
            </label>
            <select name="priority" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Priority</option>
                <option value="High" <?php echo $referral['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo $referral['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo $referral['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-circle-fill"></i> Status *
            </label>
            <select name="status" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Status</option>
                <option value="Pending" <?php echo $referral['status'] === 'Pending' ? 'selected' : ''; ?>>Pending
                </option>
                <option value="Under Review" <?php echo $referral['status'] === 'Under Review' ? 'selected' : ''; ?>>Under
                    Review</option>
                <option value="Approved" <?php echo $referral['status'] === 'Approved' ? 'selected' : ''; ?>>Approved
                </option>
                <option value="Rejected" <?php echo $referral['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected
                </option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-x"></i> Deadline *
            </label>
            <input type="date" name="deadline" required value="<?php echo $referral['deadline']; ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-person-badge"></i> Assigned To (Optional)
            </label>
            <select name="assigned_member_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Not Assigned</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php echo ($referral['assigned_member_id'] ?? '') == $user['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        (<?php echo htmlspecialchars($user['role_name']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-text"></i> Description *
            </label>
            <textarea name="description" rows="6" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($referral['description']); ?></textarea>
        </div>

        <!-- Notes -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-sticky"></i> Internal Notes
            </label>
            <textarea name="notes" rows="3" placeholder="Add any internal notes or comments..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($referral['notes'] ?? ''); ?></textarea>
        </div>

        <!-- Public Visibility -->
        <div class="md:col-span-2">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_public" <?php echo ($referral['is_public'] ?? true) ? 'checked' : ''; ?>
                    class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="bi bi-globe"></i> Make this referral publicly visible
                </span>
            </label>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="view.php?id=<?php echo $referralId; ?>"
            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-check-lg"></i> Update Referral
        </button>
    </div>
</form>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>