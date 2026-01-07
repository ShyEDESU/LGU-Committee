<?php
/**
 * Audit Helper
 * Manages audit logging for system actions
 */

// Initialize audit logs in session
if (!isset($_SESSION['audit_logs'])) {
    $_SESSION['audit_logs'] = [
        [
            'id' => 1,
            'user_id' => 1,
            'user_name' => 'Admin User',
            'action' => 'login',
            'module' => 'Authentication',
            'description' => 'User logged in successfully',
            'ip_address' => '192.168.1.100',
            'created_at' => '2024-12-07 08:30:00'
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'user_name' => 'Admin User',
            'action' => 'create',
            'module' => 'Referrals',
            'description' => 'Created referral: Ordinance No. 2024-001',
            'ip_address' => '192.168.1.100',
            'created_at' => '2024-12-07 09:15:00'
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'user_name' => 'Admin User',
            'action' => 'update',
            'module' => 'Committees',
            'description' => 'Updated committee: Finance Committee',
            'ip_address' => '192.168.1.100',
            'created_at' => '2024-12-07 10:45:00'
        ]
    ];
}

/**
 * Log an action
 */
function logAction($userId, $action, $module, $description, $ipAddress = null)
{
    if (!isset($_SESSION['audit_logs'])) {
        $_SESSION['audit_logs'] = [];
    }

    $logs = $_SESSION['audit_logs'];
    $newId = empty($logs) ? 1 : max(array_column($logs, 'id')) + 1;

    if ($ipAddress === null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    $_SESSION['audit_logs'][] = [
        'id' => $newId,
        'user_id' => $userId,
        'user_name' => $_SESSION['user_name'] ?? 'Unknown User',
        'action' => $action,
        'module' => $module,
        'description' => $description,
        'ip_address' => $ipAddress,
        'created_at' => date('Y-m-d H:i:s')
    ];

    return $newId;
}

/**
 * Get all audit logs
 */
function getAllAuditLogs()
{
    $logs = $_SESSION['audit_logs'] ?? [];
    // Sort by created_at descending
    usort($logs, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    return $logs;
}

/**
 * Get audit logs with filters
 */
function getAuditLogs($filters = [])
{
    $logs = getAllAuditLogs();

    if (isset($filters['user_id'])) {
        $logs = array_filter($logs, function ($log) use ($filters) {
            return $log['user_id'] == $filters['user_id'];
        });
    }

    if (isset($filters['action'])) {
        $logs = array_filter($logs, function ($log) use ($filters) {
            return $log['action'] === $filters['action'];
        });
    }

    if (isset($filters['module'])) {
        $logs = array_filter($logs, function ($log) use ($filters) {
            return $log['module'] === $filters['module'];
        });
    }

    if (isset($filters['date_from'])) {
        $logs = array_filter($logs, function ($log) use ($filters) {
            return strtotime($log['created_at']) >= strtotime($filters['date_from']);
        });
    }

    if (isset($filters['date_to'])) {
        $logs = array_filter($logs, function ($log) use ($filters) {
            return strtotime($log['created_at']) <= strtotime($filters['date_to'] . ' 23:59:59');
        });
    }

    return array_values($logs);
}

/**
 * Get logs by user
 */
function getLogsByUser($userId)
{
    return getAuditLogs(['user_id' => $userId]);
}

/**
 * Get logs by module
 */
function getLogsByModule($module)
{
    return getAuditLogs(['module' => $module]);
}

/**
 * Get logs by date range
 */
function getLogsByDateRange($startDate, $endDate)
{
    return getAuditLogs(['date_from' => $startDate, 'date_to' => $endDate]);
}

/**
 * Get logs by action
 */
function getLogsByAction($action)
{
    return getAuditLogs(['action' => $action]);
}
?>