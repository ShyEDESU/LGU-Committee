<?php
/**
 * Meeting Helper Functions
 * Handles all meeting-related database operations
 */

// Require database connection
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AuditHelper.php';

/**
 * Get all meetings with committee and creator information
 */
function getAllMeetings($filters = [])
{
    global $conn;

    // Automatic Status Transition (Lazy Update)
    autoUpdateMeetingStatuses();

    $sql = "SELECT 
                m.*,
                c.committee_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM meetings m
            JOIN committees c ON m.committee_id = c.committee_id
            LEFT JOIN users u ON m.created_by = u.user_id";

    $conditions = [];
    $params = [];
    $types = "";

    // Apply filters
    if (!empty($filters['committee_id'])) {
        $conditions[] = "m.committee_id = ?";
        $params[] = $filters['committee_id'];
        $types .= "i";
    }

    if (!empty($filters['status'])) {
        $conditions[] = "m.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['search'])) {
        $conditions[] = "(m.meeting_title LIKE ? OR c.committee_name LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY m.meeting_date DESC";

    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int) $filters['limit'];
    }

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $meetings = [];
    while ($row = $result->fetch_assoc()) {
        $meetings[] = [
            'id' => $row['meeting_id'],
            'committee_id' => $row['committee_id'],
            'committee_name' => $row['committee_name'],
            'title' => $row['meeting_title'],
            'description' => $row['description'],
            'date' => ($row['meeting_date'] && $row['meeting_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($row['meeting_date'])) : '',
            'time_start' => ($row['meeting_date'] && $row['meeting_date'] !== '0000-00-00 00:00:00') ? date('H:i', strtotime($row['meeting_date'])) : '',
            'time_end' => ($row['meeting_end_time'] && $row['meeting_end_time'] !== '0000-00-00 00:00:00') ? date('H:i', strtotime($row['meeting_end_time'])) : '',
            'venue' => $row['location'],
            'status' => $row['status'],
            'agenda_status' => $row['agenda_status'],
            'is_public' => $row['is_public'],
            'created_by' => $row['created_by_name'],
            'created_date' => $row['created_at']
        ];
    }

    return $meetings;
}

/**
 * Get single meeting by ID
 */
function getMeetingById($id)
{
    global $conn;

    // Automatic Status Transition (Lazy Update)
    autoUpdateMeetingStatuses($id);

    $stmt = $conn->prepare("SELECT 
                m.*,
                c.committee_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM meetings m
            JOIN committees c ON m.committee_id = c.committee_id
            LEFT JOIN users u ON m.created_by = u.user_id
            WHERE m.meeting_id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return [
            'id' => $row['meeting_id'],
            'committee_id' => $row['committee_id'],
            'committee_name' => $row['committee_name'],
            'title' => $row['meeting_title'],
            'description' => $row['description'],
            'date' => ($row['meeting_date'] && $row['meeting_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($row['meeting_date'])) : '',
            'time_start' => ($row['meeting_date'] && $row['meeting_date'] !== '0000-00-00 00:00:00') ? date('H:i', strtotime($row['meeting_date'])) : '',
            'time_end' => ($row['meeting_end_time'] && $row['meeting_end_time'] !== '0000-00-00 00:00:00') ? date('H:i', strtotime($row['meeting_end_time'])) : '',
            'venue' => $row['location'],
            'status' => $row['status'],
            'agenda_status' => $row['agenda_status'],
            'is_public' => $row['is_public'],
            'created_by' => $row['created_by_name'],
            'created_date' => $row['created_at']
        ];
    }

    return null;
}

/**
 * Get meetings by committee
 */
function getMeetingsByCommittee($committeeId)
{
    return getAllMeetings(['committee_id' => $committeeId]);
}

/**
 * Create new meeting
 */
function createMeeting($data)
{
    global $conn;

    $meetingDate = $data['date'] . ' ' . $data['time_start'];
    $meetingEndTime = null;
    if (!empty($data['time_end'])) {
        $meetingEndTime = $data['date'] . ' ' . $data['time_end'];
    }

    $stmt = $conn->prepare("INSERT INTO meetings 
        (committee_id, meeting_title, description, meeting_date, meeting_end_time, location, status, is_public, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $status = $data['status'] ?? 'Scheduled';
    $isPublic = isset($data['is_public']) ? (int) $data['is_public'] : 1;
    $createdBy = $_SESSION['user_id'] ?? 1;

    $stmt->bind_param(
        "issssssii",
        $data['committee_id'],
        $data['title'],
        $data['description'],
        $meetingDate,
        $meetingEndTime,
        $data['venue'],
        $status,
        $isPublic,
        $createdBy
    );

    if ($stmt->execute()) {
        $meetingId = $conn->insert_id;
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'CREATE',
            'meetings',
            "Created meeting '{$data['title']}' for committee ID: {$data['committee_id']}"
        );
        return $meetingId;
    }

    $error = $conn->error;
    error_log("Error creating meeting: " . $error);
    $_SESSION['error_message'] = "Database Error: " . $error;
    return false;
}

/**
 * Update meeting (Dynamic/Partial Support)
 */
function updateMeeting($id, $data)
{
    global $conn;

    $fields = [];
    $params = [];
    $types = "";

    $map = [
        'title' => 'meeting_title',
        'description' => 'description',
        'venue' => 'location',
        'location' => 'location',
        'status' => 'status',
        'agenda_status' => 'agenda_status',
        'is_public' => 'is_public'
    ];

    foreach ($map as $key => $column) {
        if (isset($data[$key])) {
            $fields[] = "$column = ?";
            $params[] = $data[$key];
            $types .= (is_int($data[$key]) || is_bool($data[$key])) ? "i" : "s";
        }
    }

    if (isset($data['date']) && isset($data['time_start'])) {
        $meetingDate = $data['date'] . ' ' . $data['time_start'];
        $fields[] = "meeting_date = ?";
        $params[] = $meetingDate;
        $types .= "s";

        if (isset($data['time_end'])) {
            $meetingEndTime = $data['date'] . ' ' . $data['time_end'];
            $fields[] = "meeting_end_time = ?";
            $params[] = $meetingEndTime;
            $types .= "s";
        }
    }

    if (empty($fields))
        return true;

    $sql = "UPDATE meetings SET " . implode(", ", $fields) . " WHERE meeting_id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}

/**
 * Delete meeting
 */
function deleteMeeting($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM meetings WHERE meeting_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'DELETE',
            'meetings',
            "Deleted meeting ID: $id"
        );
        return true;
    }
    return false;
}

/**
 * Change meeting status
 */
function changeMeetingStatus($id, $status)
{
    return updateMeeting($id, ['status' => $status]);
}

/**
 * Automatically update meeting statuses based on date and time
 */
function autoUpdateMeetingStatuses($id = null)
{
    global $conn;
    $now = date('Y-m-d H:i:s');

    // Case 1: Transition Scheduled -> Ongoing if start time passed
    $sql1 = "UPDATE meetings SET status = 'Ongoing' 
             WHERE status = 'Scheduled' 
             AND meeting_date <= ?";

    // Case 2: Transition Ongoing -> Completed if end time (or start + 2h) passed
    $sql2 = "UPDATE meetings SET status = 'Completed' 
             WHERE status = 'Ongoing' 
             AND (
                (meeting_end_time IS NOT NULL AND meeting_end_time <= ?) OR 
                (meeting_end_time IS NULL AND DATE_ADD(meeting_date, INTERVAL 2 HOUR) <= ?)
             )";

    if ($id) {
        $sql1 .= " AND meeting_id = ?";
        $sql2 .= " AND meeting_id = ?";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("si", $now, $id);
        $stmt1->execute();

        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ssi", $now, $now, $id);
        $stmt2->execute();
    } else {
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $now);
        $stmt1->execute();

        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ss", $now, $now);
        $stmt2->execute();
    }
}

/**
 * Auto-invite committee members to meeting
 */
function autoInviteCommitteeMembers($meetingId, $committeeId)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO meeting_invitations (meeting_id, user_id, status) 
                            SELECT ?, user_id, 'Pending' FROM committee_members 
                            WHERE committee_id = ? AND is_active = 1");
    $stmt->bind_param("ii", $meetingId, $committeeId);
    return $stmt->execute();
}

/**
 * Get meeting items/agenda
 */
function getAgendaItems($meetingId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM agenda_items WHERE meeting_id = ? ORDER BY item_order ASC");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['item_id']; // Alias for UI compatibility
        $items[] = $row;
    }
    return $items;
}

