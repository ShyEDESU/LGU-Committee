<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/NotificationHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId   = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'User';
$userName = $_SESSION['user_name'] ?? 'User';

$reportId = (int)($_GET['id'] ?? 0);
$report   = $reportId ? getReportById($reportId) : null;

if (!$report) {
    $_SESSION['error_message'] = 'Report not found.';
    header('Location: ../committee-profiles/index.php');
    exit();
}

$committee  = getCommitteeById($report['committee_id']);
$members    = getCommitteeMembersForReport($report['committee_id']);
$signatures = getReportSignatures($reportId);

$isAdmin     = in_array($userRole, ['Admin', 'Super Admin']);
$isLeadership = $userId == ($committee['chairperson_id'] ?? 0)
             || $userId == ($committee['vice_chair_id']  ?? 0)
             || $userId == ($committee['secretary_id']   ?? 0);
$isMember    = in_array($userId, array_column($members, 'user_id'));
$canSign     = ($isMember || $isLeadership) && $report['status'] === 'Voting';
$canManage   = $isAdmin || $isLeadership;

// Build member map
$memberMap = [];
foreach ($members as $m) {
    $memberMap[$m['user_id']] = $m;
}

// ── Handle actions ──────────────────────────────────────────────
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Cast signature vote
    if ($action === 'sign' && $canSign) {
        $vote = $_POST['vote'] ?? '';
        if (in_array($vote, ['Approved', 'Dissented', 'Abstained'])) {
            $ok = submitSignature($reportId, $userId, $vote);
            if ($ok) {
                $success = "Your vote ({$vote}) has been recorded.";
                // Reload to reflect updated counts
                $signatures = getReportSignatures($reportId);
                $report     = getReportById($reportId);
            } else {
                $error = 'Failed to record your vote. Please try again.';
            }
        }
    }

    // Open report for voting
    if ($action === 'open_voting' && $canManage && $report['status'] === 'Draft') {
        updateReport($reportId, $report['title'], $report['report_type'], $report['recommendation'], $report['content'], 'Voting');
        // Notify members to sign
        foreach ($members as $m) {
            createNotification(
                $m['user_id'],
                '🗳️ Signature Required',
                "The report \"{$report['title']}\" is now open for voting. Please cast your signature.",
                'action_item',
                'high',
                "pages/committee-reports/view.php?id={$reportId}"
            );
        }
        $_SESSION['success_message'] = 'Report is now open for voting. Members have been notified.';
        header("Location: view.php?id={$reportId}");
        exit();
    }

    // Delete report (admin/leadership, only draft stage)
    if ($action === 'delete' && $canManage && $report['status'] === 'Draft') {
        deleteReport($reportId);
        $_SESSION['success_message'] = 'Report deleted.';
        header('Location: ../committee-profiles/view.php?id=' . $report['committee_id']);
        exit();
    }
}

// Signature summary counts
$approvedCount  = count(array_filter($signatures, fn($s) => $s['status'] === 'Approved'));
$dissentedCount = count(array_filter($signatures, fn($s) => $s['status'] === 'Dissented'));
$abstainedCount = count(array_filter($signatures, fn($s) => $s['status'] === 'Abstained'));
$totalMembers   = count($members);
$signedCount    = count($signatures);
$pendingCount   = $totalMembers - $signedCount;
$requiredCount  = floor($totalMembers / 2) + 1;

// Current user's signature
$mySignature = $signatures[$userId] ?? null;

// Status colours
$statusColor = match($report['status']) {
    'Draft'    => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-800 dark:text-yellow-300', 'icon' => 'bi-pencil'],
    'Voting'   => ['bg' => 'bg-blue-100 dark:bg-blue-900/30',   'text' => 'text-blue-800 dark:text-blue-300',   'icon' => 'bi-hand-index'],
    'Approved' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-800 dark:text-green-300', 'icon' => 'bi-check-circle'],
    'Rejected' => ['bg' => 'bg-red-100 dark:bg-red-900/30',     'text' => 'text-red-800 dark:text-red-300',     'icon' => 'bi-x-circle'],
    default    => ['bg' => 'bg-gray-100 dark:bg-gray-700',       'text' => 'text-gray-700 dark:text-gray-300',   'icon' => 'bi-file'],
};

