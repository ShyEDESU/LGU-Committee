// LRMS System JavaScript

// Theme Toggle
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        html.classList.add('dark');
        updateThemeIcon(true);
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        });
    }
}

function updateThemeIcon(isDark) {
    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
        if (isDark) {
            icon.classList.remove('bi-moon');
            icon.classList.add('bi-sun');
        } else {
            icon.classList.remove('bi-sun');
            icon.classList.add('bi-moon');
        }
    }
}

// Mobile Sidebar Toggle
function initMobileSidebar() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.add('hidden');
        });
    }
}

// Dropdown Toggle (Updated to handle multiple dropdowns)
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const icon = document.getElementById(dropdownId + '-icon');
    
    // Close all other dropdowns
    document.querySelectorAll('[id$="-dropdown"]').forEach(d => {
        if (d.id !== dropdownId && d.classList.contains('show')) {
            d.classList.remove('show');
            d.classList.add('hidden');
        }
    });
    
    if (dropdown) {
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            dropdown.classList.add('hidden');
            if (icon) icon.classList.remove('rotate');
        } else {
            dropdown.classList.remove('hidden');
            dropdown.classList.add('show');
            if (icon) icon.classList.add('rotate');
        }
    }
}

// Mobile Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

// Clear All Notifications
function clearAllNotifications() {
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const notificationItems = notificationsDropdown?.querySelectorAll('[data-notification-id]');
    notificationItems?.forEach(item => item.remove());
    showToast('All notifications cleared', 'success');
    toggleDropdown('notifications-dropdown');
}

// Focus Search
function focusSearch() {
    const searchInput = document.getElementById('quick-search');
    if (searchInput) {
        searchInput.focus();
        searchInput.select();
    }
}

// Notifications Dropdown
function initNotificationsDropdown() {
    const notifBtn = document.getElementById('notifications-btn');
    const notifDropdown = document.getElementById('notifications-dropdown');
    
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
            // Close profile dropdown if open
            const profileDropdown = document.getElementById('profile-dropdown');
            if (profileDropdown) {
                profileDropdown.classList.add('hidden');
            }
        });
    }
}

// Profile Dropdown
function initProfileDropdown() {
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
            // Close notifications dropdown if open
            const notifDropdown = document.getElementById('notifications-dropdown');
            if (notifDropdown) {
                notifDropdown.classList.add('hidden');
            }
        });
    }
}

// Close dropdowns when clicking outside
function initClickOutside() {
    document.addEventListener('click', (e) => {
        const notifDropdown = document.getElementById('notifications-dropdown');
        const profileDropdown = document.getElementById('profile-dropdown');
        const notifBtn = document.getElementById('notifications-btn');
        const profileBtn = document.getElementById('profile-btn');
        
        if (notifDropdown && !notifBtn.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
        
        if (profileDropdown && !profileBtn.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Toast Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type} fade-in`;
    toast.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="bi bi-${getToastIcon(type)} text-xl"></i>
                <p class="text-sm font-medium text-gray-800">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return 'check-circle-fill';
        case 'error': return 'x-circle-fill';
        case 'warning': return 'exclamation-triangle-fill';
        case 'info': return 'info-circle-fill';
        default: return 'info-circle-fill';
    }
}

// Document Upload
function handleDocumentUpload() {
    showToast('Document uploaded successfully!', 'success');
    closeModal('upload-modal');
}

// Search Functionality
function initSearch() {
    const searchInput = document.getElementById('quick-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            console.log('Searching for:', query);
            // Implement search logic here
        });
    }
}

// Delete Confirmation
function confirmDelete(itemName) {
    if (confirm(`Are you sure you want to delete "${itemName}"?`)) {
        showToast(`"${itemName}" has been deleted.`, 'success');
        return true;
    }
    return false;
}

// Edit Item
function editItem(itemId) {
    console.log('Editing item:', itemId);
    showToast('Edit functionality would open here', 'info');
}

// View Item
function viewItem(itemId) {
    console.log('Viewing item:', itemId);
    showToast('View functionality would open here', 'info');
}

// Navigation
function navigateTo(section) {
    console.log('Navigating to:', section);
    
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked item
    event.target.closest('.nav-item').classList.add('active');
    
    // Hide all sections
    document.querySelectorAll('[id$="-section"]').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Show selected section
    const targetSection = document.getElementById(section + '-section');
    if (targetSection) {
        targetSection.classList.remove('hidden');
    }
}

// Filter Table
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    }
}

// Sort Table
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        return aText.localeCompare(bText);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Export Data
function exportData(format) {
    console.log('Exporting data as:', format);
    showToast(`Exporting data as ${format.toUpperCase()}...`, 'info');
    setTimeout(() => {
        showToast(`Data exported successfully as ${format.toUpperCase()}!`, 'success');
    }, 2000);
}

// Mark Notification as Read
function markAsRead(notificationId) {
    console.log('Marking notification as read:', notificationId);
    const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (notifElement) {
        notifElement.classList.remove('unread');
        notifElement.style.opacity = '0.6';
    }
}

// Clear All Notifications
function clearAllNotifications() {
    if (confirm('Are you sure you want to clear all notifications?')) {
        const notifList = document.querySelector('#notifications-dropdown .space-y-2');
        if (notifList) {
            notifList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No notifications</p>';
        }
        showToast('All notifications cleared', 'success');
    }
}

// Initialize Chart (using Chart.js if available)
function initCharts() {
    // Example chart initialization
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('activity-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Documents',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}

// Auto-expand dropdown if a sub-item is active
function autoExpandActiveDropdown() {
    const activeSubItem = document.querySelector('.nav-item-sub.active');
    if (activeSubItem) {
        const dropdown = activeSubItem.closest('.dropdown-content');
        if (dropdown) {
            dropdown.classList.remove('hidden');
            dropdown.classList.add('show');
            const dropdownId = dropdown.id;
            const icon = document.getElementById(dropdownId + '-icon');
            if (icon) {
                icon.classList.add('rotate');
            }
        }
    }
}

// Initialize all functions on page load
document.addEventListener('DOMContentLoaded', function() {
    initThemeToggle();
    initMobileSidebar();
    initNotificationsDropdown();
    initProfileDropdown();
    initClickOutside();
    initSearch();
    autoExpandActiveDropdown();
    initCharts();
    
    console.log('LRMS System initialized successfully');
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('quick-search');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
        });
        document.body.style.overflow = 'auto';
    }
});