function getAgendaByMeeting($meetingId)
{
    return getAgendaItems($meetingId);
}

/**
 * Add agenda item
 */
function addAgendaItem($meetingId, $data)
{
    global $conn;
    $referralId = $data['referral_id'] ?? null;
    $stmt = $conn->prepare("INSERT INTO agenda_items (meeting_id, referral_id, title, description, duration, presenter, item_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissisi", $meetingId, $referralId, $data['title'], $data['description'], $data['duration'], $data['presenter'], $data['item_order']);
    $success = $stmt->execute();
    if ($success) {
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'CREATE',
            'agenda',
            "Added agenda item to meeting ID: $meetingId - '{$data['title']}'"
        );
    }

    if ($success && $referralId) {
        // Update referral status to 'In Committee'
        if (file_exists(__DIR__ . '/ReferralHelper.php')) {
            require_once __DIR__ . '/ReferralHelper.php';
            if (function_exists('updateReferralStatus')) {
                updateReferralStatus($referralId, 'In Committee');
            }
        }
    }

    return $success;
}

/**
 * Update agenda item
 */
function updateAgendaItem($itemId, $data)
{
    global $conn;
    $fields = [];
    $params = [];
    $types = "";

    $map = [
        'title' => 'title',
        'description' => 'description',
        'duration' => 'duration',
        'presenter' => 'presenter',
        'item_order' => 'item_order',
        'item_number' => 'item_order' // UI uses item_number for sorting
    ];

    foreach ($map as $key => $column) {
        if (isset($data[$key])) {
            $fields[] = "$column = ?";
            $params[] = $data[$key];
            $types .= is_int($data[$key]) ? "i" : "s";
        }
    }

    if (empty($fields))
        return true;

    $sql = "UPDATE agenda_items SET " . implode(", ", $fields) . " WHERE item_id = ?";
    $params[] = $itemId;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

/**
 * Delete agenda item
 */
function deleteAgendaItem($itemId)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM agenda_items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    return $stmt->execute();
}

