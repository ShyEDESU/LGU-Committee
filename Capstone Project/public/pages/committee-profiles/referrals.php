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

// Get all referrals for this committee
$allReferrals = getAllReferrals();
$referrals = array_filter($allReferrals, function ($r) use ($id) {
    return ($r['committee_id'] ?? null) == $id;
});

// Sort by date (newest first)
usort($referrals, function ($a, $b) {
    return strtotime($b['date_received']) - strtotime($a['date_received']);
});

// Filter by status if provided
$statusFilter = $_GET['status'] ?? 'all';
if ($statusFilter !== 'all') {
    $referrals = array_filter($referrals, function ($r) use ($statusFilter) {
        return $r['status'] === $statusFilter;
    });
}

// Calculate statistics
$stats = [
    'total' => count(array_filter($allReferrals, function ($r) use ($id) {
        return ($r['committee_id'] ?? null) == $id;
    })),
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0
];

foreach ($allReferrals as $ref) {
    if (($ref['committee_id'] ?? null) == $id) {
        if ($ref['status'] === 'Pending')
            $stats['pending']++;
        elseif ($ref['status'] === 'In Progress')
            $stats['in_progress']++;
        elseif ($ref['status'] === 'Completed')
            $stats['completed']++;
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'] . ' - Referrals';
include '../../includes/header.php';
?>

<nav class="mb-4" aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600">
                <?php echo htmlspecialchars($committee['name']); ?>
            </a></li>
        <li class="breadcrumb-item active">Referrals</li>
    </ol>
</nav>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Referrals</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Committee
            </a>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            <a href="view.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-info-circle mr-1"></i>Overview
            </a>
            <a href="members.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-people mr-1"></i>Members
            </a>
            <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-calendar-event mr-1"></i>Meetings
            </a>
            <a href="view.php?id=<?php echo $id; ?>&tab=agendas"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-journal-text mr-1"></i>Agendas
            </a>
            <a href="referrals.php?id=<?php echo $id; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-arrow-left-right mr-1"></i>Referrals
            </a>
            <a href="documents.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-folder mr-1"></i>Documents
            </a>
            <a href="reports.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-file-earmark-text mr-1"></i>Reports
            </a>
            <a href="history.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-clock-history mr-1"></i>History
            </a>
        </nav>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Referrals</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?php echo $stats['total']; ?>
                </p>
            </div>
            <i class="bi bi-arrow-left-right text-3xl text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                <p class="text-2xl font-bold text-yellow-600">
                    <?php echo $stats['pending']; ?>
                </p>
            </div>
            <i class="bi bi-clock text-3xl text-yellow-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
                <p class="text-2xl font-bold text-blue-600">
                    <?php echo $stats['in_progress']; ?>
                </p>
            </div>
            <i class="bi bi-arrow-repeat text-3xl text-blue-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                <p class="text-2xl font-bold text-green-600">
                    <?php echo $stats['completed']; ?>
                </p>
            </div>
            <i class="bi bi-check-circle text-3xl text-green-400"></i>
        </div>
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
            <a href="?id=<?php echo $id; ?>&status=Pending"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'Pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                Pending
            </a>
            <a href="?id=<?php echo $id; ?>&status=In Progress"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'In Progress' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                In Progress
            </a>
            <a href="?id=<?php echo $id; ?>&status=Completed"
                class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'Completed' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                Completed
            </a>
        </div>
    </div>
</div>

<!-- Referrals List -->
<?php if (empty($referrals)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-inbox text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Referrals Found</h3>
        <p class="text-gray-600 dark:text-gray-400">
            <?php echo $statusFilter !== 'all' ? "No {$statusFilter} referrals for this committee" : "This committee hasn't been assigned any referrals yet"; ?>
        </p>
    </div>
<?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Referral
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        From
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Date Received
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Priority
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
                <?php foreach ($referrals as $referral): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($referral['title']); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars(substr($referral['description'], 0, 50)) . (strlen($referral['description']) > 50 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($referral['submitted_by']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo date('M j, Y', strtotime($referral['date_received'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $priorityColors = [
                                'High' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                'Medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'Low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                            ];
                            $priorityClass = $priorityColors[$referral['priority']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $priorityClass; ?>">
                                <?php echo $referral['priority']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'Pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                            ];
                            $statusClass = $statusColors[$referral['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $referral['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="../referral-management/view.php?id=<?php echo $referral['id']; ?>"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                <i class="bi bi-eye mr-1"></i>View
                            </a>
                            <a href="../referral-management/tracking.php?id=<?php echo $referral['id']; ?>"
                                class="text-green-600 hover:text-green-900 dark:text-green-400">
                                <i class="bi bi-graph-up mr-1"></i>Track
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        Showing
        <?php echo count($referrals); ?> referral(s)
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>