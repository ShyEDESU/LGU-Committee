/* =====================
   UI Enhancements
   Modern Design System
   ===================== */

class UIEnhancements {
    constructor() {
        this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        this.init();
    }

    init() {
        this.setupSidebarToggle();
        this.setupTabNavigation();
        this.setupScrollAnimations();
        this.setupMouseEffects();
        this.setupResponsiveMenu();
        this.restoreSidebarState();
    }

    /* =====================
       Sidebar Collapse Toggle
       ===================== */

    setupSidebarToggle() {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        
        if (!toggleBtn) {
            console.warn('Sidebar toggle button not found');
            return;
        }

        toggleBtn.addEventListener('click', () => {
            this.toggleSidebar();
        });

        // Close sidebar on mobile when clicking outside
        if (window.innerWidth < 768) {
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    if (!this.sidebarCollapsed) {
                        this.toggleSidebar();
                    }
                }
            });
        }
    }

    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        
        this.sidebarCollapsed = !this.sidebarCollapsed;
        
        if (this.sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            sidebar.classList.add('animate-slide-out-left');
            toggleBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('animate-slide-in-left');
            toggleBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
        }

        // Remove animation class after animation completes
        setTimeout(() => {
            sidebar.classList.remove('animate-slide-out-left', 'animate-slide-in-left');
        }, 600);

        // Save state
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
    }

    restoreSidebarState() {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        
        if (!sidebar || !toggleBtn) return;

        if (this.sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            toggleBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
        } else {
            sidebar.classList.remove('collapsed');
            toggleBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
        }
    }

    /* =====================
       Tab-Based Navigation
       ===================== */

    setupTabNavigation() {
        const tabButtons = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('[data-tab-content]');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');
                this.switchTab(tabName, tabButtons, tabContents);
            });
        });

        // Keyboard navigation (Arrow keys)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                const activeTab = document.querySelector('[data-tab].active');
                if (!activeTab) return;

                const tabsList = Array.from(tabButtons);
                const currentIndex = tabsList.findIndex(t => t.getAttribute('data-tab') === activeTab.getAttribute('data-tab'));
                
                let nextIndex;
                if (e.key === 'ArrowRight') {
                    nextIndex = (currentIndex + 1) % tabsList.length;
                } else {
                    nextIndex = (currentIndex - 1 + tabsList.length) % tabsList.length;
                }

                const nextTab = tabsList[nextIndex];
                nextTab.click();
            }
        });
    }

    switchTab(tabName, tabButtons, tabContents) {
        // Deactivate all tabs
        tabButtons.forEach(btn => {
            btn.classList.remove('active', 'animate-scale-in');
        });
        
        tabContents.forEach(content => {
            content.classList.remove('active', 'animate-fade-in');
            content.style.display = 'none';
        });

        // Activate selected tab
        const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
        const activeContent = document.querySelector(`[data-tab-content="${tabName}"]`);

        if (activeButton) {
            activeButton.classList.add('active', 'animate-scale-in');
        }

        if (activeContent) {
            activeContent.style.display = 'block';
            activeContent.classList.add('active', 'animate-fade-in');
        }

        // Save active tab to localStorage
        localStorage.setItem(`activeTab_${location.pathname}`, tabName);
    }

    restoreActiveTab() {
        const savedTab = localStorage.getItem(`activeTab_${location.pathname}`);
        if (savedTab) {
            const tabButton = document.querySelector(`[data-tab="${savedTab}"]`);
            if (tabButton) {
                tabButton.click();
            }
        }
    }

    /* =====================
       Scroll Animations
       ===================== */

    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Animate cards on scroll
        document.querySelectorAll('.card, .module-item, .list-item').forEach(element => {
            observer.observe(element);
        });

        // Animate staggered lists
        document.querySelectorAll('.stagger-items').forEach(container => {
            const items = container.querySelectorAll('li, .item, .row');
            items.forEach((item, index) => {
                item.style.animationDelay = `${index * 50}ms`;
                item.classList.add('animate-fade-in-up');
            });
        });
    }

    /* =====================
       Mouse Effects
       ===================== */

    setupMouseEffects() {
        // Ripple effect on button click
        document.querySelectorAll('button, .btn, .card').forEach(element => {
            element.addEventListener('click', (e) => {
                this.createRipple(e);
            });
        });

        // Hover glow effect
        document.querySelectorAll('.interactive').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.createGlowEffect(e.target);
            });
        });
    }

    createRipple(event) {
        const button = event.currentTarget;
        const ripple = document.createElement('span');
        
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        button.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    }

    createGlowEffect(element) {
        const glow = document.createElement('div');
        glow.classList.add('glow-effect');
        element.appendChild(glow);

        setTimeout(() => glow.remove(), 300);
    }

    /* =====================
       Responsive Mobile Menu
       ===================== */

    setupResponsiveMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });

            // Close menu on link click
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('active');
                    menuToggle.classList.remove('active');
                });
            });

            // Close menu on outside click
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
                    mobileMenu.classList.remove('active');
                    menuToggle.classList.remove('active');
                }
            });
        }
    }

    /* =====================
       Utility Functions
       ===================== */

    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} animate-fade-in-up`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        document.body.appendChild(notification);

        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.add('animate-fade-out');
            setTimeout(() => notification.remove(), 300);
        });

        setTimeout(() => {
            notification.classList.add('animate-fade-out');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    showLoader(element) {
        if (!element) return;
        const loader = document.createElement('div');
        loader.className = 'spinner animate-spin';
        element.innerHTML = '';
        element.appendChild(loader);
    }

    hideLoader(element) {
        if (!element) return;
        element.innerHTML = '';
    }

    /* =====================
       Theme Toggle
       ===================== */

    setupThemeToggle() {
        const themeToggle = document.querySelector('.theme-toggle');
        if (!themeToggle) return;

        const isDark = localStorage.getItem('isDarkMode') === 'true';
        if (isDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        themeToggle.addEventListener('click', () => {
            const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
            
            if (isDarkMode) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('isDarkMode', 'false');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('isDarkMode', 'true');
            }
        });
    }

    /* =====================
       Smooth Scroll
       ===================== */

    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /* =====================
       Form Enhancement
       ===================== */

    setupFormAnimations() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Add animation to form inputs on focus
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('has-focus');
                });

                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.parentElement.classList.remove('has-focus');
                    }
                });
            });

            // Add loading state on submit
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('is-loading');
                    submitBtn.disabled = true;
                }
            });
        });
    }

    /* =====================
       Initialize on DOM Ready
       ===================== */

    static init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.uiEnhancements = new UIEnhancements();
            });
        } else {
            window.uiEnhancements = new UIEnhancements();
        }
    }
}

// Auto-initialize
UIEnhancements.init();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UIEnhancements;
}
