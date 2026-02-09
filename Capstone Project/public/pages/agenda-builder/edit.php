<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_agenda'])) {
    $agendaTitle = $_POST['agenda_title'] ?? $meeting['title'];
    $agendaDescription = $_POST['agenda_description'] ?? '';

    updateMeeting($meetingId, [
        'title' => $agendaTitle,
        'description' => $agendaDescription
    ]);

    $_SESSION['success_message'] = 'Agenda updated successfully!';
    header('Location: view.php?id=' . $meetingId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Agenda';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Agenda</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Update agenda information for
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left mr-2"></i> Cancel
            </a>
        </div>
    </div>
</div>

<!-- Edit Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" class="space-y-6">
        <input type="hidden" name="update_agenda" value="1">

        <!-- Agenda Title -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Agenda Title <span class="text-red-600">*</span>
            </label>
            <input type="text" name="agenda_title" required value="<?php echo htmlspecialchars($meeting['title']); ?>"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                placeholder="e.g., Q1 Budget Review Meeting">
        </div>

        <!-- Agenda Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
            </label>
            <textarea name="agenda_description" rows="5"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                placeholder="Optional description or notes about this agenda..."><?php echo htmlspecialchars($meeting['description'] ?? ''); ?></textarea>
        </div>

        <!-- Meeting Information (Read-only) -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Meeting Information (Read-only)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Committee:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($meeting['committee_name']); ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Date:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Time:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Venue:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($meeting['venue']); ?>
                    </span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                <i class="bi bi-info-circle mr-1"></i>
                To change meeting details, go to the Meeting module
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-check-circle mr-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Note Box -->
<div class="bg-red-50 dark:bg-blue-900/20 border-l-4 border-red-500 p-4 mt-6">
    <div class="flex items-start">
        <i class="bi bi-lightbulb text-red-600 dark:text-blue-400 text-xl mr-3 mt-1"></i>
        <div>
            <h4 class="font-semibold text-red-900 dark:text-blue-300 mb-1">Note</h4>
            <p class="text-red-800 dark:text-blue-300 text-sm">
                This page only edits the agenda title and description. To manage agenda items, use the "Edit Items"
                button on the view page.
            </p>
        </div>
    </div>
</div>

</div> <!-- Closing module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>