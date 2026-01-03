<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user info from session
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$userId = $_SESSION['user_id'];

// Fetch user email from database
$userEmail = 'user@example.com';
if ($userId) {
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
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 bg-red-700">
                <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                <span>Dashboard</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Core Modules</p>
            </div>

            <a href="pages/committee-profiles/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-building mr-3 text-lg"></i>
                <span>Committee Profiles</span>
            </a>
            <a href="pages/committee-meetings/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-calendar-check mr-3 text-lg"></i>
                <span>Meetings</span>
            </a>
            <a href="pages/agenda-builder/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-list-ul mr-3 text-lg"></i>
                <span>Agendas</span>
            </a>
            <a href="pages/referral-management/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-inbox mr-3 text-lg"></i>
                <span>Referrals</span>
            </a>
            <a href="pages/action-items/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-check2-square mr-3 text-lg"></i>
                <span>Action Items</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Analytics</p>
            </div>

            <a href="pages/committee-reports/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-graph-up mr-3 text-lg"></i>
                <span>Reports & Analytics</span>
            </a>
            <!-- ADMINISTRATION SECTION -->
            <div class="px-4 py-3 mt-4 mb-2">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Administration</p>
            </div>

            <!-- User Management -->
            <a href="pages/user-management/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-people-fill mr-3 text-lg"></i>
                <span>User Management</span>
            </a>

            <!-- Audit Logs -->
            <a href="pages/audit-logs/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-shield-check mr-3 text-lg"></i>
                <span>Audit Logs</span>
            </a>

            <!-- My Profile -->
            <a href="pages/my-profile/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-person-circle mr-3 text-lg"></i>
                <span>My Profile</span>
            </a>
            <a href="pages/system-settings/index.php"
                class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
                <i class="bi bi-gear mr-3 text-lg"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="p-3 mt-auto border-t border-red-700/40">
            <div class="flex items-center space-x-2.5 mb-2.5">
                <div class="w-9 h-9 rounded-full bg-red-700 flex items-center justify-center">
                    <i class="bi bi-person-fill text-white text-sm"></i>
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

            <nav class="flex-1 overflow-y-auto py-4">
                <div class="px-4 space-y-1">
                    <a href="dashboard.php"
                        class="flex items-center px-4 py-3 rounded-lg bg-red-700 text-white transition-all duration-200 group">
                        <i class="bi bi-speedometer2 text-lg"></i>
                        <span class="sidebar-text ml-3 font-medium">Dashboard</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Core Modules</p>
                    </div>

                    <a href="pages/committee-profiles/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-people text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Committee
                            Profiles</span>
                    </a>
                    <a href="pages/committee-meetings/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-calendar-event text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Meetings</span>
                    </a>
                    <a href="pages/agenda-builder/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-list-check text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Agendas</span>
                    </a>
                    <a href="pages/referral-management/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-arrow-left-right text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Referrals</span>
                    </a>
                    <a href="pages/action-items/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-check2-square text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Action
                            Items</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Analytics</p>
                    </div>

                    <a href="pages/committee-reports/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-graph-up text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Reports &
                            Analytics</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Administration</p>
                    </div>

                    <a href="pages/user-management/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-people text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">User
                            Management</span>
                    </a>
                    <a href="pages/audit-logs/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-shield-check text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Audit Logs</span>
                    </a>
                    <a href="pages/my-profile/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-700/50 transition-all duration-200 group">
                        <i class="bi bi-person-circle text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">My Profile</span>
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-red-700 sidebar-user">
                <div class="flex items-center space-x-3">
                    <div class="bg-red-600 rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-fill text-white"></i>
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

                            <!-- Dark Mode Toggle -->
                            <button id="theme-toggle"
                                class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <i class="bi bi-moon-fill text-lg md:text-xl dark-mode-icon"></i>
                                <i class="bi bi-sun-fill text-xl light-mode-icon hidden"></i>
                            </button>

                            <!-- Notifications -->
                            <div class="relative">
                                <button id="notifications-btn"
                                    class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                                    <i class="bi bi-bell text-xl"></i>
                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                </button>

                                <div id="notifications-dropdown"
                                    class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                                        <button class="text-xs text-red-600 hover:text-red-700">Clear All</button>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                            <div class="flex items-start space-x-3">
                                                <div class="bg-blue-100 rounded-full p-2">
                                                    <i class="bi bi-file-earmark-text text-blue-600"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm text-gray-800">New committee meeting scheduled</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">5 minutes
                                                        ago
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 border-t border-gray-200">
                                        <a href="#" class="text-sm text-red-600 hover:text-red-700 font-medium">View all
                                            notifications</a>
                                    </div>
                                </div>
                            </div>

                            <!-- User Profile Dropdown -->
                            <div class="relative">
                                <button id="profile-btn"
                                    class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg transition">
                                    <div
                                        class="bg-red-600 rounded-full w-8 h-8 flex items-center justify-center text-white">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <p
                                            class="text-sm font-medium text-gray-800 truncate max-w-[120px] md:max-w-none">
                                            <?php echo htmlspecialchars($userName); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                                    </div>
                                    <i class="bi bi-chevron-down text-gray-600 text-xs hidden sm:inline"></i>
                                </button>

                                <div id="profile-dropdown"
                                    class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                    <div class="p-4 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-800">
                                            <?php echo htmlspecialchars($userEmail); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Committee Office</p>
                                    </div>
                                    <div class="py-2">
                                        <a href="pages/user-management/index.php"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="bi bi-person mr-2"></i>My Profile
                                        </a>
                                        <a href="pages/system-settings/index.php"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="bi bi-gear mr-2"></i>Settings
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="bi bi-question-circle mr-2"></i>Help & Support
                                        </a>
                                    </div>
                                    <div class="border-t border-gray-200 py-2">
                                        <a href="javascript:void(0);" onclick="logout(); return false;"
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
                                    12</p>
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
                                    Meetings This Month</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    8</p>
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
                                    Pending Referrals</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    15</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-400 group cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                                    <i class="bi bi-people text-purple-600 dark:text-purple-400 text-3xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-base font-medium text-gray-600 dark:text-gray-300 transition-colors duration-200 group-hover:text-purple-600 dark:group-hover:text-purple-400">
                                    Total Members</p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
                                    245</p>
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
                            <a href="pages/referral-management/index.php"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-search mr-2"></i>Search Referrals
                            </a>
                            <a href="pages/committee-reports/index.php"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-bar-chart mr-2"></i>View Reports
                            </a>
                            <a href="pages/user-management/index.php"
                                class="btn-outline w-full flex items-center justify-center transform hover:scale-105 transition-all duration-200">
                                <i class="bi bi-people mr-2"></i>Manage Users
                            </a>
                        </div>
                    </div>
                </div>



                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Documents Table -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-700">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Documents</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200">
                                    <tr>
                                        <th class="text-left py-2 text-gray-600 font-semibold">Document</th>
                                        <th class="text-left py-2 text-gray-600 font-semibold">Type</th>
                                        <th class="text-left py-2 text-gray-600 font-semibold">Status</th>
                                        <th class="text-left py-2 text-gray-600 font-semibold">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                        <td class="py-3 text-gray-900">Ordinance 2025-001</td>
                                        <td class="py-3 text-gray-600">Ordinance</td>
                                        <td class="py-3">
                                            <span
                                                class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Approved</span>
                                        </td>
                                        <td class="py-3 text-gray-600">Dec 1, 2025</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                        <td class="py-3 text-gray-900">Resolution 2025-045</td>
                                        <td class="py-3 text-gray-600">Resolution</td>
                                        <td class="py-3">
                                            <span
                                                class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                        </td>
                                        <td class="py-3 text-gray-600">Nov 28, 2025</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                        <td class="py-3 text-gray-900">Committee Report Nov 2025</td>
                                        <td class="py-3 text-gray-600">Report</td>
                                        <td class="py-3">
                                            <span
                                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">Review</span>
                                        </td>
                                        <td class="py-3 text-gray-600">Nov 25, 2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-800">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Upcoming Meetings</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 rounded border-l-4 border-red-600 hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900">Finance Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 5, 2025 at 2:00 PM
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded border-l-4 border-green-500 hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900">Planning Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 6, 2025 at 10:00 AM
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded border-l-4 border-blue-500 hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900">General Assembly</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 8, 2025 at 3:30 PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </main>
    </div>
    </div>

    <script src="assets/js/script-updated.js"></script>

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
                            data: [30, 25, 20, 25],
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
        });

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                fetch('../app/controllers/AuthController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=logout'
                })
                    .then(() => {
                        window.location.href = '../auth/login.php?logout=success';
                    })
                    .catch(() => {
                        window.location.href = '../auth/login.php?logout=success';
                    });
            }
        }
    </script>
</body>

</html>