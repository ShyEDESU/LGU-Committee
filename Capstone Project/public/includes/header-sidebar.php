<?php
/**
 * Shared Header and Sidebar Component
 * Include this file at the top of all module pages after session_start()
 * 
 * Usage:
 * <?php
 * session_start();
 * include '../../../public/includes/header-sidebar.php';
 * ?>
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get user info from session
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$userId = $_SESSION['user_id'] ?? null;

// Fetch user email from database
$userEmail = 'user@example.com';
if ($userId) {
    require_once '../../../config/database.php';
    $query = "SELECT email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userEmail = $row['email'];
    }
    $stmt->close();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#dc2626">
    <title>LRMS - Legislative Records Management System | City of Valenzuela</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../../public/assets/images/logo.png">
    <link rel="apple-touch-icon" href="../../../public/assets/images/logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Modern Animations CSS -->
    <link href="/assets/css/animations.css" rel="stylesheet">

    <script>
        // Dark mode check
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'cms-red': '#dc2626',
                        'cms-dark': '#b91c1c',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-in': 'slideIn 0.3s ease-in',
                    }
                }
            }
        }
    </script>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(0);
            }
        }

        /* Sidebar transitions */
        .sidebar {
            transition: width 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-text {
            display: none;
        }

        .sidebar-toggle-btn {
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.collapsed .sidebar-toggle-btn {
            transform: rotate(180deg);
        }
        
        /* Desktop/Mobile toggle visibility */
        @media (min-width: 768px) {
            .desktop-toggle {
                display: flex !important;
            }
            .mobile-toggle,
            .mobile-only {
                display: none !important;
            }
        }
        
        @media (max-width: 767px) {
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

<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased transition-colors duration-300">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
        onclick="toggleSidebar()"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="sidebar w-64 bg-gradient-to-b from-cms-red to-cms-dark text-white fixed md:relative h-full z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 overflow-y-auto animate-fade-in">
            <!-- Header with Collapse Button -->
            <div
                class="p-4 border-b border-red-700 sticky top-0 bg-gradient-to-b from-cms-red to-cms-dark flex items-center justify-between">
                <a href="../../../public/dashboard.php"
                    class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 flex-1">
                    <div
                        class="bg-white rounded-lg shadow-md p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <img src="../../../public/assets/images/logo.png" alt="Logo"
                            class="w-full h-full object-contain rounded-md">
                    </div>
                    <div class="sidebar-text">
                        <h1 class="text-base font-bold">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </a>

                <!-- Sidebar Collapse Button -->
                <button onclick="toggleSidebarCollapse()"
                    class="sidebar-toggle-btn p-1 hover:bg-red-700 rounded transition-all" title="Hide sidebar">
                    <i class="bi bi-chevron-left text-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1">
                <!-- Dashboard -->
                <a href="../../../public/dashboard.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100"
                    title="Dashboard">
                    <i class="bi bi-speedometer2 text-lg flex-shrink-0"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <!-- CORE MODULES SECTION -->
                <div class="px-4 py-3 mt-2 mb-2">
                    <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Core Modules</p>
                </div>

                <!-- 1: Committee Profiles & Membership -->
                <a href="../committee-profiles/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100"
                    title="Committee Profiles & Membership">
                    <i class="bi bi-building text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Committee Profiles</span>
                </a>

                <!-- 2: Committee Meetings Management -->
                <a href="../committee-meetings/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-200"
                    title="Committee Meetings Management">
                    <i class="bi bi-calendar-check text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Meetings</span>
                </a>

                <!-- 3: Agenda & Deliberation Management -->
                <a href="../agenda-deliberation/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-300"
                    title="Agenda & Deliberation Management">
                    <i class="bi bi-list-check text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Agendas &
                        Deliberation</span>
                </a>

                <!-- 4: Referral Tracking & Handling -->
                <a href="../referral-tracking/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-400"
                    title="Referral Tracking & Handling">
                    <i class="bi bi-inbox text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Referrals</span>
                </a>

                <!-- 5: Action Items & Follow-Ups -->
                <a href="../action-tracking/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-500"
                    title="Action Items & Follow-Ups">
                    <i class="bi bi-list-task text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Action Items</span>
                </a>

                <!-- 6: Committee Reports & Recommendations -->
                <a href="../committee-reports/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-600"
                    title="Committee Reports & Recommendations">
                    <i class="bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Reports &
                        Recommendations</span>
                </a>

                <!-- SUPPORTING MODULES SECTION -->
                <div class="px-4 py-3 mt-4 mb-2">
                    <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Support Systems</p>
                </div>

                <!-- User & Access Management -->
                <a href="../user-management/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100"
                    title="User & Access Management">
                    <i class="bi bi-people-fill text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">User Management</span>
                </a>

                <!-- Notification & Communication Hub -->
                <a href="../notifications/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-200"
                    title="Notification & Communication Hub">
                    <i class="bi bi-bell text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Notifications</span>
                </a>

                <!-- System Settings & Configuration -->
                <a href="../system-settings/index.php"
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-300"
                    title="System Settings & Configuration">
                    <i class="bi bi-gear text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Settings</span>
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-3 border-t border-red-700 mt-auto">
                <div class="flex items-center space-x-3 px-2 py-2 hover:bg-red-700 rounded transition-all">
                    <div
                        class="bg-red-600 rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        A</div>
                    <div class="sidebar-text flex-1 text-sm">
                        <p class="font-semibold"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-red-200">Admin</p>
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
                            <!-- Mobile Menu Button -->
                            <button onclick="toggleSidebar()" id="mobile-menu-btn"
                                class="mobile-toggle md:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                                <i class="bi bi-list text-2xl"></i>
                            </button>

                            <!-- Desktop Sidebar Toggle -->
                            <button onclick="toggleSidebarCollapse()" id="sidebar-toggle"
                                class="desktop-toggle hidden md:flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-red-600 dark:hover:text-red-400 focus:outline-none transition-all duration-200 border border-gray-200 dark:border-gray-600"
                                title="Toggle Sidebar">
                                <i class="bi bi-layout-sidebar-inset text-xl"></i>
                            </button>
                        </div>

                        <!-- Page Title & Breadcrumb -->
                        <div class="flex-1 flex items-center justify-center md:justify-start min-w-0">
                            <div class="ml-2 md:ml-4 min-w-0">
                                <h2 class="text-base md:text-xl font-bold text-gray-800 dark:text-white">Committee
                                    Management System</h2>
                            </div>
                        </div>

                        <!-- Right Side -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative group">
                                <button id="notificationBell"
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 relative transition hover-scale p-2">
                                    <i class="bi bi-bell text-xl"></i>
                                    <span id="notificationBadge"
                                        class="absolute -top-2 -right-2 bg-cms-red text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse font-semibold">5</span>
                                </button>

                                <!-- Notifications Dropdown -->
                                <div id="notificationDropdown"
                                    class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 animate-fade-in max-h-96 overflow-hidden flex flex-col">
                                    <!-- Notifications Header -->
                                    <div
                                        class="bg-gradient-to-r from-cms-red to-cms-dark p-4 flex items-center justify-between rounded-t-lg">
                                        <h3 class="font-bold text-white">Notifications</h3>
                                        <button onclick="clearAllNotifications()"
                                            class="text-red-100 hover:text-white text-sm font-semibold transition">Clear
                                            All</button>
                                    </div>

                                    <!-- Notifications List -->
                                    <div id="notificationsList" class="flex-1 overflow-y-auto">
                                        <!-- Notification 1: Meeting Reminder -->
                                        <div
                                            class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer notification-item">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                                    <i
                                                        class="bi bi-calendar-check text-blue-600 dark:text-blue-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                                        Committee Meeting Today</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Finance
                                                        Committee meeting starts at 2:00 PM</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">2 minutes
                                                        ago</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notification 2: Action Item Due -->
                                        <div
                                            class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer notification-item">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center flex-shrink-0">
                                                    <i
                                                        class="bi bi-exclamation-circle text-orange-600 dark:text-orange-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                                        Action Item Due Soon</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Review
                                                        budget proposal - Due in 3 days</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">1 hour ago
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notification 3: Referral Assigned -->
                                        <div
                                            class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer notification-item">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-inbox text-green-600 dark:text-green-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">New
                                                        Referral Assigned</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Bill
                                                        2024-05 assigned to your committee</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">3 hours ago
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notification 4: Agenda Updated -->
                                        <div
                                            class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer notification-item">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center flex-shrink-0">
                                                    <i
                                                        class="bi bi-list-check text-purple-600 dark:text-purple-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                                        Agenda Updated</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Next week's
                                                        committee agenda has been posted</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">5 hours ago
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notification 5: Report Generated -->
                                        <div
                                            class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer notification-item">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center flex-shrink-0">
                                                    <i
                                                        class="bi bi-file-earmark-text text-red-600 dark:text-red-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                                        Monthly Report Ready</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Your
                                                        monthly activity report is ready for review</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">1 day ago
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notifications Footer -->
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700 p-3 rounded-b-lg border-t border-gray-200 dark:border-gray-600">
                                        <a href="../notifications/index.php"
                                            class="text-center text-cms-red hover:text-cms-dark font-semibold text-sm transition block">View
                                            All Notifications</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <button onclick="toggleDarkMode()"
                                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition hover-scale"
                                title="Toggle dark mode">
                                <i class="bi bi-moon-stars dark:hidden"></i>
                                <i class="bi bi-sun-fill hidden dark:block"></i>
                            </button>

                            <!-- User Profile Menu -->
                            <div class="relative group">
                                <button
                                    class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <img src="../../../public/assets/images/logo.png" alt="Profile"
                                        class="w-10 h-10 rounded-full bg-cms-red p-1 object-cover">
                                    <div class="hidden sm:block text-left">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                            <?php echo htmlspecialchars($userName); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                                    </div>
                                    <i class="bi bi-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                                </button>

                                <!-- Profile Dropdown Menu -->
                                <div
                                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 animate-fade-in">
                                    <!-- Profile Header -->
                                    <div class="bg-gradient-to-r from-cms-red to-cms-dark p-4 rounded-t-lg">
                                        <div class="flex items-center space-x-3">
                                            <img src="../../../public/assets/images/logo.png" alt="Profile"
                                                class="w-16 h-16 rounded-full bg-white p-1 object-cover">
                                            <div class="text-white">
                                                <p class="font-bold text-lg"><?php echo htmlspecialchars($userName); ?>
                                                </p>
                                                <p class="text-sm text-red-100">Administrator</p>
                                                <p class="text-xs text-red-200 mt-1">
                                                    <?php echo htmlspecialchars($userEmail); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profile Options -->
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                        <a href="../../../public/pages/user-management/index.php"
                                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-user-circle text-cms-red"></i>
                                            <div>
                                                <p class="font-semibold text-sm">My Profile</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">View and manage your
                                                    profile</p>
                                            </div>
                                        </a>
                                        <a href="../../../public/pages/user-management/index.php?tab=settings"
                                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-cog text-cms-red"></i>
                                            <div>
                                                <p class="font-semibold text-sm">Settings</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Manage account
                                                    settings</p>
                                            </div>
                                        </a>
                                        <a href="../../../public/pages/user-management/index.php?tab=help"
                                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-question-circle text-cms-red"></i>
                                            <div>
                                                <p class="font-semibold text-sm">Help & Support</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Get help and support
                                                </p>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- Logout Option -->
                                    <div class="p-3">
                                        <button onclick="logout()"
                                            class="w-full bg-cms-red hover:bg-cms-dark text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center space-x-2 transition">
                                            <i class="fas fa-sign-out-alt"></i>
                                            <span>Logout</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </nav>

            <!-- Main Content Area - Child Page Content Goes Here -->
            <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 p-3 sm:p-4 lg:p-6">
                <!-- Page-specific content will be injected after this component -->

                <script>
                    // Notification functions
                    function clearAllNotifications() {
                        const notificationsList = document.getElementById('notificationsList');
                        notificationsList.innerHTML = '<div class="p-8 text-center text-gray-500 dark:text-gray-400"><i class="bi bi-inbox text-3xl mb-3 block opacity-50"></i><p class="font-semibold">No notifications</p></div>';
                        document.getElementById('notificationBadge').textContent = '0';
                    }

                    // Add click event listeners to notification items
                    document.addEventListener('DOMContentLoaded', function () {
                        const notificationItems = document.querySelectorAll('.notification-item');
                        notificationItems.forEach(item => {
                            item.addEventListener('click', function () {
                                alert('Opening notification details...');
                            });
                        });
                    });

                    // Sidebar toggle
                    function toggleSidebar() {
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('sidebarOverlay');
                        sidebar.classList.toggle('-translate-x-full');
                        overlay.classList.toggle('hidden');
                    }

                    // Sidebar collapse
                    function toggleSidebarCollapse() {
                        const sidebar = document.getElementById('sidebar');
                        sidebar.classList.toggle('collapsed');
                    }

                    // Dark mode toggle
                    function toggleDarkMode() {
                        const htmlElement = document.documentElement;
                        const isDark = htmlElement.classList.contains('dark');

                        if (isDark) {
                            htmlElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        } else {
                            htmlElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        }
                    }

                    // Logout function
                    function logout() {
                        if (confirm('Are you sure you want to logout?')) {
                            // Create a form and submit it
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '../../../auth/logout.php';
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                </script>