/**
 * Voting functions
 */
function getVotesByAgenda($meetingId)
{
    global $conn;
    $sql = "SELECT v.*, ai.title as item_title 
            FROM votes v
            JOIN agenda_items ai ON v.agenda_item_id = ai.item_id
            WHERE ai.meeting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $votes = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['vote_id'];
        $votes[] = $row;
    }
    return $votes;
}

function createVote($agendaItemId, $data)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO votes (agenda_item_id, motion_text, voting_method) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $agendaItemId, $data['motion_text'], $data['voting_method']);
    if ($stmt->execute())
        return $conn->insert_id;
    return false;
}

function recordMemberVote($voteId, $userId, $voteValue)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO member_votes (vote_id, user_id, vote) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote = ?");
    $stmt->bind_param("iiss", $voteId, $userId, $voteValue, $voteValue);
    return $stmt->execute();
}

function getVoteResults($voteId)
{
    global $conn;

    // Count votes by value
    $sql = "SELECT vote, COUNT(*) as count FROM member_votes WHERE vote_id = ? GROUP BY vote";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $voteId);
    $stmt->execute();
    $res = $stmt->get_result();

    $stats = ['Yes' => 0, 'No' => 0, 'Abstain' => 0, 'Absent' => 0];
    while ($row = $res->fetch_assoc()) {
        $stats[$row['vote']] = (int) $row['count'];
    }

    // Determine result
    $result = 'Tied';
    if ($stats['Yes'] > $stats['No'])
        $result = 'Passed';
    elseif ($stats['No'] > $stats['Yes'])
        $result = 'Failed';
    elseif ($stats['Yes'] == 0 && $stats['No'] == 0)
        $result = 'Pending';

    return [
        'yes' => $stats['Yes'],
        'no' => $stats['No'],
        'abstain' => $stats['Abstain'],
        'absent' => $stats['Absent'],
        'result' => $result
    ];
}

/**
 * Attendance Functions
 */
