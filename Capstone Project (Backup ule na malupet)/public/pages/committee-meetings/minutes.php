<?php
// Suppress all errors to prevent output corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    // Don't redirect - let page load with error message
    $meeting = [
        'id' => $meetingId,
        'title' => 'Meeting Not Found',
        'committee_id' => 0,
        'committee_name' => 'Unknown',
        'date' => date('Y-m-d'),
        'time_start' => '00:00',
        'status' => 'Unknown'
    ];
    // header('Location: index.php');
    // exit();
}

// Handle save minutes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_minutes'])) {
    if ($meeting['status'] === 'Scheduled') {
        $_SESSION['error_message'] = 'Minutes cannot be recorded for a meeting that has not formally commenced.';
        header('Location: minutes.php?id=' . $meetingId);
        exit();
    }

    $data = [
        'content' => $_POST['content'] ?? '',
        'decisions' => isset($_POST['decisions']) ? array_filter($_POST['decisions']) : [],
        'action_items' => isset($_POST['action_items']) ? array_filter($_POST['action_items']) : [],
        'attendees' => isset($_POST['attendees']) ? $_POST['attendees'] : []
    ];
    
    saveMinutes($meetingId, $data);
    $_SESSION['success_message'] = 'Minutes saved successfully';
    header('Location: minutes.php?id=' . $meetingId);
    exit();
}

// Handle approve minutes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_minutes'])) {
    if (!canApprove($_SESSION['user_id'], 'meetings', $meetingId)) {
        $_SESSION['error_message'] = 'Unauthorized: Only the Chairperson or Committee Leadership can formally approve minutes.';
        header('Location: minutes.php?id=' . $meetingId);
        exit();
    }
    approveMinutes($meetingId);
    $_SESSION['success_message'] = 'Minutes have been formally approved and locked as a permanent legislative record.';
    header('Location: minutes.php?id=' . $meetingId);
    exit();
}

// Get data
$committee = getCommitteeById($meeting['committee_id']);
$minutes = getMeetingMinutes($meetingId);
$agendaItems = getAgendaByMeeting($meetingId);
$attendance = getAttendanceRecords($meetingId); // Fixed function name

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Minutes';
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meeting Minutes</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Meeting
            </a>
            <?php if ($minutes && $minutes['status'] === 'Draft' && canApprove($_SESSION['user_id'], 'meetings', $meetingId)): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="approve_minutes" value="1">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        <i class="bi bi-check-circle mr-2"></i>Approve Minutes
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Details
            </a>
            <a href="attendance.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Attendance
            </a>
            <a href="minutes.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Minutes
            </a>
            <a href="documents.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Documents
            </a>
            <a href="voting.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Voting
            </a>
        </nav>
    </div>
</div>

<!-- Status Badge -->
<?php if ($minutes): ?>
    <div class="mb-6">
        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full 
            <?php echo $minutes['status'] === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'; ?>">
            <i class="bi bi-<?php echo $minutes['status'] === 'Approved' ? 'check-circle' : 'clock'; ?> mr-2"></i>
            <?php echo $minutes['status']; ?>
            <?php if ($minutes['status'] === 'Approved'): ?>
                - Approved by <?php echo htmlspecialchars($minutes['approved_by']); ?> 
                on <?php echo date('M j, Y g:i A', strtotime($minutes['approved_at'])); ?>
            <?php endif; ?>
        </span>
    </div>
<?php endif; ?>

