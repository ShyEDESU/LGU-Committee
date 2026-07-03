<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Get all meetings for this committee
$allMeetings = getAllMeetings();
$meetings = array_filter($allMeetings, function ($m) use ($id) {
    return $m['committee_id'] == $id;
});

// Sort by date (newest first)
usort($meetings, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Filter by status if provided
$statusFilter = $_GET['status'] ?? 'all';
if ($statusFilter !== 'all') {
    $meetings = array_filter($meetings, function ($m) use ($statusFilter) {
        return $m['status'] === $statusFilter;
    });
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'] . ' - Meetings';
include '../../includes/header.php';
?>

<nav class="mb-4" aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600">
                <?php echo htmlspecialchars($committee['name']); ?>
            </a></li>
        <li class="breadcrumb-item active">Meetings</li>
    </ol>
</nav>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Meetings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Committee
            </a>
            <a href="../committee-meetings/schedule.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-plus-circle mr-2"></i>Schedule Meeting
            </a>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Overview
            </a>
            <a href="members.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Members
            </a>
            <a href="meetings.php?id=<?php echo $id; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Meetings
            </a>
            <a href="referrals.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Referrals
            </a>
            <a href="documents.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Documents
            </a>
            <a href="history.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                History
            </a>
        </nav>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
    <div class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Status:</label>
        <div class="flex gap-2">
            <a href="?id=<?php echo $id; ?>&status=all"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'all' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                All
            </a>
            <a href="?id=<?php echo $id; ?>&status=Scheduled"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'Scheduled' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                Scheduled
            </a>
            <a href="?id=<?php echo $id; ?>&status=Completed"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'Completed' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                Completed
            </a>
            <a href="?id=<?php echo $id; ?>&status=Cancelled"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'Cancelled' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                Cancelled
            </a>
        </div>
    </div>
</div>

<!-- Meetings List -->
<?php if (empty($meetings)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-calendar-x text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Meetings Found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            <?php echo $statusFilter !== 'all' ? "No {$statusFilter} meetings for this committee" : "This committee hasn't scheduled any meetings yet"; ?>
        </p>
        <a href="../committee-meetings/schedule.php?committee_id=<?php echo $id; ?>"
            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg inline-block">
            <i class="bi bi-plus-circle mr-2"></i>Schedule First Meeting
        </a>
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
                        Date & Time
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Venue
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($meetings as $meeting): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($meeting['title']); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars(substr($meeting['description'], 0, 60)) . (strlen($meeting['description']) > 60 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($meeting['venue']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'Scheduled' => 'bg-red-100 text-red-800 dark:bg-blue-900 dark:text-blue-300',
                                'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                            ];
                            $colorClass = $statusColors[$meeting['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $colorClass; ?>">
                                <?php echo $meeting['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="../committee-meetings/view.php?id=<?php echo $meeting['id']; ?>"
                                class="text-red-600 hover:text-red-900 dark:text-blue-400 mr-3">
                                <i class="bi bi-eye mr-1"></i>View
                            </a>
                            <a href="../committee-meetings/edit.php?id=<?php echo $meeting['id']; ?>"
                                class="text-green-600 hover:text-green-900 dark:text-green-400">
                                <i class="bi bi-pencil mr-1"></i>Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        Showing
        <?php echo count($meetings); ?> meeting(s)
    </div>
<?php endif; ?>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>