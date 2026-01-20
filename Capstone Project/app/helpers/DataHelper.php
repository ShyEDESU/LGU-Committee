<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AuditHelper.php';

// ==========================================
// CORE SYSTEM HELPER
// ==========================================

/**
 * Get all action items (tasks) from database
 */
function getAllActionItems()
{
    global $conn;
    $sql = "SELECT t.*, c.committee_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM tasks t
            LEFT JOIN committees c ON t.committee_id = c.committee_id
            LEFT JOIN users u ON t.assigned_to = u.user_id
            ORDER BY t.due_date ASC";
    $result = $conn->query($sql);
    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row['task_id'];
            $items[] = $row;
        }
    }
    return $items;
}

/**
 * Get action items by status
 */
function getActionItemsByStatus($status)
{
    global $conn;
    $sql = "SELECT t.*, c.committee_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM tasks t
            LEFT JOIN committees c ON t.committee_id = c.committee_id
            LEFT JOIN users u ON t.assigned_to = u.user_id
            WHERE t.status = ?
            ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['task_id'];
        $items[] = $row;
    }
    return $items;
}

/**
 * Get statistics for action items
 */
function getActionItemStatistics()
{
    global $conn;
    $stats = [
        'total' => 0,
        'avg_progress' => 0,
        'completion_rate' => 0,
        'overdue' => 0,
        'upcoming_7_days' => 0,
        'upcoming_14_days' => 0,
        'upcoming_30_days' => 0,
        'by_status' => [
            'To Do' => 0,
            'In Progress' => 0,
            'Done' => 0,
            'On Hold' => 0,
            'Cancelled' => 0
        ],
        'by_priority' => [
            'Low' => 0,
            'Medium' => 0,
            'High' => 0,
            'Urgent' => 0
        ]
    ];

    $res = $conn->query("SELECT status, priority, progress, COUNT(*) as count FROM tasks GROUP BY status, priority, progress");
    $totalProgress = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $count = (int) $row['count'];
            $stats['total'] += $count;

            $status = $row['status'];
            if (isset($stats['by_status'][$status])) {
                $stats['by_status'][$status] += $count;
            } elseif ($status === 'Pending') {
                $stats['by_status']['To Do'] += $count;
            }

            $priority = ucfirst(strtolower($row['priority']));
            if ($priority === 'Normal')
                $priority = 'Medium';
            if (isset($stats['by_priority'][$priority])) {
                $stats['by_priority'][$priority] += $count;
            }

            $totalProgress += ((int) $row['progress'] * $count);
        }
    }

    if ($stats['total'] > 0) {
        $stats['avg_progress'] = round($totalProgress / $stats['total'], 1);
        $stats['completion_rate'] = round(($stats['by_status']['Done'] / $stats['total']) * 100, 1);
    }

    // Deadline stats
    $today = date('Y-m-d');
    $stats['overdue'] = (int) $conn->query("SELECT COUNT(*) FROM tasks WHERE due_date < '$today' AND status != 'Done'")->fetch_row()[0];
    $stats['upcoming_7_days'] = (int) $conn->query("SELECT COUNT(*) FROM tasks WHERE due_date >= '$today' AND due_date <= DATE_ADD('$today', INTERVAL 7 DAY) AND status != 'Done'")->fetch_row()[0];
    $stats['upcoming_14_days'] = (int) $conn->query("SELECT COUNT(*) FROM tasks WHERE due_date >= '$today' AND due_date <= DATE_ADD('$today', INTERVAL 14 DAY) AND status != 'Done'")->fetch_row()[0];
    $stats['upcoming_30_days'] = (int) $conn->query("SELECT COUNT(*) FROM tasks WHERE due_date >= '$today' AND due_date <= DATE_ADD('$today', INTERVAL 30 DAY) AND status != 'Done'")->fetch_row()[0];

    return $stats;
}

/**
 * Get overdue action items
 */
