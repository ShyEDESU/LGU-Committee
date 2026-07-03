<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../app/helpers/CommitteeHelper.php';

// Public reports page - only fetch Approved reports
$search = $_GET['search'] ?? '';
$committeeFilter = $_GET['committee_id'] ?? '';

$filters = ['status' => 'Approved'];
if ($search) $filters['search'] = $search;
if ($committeeFilter) $filters['committee_id'] = $committeeFilter;

$reports = getAllReports($filters);
$committees = getAllCommittees();

include 'header.php';
?>

<!-- Hero / Header Section -->
<section class="hero-pattern text-white py-12 mb-8">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Approved Committee Reports</h1>
        <p class="text-lg text-red-100">Browse official reports and recommendations ratified by committees</p>
    </div>
</section>

<div class="container mx-auto px-4 pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Search and Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-funnel text-red-600 mr-2"></i>
                    Filter Reports
                </h3>
                
                <form method="GET" action="reports.php" class="space-y-4">
                    <!-- Search Keyword -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Search Keywords</label>
                        <div class="relative">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Search title or content..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-red-500 focus:border-red-500 text-sm">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <i class="bi bi-search"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Committee Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">By Committee</label>
                        <select name="committee_id"
                            class="w-full py-2 px-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">All Committees</option>
                            <?php foreach ($committees as $comm): ?>
                                <option value="<?php echo $comm['id']; ?>" <?php echo $committeeFilter == $comm['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($comm['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-red-650 hover:bg-red-750 text-white py-2 rounded-lg font-semibold transition flex items-center justify-center space-x-2 text-sm">
                            <i class="bi bi-search"></i>
                            <span>Search Reports</span>
                        </button>
                        
                        <?php if ($search || $committeeFilter): ?>
                            <a href="reports.php"
                                class="block text-center text-gray-500 hover:text-red-600 mt-2 text-xs font-semibold">
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reports List -->
        <div class="lg:col-span-3 space-y-6">
            <?php if (empty($reports)): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500">
                    <i class="bi bi-folder-x text-5xl text-gray-300 block mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-700">No Approved Reports Found</h3>
                    <p class="text-sm mt-1">Try adjusting your filters or search keywords.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($reports as $report): 
                        // Fetch signatures and committee members
                        $sigs = getReportSignatures($report['report_id']);
                        $members = getCommitteeMembersForReport($report['committee_id']);
                        
                        $signedCount = 0;
                        foreach ($sigs as $s) {
                            if ($s['status'] === 'Approved') $signedCount++;
                        }
                        
                        $recColor = 'green';
                        if ($report['recommendation'] === 'Disapprove') $recColor = 'red';
                        elseif ($report['recommendation'] === 'Amend') $recColor = 'yellow';
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <div>
                                        <div class="text-xs font-bold text-red-600 uppercase tracking-wider">
                                            <?php echo htmlspecialchars($report['committee_name']); ?>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($report['title']); ?></h2>
                                        <div class="text-xs text-gray-500 mt-2 flex items-center space-x-4">
                                            <span>Type: <?php echo htmlspecialchars($report['report_type']); ?></span>
                                            <span>•</span>
                                            <span>Date Approved: <?php echo date('M d, Y', strtotime($report['updated_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 shrink-0">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-<?php echo $recColor; ?>-100 text-<?php echo $recColor; ?>-800">
                                            <?php echo htmlspecialchars($report['recommendation']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4 border-t border-gray-100 pt-4 flex justify-between items-center text-sm">
                                    <div class="flex items-center space-x-2 text-gray-600">
                                        <i class="bi bi-patch-check-fill text-green-600"></i>
                                        <span>Official Ratification: <?php echo $signedCount; ?> of <?php echo count($members); ?> members signed</span>
                                    </div>
                                    
                                    <button onclick="toggleReportDetails(<?php echo $report['report_id']; ?>)"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold transition text-xs flex items-center">
                                        <span>View Details</span>
                                        <i id="arrow-icon-<?php echo $report['report_id']; ?>" class="bi bi-chevron-down ml-2 transition"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Expandable Details Area -->
                            <div id="details-<?php echo $report['report_id']; ?>" class="hidden bg-gray-50 border-t border-gray-200 p-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Content -->
                                    <div class="lg:col-span-2 space-y-4">
                                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Report Findings & Recommendations</h4>
                                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-inner font-sans text-gray-800 whitespace-pre-wrap leading-relaxed">
                                            <?php echo htmlspecialchars($report['content']); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Signatures Board -->
                                    <div class="lg:col-span-1 space-y-4">
                                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Signatures Registry</h4>
                                        <div class="space-y-3">
                                            <?php foreach ($members as $member):
                                                $sig = $sigs[$member['user_id']] ?? null;
                                                $badgeColor = 'gray';
                                                $statusText = 'No response';
                                                $icon = 'bi-clock';
                                                
                                                if ($sig) {
                                                    if ($sig['status'] === 'Approved') {
                                                        $badgeColor = 'green';
                                                        $statusText = 'Signed';
                                                        $icon = 'bi-check-circle-fill';
                                                    } elseif ($sig['status'] === 'Dissented') {
                                                        $badgeColor = 'red';
                                                        $statusText = 'Dissented';
                                                        $icon = 'bi-x-circle-fill';
                                                    } elseif ($sig['status'] === 'Abstained') {
                                                        $badgeColor = 'yellow';
                                                        $statusText = 'Abstained';
                                                        $icon = 'bi-dash-circle-fill';
                                                    }
                                                }
                                                ?>
                                                <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 text-xs">
                                                    <div>
                                                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($member['name']); ?></div>
                                                        <div class="text-gray-500 text-3xs mt-0.5"><?php echo htmlspecialchars($member['role']); ?></div>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-semibold bg-<?php echo $badgeColor; ?>-100 text-<?php echo $badgeColor; ?>-800">
                                                        <i class="bi <?php echo $icon; ?> mr-1"></i>
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleReportDetails(reportId) {
    const details = document.getElementById('details-' + reportId);
    const arrow = document.getElementById('arrow-icon-' + reportId);
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        details.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}
</script>

<?php include 'footer.php'; ?>
