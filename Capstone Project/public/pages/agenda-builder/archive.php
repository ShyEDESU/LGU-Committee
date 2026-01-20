<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_agenda'])) {
    $meetingId = $_POST['meeting_id'];
    $meeting = getMeetingById($meetingId);
    if ($meeting) {
        updateMeeting($meetingId, ['agenda_status' => 'Draft']);
        $_SESSION['success_message'] = 'Agenda restored successfully';
        header('Location: archive.php');
        exit();
    }
}

// Get all archived agendas
$allMeetings = getAllMeetings();
$archivedAgendas = array_filter($allMeetings, function ($m) {
    return ($m['agenda_status'] ?? 'Draft') === 'Archived';
});

// Sort by date (newest first)
usort($archivedAgendas, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Archived Agendas';
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Archived Agendas</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                View and restore archived meeting agendas
            </p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Agendas
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Archived</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo count($archivedAgendas); ?>
                </p>
            </div>
            <i class="bi bi-archive text-4xl text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">This Year</p>
                <p class="text-3xl font-bold text-blue-600">
                    <?php
                    $thisYear = count(array_filter($archivedAgendas, function ($a) {
                        return date('Y', strtotime($a['date'])) == date('Y');
                    }));
                    echo $thisYear;
                    ?>
                </p>
            </div>
            <i class="bi bi-calendar text-4xl text-blue-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Last Month</p>
                <p class="text-3xl font-bold text-green-600">
                    <?php
                    $lastMonth = count(array_filter($archivedAgendas, function ($a) {
                        $date = strtotime($a['date']);
                        return date('Y-m', $date) == date('Y-m', strtotime('-1 month'));
                    }));
                    echo $lastMonth;
                    ?>
                </p>
            </div>
            <i class="bi bi-clock-history text-4xl text-green-400"></i>
        </div>
    </div>
</div>

<!-- Archived Agendas List -->
<?php if (empty($archivedAgendas)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-archive text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Archived Agendas</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Archived agendas will appear here
        </p>
    </div>
<?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Meeting
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Committee
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Date
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Items
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($archivedAgendas as $agenda):
                    $agendaItems = getAgendaByMeeting($agenda['id']);
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($agenda['title']); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars(substr($agenda['description'], 0, 50)) . (strlen($agenda['description']) > 50 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($agenda['committee_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo !empty($agenda['date']) ? date('M j, Y', strtotime($agenda['date'])) : 'Not Set'; ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo !empty($agenda['time_start']) ? date('g:i A', strtotime($agenda['time_start'])) : ''; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 dark:text-white">
                                <?php echo count($agendaItems); ?> items
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="view.php?id=<?php echo $agenda['id']; ?>"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                <i class="bi bi-eye mr-1"></i>View
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Restore this agenda?')">
                                <input type="hidden" name="restore_agenda" value="1">
                                <input type="hidden" name="meeting_id" value="<?php echo $agenda['id']; ?>">
                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                    <i class="bi bi-arrow-counterclockwise mr-1"></i>Restore
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        Showing
        <?php echo count($archivedAgendas); ?> archived agenda(s)
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>