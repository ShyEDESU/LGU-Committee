<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tags = !empty($_POST['tags']) ? array_map('trim', explode(',', $_POST['tags'])) : [];

    updateActionItem($itemId, [
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? '',
        'assigned_to' => $_POST['assigned_to'] ?? '',
        'due_date' => $_POST['due_date'] ?? '',
        'priority' => $_POST['priority'] ?? 'Medium',
        'status' => $_POST['status'] ?? 'To Do',
        'progress' => !empty($_POST['progress']) ? (int) $_POST['progress'] : 0,
        'category' => $_POST['category'] ?? 'General',
        'tags' => $tags,
        'estimated_hours' => !empty($_POST['estimated_hours']) ? (int) $_POST['estimated_hours'] : null,
        'actual_hours' => !empty($_POST['actual_hours']) ? (int) $_POST['actual_hours'] : null,
        'committee_id' => !empty($_POST['committee_id']) ? (int) $_POST['committee_id'] : null,
        'meeting_id' => !empty($_POST['meeting_id']) ? (int) $_POST['meeting_id'] : null,
        'agenda_item_id' => !empty($_POST['agenda_item_id']) ? (int) $_POST['agenda_item_id'] : null,
        'referral_id' => !empty($_POST['referral_id']) ? (int) $_POST['referral_id'] : null,
        'notes' => $_POST['notes'] ?? '',
    ]);

    header('Location: index.php?updated=1');
    exit();
}

// Load data for dropdowns
$committees = getAllCommittees();
$meetings = getAllMeetings();
$referrals = getAllReferrals();
$agendaItems = getAllAgendaItems();
$users = getAllUsers();

// Convert tags array to comma-separated string for display
$tagsString = is_array($item['tags'] ?? null) ? implode(', ', $item['tags']) : '';

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Action Item';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Action Item</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update action item details and progress</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-x-lg"></i> Cancel
        </a>
    </div>
</div>

