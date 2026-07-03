<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Reports & Analytics';
include '../../includes/header.php';

// Get all data
$allReferrals = getAllReferrals();
$committees = getAllCommittees();

// Calculate statistics
$totalReferrals = count($allReferrals);
$pendingReferrals = count(getReferralsByStatus('Pending'));
$approvedReferrals = count(getReferralsByStatus('Approved'));
$rejectedReferrals = count(getReferralsByStatus('Rejected'));
$overdueReferrals = count(getOverdueReferrals());

// Type distribution
$ordinances = count(getReferralsByType('Ordinance'));
$resolutions = count(getReferralsByType('Resolution'));
$communications = count(getReferralsByType('Communication'));

// Priority distribution
$highPriority = count(getReferralsByPriority('High'));
$mediumPriority = count(getReferralsByPriority('Medium'));
$lowPriority = count(getReferralsByPriority('Low'));

// Committee statistics
$committeeStats = [];
foreach ($committees as $committee) {
    $committeeReferrals = getReferralsByCommittee($committee['id']);
    $committeeStats[] = [
        'name' => $committee['name'],
        'total' => count($committeeReferrals),
        'pending' => count(array_filter($committeeReferrals, fn($r) => $r['status'] === 'Pending')),
        'approved' => count(array_filter($committeeReferrals, fn($r) => $r['status'] === 'Approved'))
    ];
}

// Sort by total
usort($committeeStats, fn($a, $b) => $b['total'] - $a['total']);

// Calculate average turnaround time (for approved referrals)
$approvedRefs = getReferralsByStatus('Approved');
$totalDays = 0;
$count = 0;
foreach ($approvedRefs as $ref) {
    if (!empty($ref['created_date']) && !empty($ref['final_action_date'])) {
        $days = floor((strtotime($ref['final_action_date']) - strtotime($ref['created_date'])) / 86400);
        $totalDays += $days;
        $count++;
    }
}
$avgTurnaround = $count > 0 ? round($totalDays / $count, 1) : 0;
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-gray-600 mt-1">Comprehensive referral statistics and insights</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <p class="text-sm text-gray-600 mb-1">Total Referrals</p>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $totalReferrals; ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-gray-500">
        <p class="text-sm text-gray-600 mb-1">Pending</p>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $pendingReferrals; ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <p class="text-sm text-gray-600 mb-1">Approved</p>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $approvedReferrals; ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <p class="text-sm text-gray-600 mb-1">Rejected</p>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $rejectedReferrals; ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
        <p class="text-sm text-gray-600 mb-1">Overdue</p>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo $overdueReferrals; ?>
        </p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Type Distribution -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4"><i class="bi bi-pie-chart mr-2"></i>By Type</h3>
        <div class="space-y-3">
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">Ordinances</span>
                    <span class="text-sm font-semibold">
                        <?php echo $ordinances; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($ordinances / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">Resolutions</span>
                    <span class="text-sm font-semibold">
                        <?php echo $resolutions; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($resolutions / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">Communications</span>
                    <span class="text-sm font-semibold">
                        <?php echo $communications; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($communications / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4"><i class="bi bi-bar-chart mr-2"></i>By Priority</h3>
        <div class="space-y-3">
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">High Priority</span>
                    <span class="text-sm font-semibold">
                        <?php echo $highPriority; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($highPriority / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">Medium Priority</span>
                    <span class="text-sm font-semibold">
                        <?php echo $mediumPriority; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($mediumPriority / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-600">Low Priority</span>
                    <span class="text-sm font-semibold">
                        <?php echo $lowPriority; ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full"
                        style="width: <?php echo $totalReferrals > 0 ? ($lowPriority / $totalReferrals * 100) : 0; ?>%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4"><i class="bi bi-speedometer2 mr-2"></i>Performance</h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-600">Avg. Turnaround Time</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo $avgTurnaround; ?> days
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Approval Rate</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo $totalReferrals > 0 ? round(($approvedReferrals / $totalReferrals) * 100, 1) : 0; ?>%
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">On-Time Completion</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo $totalReferrals > 0 ? round((($totalReferrals - $overdueReferrals) / $totalReferrals) * 100, 1) : 0; ?>%
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Committee Performance -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-bold mb-4"><i class="bi bi-building mr-2"></i>Committee Performance</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pending</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Approved</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($committeeStats as $stat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            <?php echo htmlspecialchars($stat['name']); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php echo $stat['total']; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                <?php echo $stat['pending']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <?php echo $stat['approved']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-600 h-2 rounded-full"
                                        style="width: <?php echo $stat['total'] > 0 ? ($stat['approved'] / $stat['total'] * 100) : 0; ?>%">
                                    </div>
                                </div>
                                <span class="text-xs text-gray-600">
                                    <?php echo $stat['total'] > 0 ? round(($stat['approved'] / $stat['total']) * 100) : 0; ?>%
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Options -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-bold mb-4"><i class="bi bi-download mr-2"></i>Export Reports</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <i class="bi bi-file-pdf mr-2"></i>Export to PDF
        </button>
        <button class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="bi bi-file-excel mr-2"></i>Export to Excel
        </button>
        <button class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i class="bi bi-printer mr-2"></i>Print Report
        </button>
    </div>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Report Summary: <span class="font-medium"><?php echo $totalReferrals; ?></span> referrals analyzed
    </div>
    <div class="text-sm text-gray-500 italic">
        Avg. Turnaround: <?php echo $avgTurnaround; ?> days
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>