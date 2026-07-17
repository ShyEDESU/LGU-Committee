<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_POST['committee_id'] ?? 'all';
$reportType  = $_POST['report_type']  ?? 'full';
$dateFrom    = $_POST['date_from']    ?? '';
$dateTo      = $_POST['date_to']      ?? '';

// Validate dates
if (!$dateFrom) $dateFrom = date('Y-m-01', strtotime('-1 month'));
if (!$dateTo)   $dateTo   = date('Y-m-d');

// Fetch matching committee info
$committee = null;
if ($committeeId !== 'all') {
    $committee = getCommitteeById((int)$committeeId);
    if (!$committee) {
        die("Committee not found.");
    }
}

$title = "";
$subtitle = "For period: " . date('M j, Y', strtotime($dateFrom)) . " to " . date('M j, Y', strtotime($dateTo));

// Get DB stats safely
global $conn;

// 1. Fetch meetings held
$meetings = [];
$meetingSql = "SELECT m.*, c.committee_name 
               FROM meetings m
               JOIN committees c ON m.committee_id = c.committee_id
               WHERE m.meeting_date BETWEEN ? AND ?";
if ($committee) {
    $meetingSql .= " AND m.committee_id = ?";
}
$meetingSql .= " ORDER BY m.meeting_date DESC";

$stmt = $conn->prepare($meetingSql);
if ($committee) {
    $stmt->bind_param("ssi", $dateFrom, $dateTo, $committeeId);
} else {
    $stmt->bind_param("ss", $dateFrom, $dateTo);
}
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $meetings[] = $r;
    }
}
$stmt->close();

// 2. Fetch tasks/action items
$tasks = [];
$taskSql = "SELECT t.*, c.committee_name 
            FROM tasks t
            LEFT JOIN committees c ON t.committee_id = c.committee_id
            WHERE t.created_at BETWEEN ? AND ?";
if ($committee) {
    $taskSql .= " AND t.committee_id = ?";
}
$taskSql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($taskSql);
if ($committee) {
    $stmt->bind_param("ssi", $dateFrom, $dateTo, $committeeId);
} else {
    $stmt->bind_param("ss", $dateFrom, $dateTo);
}
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $tasks[] = $r;
    }
}
$stmt->close();

// 3. Fetch referrals
$referrals = [];
$refSql = "SELECT r.*, ld.title as doc_title, c.committee_name 
           FROM referrals r
           JOIN legislative_documents ld ON r.document_id = ld.document_id
           LEFT JOIN committees c ON r.to_committee_id = c.committee_id
           WHERE r.referred_date BETWEEN ? AND ?";
if ($committee) {
    $refSql .= " AND r.to_committee_id = ?";
}
$refSql .= " ORDER BY r.referred_date DESC";

$stmt = $conn->prepare($refSql);
if ($committee) {
    $stmt->bind_param("ssi", $dateFrom, $dateTo, $committeeId);
} else {
    $stmt->bind_param("ss", $dateFrom, $dateTo);
}
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $referrals[] = $r;
    }
}
$stmt->close();

// Calculate simple rates
$meetingsCount = count($meetings);
$tasksCount = count($tasks);
$completedTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'Done'));
$taskCompletionRate = $tasksCount > 0 ? round(($completedTasks / $tasksCount) * 100) : 0;

