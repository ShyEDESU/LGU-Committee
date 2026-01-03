<?php
// Shared header and sidebar for all module pages
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? 'user@example.com';
$userRole = $_SESSION['user_role'] ?? 'User';

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
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
    <title><?php echo $pageTitle ?? 'CMS'; ?> | Committee Management System</title>
    <meta name="description" content="Committee Management System - City Government of Valenzuela, Metropolitan Manila">

    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../../assets/images/logo.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/system-styles.css">
    <link rel="stylesheet" href="../../assets/css/styles-updated.css">

    <!-- Tailwind Dark Mode Configuration -->
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

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
                        <img src="../../assets/images/logo.png" alt="Logo" class="w-9 h-9 object-contain">
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

        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            <a href="../../dashboard.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                <span>Dashboard</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Core Modules</p>
            </div>

            <a href="../committee-profiles/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'committee-profiles' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-people mr-3 text-lg"></i>
                <span>Committee Profiles</span>
            </a>
            <a href="../committee-meetings/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'committee-meetings' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-calendar-event mr-3 text-lg"></i>
                <span>Meetings</span>
            </a>
            <a href="../agenda-builder/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'agenda-builder' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-list-check mr-3 text-lg"></i>
                <span>Agendas</span>
            </a>
            <a href="../referral-management/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'referral-management' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-arrow-left-right mr-3 text-lg"></i>
                <span>Referrals</span>
            </a>
            <a href="../action-items/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'action-items' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-check2-square mr-3 text-lg"></i>
                <span>Action Items</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Analytics</p>
            </div>

            <a href="../committee-reports/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'committee-reports' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-graph-up mr-3 text-lg"></i>
                <span>Reports & Analytics</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Administration</p>
            </div>

            <a href="../user-management/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'user-management' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-people mr-3 text-lg"></i>
                <span>User Management</span>
            </a>
            <a href="../audit-logs/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'audit-logs' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-shield-check mr-3 text-lg"></i>
                <span>Audit Logs</span>
            </a>
            <a href="../my-profile/index.php"
                class="flex items-center px-4 py-3 text-white rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo $currentDir === 'my-profile' ? 'bg-red-700' : 'hover:bg-red-700/70'; ?>">
                <i class="bi bi-person-circle mr-3 text-lg"></i>
                <span>My Profile</span>
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
                <a href="../../dashboard.php"
                    class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 transform hover:scale-105 group">
                    <div class="bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                        style="width: 70px; height: 70px;">
                        <img src="../../assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%;"
                            class="object-contain">
                    </div>
                    <div class="transform transition-all duration-300 group-hover:translate-x-1 sidebar-text">
                        <h1 class="text-lg font-bold">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <nav class="flex-1 px-3 py-4 overflow-y-auto">
                    <a href="../../dashboard.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-speedometer2 text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Dashboard</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Core Modules</p>
                    </div>

                    <a href="../committee-profiles/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'committee-profiles' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-people text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Committee
                            Profiles</span>
                    </a>
                    <a href="../committee-meetings/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'committee-meetings' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-calendar-event text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Meetings</span>
                    </a>
                    <a href="../agenda-builder/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'agenda-builder' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-list-check text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Agendas</span>
                    </a>
                    <a href="../referral-management/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'referral-management' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-arrow-left-right text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Referrals</span>
                    </a>
                    <a href="../action-items/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'action-items' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-check2-square text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Action
                            Items</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Analytics</p>
                    </div>

                    <a href="../committee-reports/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'committee-reports' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-graph-up text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Reports &
                            Analytics</span>
                    </a>

                    <div class="pt-4 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Administration</p>
                    </div>

                    <a href="../user-management/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'user-management' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-people text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">User
                            Management</span>
                    </a>
                    <a href="../audit-logs/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'audit-logs' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-shield-check text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">Audit Logs</span>
                    </a>
                    <a href="../my-profile/index.php"
                        class="flex items-center px-4 py-3 rounded-lg text-white transition-all duration-200 group <?php echo $currentDir === 'my-profile' ? 'bg-red-700' : 'hover:bg-red-700/50'; ?>">
                        <i class="bi bi-person-circle text-lg"></i>
                        <span class="sidebar-text ml-3 group-hover:translate-x-1 transition-transform">My Profile</span>
                    </a>
                </nav>
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
                            <!-- Mobile Menu Button -->
                            <button id="mobile-menu-btn"
                                class="mobile-toggle md:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                                <i class="bi bi-list text-2xl"></i>
                            </button>

                            <!-- Desktop Sidebar Toggle -->
                            <button id="sidebar-toggle"
                                class="desktop-toggle flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-red-600 dark:hover:text-red-400 focus:outline-none transition-all duration-200 border border-gray-200 dark:border-gray-600"
                                title="Toggle Sidebar">
                                <i class="bi bi-layout-sidebar-inset text-xl sidebar-icon"></i>
                                <i class="bi bi-arrow-right text-xl arrow-icon hidden"></i>
                            </button>
                        </div>

                        <!-- Page Title & Breadcrumb -->
                        <div class="flex-1 flex items-center justify-center md:justify-start min-w-0">
                            <div class="ml-2 md:ml-4 min-w-0">
                                <h2 class="text-base md:text-xl font-bold text-gray-800 dark:text-white">
                                    <?php echo $pageTitle ?? 'Committee Management System'; ?>
                                </h2>
                            </div>
                        </div>

                        <!-- Right Side -->
                        <div class="flex items-center space-x-1 md:space-x-4">
                            <!-- Dark Mode Toggle -->
                            <button id="theme-toggle"
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
                                title="Toggle dark mode">
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
                                                    <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
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
                                        <p class="text-xs text-gray-500">Administrator</p>
                                    </div>
                                    <i class="bi bi-chevron-down text-gray-600 text-xs hidden sm:inline"></i>
                                </button>

                                <div id="profile-dropdown"
                                    class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                    <div class="p-4 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-800">
                                            <?php echo htmlspecialchars($userEmail); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">Committee Office</p>
                                    </div>
                                    <div class="py-2">
                                        <a href="../my-profile/index.php"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="bi bi-person mr-2"></i>My Profile
                                        </a>
                                        <a href="../user-management/index.php?tab=settings"
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
            </nav>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-3 sm:p-4 lg:p-6" id="main-content">
                <!-- Module content goes here -->