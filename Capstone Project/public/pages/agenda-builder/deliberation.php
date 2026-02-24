<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get meeting ID from URL
$meetingId = $_GET['meeting_id'] ?? 0;
$meeting = $meetingId ? getMeetingById($meetingId) : null;

// Security: Validate meeting jurisdiction if one is selected
if ($meeting && !canUpdate($userId, 'agendas', $meetingId)) {
    $_SESSION['error_message'] = 'Security Violation: Jurisdictional boundary breach detected.';
    header('Location: deliberation.php');
    exit();
}

// Get all meetings filtered by user jurisdiction
$allMeetings = getUserMeetings($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_deliberation'])) {
    $targetMeetingId = $_POST['meeting_id'];

    // Security: Final authority check before recording legislative record
    if (!canUpdate($userId, 'agendas', $targetMeetingId)) {
        $_SESSION['error_message'] = 'Security Violation: Unauthorized attempt to modify legislative record.';
        header('Location: deliberation.php');
        exit();
    }

    createDeliberation($_POST['agenda_item_id'], [
        'speaker' => $_POST['speaker'],
        'notes' => $_POST['notes'],
        'duration' => $_POST['duration'] ?? 0,
        'recorded_by' => $userId
    ]);
    header('Location: deliberation.php?meeting_id=' . $targetMeetingId . '&added=1');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Deliberation Tracking';
include '../../includes/header.php';

// Get agenda items and deliberations if meeting is selected
$agendaItems = [];
$deliberations = [];
if ($meeting) {
    $agendaItems = getAgendaByMeeting($meetingId);
    $deliberations = getDeliberationsByAgenda($meetingId);
}

$successMessage = '';
if (isset($_GET['added'])) {
    $successMessage = 'Deliberation recorded successfully.';
}
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Deliberation Tracking</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track discussions and deliberations during meetings</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left mr-2"></i> Back
        </a>
    </div>

    <?php if ($successMessage): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700 dark:text-green-300"><?php echo $successMessage; ?></p>
        </div>
    <?php endif; ?>

    <!-- Meeting Selection -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-calendar-event mr-2"></i> Select Meeting
        </h2>
        <form method="GET" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meeting</label>
                <select name="meeting_id" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Select a meeting...</option>
                    <?php foreach ($allMeetings as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $meetingId == $m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['title']); ?> -
                            <?php echo date('M j, Y', strtotime($m['date'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                Load Meeting
            </button>
        </form>
    </div>

    <?php if ($meeting): ?>
        <!-- Meeting Info -->
        <div class="bg-red-50 dark:bg-blue-900/20 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex items-center">
                <i class="bi bi-info-circle text-blue-700 dark:text-blue-300 text-xl mr-3"></i>
                <div>
                    <p class="text-blue-700 dark:text-blue-300 font-medium">
                        <?php echo htmlspecialchars($meeting['title']); ?>
                    </p>
                    <p class="text-sm text-red-600 dark:text-blue-400">
                        <?php echo htmlspecialchars($meeting['committee_name']); ?> •
                        <?php echo date('M j, Y g:i A', strtotime($meeting['date'] . ' ' . $meeting['time_start'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Agenda Items with Deliberations -->
            <div class="lg:col-span-2 space-y-4">
                <?php if (empty($agendaItems)): ?>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                        <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Agenda Items</h3>
                        <p class="text-gray-600 dark:text-gray-400">This meeting doesn't have an agenda yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($agendaItems as $item):
                        $itemDelibs = array_filter($deliberations, fn($d) => $d['agenda_item_id'] == $item['id']);
                        ?>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <i class="bi bi-clock mr-1"></i> <?php echo $item['duration']; ?> minutes allocated
                                </p>
                            </div>
                            <div class="p-4">
                                <?php if (empty($itemDelibs)): ?>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm italic">No deliberation recorded yet</p>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($itemDelibs as $delib): ?>
                                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div class="flex items-start justify-between mb-2">
                                                    <div class="flex items-center">
                                                        <i class="bi bi-person-circle text-red-600 dark:text-blue-400 text-xl mr-2"></i>
                                                        <span class="font-semibold text-gray-900 dark:text-white">
                                                            <?php echo htmlspecialchars($delib['speaker']); ?>
                                                        </span>
                                                    </div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        <?php echo date('g:i A', strtotime($delib['timestamp'])); ?>
                                                    </span>
                                                </div>
                                                <p class="text-gray-700 dark:text-gray-300 text-sm">
                                                    <?php echo nl2br(htmlspecialchars($delib['notes'])); ?>
                                                </p>
                                                <?php if ($delib['duration'] > 0): ?>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                        <i class="bi bi-clock mr-1"></i> <?php echo $delib['duration']; ?> minutes
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Deliberation Form -->
            <div class="lg:col-span-1">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                        <i class="bi bi-plus-lg mr-2"></i> Record Deliberation
                    </h2>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="add_deliberation" value="1">
                        <input type="hidden" name="meeting_id" value="<?php echo $meetingId; ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Agenda Item <span class="text-red-600">*</span>
                            </label>
                            <select name="agenda_item_id" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Select item...</option>
                                <?php foreach ($agendaItems as $item): ?>
                                    <option value="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Speaker <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="speaker" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Speaker name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes/Discussion <span class="text-red-600">*</span>
                            </label>
                            <textarea name="notes" rows="4" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Record discussion points..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Duration (minutes)
                            </label>
                            <input type="number" name="duration" min="0"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Optional">
                        </div>

                        <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                            <i class="bi bi-plus-lg mr-2"></i> Add Deliberation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination/Summary Box - Styled to match index.php exactly -->
<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span class="font-medium"><?php echo count($deliberations); ?></span> of
        <span class="font-medium"><?php echo count($deliberations); ?></span> record(s)
    </div>
    <div class="flex gap-2">
        <!-- Empty space for pagination consistency -->
        <div class="text-xs text-gray-400 italic font-medium">Meeting ID: <?php echo htmlspecialchars($meetingId); ?>
        </div>
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>