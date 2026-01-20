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

// Get statistics
$stats = getCommitteeStatistics($id);
$committee = array_merge($committee, $stats);

// Get action items for this committee
$actionItems = getActionItemsByCommittee($id);

// Sort by priority and due date
usort($actionItems, function ($a, $b) {
    $priorityOrder = ['High' => 1, 'Medium' => 2, 'Low' => 3];
    $aPriority = $priorityOrder[$a['priority']] ?? 4;
    $bPriority = $priorityOrder[$b['priority']] ?? 4;

    if ($aPriority !== $bPriority) {
        return $aPriority - $bPriority;
    }
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});

// Filter by status if provided
$statusFilter = $_GET['status'] ?? 'all';
if ($statusFilter !== 'all') {
    $actionItems = array_filter($actionItems, function ($item) use ($statusFilter) {
        return $item['status'] === $statusFilter;
    });
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $committee['name'] . ' - Action Items';
include '../../includes/header.php';
?>

<nav class="mb-4" aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600">
                <?php echo htmlspecialchars($committee['name']); ?>
            </a></li>
        <li class="breadcrumb-item active">Action Items</li>
    </ol>
</nav>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Action Items</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back to Committee
        </a>
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
            <a href="action-items.php?id=<?php echo $id; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Action Items
            </a>
            <a href="reports.php?id=<?php echo $id; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
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

<!-- Action Items List -->
<?php if (empty($actionItems)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-list-check text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Action Items Found</h3>
        <p class="text-gray-600 dark:text-gray-400">
            <?php echo $statusFilter !== 'all' ? "No {$statusFilter} action items for this committee" : "This committee doesn't have any action items yet"; ?>
        </p>
    </div>
<?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Action Item
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Assigned To
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Due Date
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
                <?php foreach ($actionItems as $item): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars(substr($item['description'], 0, 60)) . (strlen($item['description']) > 60 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['assigned_to']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <?php echo date('M j, Y', strtotime($item['due_date'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $priorityColors = [
                                'High' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                'Medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'Low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                            ];
                            $priorityClass = $priorityColors[$item['priority']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $priorityClass; ?>">
                                <?php echo $item['priority']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'Pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                            ];
                            $statusClass = $statusColors[$item['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="../action-items/view.php?id=<?php echo $item['id']; ?>"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                <i class="bi bi-eye mr-1"></i>View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        Showing
        <?php echo count($actionItems); ?> action item(s)
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>