<form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-heading"></i> Task Title *
            </label>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($item['title']); ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <select name="assigned_to" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Member</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php echo (int) ($item['assigned_to'] ?? 0) === (int) $user['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        (<?php echo htmlspecialchars($user['role_name']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-calendar-x"></i> Due Date *
            </label>
            <input type="date" name="due_date" required value="<?php echo htmlspecialchars($item['due_date'] ?? ''); ?>"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-exclamation-triangle"></i> Priority *
            </label>
            <select name="priority" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="High" <?php echo ($item['priority'] ?? '') === 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo ($item['priority'] ?? '') === 'Medium' ? 'selected' : ''; ?>>Medium
                </option>
                <option value="Low" <?php echo ($item['priority'] ?? '') === 'Low' ? 'selected' : ''; ?>>Low</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-list-check"></i> Status *
            </label>
            <select name="status" required
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="To Do" <?php echo ($item['status'] ?? '') === 'To Do' ? 'selected' : ''; ?>>To Do</option>
                <option value="In Progress" <?php echo ($item['status'] ?? '') === 'In Progress' ? 'selected' : ''; ?>>In
                    Progress</option>
                <option value="Done" <?php echo ($item['status'] ?? '') === 'Done' ? 'selected' : ''; ?>>Done</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-graph-up"></i> Progress: <span
                    id="progressValue"><?php echo ($item['progress'] ?? 0); ?>%</span>
            </label>
            <input type="range" name="progress" min="0" max="100" value="<?php echo ($item['progress'] ?? 0); ?>"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                oninput="document.getElementById('progressValue').textContent = this.value + '%'">
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>0%</span>
                <span>25%</span>
                <span>50%</span>
                <span>75%</span>
                <span>100%</span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-folder"></i> Category
            </label>
            <select name="category"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="General" <?php echo ($item['category'] ?? '') === 'General' ? 'selected' : ''; ?>>General
                </option>
                <option value="Research" <?php echo ($item['category'] ?? '') === 'Research' ? 'selected' : ''; ?>>
                    Research</option>
                <option value="Review" <?php echo ($item['category'] ?? '') === 'Review' ? 'selected' : ''; ?>>Review
                </option>
                <option value="Draft" <?php echo ($item['category'] ?? '') === 'Draft' ? 'selected' : ''; ?>>Draft
                </option>
                <option value="Coordinate" <?php echo ($item['category'] ?? '') === 'Coordinate' ? 'selected' : ''; ?>>
                    Coordinate</option>
                <option value="Report" <?php echo ($item['category'] ?? '') === 'Report' ? 'selected' : ''; ?>>Report
                </option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-clock-history"></i> Estimated Hours
            </label>
            <input type="number" name="estimated_hours" min="1" max="1000"
                value="<?php echo ($item['estimated_hours'] ?? ''); ?>" placeholder="e.g., 8"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-clock"></i> Actual Hours
            </label>
            <input type="number" name="actual_hours" min="1" max="1000"
                value="<?php echo ($item['actual_hours'] ?? ''); ?>" placeholder="e.g., 10"
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
                    <option value="<?php echo $committee['id']; ?>" <?php echo ($item['committee_id'] ?? '') == $committee['id'] ? 'selected' : ''; ?>>
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
                    <option value="<?php echo $meeting['id']; ?>" data-committee-id="<?php echo $meeting['committee_id']; ?>" 
                        <?php echo (int) ($item['related_meeting_id'] ?? 0) === (int) $meeting['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($meeting['title']); ?> -
                        <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-list-task"></i> Related Agenda Item
            </label>
            <select name="agenda_item_id"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select Agenda Item (Optional)</option>
                <?php foreach ($agendaItems as $aItem): ?>
                    <option value="<?php echo $aItem['id']; ?>" data-meeting-id="<?php echo $aItem['meeting_id']; ?>"
                        <?php echo (int) ($item['agenda_item_id'] ?? 0) === (int) $aItem['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($aItem['title']); ?>
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
                    <option value="<?php echo $referral['id']; ?>" data-committee-id="<?php echo $referral['committee_id']; ?>"
                        <?php echo (int) ($item['referral_id'] ?? 0) === (int) $referral['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($referral['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-tags"></i> Tags
            </label>
            <input type="text" name="tags" value="<?php echo htmlspecialchars($tagsString); ?>"
                placeholder="e.g., budget, urgent, review (comma-separated)"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separate tags with commas</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-card-text"></i> Description *
            </label>
            <textarea name="description" rows="5" required placeholder="Detailed description of the task..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="bi bi-sticky"></i> Internal Notes
            </label>
            <textarea name="notes" rows="3" placeholder="Internal notes (not visible to assignee)..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($item['notes'] ?? ''); ?></textarea>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php"
            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-check-lg"></i> Update Action Item
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const committeeSelect = document.querySelector('select[name="committee_id"]');
    const meetingSelect = document.querySelector('select[name="meeting_id"]');
    const referralSelect = document.querySelector('select[name="referral_id"]');
    const agendaSelect = document.querySelector('select[name="agenda_item_id"]');

    function updateDropdowns(isInitial = false) {
        const committeeId = committeeSelect.value;
        const meetingId = meetingSelect.value;

        // Meetings
        meetingSelect.disabled = !committeeId;
        meetingSelect.parentElement.classList.toggle('opacity-50', !committeeId);
        Array.from(meetingSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = opt.getAttribute('data-committee-id') === committeeId ? '' : 'none';
        });

        // Referrals
        referralSelect.disabled = !committeeId;
        referralSelect.parentElement.classList.toggle('opacity-50', !committeeId);
        Array.from(referralSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = opt.getAttribute('data-committee-id') === committeeId ? '' : 'none';
        });

        // Agenda Items
        agendaSelect.disabled = !meetingId;
        agendaSelect.parentElement.classList.toggle('opacity-50', !meetingId);
        Array.from(agendaSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = opt.getAttribute('data-meeting-id') === meetingId ? '' : 'none';
        });
    }

    committeeSelect.addEventListener('change', function() {
        meetingSelect.value = "";
        referralSelect.value = "";
        agendaSelect.value = "";
        updateDropdowns();
    });

    meetingSelect.addEventListener('change', function() {
        agendaSelect.value = "";
        updateDropdowns();
    });

    // Run once on load
    updateDropdowns(true);
});
</script>

<?php include '../../includes/footer.php'; ?>