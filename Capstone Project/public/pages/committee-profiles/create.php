<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Committee';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real implementation, this would save to database
    // For now, just redirect back to index
    header('Location: index.php');
    exit();
}

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Committee</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new committee to the system</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<!-- Create Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Committee Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Committee Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    placeholder="e.g., Committee on Finance">
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Committee Type <span class="text-red-600">*</span>
                </label>
                <select name="type" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Type</option>
                    <option value="Standing">Standing Committee</option>
                    <option value="Special">Special Committee</option>
                    <option value="Ad Hoc">Ad Hoc Committee</option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <!-- Chairperson -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Chairperson <span class="text-red-600">*</span>
                </label>
                <input type="text" name="chair" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    placeholder="e.g., Hon. Maria Santos">
            </div>

            <!-- Vice Chairperson -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Vice Chairperson
                </label>
                <input type="text" name="vice_chair"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    placeholder="e.g., Hon. Roberto Cruz">
            </div>

            <!-- Jurisdiction -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Jurisdiction <span class="text-red-600">*</span>
                </label>
                <textarea name="jurisdiction" required rows="4"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Describe the committee's areas of responsibility..."></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="index.php"
                class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="bi bi-check-circle mr-2"></i> Create Committee
            </button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>