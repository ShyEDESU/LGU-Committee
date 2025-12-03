<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Management System - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased transition-colors duration-300">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 bg-gradient-to-b from-cms-red to-cms-dark text-white fixed md:relative h-full z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 overflow-y-auto">
            <!-- Logo Section -->
            <div class="p-6 border-b border-red-700 sticky top-0 bg-gradient-to-b from-cms-red to-cms-dark">
                <a href="#" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300">
                    <div class="bg-white rounded-lg shadow-md p-2 w-12 h-12 flex items-center justify-center">
                        <img src="assets/images/logo.png" alt="Logo" class="w-full h-full object-contain rounded-md">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">CMS</h1>
                        <p class="text-xs text-red-200">Committee System</p>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- 3.1: Committee Structure & Configuration -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-building mr-2"></i>Committee Structure</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/committee-structure/index.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">All Committees</a>
                        <a href="pages/committee-structure/create.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Create Committee</a>
                        <a href="pages/committee-structure/types.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Committee Types</a>
                        <a href="pages/committee-structure/charter.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Charter & Rules</a>
                        <a href="pages/committee-structure/contact.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Contact Information</a>
                    </div>
                </div>

                <!-- 3.2: Member Assignment & Roles -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-users mr-2"></i>Member Assignment</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/member-assignment/directory.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Member Directory</a>
                        <a href="pages/member-assignment/assign.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Assign to Committee</a>
                        <a href="pages/member-assignment/roles.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Member Roles</a>
                        <a href="pages/member-assignment/history.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Membership History</a>
                        <a href="pages/member-assignment/substitutes.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Substitute Management</a>
                    </div>
                </div>

                <!-- 3.3: Committee Referral Management -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-inbox mr-2"></i>Referrals</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/referral-management/inbox.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Referral Inbox</a>
                        <a href="pages/referral-management/incoming.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Incoming Referrals</a>
                        <a href="pages/referral-management/multi.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Multi-Committee</a>
                        <a href="pages/referral-management/deadlines.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Deadlines & Alerts</a>
                        <a href="pages/referral-management/acknowledgment.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Acknowledgments</a>
                    </div>
                </div>

                <!-- 3.4: Committee Meeting Scheduler -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-calendar-alt mr-2"></i>Meetings</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/meeting-scheduler/view.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">View Meetings</a>
                        <a href="pages/meeting-scheduler/schedule.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Schedule Meeting</a>
                        <a href="pages/meeting-scheduler/calendar.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Calendar View</a>
                        <a href="pages/meeting-scheduler/rooms.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Room Booking</a>
                        <a href="pages/meeting-scheduler/recurring.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Recurring Meetings</a>
                        <a href="pages/meeting-scheduler/quorum.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Quorum Settings</a>
                    </div>
                </div>

                <!-- 3.5: Committee Agenda Builder -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-list-check mr-2"></i>Agendas</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/agenda-builder/create.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Create Agenda</a>
                        <a href="pages/agenda-builder/items.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Agenda Items</a>
                        <a href="pages/agenda-builder/templates.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Templates</a>
                        <a href="pages/agenda-builder/distribution.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Distribution</a>
                        <a href="pages/agenda-builder/timing.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Time Allocation</a>
                    </div>
                </div>

                <!-- 3.6: Committee Deliberation Tools -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-comments mr-2"></i>Deliberation</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/deliberation-tools/discussions.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Discussion Threads</a>
                        <a href="pages/deliberation-tools/amendments.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Amendment Proposals</a>
                        <a href="pages/deliberation-tools/positions.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Member Positions</a>
                        <a href="pages/deliberation-tools/voting.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Committee Voting</a>
                        <a href="pages/deliberation-tools/history.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Deliberation History</a>
                    </div>
                </div>

                <!-- 3.7: Action Item Tracking -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-tasks mr-2"></i>Action Items</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/action-items/all.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">All Items</a>
                        <a href="pages/action-items/assigned.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">My Assignments</a>
                        <a href="pages/action-items/overdue.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Overdue Items</a>
                    </div>
                </div>

                <!-- 3.8: Committee Report Generation -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-file-pdf mr-2"></i>Reports</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/report-generation/generate.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Generate Report</a>
                        <a href="pages/report-generation/templates.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Report Templates</a>
                        <a href="pages/report-generation/recommendations.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Recommendations</a>
                        <a href="pages/report-generation/minority.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Minority Reports</a>
                        <a href="pages/report-generation/approval.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Approval Workflow</a>
                    </div>
                </div>

                <!-- 3.9: Inter-Committee Communication -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-network-wired mr-2"></i>Coordination</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/inter-committee/joint.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Joint Committees</a>
                        <a href="pages/inter-committee/board.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Message Board</a>
                        <a href="pages/inter-committee/sharing.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Document Sharing</a>
                        <a href="pages/inter-committee/hearings.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Joint Hearings</a>
                    </div>
                </div>

                <!-- 3.10: Research Support Integration -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-book mr-2"></i>Research & Support</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/research-support/request.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Request Research</a>
                        <a href="pages/research-support/briefs.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Policy Briefs</a>
                        <a href="pages/research-support/legal.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Legal Analysis</a>
                        <a href="pages/research-support/comparative.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Comparative Legislation</a>
                        <a href="pages/research-support/findings.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Research Findings</a>
                    </div>
                </div>

                <!-- 3.11: User Management (Admin Only) -->
                <div class="space-y-1">
                    <button onclick="toggleModule(this)" class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-between font-semibold text-sm">
                        <span><i class="fas fa-users-cog mr-2"></i>User Management</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden submenu pl-4 space-y-1">
                        <a href="pages/user-management/all-users.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">All Users</a>
                        <a href="pages/user-management/create-user.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Create Account</a>
                        <a href="pages/user-management/roles.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">User Roles</a>
                        <a href="pages/user-management/permissions.php" class="block px-4 py-2 text-sm text-red-100 hover:text-white hover:bg-red-700 rounded transition-all">Permissions</a>
                    </div>
                </div>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-red-700">
                <div class="flex items-center space-x-3 px-2 py-2">
                    <div class="bg-red-600 rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold">A</div>
                    <div class="flex-1 text-sm">
                        <p class="font-semibold">Admin User</p>
                        <p class="text-xs text-red-200">Active</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-20">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Hamburger Menu & Logo -->
                    <div class="flex items-center space-x-4">
                        <button onclick="toggleSidebar()" class="md:hidden text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                        <div class="hidden md:block">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Committee Management System</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">City Government of Valenzuela</p>
                        </div>
                    </div>

                    <!-- Header Right -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 relative transition">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                            </button>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Toggle dark mode">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block"></i>
                        </button>

                        <!-- User Profile Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <img src="assets/images/logo.png" alt="Profile" class="w-10 h-10 rounded-full bg-cms-red p-1 object-cover">
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white">Admin User</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                            </button>

                            <!-- Profile Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <!-- Profile Header -->
                                <div class="bg-gradient-to-r from-cms-red to-cms-dark p-4 rounded-t-lg">
                                    <div class="flex items-center space-x-3">
                                        <img src="assets/images/logo.png" alt="Profile" class="w-16 h-16 rounded-full bg-white p-1 object-cover">
                                        <div class="text-white">
                                            <p class="font-bold text-lg">Admin User</p>
                                            <p class="text-sm text-red-100">Administrator</p>
                                            <p class="text-xs text-red-200 mt-1">admin@example.com</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Options -->
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-user-circle text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">View Profile</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">See your profile details</p>
                                        </div>
                                    </a>
                                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-edit text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">Edit Profile</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Update your information</p>
                                        </div>
                                    </a>
                                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-lock text-cms-red"></i>
                                        <div>
                                            <p class="font-semibold text-sm">Change Password</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Update your password</p>
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
                <div class="bg-gradient-to-r from-cms-red to-cms-dark text-white rounded-lg p-8 shadow-lg">
                    <h1 class="text-3xl font-bold mb-2">Welcome to Committee Management System</h1>
                    <p class="text-red-100">Manage committees, meetings, and legislative processes efficiently</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-cms-red">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold">Active Committees</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">12</p>
                            </div>
                            <i class="fas fa-building text-cms-red text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold">Pending Referrals</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">8</p>
                            </div>
                            <i class="fas fa-inbox text-blue-500 text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold">Upcoming Meetings</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">5</p>
                            </div>
                            <i class="fas fa-calendar-alt text-green-500 text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold">Action Items</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">14</p>
                            </div>
                            <i class="fas fa-tasks text-yellow-500 text-4xl opacity-20"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-cms-red bg-opacity-10 rounded-full p-3">
                                        <i class="fas fa-file-alt text-cms-red"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white">New referral received</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded">New</span>
                            </div>
                            <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-3">
                                        <i class="fas fa-calendar text-blue-600 dark:text-blue-300"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white">Meeting scheduled</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">1 day ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Meetings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Upcoming Meetings</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-cms-red">
                                <p class="font-semibold text-sm text-gray-800 dark:text-white">Finance Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 5, 2025 at 2:00 PM</p>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-green-500">
                                <p class="font-semibold text-sm text-gray-800 dark:text-white">Planning Committee</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dec 6, 2025 at 10:00 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dark Mode Toggle Function
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            // Save preference to localStorage
            const isDarkMode = html.classList.contains('dark');
            localStorage.setItem('darkMode', isDarkMode);
        }

        // Initialize dark mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            const darkModePreference = localStorage.getItem('darkMode');
            const html = document.documentElement;
            
            if (darkModePreference === 'true' || (!darkModePreference && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            }
        });

        // Logout Function
        function logout() {
            // Send logout request to AuthController
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
                    // Redirect to login page with success notification
                    window.location.href = '../auth/login.php?logout=success';
                } else {
                    alert('Logout failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Still redirect even if there's an error
                window.location.href = '../auth/login.php?logout=success';
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function toggleModule(button) {
            const submenu = button.nextElementSibling;
            const icon = button.querySelector('i:last-child');
            
            submenu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Close sidebar when clicking a link on mobile
        if (window.innerWidth < 768) {
            document.querySelectorAll('.submenu a').forEach(link => {
                link.addEventListener('click', () => {
                    document.getElementById('sidebar').classList.add('-translate-x-full');
                    document.getElementById('sidebarOverlay').classList.add('hidden');
                });
            });
        }
    </script>
</body>
</html>
