<?php
// Shared header and sidebar for all module pages
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? 'user@example.com';
$userRole = $_SESSION['user_role'] ?? 'User';

// Include Helpers
require_once __DIR__ . '/../../app/helpers/NotificationHelper.php';
require_once __DIR__ . '/../../app/helpers/PermissionHelper.php';
require_once __DIR__ . '/../../app/helpers/UserHelper.php';
require_once __DIR__ . '/../../app/helpers/SystemSettingsHelper.php';

// Fetch system settings for branding
$settings = getSystemSettings();
$themeColor = $settings['theme_color'] ?? '#dc2626';
$systemLogo = $settings['lgu_logo_path'] ?? 'assets/images/logo.png';

// Get current user's unread notifications
$currentUserId = $_SESSION['user_id'] ?? 0;
$unreadCount = getUnreadNotificationCount($currentUserId);
$recentNotifications = getUserNotifications($currentUserId, 5);

// ALWAYS fetch fresh profile picture from database
require_once __DIR__ . '/../../config/database.php';
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

// Debug: Log profile picture status
error_log("Header: User ID = $userId, Profile Picture = " . ($profilePicture ?? 'NULL'));

// Generate user initials for fallback
$userInitials = '';
if ($userName !== 'User') {
    $nameParts = explode(' ', $userName);
    $userInitials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
}

// Detect if we're in a module subdirectory or dashboard
// For modules in pages/module-name/: ../../assets/
// For dashboard (in public/): assets/
$isInModuleSubdir = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$assetPath = $isInModuleSubdir ? '../../' : '';
$imagePathPrefix = $assetPath;
$rootPath = $isInModuleSubdir ? '../../../' : '../';

// For dashboard (one level up to root for index.php)
if (!$isInModuleSubdir && strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $rootPath = '../';
}

// Verify profile picture file exists and build correct path
$profilePictureExists = false;
$displayPath = '';

if ($profilePicture) {
    // Database stores: uploads/profiles/file.jpg
    // Current file: public/includes/header.php
    $fullPath = __DIR__ . '/../' . $profilePicture;
    $profilePictureExists = file_exists($fullPath);

    if ($profilePictureExists) {
        $displayPath = $imagePathPrefix . $profilePicture;
    }
}

// Fallback to default avatar if no picture or file doesn't exist
if (!$displayPath) {
    $displayPath = $imagePathPrefix . 'assets/images/default-avatar.png';
}

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Load NotificationHelper
require_once __DIR__ . '/../../app/helpers/NotificationHelper.php';

