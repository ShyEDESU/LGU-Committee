<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$pageTitle = "Reports & Analytics";
include '../../includes/header.php';

// Fetch advanced analytics data
$stats = getOverallStats();
$trends = getMonthlyTrends();
$committeeStats = getActiveCommittees();
$attendanceMetrics = getAttendanceMetrics();
$legislativeMetrics = getLegislativeCycleTime();
$taskMetrics = getTaskEfficiency();

// Prepare trend chart data
$months = array_keys($trends);
$meetingTrendData = array_map(fn($m) => $trends[$m]['meetings'], $months);
$documentTrendData = array_map(fn($m) => $trends[$m]['documents'], $months);
$monthLabels = array_map(fn($m) => date('M Y', strtotime($m)), $months);

// Prepare attendance trend data
$attendanceMonths = array_keys($attendanceMetrics['monthly_trend']);
$attendanceRates = array_values($attendanceMetrics['monthly_trend']);
$attendanceLabels = array_map(fn($m) => date('M Y', strtotime($m)), $attendanceMonths);
?>

<style>
    /* Traditional Admin Style Tokens */
    .admin-card {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        border-top: 4px solid #dc2626; /* Signature red top border */
    }

    .dark .admin-card {
        background: #1f2937;
        border-color: #374151;
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .dark .kpi-card {
        background: #1f2937;
        border-color: #374151;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* Page Header Overrides */
    .page-title-group {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .dark .page-title-group {
        border-color: #374151;
    }
</style>

<div class="px-6 py-6 max-w-full mx-auto">
    <!-- Traditional Page Header -->
    <div class="page-title-group flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">System-wide performance metrics and legislative insights</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center gap-2 border border-gray-300 dark:border-gray-600">
                <i class="bi bi-printer"></i> Export
            </button>
            <a href="generate.php"
                class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition flex items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-text"></i> Generate Custom Report
            </a>
        </div>
    </div>

    <!-- Main Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <!-- Attendance Analytics -->
        <div class="lg:col-span-2 admin-card border-t-4 border-red-600 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Member Participation</h3>
                    <p class="text-sm text-gray-500">Attendance trends over time</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-red-600"><?php echo $attendanceMetrics['overall_avg']; ?>%</span>
                    <p class="text-[10px] text-gray-400 uppercase font-black">Average</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>

        <!-- Legislative Pipeline -->
        <div class="admin-card border-t-4 border-red-600 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Legislative Pipeline</h3>
            <p class="text-sm text-gray-500 mb-6">Avg cycle time (days)</p>

            <div class="flex flex-col items-center justify-center">
                <!-- Fix: Added viewBox to prevent truncation and centered properly -->
                <div class="relative w-40 h-40 mb-4">
                    <svg viewBox="0 0 192 192" class="w-full h-full transform -rotate-90">
                        <circle cx="96" cy="96" r="80" stroke="currentColor" stroke-width="14" fill="transparent"
                            class="text-gray-100 dark:text-gray-800" />
                        <circle cx="96" cy="96" r="80" stroke="#dc2626" stroke-width="14" fill="transparent"
                            stroke-dasharray="502.65"
                            stroke-dashoffset="<?php echo 502.65 * (1 - min($legislativeMetrics['avg_cycle_days'] / 30, 1)); ?>"
                            stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo $legislativeMetrics['avg_cycle_days']; ?></span>
                        <span class="text-[10px] text-gray-400 uppercase font-bold">Days Avg</span>
                    </div>
                </div>
                <div class="w-full space-y-2 mt-2">
                    <?php foreach ($legislativeMetrics['type_distribution'] as $type => $count): ?>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600 dark:text-gray-400"><?php echo ucwords(str_replace('_', ' ', $type)); ?></span>
                            <span class="font-bold text-gray-900 dark:text-white"><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Task Efficiency -->
        <div class="admin-card border-t-4 border-red-600 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Efficiency Index</h3>
            <p class="text-sm text-gray-500 mb-6">Bottleneck detection</p>

            <div class="space-y-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-red-600 font-black uppercase tracking-wider">Aging Backlog</span>
                        <i class="bi bi-clock-history text-red-600"></i>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $taskMetrics['older_tasks_count']; ?></span>
                        <span class="text-xs text-gray-500">pending tasks</span>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-blue-600 font-black uppercase tracking-wider">Velocity</span>
                        <i class="bi bi-lightning-charge text-blue-600"></i>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $taskMetrics['avg_completion_days']; ?></span>
                        <span class="text-xs text-gray-500">days to complete</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Section -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
        <?php
        $kpiItems = [
            ['Committees', $stats['committees'], 'bi-building', 'text-blue-600', 'bg-blue-50'],
            ['Meetings', $stats['meetings'], 'bi-calendar-check', 'text-red-600', 'bg-red-50'],
            ['Referrals', $stats['referrals'], 'bi-arrow-repeat', 'text-amber-600', 'bg-amber-50'],
            ['Active Tasks', $stats['tasks'], 'bi-check2-square', 'text-emerald-600', 'bg-emerald-50'],
            ['System Users', $stats['users'], 'bi-people', 'text-purple-600', 'bg-purple-50']
        ];
        foreach ($kpiItems as [$label, $val, $icon, $tColor, $bgColor]):
            ?>
            <div class="kpi-card p-5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg <?php echo $bgColor; ?> dark:bg-gray-700 flex items-center justify-center <?php echo $tColor; ?>">
                        <i class="bi <?php echo $icon; ?> text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest"><?php echo $label; ?></p>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo number_format($val); ?></h4>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Secondary Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 admin-card border-t-4 border-red-600 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Operational Velocity</h3>
            <div class="chart-container">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        <div class="admin-card border-t-4 border-red-600 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Committee Ranking</h3>
            <div class="space-y-4">
                <?php foreach (array_slice($committeeStats, 0, 5) as $idx => $comm): ?>
                    <div class="pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate pr-2">
                                <?php echo $comm['committee_name']; ?>
                            </span>
                            <span class="text-xs font-bold text-red-600"><?php echo $comm['meeting_count']; ?></span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full">
                            <div class="bg-red-600 h-1.5 rounded-full" 
                                style="width: <?php echo ($comm['meeting_count'] / max(1, $committeeStats[0]['meeting_count'])) * 100; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="../committee-profiles/index.php" class="block w-full text-center mt-6 text-xs text-red-600 font-bold hover:underline">
                View All Committees
            </a>
        </div>
    </div>
</div>

<script>
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#6b7280';

    document.addEventListener('DOMContentLoaded', function () {
        // Attendance Trend Chart
        const attendanceCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($attendanceLabels); ?>,
                datasets: [{
                    label: 'Attendance Rate %',
                    data: <?php echo json_encode($attendanceRates); ?>,
                    borderColor: '#dc2626',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#dc2626',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { callback: value => value + '%' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Velocity Chart
        const velocityCtx = document.getElementById('velocityChart').getContext('2d');
        new Chart(velocityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthLabels); ?>,
                datasets: [
                    {
                        label: 'Meetings',
                        data: <?php echo json_encode($meetingTrendData); ?>,
                        backgroundColor: '#dc2626',
                        borderRadius: 4
                    },
                    {
                        label: 'Documents',
                        data: <?php echo json_encode($documentTrendData); ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20 } } },
                scales: {
                    y: { grid: { borderDash: [5, 5], drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>
