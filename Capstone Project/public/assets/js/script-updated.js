// LRMS System JavaScript - Standardized Global Script
// This script is shared across Landing, Dashboard, and all Modules.
// It handles: Real-time Clock, standard Logout, Dark Mode, Sidebar toggles, and UI animations.

// Define global logout function early to ensure it's reachable
window.logout = function () {
    if (confirm('Are you sure you want to log out?')) {
        // Determine base path based on current URL structure
        // If in /public/pages/module/index.php -> ../../../ (to root)
        // If in /public/dashboard.php -> ../ (to root)
        const isInPages = window.location.pathname.includes('/pages/');
        const root = isInPages ? '../../../' : '../';

        // Final destination after logout
        const landingPage = root + 'index.php';

        fetch(root + 'app/controllers/AuthController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=logout'
        })
            .then(() => {
                window.location.href = landingPage + '?logout=success';
            })
            .catch(() => {
                window.location.href = landingPage + '?logout=success';
            });
    }
};

document.addEventListener('DOMContentLoaded', function () {
    console.log('LRMS System initializing...');

    // ==========================================
    // 1. MOBILE SIDEBAR TOGGLE (Robust)
    // ==========================================
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

    if (mobileMenuBtn && mobileSidebar && sidebarOverlay) {
        function openMobileSidebar() {
            sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
            sidebarOverlay.classList.add('opacity-100', 'pointer-events-auto');
            mobileSidebar.classList.remove('-translate-x-full');
            mobileSidebar.classList.add('translate-x-0');

            // Staggered animation for menu items
            const menuItems = mobileSidebar.querySelectorAll('nav a, nav > div');
            menuItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 50 + (index * 30));
            });

            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebarFn() {
            sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
            sidebarOverlay.classList.remove('opacity-100', 'pointer-events-auto');
            mobileSidebar.classList.add('-translate-x-full');
            mobileSidebar.classList.remove('translate-x-0');
            document.body.style.overflow = '';
        }

        mobileMenuBtn.addEventListener('click', openMobileSidebar);
        closeMobileSidebar?.addEventListener('click', closeMobileSidebarFn);
        sidebarOverlay.addEventListener('click', closeMobileSidebarFn);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileSidebar.classList.contains('-translate-x-full')) {
                closeMobileSidebarFn();
            }
        });
    }

    // ==========================================
    // 2. DESKTOP SIDEBAR TOGGLE (Robust Multi-button support)
    // ==========================================
    const sidebarToggles = document.querySelectorAll('#sidebar-toggle, #sidebar-collapse, .sidebar-collapse-btn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    if (sidebar && sidebarToggles.length > 0) {
        // Load state
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent?.classList.add('expanded');
            sidebarToggles.forEach(btn => {
                btn.classList.add('sidebar-hidden');
                btn.querySelector('.sidebar-icon')?.classList.add('hidden');
                btn.querySelector('.arrow-icon')?.classList.remove('hidden');
            });
        }

        sidebarToggles.forEach(toggleBtn => {
            toggleBtn.addEventListener('click', function () {
                const isNowCollapsed = sidebar.classList.toggle('collapsed');
                mainContent?.classList.toggle('expanded');

                // Update all toggle buttons to match state
                sidebarToggles.forEach(btn => {
                    if (isNowCollapsed) {
                        btn.classList.add('sidebar-hidden');
                        btn.querySelector('.sidebar-icon')?.classList.add('hidden');
                        btn.querySelector('.arrow-icon')?.classList.remove('hidden');
                    } else {
                        btn.classList.remove('sidebar-hidden');
                        btn.querySelector('.sidebar-icon')?.classList.remove('hidden');
                        btn.querySelector('.arrow-icon')?.classList.add('hidden');
                    }
                });

                localStorage.setItem('sidebarCollapsed', isNowCollapsed);
            });
        });
    }

    // ==========================================
    // 3. DARK MODE TOGGLE (Robust)
    // ==========================================
    const themeToggle = document.getElementById('theme-toggle');
    const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
    const html = document.documentElement;

    function refreshThemeIcons() {
        const isDark = html.classList.contains('dark');
        const darkIcons = document.querySelectorAll('.dark-mode-icon');
        const lightIcons = document.querySelectorAll('.light-mode-icon');

        darkIcons.forEach(icon => isDark ? icon.classList.add('hidden') : icon.classList.remove('hidden'));
        lightIcons.forEach(icon => isDark ? icon.classList.remove('hidden') : icon.classList.add('hidden'));
    }

    function toggleTheme() {
        html.classList.toggle('dark');
        const isDark = html.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        refreshThemeIcons();
    }

    themeToggle?.addEventListener('click', toggleTheme);
    mobileThemeToggle?.addEventListener('click', toggleTheme);
    refreshThemeIcons(); // Initial sync

    // ==========================================
    // 4. DROPDOWN HANDLERS (Robust)
    // ==========================================
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (notificationsBtn && notificationsDropdown) {
        notificationsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('hidden');
            profileDropdown?.classList.add('hidden');
        });
    }

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
            notificationsDropdown?.classList.add('hidden');
        });
    }

    document.addEventListener('click', function () {
        notificationsDropdown?.classList.add('hidden');
        profileDropdown?.classList.add('hidden');
    });

    // Mark all as read placeholder
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    markAllReadBtn?.addEventListener('click', function () {
        showToast('Notifications marked as read', 'success');
        notificationsDropdown?.classList.add('hidden');
        const badge = document.getElementById('notification-count');
        if (badge) badge.style.display = 'none';
    });

    // ==========================================
    // 5. REAL-TIME CLOCK & DATE (Robust)
    // ==========================================
    function updateClock() {
        const now = new Date();

        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);

        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', dateOptions);

        document.querySelectorAll('.real-time-clock').forEach(el => { el.textContent = timeStr; });
        document.querySelectorAll('.real-time-date').forEach(el => { el.textContent = dateStr; });
    }

    setInterval(updateClock, 1000);
    updateClock(); // Run immediately

    // ==========================================
    // 6. UI UTILITIES (Robust)
    // ==========================================
    // Animation on scroll
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll, .scroll-reveal').forEach(el => scrollObserver.observe(el));

    // Toast Notification System
    window.showToast = function (message, type = 'info') {
        const container = document.getElementById('toast-container') || document.body;
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 z-[100] transform transition-all duration-300 opacity-0 translate-y-2 p-4 rounded-lg shadow-lg text-white font-bold flex items-center gap-3 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        toast.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('opacity-0', 'translate-y-2'), 10);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    console.log('LRMS System Ready.');
});
