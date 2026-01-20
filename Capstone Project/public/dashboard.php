<?php
require_once __DIR__ . '/../config/session_config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch complete user data from database (same as other modules)
$query = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    error_log("User not found in database: user_id = " . $userId);
    die("Error: User profile not found. Please contact administrator.");
}

// Set user variables
$userName = $user['first_name'] . ' ' . $user['last_name'];
$userEmail = $user['email'];
$userRole = $user['role_name'] ?? $user['user_role'] ?? 'User';
$profilePicture = $user['profile_picture'] ?? null;
$userInitials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Update session with latest data
$_SESSION['user_name'] = $userName;
$_SESSION['user_email'] = $userEmail;
$_SESSION['user_role'] = $userRole;

// Fetch profile picture from database
$userId = $_SESSION['user_id'];
$query = "SELECT profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get profile picture path
$profilePicture = $user['profile_picture'] ?? null;

// Verify profile picture file exists
$profilePictureExists = false;
$displayPath = '';

if ($profilePicture) {
    // Database stores: uploads/profiles/file.jpg
    // Dashboard is in public/, so path is direct
    $fullPath = __DIR__ . '/' . $profilePicture;
    $profilePictureExists = file_exists($fullPath);
    $displayPath = $profilePicture;
}

// Fallback to default avatar if no picture or file doesn't exist
if (!$profilePicture || !$profilePictureExists) {
    $displayPath = 'assets/images/default-avatar.png';
}

$_SESSION['profile_picture'] = $profilePicture;

// Advanced Features: Fetch dynamic data
require_once __DIR__ . '/../app/helpers/MeetingHelper.php';

// Fetch upcoming meetings (next 30 days)
$upcomingMeetings = getAllMeetings([
    'status' => 'Scheduled',
    'limit' => 5
]);

// Fetch all meetings for the calendar
$calendarMeetings = getAllMeetings();

// Fetch counts for stats cards
$stats = [
    'committees' => $conn->query("SELECT COUNT(*) FROM committees WHERE is_active = 1")->fetch_row()[0],
    'meetings' => $conn->query("SELECT COUNT(*) FROM meetings WHERE status = 'Scheduled'")->fetch_row()[0],
    'documents' => $conn->query("SELECT COUNT(*) FROM legislative_documents")->fetch_row()[0],
    'tasks' => $conn->query("SELECT COUNT(*) FROM tasks WHERE status != 'Done'")->fetch_row()[0],
    'referrals' => $conn->query("SELECT COUNT(*) FROM referrals WHERE status != 'Approved' AND status != 'Rejected'")->fetch_row()[0]
];

// Fetch data for the doughnut chart (Document Distribution)
$docDistribution = [
    'Ordinances' => $conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'ordinance'")->fetch_row()[0],
    'Resolutions' => $conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'resolution'")->fetch_row()[0],
    'Reports' => $conn->query("SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'committee_report'")->fetch_row()[0],
    'Agendas' => $conn->query("SELECT COUNT(*) FROM meetings WHERE agenda_status != 'None'")->fetch_row()[0]
];