function getOverdueActionItems()
{
    global $conn;
    $today = date('Y-m-d');
    $sql = "SELECT t.*, c.committee_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name 
            FROM tasks t 
            LEFT JOIN committees c ON t.committee_id = c.committee_id 
            LEFT JOIN users u ON t.assigned_to = u.user_id 
            WHERE t.due_date < ? AND t.status != 'Done' 
            ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get upcoming action items within N days
 */
function getUpcomingActionItems($days = 7)
{
    global $conn;
    $today = date('Y-m-d');
    $ahead = date('Y-m-d', strtotime("+$days days"));
    $sql = "SELECT t.*, c.committee_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name 
            FROM tasks t 
            LEFT JOIN committees c ON t.committee_id = c.committee_id 
            LEFT JOIN users u ON t.assigned_to = u.user_id 
            WHERE t.due_date >= ? AND t.due_date <= ? AND t.status != 'Done' 
            ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $today, $ahead);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get action item by ID
 */
function getActionItemById($id)
{
    global $conn;
    $sql = "SELECT t.*, c.committee_name, t.referral_id, 
                   CONCAT(u1.first_name, ' ', u1.last_name) as assigned_to_name,
                   CONCAT(u2.first_name, ' ', u2.last_name) as creator_name
            FROM tasks t 
            LEFT JOIN committees c ON t.committee_id = c.committee_id 
            LEFT JOIN users u1 ON t.assigned_to = u1.user_id
            LEFT JOIN users u2 ON t.created_by = u2.user_id
            WHERE t.task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $row['id'] = $row['task_id'];
        return $row;
    }
    return null;
}

/**
 * Get action items assigned to a specific user
 */
function getActionItemsByAssignee($userId)
{
    global $conn;
    $sql = "SELECT t.*, c.committee_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM tasks t 
            LEFT JOIN committees c ON t.committee_id = c.committee_id 
            LEFT JOIN users u ON t.assigned_to = u.user_id
            WHERE t.assigned_to = ? 
            ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['task_id'];
        $items[] = $row;
    }
    return $items;
}

/**
 * Create a new action item
 */
function createActionItem($data)
{
    global $conn;

    $mapping = [
        'title' => 's',
        'description' => 's',
        'committee_id' => 'i',
        'assigned_to' => 'i',
        'task_type' => 's',
        'category' => 's',
        'meeting_id' => 'i',
        'agenda_item_id' => 'i',
        'document_id' => 'i',
        'referral_id' => 'i',
        'priority' => 's',
        'status' => 's',
        'progress' => 'i',
        'due_date' => 's',
        'tags' => 's',
        'estimated_hours' => 'd',
        'notes' => 's',
        'created_by' => 'i'
    ];

    $fields = [];
    $placeholders = [];
    $types = "";
    $values = [];

    // Set defaults/pre-process
    $data['task_type'] = $data['task_type'] ?? 'action_item';
    $data['category'] = $data['category'] ?? 'General';
    $data['priority'] = strtolower($data['priority'] ?? 'medium');
    $data['status'] = $data['status'] ?? 'To Do';
    $data['progress'] = (isset($data['progress'])) ? (int) $data['progress'] : 0;
    $data['due_date'] = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d');
    $data['created_by'] = $_SESSION['user_id'] ?? 1;

    if (isset($data['tags']) && is_array($data['tags'])) {
        $data['tags'] = json_encode($data['tags']);
    }

    foreach ($mapping as $key => $type) {
        $dbKey = ($key === 'meeting_id') ? 'related_meeting_id' :
            (($key === 'document_id') ? 'related_document_id' : $key);

        $fields[] = $dbKey;
        $placeholders[] = "?";
        $types .= $type;

        $val = $data[$key] ?? null;
        if ($type === 'i' || $type === 'd') {
            if ($val === '' || $val === null)
                $val = null;
        }
        $values[] = $val;
    }

    $sql = "INSERT INTO tasks (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        $taskId = $conn->insert_id;
        logAuditAction($_SESSION['user_id'] ?? null, 'CREATE', 'tasks', "Created action item: '{$data['title']}'");
        return $taskId;
    }
    error_log("Error creating task: " . $stmt->error);
    return false;
}