$refCount = count($referrals);
$actedRefs = count(array_filter($referrals, fn($rf) => $rf['status'] !== 'Pending'));
$refActionRate = $refCount > 0 ? round(($actedRefs / $refCount) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legislative Report Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .print-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

    <!-- Print control banner -->
    <div class="no-print bg-white border-b border-gray-200 py-4 px-6 sticky top-0 flex items-center justify-between shadow-sm z-50">
        <div class="flex items-center gap-3">
            <span class="p-2 bg-red-100 rounded-lg text-red-600"><i class="bi bi-file-pdf"></i></span>
            <div>
                <p class="font-bold text-gray-900 text-sm">Report Print Preview</p>
                <p class="text-xs text-gray-500">Save as PDF or print this professional document.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded-xl text-sm transition">
                <i class="bi bi-printer mr-2"></i> Print / Save as PDF
            </button>
            <button onclick="window.close()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                Close Preview
            </button>
        </div>
    </div>

    <!-- Professional Report Sheet Layout -->
    <div class="max-w-4xl mx-auto my-8 bg-white p-10 border border-gray-200 rounded-2xl shadow-sm print-card">
        
        <!-- Header -->
        <div class="border-b-4 border-red-600 pb-6 mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Valenzuela LGU Committee Report</h1>
                <p class="text-gray-500 font-medium mt-1">Legislative Monitoring &amp; Oversight System</p>
                <p class="text-xs text-red-600 font-bold tracking-widest uppercase mt-3"><?php echo $subtitle; ?></p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold bg-red-50 text-red-600 px-3 py-1.5 rounded-full uppercase tracking-wider">
                    <?php echo htmlspecialchars($reportType === 'full' ? 'Operational Summary' : ucwords($reportType)); ?>
                </span>
            </div>
        </div>

        <!-- Metadata / Info Box -->
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-5 rounded-xl border border-gray-100 mb-8 text-sm">
            <div>
                <p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Filter Target</p>
                <p class="font-bold text-gray-800 text-base"><?php echo $committee ? htmlspecialchars($committee['name']) : 'All Committees (System-wide Summary)'; ?></p>
            </div>
            <div>
                <p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Date Generated</p>
                <p class="font-bold text-gray-800 text-base"><?php echo date('F j, Y g:i A'); ?></p>
            </div>
        </div>

        <!-- Quick Metrics Grid -->
        <div class="grid grid-cols-4 gap-4 mb-8 text-center">
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                <p class="text-2xl font-black text-red-600"><?php echo $meetingsCount; ?></p>
                <p class="text-xs text-gray-500 font-semibold mt-1">Meetings Held</p>
            </div>
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                <p class="text-2xl font-black text-gray-900"><?php echo $tasksCount; ?></p>
                <p class="text-xs text-gray-500 font-semibold mt-1">Action Items</p>
            </div>
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                <p class="text-2xl font-black text-green-600"><?php echo $taskCompletionRate; ?>%</p>
                <p class="text-xs text-gray-500 font-semibold mt-1">Task Completion</p>
            </div>
            <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                <p class="text-2xl font-black text-blue-600"><?php echo $refActionRate; ?>%</p>
                <p class="text-xs text-gray-500 font-semibold mt-1">Referrals Acted</p>
            </div>
        </div>

        <!-- Meeting Schedule Log -->
        <?php if ($reportType === 'full' || $reportType === 'attendance'): ?>
        <div class="mb-8">
            <h2 class="text-lg font-black text-gray-900 uppercase border-b-2 border-gray-100 pb-2 mb-4">Meetings &amp; Hearings History</h2>
            <?php if (empty($meetings)): ?>
                <p class="text-sm text-gray-400 italic">No meetings scheduled or held during this timeframe.</p>
            <?php else: ?>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold uppercase">
                            <th class="p-2 border border-gray-200">Date/Time</th>
                            <th class="p-2 border border-gray-200">Committee</th>
                            <th class="p-2 border border-gray-200">Location</th>
                            <th class="p-2 border border-gray-200">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meetings as $m): ?>
                        <tr>
                            <td class="p-2 border border-gray-200 font-semibold"><?php echo date('M j, Y', strtotime($m['meeting_date'])) . ' ' . date('g:i A', strtotime($m['meeting_date'] . ' ' . $m['time_start'])); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo htmlspecialchars($m['committee_name']); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo htmlspecialchars($m['location'] ?? 'Session Hall'); ?></td>
                            <td class="p-2 border border-gray-200 font-medium text-red-600"><?php echo htmlspecialchars($m['meeting_type'] ?? 'Regular'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Action Items (Tasks) Tracker -->
        <?php if ($reportType === 'full' || $reportType === 'tasks'): ?>
        <div class="mb-8">
            <h2 class="text-lg font-black text-gray-900 uppercase border-b-2 border-gray-100 pb-2 mb-4">Action Item (Task) Tracking Log</h2>
            <?php if (empty($tasks)): ?>
                <p class="text-sm text-gray-400 italic">No tasks created during this timeframe.</p>
            <?php else: ?>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold uppercase">
                            <th class="p-2 border border-gray-200">Action Item / Task</th>
                            <th class="p-2 border border-gray-200">Committee</th>
                            <th class="p-2 border border-gray-200">Due Date</th>
                            <th class="p-2 border border-gray-200">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td class="p-2 border border-gray-200 font-semibold"><?php echo htmlspecialchars($t['title']); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo htmlspecialchars($t['committee_name'] ?? 'N/A'); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo date('M j, Y', strtotime($t['due_date'])); ?></td>
                            <td class="p-2 border border-gray-200">
                                <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] <?php echo $t['status'] === 'Done' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Legislative Referrals List -->
        <?php if ($reportType === 'full' || $reportType === 'referrals'): ?>
        <div class="mb-8">
            <h2 class="text-lg font-black text-gray-900 uppercase border-b-2 border-gray-100 pb-2 mb-4">Legislative Referrals Log</h2>
            <?php if (empty($referrals)): ?>
                <p class="text-sm text-gray-400 italic">No referrals processed during this timeframe.</p>
            <?php else: ?>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold uppercase">
                            <th class="p-2 border border-gray-200">Referral Document</th>
                            <th class="p-2 border border-gray-200">Assigned Committee</th>
                            <th class="p-2 border border-gray-200">Date Referred</th>
                            <th class="p-2 border border-gray-200">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $r): ?>
                        <tr>
                            <td class="p-2 border border-gray-200 font-semibold"><?php echo htmlspecialchars($r['doc_title']); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo htmlspecialchars($r['committee_name'] ?? 'N/A'); ?></td>
                            <td class="p-2 border border-gray-200"><?php echo date('M j, Y', strtotime($r['referred_date'])); ?></td>
                            <td class="p-2 border border-gray-200">
                                <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] <?php echo $r['status'] === 'Pending' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <?php echo htmlspecialchars($r['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sign-Off Line -->
        <div class="mt-12 pt-8 border-t border-gray-200 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <div class="h-12"></div>
                <div class="border-b border-gray-400 w-48 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Officer'); ?></p>
                <p class="text-gray-400 uppercase tracking-widest text-[9px]">Generated By</p>
            </div>
            <div>
                <div class="h-12"></div>
                <div class="border-b border-gray-400 w-48 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800">Hon. Secretariat Office</p>
                <p class="text-gray-400 uppercase tracking-widest text-[9px]">Certified Correct</p>
            </div>
        </div>

    </div>
</body>
</html>
