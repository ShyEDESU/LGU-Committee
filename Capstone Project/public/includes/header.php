<?php
// Shared header and sidebar for all module pages
// Include this file at the top of each module page

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#dc2626">
    <title><?php echo $pageTitle ?? 'CMS'; ?> | Committee Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="apple-touch-icon" href="../../assets/images/logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Animations CSS -->
    <link rel="stylesheet" href="../../assets/css/animations.css">
    
    <script>
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
                    }
                }
            }
        }
    </script>
    
    <style>
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased transition-colors duration-300">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 bg-gradient-to-b from-cms-red to-cms-dark text-white fixed md:relative h-full z-40 transform md:translate-x-0 transition-all duration-300 overflow-y-auto">
            <!-- Header with Logo -->
            <div class="p-4 border-b border-red-700 sticky top-0 bg-gradient-to-b from-cms-red to-cms-dark flex items-center justify-between">
                <a href="../../dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 flex-1">
                    <!-- Circular Logo -->
                    <div class="bg-white rounded-full shadow-md p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <img src="../../assets/images/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <div class="sidebar-text">
                        <h1 class="text-base font-bold">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </a>
                
                <!-- Sidebar Collapse Button -->
                <button onclick="toggleSidebarCollapse()" class="sidebar-toggle-btn p-1 hover:bg-red-700 rounded transition-all" title="Hide sidebar">
                    <i class="bi bi-chevron-left text-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <!-- Dashboard -->
                <a href="../../dashboard.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 font-semibold text-sm group">
                    <i class="bi bi-speedometer2 text-lg flex-shrink-0"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <!-- CORE MODULES SECTION -->
                <div class="pt-4 pb-2 sidebar-text">
                    <p class="px-4 text-xs font-semibold text-red-200 uppercase tracking-wider">Core Modules</p>
                </div>
                
                <!-- Committee Profiles -->
                <a href="../committee-profiles/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'committee-profiles' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-building text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Committee Profiles</span>
                </a>
                
                <!-- Meetings -->
                <a href="../committee-meetings/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'committee-meetings' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-calendar-check text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Meetings</span>
                </a>
                
                <!-- Agendas & Deliberation -->
                <a href="../agenda-builder/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'agenda-builder' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-list-ul text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Agendas & Deliberation</span>
                </a>
                
                <!-- Referrals -->
                <a href="../referral-management/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'referral-management' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-inbox text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Referrals</span>
                </a>
                
                <!-- Action Items -->
                <a href="../action-items/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'action-items' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-list-task text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Action Items</span>
                </a>
                
                <!-- Reports & Recommendations -->
                <a href="../committee-reports/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'committee-reports' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Reports & Recommendations</span>
                </a>

                <!-- SUPPORT SYSTEMS SECTION -->
                <div class="pt-4 pb-2 sidebar-text">
                    <p class="px-4 text-xs font-semibold text-red-200 uppercase tracking-wider">Support Systems</p>
                </div>
                
                <!-- User Management -->
                <a href="../user-management/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'user-management' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-people text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">User Management</span>
                </a>
                
                <!-- Settings -->
                <a href="../system-settings/index.php" class="w-full text-left px-4 py-3 rounded-lg <?php echo $currentDir === 'system-settings' ? 'bg-red-700' : 'hover:bg-red-700'; ?> transition-all flex items-center space-x-3 text-sm group">
                    <i class="bi bi-gear text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Settings</span>
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-3 border-t border-red-700 mt-auto">
                <div class="flex items-center space-x-3 px-2 py-2 hover:bg-red-700 rounded transition-all">
                    <div class="bg-red-600 rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                    <div class="sidebar-text flex-1 text-sm">
                        <p class="font-semibold"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-red-200">Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-20">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <button onclick="toggleSidebar()" class="md:hidden text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                        <button onclick="toggleSidebarCollapse()" class="hidden md:block p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition" title="Toggle sidebar">
                            <i class="bi bi-arrow-left-right text-xl text-gray-600 dark:text-gray-400"></i>
                        </button>
                        <div class="hidden md:block">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo $pageTitle ?? 'Committee Management System'; ?></h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">City Government of Valenzuela</p>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button onclick="toggleNotifications()" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 relative transition p-2">
                                <i class="bi bi-bell text-xl"></i>
                                <span class="absolute -top-2 -right-2 bg-cms-red text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse font-semibold">5</span>
                            </button>
                            
                            <!-- Notification Dropdown (same as dashboard) -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50">
                                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notifications</h3>
                                    <span class="bg-cms-red text-white text-xs px-2 py-1 rounded-full font-semibold">5 New</span>
                                </div>
                                <div class="max-h-96 overflow-y-auto p-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 p-4 text-center">No new notifications</p>
                                </div>
                            </div>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            <i class="bi bi-moon-stars dark:hidden"></i>
                            <i class="bi bi-sun-fill hidden dark:block"></i>
                        </button>

                        <!-- User Profile Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <img src="../../assets/images/logo.png" alt="Profile" class="w-10 h-10 rounded-full bg-cms-red p-1 object-cover">
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($userName); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                                </div>
                                <i class="bi bi-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                            </button>

                            <!-- Profile Dropdown -->
                            <div class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <div class="bg-gradient-to-r from-cms-red to-cms-dark p-4 rounded-t-lg">
                                    <div class="flex items-center space-x-3">
                                        <img src="../../assets/images/logo.png" alt="Profile" class="w-16 h-16 rounded-full bg-white p-1 object-cover">
                                        <div class="text-white">
                                            <p class="font-bold text-lg"><?php echo htmlspecialchars($userName); ?></p>
                                            <p class="text-sm text-red-100">Administrator</p>
                                            <p class="text-xs text-red-200 mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <a href="../user-management/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-user-circle text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">My Profile</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">View and manage your profile</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="p-3">
                                    <button onclick="logout()" class="w-full bg-cms-red hover:bg-cms-dark text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center space-x-2 transition">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-auto p-6 bg-gray-50 dark:bg-gray-900">
                <!-- Page content goes here -->
