<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId   = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'User';

// Filter params
$filterStatus    = $_GET['status']    ?? 'all';
$filterCommittee = $_GET['committee'] ?? 'all';

// Fetch all reports with committee name and author
$sql = "SELECT r.*, 
               c.committee_name,
               CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM reports r
        LEFT JOIN committees c ON r.committee_id = c.committee_id
        LEFT JOIN users u ON r.created_by = u.user_id
        WHERE 1=1";

$params = [];
$types  = '';

if ($filterStatus !== 'all') {
    $sql     .= " AND r.status = ?";
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($filterCommittee !== 'all') {
    $sql     .= " AND r.committee_id = ?";
    $params[] = (int)$filterCommittee;
    $types   .= 'i';
}
$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result  = $stmt->get_result();
$reports = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}
$stmt->close();

// Count by status for the summary cards
$counts = ['Draft' => 0, 'Voting' => 0, 'Approved' => 0, 'Rejected' => 0];
foreach ($reports as $r) {
    $s = $r['status'] ?? 'Draft';
    if (isset($counts[$s])) $counts[$s]++;
}

$committees = getAllCommittees();
$pageTitle  = 'Committee Reports';
include '../../includes/header.php';
?>

<div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                    <i class="bi bi-file-text text-green-600 dark:text-green-400 text-xl"></i>
                </span>
                Committee Reports Management
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                All reports drafted by committees. Members cast Approve / Dissent / Abstain votes to finalize each report.
            </p>
        </div>
        <?php if (in_array($userRole, ['Admin', 'Super Admin'])): ?>
        <a href="../committee-profiles/index.php"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition flex items-center gap-2 shadow-sm">
            <i class="bi bi-plus-circle"></i> Go to Committees to Draft
        </a>
        <?php endif; ?>
    </div>

    <!-- Status Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $cardDefs = [
            ['label' => 'Draft',    'icon' => 'bi-pencil-square', 'color' => 'yellow', 'status' => 'Draft'],
            ['label' => 'Open for Voting', 'icon' => 'bi-hand-index-thumb', 'color' => 'blue', 'status' => 'Voting'],
            ['label' => 'Approved', 'icon' => 'bi-patch-check-fill', 'color' => 'green', 'status' => 'Approved'],
            ['label' => 'Rejected', 'icon' => 'bi-x-circle-fill',   'color' => 'red',   'status' => 'Rejected'],
        ];
        foreach ($cardDefs as $card):
            $active = ($filterStatus === $card['status']);
            $c = $card['color'];
        ?>
        <a href="?status=<?php echo $card['status']; ?>&committee=<?php echo urlencode($filterCommittee); ?>"
           class="bg-white dark:bg-gray-800 rounded-xl border <?php echo $active ? "border-{$c}-500 ring-2 ring-{$c}-300" : 'border-gray-200 dark:border-gray-700'; ?> p-5 hover:shadow-md transition flex items-center gap-4">
            <div class="p-3 bg-<?php echo $c; ?>-100 dark:bg-<?php echo $c; ?>-900/30 rounded-xl flex-shrink-0">
                <i class="bi <?php echo $card['icon']; ?> text-<?php echo $c; ?>-600 dark:text-<?php echo $c; ?>-400 text-2xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900 dark:text-white"><?php echo $counts[$card['status']]; ?></p>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight"><?php echo $card['label']; ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex flex-wrap gap-3 items-center">
        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400 flex items-center gap-1">
            <i class="bi bi-funnel"></i> Filter:
        </span>

        <?php foreach (['all' => 'All Statuses', 'Draft' => 'Draft', 'Voting' => 'Open for Voting', 'Approved' => 'Approved', 'Rejected' => 'Rejected'] as $val => $label): ?>
        <a href="?status=<?php echo $val; ?>&committee=<?php echo urlencode($filterCommittee); ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $filterStatus === $val ? 'bg-green-600 text-white shadow' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
            <?php echo $label; ?>
        </a>
        <?php endforeach; ?>

        <div class="ml-auto">
            <select onchange="window.location='?status=<?php echo urlencode($filterStatus); ?>&committee='+this.value"
                    class="text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                <option value="all" <?php echo $filterCommittee === 'all' ? 'selected' : ''; ?>>All Committees</option>
                <?php foreach ($committees as $com): ?>
                <option value="<?php echo $com['committee_id']; ?>"
                        <?php echo $filterCommittee == $com['committee_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($com['committee_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-bold text-gray-900 dark:text-white">
                <?php echo count($reports); ?> Report<?php echo count($reports) !== 1 ? 's' : ''; ?>
                <?php if ($filterStatus !== 'all'): ?>
                    <span class="text-sm font-normal text-gray-400 ml-1">— filtered by "<?php echo htmlspecialchars($filterStatus); ?>"</span>
                <?php endif; ?>
            </h2>
            <?php if ($filterStatus !== 'all' || $filterCommittee !== 'all'): ?>
            <a href="index.php" class="text-xs text-red-600 hover:underline font-semibold flex items-center gap-1">
                <i class="bi bi-x-circle"></i> Clear filters
            </a>
            <?php endif; ?>
        </div>

        <?php if (empty($reports)): ?>
        <div class="text-center py-16 px-6">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-file-earmark-text text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">No Reports Found</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                Reports are drafted from inside each Committee Profile. Go to a committee profile and click <strong>"Draft New"</strong> in the Committee Reports card.
            </p>
            <a href="../committee-profiles/index.php"
               class="inline-flex items-center gap-2 mt-4 bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition">
                <i class="bi bi-building"></i> View Committees
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-5 py-3 font-bold">Report Title</th>
                        <th class="px-5 py-3 font-bold">Committee</th>
                        <th class="px-5 py-3 font-bold">Drafted By</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                        <th class="px-5 py-3 font-bold">Date</th>
                        <th class="px-5 py-3 font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($reports as $report):
                        $statusMap = [
                            'Draft'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'Voting'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                            'Approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                            'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                        ];
                        $statusClass = $statusMap[$report['status'] ?? 'Draft'] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-900 dark:text-white leading-tight">
                                <?php echo htmlspecialchars($report['title']); ?>
                            </p>
                            <?php if (!empty($report['recommendation'])): ?>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Rec: <span class="font-medium"><?php echo htmlspecialchars($report['recommendation']); ?></span>
                            </p>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            <?php echo htmlspecialchars($report['committee_name'] ?? '—'); ?>
                        </td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            <?php echo htmlspecialchars($report['author_name'] ?? '—'); ?>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($report['status'] ?? 'Draft'); ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                            <?php echo date('M j, Y', strtotime($report['created_at'])); ?>
                        </td>
                        <td class="px-5 py-4">
                            <a href="view.php?id=<?php echo $report['report_id']; ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40 rounded-lg text-xs font-semibold transition">
                                <i class="bi bi-eye"></i> View / Vote
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end page content -->
</div><!-- /#module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>
