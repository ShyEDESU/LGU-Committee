// ==============================
// AUDIT LOGS MODULE
// ==============================

// Mock audit logs data
const auditLogs = [
    {
        id: 1,
        user: "Admin User",
        action: "create",
        description: "Created new committee 'Finance Committee'",
        timestamp: "2025-12-22 10:30 AM",
        ipAddress: "192.168.1.100"
    },
    {
        id: 2,
        user: "John Doe",
        action: "update",
        description: "Updated committee profile for 'Health Committee'",
        timestamp: "2025-12-22 09:15 AM",
        ipAddress: "192.168.1.101"
    },
    {
        id: 3,
        user: "Admin User",
        action: "delete",
        description: "Deleted referral #REF-2025-001",
        timestamp: "2025-12-22 08:45 AM",
        ipAddress: "192.168.1.100"
    },
    {
        id: 4,
        user: "Jane Smith",
        action: "login",
        description: "User logged into the system",
        timestamp: "2025-12-22 08:00 AM",
        ipAddress: "192.168.1.102"
    },
    {
        id: 5,
        user: "Admin User",
        action: "create",
        description: "Created new user account for 'Staff Member'",
        timestamp: "2025-12-21 04:30 PM",
        ipAddress: "192.168.1.100"
    },
    {
        id: 6,
        user: "John Doe",
        action: "update",
        description: "Updated meeting schedule for 'Budget Committee'",
        timestamp: "2025-12-21 03:15 PM",
        ipAddress: "192.168.1.101"
    },
    {
        id: 7,
        user: "Jane Smith",
        action: "logout",
        description: "User logged out of the system",
        timestamp: "2025-12-21 02:00 PM",
        ipAddress: "192.168.1.102"
    },
    {
        id: 8,
        user: "Admin User",
        action: "update",
        description: "Modified system settings - Email notifications enabled",
        timestamp: "2025-12-21 01:30 PM",
        ipAddress: "192.168.1.100"
    },
    {
        id: 9,
        user: "Staff Member",
        action: "create",
        description: "Created new referral #REF-2025-015",
        timestamp: "2025-12-21 12:00 PM",
        ipAddress: "192.168.1.103"
    },
    {
        id: 10,
        user: "John Doe",
        action: "delete",
        description: "Deleted agenda item from 'Planning Committee'",
        timestamp: "2025-12-21 11:00 AM",
        ipAddress: "192.168.1.101"
    }
];

// Initialize audit logs on page load
document.addEventListener('DOMContentLoaded', function () {
    filterAuditLogs();
});

// Filter audit logs based on selected criteria
function filterAuditLogs() {
    const actionFilter = document.getElementById('filterAction')?.value || '';
    const userFilter = document.getElementById('filterUser')?.value.toLowerCase() || '';
    const dateFilter = document.getElementById('filterDate')?.value || '';

    let filtered = auditLogs.filter(log => {
        const matchesAction = !actionFilter || log.action === actionFilter;
        const matchesUser = !userFilter || log.user.toLowerCase().includes(userFilter);
        const matchesDate = !dateFilter || log.timestamp.includes(dateFilter);

        return matchesAction && matchesUser && matchesDate;
    });

    const tbody = document.getElementById('auditLogsList');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="bi bi-inbox text-4xl mb-2 block"></i>
                    No audit logs found matching your criteria
                </td>
            </tr>
        `;
        updateCounts(0, auditLogs.length);
        return;
    }

    tbody.innerHTML = filtered.map(log => `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">${log.timestamp}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${log.user}</td>
            <td class="px-6 py-4">${getActionBadge(log.action)}</td>
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">${log.description}</td>
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">${log.ipAddress}</td>
        </tr>
    `).join('');

    updateCounts(filtered.length, auditLogs.length);
}

// Get badge HTML for action type
function getActionBadge(action) {
    const badges = {
        create: '<span class="badge bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Create</span>',
        update: '<span class="badge bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Update</span>',
        delete: '<span class="badge bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Delete</span>',
        login: '<span class="badge bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Login</span>',
        logout: '<span class="badge bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Logout</span>'
    };
    return badges[action] || `<span class="badge bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">${action}</span>`;
}

// Update showing counts
function updateCounts(showing, total) {
    const showingCount = document.getElementById('showingCount');
    const totalCount = document.getElementById('totalCount');

    if (showingCount) showingCount.textContent = showing;
    if (totalCount) totalCount.textContent = total;
}

// Clear all filters
function clearFilters() {
    document.getElementById('filterAction').value = '';
    document.getElementById('filterUser').value = '';
    document.getElementById('filterDate').value = '';
    filterAuditLogs();
}

// Add new audit log entry (can be called from other pages)
function addAuditLog(action, description) {
    const newLog = {
        id: auditLogs.length + 1,
        user: "<?php echo $_SESSION['user_name'] ?? 'Current User'; ?>",
        action: action,
        description: description,
        timestamp: new Date().toLocaleString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }),
        ipAddress: '192.168.1.100'
    };

    auditLogs.unshift(newLog);

    // If on audit logs page, refresh the table
    if (document.getElementById('auditLogsList')) {
        filterAuditLogs();
    }
}
