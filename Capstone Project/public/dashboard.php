<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#dc2626">
    <title>CMS - Committee Management System | City of Valenzuela</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    
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
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased transition-colors duration-300">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 bg-gradient-to-b from-cms-red to-cms-dark text-white fixed md:relative h-full z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 overflow-y-auto animate-fade-in">
            <!-- Header with Collapse Button -->
            <div class="p-4 border-b border-red-700 sticky top-0 bg-gradient-to-b from-cms-red to-cms-dark flex items-center justify-between">
                <a href="dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 flex-1">
                    <div class="bg-white rounded-full shadow-md p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <img src="assets/images/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full">
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
                <a href="dashboard.php" class="w-full text-left px-4 py-3 rounded-lg bg-red-700 hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up" title="Dashboard">
                    <i class="bi bi-speedometer2 text-lg flex-shrink-0"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <!-- CORE MODULES SECTION -->
                <div class="pt-4 pb-2 sidebar-text">
                    <p class="px-4 text-xs font-semibold text-red-200 uppercase tracking-wider">Core Modules</p>
                </div>
                
                <!-- Committee Profiles -->
                <a href="pages/committee-profiles/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Committee Profiles">
                    <i class="bi bi-building text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Committee Profiles</span>
                </a>

                <!-- Meetings -->
                <a href="pages/committee-meetings/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Meetings">
                    <i class="bi bi-calendar-check text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Meetings</span>
                </a>

                <!-- Agendas & Deliberation -->
                <a href="pages/agenda-builder/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Agendas & Deliberation">
                    <i class="bi bi-list-ul text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Agendas & Deliberation</span>
                </a>

                <!-- Referrals -->
                <a href="pages/referral-management/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Referrals">
                    <i class="bi bi-inbox text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Referrals</span>
                </a>

                <!-- Action Items -->
                <a href="pages/action-items/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Action Items">
                    <i class="bi bi-list-task text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Action Items</span>
                </a>

                <!-- Reports & Recommendations -->
                <a href="pages/committee-reports/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Reports & Recommendations">
                    <i class="bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Reports & Recommendations</span>
                </a>

                <!-- SUPPORT SYSTEMS SECTION -->
                <div class="pt-4 pb-2 sidebar-text">
                    <p class="px-4 text-xs font-semibold text-red-200 uppercase tracking-wider">Support Systems</p>
                </div>
                
                <!-- User Management -->
                <a href="pages/user-management/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="User Management">
                    <i class="bi bi-people text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">User Management</span>
                </a>
                
                <!-- Settings -->
                <a href="pages/system-settings/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 text-sm group" title="Settings">
                    <i class="bi bi-gear text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Settings</span>
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-3 border-t border-red-700 mt-auto">
                <div class="flex items-center space-x-3 px-2 py-2 hover:bg-red-700 rounded transition-all">
                    <div class="bg-red-600 rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold flex-shrink-0">A</div>
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
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-20 animate-slide-in">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <button onclick="toggleSidebar()" class="md:hidden text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition hover-scale">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                        <button onclick="toggleSidebarCollapse()" class="hidden md:block p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition hover-scale" title="Toggle sidebar collapse">
                            <i class="bi bi-arrow-left-right text-xl text-gray-600 dark:text-gray-400"></i>
                        </button>
                        <div class="hidden md:block">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Committee Management System</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">City Government of Valenzuela</p>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button onclick="toggleNotifications()" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 relative transition hover-scale p-2">
                                <i class="bi bi-bell text-xl"></i>
                                <span class="absolute -top-2 -right-2 bg-cms-red text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse font-semibold">5</span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50 animate-fade-in">
                                <!-- Header -->
                                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notifications</h3>
                                    <span class="bg-cms-red text-white text-xs px-2 py-1 rounded-full font-semibold">5 New</span>
                                </div>
                                
                                <!-- Notification List -->
                                <div class="max-h-96 overflow-y-auto">
                                    <!-- Notification Item 1 -->
                                    <a href="pages/referral-management/index.php" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                <i class="bi bi-inbox text-blue-600 dark:text-blue-400"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">New Referral Assigned</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Ordinance No. 2024-001 has been assigned to your committee</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">5 minutes ago</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="w-2 h-2 bg-cms-red rounded-full block"></span>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <!-- Notification Item 2 -->
                                    <a href="pages/committee-meetings/index.php" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                <i class="bi bi-calendar-check text-green-600 dark:text-green-400"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Meeting Scheduled</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Committee on Finance meeting scheduled for tomorrow at 2:00 PM</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="w-2 h-2 bg-cms-red rounded-full block"></span>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <!-- Notification Item 3 -->
                                    <a href="pages/action-items/index.php" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                                <i class="bi bi-list-task text-yellow-600 dark:text-yellow-400"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Action Item Deadline Approaching</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Review budget proposal is due in 2 days</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">5 hours ago</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="w-2 h-2 bg-cms-red rounded-full block"></span>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <!-- Notification Item 4 (Read) -->
                                    <a href="pages/committee-reports/index.php" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition opacity-60">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                                <i class="bi bi-file-earmark-text text-purple-600 dark:text-purple-400"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Committee Report Submitted</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Committee on Health submitted their quarterly report</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">1 day ago</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                
                                <!-- Footer -->
                                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
                                    <a href="#" class="text-sm text-cms-red hover:text-cms-dark font-semibold block text-center">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition hover-scale" title="Toggle dark mode">
                            <i class="bi bi-moon-stars dark:hidden"></i>
                            <i class="bi bi-sun-fill hidden dark:block"></i>
                        </button>

                        <!-- User Profile Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <img src="assets/images/logo.png" alt="Profile" class="w-10 h-10 rounded-full bg-cms-red p-1 object-cover">
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($userName); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                                </div>
                                <i class="bi bi-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                            </button>

                            <!-- Profile Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 animate-fade-in">
                                <!-- Profile Header -->
                                <div class="bg-gradient-to-r from-cms-red to-cms-dark p-4 rounded-t-lg">
                                    <div class="flex items-center space-x-3">
                                        <img src="assets/images/logo.png" alt="Profile" class="w-16 h-16 rounded-full bg-white p-1 object-cover">
                                        <div class="text-white">
                                            <p class="font-bold text-lg"><?php echo htmlspecialchars($userName); ?></p>
                                            <p class="text-sm text-red-100">Administrator</p>
                                            <p class="text-xs text-red-200 mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Options -->
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <a href="pages/user-management/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-user-circle text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">My Profile</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">View and manage your profile</p>
                                        </div>
                                    </a>
                                    <a href="pages/user-management/index.php?tab=settings" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-cog text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">Settings</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Manage account settings</p>
                                        </div>
                                    </a>
                                    <a href="pages/user-management/index.php?tab=help" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-question-circle text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">Help & Support</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Get help and support</p>
                                        </div>
                                    </a>
                                </div>

                                <!-- Logout Option -->
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
            <main class="flex-1 overflow-auto p-6 space-y-6 bg-gray-50 dark:bg-gray-900">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-cms-red to-cms-dark text-white rounded-lg p-8 shadow-lg animate-fade-in">
                    <h1 class="text-3xl font-bold mb-2">Welcome to Committee Management System</h1>
                    <p class="text-red-100">Manage committees, meetings, and legislative processes efficiently</p>
                </div>

                <!-- Stats Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Active Committees Stat -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-cms-red hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold">Active Committees</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">12</p>
                            </div>
                            <div class="bg-cms-red bg-opacity-10 p-4 rounded-lg">
                                <i class="bi bi-building text-2xl text-cms-red"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                            <i class="bi bi-arrow-up text-green-500"></i>
                            <span class="text-green-500 font-semibold">+2</span> from last month
                        </p>
                    </div>

                    <!-- Upcoming Meetings Stat -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold">Meetings This Month</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">8</p>
                            </div>
                            <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                                <i class="bi bi-calendar-event text-2xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                            <i class="bi bi-exclamation-circle text-orange-500"></i>
                            <span class="text-orange-500 font-semibold">2</span> scheduled for this week
                        </p>
                    </div>

                    <!-- Pending Referrals Stat -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-green-500 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold">Pending Referrals</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">15</p>
                            </div>
                            <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                                <i class="bi bi-inbox text-2xl text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                            <i class="bi bi-arrow-down text-red-500"></i>
                            <span class="text-red-500 font-semibold">3</span> overdue
                        </p>
                    </div>

                    <!-- Total Members Stat -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold">Total Members</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">245</p>
                            </div>
                            <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-lg">
                                <i class="bi bi-people text-2xl text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                            <i class="bi bi-arrow-up text-green-500"></i>
                            <span class="text-green-500 font-semibold">+12</span> new this month
                        </p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Document Distribution Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Document Distribution</h3>
                        <div class="flex items-center justify-center" style="height: 300px;">
                            <canvas id="documentChart"></canvas>
                        </div>
                    </div>

                    <!-- Meeting Attendance Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Meeting Attendance Rate</h3>
                        <div class="flex items-center justify-center" style="height: 300px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Upcoming -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Documents -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Documents</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-semibold">Document</th>
                                        <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-semibold">Type</th>
                                        <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-semibold">Status</th>
                                        <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-semibold">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="py-3 text-gray-900 dark:text-gray-100">Ordinance 2025-001</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Ordinance</td>
                                        <td class="py-3">
                                            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-xs font-semibold">Approved</span>
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Dec 1, 2025</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="py-3 text-gray-900 dark:text-gray-100">Resolution 2025-045</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Resolution</td>
                                        <td class="py-3">
                                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Nov 28, 2025</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="py-3 text-gray-900 dark:text-gray-100">Committee Report Nov 2025</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Report</td>
                                        <td class="py-3">
                                            <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded text-xs font-semibold">Review</span>
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">Nov 25, 2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="#" class="text-cms-red hover:text-cms-dark font-semibold text-sm mt-4 inline-block">View all documents →</a>
                    </div>

                    <!-- Upcoming Meetings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Upcoming Meetings</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-cms-red hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">Finance Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 5, 2025 at 2:00 PM</p>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-green-500 hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">Planning Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 6, 2025 at 10:00 AM</p>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-blue-500 hover:shadow-md transition">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">General Assembly</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 8, 2025 at 3:30 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="/assets/js/ui-enhancements.js"></script>

    <script>
        // Dark Mode Toggle Function
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            // Save preference to localStorage
            const isDarkMode = html.classList.contains('dark');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        }

        // Initialize dark mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme');
            const html = document.documentElement;
            
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            }

            // Initialize Charts
            initCharts();
        });

        // Initialize Charts
        function initCharts() {
            // Document Distribution Chart
            const documentCtx = document.getElementById('documentChart');
            if (documentCtx) {
                new Chart(documentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Ordinances', 'Resolutions', 'Reports', 'Agendas'],
                        datasets: [{
                            data: [30, 25, 20, 25],
                            backgroundColor: [
                                '#dc2626',
                                '#3b82f6',
                                '#10b981',
                                '#f59e0b'
                            ],
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
                                    color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151'
                                }
                            }
                        }
                    }
                });
            }

            // Meeting Attendance Chart
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
                        indexAxis: 'x',
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) { return value + '%'; },
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                                }
                            },
                            x: {
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    font: { size: 12 },
                                    color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151'
                                }
                            }
                        }
                    }
                });
            }
        }

        // Sidebar Toggle for Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Sidebar Collapse Toggle for Desktop
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Reinitialize charts on resize
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.add('collapsed');
            }
        });

        // Logout Function with Confirmation
        function logout() {
            showLogoutConfirmation();
        }
        
        function showLogoutConfirmation() {
            const modal = document.createElement('div');
            modal.id = 'logoutConfirmModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 animate-fade-in';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-md w-full animate-scale-in">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-box-arrow-right text-cms-red"></i>
                            Confirm Logout
                        </h3>
                        <button onclick="closeLogoutModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <i class="bi bi-x text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to log out?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">You will be redirected to the login page.</p>
                    </div>
                    
                    <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
                        <button onclick="closeLogoutModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 font-semibold transition">
                            Cancel
                        </button>
                        <button onclick="confirmLogout()" class="flex-1 px-4 py-2 text-white bg-cms-red hover:bg-cms-dark rounded-lg font-semibold transition flex items-center justify-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
        }
        
        function closeLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        function confirmLogout() {
            closeLogoutModal();
            
            fetch('../app/controllers/AuthController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=logout'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '../auth/login.php?logout=success';
                } else {
                    // Still redirect even if fetch fails
                    window.location.href = '../auth/login.php?logout=success';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = '../auth/login.php?logout=success';
            });
        }
        
        // Notification toggle function
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notificationDropdown');
            const notificationButton = e.target.closest('button[onclick="toggleNotifications()"]');
            
            if (!notificationButton && dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
            
            const modal = document.getElementById('logoutConfirmModal');
            if (modal && e.target === modal) {
                closeLogoutModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
                
                if (document.getElementById('logoutConfirmModal')) {
                    closeLogoutModal();
                }
            }
        });

        // Close sidebar when clicking a link on mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.remove('-translate-x-full');
                document.getElementById('sidebarOverlay').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
