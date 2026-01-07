<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Management';
include '../../includes/header.php';

// Get all referrals
$allReferrals = getAllReferrals();

// Apply filters
$searchTerm = $_GET['search'] ?? '';
$filterCommittee = $_GET['committee'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterPriority = $_GET['priority'] ?? '';

$filteredReferrals = $allReferrals;

if (!empty($searchTerm)) {
    $filteredReferrals = array_filter($filteredReferrals, function ($ref) use ($searchTerm) {
        return stripos($ref['title'], $searchTerm) !== false ||
            stripos($ref['description'], $searchTerm) !== false;
    });
}

if (!empty($filterCommittee)) {
    $filteredReferrals = array_filter($filteredReferrals, function ($ref) use ($filterCommittee) {
        return $ref['committee_id'] == $filterCommittee;
    });
}

if (!empty($filterType)) {
    $filteredReferrals = array_filter($filteredReferrals, function ($ref) use ($filterType) {
        return $ref['type'] === $filterType;
    });
}

if (!empty($filterStatus)) {
    $filteredReferrals = array_filter($filteredReferrals, function ($ref) use ($filterStatus) {
        return $ref['status'] === $filterStatus;
    });
}

if (!empty($filterPriority)) {
    $filteredReferrals = array_filter($filteredReferrals, function ($ref) use ($filterPriority) {
        return $ref['priority'] === $filterPriority;
    });
}

// Calculate real stats from actual data
$totalReferrals = count($allReferrals);
$pendingCount = count(getReferralsByStatus('Pending'));
$inProgressCount = count(getReferralsByStatus('Under Review')) + count(getReferralsByStatus('In Committee'));
$completedCount = count(getReferralsByStatus('Approved')) + count(getReferralsByStatus('Rejected'));

// Get all committees for filter dropdown
$committees = getAllCommittees();
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Management</h1>
            <p class="text-gray-600 mt-1">Track ordinances, resolutions, and communications</p>
        </div>
        <a href="create.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"><i
                class="bi bi-plus-lg"></i> New Referral</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold"><i class="bi bi-list"></i>
            All Referrals</a>
        <a href="tracking.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-graph-up"></i> Tracking</a>
        <a href="assign.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-person-plus"></i> Assignment</a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i
                class="bi bi-calendar-x"></i> Deadlines</a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                placeholder="Search by title or committee..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
        </div>
        <select name="committee" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
            <option value="">All Committees</option>
            <?php foreach ($committees as $committee): ?>
                <option value="<?php echo $committee['id']; ?>" <?php echo $filterCommittee == $committee['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($committee['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
            <option value="">All Types</option>
            <option value="Ordinance" <?php echo $filterType === 'Ordinance' ? 'selected' : ''; ?>>Ordinance</option>
            <option value="Resolution" <?php echo $filterType === 'Resolution' ? 'selected' : ''; ?>>Resolution</option>
            <option value="Communication" <?php echo $filterType === 'Communication' ? 'selected' : ''; ?>>Communication
            </option>
        </select>
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
            <option value="">All Status</option>
            <option value="Pending" <?php echo $filterStatus === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Under Review" <?php echo $filterStatus === 'Under Review' ? 'selected' : ''; ?>>Under Review
            </option>
            <option value="In Committee" <?php echo $filterStatus === 'In Committee' ? 'selected' : ''; ?>>In Committee
            </option>
            <option value="Approved" <?php echo $filterStatus === 'Approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?php echo $filterStatus === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <div class="md:col-span-5 flex justify-end gap-2">
            <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="bi bi-x-circle"></i> Clear Filters
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="bi bi-search"></i> Search
            </button>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total Referrals</p>
            <i class="bi bi-list-ul text-2xl text-blue-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo $totalReferrals; ?>
        </p>
        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">All referrals</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-gray-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
            <i class="bi bi-hourglass-split text-2xl text-gray-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo $pendingCount; ?>
        </p>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Awaiting action</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
            <i class="bi bi-arrow-repeat text-2xl text-purple-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo $inProgressCount; ?>
        </p>
        <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Under review/In committee</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
            <i class="bi bi-check-circle text-2xl text-green-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo $completedCount; ?>
        </p>
        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Approved/Rejected</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($filteredReferrals)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-inbox text-4xl mb-2"></i>
                        <p>No referrals found</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($filteredReferrals as $referral): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($referral['title']); ?>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($referral['type']); ?>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($referral['committee_name']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?php
                            echo $referral['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                                ($referral['status'] === 'Under Review' ? 'bg-blue-100 text-blue-800' :
                                    ($referral['status'] === 'In Committee' ? 'bg-purple-100 text-purple-800' :
                                        ($referral['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')));
                            ?>">
                                <?php echo $referral['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?php
                            echo $referral['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                ($referral['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                            ?>">
                                <?php echo $referral['priority']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">
                            <?php echo !empty($referral['deadline']) ? date('M j, Y', strtotime($referral['deadline'])) : 'No deadline'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="view.php?id=<?php echo $referral['id']; ?>"
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm">
                                    View
                                </a>
                                <a href="edit.php?id=<?php echo $referral['id']; ?>"
                                    class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition text-sm">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>