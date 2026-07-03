<?php
/**
 * Reports and Analytics Helper
 * Provides aggregated data for dashboards and reports
 */

/**
 * Get overall summary statistics
 */
function getOverallStats()
{
    global $conn;
    $stats = [];

    // Total Meetings
    $res = $conn->query("SELECT COUNT(*) as count FROM meetings");
    $stats['meetings'] = $res->fetch_assoc()['count'];

    // Total Committee Reports
    $res = $conn->query("SELECT COUNT(*) as count FROM reports");
    $stats['reports'] = $res->fetch_assoc()['count'];

    // Total Action Items
    $res = $conn->query("SELECT COUNT(*) as count FROM tasks");
    $stats['tasks'] = $res->fetch_assoc()['count'];

    // Total Committees
    $res = $conn->query("SELECT COUNT(*) as count FROM committees WHERE is_active = 1");
    $stats['committees'] = $res->fetch_assoc()['count'];

    // Total Users
    $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['users'] = $res->fetch_assoc()['count'];

    // Legislative Documents (used as "Referrals" in analytics KPI)
    $res = $conn->query("SELECT COUNT(*) as count FROM legislative_documents");
    $stats['referrals'] = $res ? ($res->fetch_assoc()['count'] ?? 0) : 0;

    return $stats;
}

/**
 * Get monthly meeting and document trends for the last 12 months
 */
function getMonthlyTrends()
{
    global $conn;
    $trends = [];

    // Initialize months
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $trends[$month] = ['meetings' => 0, 'documents' => 0];
    }

    // Meetings trend
    $sql = "SELECT DATE_FORMAT(meeting_date, '%Y-%m') as month, COUNT(*) as count 
            FROM meetings 
            WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (isset($trends[$row['month']])) {
            $trends[$row['month']]['meetings'] = (int) $row['count'];
        }
    }

    // Documents trend
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
            FROM meeting_documents 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (isset($trends[$row['month']])) {
            $trends[$row['month']]['documents'] = (int) $row['count'];
        }
    }

    return $trends;
}

/**
 * Get counts by status for a specific table
 */
function getStatusDistribution($type = 'tasks')
{
    global $conn;
    $table = ($type === 'tasks') ? 'tasks' : (($type === 'reports') ? 'reports' : 'meetings');

    $sql = "SELECT status, COUNT(*) as count FROM $table GROUP BY status";
    $result = $conn->query($sql);

    $distribution = [];
    while ($row = $result->fetch_assoc()) {
        $distribution[$row['status']] = (int) $row['count'];
    }
    return $distribution;
}

/**
 * Get most active committees
 */
function getActiveCommittees($limit = 5)
{
    global $conn;
    $sql = "SELECT c.committee_name, COUNT(m.meeting_id) as meeting_count 
            FROM committees c
            LEFT JOIN meetings m ON c.committee_id = m.committee_id
            GROUP BY c.committee_id
            ORDER BY meeting_count DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get audit log summary by action type
 */
function getAuditActivitySummary()
{
    global $conn;
    $sql = "SELECT action, COUNT(*) as count 
            FROM audit_logs 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY action";
    $result = $conn->query($sql);

    $summary = [];
    while ($row = $result->fetch_assoc()) {
        $summary[$row['action']] = (int) $row['count'];
    }
    return $summary;
}

/**
 * Get detailed attendance metrics
 */
function getAttendanceMetrics()
{
    global $conn;

    // Average attendance rate across all meetings
    $sql = "SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_rate 
            FROM attendance_records";
    $res = $conn->query($sql);
    $overall_avg = $res->fetch_assoc()['avg_rate'] ?? 0;

    // Monthly attendance trend (last 6 months)
    $monthly_sql = "SELECT DATE_FORMAT(m.meeting_date, '%Y-%m') as month, 
                           AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as rate
                    FROM meetings m
                    LEFT JOIN attendance_records a ON m.meeting_id = a.meeting_id
                    WHERE m.meeting_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY month
                    ORDER BY month ASC";
    $monthly_res = $conn->query($monthly_sql);
    $monthly_trend = [];
    while ($row = $monthly_res->fetch_assoc()) {
        $monthly_trend[$row['month']] = round($row['rate'], 1);
    }

    return [
        'overall_avg' => round($overall_avg, 1),
        'monthly_trend' => $monthly_trend
    ];
}

/**
 * Get statistics of committee reports by status
 */
function getReportStats()
{
    global $conn;
    $sql = "SELECT status, COUNT(*) as count FROM reports GROUP BY status";
    $result = $conn->query($sql);
    
    $stats = ['Draft' => 0, 'Voting' => 0, 'Approved' => 0, 'Rejected' => 0];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'] ?? 'Draft';
            $stats[$status] = (int) $row['count'];
        }
    }
    return $stats;
}

