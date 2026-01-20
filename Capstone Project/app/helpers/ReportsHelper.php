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

    // Total Referrals
    $res = $conn->query("SELECT COUNT(*) as count FROM referrals");
    $stats['referrals'] = $res->fetch_assoc()['count'];

    // Total Action Items
    $res = $conn->query("SELECT COUNT(*) as count FROM tasks");
    $stats['tasks'] = $res->fetch_assoc()['count'];

    // Total Committees
    $res = $conn->query("SELECT COUNT(*) as count FROM committees WHERE is_active = 1");
    $stats['committees'] = $res->fetch_assoc()['count'];

    // Total Users
    $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['users'] = $res->fetch_assoc()['count'];

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
    $table = ($type === 'tasks') ? 'tasks' : (($type === 'referrals') ? 'referrals' : 'meetings');

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
 * Get legislative pipeline cycle time (Referral to Completion)
 */
function getLegislativeCycleTime()
{
    global $conn;

    // Time from referral creation to 'Approved' or 'Enacted' status
    $sql = "SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days
            FROM referrals 
            WHERE status IN ('Approved', 'Enacted', 'Completed')";
    $res = $conn->query($sql);
    $avg_days = $res->fetch_assoc()['avg_days'] ?? 0;

    // Distribution by document type
    $type_sql = "SELECT ld.document_type as type, COUNT(*) as count 
                 FROM referrals r 
                 JOIN legislative_documents ld ON r.document_id = ld.document_id 
                 GROUP BY ld.document_type";
    $type_res = $conn->query($type_sql);
    $types = [];
    while ($row = $type_res->fetch_assoc()) {
        $types[ucfirst($row['type'])] = (int) $row['count'];
    }

    return [
        'avg_cycle_days' => round($avg_days, 1),
        'type_distribution' => $types
    ];
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
