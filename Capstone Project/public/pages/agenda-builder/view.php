<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get agenda ID from URL
$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    header('Location: index.php');
    exit();
}

// Handle status changes BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $newStatus = $_POST['new_status'];
    updateMeeting($meetingId, ['agenda_status' => $newStatus]);
    $_SESSION['success_message'] = "Agenda status changed to: $newStatus";
    header('Location: view.php?id=' . $meetingId);
    exit();
}

// Get agenda items
$agendaItems = getAgendaByMeeting($meetingId);
$committee = getCommitteeById($meeting['committee_id']);

// Calculate total duration
$totalDuration = array_sum(array_column($agendaItems, 'duration'));

// Get meeting status
$agendaStatus = $meeting['agenda_status'] ?? 'Draft';

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'View Agenda';
include '../../includes/header.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-700 dark:text-green-300 text-xl mr-3"></i>
            <p class="text-green-700 dark:text-green-300 font-medium">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">View Agenda</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex space-x-2">
            <!-- Status Change Buttons -->
            <?php if ($agendaStatus === 'Draft'): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="change_status" value="1">
                    <input type="hidden" name="new_status" value="Under Review">
                    <button type="submit"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                        <i class="bi bi-send mr-2"></i> Submit for Review
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($agendaStatus === 'Draft' || $agendaStatus === 'Under Review'): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="change_status" value="1">
                    <input type="hidden" name="new_status" value="Approved">
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        <i class="bi bi-check-circle mr-2"></i> Approve
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($agendaStatus === 'Approved'): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="change_status" value="1">
                    <input type="hidden" name="new_status" value="Published">
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        <i class="bi bi-megaphone mr-2"></i> Publish
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($agendaStatus !== 'Draft'): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="change_status" value="1">
                    <input type="hidden" name="new_status" value="Draft">
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                        <i class="bi bi-arrow-counterclockwise mr-2"></i> Set to Draft
                    </button>
                </form>
            <?php endif; ?>

            <button onclick="window.print()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
            <a href="items.php?meeting_id=<?php echo $meetingId; ?>"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="bi bi-pencil mr-2"></i> Edit Items
            </a>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Meeting Information -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Committee</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['committee_name']); ?>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Date & Time</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo date('M j, Y', strtotime($meeting['date'])); ?><br>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                </span>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['venue']); ?>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</h3>
            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full 
                <?php
                echo $agendaStatus === 'Draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                    ($agendaStatus === 'Under Review' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                        ($agendaStatus === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'));
                ?>">
                <?php echo $agendaStatus; ?>
            </span>
        </div>
    </div>
</div>

<!-- Agenda Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($agendaItems); ?>
                </p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-list-check text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Estimated Duration</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo $totalDuration; ?> <span class="text-lg">min</span>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-clock text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">End Time (Est.)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php
                    $endTime = strtotime($meeting['time_start']) + ($totalDuration * 60);
                    echo date('g:i A', $endTime);
                    ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-check text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Agenda Items -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            <i class="bi bi-list-ol mr-2"></i> Agenda Items
        </h2>
    </div>

    <?php if (empty($agendaItems)): ?>
        <div class="p-12 text-center">
            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Agenda Items</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">This agenda doesn't have any items yet</p>
            <a href="items.php?meeting_id=<?php echo $meetingId; ?>"
                class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-plus-lg mr-2"></i> Add Items
            </a>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            $currentTime = strtotime($meeting['time_start']);
            foreach ($agendaItems as $index => $item):
                $itemNumber = $item['item_number'] ?? ($index + 1);
                $startTime = date('g:i A', $currentTime);
                $itemDuration = $item['duration'] ?? 0; // Fix: use null coalescing
                $currentTime += ($itemDuration * 60);
                $endTime = date('g:i A', $currentTime);
                ?>
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                <span class="text-xl font-bold text-red-600 dark:text-red-400">
                                    <?php echo $itemNumber; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-gray-600 dark:text-gray-400 mb-2">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['presenter'])): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-500">
                                            <i class="bi bi-person-badge mr-1"></i> Presenter:
                                            <?php echo htmlspecialchars($item['presenter']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo $startTime; ?> -
                                        <?php echo $endTime; ?>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                                        <i class="bi bi-clock mr-1"></i>
                                        <?php echo ($itemDuration ?? 0); ?> minutes
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Meeting Description -->
<?php if (!empty($meeting['description'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
            <i class="bi bi-info-circle mr-2"></i> Meeting Description
        </h2>
        <p class="text-gray-700 dark:text-gray-300">
            <?php echo nl2br(htmlspecialchars($meeting['description'])); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-lightning mr-2"></i> Quick Actions
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="items.php?meeting_id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
            <i class="bi bi-pencil mr-2"></i> Edit Items
        </a>
        <a href="deliberation.php?meeting_id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
            <i class="bi bi-chat-left-text mr-2"></i> Start Deliberation
        </a>
        <a href="voting.php?meeting_id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
            <i class="bi bi-hand-thumbs-up mr-2"></i> Manage Voting
        </a>
        <a href="../committee-meetings/view.php?id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-event mr-2"></i> View Meeting
        </a>
    </div>
</div>

<style>
    @media print {

        .no-print,
        nav,
        header,
        footer,
        button,
        a[href*="edit"],
        a[href*="Back"] {
            display: none !important;
        }

        body {
            background: white;
        }

        .dark\:bg-gray-800,
        .dark\:bg-gray-700 {
            background: white !important;
        }

        .dark\:text-white,
        .dark\:text-gray-300 {
            color: black !important;
        }
    }
</style>

<?php include '../../includes/footer.php'; ?>