<?php
/**
 * Audit Helper
 * Manages system-wide activity logging and retrieval
 */

/**
 * Log a new activity in the audit logs
 * 
 * @param int $userId The ID of the user performing the action
 * @param string $action The type of action (e.g., CREATE, UPDATE, DELETE, LOGIN)
 * @param string $module The module where the action occurred
 * @param string $description A human-readable description of the action
 * @return bool Success or failure
 */
function logAuditAction($userId, $action, $module, $description)
{
    global $conn;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)");

    // Convert 0 to NULL for guest/failed login attempts
    $dbUserId = (empty($userId) || $userId == 0) ? null : $userId;

    $stmt->bind_param("issss", $dbUserId, $action, $module, $description, $ipAddress);
    return $stmt->execute();
}

/**
 * Get recent activity with optional filters
 */
function getAuditLogs($limit = 50, $offset = 0, $filters = [])
{
    global $conn;

    $sql = "SELECT al.*, u.first_name, u.last_name 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.user_id";

    $whereClauses = [];
    $params = [];
    $types = "";

    if (!empty($filters['user_id'])) {
        $whereClauses[] = "al.user_id = ?";
        $params[] = $filters['user_id'];
        $types .= "i";
    }

    if (!empty($filters['action'])) {
        $whereClauses[] = "al.action = ?";
        $params[] = $filters['action'];
        $types .= "s";
    }

    if (!empty($filters['date'])) {
        $whereClauses[] = "DATE(al.timestamp) = ?";
        $params[] = $filters['date'];
        $types .= "s";
    }

    if (!empty($filters['user_search'])) {
        $searchTerm = "%" . $filters['user_search'] . "%";
        $whereClauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $sql .= " ORDER BY al.timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    return $logs;
}

/**
 * Get total count of audit logs (for pagination)
 */
function getAuditLogsCount($filters = [])
{
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id";

    $whereClauses = [];
    $params = [];
    $types = "";

    if (!empty($filters['user_id'])) {
        $whereClauses[] = "al.user_id = ?";
        $params[] = $filters['user_id'];
        $types .= "i";
    }

    if (!empty($filters['action'])) {
        $whereClauses[] = "al.action = ?";
        $params[] = $filters['action'];
        $types .= "s";
    }

    if (!empty($filters['date'])) {
        $whereClauses[] = "DATE(al.timestamp) = ?";
        $params[] = $filters['date'];
        $types .= "s";
    }

    if (!empty($filters['user_search'])) {
        $searchTerm = "%" . $filters['user_search'] . "%";
        $whereClauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}