function getAttendanceRecords($meetingId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as name, u.email 
                            FROM attendance_records a 
                            JOIN users u ON a.user_id = u.user_id 
                            WHERE a.meeting_id = ?");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function markAttendance($meetingId, $userId, $status)
{
    global $conn;
    $recordedBy = $_SESSION['user_id'] ?? 1;
    $checkIn = ($status === 'present') ? date('Y-m-d H:i:s') : null;

    $stmt = $conn->prepare("INSERT INTO attendance_records (meeting_id, user_id, status, check_in_time, recorded_by) 
                            VALUES (?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE status = ?, check_in_time = ?, recorded_by = ?");
    $stmt->bind_param("iisssssi", $meetingId, $userId, $status, $checkIn, $recordedBy, $status, $checkIn, $recordedBy);
    return $stmt->execute();
}

function getAttendanceStats($meetingId)
{
    global $conn;

    // Total members (from invitations or committee)
    $invitees = getMeetingInvitees($meetingId);
    $totalMembers = count($invitees);

    // Attendance counts
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM attendance_records WHERE meeting_id = ? GROUP BY status");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $counts = ['present' => 0, 'absent' => 0, 'excused' => 0];
    while ($row = $res->fetch_assoc()) {
        $counts[$row['status']] = $row['count'];
    }

    $quorumRequired = ceil($totalMembers / 2);
    $hasQuorum = ($counts['present'] >= $quorumRequired && $totalMembers > 0);
    $attendanceRate = ($totalMembers > 0) ? round(($counts['present'] / $totalMembers) * 100) : 0;

    return [
        'total_members' => $totalMembers,
        'present' => $counts['present'],
        'absent' => $counts['absent'],
        'excused' => $counts['excused'],
        'has_quorum' => $hasQuorum,
        'attendance_rate' => $attendanceRate,
        'quorum_required' => $quorumRequired
    ];
}

/**
 * Meeting Invitees / Assignments
 */
function getMeetingInvitees($meetingId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as name, u.email, u.position, i.status 
                            FROM meeting_invitations i 
                            JOIN users u ON i.user_id = u.user_id 
                            WHERE i.meeting_id = ?");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function syncMeetingInvitees($meetingId, $userIds)
{
    global $conn;

    // Remove existing if not in new list
    if (empty($userIds)) {
        $stmt = $conn->prepare("DELETE FROM meeting_invitations WHERE meeting_id = ?");
        $stmt->bind_param("i", $meetingId);
        return $stmt->execute();
    }

    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $sql = "DELETE FROM meeting_invitations WHERE meeting_id = ? AND user_id NOT IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $params = array_merge([$meetingId], $userIds);
    $types = "i" . str_repeat("i", count($userIds));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // Add new ones
    $stmt = $conn->prepare("INSERT IGNORE INTO meeting_invitations (meeting_id, user_id, status) VALUES (?, ?, 'pending')");
    foreach ($userIds as $userId) {
        $stmt->bind_param("ii", $meetingId, $userId);
        $stmt->execute();
    }

    return true;
}

/**
 * Documents & Minutes
 */
function getMeetingDocuments($meetingId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name 
                            FROM meeting_documents d 
                            LEFT JOIN users u ON d.uploaded_by = u.user_id 
                            WHERE d.meeting_id = ? AND (d.document_type != 'minutes' OR (d.file_path IS NOT NULL AND d.file_path != ''))");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $docs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Map fields for UI compatibility
    return array_map(function ($doc) {
        $doc['name'] = $doc['title'];
        $doc['description'] = $doc['content'] ?? '';
        $doc['uploaded_by'] = $doc['uploaded_by_name'];
        $doc['uploaded_at'] = $doc['created_at'];
        $doc['version'] = '1.0'; // Default version

        $doc['file_size'] = 0;
        if (!empty($doc['file_path'])) {
            $fullPath = __DIR__ . '/../../' . $doc['file_path'];
            if (file_exists($fullPath)) {
                $doc['file_size'] = round(filesize($fullPath) / 1024, 2);
            }
        }

        $doc['id'] = $doc['document_id'];
        $doc['category'] = ucfirst($doc['document_type']);
        return $doc;
    }, $docs);
}

function addMeetingDocument($meetingId, $data, $file = null)
{
    global $conn;
    $uploadedBy = $_SESSION['user_id'] ?? 1;
    $filePath = '';

    // Normalize category to lowercase to match DB ENUM
    $category = strtolower($data['category'] ?? 'supporting_doc');
    // Map 'other' to 'supporting_doc' as it's not in the ENUM but useful for UI
    if ($category === 'other')
        $category = 'supporting_doc';

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/meeting-documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'meeting_' . $meetingId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $filePath = 'uploads/meeting-documents/' . $fileName;
        } else {
            error_log("Failed to move uploaded file to $targetPath");
            return false;
        }
    } elseif ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("File upload error: " . $file['error']);
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO meeting_documents (meeting_id, document_type, title, content, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $meetingId, $category, $data['name'], $data['description'], $filePath, $uploadedBy);
    return $stmt->execute();
}

function deleteMeetingDocument($docId)
{
    global $conn;

    // Get file info first
    $stmt = $conn->prepare("SELECT file_path FROM meeting_documents WHERE document_id = ?");
    $stmt->bind_param("i", $docId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['file_path'])) {
            $fullPath = __DIR__ . '/../../' . $row['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    $stmt = $conn->prepare("DELETE FROM meeting_documents WHERE document_id = ?");
    $stmt->bind_param("i", $docId);
    return $stmt->execute();
}

function getMeetingMinutes($meetingId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) as author 
                            FROM meeting_documents d 
                            LEFT JOIN users u ON d.uploaded_by = u.user_id 
                            WHERE d.meeting_id = ? AND d.document_type = 'minutes' AND (d.file_path IS NULL OR d.file_path = '')
                            ORDER BY d.created_at DESC LIMIT 1");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $data = json_decode($row['content'], true);
        if (!$data) {
            $data = ['content' => $row['content'], 'decisions' => [], 'action_items' => []];
        }
        $data['status'] = $data['status'] ?? 'Draft';
        $data['id'] = $row['document_id'];
        $data['approved_by'] = $data['approved_by'] ?? null;
        $data['approved_at'] = $data['approved_at'] ?? null;
        return $data;
    }
    return null;
}

function saveMinutes($meetingId, $data)
{
    global $conn;
    $uploadedBy = $_SESSION['user_id'] ?? 1;
    $jsonContent = json_encode($data);
    $title = "Minutes for Meeting " . $meetingId;

    // Check if minutes already exist (check most recent)
    $stmt = $conn->prepare("SELECT document_id FROM meeting_documents WHERE meeting_id = ? AND document_type = 'minutes' ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE meeting_documents SET content = ?, uploaded_by = ?, updated_at = NOW() WHERE document_id = ?");
        $stmt->bind_param("sii", $jsonContent, $uploadedBy, $row['document_id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO meeting_documents (meeting_id, document_type, title, content, uploaded_by) VALUES (?, 'minutes', ?, ?, ?)");
        $stmt->bind_param("issi", $meetingId, $title, $jsonContent, $uploadedBy);
    }

    $success = $stmt->execute();
    if (!$success) {
        error_log("Failed to save minutes for meeting $meetingId: " . $conn->error);
    }
    return $success;
}

function approveMinutes($meetingId)
{
    $minutes = getMeetingMinutes($meetingId);
    if ($minutes) {
        $minutes['status'] = 'Approved';
        $minutes['approved_by'] = $_SESSION['user_name'] ?? 'System';
        $minutes['approved_at'] = date('Y-m-d H:i:s');
        return saveMinutes($meetingId, $minutes);
    }
    return false;
}

/**
 * Deliberation functions
 */
function createDeliberation($agendaItemId, $data)
{
    global $conn;
    $userId = $_SESSION['user_id'] ?? 1;
    $stmt = $conn->prepare("INSERT INTO deliberations (agenda_item_id, speaker, notes, duration, recorded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $agendaItemId, $data['speaker'], $data['notes'], $data['duration'], $userId);
    return $stmt->execute();
}

function getDeliberationsByAgenda($meetingId)
{
    global $conn;
    $sql = "SELECT d.*, ai.title as item_title 
            FROM deliberations d
            JOIN agenda_items ai ON d.agenda_item_id = ai.item_id
            WHERE ai.meeting_id = ?
            ORDER BY d.timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $delibs = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['deliberation_id'];
        $delibs[] = $row;
    }
    return $delibs;
}

/**
 * Template functions
 */
function getAllAgendaTemplates()
{
    global $conn;
    $result = $conn->query("SELECT *, template_id FROM agenda_templates ORDER BY name ASC");
    $templates = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Fetch items for this template
            $row['items'] = getTemplateItems($row['template_id']);
            $templates[] = $row;
        }
    }
    return $templates;
}