<div class="mb-6">
    <?php if ($meeting['status'] === 'Scheduled'): ?>
        <div class="bg-gray-50 dark:bg-gray-800 border-l-4 border-gray-400 p-4 mb-4">
            <div class="flex items-center">
                <i class="bi bi-lock-fill text-gray-400 text-xl mr-3"></i>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Temporal Recording Lockdown</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recording of proceedings is disabled until the session formally commences. You can review the agenda items below in the meantime.</p>
                </div>
            </div>
        </div>
    <?php elseif ($minutes && $minutes['status'] === 'Approved'): ?>
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-4">
            <div class="flex items-center">
                <i class="bi bi-shield-check text-blue-600 text-xl mr-3"></i>
                <div>
                    <h4 class="font-bold text-blue-900 dark:text-blue-300">Permanent Record (Locked)</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-400">These minutes have been formally approved and attested. They are now preserved as an official legislative record and cannot be modified.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="save_minutes" value="1">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Meeting Minutes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Meeting Minutes</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Minutes Content
                    </label>
                    <textarea name="content" rows="15" 
                        <?php echo ($meeting['status'] === 'Scheduled' || ($minutes && $minutes['status'] === 'Approved')) ? 'readonly' : ''; ?>
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter detailed meeting minutes here..."><?php echo htmlspecialchars($minutes['content'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Document discussions, presentations, and key points from the meeting
                    </p>
                </div>
            </div>

            <!-- Key Decisions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Key Decisions & Resolutions</h2>
                
                <div id="decisions-container">
                    <?php 
                    $decisions = $minutes['decisions'] ?? [''];
                    foreach ($decisions as $index => $decision): 
                    ?>
                        <div class="mb-3 flex gap-2">
                            <input type="text" name="decisions[]" 
                                value="<?php echo htmlspecialchars($decision); ?>"
                                <?php echo ($meeting['status'] === 'Scheduled' || ($minutes && $minutes['status'] === 'Approved')) ? 'readonly' : ''; ?>
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                                placeholder="Decision or resolution #<?php echo $index + 1; ?>">
                            <?php if (!$minutes || $minutes['status'] !== 'Approved'): ?>
                                <button type="button" onclick="this.parentElement.remove()"
                                    class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!$minutes || $minutes['status'] !== 'Approved'): ?>
                    <button type="button" onclick="addDecision()"
                        class="mt-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="bi bi-plus-circle mr-2"></i>Add Decision
                    </button>
                <?php endif; ?>
            </div>

            <!-- Action Items from Meeting -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Action Items Created</h2>
                
                <div id="action-items-container">
                    <?php 
                    $actionItems = $minutes['action_items'] ?? [''];
                    foreach ($actionItems as $index => $actionItem): 
                    ?>
                        <div class="mb-3 flex gap-2">
                            <input type="text" name="action_items[]" 
                                value="<?php echo htmlspecialchars($actionItem); ?>"
                                <?php echo ($meeting['status'] === 'Scheduled' || ($minutes && $minutes['status'] === 'Approved')) ? 'readonly' : ''; ?>
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                                placeholder="Action item #<?php echo $index + 1; ?>">
                            <?php if (!$minutes || $minutes['status'] !== 'Approved'): ?>
                                <button type="button" onclick="this.parentElement.remove()"
                                    class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!$minutes || $minutes['status'] !== 'Approved'): ?>
                    <button type="button" onclick="addActionItem()"
                        class="mt-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="bi bi-plus-circle mr-2"></i>Add Action Item
                    </button>
                <?php endif; ?>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    <i class="bi bi-info-circle mr-1"></i>
                    These are summary notes. Create formal action items in the Action Items module.
                </p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Meeting Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Meeting Information</h3>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Committee</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($committee['name']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Date</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Time</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                            <?php if (!empty($meeting['time_end'])): ?>
                                - <?php echo date('g:i A', strtotime($meeting['time_end'])); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Venue</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($meeting['venue']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Agenda Items -->
            <?php if (!empty($agendaItems)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Agenda Items</h3>
                    
                    <div class="space-y-2">
                        <?php foreach ($agendaItems as $item): ?>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    <?php echo $item['duration']; ?> min - <?php echo htmlspecialchars($item['presenter']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Attendance Summary -->
            <?php if (!empty($attendance)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Attendance</h3>
                    
                    <div class="space-y-2">
                        <?php 
                        $stats = getAttendanceStats($meetingId);
                        ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Present:</span>
                            <span class="font-semibold text-green-600"><?php echo $stats['present']; ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Absent:</span>
                            <span class="font-semibold text-red-600"><?php echo $stats['absent']; ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Excused:</span>
                            <span class="font-semibold text-yellow-600"><?php echo $stats['excused']; ?></span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Quorum:</span>
                                <span class="font-semibold <?php echo $stats['has_quorum'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $stats['has_quorum'] ? 'Achieved' : 'Not Met'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="attendance.php?id=<?php echo $meetingId; ?>"
                        class="mt-3 block text-center text-sm text-red-600 hover:text-red-800 dark:text-blue-400">
                        View Full Attendance →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($meeting['status'] !== 'Scheduled' && (!$minutes || $minutes['status'] !== 'Approved')): ?>
        <div class="mt-6 flex justify-end gap-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="bi bi-save mr-2"></i>Save Minutes
            </button>
        </div>
    <?php endif; ?>
</form>
</div>

<script>
function addDecision() {
    const container = document.getElementById('decisions-container');
    const index = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'mb-3 flex gap-2';
    div.innerHTML = `
        <input type="text" name="decisions[]" 
            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
            placeholder="Decision or resolution #${index}">
        <button type="button" onclick="this.parentElement.remove()"
            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function addActionItem() {
    const container = document.getElementById('action-items-container');
    const index = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'mb-3 flex gap-2';
    div.innerHTML = `
        <input type="text" name="action_items[]" 
            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
            placeholder="Action item #${index}">
        <button type="button" onclick="this.parentElement.remove()"
            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}
</script>
</div> <!-- Closing module-content-wrapper -->

<?php 
include '../../includes/footer.php'; 
include '../../includes/layout-end.php';
?>