// Fetch recent referrals
$recentReferralsQuery = "SELECT r.*, ld.title, ld.document_type as type FROM referrals r JOIN legislative_documents ld ON r.document_id = ld.document_id ORDER BY r.created_at DESC LIMIT 5";
$recentReferralsResult = $conn->query($recentReferralsQuery);
$recentReferrals = [];
if ($recentReferralsResult) {
    while ($row = $recentReferralsResult->fetch_assoc()) {
        $recentReferrals[] = $row;
    }
}

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
// If not Administrator, show only own activity
$isPrivileged = in_array($userRole, ['Administrator', 'Super Administrator']);
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#dc2626">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title>CMS - Committee Management System | City of Valenzuela</title>
    <meta name="description" content="Committee Management System - City Government of Valenzuela, Metropolitan Manila">
    <meta name="keywords" content="CMS, Valenzuela, Committee Management, Legislative Services">

    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/system-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <!-- Tailwind Configuration - CRITICAL for dark mode toggle -->
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <!-- Prevent dark mode flicker and handle initialization -->
    <script>
        // Initialize dark mode from localStorage, default to light mode
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            // Explicitly remove dark class if not set or set to light
            document.documentElement.classList.remove('dark');
            if (!theme) {
                localStorage.setItem('theme', 'light');
            }
        }
    </script>

    <link rel="stylesheet" href="assets/css/styles-updated.css">

    <!-- Ensure sidebar is visible on desktop (when not collapsed) -->
    <style>
        /* Hide scrollbar while maintaining scroll functionality */
        .sidebar nav::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }

        .sidebar nav {
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE/Edge */
        }

        @media (min-width: 768px) {
            #sidebar:not(.collapsed) {
                display: flex !important;
                transform: translateX(0) !important;
                position: relative !important;
            }

            /* Desktop: show sidebar toggle, hide mobile elements */
            .desktop-toggle {
                display: flex !important;
            }

            .mobile-toggle,
            .mobile-only {
                display: none !important;
            }
        }

        /* Mobile: hide desktop sidebar and toggle */
        @media (max-width: 767px) {
            #sidebar {
                display: none !important;
            }

            .desktop-toggle {
                display: none !important;
            }

            .mobile-toggle {
                display: flex !important;
            }

            .mobile-only {
                display: flex !important;
            }
        }
    </style>

</head>