/**
 * Update an action item
 */
function updateActionItem($id, $data)
{
    global $conn;
    $fields = [];
    $types = "";
    $values = [];

    $mapping = [
        'title' => 's',
        'description' => 's',
        'committee_id' => 'i',
        'assigned_to' => 'i',
        'task_type' => 's',
        'category' => 's',
        'meeting_id' => 'i',
        'related_meeting_id' => 'i',
        'agenda_item_id' => 'i',
        'document_id' => 'i',
        'related_document_id' => 'i',
        'referral_id' => 'i',
        'priority' => 's',
        'status' => 's',
        'progress' => 'i',
        'due_date' => 's',
        'tags' => 's',
        'estimated_hours' => 'd',
        'actual_hours' => 'd',
        'notes' => 's',
        'is_recurring' => 'i',
        'completed_at' => 's'
    ];

    foreach ($mapping as $key => $type) {
        $dbKey = ($key === 'meeting_id') ? 'related_meeting_id' :
            (($key === 'document_id') ? 'related_document_id' : $key);

        if (isset($data[$key])) {
            $fields[] = "$dbKey = ?";
            $val = $data[$key];
            if ($type === 's' && is_array($val))
                $val = json_encode($val);
            if ($type === 'i' || $type === 'd') {
                if ($val === '' || $val === null)
                    $val = null;
            }
            $values[] = $val;
            $types .= $type;
        }
    }

    if (empty($fields))
        return false;

    $sql = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE task_id = ?";
    $types .= "i";
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    if ($stmt->execute()) {
        logAuditAction($_SESSION['user_id'] ?? null, 'UPDATE', 'tasks', "Updated action item ID: $id");
        return true;
    }
    error_log("Error updating task: " . $stmt->error);
    return false;
}

/**
 * Delete an action item
 */
function deleteActionItem($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        logAuditAction($_SESSION['user_id'] ?? null, 'DELETE', 'tasks', "Deleted action item ID: $id");
        return true;
    }
    return false;
}

/**
 * Get all reports from database
 */
function getAllReports()
{
    global $conn;
    $sql = "SELECT r.*, c.committee_name FROM reports r LEFT JOIN committees c ON r.committee_id = c.committee_id ORDER BY r.created_at DESC";
    $result = $conn->query($sql);
    $reports = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row['report_id'];
            $reports[] = $row;
        }
    }
    return $reports;
}

function getActionItemsByCommittee($committeeId)
{
    global $conn;
    $sql = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.user_id
            WHERE t.committee_id = ?
            ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['task_id'];
        $items[] = $row;
    }
    return $items;
}

/**
 * Create a new report
 */
function createReport($data)
{
    global $conn;
    $committeeId = $data['committee_id'];
    $title = $data['title'];
    $type = $data['type'] ?? 'Committee Report';
    $content = $data['content'] ?? '';
    $createdBy = $_SESSION['user_id'] ?? 1;

    $stmt = $conn->prepare("INSERT INTO reports (committee_id, title, report_type, content, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $committeeId, $title, $type, $content, $createdBy);

    if ($stmt->execute()) {
        $reportId = $conn->insert_id;
        logAuditAction($_SESSION['user_id'] ?? null, 'CREATE', 'reports', "Created report: '{$title}'");
        return $reportId;
    }
    return false;
}

// ==========================================
// INTEGRATION HELPERS
// ==========================================

function getReportsByCommittee($committeeId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM reports WHERE committee_id = ?");
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['report_id'];
        $reports[] = $row;
    }
    return $reports;
}

function getActionItemsByMeeting($meetingId)
{
    global $conn;
    // Assuming meeting_id might be stored in a linked field or we filter by category
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE description LIKE ?");
    $term = "%Meeting ID: $meetingId%";
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['task_id'];
        $items[] = $row;
    }
    return $items;
}

require_once __DIR__ . '/ReferralHelper.php';
?>