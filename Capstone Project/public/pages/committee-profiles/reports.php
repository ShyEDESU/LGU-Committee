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

// Get all related data
$meetings = array_filter(getAllMeetings(), function ($m) use ($id) {
    return $m['committee_id'] == $id;
});

$actionItems = array_filter(getAllActionItems(), function ($item) use ($id) {
    return ($item['committee_id'] ?? null) == $id;
});

$referrals = array_filter(getAllReferrals(), function ($r) use ($id) {
    return ($r['committee_id'] ?? null) == $id;
});

// Calculate statistics
$totalMeetings = count($meetings);
$completedMeetings = count(array_filter($meetings, function ($m) {
    return $m['status'] === 'Completed';
}));

$totalActionItems = count($actionItems);
$completedActionItems = count(array_filter($actionItems, function ($item) {
    return $item['status'] === 'Done';
}));
$actionItemsCompletionRate = $totalActionItems > 0 ? round(($completedActionItems / $totalActionItems) * 100, 1) : 0;

$totalReferrals = count($referrals);
$completedReferrals = count(array_filter($referrals, function ($r) {
    return $r['status'] === 'Completed';
}));
$referralsCompletionRate = $totalReferrals > 0 ? round(($completedReferrals / $totalReferrals) * 100, 1) : 0;

// Meeting attendance stats
$totalAttendanceRecords = 0;
$totalPresentRecords = 0;
foreach ($meetings as $meeting) {
    $attendance = getMeetingAttendance($meeting['id']);
    $totalAttendanceRecords += count($attendance);
    foreach ($attendance as $record) {
        if ($record['status'] === 'Present') {
            $totalPresentRecords++;
        }
    }
}
$avgAttendanceRate = $totalAttendanceRecords > 0 ? round(($totalPresentRecords / $totalAttendanceRecords) * 100, 1) : 0;

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'] . ' - Reports';
include '../../includes/header.php';
?>

<nav class="mb-4" aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600">
                <?php echo htmlspecialchars($committee['name']); ?>
            </a></li>
        <li class="breadcrumb-item active">Reports</li>
    </ol>
</nav>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Performance Reports</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Committee
            </a>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-printer mr-2"></i>Print Report
            </button>
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
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Meetings
            </a>
            <a href="referrals.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Referrals
            </a>
            <a href="reports.php?id=<?php echo $id; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Reports
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

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Meetings</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo $totalMeetings; ?>
                </p>
                <p class="text-xs text-green-600 mt-1">
                    <?php echo $completedMeetings; ?> completed
                </p>
            </div>
            <i class="bi bi-calendar-event text-4xl text-blue-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Action Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo $totalActionItems; ?>
                </p>
                <p class="text-xs text-green-600 mt-1">
                    <?php echo $actionItemsCompletionRate; ?>% completion
                </p>
            </div>
            <i class="bi bi-check-circle text-4xl text-green-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Referrals</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo $totalReferrals; ?>
                </p>
                <p class="text-xs text-green-600 mt-1">
                    <?php echo $referralsCompletionRate; ?>% resolved
                </p>
            </div>
            <i class="bi bi-arrow-left-right text-4xl text-purple-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Avg Attendance</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo $avgAttendanceRate; ?>%
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Meeting attendance
                </p>
            </div>
            <i class="bi bi-people text-4xl text-orange-400"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Meetings Performance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-calendar-event mr-2"></i>Meetings Performance
        </h2>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">Completed</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?php echo $completedMeetings; ?> /
                        <?php echo $totalMeetings; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full"
                        style="width: <?php echo $totalMeetings > 0 ? ($completedMeetings / $totalMeetings * 100) : 0; ?>%">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Recent Meetings</h3>
                <?php
                $recentMeetings = array_slice($meetings, 0, 5);
                if (empty($recentMeetings)):
                    ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No meetings yet</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($recentMeetings as $meeting): ?>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($meeting['title']); ?>
                                </span>
                                <span
                                    class="px-2 py-1 text-xs rounded-full 
                                    <?php echo $meeting['status'] === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; ?>">
                                    <?php echo $meeting['status']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Items Performance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-check-circle mr-2"></i>Action Items Performance
        </h2>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">Completion Rate</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?php echo $actionItemsCompletionRate; ?>%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full"
                        style="width: <?php echo $actionItemsCompletionRate; ?>%"></div>
                </div>
            </div>

            <?php
            $statusCounts = [
                'To Do' => 0,
                'In Progress' => 0,
                'Done' => 0
            ];
            foreach ($actionItems as $item) {
                $status = $item['status'] ?? 'To Do';
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status]++;
                }
            }
            ?>

            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-600">
                        <?php echo $statusCounts['To Do']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">To Do</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">
                        <?php echo $statusCounts['In Progress']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">
                        <?php echo $statusCounts['Done']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Done</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals Performance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-arrow-left-right mr-2"></i>Referrals Performance
        </h2>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400">Resolution Rate</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?php echo $referralsCompletionRate; ?>%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo $referralsCompletionRate; ?>%">
                    </div>
                </div>
            </div>

            <?php
            $referralStatusCounts = [
                'Pending' => 0,
                'In Progress' => 0,
                'Completed' => 0
            ];
            foreach ($referrals as $ref) {
                $status = $ref['status'] ?? 'Pending';
                if (isset($referralStatusCounts[$status])) {
                    $referralStatusCounts[$status]++;
                }
            }
            ?>

            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-600">
                        <?php echo $referralStatusCounts['Pending']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">
                        <?php echo $referralStatusCounts['In Progress']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">
                        <?php echo $referralStatusCounts['Completed']; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-people mr-2"></i>Attendance Summary
        </h2>

        <div class="space-y-4">
            <div class="text-center py-4">
                <p class="text-5xl font-bold text-blue-600">
                    <?php echo $avgAttendanceRate; ?>%
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Average Attendance Rate</p>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Based on
                    <?php echo $totalMeetings; ?> meetings
                </p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total Records:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            <?php echo $totalAttendanceRecords; ?>
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Present:</span>
                        <span class="font-semibold text-green-600">
                            <?php echo $totalPresentRecords; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>