// LRMS System JavaScript - Standardized Global Script
// This script is shared across Landing, Dashboard, and all Modules.
// It handles: Real-time Clock, standard Logout, Dark Mode, Sidebar toggles, and UI animations.

// ── CENTERED PAGE LOADER OVERLAY ────────────────────────────────────────────
window.PageLoader = {
    el: null,

    init() {
        if (this.el || !document.body) return;

        const style = document.createElement('style');
        style.textContent = `
            #cms-page-loader {
                position: fixed;
                inset: 0;
                z-index: 99998;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(3px);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.18s ease;
            }
            #cms-page-loader.visible {
                opacity: 1;
                pointer-events: all;
            }
            #cms-page-loader .loader-card {
                background: #fff;
                border-radius: 16px;
                padding: 32px 40px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                min-width: 160px;
            }
            .dark #cms-page-loader .loader-card {
                background: #1f2937;
            }
            #cms-page-loader .loader-spinner {
                width: 48px;
                height: 48px;
                border: 4px solid #fee2e2;
                border-top-color: #dc2626;
                border-radius: 50%;
                animation: cms-spin 0.7s linear infinite;
            }
            #cms-page-loader .loader-text {
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }
            .dark #cms-page-loader .loader-text {
                color: #9ca3af;
            }
            @keyframes cms-spin {
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        this.el = document.createElement('div');
        this.el.id = 'cms-page-loader';
        this.el.innerHTML = `
            <div class="loader-card">
                <div class="loader-spinner"></div>
                <span class="loader-text">Loading...</span>
            </div>`;
        document.body.appendChild(this.el);
    },

    show() {
        this.init();
        if (!this.el) return;
        // Force reflow so transition fires
        this.el.offsetHeight;
        this.el.classList.add('visible');
    },

    hide() {
        if (!this.el) return;
        this.el.classList.remove('visible');
    }
};

// ── TOP PROGRESS BAR ─────────────────────────────────────────────────────────
window.LoadingBar = {
    el: null,
    interval: null,

    init() {
        if (this.el) return;

        const style = document.createElement('style');
        style.textContent = `
            #cms-global-loader {
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                background: linear-gradient(to right, #dc2626, #ef4444, #f97316);
                z-index: 999999;
                width: 0%;
                opacity: 0;
                transition: width 0.2s ease-out, opacity 0.3s ease-in-out;
                box-shadow: 0 0 8px rgba(220, 38, 38, 0.6);
                pointer-events: none;
            }
        `;
        document.head.appendChild(style);

        // Guard: body may not exist if called before DOMContentLoaded
        if (!document.body) return;
        this.el = document.createElement('div');
        this.el.id = 'cms-global-loader';
        document.body.appendChild(this.el);
    },

    
    start() {
        this.init();
        if (!this.el) return; // body wasn't ready yet
        if (this.interval) clearInterval(this.interval);
        
        this.el.style.opacity = '1';
        this.el.style.width = '0%';
        // Force reflow
        this.el.offsetHeight;
        this.el.style.width = '25%';
        
        // Simulate progressive loading
        this.interval = setInterval(() => {
            const currentWidth = parseFloat(this.el.style.width);
            if (currentWidth < 85) {
                this.el.style.width = (currentWidth + Math.random() * 8) + '%';
            }
        }, 100);
    },
    
    finish(callback) {
        if (this.interval) clearInterval(this.interval);
        if (this.el) {
            this.el.style.width = '100%';
            setTimeout(() => {
                this.el.style.opacity = '0';
                setTimeout(() => {
                    this.el.style.width = '0%';
                    if (callback) callback();
                }, 300);
            }, 200);
        } else if (callback) {
            callback();
        }
    }
};

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

        // Show centered spinner + start top bar
        window.PageLoader.show();
        window.LoadingBar.start();

        fetch(root + 'app/controllers/AuthController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=logout'
        })
            .then(() => {
                window.LoadingBar.finish(() => {
                    window.location.href = landingPage + '?logout=success';
                });
            })
            .catch(() => {
                window.LoadingBar.finish(() => {
                    window.location.href = landingPage + '?logout=success';
                });
            });
    }
};
document.addEventListener('DOMContentLoaded', function () {
    // ── PAGE TRANSITION SYSTEM ──────────────────────────────────────────────
    // Initialise the centered loader overlay now that body exists
    window.PageLoader.init();

    // Fade in the content wrapper when the page is ready
    const contentWrapper = document.getElementById('module-content-wrapper');
    if (contentWrapper) {
        requestAnimationFrame(() => {
            contentWrapper.style.opacity = '1';
        });
    }

    // Hide any lingering loader overlay from the previous navigation
    window.PageLoader.hide();

    // Start + finish the top loading bar on every page load
    window.LoadingBar.start();
    window.addEventListener('load', function () {
        window.LoadingBar.finish();
    });

    // Intercept all internal link clicks → show loader, fade out, then navigate
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        const target = link.getAttribute('target');

        // Skip: hash-only, javascript:, new-tab, logout-handled, or no href
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank') return;
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('logout')) return;

        // Prevent instant navigation
        e.preventDefault();

        // Show centered spinner overlay
        window.PageLoader.show();

        // Start the top loading bar
        window.LoadingBar.start();

        // Fade out the content wrapper
        if (contentWrapper) {
            contentWrapper.style.opacity = '0';
        }

        // Navigate after the fade-out completes (220ms matches CSS transition)
        setTimeout(() => {
            window.location.href = href;
        }, 220);
    }, true); // use capture so it fires before other handlers

    // Intercept ALL form submissions → show loader overlay
    document.addEventListener('submit', function () {
        window.PageLoader.show();
        window.LoadingBar.start();
        if (contentWrapper) {
            contentWrapper.style.opacity = '0';
        }
    });
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

    // Mark all as read with AJAX
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    markAllReadBtn?.addEventListener('click', async function () {
        try {
            const apiPath = (window.CMS_ASSET_PATH || '') + 'api/notifications.php?action=mark_all_read';
            const response = await fetch(apiPath);
            const result = await response.json();

            if (result.success) {
                showToast('All notifications marked as read', 'success');
                notificationsDropdown?.classList.add('hidden');

                // Hide badges locally
                document.querySelectorAll('.unread-badge, #notifications-btn .absolute').forEach(el => {
                    el.style.display = 'none';
                });

                // Refresh UI after a short delay
                setTimeout(() => location.reload(), 500);
            }
        } catch (error) {
            console.error('Error marking notifications as read:', error);
            showToast('Failed to mark notifications as read', 'error');
        }
    });

    // ==========================================
    // 5. REAL-TIME CLOCK & DATE (Standardized)
    // ==========================================
    function updateClock() {
        const now = new Date();

        // 12-hour format with seconds: HH:MM:SS AM/PM
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);

        // Standard date: Wed, Feb 12, 2025
        const dateOptions = {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        };
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