/**
 * Get items for a template
 */
function getTemplateItems($templateId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM agenda_template_items WHERE template_id = ? ORDER BY item_order ASC");
    $stmt->bind_param("i", $templateId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

/**
 * Apply a template to a meeting agenda
 */
function applyTemplateToAgenda($meetingId, $templateId)
{
    global $conn;
    $items = getTemplateItems($templateId);
    if (empty($items)) {
        return false;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO agenda_items (meeting_id, title, description, duration, item_type, item_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->bind_param(
                "issisi",
                $meetingId,
                $item['title'],
                $item['description'],
                $item['duration'],
                $item['item_type'],
                $item['item_order']
            );
            $stmt->execute();
        }

        // Update meeting status if it was 'None'
        $conn->query("UPDATE meetings SET agenda_status = 'Draft' WHERE meeting_id = $meetingId AND (agenda_status IS NULL OR agenda_status = 'None' OR agenda_status = '')");

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error applying template: " . $e->getMessage());
        return false;
    }
}

/**
 * Create new agenda template
 */
function createAgendaTemplate($data)
{
    global $conn;
    $conn->begin_transaction();

    try {
        $name = $data['name'];
        $description = $data['description'] ?? '';
        $committeeType = $data['committee_type'] ?? 'General';
        $createdBy = $_SESSION['user_id'] ?? null;

        $stmt = $conn->prepare("INSERT INTO agenda_templates (name, description, committee_type, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $description, $committeeType, $createdBy);

        if (!$stmt->execute()) {
            throw new Exception("Failed to create template: " . $stmt->error);
        }

        $templateId = $conn->insert_id;

        if (!empty($data['items'])) {
            $stmtItem = $conn->prepare("INSERT INTO agenda_template_items (template_id, title, description, duration, item_type, item_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($data['items'] as $index => $item) {
                $itemOrder = $index + 1;
                $iTitle = $item['title'];
                $iDesc = $item['description'] ?? '';
                $iDur = $item['duration'] ?? 15;
                $iType = $item['item_type'] ?? 'Discussion';
                $stmtItem->bind_param("issisi", $templateId, $iTitle, $iDesc, $iDur, $iType, $itemOrder);
                $stmtItem->execute();
            }
        }

        $conn->commit();
        return $templateId;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating agenda template: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete agenda template
 */
function deleteAgendaTemplate($id)
{
    global $conn;
    // items will be deleted by CASCADE if FOREIGN KEY is set correctly
    $stmt = $conn->prepare("DELETE FROM agenda_templates WHERE template_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * Comments
 */
function addAgendaComment($meetingId, $itemId, $comment)
{
    global $conn;
    $userId = $_SESSION['user_id'] ?? 1;
    $stmt = $conn->prepare("INSERT INTO agenda_comments (meeting_id, item_id, author_id, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $meetingId, $itemId, $userId, $comment);
    return $stmt->execute();
}

function getAgendaComments($meetingId, $itemId = null)
{
    global $conn;
    if ($itemId) {
        $stmt = $conn->prepare("SELECT ac.*, CONCAT(u.first_name, ' ', u.last_name) as author_name, u.profile_picture FROM agenda_comments ac JOIN users u ON ac.author_id = u.user_id WHERE ac.item_id = ? ORDER BY ac.created_at DESC");
        $stmt->bind_param("i", $itemId);
    } else {
        $stmt = $conn->prepare("SELECT ac.*, CONCAT(u.first_name, ' ', u.last_name) as author_name, u.profile_picture FROM agenda_comments ac JOIN users u ON ac.author_id = u.user_id WHERE ac.meeting_id = ? ORDER BY ac.created_at DESC");
        $stmt->bind_param("i", $meetingId);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Distribution
 */
function changeMeetingAgendaStatus($meetingId, $status)
{
    return updateMeeting($meetingId, ['agenda_status' => $status]);
}

function logAgendaDistribution($meetingId, $recipients, $method)
{
    global $conn;
    $userId = $_SESSION['user_id'] ?? null;
    $stmt = $conn->prepare("INSERT INTO agenda_distribution (meeting_id, method, distributed_by) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $meetingId, $method, $userId);
    if ($stmt->execute()) {
        $distId = $conn->insert_id;
        $stmtRec = $conn->prepare("INSERT INTO agenda_distribution_recipients (distribution_id, member_id) VALUES (?, ?)");
        foreach ($recipients as $memberId) {
            $stmtRec->bind_param("ii", $distId, $memberId);
            $stmtRec->execute();
        }
        return $distId;
    }
    return false;
}

function getDistributionLog($meetingId)
{
    global $conn;
    // We assume the ID column in agenda_distribution is either 'id' or 'distribution_id'
    // Let's use a robust approach
    $sql = "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) as distributed_by_name 
            FROM agenda_distribution d 
            LEFT JOIN users u ON d.distributed_by = u.user_id 
            WHERE d.meeting_id = ? 
            ORDER BY d.distributed_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $distId = $row['id'] ?? $row['distribution_id'] ?? 0;
        if ($distId) {
            $stmtRec = $conn->prepare("SELECT member_id FROM agenda_distribution_recipients WHERE distribution_id = ?");
            $stmtRec->bind_param("i", $distId);
            $stmtRec->execute();
            $resRec = $stmtRec->get_result();
            $recipients = [];
            while ($rec = $resRec->fetch_assoc()) {
                $recipients[] = $rec['member_id'];
            }
            $row['recipients'] = $recipients;
        } else {
            $row['recipients'] = [];
        }
        $logs[] = $row;
    }
    return $logs;
}

/**
 * Cross-module functions
 */
function getAgendasByCommittee($committeeId)
{
    global $conn;
    // Join with agenda_items and group by meeting_id to ensure we only return meetings that actually HAVE agenda items
    $sql = "SELECT DISTINCT m.* 
            FROM meetings m 
            INNER JOIN agenda_items ai ON m.meeting_id = ai.meeting_id
            WHERE m.committee_id = ? 
            ORDER BY m.meeting_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $agendas = [];
    while ($row = $result->fetch_assoc()) {
        // Since we did an INNER JOIN, we know items exist, but we still use the helper for consistency
        $items = getAgendaItems($row['meeting_id']);
        $agendas[] = [
            'meeting' => [
                'id' => $row['meeting_id'],
                'title' => $row['meeting_title'],
                'date' => $row['meeting_date'],
                'agenda_status' => $row['agenda_status'],
                'status' => $row['status']
            ],
            'item_count' => count($items)
        ];
    }
    return $agendas;
}

/**
 * Get all active votes for a meeting
 */
function getActiveVotesByMeeting($meetingId)
{
    global $conn;
    $sql = "SELECT v.*, v.vote_id as id, ai.title as item_title 
            FROM votes v
            JOIN agenda_items ai ON v.agenda_item_id = ai.item_id
            WHERE ai.meeting_id = ? AND v.result = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get a specific member's vote for a motion
 */
function getMemberVote($voteId, $userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT vote FROM member_votes WHERE vote_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $voteId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['vote'];
    }
    return null;
}

/**
 * Get all agenda items for a specific meeting or all meetings
 */
function getAllAgendaItems($meetingId = null)
{
    global $conn;
    $sql = "SELECT item_id as id, meeting_id, title, description, item_order, item_type, status 
            FROM agenda_items";
    if ($meetingId) {
        $sql .= " WHERE meeting_id = " . (int) $meetingId;
    }
    $sql .= " ORDER BY item_order ASC";

    $result = $conn->query($sql);
    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    return $items;
}

/**
 * Get a specific agenda item by ID
 */
function getAgendaItemById($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT ai.item_id as id, ai.*, m.meeting_title as meeting_title 
                           FROM agenda_items ai 
                           JOIN meetings m ON ai.meeting_id = m.meeting_id 
                           WHERE ai.item_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

?>