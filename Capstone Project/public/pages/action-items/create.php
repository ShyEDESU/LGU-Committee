<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tags = !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [];

    $itemId = createActionItem([
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? '',
        'assigned_to' => $_POST['assigned_to'] ?? '',
        'due_date' => $_POST['due_date'] ?? '',
        'priority' => $_POST['priority'] ?? 'Medium',
        'category' => $_POST['category'] ?? 'General',
        'tags' => $tags,
        'estimated_hours' => !empty($_POST['estimated_hours']) ? (int) $_POST['estimated_hours'] : null,
        'committee_id' => !empty($_POST['committee_id']) ? (int) $_POST['committee_id'] : null,
        'meeting_id' => !empty($_POST['meeting_id']) ? (int) $_POST['meeting_id'] : null,
        'referral_id' => !empty($_POST['referral_id']) ? (int) $_POST['referral_id'] : null,
        'notes' => $_POST['notes'] ?? '',
    ]);

    header('Location: index.php?created=1');
    exit();
}

// Load data for dropdowns
$committees = getAllCommittees();
$meetings = getAllMeetings();
$referrals = getAllReferrals();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Action Item';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Action Item</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Assign a new task or action item</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-x-lg"></i> Cancel
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <a href="assign.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assign
        </a>
        <a href="progress.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Progress
        </a>
        <a href="create.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-plus-lg"></i> Create
        </a>
    </div>
</div>

<form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-heading"></i> Task Title *
            </label>
            <input type="text" name="title" required placeholder="e.g., Review 2025 Budget Proposal"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-person"></i> Assign To *
            </label>
            <select name="assigned_to" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Member</option>
                <option value="Hon. Maria Santos">Hon. Maria Santos</option>
                <option value="Hon. Juan Dela Cruz">Hon. Juan Dela Cruz</option>
                <option value="Hon. Ana Reyes">Hon. Ana Reyes</option>
                <option value="Hon. Pedro Garcia">Hon. Pedro Garcia</option>
                <option value="Hon. Rosa Martinez">Hon. Rosa Martinez</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-x"></i> Due Date *
            </label>
            <input type="date" name="due_date" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

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

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-folder"></i> Category
            </label>
            <select name="category"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="General">General</option>
                <option value="Research">Research</option>
                <option value="Review">Review</option>
                <option value="Draft">Draft</option>
                <option value="Coordinate">Coordinate</option>
                <option value="Report">Report</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-clock-history"></i> Estimated Hours
            </label>
            <input type="number" name="estimated_hours" min="1" max="1000" placeholder="e.g., 8"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-building"></i> Related Committee
            </label>
            <select name="committee_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Committee (Optional)</option>
                <?php foreach ($committees as $committee): ?>
                    <option value="<?php echo $committee['id']; ?>">
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-event"></i> Related Meeting
            </label>
            <select name="meeting_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Meeting (Optional)</option>
                <?php foreach ($meetings as $meeting): ?>
                    <option value="<?php echo $meeting['id']; ?>">
                        <?php echo htmlspecialchars($meeting['title']); ?> -
                        <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-file-earmark-text"></i> Related Referral
            </label>
            <select name="referral_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Referral (Optional)</option>
                <?php foreach ($referrals as $referral): ?>
                    <option value="<?php echo $referral['id']; ?>">
                        <?php echo htmlspecialchars($referral['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-tags"></i> Tags
            </label>
            <input type="text" name="tags" placeholder="e.g., budget, urgent, review (comma-separated)"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separate tags with commas</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-text"></i> Description *
            </label>
            <textarea name="description" rows="5" required placeholder="Detailed description of the task..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-sticky"></i> Internal Notes
            </label>
            <textarea name="notes" rows="3" placeholder="Internal notes (not visible to assignee)..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"></textarea>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php"
            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-check-lg"></i> Create Action Item
        </button>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>