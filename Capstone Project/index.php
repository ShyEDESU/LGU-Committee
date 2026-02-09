<?php
// Root index.php - Landing Page
require_once __DIR__ . '/config/session_config.php';
require_once __DIR__ . '/config/database.php';

// Helper Includes
require_once __DIR__ . '/app/helpers/MeetingHelper.php';
require_once __DIR__ . '/app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/app/helpers/DataHelper.php';
require_once __DIR__ . '/app/helpers/ReferralHelper.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: public/dashboard.php');
    exit();
}

// Safe Query Function
function getSafeCount($conn, $sql)
{
    try {
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_row()) {
            return (int) $row[0];
        }
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
    }
    return 0;
}

// Fetch Dynamic Stats
$totalOrdinances = getSafeCount($conn, "SELECT COUNT(*) FROM legislative_documents WHERE document_type = 'ordinance'");
$activeCommitteesCount = getSafeCount($conn, "SELECT COUNT(*) FROM committees WHERE is_active = 1");
$totalReferrals = getSafeCount($conn, "SELECT COUNT(*) FROM referrals");

// Additional stats for landing page dashboard
$scheduledMeetings = getSafeCount($conn, "SELECT COUNT(*) FROM meetings WHERE status = 'Scheduled'");
$totalDocuments = getSafeCount($conn, "SELECT COUNT(*) FROM legislative_documents");
$activeUsers = getSafeCount($conn, "SELECT COUNT(*) FROM users WHERE is_active = 1");

// Fetch Active/Upcoming Sessions
$liveSessions = function_exists('getAllMeetings') ? getAllMeetings(['status' => 'Ongoing', 'limit' => 3]) : [];
$upcomingSessions = function_exists('getAllMeetings') ? getAllMeetings(['status' => 'Scheduled', 'limit' => 3]) : [];

// Combined sessions for showcase
$featuredSessions = array_merge($liveSessions, $upcomingSessions);
if (count($featuredSessions) > 3) {
    $featuredSessions = array_slice($featuredSessions, 0, 3);
}