<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden opacity-0 pointer-events-none transition-all duration-300 ease-out">
    </div>

    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar"
        class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-72 bg-gradient-to-b from-red-800 to-red-900 text-white z-50 transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-4 border-b border-red-700/50 sidebar-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 sidebar-logo">
                    <div class="bg-white rounded-full p-1.5 shadow-lg">
                        <img src="assets/images/logo.png" alt="Logo" class="w-9 h-9 object-contain">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </div>
                <button id="close-mobile-sidebar"
                    class="text-white/80 p-2 hover:bg-red-700/50 hover:text-white rounded-lg transition-all duration-200 hover:rotate-90">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>

        <nav class="flex-1 py-4 px-3 overflow-y-auto">
            <a href="dashboard.php"
                class="flex items-center px-3 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 bg-red-700">
                <i class="bi bi-speedometer2 mr-2.5 text-xl"></i>
                <span class="text-lg">Dashboard</span>
            </a>

            <div class="mt-3 mb-2 px-3">
                <p class="text-sm font-semibold text-red-300/80 uppercase tracking-wider">Core Modules</p>
            </div>

            <a href="pages/committee-profiles/index.php"
                class="flex items-center px-3 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-building mr-2.5 text-xl"></i>
                <span class="text-base">Committee Profiles</span>
            </a>
            <a href="pages/committee-meetings/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-calendar-check mr-2.5 text-xl"></i>
                <span class="text-base">Meetings</span>
            </a>
            <a href="pages/agenda-builder/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-list-ul mr-2.5 text-xl"></i>
                <span class="text-base">Agendas</span>
            </a>
            <a href="pages/referral-management/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-inbox mr-2.5 text-xl"></i>
                <span class="text-base">Referrals</span>
            </a>
            <a href="pages/action-items/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-check2-square mr-2.5 text-xl"></i>
                <span class="text-base">Action Items</span>
            </a>

            <div class="mt-3 mb-2 px-3">
                <p class="text-sm font-semibold text-red-300/80 uppercase tracking-wider">Analytics</p>
            </div>

            <a href="pages/reports-analytics/index.php"
                class="flex items-center px-3 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-graph-up mr-2.5 text-xl"></i>
                <span class="text-base">Reports & Analytics</span>
            </a>

            <div class="mt-3 mb-2 px-3">
                <p class="text-[10px] font-semibold text-red-300/80 uppercase tracking-wider">Administration</p>
            </div>

            <!-- User Management -->
            <a href="pages/user-management/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-people-fill mr-2.5 text-xl"></i>
                <span class="text-base">User Management</span>
            </a>

            <!-- Audit Logs -->
            <a href="pages/audit-logs/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-shield-check mr-2.5 text-xl"></i>
                <span class="text-base">Audit Logs</span>
            </a>

            <!-- Notifications -->
            <a href="pages/notifications/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-bell mr-2.5 text-xl"></i>
                <span class="text-base">Notifications</span>
            </a>

            <!-- My Profile -->
            <a href="pages/my-profile/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-person-circle mr-2.5 text-xl"></i>
                <span class="text-base">My Profile</span>
            </a>
            <a href="pages/system-settings/index.php"
                class="flex items-center px-3 py-2 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-gear mr-2.5 text-xl"></i>
                <span class="text-base">Settings</span>
            </a>
        </nav>

        <div class="p-3 mt-auto border-t border-red-700/40">
            <div class="flex items-center space-x-2.5 mb-2.5">
                <div class="w-9 h-9 rounded-full overflow-hidden bg-red-700 flex items-center justify-center">
                    <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile"
                        class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-xs text-red-300 truncate">Administrator</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">
        <!-- Desktop Sidebar -->
        <aside id="sidebar"
            class="sidebar sidebar-expanded w-64 bg-gradient-to-b from-red-800 to-red-900 text-white flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out animate-slide-in-left h-screen fixed md:relative z-30 -translate-x-full md:translate-x-0">
            <div class="p-6 border-b border-red-700 sidebar-logo">
                <a href="dashboard.php"
                    class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 transform hover:scale-105 group">
                    <div class="bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                        style="width: 70px; height: 70px;">
                        <img src="assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%;"
                            class="object-contain">
                    </div>
                    <div class="transform transition-all duration-300 group-hover:translate-x-1 sidebar-text">
                        <h1 class="text-lg font-bold">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto py-3">
                <div class="px-3 space-y-1">
                    <a href="dashboard.php"
                        class="flex items-center px-3 py-3 rounded-lg bg-red-700 text-white transition-all duration-200 group">
                        <i class="bi bi-speedometer2 text-lg"></i>
                        <span class="sidebar-text ml-2 text-lg">Dashboard</span>
                    </a>

                    <div class="pt-3 pb-1 sidebar-text">
                        <p class="px-3 text-sm font-semibold text-red-300 uppercase tracking-wider">Core Modules</p>
                    </div>

                    <a href="pages/committee-profiles/index.php"
                        class="flex items-center px-3 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-people text-lg"></i>
                        <span
                            class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Committee
                            Profiles</span>
                    </a>
                    <a href="pages/committee-meetings/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-calendar-event text-lg"></i>
                        <span
                            class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Meetings</span>
                    </a>
                    <a href="pages/agenda-builder/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-list-check text-lg"></i>
                        <span
                            class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Agendas</span>
                    </a>
                    <a href="pages/referral-management/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-arrow-left-right text-lg"></i>
                        <span
                            class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Referrals</span>
                    </a>
                    <a href="pages/action-items/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-check2-square text-lg"></i>
                        <span class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Action
                            Items</span>
                    </a>

                    <div class="pt-3 pb-1 sidebar-text">
                        <p class="px-3 text-sm font-semibold text-red-300 uppercase tracking-wider">Analytics</p>
                    </div>

                    <a href="pages/reports-analytics/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-graph-up text-lg"></i>
                        <span class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Reports
                            &
                            Analytics</span>
                    </a>

                    <div class="pt-3 pb-1 sidebar-text">
                        <p class="px-3 text-[10px] font-semibold text-red-300 uppercase tracking-wider">Administration
                        </p>
                    </div>

                    <a href="pages/user-management/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-people text-lg"></i>
                        <span class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">User
                            Management</span>
                    </a>
                    <a href="pages/audit-logs/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-shield-check text-lg"></i>
                        <span class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Audit
                            Logs</span>
                    </a>
                    <a href="pages/notifications/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-bell text-lg"></i>
                        <span
                            class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">Notifications</span>
                    </a>
                    <a href="pages/my-profile/index.php"
                        class="flex items-center px-3 py-2 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-person-circle text-lg"></i>
                        <span class="sidebar-text ml-2 text-base group-hover:translate-x-1 transition-transform">My
                            Profile</span>
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-red-700 sidebar-user">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center flex-shrink-0 border-2 border-red-400">
                        <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0 sidebar-text">
                        <p class="text-sm font-semibold truncate"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-red-200 truncate">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header / Navbar -->
            <nav
                class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Left Side: Toggle buttons and Logo -->
                        <div class="flex items-center">
                            <!-- Desktop Sidebar Toggle - Always visible on md+ screens -->
                            <button id="sidebar-toggle"
                                class="desktop-toggle flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-red-600 dark:hover:text-red-400 focus:outline-none transition-all duration-200 border border-gray-200 dark:border-gray-600"
                                title="Toggle Sidebar">
                                <i class="bi bi-layout-sidebar-inset text-xl sidebar-icon"></i>
                                <i class="bi bi-arrow-right text-xl arrow-icon hidden"></i>
                            </button>

                            <!-- Mobile Menu Button -->
                            <button id="mobile-menu-btn"
                                class="mobile-toggle text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                                <i class="bi bi-list text-2xl"></i>
                            </button>

                            <!-- Logo (Mobile) -->
                            <div class="mobile-only flex items-center ml-2">
                                <img src="assets/images/logo.png" alt="CMS" class="w-10 h-10 object-contain">
                            </div>
                        </div>

                        <!-- Page Title & Breadcrumb -->
                        <div class="flex-1 flex items-center justify-center md:justify-start min-w-0">
                            <div class="ml-2 md:ml-4 min-w-0">
                                <h2 id="page-title"
                                    class="text-base md:text-xl font-bold text-gray-800 dark:text-white">Dashboard</h2>
                            </div>
                        </div>

                        <!-- Right Side Actions -->
                        <div class="flex items-center space-x-1 md:space-x-4">
                            <!-- Search Bar (Hidden on mobile) -->
                            <div class="hidden lg:block">
                                <div class="relative group">
                                    <input type="text" id="quick-search" placeholder="Quick search..."
                                        class="w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 text-base">
                                    <i
                                        class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 transition-all group-focus-within:text-red-600 group-focus-within:scale-110"></i>
                                </div>
                            </div>

                            <!-- Real-time Clock & Date -->
                            <div
                                class="hidden md:flex flex-col items-end mr-2 pr-4 border-r border-gray-200 dark:border-gray-700">
                                <div
                                    class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter real-time-date text-right">
                                    Loading date...</div>
                                <div class="text-sm font-black text-gray-800 dark:text-white real-time-clock">00:00:00
                                    AM</div>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <button id="theme-toggle"
                                class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <i class="bi bi-moon-fill text-lg md:text-xl dark-mode-icon"></i>
                                <i class="bi bi-sun-fill text-xl light-mode-icon hidden"></i>
                            </button>

                            <!-- Notifications -->
                            <div class="relative">
                                <button id="notifications-btn"
                                    class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <i class="bi bi-bell text-xl"></i>
                                    <?php
                                    // Dummy unread count
                                    $dummyUnreadCount = 3;
                                    if ($dummyUnreadCount > 0):
                                        ?>
                                        <span
                                            class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full"><?php echo $dummyUnreadCount > 9 ? '9+' : $dummyUnreadCount; ?></span>
                                    <?php endif; ?>
                                </button>

                                <div id="notifications-dropdown"
                                    class="hidden absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                                    <div
                                        class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Notifications
                                            (<?php echo $dummyUnreadCount; ?>)</h3>
                                        <button onclick="markAllAsRead()"
                                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">Mark
                                            all read</button>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <!-- Notification Item 1 -->
                                        <a href="pages/notifications/index.php"
                                            class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-2 flex-shrink-0">
                                                    <i
                                                        class="bi bi-calendar-event text-blue-600 dark:text-blue-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between">
                                                        <p class="text-sm font-medium text-gray-800 dark:text-white">New
                                                            Committee Meeting Scheduled</p>
                                                        <span
                                                            class="ml-2 w-2 h-2 bg-red-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Finance
                                                        Committee meeting on Jan 15, 2026 at 2:00 PM</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">5 minutes
                                                        ago</p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Notification Item 2 -->
                                        <a href="pages/notifications/index.php"
                                            class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="bg-green-100 dark:bg-green-900/30 rounded-full p-2 flex-shrink-0">
                                                    <i
                                                        class="bi bi-file-earmark-check text-green-600 dark:text-green-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between">
                                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                            Agenda Approved</p>
                                                        <span
                                                            class="ml-2 w-2 h-2 bg-red-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Your
                                                        submitted agenda for Education Committee has been approved</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">1 hour ago
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Notification Item 3 -->
                                        <a href="pages/notifications/index.php"
                                            class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="bg-yellow-100 dark:bg-yellow-900/30 rounded-full p-2 flex-shrink-0">
                                                    <i
                                                        class="bi bi-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between">
                                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                            Action Item Deadline Approaching</p>
                                                        <span
                                                            class="ml-2 w-2 h-2 bg-red-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Budget
                                                        review action item due in 2 days</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">3 hours ago
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Notification Item 4 (Read) -->
                                        <a href="pages/notifications/index.php"
                                            class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition opacity-75">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="bg-purple-100 dark:bg-purple-900/30 rounded-full p-2 flex-shrink-0">
                                                    <i class="bi bi-people text-purple-600 dark:text-purple-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800 dark:text-white">New
                                                        Member Added</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">John Doe
                                                        has been added to Health Committee</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Yesterday
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Notification Item 5 (Read) -->
                                        <a href="pages/notifications/index.php"
                                            class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition opacity-75">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="bg-red-100 dark:bg-red-900/30 rounded-full p-2 flex-shrink-0">
                                                    <i class="bi bi-inbox text-red-600 dark:text-red-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800 dark:text-white">New
                                                        Referral Received</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Infrastructure improvement proposal has been referred to your
                                                        committee</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 days ago
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div
                                        class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                        <a href="pages/notifications/index.php"
                                            class="block text-center text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">View
                                            all
                                            notifications</a>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function markAllAsRead() {
                                    // This will be implemented when database is ready
                                    alert('Mark all as read functionality will be implemented with the notification module');
                                    // For now, just close the dropdown
                                    document.getElementById('notifications-dropdown').classList.add('hidden');
                                }
                            </script>

                            <!-- User Profile Dropdown -->
                            <div class="relative">
                                <button id="profile-btn"
                                    class="flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center border-2 border-red-500 shadow-sm">
                                        <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <p
                                            class="text-sm font-medium text-gray-800 truncate max-w-[120px] md:max-w-none">
                                            <?php echo htmlspecialchars($userName); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($userRole); ?>
                                        </p>
                                    </div>
                                    <i class="bi bi-chevron-down text-gray-600 text-xs hidden sm:inline"></i>
                                </button>

                                <div id="profile-dropdown"
                                    class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                    <div
                                        class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center space-x-3">
                                        <div
                                            class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 border-2 border-white shadow-sm">
                                            <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile"
                                                class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                                                <?php echo htmlspecialchars($userName); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                <?php echo htmlspecialchars($userEmail); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="py-2">
                                        <a href="pages/my-profile/index.php"
                                            class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="bi bi-person mr-3 text-base"></i>My Profile
                                        </a>
                                        <a href="pages/notifications/index.php"
                                            class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="bi bi-bell mr-3 text-base"></i>Notifications
                                        </a>
                                    </div>
                                    <div class="border-t border-gray-200 py-2">
                                        <a href="javascript:void(0);" id="logout-btn" onclick="logout(); return false;"
                                            class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 cursor-pointer">
                                            <i class="bi bi-box-arrow-right mr-2"></i>Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-3 sm:p-4 lg:p-6">
                <!-- Welcome Banner -->
                <div
                    class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-6 md:p-8 text-white transform hover:scale-[1.01] transition-all duration-300 mb-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div class="animate-slide-in-left">
                            <h1 class="text-3xl md:text-4xl font-bold mb-2">Welcome back,
                                <?php echo htmlspecialchars($userName); ?>! 👋
                            </h1>
                            <p class="text-lg md:text-xl text-red-100">Here's what's happening with your
                                committee
                                management
                                today.</p>
                        </div>
                        <div class="hidden lg:block animate-slide-in-right">
                            <i class="bi bi-speedometer2 text-8xl opacity-20"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-100 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3">
                                    <i class="bi bi-building text-red-600 dark:text-red-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-red-600 dark:group-hover:text-red-400">
                                    Active Committees</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    <?php echo $stats['committees']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-200 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                                    <i class="bi bi-calendar-event text-blue-600 dark:text-blue-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    Upcoming Meetings</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    <?php echo $stats['meetings']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-300 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                                    <i class="bi bi-inbox text-green-600 dark:text-green-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-green-600 dark:group-hover:text-green-400">
                                    Documents</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    <?php echo $stats['documents']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-400 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                                    <i class="bi bi-check2-square text-purple-600 dark:text-purple-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-purple-600 dark:group-hover:text-purple-400">
                                    Pending Tasks</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    <?php echo $stats['tasks']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-500 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-3">
                                    <i class="bi bi-arrow-left-right text-yellow-600 dark:text-yellow-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-yellow-600 dark:group-hover:text-yellow-400">
                                    Active Referrals</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    <?php echo $stats['referrals']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Charts Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Document
                                Distribution
                            </h3>
                            <div class="flex items-center justify-center" style="height: 300px;">
                                <canvas id="documentChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Column -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-600">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="pages/committee-profiles/index.php"
                                class="btn-primary w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-building mr-2"></i>Committee Profiles
                            </a>
                            <a href="pages/notifications/index.php"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-bell mr-2"></i>Notifications
                            </a>
                            <a href="pages/reports-analytics/index.php"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-bar-chart mr-2"></i>View Reports
                            </a>
                            <a href="portal/index.php" target="_blank"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-globe mr-2"></i>Public Portal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Meeting Calendar Card -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-700">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            <i class="bi bi-calendar3 mr-2 text-red-600"></i>Legislative Calendar
                        </h3>
                        <div class="flex space-x-2">
                            <span class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                <span class="w-3 h-3 bg-red-600 rounded-full mr-1"></span> Meetings
                            </span>
                        </div>
                    </div>
                    <div id="calendar" class="min-h-[600px]"></div>
                </div>

                <!-- Recent Activity & Notifications -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Recent Activity -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Activity</h3>
                            <a href="pages/audit-logs/index.php" class="text-xs text-red-600 hover:underline">View
                                All</a>
                        </div>
                        <div class="space-y-4">
                            <?php if (empty($recentActivities)): ?>
                                <p class="text-sm text-gray-500 italic text-center py-4">No recent activity.</p>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div
                                        class="flex items-start space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded transition">
                                        <div class="flex-shrink-0 mt-1">
                                            <div
                                                class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                <i class="bi <?php
                                                switch ($activity['action']) {
                                                    case 'CREATE':
                                                        echo 'bi-plus-circle text-green-600';
                                                        break;
                                                    case 'UPDATE':
                                                    case 'UPDATE_PROFILE':
                                                        echo 'bi-pencil text-blue-600';
                                                        break;
                                                    case 'DELETE':
                                                    case 'REMOVE_PICTURE':
                                                        echo 'bi-trash text-red-600';
                                                        break;
                                                    case 'CHANGE_PASSWORD':
                                                        echo 'bi-shield-lock text-yellow-600';
                                                        break;
                                                    case 'UPDATE_PICTURE':
                                                        echo 'bi-image text-purple-600';
                                                        break;
                                                    case 'LOGIN':
                                                    case 'OAUTH_LOGIN':
                                                        echo 'bi-box-arrow-in-right text-indigo-600';
                                                        break;
                                                    case 'LOGOUT':
                                                        echo 'bi-box-arrow-right text-orange-600';
                                                        break;
                                                    default:
                                                        echo 'bi-info-circle text-gray-600';
                                                }
                                                ?> text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                                <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                            </p>
                                            <p class="text-[10px] text-gray-600 dark:text-gray-400 line-clamp-2">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </p>
                                            <p class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">
                                                <?php echo date('M j, g:i A', strtotime($activity['timestamp'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- My Tasks -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-800">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">My Pending Tasks</h3>
                            <a href="pages/action-items/index.php" class="text-xs text-red-600 hover:underline">View
                                All</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($myTasks)): ?>
                                <p class="text-sm text-gray-500 italic text-center py-4">No pending tasks assigned to you.
                                </p>
                            <?php else: ?>
                                <?php foreach ($myTasks as $task): ?>
                                    <div
                                        class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded border-l-4 border-purple-500 hover:shadow-md transition">
                                        <div class="flex justify-between items-start">
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white truncate flex-1">
                                                <?php echo htmlspecialchars($task['title']); ?>
                                            </p>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded <?php
                                            $p = strtolower($task['priority'] ?? 'normal');
                                            echo ($p === 'high' || $p === 'urgent') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                                            ?> ml-2">
                                                <?php echo ucfirst($task['priority'] ?? 'Normal'); ?>
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
                                            Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?> •
                                            <?php echo htmlspecialchars($task['committee_name'] ?? 'General'); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Meetings (Compact) -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-900">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upcoming Meetings</h3>
                            <a href="pages/committee-meetings/index.php"
                                class="text-xs text-red-600 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($upcomingMeetings)): ?>
                                <p class="text-sm text-gray-500 italic text-center py-4">No upcoming meetings.</p>
                            <?php else: ?>
                                <?php foreach ($upcomingMeetings as $m): ?>
                                    <div
                                        class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded border-l-4 border-red-600 hover:shadow-md transition">
                                        <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">
                                            <?php echo htmlspecialchars($m['committee_name']); ?>
                                        </p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            <?php echo date('M j, Y', strtotime($m['date'])); ?> at
                                            <?php echo date('g:i A', strtotime($m['date'] . ' ' . $m['time_start'])); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </div>
        </main>
    </div>
    </div>

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
                                <?php echo $docDistribution['Ordinances']; ?>,
                                <?php echo $docDistribution['Resolutions']; ?>,
                                <?php echo $docDistribution['Reports']; ?>,
                                <?php echo $docDistribution['Agendas']; ?>
                            ],
                            backgroundColor: ['#dc2626', '#3b82f6', '#10b981', '#f59e0b'],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 12 },
                                    color: document.documentElement.classList.contains('dark') ? '#e5e5e5' : '#374151'
                                }
                            }
                        }
                    }
                });
            }

            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        datasets: [{
                            label: 'Attendance %',
                            data: [92, 85, 88, 95],
                            backgroundColor: '#dc2626',
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function (value) { return value + '%'; },
                                    color: document.documentElement.classList.contains('dark') ? '#a3a3a3' : '#6b7280'
                                },
                                grid: { color: document.documentElement.classList.contains('dark') ? '#404040' : '#e5e7eb' }
                            },
                            x: {
                                ticks: { color: document.documentElement.classList.contains('dark') ? '#a3a3a3' : '#6b7280' },
                                grid: { display: false }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    font: { size: 12 },
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
                    slotLabelFormat: {
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
                                title: '<?php echo addslashes($m['committee_name']); ?>: <?php echo addslashes($m['title']); ?>',
                                start: '<?php echo $m['date']; ?>T<?php echo $m['time_start']; ?>',
                                end: '<?php echo $m['date']; ?>T<?php echo $m['time_end']; ?>',
                                backgroundColor: '#dc2626',
                                borderColor: '#b91c1c',
                                textColor: '#ffffff',
                                extendedProps: {
                                    committee: '<?php echo addslashes($m['committee_name']); ?>',
                                    venue: '<?php echo addslashes($m['venue']); ?>'
                                }
                            },
                        <?php endforeach; ?>
                    ],
                    eventClick: function (info) {
                        window.location.href = 'pages/committee-meetings/view.php?id=' + info.event.id;
                    },
                    eventDidMount: function (info) {
                        // Simple tooltip logic or custom styling can go here
                    },
                    height: 'auto',
                    handleWindowResize: true
                });
                calendar.render();
            }
        });

    </script>

    <!-- Template Scripts -->
    <script src="assets/js/script-updated.js"></script>

    <!-- Unified Session Management -->
    <script src="assets/js/session-manager.js"></script>
</body>

</html>