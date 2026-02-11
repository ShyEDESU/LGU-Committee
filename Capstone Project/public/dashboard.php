<?php
require_once __DIR__ . '/../config/session_config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Set user variables from session
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$userId = $_SESSION['user_id'];

// Fetch upcoming meetings (next 30 days)
require_once __DIR__ . '/../app/helpers/MeetingHelper.php';
$upcomingMeetings = getAllMeetings([
    'status' => 'Scheduled',
    'limit' => 5
]);

// Set Page Title for header.php
$pageTitle = 'Dashboard';

// Fetch all meetings for the calendar
$calendarMeetings = getAllMeetings();

// Fetch counts for stats cards
$stats = [
    'committees' => $conn->query("SELECT COUNT(*) FROM committees WHERE is_active = 1")->fetch_row()[0],
    'meetings' => $conn->query("SELECT COUNT(*) FROM meetings WHERE status = 'Scheduled'")->fetch_row()[0],
    'documents' => ($conn->query("SELECT COUNT(*) FROM legislative_documents")->fetch_row()[0] +
        $conn->query("SELECT COUNT(*) FROM meeting_documents WHERE file_path IS NOT NULL AND file_path != ''")->fetch_row()[0]),
    'tasks' => $conn->query("SELECT COUNT(*) FROM tasks WHERE status != 'Done'")->fetch_row()[0],
    'referrals' => $conn->query("SELECT COUNT(*) FROM referrals WHERE status != 'Approved' AND status != 'Rejected'")->fetch_row()[0]
];

// Fetch data for the doughnut chart (Document Distribution)
$docDistribution = [
    'Ordinances' => $conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'ordinance'")->fetch_row()[0],
    'Resolutions' => ($conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'resolution'")->fetch_row()[0] +
        $conn->query("SELECT COUNT(*) FROM meeting_documents WHERE document_type = 'resolution'")->fetch_row()[0]),
    'Reports' => ($conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'committee_report'")->fetch_row()[0] +
        $conn->query("SELECT COUNT(*) FROM meeting_documents WHERE document_type = 'recommendation'")->fetch_row()[0]),
    'Agendas' => ($conn->query("SELECT COUNT(*) FROM meetings WHERE agenda_status != 'None'")->fetch_row()[0] +
        $conn->query("SELECT COUNT(*) FROM meeting_documents WHERE document_type = 'agenda' AND file_path IS NOT NULL AND file_path != ''")->fetch_row()[0])
];