/**
 * Get Task Efficiency and Aging (Bottlenecks)
 */
function getTaskEfficiency()
{
    global $conn;

    // Average completion time for tasks
    $sql = "SELECT AVG(DATEDIFF(completed_at, created_at)) as avg_completion_days
            FROM tasks
            WHERE status = 'Done'";
    $res = $conn->query($sql);
    $avg_completion = $res->fetch_assoc()['avg_completion_days'] ?? 0;

    // Aging: Tasks pending for more than 14 days
    $aging_sql = "SELECT COUNT(*) as older_tasks
                  FROM tasks
                  WHERE status != 'Done' 
                  AND DATEDIFF(NOW(), created_at) > 14";
    $aging_res = $conn->query($aging_sql);
    $older_tasks = $aging_res->fetch_assoc()['older_tasks'] ?? 0;

    return [
        'avg_completion_days' => round($avg_completion, 1),
        'older_tasks_count' => (int) $older_tasks
    ];
}

/**
 * Get legislative cycle time metrics from legislative_documents table.
 * Returns average age in days (since created_at) and document type distribution.
 */
function getLegislativeCycleTime()
{
    global $conn;

    // Average age in days of documents from creation (proxy for cycle time)
    $cycleSql = "SELECT AVG(DATEDIFF(NOW(), created_at)) as avg_days
                 FROM legislative_documents
                 WHERE status = 'Approved'";
    $cycleRes = $conn->query($cycleSql);
    $avgDays = 0;
    if ($cycleRes && $row = $cycleRes->fetch_assoc()) {
        $avgDays = round($row['avg_days'] ?? 0);
    }

    // Document type distribution
    $typeSql = "SELECT document_type, COUNT(*) as count
                FROM legislative_documents
                GROUP BY document_type
                ORDER BY count DESC";
    $typeRes = $conn->query($typeSql);
    $typeDistribution = [];
    if ($typeRes) {
        while ($row = $typeRes->fetch_assoc()) {
            $typeDistribution[$row['document_type']] = (int) $row['count'];
        }
    }

    // Fallback if table is empty
    if (empty($typeDistribution)) {
        $typeDistribution = ['No Documents' => 0];
    }

    return [
        'avg_cycle_days' => $avgDays,
        'type_distribution' => $typeDistribution
    ];
}


/**
 * Retrieve all reports with optional filters
 */
