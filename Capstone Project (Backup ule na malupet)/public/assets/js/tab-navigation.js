/**
 * Tab Navigation System
 * Handles tab switching with animations and localStorage persistence
 */

class TabNavigation {
    constructor(containerId) {
        this.container = document.getElementById(containerId) || document.body;
        this.init();
    }

    init() {
        this.setupTabButtons();
        this.restoreActiveTab();
    }

    setupTabButtons() {
        const tabButtons = this.container.querySelectorAll('[data-tab]');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = button.getAttribute('data-tab');
                this.switchTab(tabName, button);
            });
        });
    }

    switchTab(tabName, buttonElement = null) {
        // Get all tab content elements
        const tabContents = this.container.querySelectorAll('[data-tab-content]');
        const tabButtons = this.container.querySelectorAll('[data-tab]');

        // Hide all tab contents with fade out animation
        tabContents.forEach(content => {
            if (content.getAttribute('data-tab-content') !== tabName) {
                content.classList.add('hidden');
                content.style.animation = 'none';
            }
        });

        // Remove active state from all buttons
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderBottomColor = 'transparent';
        });

        // Show the selected tab content with fade in animation
        const activeContent = this.container.querySelector(`[data-tab-content="${tabName}"]`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
            activeContent.style.animation = 'tab-content-fade 300ms ease-in-out';
            activeContent.offsetHeight; // Trigger reflow
        }

        // Set active button
        const activeButton = buttonElement || this.container.querySelector(`[data-tab="${tabName}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
            activeButton.style.borderBottomColor = '#dc2626';
        }

        // Save active tab to localStorage
        const pageKey = `activeTab_${window.location.pathname}`;
        localStorage.setItem(pageKey, tabName);
    }

    restoreActiveTab() {
        const pageKey = `activeTab_${window.location.pathname}`;
        const savedTab = localStorage.getItem(pageKey);

        if (savedTab) {
            this.switchTab(savedTab);
        } else {
            // Activate first tab by default
            const firstButton = this.container.querySelector('[data-tab]');
            if (firstButton) {
                const firstTabName = firstButton.getAttribute('data-tab');
                this.switchTab(firstTabName, firstButton);
            }
        }
    }

    // Add smooth scrolling to tab content
    scrollToContent(tabName) {
        const content = this.container.querySelector(`[data-tab-content="${tabName}"]`);
        if (content) {
            content.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize main tab navigation
    const mainTabs = document.getElementById('main-tabs');
    if (mainTabs) {
        new TabNavigation('main-tabs');
    }

    // Setup keyboard navigation
    setupKeyboardNavigation();

    // Setup item animations
    setupItemAnimations();
});

/**
 * Keyboard Navigation for Tabs
 * Arrow keys to switch between tabs
 */
function setupKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const activeTab = document.querySelector('[data-tab].active');
            if (!activeTab) return;

            const allTabs = Array.from(document.querySelectorAll('[data-tab]'));
            const currentIndex = allTabs.indexOf(activeTab);

            let nextIndex;
            if (e.key === 'ArrowLeft') {
                nextIndex = currentIndex > 0 ? currentIndex - 1 : allTabs.length - 1;
            } else {
                nextIndex = currentIndex < allTabs.length - 1 ? currentIndex + 1 : 0;
            }

            const nextTab = allTabs[nextIndex];
            if (nextTab) {
                const tabName = nextTab.getAttribute('data-tab');
                const container = nextTab.closest('[id*="tabs"]') || document.body;
                const tabNav = new TabNavigation(container.id);
                tabNav.switchTab(tabName, nextTab);
                e.preventDefault();
            }
        }
    });
}

/**
 * Setup animations for list items
 * Items animate in with staggered timing
 */
function setupItemAnimations() {
    const listItems = document.querySelectorAll('.submodule-item');
    
    listItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        // Stagger animation with delay
        setTimeout(() => {
            item.style.transition = 'all 300ms cubic-bezier(0.4, 0, 0.2, 1)';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 50);
    });

    // Setup hover effects on items
    listItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
            this.style.backgroundColor = 'rgba(220, 38, 38, 0.05)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.backgroundColor = 'transparent';
        });
    });
}

/**
 * Export for use in module pages
 */
window.TabNav = {
    init: function(containerId) {
        return new TabNavigation(containerId);
    },
    setupKeyboard: setupKeyboardNavigation,
    animateItems: setupItemAnimations
};
