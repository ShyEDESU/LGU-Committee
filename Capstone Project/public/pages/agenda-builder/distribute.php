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

// Handle distribution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_agenda'])) {
    $recipients = $_POST['recipients'] ?? [];
    $method = $_POST['method'] ?? 'email';

    // Log distribution
    logAgendaDistribution($meetingId, $recipients, $method);

    $_SESSION['success_message'] = 'Agenda distributed to ' . count($recipients) . ' recipient(s)';
    header('Location: distribute.php?id=' . $meetingId);
    exit();
}

// Get committee and members
$committee = getCommitteeById($meeting['committee_id']);
$members = getCommitteeMembers($meeting['committee_id']);
$agendaItems = getAgendaByMeeting($meetingId);
$distributionLog = getDistributionLog($meetingId);

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Distribute Agenda';
include '../../includes/header.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-green-700 dark:text-green-300">
            <?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </p>
    </div>
<?php endif; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Distribute Agenda</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $meetingId; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="bi bi-arrow-left mr-2"></i>Back
        </a>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-list-ol mr-1"></i>Agenda Items
            </a>
            <a href="comments.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-chat-dots mr-1"></i>Comments
            </a>
            <a href="distribute.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 dark:text-red-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                <i class="bi bi-send mr-1"></i>Distribution
            </a>
        </nav>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Distribution Form -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Send Agenda</h2>

            <form method="POST">
                <input type="hidden" name="distribute_agenda" value="1">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Select Recipients *
                    </label>

                    <div
                        class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                        <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                            <input type="checkbox" id="select_all" onclick="toggleAll(this)"
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-semibold text-gray-900 dark:text-white">
                                Select All Members
                            </span>
                        </label>
                        <hr class="my-2 border-gray-200 dark:border-gray-600">

                        <?php foreach ($members as $member): ?>
                            <label
                                class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded member-checkbox">
                                <input type="checkbox" name="recipients[]" value="<?php echo $member['member_id']; ?>"
                                    class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <div class="ml-3 flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        <?php echo htmlspecialchars($member['position']); ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Distribution Method *
                    </label>
                    <select name="method" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="email">Email</option>
                        <option value="notification">System Notification</option>
                        <option value="both">Email + Notification</option>
                    </select>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <i class="bi bi-info-circle mr-2"></i>
                        Recipients will receive the agenda with all
                        <?php echo count($agendaItems); ?> agenda items
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="view.php?id=<?php echo $meetingId; ?>"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="bi bi-send mr-2"></i>Distribute Agenda
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Agenda Preview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Agenda Preview</h3>

            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Meeting</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($meeting['title']); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                        at
                        <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Venue</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($meeting['venue']); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Agenda Items</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo count($agendaItems); ?> items
                    </p>
                </div>
            </div>
        </div>

        <!-- Distribution History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Distribution History</h3>

            <?php if (empty($distributionLog)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No distribution history yet
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($distributionLog, 0, 5) as $log): ?>
                        <div class="text-sm">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php
                                $recipients = $log['recipients'] ?? [];
                                if (is_string($recipients)) {
                                    $recipients = json_decode($recipients, true) ?? [];
                                }
                                echo count($recipients);
                                ?> recipients
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?php echo date('M j, Y g:i A', strtotime($log['distributed_at'])); ?>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                via
                                <?php echo ucfirst($log['method']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.member-checkbox input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
    }
</script>

<?php include '../../includes/footer.php'; ?>