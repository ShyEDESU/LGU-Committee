<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/UserHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
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
        'notes' => $_POST['notes'] ?? '',
        'is_public' => isset($_POST['is_public']) ? 1 : 0
    ];

    $newId = createReferral($referralData);
    if ($newId) {
        $_SESSION['success_message'] = 'Referral created successfully';
        header('Location: view.php?id=' . $newId);
        exit();
    } else {
        $error = "Failed to create referral. Please try again.";
    }
}

// Get all committees for dropdown
$committees = getAllCommittees();
$users = getAllUsers();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Referral';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Referral</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Submit a new ordinance, resolution, or communication</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-x-lg"></i> Cancel
        </a>
    </div>
</div>

<form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Title -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-heading"></i> Referral Title *
            </label>
            <input type="text" name="title" required
                placeholder="e.g., Ordinance No. 2025-001 - Annual Budget Appropriation"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-tag"></i> Type *
            </label>
            <select name="type" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Type</option>
                <option value="Ordinance">Ordinance</option>
                <option value="Resolution">Resolution</option>
                <option value="Communication">Communication</option>
            </select>
        </div>

        <!-- Committee -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-building"></i> Assign to Committee *
            </label>
            <select name="committee_id" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Committee</option>
                <?php foreach ($committees as $committee): ?>
                    <option value="<?php echo $committee['id']; ?>">
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Priority -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-exclamation-triangle"></i> Priority *
            </label>
            <select name="priority" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Priority</option>
                <option value="High">High</option>
                <option value="Medium" selected>Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>

        <!-- Deadline -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-x"></i> Deadline *
            </label>
            <input type="date" name="deadline" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Submitted By -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-person"></i> Submitted By
            </label>
            <input type="text" name="submitted_by" value="<?php echo htmlspecialchars($userName); ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Date Received -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-check"></i> Date Received
            </label>
            <input type="date" name="date_received" value="<?php echo date('Y-m-d'); ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Assigned To -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-person-badge"></i> Assigned To (Optional)
            </label>
            <select name="assigned_member_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Not Assigned</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        (<?php echo htmlspecialchars($user['role_name']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-text"></i> Description *
            </label>
            <textarea name="description" rows="6" required placeholder="Detailed description of the referral..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"></textarea>
        </div>

        <!-- Notes -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-sticky"></i> Internal Notes (Optional)
            </label>
            <textarea name="notes" rows="3" placeholder="Add any internal notes or comments..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"></textarea>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">These notes are for internal use only</p>
        </div>

        <!-- Public Visibility -->
        <div class="md:col-span-2">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_public" checked
                    class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="bi bi-globe"></i> Make this referral publicly visible
                </span>
            </label>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-8">Public referrals will be visible in the public
                portal</p>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php"
            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-check-lg"></i> Create Referral
        </button>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>