// Fetch user tasks
$userTasksQuery = "SELECT t.*, c.committee_name FROM tasks t LEFT JOIN committees c ON t.committee_id = c.committee_id WHERE t.assigned_to = ? AND t.status != 'Done' ORDER BY t.due_date ASC LIMIT 5";
$stmt = $conn->prepare($userTasksQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userTasksResult = $stmt->get_result();
$myTasks = [];
if ($userTasksResult) {
    while ($row = $userTasksResult->fetch_assoc()) {
        $myTasks[] = $row;
    }
}
$stmt->close();

// Fetch Recent Activity from audit logs
$isPrivileged = in_array($userRole, ['Admin', 'Super Admin']);
$recentActivityQuery = "SELECT al.*, u.first_name, u.last_name 
                        FROM audit_logs al 
                        LEFT JOIN users u ON al.user_id = u.user_id";

if (!$isPrivileged) {
    $recentActivityQuery .= " WHERE al.user_id = " . intval($userId);
}

$recentActivityQuery .= " ORDER BY al.timestamp DESC LIMIT 6";
$recentActivityResult = $conn->query($recentActivityQuery);
$recentActivities = [];
if ($recentActivityResult) {
    while ($row = $recentActivityResult->fetch_assoc()) {
        $recentActivities[] = $row;
    }
}

// Include the standardized header
include 'includes/header.php';
?>

<!-- Combined Module Content Wrapper (opens in header.php) -->
<div class="space-y-8">
    <!-- Dashboard Sub-Header -->
    <div class="flex items-center space-x-3 mb-2 animate-fade-in">
        <div
            class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center border border-gray-100 dark:border-gray-700">
            <i class="bi bi-layout-text-window-reverse text-gray-400"></i>
        </div>
        <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Dashboard</h2>
    </div>

    <!-- Welcome Banner (Screenshot 1 Style) -->
    <div
        class="relative overflow-hidden bg-[#dc2626] rounded-3xl p-8 md:p-10 text-white shadow-xl animate-fade-in mb-8">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="animate-slide-in-left">
                <h1 class="text-4xl md:text-5xl font-black mb-2 tracking-tighter uppercase italic">
                    WELCOME BACK, <?php echo strtoupper(explode(' ', $userName)[0]); ?>! 👋
                </h1>
                <p class="text-red-100/90 text-lg font-medium max-w-xl italic">
                    Here's what's happening with your committee management today.
                </p>
            </div>
            <div class="hidden lg:block animate-slide-in-right opacity-20">
                <i class="bi bi-speedometer2 text-[100px]"></i>
            </div>
        </div>
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl">
        </div>
        <div
            class="absolute bottom-0 left-0 w-32 h-32 bg-black/5 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl">
        </div>
    </div>

    <!-- Stats Grid (5 Columns - Screenshot 1 Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Committees -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center group hover:shadow-lg transition-all duration-300">
            <div
                class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mr-5 group-hover:bg-red-600 transition-all duration-500">
                <i
                    class="bi bi-building text-red-600 dark:text-red-400 text-2xl group-hover:text-white group-hover:scale-110 transition-all"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">
                    Committees</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-none tracking-tighter">
                        <?php echo $stats['committees']; ?>
                    </h3>
                    <span
                        class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Active</span>
                </div>
            </div>
        </div>

        <!-- Meetings -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center group hover:shadow-lg transition-all duration-300">
            <div
                class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mr-5 group-hover:bg-red-600 transition-all duration-500">
                <i
                    class="bi bi-calendar-event text-red-600 dark:text-red-400 text-2xl group-hover:text-white group-hover:scale-110 transition-all"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">
                    Meetings</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-none tracking-tighter">
                        <?php echo $stats['meetings']; ?>
                    </h3>
                    <span
                        class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Pending</span>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center group hover:shadow-lg transition-all duration-300">
            <div
                class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mr-5 group-hover:bg-red-600 transition-all duration-500">
                <i
                    class="bi bi-file-earmark-text text-red-600 dark:text-red-400 text-2xl group-hover:text-white group-hover:scale-110 transition-all"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">
                    Documents</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-none tracking-tighter">
                        <?php echo $stats['documents']; ?>
                    </h3>
                    <span
                        class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Total</span>
                </div>
            </div>
        </div>

        <!-- Task Items -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center group hover:shadow-lg transition-all duration-300">
            <div
                class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mr-5 group-hover:bg-red-600 transition-all duration-500">
                <i
                    class="bi bi-check2-square text-red-600 dark:text-red-400 text-2xl group-hover:text-white group-hover:scale-110 transition-all"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">Task
                    Items</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-none tracking-tighter">
                        <?php echo $stats['tasks']; ?>
                    </h3>
                    <span
                        class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Active</span>
                </div>
            </div>
        </div>

        <!-- Referrals -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center group hover:shadow-lg transition-all duration-300">
            <div
                class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mr-5 group-hover:bg-red-600 transition-all duration-500">
                <i
                    class="bi bi-arrow-left-right text-red-600 dark:text-red-400 text-2xl group-hover:text-white group-hover:scale-110 transition-all"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">
                    Referrals</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-none tracking-tighter">
                        <?php echo $stats['referrals']; ?>
                    </h3>
                    <span
                        class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">New</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
        <!-- Document Distribution Chart -->
        <div class="lg:col-span-3">
            <div
                class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8 h-[450px]">
                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter uppercase mb-6 italic">
                    Document Distribution</h3>
                <div class="relative h-[320px]">
                    <canvas id="documentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="lg:col-span-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8 h-full flex flex-col justify-between">
                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter uppercase mb-6 italic">
                    Quick Actions</h3>
                <div class="space-y-4">
                    <a href="pages/committee-profiles/index.php"
                        class="flex items-center justify-center space-x-3 p-4 bg-[#dc2626] text-white rounded-2xl font-black uppercase tracking-tighter hover:bg-red-700 transition shadow-lg shadow-red-600/20 group animate-fade-in">
                        <i class="bi bi-building"></i>
                        <span>Committee Profiles</span>
                    </a>
                    <a href="pages/notifications/index.php"
                        class="flex items-center justify-center space-x-3 p-4 border-2 border-[#dc2626] text-[#dc2626] rounded-2xl font-black uppercase tracking-tighter hover:bg-red-50 transition group animate-fade-in animation-delay-100">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="pages/reports-analytics/index.php"
                        class="flex items-center justify-center space-x-3 p-4 border-2 border-[#dc2626] text-[#dc2626] rounded-2xl font-black uppercase tracking-tighter hover:bg-red-50 transition group animate-fade-in animation-delay-200">
                        <i class="bi bi-bar-chart"></i>
                        <span>View Reports</span>
                    </a>
                    <a href="portal/index.php" target="_blank"
                        class="flex items-center justify-center space-x-3 p-4 border-2 border-[#dc2626] text-[#dc2626] rounded-2xl font-black uppercase tracking-tighter hover:bg-red-50 transition group animate-fade-in animation-delay-300">
                        <i class="bi bi-globe"></i>
                        <span>Public Portal</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legislative Calendar card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8 mb-8 overflow-hidden">
        <div class="flex items-center justify-between mb-8">
            <h3
                class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter uppercase italic flex items-center">
                <i class="bi bi-calendar3 mr-4 text-[#dc2626]"></i>Legislative Calendar
            </h3>
            <div class="flex items-center space-x-4">
                <span class="flex items-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span class="w-3 h-3 bg-[#dc2626] rounded-full mr-2"></span> Meetings
                </span>
            </div>
        </div>
        <div id="calendar" class="min-h-[600px]"></div>
    </div>

    <!-- Feeds Grid (3 Columns - Activity, Tasks, Meetings) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-0">
        <!-- Recent Activity Feed -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter uppercase italic">Recent
                    Activity</h3>
                <a href="pages/audit-logs/index.php"
                    class="text-[10px] font-black text-red-600 uppercase tracking-widest hover:underline">View All</a>
            </div>
            <div class="space-y-6">
                <?php if (empty($recentActivities)): ?>
                    <p class="text-sm text-gray-400 italic font-medium text-center py-8">No activity recorded.</p>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="flex items-start space-x-4 group">
                            <div
                                class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900/50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-50 dark:group-hover:bg-red-900/20 transition-all">
                                <i class="bi <?php
                                switch ($activity['action']) {
                                    case 'CREATE':
                                        echo 'bi-plus-circle text-green-600';
                                        break;
                                    case 'UPDATE':
                                        echo 'bi-pencil text-blue-600';
                                        break;
                                    case 'DELETE':
                                        echo 'bi-trash text-red-600';
                                        break;
                                    case 'LOGIN':
                                        echo 'bi-box-arrow-in-right text-indigo-600';
                                        break;
                                    default:
                                        echo 'bi-info-circle text-gray-600';
                                }
                                ?> text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <p class="text-[11px] font-black text-gray-800 dark:text-white truncate uppercase">
                                        <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                    </p>
                                    <span
                                        class="text-[9px] font-black text-gray-400 uppercase tracking-tighter"><?php echo date('g:i A', strtotime($activity['timestamp'])); ?></span>
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight line-clamp-2 italic">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Pending Tasks Feed -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter uppercase italic">My
                    Pending Tasks</h3>
                <a href="pages/action-items/index.php"
                    class="text-[10px] font-black text-red-600 uppercase tracking-widest hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($myTasks)): ?>
                    <p class="text-sm text-gray-400 italic font-medium text-center py-8">No pending tasks.</p>
                <?php else: ?>
                    <?php foreach ($myTasks as $task): ?>
                        <div
                            class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/30 border-l-4 border-[#dc2626] transition hover:shadow-md cursor-pointer group">
                            <p
                                class="font-black text-xs text-gray-800 dark:text-white truncate mb-1 group-hover:text-red-600 transition">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </p>
                            <div class="flex items-center text-[9px] font-bold text-gray-400 uppercase tracking-tighter">
                                <i class="bi bi-clock mr-1"></i> DUE: <?php echo date('M j', strtotime($task['due_date'])); ?>
                                <span class="mx-2 font-black text-gray-200">•</span>
                                <i class="bi bi-building mr-1"></i>
                                <?php echo htmlspecialchars($task['committee_name'] ?? 'General'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Meetings Feed -->
        <div
            class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter uppercase italic">Upcoming
                    Meetings</h3>
                <a href="pages/committee-meetings/index.php"
                    class="text-[10px] font-black text-red-600 uppercase tracking-widest hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($upcomingMeetings)): ?>
                    <p class="text-sm text-gray-400 italic font-medium text-center py-8">No upcoming meetings.</p>
                <?php else: ?>
                    <?php foreach ($upcomingMeetings as $m): ?>
                        <div
                            class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/30 border-l-4 border-[#dc2626] transition hover:shadow-md cursor-pointer group">
                            <p
                                class="font-black text-xs text-gray-800 dark:text-white truncate mb-1 group-hover:text-red-600 transition">
                                <?php echo htmlspecialchars($m['committee_name']); ?>
                            </p>
                            <div class="flex items-center text-[9px] font-bold text-gray-400 uppercase tracking-tighter">
                                <i class="bi bi-calendar-event mr-1 text-red-600"></i>
                                <?php echo date('M j, Y', strtotime($m['date'])); ?>
                                <span class="mx-2 font-black text-gray-200">•</span>
                                <i class="bi bi-clock mr-1"></i>
                                <?php echo date('g:i A', strtotime($m['date'] . ' ' . $m['time_start'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts inside the content wrapper to match reference pages -->
    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function () {
            const documentCtx = document.getElementById('documentChart');
            if (documentCtx) {
                new Chart(documentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Ordinances', 'Resolutions', 'Reports', 'Agendas'],
                        datasets: [{
                            data: [
                                <?php echo intval($docDistribution['Ordinances']); ?>,
                                <?php echo intval($docDistribution['Resolutions']); ?>,
                                <?php echo intval($docDistribution['Reports']); ?>,
                                <?php echo intval($docDistribution['Agendas']); ?>
                            ],
                            backgroundColor: ['#dc2626', '#3b82f6', '#10b981', '#f59e0b'],
                            borderColor: '#fff',
                            borderWidth: 4,
                            hoverOffset: 20
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: { size: 11, weight: '900', family: 'Inter' },
                                    color: document.documentElement.classList.contains('dark') ? '#e5e5e5' : '#374151'
                                }
                            }
                        }
                    }
                });
            }

            // FullCalendar Initialization
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    eventTimeFormat: {
                        hour: 'numeric',
                        minute: '2-digit',
                        meridiem: 'short',
                        hour12: true
                    },
                    themeSystem: 'standard',
                    events: [
                        <?php foreach ($calendarMeetings as $m): ?>
                                                {
                                id: '<?php echo $m['id']; ?>',
                                title: '<?php echo addslashes($m['committee_name']); ?>',
                                start: '<?php echo $m['date']; ?>T<?php echo $m['time_start']; ?>',
                                end: '<?php echo $m['date']; ?>T<?php echo $m['time_end']; ?>',
                                backgroundColor: '#dc2626',
                                borderColor: '#b91c1c',
                                textColor: '#ffffff',
                            },
                        <?php endforeach; ?>
                    ],
                    eventClick: function (info) {
                        window.location.href = 'pages/committee-meetings/view.php?id=' + info.event.id;
                    },
                    height: 'auto',
                    handleWindowResize: true
                });
                calendar.render();
            }
        });

    </script>


</div> <!-- Closing space-y-8 -->
</div> <!-- Closing module-content-wrapper (opened in header.php) -->

<?php
include 'includes/footer.php';
include 'includes/layout-end.php';
?>