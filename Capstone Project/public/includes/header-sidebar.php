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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
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
                <a href="../../../public/dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 flex-1">
                    <div class="bg-white rounded-lg shadow-md p-2 w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <img src="../../../public/assets/images/logo.png" alt="Logo" class="w-full h-full object-contain rounded-md">
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
            <nav class="flex-1 px-3 py-4 space-y-1">
                <!-- Dashboard -->
                <a href="../../../public/dashboard.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100" title="Dashboard">
                    <i class="bi bi-speedometer2 text-lg flex-shrink-0"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <!-- 3.1: Committee Structure -->
                <a href="../committee-structure/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100" title="Committee Structure">
                    <i class="bi bi-building text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Committee Structure</span>
                </a>

                <!-- 3.2: Member Assignment -->
                <a href="../member-assignment/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-200" title="Member Assignment">
                    <i class="bi bi-people text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Member Assignment</span>
                </a>

                <!-- 3.3: Referrals -->
                <a href="../referral-management/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-300" title="Referrals">
                    <i class="bi bi-inbox text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Referrals</span>
                </a>

                <!-- 3.4: Meetings -->
                <a href="../meeting-scheduler/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-400" title="Meetings">
                    <i class="bi bi-calendar text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Meetings</span>
                </a>

                <!-- 3.5: Agendas -->
                <a href="../agenda-builder/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-500" title="Agendas">
                    <i class="bi bi-list-check text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Agendas</span>
                </a>

                <!-- 3.6: Deliberation -->
                <a href="../deliberation-tools/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-600" title="Deliberation">
                    <i class="bi bi-chat-dots text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Deliberation</span>
                </a>

                <!-- 3.7: Action Items -->
                <a href="../action-items/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-700" title="Action Items">
                    <i class="bi bi-list-task text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Action Items</span>
                </a>

                <!-- 3.8: Reports -->
                <a href="../report-generation/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-800" title="Reports">
                    <i class="bi bi-file-pdf text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Reports</span>
                </a>

                <!-- 3.9: Coordination -->
                <a href="../inter-committee/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-900" title="Coordination">
                    <i class="bi bi-diagram-2 text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Coordination</span>
                </a>

                <!-- 3.10: Research & Support -->
                <a href="../research-support/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-100" title="Research & Support">
                    <i class="bi bi-book text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">Research & Support</span>
                </a>

                <!-- 3.11: User Management -->
                <a href="../user-management/index.php" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-700 transition-all flex items-center space-x-3 font-semibold text-sm group animate-fade-in-up delay-200" title="User Management">
                    <i class="bi bi-people-fill text-lg flex-shrink-0"></i>
                    <span class="sidebar-text group-hover:translate-x-1 transition-transform">User Management</span>
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
                        <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 relative transition hover-scale p-2">
                            <i class="bi bi-bell text-xl"></i>
                            <span class="absolute -top-2 -right-2 bg-cms-red text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse font-semibold">3</span>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition hover-scale" title="Toggle dark mode">
                            <i class="bi bi-moon-stars dark:hidden"></i>
                            <i class="bi bi-sun-fill hidden dark:block"></i>
                        </button>

                        <!-- User Profile Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <img src="../../../public/assets/images/logo.png" alt="Profile" class="w-10 h-10 rounded-full bg-cms-red p-1 object-cover">
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
                                        <img src="../../../public/assets/images/logo.png" alt="Profile" class="w-16 h-16 rounded-full bg-white p-1 object-cover">
                                        <div class="text-white">
                                            <p class="font-bold text-lg"><?php echo htmlspecialchars($userName); ?></p>
                                            <p class="text-sm text-red-100">Administrator</p>
                                            <p class="text-xs text-red-200 mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Options -->
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <a href="../../../public/pages/user-management/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-user-circle text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">My Profile</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">View and manage your profile</p>
                                        </div>
                                    </a>
                                    <a href="../../../public/pages/user-management/index.php?tab=settings" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-cog text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">Settings</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Manage account settings</p>
                                        </div>
                                    </a>
                                    <a href="../../../public/pages/user-management/index.php?tab=help" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
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

            <!-- Main Content Area - Child Page Content Goes Here -->
            <main class="flex-1 overflow-auto p-6 bg-gray-50 dark:bg-gray-900">
                <!-- Page-specific content will be injected after this component -->
