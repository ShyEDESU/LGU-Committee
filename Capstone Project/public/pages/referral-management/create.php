<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

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
        <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-x-lg"></i> Cancel
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
        <a href="create.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
            <i class="bi bi-plus-lg"></i> Create
        </a>
    </div>
</div>

<form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-heading"></i> Referral Title *
            </label>
            <input type="text" name="title" required placeholder="e.g., Ordinance No. 2024-001 - Annual Budget"
                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-tag"></i> Type *
            </label>
            <select name="type" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                <option value="">Select Type</option>
                <option value="Ordinance">Ordinance</option>
                <option value="Resolution">Resolution</option>
                <option value="Communication">Communication</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-building"></i> Assign to Committee *
            </label>
            <select name="committee" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                <option value="">Select Committee</option>
                <option value="Finance">Finance</option>
                <option value="Health">Health</option>
                <option value="Education">Education</option>
                <option value="Infrastructure">Infrastructure</option>
                <option value="Public Safety">Public Safety</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-exclamation-triangle"></i> Priority *
            </label>
            <select name="priority" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                <option value="">Select Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-x"></i> Deadline *
            </label>
            <input type="date" name="deadline" required
                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-text"></i> Description *
            </label>
            <textarea name="description" rows="6" required placeholder="Detailed description of the referral..."
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-paperclip"></i> Attachments
            </label>
            <input type="file" name="attachments[]" multiple
                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">You can upload multiple files</p>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php" class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
            <i class="bi bi-check-lg"></i> Create Referral
        </button>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>