function getAllReports($filters = [])
{
    global $conn;
    
    $sql = "SELECT r.*, c.committee_name, 
                   ld.title as document_title, ld.document_number,
                   m.meeting_title, m.meeting_date,
                   CONCAT(u.first_name, ' ', u.last_name) as creator_name 
            FROM reports r
            LEFT JOIN committees c ON r.committee_id = c.committee_id
            LEFT JOIN legislative_documents ld ON r.document_id = ld.document_id
            LEFT JOIN meetings m ON r.meeting_id = m.meeting_id
            LEFT JOIN users u ON r.created_by = u.user_id";
            
    $whereClauses = [];
    $params = [];
    $types = "";
    
    if (!empty($filters['committee_id'])) {
        $whereClauses[] = "r.committee_id = ?";
        $params[] = $filters['committee_id'];
        $types .= "i";
    }
    
    if (!empty($filters['status'])) {
        $whereClauses[] = "r.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $whereClauses[] = "(r.title LIKE ? OR r.content LIKE ?)";
        $searchParam = "%" . $filters['search'] . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    
    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    $stmt->close();
    
    return $reports;
}

/**
 * Retrieve report by ID
 */
function getReportById($reportId)
{
    global $conn;
    
    $stmt = $conn->prepare("SELECT r.*, c.committee_name, 
                                   ld.title as document_title, ld.document_number,
                                   m.meeting_title, m.meeting_date,
                                   CONCAT(u.first_name, ' ', u.last_name) as creator_name 
                            FROM reports r
                            LEFT JOIN committees c ON r.committee_id = c.committee_id
                            LEFT JOIN legislative_documents ld ON r.document_id = ld.document_id
                            LEFT JOIN meetings m ON r.meeting_id = m.meeting_id
                            LEFT JOIN users u ON r.created_by = u.user_id
                            WHERE r.report_id = ?");
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    $report = $result->fetch_assoc();
    $stmt->close();
    
    return $report;
}

/**
 * Create a new report draft
 */
function createReport($committeeId, $title, $reportType, $recommendation, $content, $createdBy, $documentId = null, $meetingId = null)
{
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO reports (committee_id, document_id, meeting_id, title, report_type, recommendation, status, content, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, 'Draft', ?, ?)");
    $stmt->bind_param("iiissssi", $committeeId, $documentId, $meetingId, $title, $reportType, $recommendation, $content, $createdBy);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success && function_exists('logAuditAction')) {
        logAuditAction($createdBy, 'CREATE', 'reports', "Drafted report '{$title}'");
    }
    
    return $success ? $conn->insert_id : false;
}

/**
 * Update report details
 */
function updateReport($reportId, $title, $reportType, $recommendation, $content, $status = null, $documentId = null, $meetingId = null)
{
    global $conn;
    
    if ($status) {
        $stmt = $conn->prepare("UPDATE reports 
                                SET title = ?, report_type = ?, recommendation = ?, content = ?, status = ?, document_id = ?, meeting_id = ? 
                                WHERE report_id = ?");
        $stmt->bind_param("sssssiii", $title, $reportType, $recommendation, $content, $status, $documentId, $meetingId, $reportId);
    } else {
        $stmt = $conn->prepare("UPDATE reports 
                                SET title = ?, report_type = ?, recommendation = ?, content = ?, document_id = ?, meeting_id = ? 
                                WHERE report_id = ?");
        $stmt->bind_param("ssssiii", $title, $reportType, $recommendation, $content, $documentId, $meetingId, $reportId);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success && function_exists('logAuditAction')) {
        $userId = $_SESSION['user_id'] ?? null;
        logAuditAction($userId, 'UPDATE', 'reports', "Updated report ID {$reportId} - '{$title}'");
    }
    
    return $success;
}

/**
 * Delete a report
 */
function deleteReport($reportId)
{
    global $conn;
    
    // Get details for auditing
    $report = getReportById($reportId);
    $title = $report ? $report['title'] : "Unknown";
    
    $stmt = $conn->prepare("DELETE FROM reports WHERE report_id = ?");
    $stmt->bind_param("i", $reportId);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success && function_exists('logAuditAction')) {
        $userId = $_SESSION['user_id'] ?? null;
        logAuditAction($userId, 'DELETE', 'reports', "Deleted report ID {$reportId} - '{$title}'");
    }
    
    return $success;
}

/**
 * Get all signatures recorded for a report
 */
function getReportSignatures($reportId)
{
    global $conn;
    
    $stmt = $conn->prepare("SELECT rs.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email 
                            FROM report_signatures rs
                            INNER JOIN users u ON rs.user_id = u.user_id
                            WHERE rs.report_id = ?");
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    $signatures = [];
    while ($row = $result->fetch_assoc()) {
        $signatures[$row['user_id']] = $row;
    }
    $stmt->close();
    
    return $signatures;
}

/**
 * Submit signature (Approved, Dissented, Abstained) for a report
 */
function submitSignature($reportId, $userId, $status)
{
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO report_signatures (report_id, user_id, status, signed_at) 
                            VALUES (?, ?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE status = ?, signed_at = NOW()");
    $stmt->bind_param("iiss", $reportId, $userId, $status, $status);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        if (function_exists('logAuditAction')) {
            logAuditAction($userId, 'UPDATE', 'reports', "Signed report ID {$reportId} as {$status}");
        }
        updateReportStatusBasedOnSignatures($reportId);
    }
    
    return $success;
}

/**
 * Get committee members for report signing (Chair, Vice-Chair, Secretary + active members)
 */
function getCommitteeMembersForReport($committeeId) {
    global $conn;
    
    // Get committee leadership
    $stmt = $conn->prepare("SELECT chairperson_id, vice_chair_id, secretary_id FROM committees WHERE committee_id = ?");
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $leadership = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $memberIds = [];
    $memberRoles = [];
    
    if ($leadership) {
        if ($leadership['chairperson_id']) {
            $memberIds[] = $leadership['chairperson_id'];
            $memberRoles[$leadership['chairperson_id']] = 'Chairperson';
        }
        if ($leadership['vice_chair_id']) {
            $memberIds[] = $leadership['vice_chair_id'];
            $memberRoles[$leadership['vice_chair_id']] = 'Vice-Chairperson';
        }
        if ($leadership['secretary_id']) {
            $memberIds[] = $leadership['secretary_id'];
            $memberRoles[$leadership['secretary_id']] = 'Secretary';
        }
    }
    
    // Get other active members
    $stmt = $conn->prepare("SELECT user_id, position FROM committee_members WHERE committee_id = ? AND is_active = 1");
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        if (!in_array($userId, $memberIds)) {
            $memberIds[] = $userId;
            $memberRoles[$userId] = $row['position'] ?: 'Member';
        }
    }
    $stmt->close();
    
    if (empty($memberIds)) {
        return [];
    }
    
    // Fetch user details for all members
    $idsPlaceholder = implode(',', array_fill(0, count($memberIds), '?'));
    $sql = "SELECT user_id, first_name, last_name, email, CONCAT(first_name, ' ', last_name) as full_name 
            FROM users 
            WHERE user_id IN ($idsPlaceholder)";
    $stmt = $conn->prepare($sql);
    
    $types = str_repeat('i', count($memberIds));
    $stmt->bind_param($types, ...$memberIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $members[] = [
            'user_id' => $userId,
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $memberRoles[$userId]
        ];
    }
    $stmt->close();
    
    return $members;
}

/**
 * Automatically update report status based on signatures count
 */
function updateReportStatusBasedOnSignatures($reportId)
{
    global $conn;
    
    $report = getReportById($reportId);
    if (!$report) return false;
    
    // Only update if currently in Voting state (or Draft, if someone initiates)
    if ($report['status'] !== 'Voting' && $report['status'] !== 'Draft') {
        return false;
    }
    
    $members = getCommitteeMembersForReport($report['committee_id']);
    $totalMembers = count($members);
    if ($totalMembers === 0) return false;
    
    $signatures = getReportSignatures($reportId);
    
    $approvedCount = 0;
    $dissentedCount = 0;
    
    foreach ($signatures as $sig) {
        if ($sig['status'] === 'Approved') {
            $approvedCount++;
        } elseif ($sig['status'] === 'Dissented') {
            $dissentedCount++;
        }
    }
    
    // Standard majority:
    $requiredCount = floor($totalMembers / 2) + 1; // 1 for 1, 2 for 2 or 3, 3 for 4 or 5, etc.
    
    $newStatus = null;
    if ($approvedCount >= $requiredCount) {
        $newStatus = 'Approved';
    } elseif ($dissentedCount >= $requiredCount) {
        $newStatus = 'Rejected';
    }
    
    if ($newStatus && $newStatus !== $report['status']) {
        $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
        $stmt->bind_param("si", $newStatus, $reportId);
        $stmt->execute();
        $stmt->close();
        
        if (function_exists('logAuditAction')) {
            logAuditAction(null, 'STATUS_CHANGE', 'reports', "Report ID {$reportId} status updated to {$newStatus} based on votes/signatures");
        }
        
        // Notify committee members of status change
        require_once __DIR__ . '/NotificationHelper.php';
        foreach ($members as $member) {
            $title = "Report status updated: {$newStatus}";
            $message = "The committee report '{$report['title']}' has been {$newStatus}.";
            $link = "pages/committee-reports/view.php?id=" . $reportId;
            createNotification($member['user_id'], $title, $message, 'info', 'medium', $link);
        }
        
        return true;
    }
    
    return false;
}
