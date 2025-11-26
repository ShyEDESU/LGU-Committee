<?php
/**
 * Dashboard - Main landing page after login
 * 
 * Displays overview of system with key statistics and quick links.
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../app/middleware/SessionManager.php');

$sessionManager = new SessionManager($conn);

// Check if user is logged in
if (!$sessionManager->isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$user = $sessionManager->getCurrentUser();
$role = $_SESSION['role_name'];

// Get statistics
$stats = [
    'committees' => $conn->query("SELECT COUNT(*) as count FROM committees")->fetch_assoc()['count'],
    'upcoming_meetings' => $conn->query("SELECT COUNT(*) as count FROM meetings WHERE status = 'scheduled' AND meeting_date > NOW()")->fetch_assoc()['count'],
    'pending_documents' => $conn->query("SELECT COUNT(*) as count FROM legislative_documents WHERE status IN ('draft', 'in_committee')")->fetch_assoc()['count'],
    'active_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE")->fetch_assoc()['count'],
    'pending_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status IN ('pending', 'in_progress') AND assigned_to_id = " . intval($user['user_id']))->fetch_assoc()['count'],
];

// Get document statistics by status
$doc_status = [
    'draft' => $conn->query("SELECT COUNT(*) as count FROM legislative_documents WHERE status = 'draft'")->fetch_assoc()['count'] ?? 0,
    'in_committee' => $conn->query("SELECT COUNT(*) as count FROM legislative_documents WHERE status = 'in_committee'")->fetch_assoc()['count'] ?? 0,
    'approved' => $conn->query("SELECT COUNT(*) as count FROM legislative_documents WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0,
    'rejected' => $conn->query("SELECT COUNT(*) as count FROM legislative_documents WHERE status = 'rejected'")->fetch_assoc()['count'] ?? 0,
];

// Get referral statistics
$referral_stats = [
    'incoming' => $conn->query("SELECT COUNT(*) as count FROM referrals WHERE referral_type = 'incoming'")->fetch_assoc()['count'] ?? 0,
    'outgoing' => $conn->query("SELECT COUNT(*) as count FROM referrals WHERE referral_type = 'outgoing'")->fetch_assoc()['count'] ?? 0,
    'pending' => $conn->query("SELECT COUNT(*) as count FROM referrals WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0,
];

// Get meeting statistics by month
$monthly_meetings = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i month"));
    $count = $conn->query("SELECT COUNT(*) as count FROM meetings WHERE DATE_FORMAT(meeting_date, '%Y-%m') = '$date'")->fetch_assoc()['count'] ?? 0;
    $monthly_meetings[date('M', strtotime($date))] = $count;
}

// Get task completion stats
$task_stats = [
    'completed' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'")->fetch_assoc()['count'] ?? 0,
    'in_progress' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'in_progress'")->fetch_assoc()['count'] ?? 0,
    'pending' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0,
];

// Get recent activities
$recentActivities = $conn->query("
    SELECT * FROM audit_logs 
    WHERE user_id = " . intval($user['user_id']) . "
    ORDER BY timestamp DESC 
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Legislative Services CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="header-left">
                <button class="hamburger-btn" title="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1>Legislative Services CMS</h1>
                    <p>Committee Management System</p>
                </div>
            </div>
            
            <div class="header-right">
                <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="notification-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="user-dropdown">
                    <div class="user-info" id="userInfoBtn">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
                        </div>
                    </div>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <a href="#" class="user-dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="#" class="user-dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <button class="user-dropdown-item logout" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Overlay for sidebar -->
    <div class="overlay"></div>
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            <div class="sidebar-category">
                <a href="dashboard.php" class="sidebar-link">
                    <i class="sidebar-icon fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <!-- Committees & Members -->
            <div class="sidebar-category">
                <div class="sidebar-category-title">Committees</div>
                
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-users"></i>
                        <span>Committee Management</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="committees/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-list"></i> All Committees
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="committees/create.php" class="sidebar-submenu-link">
                                <i class="fas fa-plus-circle"></i> Create Committee
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="committees/directory.php" class="sidebar-submenu-link">
                                <i class="fas fa-address-book"></i> Member Directory
                            </a>
                        </li>
                    </ul>
                </li>
            </div>
            
            <!-- Meetings & Agendas -->
            <div class="sidebar-category">
                <div class="sidebar-category-title">Meetings</div>
                
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-calendar-alt"></i>
                        <span>Meetings & Sessions</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="meetings/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-list"></i> View Meetings
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="meetings/schedule.php" class="sidebar-submenu-link">
                                <i class="fas fa-calendar-plus"></i> Schedule Meeting
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="meetings/calendar.php" class="sidebar-submenu-link">
                                <i class="fas fa-calendar"></i> Calendar View
                            </a>
                        </li>
                    </ul>
                </li>
            </div>
            
            <!-- Bills & Legislation -->
            <div class="sidebar-category">
                <div class="sidebar-category-title">Legislation</div>
                
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-file-alt"></i>
                        <span>Bills & Documents</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="documents/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-list"></i> Browse Documents
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="documents/create.php" class="sidebar-submenu-link">
                                <i class="fas fa-file-circle-plus"></i> File Document
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="documents/tracking.php" class="sidebar-submenu-link">
                                <i class="fas fa-tracking"></i> Track Progress
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-arrow-right-arrow-left"></i>
                        <span>Referrals & Routing</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="referrals/incoming.php" class="sidebar-submenu-link">
                                <i class="fas fa-inbox"></i> Incoming Referrals
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="referrals/outgoing.php" class="sidebar-submenu-link">
                                <i class="fas fa-paper-plane"></i> Outgoing Referrals
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="endorsements/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-stamp"></i> Endorsements
                            </a>
                        </li>
                    </ul>
                </li>
            </div>
            
            <!-- Tracking & Analytics -->
            <div class="sidebar-category">
                <div class="sidebar-category-title">Tracking</div>
                
                <li class="sidebar-menu-item">
                    <a href="tasks/index.php" class="sidebar-link">
                        <i class="sidebar-icon fas fa-tasks"></i>
                        <span>Action Items</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="reports/index.php" class="sidebar-link">
                        <i class="sidebar-icon fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
            </div>
            
            <!-- System Administration -->
            <div class="sidebar-category">
                <div class="sidebar-category-title">Administration</div>
                
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-users-cog"></i>
                        <span>User Management</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="users/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-list"></i> Users
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="users/add.php" class="sidebar-submenu-link">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="roles/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-shield-alt"></i> Roles
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-link" data-toggle>
                        <i class="sidebar-icon fas fa-cogs"></i>
                        <span>Settings</span>
                        <i class="sidebar-toggle-icon fas fa-chevron-right"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu-item">
                            <a href="settings/general.php" class="sidebar-submenu-link">
                                <i class="fas fa-sliders-h"></i> General
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="backup/index.php" class="sidebar-submenu-link">
                                <i class="fas fa-database"></i> Backup
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="logs/audit.php" class="sidebar-submenu-link">
                                <i class="fas fa-history"></i> Audit Logs
                            </a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="logs/error.php" class="sidebar-submenu-link">
                                <i class="fas fa-exclamation-triangle"></i> Errors
                            </a>
                        </li>
                    </ul>
                </li>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-container">
        <main class="main-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                <span>></span>
                <span>Dashboard</span>
            </div>
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1 class="page-title">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </h1>
                    <p class="page-subtitle">Real-time overview of your system</p>
                </div>
                <div class="page-header-right">
                    <div class="welcome-text">
                        <p class="welcome-label">Welcome back</p>
                        <p class="welcome-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                        <p class="welcome-role"><?php echo htmlspecialchars($role); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards - Core Operations -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-chart-bar"></i> Key Metrics
                    </h2>
                    <p class="section-subtitle">Real-time system statistics and status</p>
                </div>
                <div class="dashboard-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Total Committees</div>
                            <div class="stat-value"><?php echo $stats['committees']; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> Active
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Upcoming Meetings</div>
                            <div class="stat-value"><?php echo $stats['upcoming_meetings']; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> This month
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Pending Documents</div>
                            <div class="stat-value"><?php echo $stats['pending_documents']; ?></div>
                            <div class="stat-change">
                                <i class="fas fa-clock"></i> Awaiting Review
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value"><?php echo $stats['active_users']; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> Online
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">My Tasks</div>
                            <div class="stat-value"><?php echo $stats['pending_tasks']; ?></div>
                            <div class="stat-change">
                                <i class="fas fa-hourglass"></i> Pending
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Main Content Row - Activities and Quick Actions -->
            <section class="dashboard-section">
                <div class="content-grid">
                    <!-- Recent Activities -->
                    <div class="card card-large">
                        <div class="card-header">
                            <div class="card-title-wrapper">
                                <h2 class="card-title">
                                    <i class="fas fa-history"></i> Recent Activities
                                </h2>
                                <p class="card-subtitle">Latest system activities</p>
                            </div>
                            <a href="logs/audit.php" class="btn btn-sm btn-secondary">View All</a>
                        </div>
                        <div class="card-content">
                            <div class="activities-list">
                                <?php if ($recentActivities && $recentActivities->num_rows > 0): ?>
                                    <?php while ($activity = $recentActivities->fetch_assoc()): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="activity-info">
                                                <p class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></p>
                                                <p class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($activity['timestamp'])); ?></span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No recent activities</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card card-actions">
                        <div class="card-header">
                            <div class="card-title-wrapper">
                                <h2 class="card-title">
                                    <i class="fas fa-bolt"></i> Quick Actions
                                </h2>
                                <p class="card-subtitle">Frequently used actions</p>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="actions-grid">
                                <a href="committees/create.php" class="action-btn btn-primary">
                                    <div class="action-icon">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    <div class="action-text">
                                        <span class="action-title">New Committee</span>
                                        <span class="action-desc">Create a new committee</span>
                                    </div>
                                </a>
                                <a href="meetings/schedule.php" class="action-btn btn-success">
                                    <div class="action-icon">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="action-text">
                                        <span class="action-title">Schedule Meeting</span>
                                        <span class="action-desc">Schedule a new meeting</span>
                                    </div>
                                </a>
                                <a href="documents/create.php" class="action-btn btn-warning">
                                    <div class="action-icon">
                                        <i class="fas fa-file-circle-plus"></i>
                                    </div>
                                    <div class="action-text">
                                        <span class="action-title">New Document</span>
                                        <span class="action-desc">Upload a document</span>
                                    </div>
                                </a>
                                <a href="tasks/create.php" class="action-btn btn-info">
                                    <div class="action-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="action-text">
                                        <span class="action-title">Assign Task</span>
                                        <span class="action-desc">Create a new task</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Monitoring & Statistics Section -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2>Monitoring & Statistics</h2>
                    <p>System overview and performance metrics</p>
                </div>

                <!-- Charts Container -->
                <div class="charts-container">
                    <!-- Document Status Chart -->
                    <div class="chart-card">
                        <h3>Document Status Distribution</h3>
                        <canvas id="docStatusChart"></canvas>
                        <div class="chart-legend">
                            <span><i class="fas fa-circle" style="color: #3498db;"></i> Draft</span>
                            <span><i class="fas fa-circle" style="color: #f39c12;"></i> In Committee</span>
                            <span><i class="fas fa-circle" style="color: #2ecc71;"></i> Approved</span>
                            <span><i class="fas fa-circle" style="color: #e74c3c;"></i> Rejected</span>
                        </div>
                    </div>

                    <!-- Meeting Trends Chart -->
                    <div class="chart-card">
                        <h3>Monthly Meeting Trends</h3>
                        <canvas id="meetingTrendsChart"></canvas>
                        <p class="chart-subtitle">Meetings scheduled by month</p>
                    </div>

                    <!-- Referral Status Chart -->
                    <div class="chart-card">
                        <h3>Referral Overview</h3>
                        <canvas id="referralChart"></canvas>
                        <div class="chart-legend">
                            <span><i class="fas fa-circle" style="color: #9b59b6;"></i> Incoming</span>
                            <span><i class="fas fa-circle" style="color: #1abc9c;"></i> Outgoing</span>
                            <span><i class="fas fa-circle" style="color: #e67e22;"></i> Pending</span>
                        </div>
                    </div>

                    <!-- Task Completion Chart -->
                    <div class="chart-card">
                        <h3>Task Status Summary</h3>
                        <canvas id="taskStatusChart"></canvas>
                        <div class="chart-legend">
                            <span><i class="fas fa-circle" style="color: #27ae60;"></i> Completed</span>
                            <span><i class="fas fa-circle" style="color: #f39c12;"></i> In Progress</span>
                            <span><i class="fas fa-circle" style="color: #95a5a6;"></i> Pending</span>
                        </div>
                    </div>
                </div>
            </section>
            </section>
        </main>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script src="../../public/assets/js/main.js"></script>
    <script>
        // Initialize sidebar - start visible
        function initSidebar() {
            const sidebar = document.querySelector('.sidebar');
            // Always start visible on page load
            sidebar.classList.add('active');
            sidebar.style.transform = 'translateX(0)';
            setActiveLink();
            console.log('Sidebar initialized - visible');
        }
        
        // Initialize theme from localStorage
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            updateThemeIcon(theme);
        }
        
        function updateThemeIcon(theme) {
            const icon = document.querySelector('#themeToggleBtn i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
        
        // Set active link based on current page
        function setActiveLink() {
            const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
            
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
                const href = link.getAttribute('href');
                
                if (href && href.includes(currentPage)) {
                    link.classList.add('active');
                    
                    // Expand parent menu if submenu item is active
                    const submenu = link.closest('.sidebar-submenu');
                    if (submenu) {
                        submenu.classList.add('active');
                        const parent = submenu.previousElementSibling;
                        if (parent) {
                            parent.classList.add('collapsed');
                        }
                    }
                }
            });
        }
        
        // Theme toggle functionality
        document.getElementById('themeToggleBtn').addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        // Initialize theme on page load
        initTheme();
        initSidebar();
        
        // User dropdown menu toggle
        const userInfoBtn = document.getElementById('userInfoBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userInfoBtn) {
            userInfoBtn.addEventListener('click', () => {
                userDropdownMenu.classList.toggle('active');
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-dropdown')) {
                userDropdownMenu.classList.remove('active');
            }
        });
        
        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                
                fetch('../../app/controllers/AuthController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../../auth/login.php';
                    }
                })
                .catch(error => console.error('Logout error:', error));
            }
        });
        
        // ============================================================================
        // SIDEBAR DROPDOWN TOGGLE FUNCTIONALITY
        // ============================================================================
        const sidebarLinks = document.querySelectorAll('.sidebar-link[data-toggle]');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const submenu = this.nextElementSibling;
                const isActive = submenu?.classList.contains('active');
                
                // Close all other submenus
                document.querySelectorAll('.sidebar-submenu').forEach(menu => {
                    menu.classList.remove('active');
                });
                document.querySelectorAll('.sidebar-link[data-toggle]').forEach(l => {
                    l.classList.remove('collapsed');
                });
                
                // Toggle current submenu
                if (submenu && !isActive) {
                    submenu.classList.add('active');
                    this.classList.add('collapsed');
                }
            });
        });
        
        // ============================================================================
        // HAMBURGER BUTTON TOGGLE - WITH ACTIVE STATE
        // ============================================================================
        const hamburgerBtn = document.querySelector('.hamburger-btn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.overlay');
        
        if (hamburgerBtn && sidebar) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (sidebar.classList.contains('active')) {
                    // Hide sidebar
                    sidebar.classList.remove('active');
                    sidebar.style.transform = 'translateX(-100%)';
                    hamburgerBtn.classList.remove('active');
                    if (overlay) overlay.style.display = 'none';
                } else {
                    // Show sidebar
                    sidebar.classList.add('active');
                    sidebar.style.transform = 'translateX(0)';
                    hamburgerBtn.classList.add('active');
                    if (overlay) overlay.style.display = 'block';
                }
                
                return false;
            });
        }
        
        // Close sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                if (sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    sidebar.style.transform = 'translateX(-100%)';
                    hamburgerBtn.classList.remove('active');
                    overlay.style.display = 'none';
                }
            });
        }
        
        // Close sidebar on small screen when link clicked
        document.querySelectorAll('.sidebar-submenu-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('active');
                    sidebar.style.transform = 'translateX(-100%)';
                    hamburgerBtn.classList.remove('active');
                    if (overlay) overlay.style.display = 'none';
                }
            });
        });
        
        // ============================================================================
        // CHART.JS INITIALIZATION
        // ============================================================================
        const isDarkMode = localStorage.getItem('theme') === 'dark';
        const chartTextColor = isDarkMode ? '#e5e7eb' : '#374151';
        const chartGridColor = isDarkMode ? '#374151' : '#e5e7eb';
        
        // Document Status Chart
        const docStatusCtx = document.getElementById('docStatusChart');
        if (docStatusCtx) {
            new Chart(docStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'In Committee', 'Approved', 'Rejected'],
                    datasets: [{
                        data: [<?php echo $doc_status['draft'] . ', ' . $doc_status['in_committee'] . ', ' . $doc_status['approved'] . ', ' . $doc_status['rejected']; ?>],
                        backgroundColor: ['#3498db', '#f39c12', '#2ecc71', '#e74c3c'],
                        borderColor: isDarkMode ? '#1f2937' : '#ffffff',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Meeting Trends Chart
        const meetingTrendsCtx = document.getElementById('meetingTrendsChart');
        if (meetingTrendsCtx) {
            new Chart(meetingTrendsCtx, {
                type: 'line',
                data: {
                    labels: [<?php echo "'" . implode("', '", array_keys($monthly_meetings)) . "'"; ?>],
                    datasets: [{
                        label: 'Meetings',
                        data: [<?php echo implode(', ', array_values($monthly_meetings)); ?>],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: isDarkMode ? '#1f2937' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: chartGridColor,
                                drawBorder: false
                            },
                            ticks: {
                                color: chartTextColor
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: chartTextColor
                            }
                        }
                    }
                }
            });
        }
        
        // Referral Status Chart
        const referralCtx = document.getElementById('referralChart');
        if (referralCtx) {
            new Chart(referralCtx, {
                type: 'bar',
                data: {
                    labels: ['Incoming', 'Outgoing', 'Pending'],
                    datasets: [{
                        label: 'Referrals',
                        data: [<?php echo $referral_stats['incoming'] . ', ' . $referral_stats['outgoing'] . ', ' . $referral_stats['pending']; ?>],
                        backgroundColor: ['#9b59b6', '#1abc9c', '#e67e22'],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: chartGridColor,
                                drawBorder: false
                            },
                            ticks: {
                                color: chartTextColor
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: chartTextColor
                            }
                        }
                    }
                }
            });
        }
        
        // Task Status Chart
        const taskStatusCtx = document.getElementById('taskStatusChart');
        if (taskStatusCtx) {
            new Chart(taskStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending'],
                    datasets: [{
                        data: [<?php echo $task_stats['completed'] . ', ' . $task_stats['in_progress'] . ', ' . $task_stats['pending']; ?>],
                        backgroundColor: ['#27ae60', '#f39c12', '#95a5a6'],
                        borderColor: isDarkMode ? '#1f2937' : '#ffffff',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