// Get user notifications
$unreadCount = getUnreadNotificationCount($userId);
$recentNotifications = getUserNotifications($userId, 5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="<?php echo $themeColor; ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo $pageTitle ?? 'CMS'; ?> | Committee Management System</title>
    <script>
        window.CMS_ROOT = '<?php echo $rootPath; ?>';
        window.CMS_ASSET_PATH = '<?php echo $assetPath; ?>';
    </script>

    <meta name="description" content="Committee Management System - City Government of Valenzuela, Metropolitan Manila">

    <link rel="icon" type="image/png" href="<?php echo $assetPath . $systemLogo; ?>">
    <link rel="apple-touch-icon" href="<?php echo $assetPath . $systemLogo; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/system-styles.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/animations.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/styles-updated.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: 'var(--cms-primary)',
                        'primary-dark': 'var(--cms-primary-dark)',
                        'primary-light': 'var(--cms-primary-light)',
                    }
                }
            }
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

    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/styles-updated.css">

    <style>
        :root {
            --cms-primary:
                <?php echo $themeColor; ?>
            ;
            --cms-primary-dark:
                <?php echo $themeColor; ?>
                dd;
            --cms-primary-light:
                <?php echo $themeColor; ?>
                22;
        }

        /* Sidebar Dynamic Styling - Layout/Solid Background */
        #mobile-sidebar,
        #sidebar {
            background-color: var(--cms-primary) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Active link styling matching the photo (Solid vibrant box) */
        .bg-white\/20.shadow-lg {
            background-color: #dc2626 !important;
            /* Force vibrant red for active */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1) !important;
        }

        /* If not in Red theme, use the white/20 fallback */
        :root[style*="--cms-primary: #dc2626"] .bg-white\/20.shadow-lg,
        :root[style*="--cms-primary: #991b1b"] .bg-white\/20.shadow-lg {
            background-color: #dc2626 !important;
        }

        .hover\:bg-red-700:hover,
        .hover\:bg-red-800:hover {
            filter: brightness(0.9);
            cursor: pointer;
        }

        .text-cms-red {
            color: var(--cms-primary) !important;
        }

        .bg-cms-red {
            background-color: var(--cms-primary) !important;
        }

        .border-cms-red {
            border-color: var(--cms-primary) !important;
        }
    </style>
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
    <!-- Module Loading Overlay -->
    <div id="moduleLoadingOverlay"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center pointer-events-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 md:p-12 text-center">
            <div class="flex justify-center mb-6">
                <div class="relative w-16 h-16">
                    <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-slate-600"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-[var(--cms-primary)] border-r-[var(--cms-primary)]"
                        id="smoothSpinner" style="animation: smoothSpin 2s linear infinite; will-change: transform;">
                    </div>
                </div>
            </div>
            <h3 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white mb-2">Loading Module</h3>
            <p class="text-sm md:text-base text-gray-600 dark:text-slate-400">Please wait while we load the content...
            </p>
        </div>
    </div>
    <style>
        @keyframes smoothSpin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #smoothSpinner {
            transform-origin: center;
            -webkit-transform-origin: center;
        }
    </style>
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden opacity-0 pointer-events-none transition-all duration-300 ease-out">
    </div>

    <div id="mobile-sidebar"
        class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-72 text-white z-50 transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden flex flex-col shadow-2xl"
        style="background-color: var(--cms-primary);">
        <div class="p-4 border-b border-red-700/50 sidebar-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 sidebar-logo">
                    <div class="bg-white rounded-full p-1.5 shadow-lg">
                        <img src="<?php echo $assetPath . $systemLogo; ?>" alt="Logo" class="w-9 h-9 object-contain">
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
            <a href="<?php echo $assetPath; ?>dashboard.php"
                class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-white/20 shadow-lg' : ''; ?>">
                <i class="bi bi-speedometer2 mr-3 text-xl"></i>
                <span class="font-semibold text-lg">Dashboard</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-bold text-white/50 uppercase tracking-widest">Core Modules</p>
            </div>

            <?php if (canViewModule($userId, 'committees')): ?>
                <a href="<?php echo $assetPath; ?>pages/committee-profiles/index.php"
                    class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'committee-profiles' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-building mr-3 text-xl"></i>
                    <span class="font-medium">Committee Profiles</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'meetings')): ?>
                <a href="<?php echo $assetPath; ?>pages/committee-meetings/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'committee-meetings' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-calendar-check mr-3 text-xl"></i>
                    <span class="font-medium">Meetings</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'agendas')): ?>
                <a href="<?php echo $assetPath; ?>pages/agenda-builder/index.php"
                    class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'agenda-builder' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-list-ul mr-3 text-xl"></i>
                    <span class="font-medium">Agendas</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'referrals')): ?>
                <a href="<?php echo $assetPath; ?>pages/referral-management/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'referral-management' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-inbox mr-3 text-xl"></i>
                    <span class="font-medium">Referrals</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'action_items')): ?>
                <a href="<?php echo $assetPath; ?>pages/action-items/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'action-items' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-check2-square mr-3 text-xl"></i>
                    <span class="font-medium">Action Items</span>
                </a>
            <?php endif; ?>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-bold text-white/50 uppercase tracking-widest">Analytics</p>
            </div>

            <?php if (canViewModule($userId, 'reports')): ?>
                <a href="<?php echo $assetPath; ?>pages/reports-analytics/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'reports-analytics' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-graph-up mr-3 text-xl"></i>
                    <span class="font-medium">Reports & Analytics</span>
                </a>
            <?php endif; ?>

            <div class="mt-4 mb-2 px-4 border-t border-white/10 pt-4">
                <p class="text-xs font-bold text-white/50 uppercase tracking-widest">Administration</p>
            </div>

            <?php if (canViewModule($userId, 'users')): ?>
                <a href="<?php echo $assetPath; ?>pages/user-management/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'user-management' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-people-fill mr-3 text-xl"></i>
                    <span class="font-medium">User Management</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'audit_logs')): ?>
                <a href="<?php echo $assetPath; ?>pages/audit-logs/index.php"
                    class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'audit-logs' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-shield-check mr-3 text-xl"></i>
                    <span class="font-medium">Audit Logs</span>
                </a>
            <?php endif; ?>

            <?php if (canViewModule($userId, 'settings')): ?>
                <a href="<?php echo $assetPath; ?>pages/system-settings/index.php"
                    class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'system-settings' ? 'bg-white/20 shadow-lg' : ''; ?>">
                    <i class="bi bi-gear mr-3 text-xl"></i>
                    <span class="font-medium">Settings</span>
                </a>
            <?php endif; ?>

            <a href="<?php echo $assetPath; ?>pages/notifications/index.php"
                class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'notifications' ? 'bg-white/20 shadow-lg' : ''; ?>">
                <i class="bi bi-bell mr-3 text-xl"></i>
                <span class="font-medium">Notifications</span>
            </a>

            <a href="<?php echo $assetPath; ?>pages/my-profile/index.php"
                class="flex items-center px-4 py-2.5 text-white hover:bg-white/10 rounded-xl mb-1 transition-all duration-200 <?php echo $currentDir === 'my-profile' ? 'bg-white/20 shadow-lg' : ''; ?>">
                <i class="bi bi-person-circle mr-3 text-xl"></i>
                <span class="font-medium">My Profile</span>
            </a>

            </a>
        </nav>

        <div class="p-3 mt-auto border-t border-red-700/40">
            <div class="flex items-center space-x-2.5 mb-2.5">
                <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                    <?php if ($profilePicture && $profilePictureExists): ?>
                        <img src="<?php echo $imagePathPrefix . htmlspecialchars($profilePicture); ?>" alt="Profile"
                            class="w-full h-full object-cover"
                            onerror="this.src='<?php echo $imagePathPrefix; ?>assets/images/default-avatar.png'">
                    <?php else: ?>
                        <img src="<?php echo $imagePathPrefix; ?>assets/images/default-avatar.png" alt="Default Avatar"
                            class="w-full h-full object-cover">
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-xs text-red-300 truncate"><?php echo htmlspecialchars($userRole); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">
        <!-- Desktop Sidebar -->
        <aside id="sidebar"
            class="sidebar sidebar-expanded w-64 text-white flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out animate-slide-in-left h-screen fixed md:relative z-30 -translate-x-full md:translate-x-0"
            style="background-color: var(--cms-primary);">
            <div class="p-6 border-b border-red-700 sidebar-logo">
                <a href="<?php echo $assetPath; ?>dashboard.php"
                    class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 transform hover:scale-105 group">
                    <div class="bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                        style="width: 70px; height: 70px;">
                        <img src="<?php echo $assetPath . $systemLogo; ?>" alt="Logo" style="width: 100%; height: 100%;"
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
                    <a href="<?php echo $assetPath; ?>dashboard.php"
                        class="flex items-center px-4 py-3 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-white/20 shadow-lg' : ''; ?>">
                        <i class="bi bi-speedometer2 text-lg"></i>
                        <span class="sidebar-text ml-3 text-lg font-semibold">Dashboard</span>
                    </a>

                    <div class="pt-5 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-bold text-white/50 uppercase tracking-widest">Core Modules</p>
                    </div>

                    <?php if (canViewModule($userId, 'committees')): ?>
                        <a href="<?php echo $assetPath; ?>pages/committee-profiles/index.php"
                            class="flex items-center px-4 py-3 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'committee-profiles' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-people text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Committee
                                Profiles</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'meetings')): ?>
                        <a href="<?php echo $assetPath; ?>pages/committee-meetings/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'committee-meetings' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-calendar-event text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Meetings</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'agendas')): ?>
                        <a href="<?php echo $assetPath; ?>pages/agenda-builder/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'agenda-builder' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-list-check text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Agendas</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'referrals')): ?>
                        <a href="<?php echo $assetPath; ?>pages/referral-management/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'referral-management' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-arrow-left-right text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Referrals</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'action_items')): ?>
                        <a href="<?php echo $assetPath; ?>pages/action-items/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'action-items' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-check2-square text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Action
                                Items</span>
                        </a>
                    <?php endif; ?>

                    <div class="pt-5 pb-2 sidebar-text">
                        <p class="px-4 text-xs font-bold text-white/50 uppercase tracking-widest">Analytics</p>
                    </div>

                    <?php if (canViewModule($userId, 'reports')): ?>
                        <a href="<?php echo $assetPath; ?>pages/reports-analytics/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'reports-analytics' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-graph-up text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Reports
                                &
                                Analytics</span>
                        </a>
                    <?php endif; ?>

                    <div class="pt-5 pb-2 sidebar-text border-t border-white/10 mt-4">
                        <p class="px-4 text-xs font-bold text-white/50 uppercase tracking-widest">System Settings</p>
                    </div>

                    <?php if (canViewModule($userId, 'users')): ?>
                        <a href="<?php echo $assetPath; ?>pages/user-management/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'user-management' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-people text-lg"></i>
                            <span class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">User
                                Management</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'audit_logs')): ?>
                        <a href="<?php echo $assetPath; ?>pages/audit-logs/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'audit-logs' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-shield-check text-lg"></i>
                            <span class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Audit
                                Logs</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModule($userId, 'settings')): ?>
                        <a href="<?php echo $assetPath; ?>pages/system-settings/index.php"
                            class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'system-settings' ? 'bg-white/20 shadow-lg' : ''; ?>">
                            <i class="bi bi-gear text-lg"></i>
                            <span
                                class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Settings</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $assetPath; ?>pages/notifications/index.php"
                        class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'notifications' ? 'bg-white/20 shadow-lg' : ''; ?>">
                        <i class="bi bi-bell text-lg"></i>
                        <span
                            class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">Notifications</span>
                    </a>
                    <a href="<?php echo $assetPath; ?>pages/my-profile/index.php"
                        class="flex items-center px-4 py-2.5 rounded-xl text-white transition-all duration-200 hover:bg-white/10 group <?php echo $currentDir === 'my-profile' ? 'bg-white/20 shadow-lg' : ''; ?>">
                        <i class="bi bi-person-circle text-lg"></i>
                        <span class="sidebar-text ml-3 font-medium group-hover:translate-x-1 transition-transform">My
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
                        <p class="text-xs text-red-200 truncate"><?php echo htmlspecialchars($userRole); ?></p>
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
                                class="sidebar-toggle desktop-toggle flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-red-600 dark:hover:text-red-400 focus:outline-none transition-all duration-200 border border-gray-200 dark:border-gray-600"
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
                                <img src="<?php echo $assetPath . $systemLogo; ?>" alt="CMS"
                                    class="w-10 h-10 object-contain">
                            </div>
                        </div>

                        <!-- Page Title & Breadcrumb -->
                        <div class="flex-1 flex items-center justify-center md:justify-start min-w-0">
                            <div class="ml-2 md:ml-4 min-w-0">
                                <h2 id="page-title"
                                    class="text-base md:text-xl font-bold text-gray-800 dark:text-white">
                                    <?php echo $pageTitle ?? 'Committee Management System'; ?>
                                </h2>
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
                                    class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter real-time-date">
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
                                    <?php if ($unreadCount > 0): ?>
                                        <span
                                            class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                                    <?php endif; ?>
                                </button>

                                <div id="notifications-dropdown"
                                    class="hidden absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                                    <div
                                        class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Notifications
                                            (<?php echo $unreadCount; ?>)</h3>
                                        <button id="mark-all-read-btn"
                                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">Mark
                                            all read</button>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <?php if (empty($recentNotifications)): ?>
                                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                                <i class="bi bi-bell-slash text-4xl mb-2 block"></i>
                                                <p>No new notifications</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($recentNotifications as $notif): ?>
                                                <?php
                                                $typeIcon = $notif['icon'] ?? 'bi-info-circle';
                                                $color = $notif['color'] ?? 'red';

                                                // Map colors to Tailwind classes
                                                $typeBg = "bg-{$color}-100 dark:bg-{$color}-900/30";
                                                $typeText = "text-{$color}-600 dark:text-{$color}-400";

                                                $timeStr = timeAgo($notif['created_at']);
                                                ?>
                                                <a href="<?php echo $assetPath . ($notif['link'] ?? '#'); ?>"
                                                    class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition <?php echo $notif['is_read'] ? 'opacity-75' : ''; ?>">
                                                    <div class="flex items-start space-x-3">
                                                        <div class="<?php echo $typeBg; ?> rounded-full p-2 flex-shrink-0">
                                                            <i class="bi <?php echo $typeIcon; ?> <?php echo $typeText; ?>"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-start justify-between">
                                                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                                </p>
                                                                <?php if (!$notif['is_read']): ?>
                                                                    <span
                                                                        class="ml-2 w-2 h-2 bg-red-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                                <?php echo htmlspecialchars($notif['message']); ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                                <?php echo $timeStr; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div
                                        class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                        <a href="<?php echo $assetPath; ?>pages/notifications/index.php"
                                            class="block text-center text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">View
                                            all
                                            notifications</a>
                                    </div>
                                </div>
                            </div>


                            <!-- User Profile Dropdown -->
                            <div class="relative">
                                <button id="profile-btn"
                                    class="flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center border-2 border-primary shadow-sm">
                                        <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Profile"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <p
                                            class="text-sm font-medium text-gray-800 dark:text-white truncate max-w-[120px] md:max-w-none">
                                            <?php echo htmlspecialchars($userName); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($userRole); ?>
                                        </p>
                                    </div>
                                    <i
                                        class="bi bi-chevron-down text-gray-600 dark:text-gray-400 text-xs hidden sm:inline"></i>
                                </button>

                                <div id="profile-dropdown"
                                    class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
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
                                        <a href="<?php echo $assetPath; ?>pages/my-profile/index.php"
                                            class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="bi bi-person mr-3 text-base"></i>My Profile
                                        </a>
                                        <a href="<?php echo $assetPath; ?>pages/notifications/index.php"
                                            class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="bi bi-bell mr-3 text-base"></i>Notifications
                                        </a>
                                        <?php if ($userRole === 'Admin' || $userRole === 'Super Admin'): ?>
                                            <a href="<?php echo $assetPath; ?>pages/system-settings/index.php"
                                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                <i class="bi bi-gear mr-3 text-base"></i>Settings
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="border-t border-gray-200 py-2">
                                        <a href="javascript:void(0);" onclick="showLogoutModal();"
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
            <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 flex flex-col" id="main-content">
                <div class="flex-1 p-3 sm:p-4 lg:p-6" id="module-content-wrapper">
                    <!-- Module content goes here -->