// Fetch Latest Ordinances
$latestOrdinances = [];
try {
    $res = $conn->query("SELECT * FROM legislative_documents WHERE document_type = 'ordinance' ORDER BY created_at DESC LIMIT 3");
    if ($res) {
        $latestOrdinances = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
}

// Fetch Committees with referral counts
$committees = [];
try {
    $res = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM referrals r WHERE r.to_committee_id = c.committee_id AND r.status = 'Pending') as pending_count FROM committees c WHERE c.is_active = 1 LIMIT 8");
    if ($res) {
        $committees = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legislative CMS | City of Valenzuela</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="public/assets/images/logo.png">
    <link rel="apple-touch-icon" href="public/assets/images/logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Tailwind Configuration - CRITICAL for dark mode toggle -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'v-navy': '#450a0a',
                        'v-red': '#D22B2B',
                        'v-gold': '#FFD700',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --v-red: #dc2626;
            --v-red-dark: #991b1b;
            --v-red-darker: #7f1d1d;
        }

        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark body {
            background-color: #0f172a;
            color: #f8fafc;
        }

        h1,
        h2,
        h3,
        h4,
        .brand {
            font-family: 'Outfit', sans-serif;
        }

        /* Nav Blur Fix for Safari */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .dark .glass {
            background: rgba(15, 23, 42, 0.8);
            border-bottom-color: rgba(51, 65, 85, 0.5);
        }

        .hero-pattern {
            background-image: radial-gradient(circle at 2px 2px, rgba(255, 255, 255, 0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .scroll-reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .scroll-reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        .dark .card-hover:hover {
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5);
        }
    </style>

    <script>
        // Initialize theme before page load
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-900 scroll-smooth selection:bg-red-500 selection:text-white">



    <!-- Navigation -->
    <nav class="sticky top-0 w-full z-50 glass border-b border-slate-200 dark:border-slate-800 transition-all duration-300"
        id="mainNav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20" id="navHeightContainer">
                <div class="flex items-center gap-3">
                    <img src="public/assets/images/logo.png" alt="Logo" class="h-14 w-auto">
                    <div class="hidden md:block">
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white leading-tight brand">CMS<span
                                class="text-v-red">VALENZUELA</span></span>
                        <span
                            class="block text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-[0.3em]">Committee
                            Management System</span>
                    </div>
                </div>
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#sessions"
                        class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-v-red dark:hover:text-red-400 transition-colors">Sessions</a>
                    <a href="#legislation"
                        class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-v-red dark:hover:text-red-400 transition-colors">Legislation</a>
                    <a href="#committees"
                        class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-v-red dark:hover:text-red-400 transition-colors">Committees</a>
                    <a href="#leaders"
                        class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-v-red dark:hover:text-red-400 transition-colors">Council</a>

                    <!-- Real-time Clock & Date -->
                    <div
                        class="hidden lg:flex flex-col items-end mr-4 pr-4 border-r border-slate-200 dark:border-slate-700">
                        <div
                            class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider real-time-date">
                            Loading...</div>
                        <div class="text-sm font-black text-slate-900 dark:text-white real-time-clock">00:00:00 AM</div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="theme-toggle"
                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700"
                        title="Toggle Theme">
                        <i class="bi bi-moon-fill dark:hidden"></i>
                        <i class="bi bi-sun-fill hidden dark:inline"></i>
                    </button>

                    <a href="auth/login.php"
                        class="bg-red-600 dark:bg-red-700 text-white px-8 py-3 rounded-full font-bold text-sm shadow-xl hover:bg-red-700 dark:hover:bg-red-800 transition-all transform hover:scale-105 active:scale-95">
                        <i class="bi bi-shield-lock-fill mr-2"></i>Staff Access
                    </a>
                </div>
                <div class="flex items-center gap-4 lg:hidden">
                    <button id="mobile-theme-toggle" class="p-2 text-slate-600 dark:text-slate-300">
                        <i class="bi bi-moon-fill dark:hidden"></i>
                        <i class="bi bi-sun-fill hidden dark:inline"></i>
                    </button>
                    <button class="text-slate-900 dark:text-white text-3xl">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-[85vh] flex items-center bg-gradient-to-br from-red-900 to-red-800 overflow-hidden">
        <div class="absolute inset-0">
            <img src="public/assets/images/legislative-building.jpg" alt="Valenzuela Legislative Building"
                class="w-full h-full object-cover opacity-30">
        </div>
        <div class="absolute inset-0 hero-pattern"></div>
        <!-- Decorative circular gradient -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-red-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-red-600/20 rounded-full blur-[120px]"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-center lg:text-left space-y-8">
                    <div
                        class="inline-flex items-center gap-3 bg-red-500/10 border border-red-500/20 px-4 py-2 rounded-full text-red-400 text-xs font-black tracking-widest uppercase">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Live Legislative Portal
                    </div>
                    <h1 class="text-5xl sm:text-7xl font-black text-white leading-[1.1] tracking-tight">
                        Empowering <br><span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-orange-400">Local
                            Legislation</span>.
                    </h1>
                    <p class="text-xl text-slate-300 max-w-2xl font-medium leading-relaxed">
                        Access and track committee deliberations, ordinances, and resolutions. Modernizing how the City
                        of Valenzuela serves its people through transparent digital governance.
                    </p>

                    <!-- Legislative Search Bar -->
                    <div class="relative max-w-xl mx-auto lg:mx-0 group">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                            <i
                                class="bi bi-search text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                        </div>
                        <input type="text" placeholder="Search Ordinances, Resolutions, or Keywords..."
                            class="w-full bg-slate-800/50 border-2 border-slate-700 text-white rounded-2xl py-5 pl-14 pr-32 focus:outline-none focus:border-red-500 transition-all font-medium placeholder:text-slate-500 text-lg">
                        <button
                            class="absolute right-3 top-2.5 bottom-2.5 bg-red-600 text-white px-6 rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg active:scale-95">
                            Search
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 pt-4">
                        <div class="flex -space-x-4">
                            <!-- Placeholder Council Members -->
                            <div class="w-12 h-12 rounded-full border-4 border-v-navy bg-slate-700 flex items-center justify-center text-xs text-white"
                                title="Councilor 1">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="w-12 h-12 rounded-full border-4 border-v-navy bg-slate-600 flex items-center justify-center text-xs text-white"
                                title="Councilor 2">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="w-12 h-12 rounded-full border-4 border-v-navy bg-slate-500 flex items-center justify-center text-xs text-white"
                                title="Councilor 3">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <span class="text-slate-400 text-sm font-bold uppercase tracking-widest hidden sm:block">Meet
                            the 8th City Council</span>
                    </div>
                </div>

                <!-- Committee Activity Card -->
                <div class="hidden lg:block relative scroll-reveal">
                    <div
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-[2.5rem] p-8 shadow-2xl overflow-hidden group">
                        <div class="absolute top-0 right-0 p-10 opacity-10 text-9xl">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="relative z-10 space-y-6">
                            <div class="flex justify-between items-start">
                                <div class="p-4 bg-red-600 rounded-2xl text-white text-3xl">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <span
                                    class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest">System
                                    Overview</span>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-white">Committee Management</h3>
                                <p class="text-slate-400 font-bold uppercase text-xs tracking-widest mt-1">8th City
                                    Council of Valenzuela</p>
                            </div>
                            <div class="space-y-4 pt-4 border-t border-slate-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                        <span class="text-slate-300 text-sm font-medium">Active Committees</span>
                                    </div>
                                    <span
                                        class="text-white font-black text-lg"><?php echo $activeCommitteesCount; ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                                        <span class="text-slate-300 text-sm font-medium">Scheduled Meetings</span>
                                    </div>
                                    <span class="text-white font-black text-lg"><?php echo $scheduledMeetings; ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                        <span class="text-slate-300 text-sm font-medium">Published Documents</span>
                                    </div>
                                    <span class="text-white font-black text-lg"><?php echo $totalDocuments; ?></span>
                                </div>
                            </div>
                            <a href="#sessions"
                                class="block w-full py-4 text-center bg-white/5 hover:bg-white/10 border border-slate-600 rounded-xl text-white font-black uppercase text-sm tracking-widest transition-all group-hover:border-red-500">Explore
                                System</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- System Statistics Dashboard -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Active Committees -->
            <div
                class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-8 rounded-2xl text-center shadow-xl border border-red-100 dark:border-red-900/30 card-hover">
                <div class="text-5xl font-black text-red-600 dark:text-red-500 mb-2">
                    <?php echo $activeCommitteesCount; ?>
                </div>
                <div class="text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Active
                    Committees</div>
            </div>

            <!-- Scheduled Meetings -->
            <div
                class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-8 rounded-2xl text-center shadow-xl border border-orange-100 dark:border-orange-900/30 card-hover">
                <div class="text-5xl font-black text-orange-600 dark:text-orange-500 mb-2">
                    <?php echo $scheduledMeetings; ?>
                </div>
                <div class="text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Scheduled
                    Meetings</div>
            </div>

            <!-- Published Documents -->
            <div
                class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-8 rounded-2xl text-center shadow-xl border border-green-100 dark:border-green-900/30 card-hover">
                <div class="text-5xl font-black text-green-600 dark:text-green-500 mb-2"><?php echo $totalDocuments; ?>
                </div>
                <div class="text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Published
                    Documents</div>
            </div>

            <!-- Active Users -->
            <div
                class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-8 rounded-2xl text-center shadow-xl border border-purple-100 dark:border-purple-900/30 card-hover">
                <div class="text-5xl font-black text-purple-600 dark:text-purple-500 mb-2"><?php echo $activeUsers; ?>
                </div>
                <div class="text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">System Users
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions Section -->
    <section id="sessions" class="py-24 bg-white dark:bg-slate-900 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row justify-between items-end mb-16 gap-8 scroll-reveal">
                <div class="max-w-2xl space-y-4">
                    <h2 class="text-v-red font-black tracking-[0.3em] uppercase text-xs">Live Monitoring</h2>
                    <h3 class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white leading-tight">Ongoing
                        Committee <span
                            class="bg-v-navy dark:bg-red-600 text-white px-3 py-1 -rotate-2 inline-block">Sessions</span>
                    </h3>
                </div>
                <div class="pb-2">
                    <a href="public/pages/committee-meetings/index.php"
                        class="group text-slate-900 dark:text-slate-300 font-extrabold flex items-center gap-2 hover:text-v-red dark:hover:text-red-400 transition-all">
                        Complete Session Archive <i
                            class="bi bi-arrow-right-short text-2xl group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($featuredSessions)): ?>
                    <div
                        class="col-span-full py-20 text-center bg-slate-50 dark:bg-slate-800/50 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-700">
                        <i class="bi bi-calendar-x text-5xl text-slate-300 dark:text-slate-600 mb-4 block"></i>
                        <p class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">No sessions
                            scheduled for today</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredSessions as $session): ?>
                        <div
                            class="bg-slate-50 dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 overflow-hidden card-hover group scroll-reveal">
                            <div class="h-48 bg-slate-200 dark:bg-slate-700 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <?php if ($session['status'] === 'Ongoing'): ?>
                                    <div
                                        class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> IN SESSION
                                    </div>
                                <?php else: ?>
                                    <div
                                        class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                        UPCOMING</div>
                                <?php endif; ?>
                                <div
                                    class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500 group-hover:scale-110 transition-transform duration-700">
                                    <i class="bi bi-bank text-4xl"></i>
                                </div>
                            </div>
                            <div class="p-8 space-y-4">
                                <span
                                    class="text-v-red font-bold text-[10px] uppercase tracking-widest"><?php echo htmlspecialchars($session['committee_name']); ?></span>
                                <h4 class="text-xl font-bold text-slate-900 dark:text-white">
                                    <?php echo htmlspecialchars($session['title']); ?>
                                </h4>
                                <div class="flex items-center gap-4 text-xs font-bold text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-1"><i class="bi bi-clock"></i>
                                        <?php echo date('g:i A', strtotime($session['time_start'])); ?></span>
                                    <span class="flex items-center gap-1"><i class="bi bi-geo-alt"></i>
                                        <?php echo htmlspecialchars($session['venue']); ?></span>
                                </div>
                                <a href="public/pages/committee-meetings/view.php?id=<?php echo $session['id']; ?>"
                                    class="block text-center py-4 bg-v-navy dark:bg-slate-700 text-white rounded-xl font-bold hover:bg-v-red dark:hover:bg-red-600 transition-all">
                                    <?php echo ($session['status'] === 'Ongoing') ? 'Monitor Deliberation' : 'View Agenda'; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Legislation Showcase -->
    <section id="legislation" class="py-24 bg-v-navy text-white overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-white to-transparent opacity-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-5 space-y-8 scroll-reveal">
                    <h2 class="text-red-500 font-black tracking-[0.3em] uppercase text-xs">Policy Index</h2>
                    <h3 class="text-5xl font-black leading-tight">Official <br><span
                            class="text-red-500 underline decoration-slate-700 underline-offset-8">Ordinance</span>
                        Repository.</h3>
                    <p class="text-slate-400 text-lg font-medium leading-relaxed">
                        Access the complete digital library of laws passed by the City Council. Filter by sector,
                        author, or effectiveness date.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-6 bg-white/5 rounded-3xl border border-white/10 card-hover">
                            <i class="bi bi-book-half text-red-500 text-2xl mb-4 block"></i>
                            <h5 class="font-bold text-white mb-1">Administrative</h5>
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">
                                <?php echo number_format($totalOrdinances); ?> Records
                            </p>
                        </div>
                        <div class="p-6 bg-white/5 rounded-3xl border border-white/10 card-hover">
                            <i class="bi bi-heart-pulse text-red-500 text-2xl mb-4 block"></i>
                            <h5 class="font-bold text-white mb-1">Health & Social</h5>
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Active Monitoring
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 scroll-reveal">
                    <div
                        class="bg-white dark:bg-slate-800 rounded-[3rem] p-4 sm:p-10 text-slate-900 dark:text-white shadow-2xl">
                        <div class="flex justify-between items-center mb-8">
                            <h4 class="text-xl font-black uppercase tracking-tight">Latest Published Acts</h4>
                            <span class="text-slate-400 dark:text-slate-500 text-sm font-bold"><?php echo date('Y'); ?>
                                Series</span>
                        </div>
                        <div class="space-y-4">
                            <?php if (empty($latestOrdinances)): ?>
                                <p class="text-center py-10 text-slate-400 italic">No legislation records found.</p>
                            <?php else: ?>
                                <?php foreach ($latestOrdinances as $ord): ?>
                                    <div
                                        class="p-6 bg-slate-50 dark:bg-slate-900/50 rounded-2xl flex flex-col sm:flex-row justify-between gap-4 border border-slate-100 dark:border-slate-700 hover:border-red-500 transition-all card-hover group">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="bg-red-600 text-white text-[9px] font-black px-2 py-0.5 rounded"><?php echo htmlspecialchars($ord['document_number']); ?></span>
                                                <span
                                                    class="text-slate-400 dark:text-slate-500 text-xs font-bold"><?php echo date('M j, Y', strtotime($ord['created_at'])); ?></span>
                                            </div>
                                            <h5
                                                class="font-black text-slate-900 dark:text-white text-lg group-hover:text-v-red transition-colors">
                                                <?php echo htmlspecialchars($ord['title']); ?>
                                            </h5>
                                            <p class="text-slate-500 dark:text-slate-400 text-sm">
                                                <?php echo htmlspecialchars(substr($ord['description'], 0, 80)) . '...'; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="public/pages/legislative-tracker/index.php"
                            class="block w-full mt-8 py-4 text-center font-black uppercase text-xs tracking-widest text-slate-400 hover:text-v-red transition-colors">
                            Explore full digital repository <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Committees Grid -->
    <section id="committees" class="py-24 bg-slate-50 dark:bg-slate-900 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto mb-20 scroll-reveal">
                <h2 class="text-v-red font-black tracking-[0.3em] uppercase text-xs mb-4">Organizational Structure</h2>
                <h3 class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white mb-8">Guided by Statutory
                    <br><span class="text-red-500">Expertise</span>.
                </h3>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 scroll-reveal">
                <?php foreach ($committees as $committee): ?>
                    <div
                        class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-700 card-hover group">
                        <div
                            class="w-14 h-14 bg-red-50 dark:bg-red-900/20 text-v-red rounded-2xl flex items-center justify-center text-2xl mx-auto mb-6 group-hover:bg-v-red group-hover:text-white transition-all">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <h4 class="font-black text-slate-900 dark:text-white mb-2">
                            <?php echo htmlspecialchars($committee['committee_name']); ?>
                        </h4>
                        <span
                            class="text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-widest"><?php echo $committee['pending_count']; ?>
                            Active Referrals</span>
                    </div>
                <?php endforeach; ?>

                <div
                    class="bg-v-navy dark:bg-slate-800 text-white p-8 rounded-[2.5rem] shadow-xl flex flex-col justify-center card-hover overflow-hidden relative border border-transparent dark:border-red-600/30">
                    <div class="absolute -right-4 -bottom-4 opacity-10 text-8xl"><i class="bi bi-diagram-3"></i></div>
                    <h4 class="font-black mb-4">Explore All 24 Committees</h4>
                    <a href="public/pages/committee-profiles/index.php"
                        class="text-xs font-black uppercase tracking-widest text-red-500 hover:text-white transition-colors">Current
                        Directory <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Leadership - Real Portraits Placeholder -->
    <section id="leaders" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-16 scroll-reveal">
                <h2 class="text-v-navy font-black tracking-[0.3em] uppercase text-xs mb-4">8th City Council</h2>
                <h3 class="text-4xl sm:text-5xl font-black text-slate-900 leading-tight">Leadership Built on <span
                        class="text-v-red">Trust</span>.</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
                <!-- Mayor -->
                <div class="group scroll-reveal">
                    <div
                        class="relative aspect-[4/5] rounded-[3rem] overflow-hidden bg-slate-100 dark:bg-slate-800 shadow-2xl mb-8">
                        <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-700">
                            <i class="bi bi-person-fill text-slate-400 text-8xl"></i>
                        </div>
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-v-navy/90 via-v-navy/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex flex-col justify-end p-10">
                            <p class="text-slate-300 text-sm font-medium italic">"Tuloy ang Progreso para sa Pamilyang
                                Valenzuelano."</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-2xl font-black text-slate-900">Wes Gatchalian</h4>
                        <p class="text-v-red font-black text-xs uppercase tracking-widest">City Mayor</p>
                    </div>
                </div>

                <!-- Vice Mayor -->
                <div class="group scroll-reveal" style="transition-delay: 100ms;">
                    <div
                        class="relative aspect-[4/5] rounded-[3rem] overflow-hidden bg-slate-100 dark:bg-slate-800 shadow-2xl mb-8 border-4 border-v-red/20 scale-105">
                        <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-700">
                            <i class="bi bi-person-fill text-slate-400 text-8xl"></i>
                        </div>
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-v-red/90 via-v-red/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex flex-col justify-end p-10">
                            <p class="text-white text-sm font-medium italic">"Presiding Officer, 8th City Council."</p>
                        </div>
                        <div
                            class="absolute top-6 left-6 bg-v-red text-white text-[10px] font-black px-4 py-2 rounded-full uppercase tracking-widest shadow-lg">
                            Presiding Officer</div>
                    </div>
                    <div class="space-y-1 text-center lg:text-left mt-4 lg:mt-0">
                        <h4 class="text-2xl font-black text-slate-900">Lorie Natividad-Borja</h4>
                        <p class="text-v-red font-black text-xs uppercase tracking-widest">City Vice Mayor</p>
                    </div>
                </div>

                <!-- Council Info -->
                <div class="flex flex-col justify-center space-y-10 scroll-reveal" style="transition-delay: 200ms;">
                    <div class="space-y-4">
                        <h5 class="text-v-navy font-black text-2xl">Council Directory</h5>
                        <p class="text-slate-500 font-medium">Get to know the district representatives and sectoral
                            leaders of Valenzuela City.</p>
                    </div>
                    <div class="space-y-6">
                        <a href="#"
                            class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-200 hover:border-v-red transition-all group">
                            <span class="font-black text-slate-900">District 1 Councilors</span>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:text-v-red"></i>
                        </a>
                        <a href="#"
                            class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-200 hover:border-v-red transition-all group">
                            <span class="font-black text-slate-900">District 2 Councilors</span>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:text-v-red"></i>
                        </a>
                        <a href="#"
                            class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-200 hover:border-v-red transition-all group">
                            <span class="font-black text-slate-900">Sectoral Representatives</span>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:text-v-red"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Milestones -->
    <section class="py-24 bg-slate-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-20 scroll-reveal">
                <h2 class="text-v-red font-black tracking-[0.3em] uppercase text-xs mb-4">Impact Tracker</h2>
                <h3 class="text-4xl sm:text-5xl font-black text-slate-900">Legislative <br><span
                        class="text-v-navy underline decoration-red-500 underline-offset-8">Milestones</span>.</h3>
            </div>

            <div class="grid md:grid-cols-2 gap-12">
                <div class="group scroll-reveal">
                    <div
                        class="aspect-video bg-slate-200 dark:bg-slate-800 rounded-[3rem] overflow-hidden mb-8 shadow-2xl relative group-hover:scale-[1.02] transition-transform duration-700">
                        <img src="public/assets/images/peoples-park.jpg" alt="Valenzuela People's Park"
                            class="w-full h-full object-cover">
                        <div
                            class="absolute top-8 left-8 bg-black/40 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-white/20">
                            Signature Project</div>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 mb-4">VCPL People's Park Initiative</h4>
                    <p class="text-slate-500 leading-relaxed mb-6 font-medium">Passed through the Environment Committee,
                        this ordinance transformed 1.3 hectares of land into a premium public space, enhancing urban
                        quality of life.</p>
                    <a href="#"
                        class="text-v-red font-black flex items-center gap-2 group-hover:gap-4 transition-all">Impact
                        Documentation <i class="bi bi-chevron-right"></i></a>
                </div>

                <div class="group scroll-reveal" style="transition-delay: 150ms;">
                    <div
                        class="aspect-video bg-slate-200 dark:bg-slate-800 rounded-[3rem] overflow-hidden mb-8 shadow-2xl relative group-hover:scale-[1.02] transition-transform duration-700">
                        <img src="public/assets/images/wes-arena.jpg" alt="WES Arena"
                            class="w-full h-full object-cover">
                        <div
                            class="absolute top-8 left-8 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest px-5 py-2 rounded-full shadow-lg">
                            Social Impact</div>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 mb-4">WES Arena Sports Complex</h4>
                    <p class="text-slate-500 leading-relaxed mb-6 font-medium">The landmark sports facility of the 8th
                        City Council, providing world-class training grounds and community spaces for the people of
                        Valenzuela.</p>
                    <a href="#"
                        class="text-v-red font-black flex items-center gap-2 group-hover:gap-4 transition-all">Project
                        Case Study <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'public/includes/footer.php'; ?>

    <!-- Template Scripts -->
    <script src="assets/js/script-updated.js"></script>

    <script>
        // Scroll Reveal Implementation
        const observerOptions = {
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));

        // Sticky Nav Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            const navContainer = document.getElementById('navHeightContainer');
            if (window.scrollY > 80) {
                nav.classList.add('shadow-2xl');
                if (navContainer) {
                    navContainer.classList.add('h-16');
                    navContainer.classList.remove('h-20');
                }
            } else {
                nav.classList.remove('shadow-2xl');
                if (navContainer) {
                    navContainer.classList.add('h-20');
                    navContainer.classList.remove('h-16');
                }
            }
    </script>

    <!-- System Scripts -->
    <script src="public/assets/js/script-updated.js"></script>
</body>

</html>