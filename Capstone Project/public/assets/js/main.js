/**
 * Main JavaScript - Legislative Services Committee Management System
 * 
 * Handles:
 * - Sidebar toggle and navigation
 * - Form submissions
 * - Modal management
 * - Dynamic content loading
 * - User interactions
 * 
 * @version 1.0
 */

// ============================================================================
// SIDEBAR MANAGEMENT
// ============================================================================

class SidebarManager {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.hamburgerBtn = document.querySelector('.hamburger-btn');
        this.overlay = document.querySelector('.overlay');
        this.sidebarLinks = document.querySelectorAll('.sidebar-link[data-toggle]');
        this.init();
    }
    
    init() {
        // Hamburger button click
        this.hamburgerBtn?.addEventListener('click', () => this.toggleSidebar());
        
        // Overlay click to close sidebar
        this.overlay?.addEventListener('click', () => this.closeSidebar());
        
        // Sidebar menu toggles (parent items with submenus)
        this.sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSubmenu(link);
            });
        });
        
        // Sidebar links - close on small screens
        document.querySelectorAll('.sidebar-link:not([data-toggle])').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    this.closeSidebar();
                }
            });
        });
        
        // Set active link based on current page
        this.setActiveLink();
        
        // Close sidebar on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.sidebar') && 
                !e.target.closest('.hamburger-btn') &&
                window.innerWidth < 768) {
                this.closeSidebar();
            }
        });
    }
    
    toggleSidebar() {
        this.sidebar?.classList.toggle('active');
        this.overlay?.classList.toggle('active');
    }
    
    closeSidebar() {
        this.sidebar?.classList.remove('active');
        this.overlay?.classList.remove('active');
    }
    
    openSidebar() {
        this.sidebar?.classList.add('active');
        this.overlay?.classList.add('active');
    }
    
    toggleSubmenu(link) {
        const submenu = link.nextElementSibling;
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
            link.classList.add('collapsed');
        }
    }
    
    setActiveLink() {
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
}

// ============================================================================
// MODAL MANAGEMENT
// ============================================================================

class ModalManager {
    static showModal(modalId) {
        const modal = document.getElementById(modalId);
        const overlay = modal?.closest('.modal-overlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    static hideModal(modalId) {
        const modal = document.getElementById(modalId);
        const overlay = modal?.closest('.modal-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
    
    static hideAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.classList.remove('active');
        });
        document.body.style.overflow = 'auto';
    }
    
    static init() {
        // Close modal on close button click
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const overlay = e.target.closest('.modal-overlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        });
        
        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        });
    }
}

// ============================================================================
// ALERT MANAGEMENT
// ============================================================================

class AlertManager {
    static showAlert(message, type = 'info', duration = 5000) {
        const alertHtml = `
            <div class="alert alert-${type}">
                <span>${message}</span>
                <button class="alert-close">&times;</button>
            </div>
        `;
        
        // Find or create alerts container
        let container = document.querySelector('.alerts-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'alerts-container';
            container.style.position = 'fixed';
            container.style.top = '5rem';
            container.style.right = '1rem';
            container.style.zIndex = '1100';
            container.style.maxWidth = '400px';
            document.body.appendChild(container);
        }
        
        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHtml;
        const alert = alertElement.firstElementChild;
        
        container.appendChild(alert);
        
        // Close button functionality
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.remove();
        });
        
        // Auto remove
        if (duration) {
            setTimeout(() => {
                alert.remove();
            }, duration);
        }
    }
    
    static success(message) {
        this.showAlert(message, 'success');
    }
    
    static danger(message) {
        this.showAlert(message, 'danger');
    }
    
    static warning(message) {
        this.showAlert(message, 'warning');
    }
    
    static info(message) {
        this.showAlert(message, 'info');
    }
}

// ============================================================================
// FORM HANDLING
// ============================================================================

class FormHandler {
    static init() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        });
    }
    
    static handleSubmit(e) {
        const form = e.target;
        const method = form.getAttribute('method') || 'POST';
        const action = form.getAttribute('action');
        const isAjax = form.getAttribute('data-ajax') === 'true';
        
        if (isAjax) {
            e.preventDefault();
            this.submitFormAjax(form, method, action);
        }
    }
    
    static submitFormAjax(form, method, action) {
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalButtonText = button?.textContent;
        
        // Disable button
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span> Processing...';
        }
        
        fetch(action, {
            method: method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            if (button) {
                button.disabled = false;
                button.textContent = originalButtonText;
            }
            
            if (data.success) {
                AlertManager.success(data.message);
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
                if (data.callback) {
                    window[data.callback](data);
                }
            } else {
                AlertManager.danger(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            AlertManager.danger('An error occurred while processing your request');
            if (button) {
                button.disabled = false;
                button.textContent = originalButtonText;
            }
        });
    }
    
    static validateForm(form) {
        let isValid = true;
        
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        return isValid;
    }
    
    static showFieldError(field, message) {
        field.classList.add('is-invalid');
        let errorMsg = field.parentElement.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('small');
            errorMsg.className = 'error-message';
            errorMsg.style.color = 'var(--danger-color)';
            errorMsg.style.display = 'block';
            errorMsg.style.marginTop = '0.25rem';
            field.parentElement.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
    }
    
    static clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorMsg = field.parentElement.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
}

// ============================================================================
// DATA TABLE ENHANCEMENTS
// ============================================================================

class TableManager {
    static init() {
        // Sort columns
        document.querySelectorAll('th[data-sortable]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', (e) => this.sortTable(e));
        });
        
        // Search functionality
        const searchInput = document.querySelector('[data-table-search]');
        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => this.filterTable(e));
        }
    }
    
    static sortTable(e) {
        const th = e.target;
        const table = th.closest('table');
        const column = Array.from(th.parentElement.children).indexOf(th);
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const isAsc = th.classList.toggle('sort-asc');
        
        rows.sort((a, b) => {
            const aValue = a.children[column]?.textContent.trim();
            const bValue = b.children[column]?.textContent.trim();
            
            const comparison = isNaN(aValue) ?
                aValue.localeCompare(bValue) :
                aValue - bValue;
            
            return isAsc ? comparison : -comparison;
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
    
    static filterTable(e) {
        const input = e.target;
        const filter = input.value.toLowerCase();
        const table = input.closest('[data-table-container]')?.querySelector('table');
        
        if (!table) return;
        
        table.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }
}

// ============================================================================
// UTILITIES
// ============================================================================

class Utils {
    static formatDate(date, format = 'MM/DD/YYYY') {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return format
            .replace('DD', day)
            .replace('MM', month)
            .replace('YYYY', year)
            .replace('HH', hours)
            .replace('mm', minutes);
    }
    
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    static throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    static copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            AlertManager.success('Copied to clipboard');
        });
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all managers
    // COMMENTED OUT: new SidebarManager(); // Using simpler toggle in dashboard.php
    ModalManager.init();
    FormHandler.init();
    TableManager.init();
    
    // Add overlay element if not exists
    if (!document.querySelector('.overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
    }
});

// Make classes globally available
window.SidebarManager = SidebarManager;
window.ModalManager = ModalManager;
window.AlertManager = AlertManager;
window.FormHandler = FormHandler;
window.TableManager = TableManager;
window.Utils = Utils;