$pageTitle = 'Committee Report — ' . htmlspecialchars($report['title']);
include '../../includes/header.php';
?>

<div class="container-fluid space-y-6">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../committee-profiles/index.php" class="text-red-600">Committee Profiles</a></li>
            <li class="breadcrumb-item"><a href="../committee-profiles/view.php?id=<?php echo $report['committee_id']; ?>" class="text-red-600"><?php echo htmlspecialchars($committee['name'] ?? 'Committee'); ?></a></li>
            <li class="breadcrumb-item active">Report</li>
        </ol>
    </nav>

    <?php if ($success): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
            <p class="text-green-800 dark:text-green-300 font-medium"><i class="bi bi-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
            <p class="text-red-800 dark:text-red-300 font-medium"><i class="bi bi-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Report Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Report Header Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?php echo $statusColor['bg'] . ' ' . $statusColor['text']; ?> mb-3">
                            <i class="bi <?php echo $statusColor['icon']; ?>"></i>
                            <?php echo htmlspecialchars($report['status']); ?>
                        </span>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($report['title']); ?></h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <?php echo htmlspecialchars($report['report_type'] ?? 'Committee Report'); ?> ·
                            Drafted by <?php echo htmlspecialchars($report['creator_name'] ?? 'N/A'); ?> ·
                            <?php echo date('F j, Y', strtotime($report['created_at'])); ?>
                        </p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <?php if ($canManage && $report['status'] === 'Draft'): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Open this report for voting? All members will be notified.')">
                                <input type="hidden" name="action" value="open_voting">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                    <i class="bi bi-hand-index mr-1"></i> Open for Voting
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this report? This cannot be undone.')">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="bg-gray-200 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm transition">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recommendation Badge -->
                <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Committee Recommendation</p>
                    <?php
                    $recColor = match($report['recommendation'] ?? '') {
                        'Approve'         => 'text-green-700 dark:text-green-400',
                        'Disapprove'      => 'text-red-700 dark:text-red-400',
                        'Amend'           => 'text-amber-700 dark:text-amber-400',
                        'Defer'           => 'text-blue-700 dark:text-blue-400',
                        'Return to Sponsor' => 'text-purple-700 dark:text-purple-400',
                        default           => 'text-gray-700 dark:text-gray-300'
                    };
                    ?>
                    <p class="text-2xl font-black <?php echo $recColor; ?>"><?php echo htmlspecialchars($report['recommendation'] ?? 'N/A'); ?></p>
                </div>

                <!-- Content -->
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">Report Content</p>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <?php echo nl2br(htmlspecialchars($report['content'] ?? '')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Signature Panel -->
        <div class="space-y-6">

            <!-- Voting Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-pen text-red-500"></i> Signature Summary
                </h2>
                <div class="space-y-3 mb-4">
                    <!-- Progress bar -->
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Signatures: <?php echo $signedCount; ?> / <?php echo $totalMembers; ?></span>
                            <span>Required: <?php echo $requiredCount; ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full transition-all duration-500"
                                 style="width: <?php echo $totalMembers > 0 ? round(($signedCount/$totalMembers)*100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <!-- Counts -->
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-3">
                            <p class="text-2xl font-black text-green-700 dark:text-green-400"><?php echo $approvedCount; ?></p>
                            <p class="text-xs text-green-700 dark:text-green-400 font-semibold">Approved</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3">
                            <p class="text-2xl font-black text-red-700 dark:text-red-400"><?php echo $dissentedCount; ?></p>
                            <p class="text-xs text-red-700 dark:text-red-400 font-semibold">Dissented</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                            <p class="text-2xl font-black text-gray-700 dark:text-gray-300"><?php echo $abstainedCount; ?></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Abstained</p>
                        </div>
                    </div>
                </div>

                <!-- Current User Voting Buttons -->
                <?php if ($report['status'] === 'Voting'): ?>
                    <?php if ($canSign): ?>
                        <?php if ($mySignature): ?>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700 text-center">
                                <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                                    <i class="bi bi-check2-circle mr-1"></i>
                                    You voted: <strong><?php echo htmlspecialchars($mySignature['status']); ?></strong>
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">You can change your vote below.</p>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="mt-3 space-y-2">
                            <input type="hidden" name="action" value="sign">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Cast Your Signature</p>
                            <button type="submit" name="vote" value="Approved"
                                    class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold transition flex items-center justify-center gap-2">
                                <i class="bi bi-hand-thumbs-up"></i> Approve
                            </button>
                            <button type="submit" name="vote" value="Dissented"
                                    onclick="return confirm('Are you sure you want to dissent?')"
                                    class="w-full py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition flex items-center justify-center gap-2">
                                <i class="bi bi-hand-thumbs-down"></i> Dissent
                            </button>
                            <button type="submit" name="vote" value="Abstained"
                                    class="w-full py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold transition flex items-center justify-center gap-2">
                                <i class="bi bi-dash-circle"></i> Abstain
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">This report is open for voting by committee members.</p>
                        </div>
                    <?php endif; ?>
                <?php elseif ($report['status'] === 'Draft'): ?>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-700 text-center">
                        <i class="bi bi-pencil-square text-yellow-600 text-2xl block mb-1"></i>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 font-semibold">Still in Draft</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                            <?php echo $canManage ? 'Click "Open for Voting" to collect signatures.' : 'Waiting to be opened for voting.'; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl text-center">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Voting is closed.</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Final status: <strong><?php echo htmlspecialchars($report['status']); ?></strong></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Member Signatures List -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-people text-red-500"></i> Committee Members (<?php echo $totalMembers; ?>)
                </h2>
                <div class="space-y-2">
                    <?php foreach ($members as $m):
                        $sig = $signatures[$m['user_id']] ?? null;
                        $sigBadge = match($sig['status'] ?? '') {
                            'Approved'  => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-400', 'icon' => 'bi-check-circle-fill', 'label' => 'Approved'],
                            'Dissented' => ['bg' => 'bg-red-100 dark:bg-red-900/30',     'text' => 'text-red-700 dark:text-red-400',     'icon' => 'bi-x-circle-fill',   'label' => 'Dissented'],
                            'Abstained' => ['bg' => 'bg-gray-100 dark:bg-gray-700',       'text' => 'text-gray-600 dark:text-gray-400',   'icon' => 'bi-dash-circle-fill','label' => 'Abstained'],
                            default     => ['bg' => 'bg-gray-50 dark:bg-gray-700/50',     'text' => 'text-gray-400 dark:text-gray-500',   'icon' => 'bi-clock',           'label' => 'Pending'],
                        };
                    ?>
                        <div class="flex items-center justify-between p-3 rounded-xl <?php echo $sigBadge['bg']; ?>">
                            <div>
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($m['name']); ?>
                                    <?php if ($m['user_id'] == $userId): ?>
                                        <span class="text-xs font-normal text-gray-400">(You)</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs <?php echo $sigBadge['text']; ?>"><?php echo htmlspecialchars($m['role']); ?></p>
                            </div>
                            <span class="flex items-center gap-1 text-xs font-bold <?php echo $sigBadge['text']; ?>">
                                <i class="bi <?php echo $sigBadge['icon']; ?>"></i>
                                <?php echo $sigBadge['label']; ?>
                                <?php if ($sig && !empty($sig['signed_at'])): ?>
                                    <span class="text-gray-400 font-normal ml-1"><?php echo date('M j', strtotime($sig['signed_at'])); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($members)): ?>
                        <p class="text-sm text-gray-400 text-center py-4">No committee members found.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- END RIGHT -->
    </div><!-- END GRID -->
</div><!-- /.container-fluid -->
</div><!-- /#module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>
