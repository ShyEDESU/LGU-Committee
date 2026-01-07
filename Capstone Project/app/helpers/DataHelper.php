<?php
/**
 * Meeting Data Handler
 * Manages meeting data in session storage
 */

// Initialize meetings in session
if (!isset($_SESSION['meetings'])) {
    $_SESSION['meetings'] = [
        [
            'id' => 1,
            'committee_id' => 1,
            'committee_name' => 'Committee on Finance',
            'title' => 'Q4 Budget Review',
            'description' => 'Discussion on Q4 Budget Allocation and Revenue Enhancement Strategies',
            'date' => '2025-12-15',
            'time_start' => '14:00',
            'time_end' => '16:00',
            'venue' => 'City Hall Conference Room A',
            'status' => 'Scheduled',
            'is_public' => true,
            'created_by' => 'Admin User',
            'created_date' => '2025-11-01'
        ],
        [
            'id' => 2,
            'committee_id' => 2,
            'committee_name' => 'Committee on Health',
            'title' => 'Healthcare Facilities Review',
            'description' => 'Review of Healthcare Facilities and Public Health Programs Implementation',
            'date' => '2025-12-16',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'venue' => 'City Hall Conference Room B',
            'status' => 'Scheduled',
            'is_public' => true,
            'created_by' => 'Admin User',
            'created_date' => '2025-11-05'
        ],
        [
            'id' => 3,
            'committee_id' => 3,
            'committee_name' => 'Committee on Education',
            'title' => 'School Infrastructure Planning',
            'description' => 'Discussion on School Building Improvements and Educational Technology',
            'date' => '2025-12-10',
            'time_start' => '13:00',
            'time_end' => '15:00',
            'venue' => 'City Hall Main Hall',
            'status' => 'Completed',
            'is_public' => true,
            'created_by' => 'Admin User',
            'created_date' => '2025-10-20'
        ]
    ];
}

// Initialize agendas
if (!isset($_SESSION['agendas'])) {
    $_SESSION['agendas'] = [
        ['id' => 1, 'meeting_id' => 1, 'item_number' => 1, 'title' => 'Call to Order', 'description' => '', 'duration' => 5],
        ['id' => 2, 'meeting_id' => 1, 'item_number' => 2, 'title' => 'Budget Presentation', 'description' => 'Q4 budget overview', 'duration' => 30],
        ['id' => 3, 'meeting_id' => 1, 'item_number' => 3, 'title' => 'Discussion', 'description' => 'Open floor discussion', 'duration' => 60],
    ];
}

// Initialize referrals
if (!isset($_SESSION['referrals'])) {
    $_SESSION['referrals'] = [
        [
            'id' => 1,
            'committee_id' => 1,
            'committee_name' => 'Committee on Finance',
            'title' => 'Ordinance No. 2025-001',
            'type' => 'Ordinance',
            'description' => 'An ordinance appropriating funds for infrastructure projects',
            'priority' => 'High',
            'status' => 'Pending',
            'date_received' => '2025-11-15',
            'deadline' => '2025-12-31',
            'assigned_to' => 'Hon. Maria Santos',
            'submitted_by' => 'City Mayor',
            'submitted_date' => '2025-11-15'
        ],
        [
            'id' => 2,
            'committee_id' => 2,
            'committee_name' => 'Committee on Health',
            'title' => 'Resolution No. 2025-045',
            'type' => 'Resolution',
            'description' => 'Resolution supporting mental health programs',
            'priority' => 'Medium',
            'status' => 'Under Review',
            'date_received' => '2025-11-20',
            'deadline' => '2026-01-15',
            'assigned_to' => 'Hon. Juan Dela Cruz',
            'submitted_by' => 'Health Department',
            'submitted_date' => '2025-11-20'
        ]
    ];
}

// Initialize action items
if (!isset($_SESSION['action_items'])) {
    $_SESSION['action_items'] = [
        [
            'id' => 1,
            'meeting_id' => 1,  // Finance Committee meeting
            'title' => 'Prepare budget report',
            'description' => 'Compile Q4 financial data',
            'assigned_to' => 'Finance Staff',
            'due_date' => '2025-12-20',
            'priority' => 'High',
            'status' => 'In Progress'
        ],
        [
            'id' => 2,
            'meeting_id' => 2,  // Health Committee meeting
            'title' => 'Schedule facility inspection',
            'description' => 'Coordinate with health department',
            'assigned_to' => 'Health Committee Staff',
            'due_date' => '2025-12-18',
            'priority' => 'Medium',
            'status' => 'Pending'
        ]
    ];
}

// Initialize reports
if (!isset($_SESSION['reports'])) {
    $_SESSION['reports'] = [
        [
            'id' => 1,
            'committee_id' => 1,
            'committee_name' => 'Committee on Finance',
            'title' => 'Annual Financial Report 2024',
            'type' => 'Annual Report',
            'date_created' => '2025-01-15',
            'status' => 'Published',
            'created_by' => 'Admin User'
        ]
    ];
}

function getAllMeetings()
{
    return $_SESSION['meetings'] ?? [];
}

function getMeetingById($id)
{
    $meetings = getAllMeetings();
    foreach ($meetings as $meeting) {
        if ($meeting['id'] == $id)
            return $meeting;
    }
    return null;
}

function createMeeting($data)
{
    $meetings = getAllMeetings();
    $newId = empty($meetings) ? 1 : max(array_column($meetings, 'id')) + 1;

    $newMeeting = [
        'id' => $newId,
        'committee_id' => $data['committee_id'],
        'committee_name' => $data['committee_name'],
        'title' => $data['title'],
        'description' => $data['description'] ?? '',
        'date' => $data['date'],
        'time_start' => $data['time_start'],
        'time_end' => $data['time_end'],
        'venue' => $data['venue'],
        'status' => 'Scheduled',
        'is_public' => $data['is_public'] ?? true,
        'created_by' => $_SESSION['user_name'] ?? 'User',
        'created_date' => date('Y-m-d')
    ];

    $_SESSION['meetings'][] = $newMeeting;
    return $newId;
}

/**
 * Update meeting
 */
function updateMeeting($id, $data)
{
    $meetings = &$_SESSION['meetings'];
    foreach ($meetings as &$meeting) {
        if ($meeting['id'] == $id) {
            $meeting['committee_id'] = $data['committee_id'];
            $meeting['committee_name'] = $data['committee_name'];
            $meeting['title'] = $data['title'];
            $meeting['description'] = $data['description'] ?? '';
            $meeting['date'] = $data['date'];
            $meeting['time_start'] = $data['time_start'];
            $meeting['time_end'] = $data['time_end'] ?? '';
            $meeting['venue'] = $data['venue'];
            $meeting['is_public'] = $data['is_public'] ?? true;
            return true;
        }
    }
    return false;
}

/**
 * Delete meeting
 */
function deleteMeeting($id)
{
    $meetings = &$_SESSION['meetings'];
    foreach ($meetings as $key => $meeting) {
        if ($meeting['id'] == $id) {
            unset($meetings[$key]);
            $_SESSION['meetings'] = array_values($meetings);
            return true;
        }
    }
    return false;
}

function getAllReferrals()
{
    return $_SESSION['referrals'] ?? [];
}

function getReferralById($id)
{
    $referrals = getAllReferrals();
    foreach ($referrals as $ref) {
        if ($ref['id'] == $id)
            return $ref;
    }
    return null;
}

function createReferral($data)
{
    $referrals = getAllReferrals();
    $newId = empty($referrals) ? 1 : max(array_column($referrals, 'id')) + 1;

    $_SESSION['referrals'][] = [
        'id' => $newId,
        'committee_id' => $data['committee_id'],
        'committee_name' => $data['committee_name'],
        'title' => $data['title'],
        'description' => $data['description'] ?? '',
        'priority' => $data['priority'] ?? 'Medium',
        'status' => 'Pending',
        'date_received' => date('Y-m-d'),
        'deadline' => $data['deadline'] ?? '',
        'assigned_to' => $data['assigned_to'] ?? ''
    ];

    return $newId;
}

/**
 * Update referral
 */
function updateReferral($id, $data)
{
    $referrals = &$_SESSION['referrals'];
    foreach ($referrals as &$ref) {
        if ($ref['id'] == $id) {
            $ref['committee_id'] = $data['committee_id'];
            $ref['committee_name'] = $data['committee_name'];
            $ref['title'] = $data['title'];
            $ref['description'] = $data['description'] ?? '';
            $ref['priority'] = $data['priority'] ?? 'Medium';
            $ref['deadline'] = $data['deadline'] ?? '';
            $ref['assigned_to'] = $data['assigned_to'] ?? '';
            if (isset($data['status'])) {
                $ref['status'] = $data['status'];
            }
            return true;
        }
    }
    return false;
}

/**
 * Delete referral
 */
function deleteReferral($id)
{
    $referrals = &$_SESSION['referrals'];
    foreach ($referrals as $key => $ref) {
        if ($ref['id'] == $id) {
            unset($referrals[$key]);
            $_SESSION['referrals'] = array_values($referrals);
            return true;
        }
    }
    return false;
}

function getAllActionItems()
{
    return $_SESSION['action_items'] ?? [];
}

function getActionItemById($id)
{
    $items = getAllActionItems();
    foreach ($items as $item) {
        if ($item['id'] == $id)
            return $item;
    }
    return null;
}

function createActionItem($data)
{
    $items = getAllActionItems();
    $newId = empty($items) ? 1 : max(array_column($items, 'id')) + 1;

    $_SESSION['action_items'][] = [
        'id' => $newId,
        'meeting_id' => $data['meeting_id'] ?? null,
        'title' => $data['title'],
        'description' => $data['description'] ?? '',
        'assigned_to' => $data['assigned_to'] ?? '',
        'due_date' => $data['due_date'] ?? '',
        'priority' => $data['priority'] ?? 'Medium',
        'status' => 'Pending'
    ];

    return $newId;
}

/**
 * Update action item
 */
function updateActionItem($id, $data)
{
    $items = &$_SESSION['action_items'];
    foreach ($items as &$item) {
        if ($item['id'] == $id) {
            $item['meeting_id'] = $data['meeting_id'] ?? null;
            $item['title'] = $data['title'];
            $item['description'] = $data['description'] ?? '';
            $item['assigned_to'] = $data['assigned_to'] ?? '';
            $item['due_date'] = $data['due_date'] ?? '';
            $item['priority'] = $data['priority'] ?? 'Medium';
            if (isset($data['status'])) {
                $item['status'] = $data['status'];
            }
            return true;
        }
    }
    return false;
}

/**
 * Delete action item
 */
function deleteActionItem($id)
{
    $items = &$_SESSION['action_items'];
    foreach ($items as $key => $item) {
        if ($item['id'] == $id) {
            unset($items[$key]);
            $_SESSION['action_items'] = array_values($items);
            return true;
        }
    }
    return false;
}

function getAllReports()
{
    return $_SESSION['reports'] ?? [];
}

function getReportById($id)
{
    $reports = getAllReports();
    foreach ($reports as $report) {
        if ($report['id'] == $id)
            return $report;
    }
    return null;
}

/**
 * Create report
 */
function createReport($data)
{
    $reports = getAllReports();
    $newId = empty($reports) ? 1 : max(array_column($reports, 'id')) + 1;

    $_SESSION['reports'][] = [
        'id' => $newId,
        'committee_id' => $data['committee_id'],
        'committee_name' => $data['committee_name'],
        'title' => $data['title'],
        'type' => $data['type'] ?? 'Committee Report',
        'date_created' => date('Y-m-d'),
        'status' => 'Draft',
        'created_by' => $_SESSION['user_name'] ?? 'User'
    ];

    return $newId;
}

/**
 * Update report
 */
function updateReport($id, $data)
{
    $reports = &$_SESSION['reports'];
    foreach ($reports as &$report) {
        if ($report['id'] == $id) {
            $report['committee_id'] = $data['committee_id'];
            $report['committee_name'] = $data['committee_name'];
            $report['title'] = $data['title'];
            $report['type'] = $data['type'] ?? 'Committee Report';
            if (isset($data['status'])) {
                $report['status'] = $data['status'];
            }
            return true;
        }
    }
    return false;
}

/**
 * Delete report
 */
function deleteReport($id)
{
    $reports = &$_SESSION['reports'];
    foreach ($reports as $key => $report) {
        if ($report['id'] == $id) {
            unset($reports[$key]);
            $_SESSION['reports'] = array_values($reports);
            return true;
        }
    }
    return false;
}

// ==========================================
// QUERY FUNCTIONS FOR CROSS-MODULE INTEGRATION
// ==========================================

/**
 * Get meetings by committee
 */
function getMeetingsByCommittee($committeeId)
{
    $meetings = getAllMeetings();
    return array_filter($meetings, function ($meeting) use ($committeeId) {
        return $meeting['committee_id'] == $committeeId;
    });
}

/**
 * Get referrals by committee
 */
function getReferralsByCommittee($committeeId)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($committeeId) {
        return $ref['committee_id'] == $committeeId;
    });
}

/**
 * Get referrals by status
 */
function getReferralsByStatus($status)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($status) {
        return $ref['status'] === $status;
    });
}

/**
 * Get action items by meeting
 */
function getActionItemsByMeeting($meetingId)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($meetingId) {
        return $item['meeting_id'] == $meetingId;
    });
}

/**
 * Get action items by assignee
 */
function getActionItemsByAssignee($assignee)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($assignee) {
        return stripos($item['assigned_to'], $assignee) !== false;
    });
}

/**
 * Get action items by status
 */
function getActionItemsByStatus($status)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($status) {
        return $item['status'] === $status;
    });
}

/**
 * Get reports by committee
 */
function getReportsByCommittee($committeeId)
{
    $reports = getAllReports();
    return array_filter($reports, function ($report) use ($committeeId) {
        return $report['committee_id'] == $committeeId;
    });
}

/**
 * Get reports by status
 */
function getReportsByStatus($status)
{
    $reports = getAllReports();
    return array_filter($reports, function ($report) use ($status) {
        return $report['status'] === $status;
    });
}

// ==========================================
// AGENDA FUNCTIONS
// ==========================================

/**
 * Get all agendas
 */
function getAllAgendas()
{
    return $_SESSION['agendas'] ?? [];
}

/**
 * Get agenda by ID
 */
function getAgendaById($id)
{
    $agendas = getAllAgendas();
    foreach ($agendas as $agenda) {
        if ($agenda['id'] == $id) {
            return $agenda;
        }
    }
    return null;
}

/**
 * Get agenda by meeting
 */
function getAgendaByMeeting($meetingId)
{
    $agendas = getAllAgendas();
    return array_filter($agendas, function ($agenda) use ($meetingId) {
        return $agenda['meeting_id'] == $meetingId;
    });
}

/**
 * Get agenda items
 */
function getAgendaItems($meetingId)
{
    return getAgendaByMeeting($meetingId);
}

/**
 * Create agenda
 */
function createAgenda($data)
{
    $agendas = getAllAgendas();
    $newId = empty($agendas) ? 1 : max(array_column($agendas, 'id')) + 1;

    $_SESSION['agendas'][] = [
        'id' => $newId,
        'meeting_id' => $data['meeting_id'],
        'item_number' => $data['item_number'],
        'title' => $data['title'],
        'description' => $data['description'] ?? '',
        'duration' => $data['duration'] ?? 0,
        'presenter' => $data['presenter'] ?? '',
        'created_by' => $_SESSION['user_name'] ?? 'User'
    ];

    return $newId;
}

/**
 * Add agenda item
 */
function addAgendaItem($meetingId, $data)
{
    $existingItems = getAgendaItems($meetingId);
    $nextItemNumber = count($existingItems) + 1;

    $data['meeting_id'] = $meetingId;
    $data['item_number'] = $nextItemNumber;

    return createAgenda($data);
}

/**
 * Update agenda
 */
function updateAgenda($id, $data)
{
    $agendas = &$_SESSION['agendas'];
    foreach ($agendas as &$agenda) {
        if ($agenda['id'] == $id) {
            $agenda['title'] = $data['title'];
            $agenda['description'] = $data['description'] ?? '';
            $agenda['duration'] = $data['duration'] ?? 0;
            $agenda['presenter'] = $data['presenter'] ?? '';
            if (isset($data['item_number'])) {
                $agenda['item_number'] = $data['item_number'];
            }
            return true;
        }
    }
    return false;
}

/**
 * Delete agenda
 */
function deleteAgenda($id)
{
    $agendas = &$_SESSION['agendas'];
    foreach ($agendas as $key => $agenda) {
        if ($agenda['id'] == $id) {
            unset($agendas[$key]);
            $_SESSION['agendas'] = array_values($agendas);
            return true;
        }
    }
    return false;
}

// ==========================================
// MINUTES FUNCTIONS
// ==========================================

// Initialize minutes in session
if (!isset($_SESSION['minutes'])) {
    $_SESSION['minutes'] = [
        [
            'id' => 1,
            'meeting_id' => 3,
            'content' => 'Meeting minutes for School Infrastructure Planning...',
            'prepared_by' => 'Committee Staff',
            'approved_by' => 'Hon. Ana Reyes',
            'status' => 'Approved',
            'created_at' => '2025-12-11',
            'approved_at' => '2025-12-12'
        ]
    ];
}

/**
 * Get all minutes
 */
function getAllMinutes()
{
    return $_SESSION['minutes'] ?? [];
}

/**
 * Get minutes by ID
 */
function getMinutesById($id)
{
    $minutes = getAllMinutes();
    foreach ($minutes as $minute) {
        if ($minute['id'] == $id) {
            return $minute;
        }
    }
    return null;
}

/**
 * Get minutes by meeting
 */
function getMinutesByMeeting($meetingId)
{
    $minutes = getAllMinutes();
    foreach ($minutes as $minute) {
        if ($minute['meeting_id'] == $meetingId) {
            return $minute;
        }
    }
    return null;
}

/**
 * Create minutes
 */
function createMinutes($data)
{
    $minutes = getAllMinutes();
    $newId = empty($minutes) ? 1 : max(array_column($minutes, 'id')) + 1;

    $_SESSION['minutes'][] = [
        'id' => $newId,
        'meeting_id' => $data['meeting_id'],
        'content' => $data['content'],
        'prepared_by' => $_SESSION['user_name'] ?? 'User',
        'approved_by' => null,
        'status' => 'Draft',
        'created_at' => date('Y-m-d'),
        'approved_at' => null
    ];

    return $newId;
}

/**
 * Update minutes
 */
function updateMinutes($id, $data)
{
    $minutes = &$_SESSION['minutes'];
    foreach ($minutes as &$minute) {
        if ($minute['id'] == $id) {
            $minute['content'] = $data['content'];
            if (isset($data['status'])) {
                $minute['status'] = $data['status'];
            }
            if (isset($data['approved_by'])) {
                $minute['approved_by'] = $data['approved_by'];
                $minute['approved_at'] = date('Y-m-d');
            }
            return true;
        }
    }
    return false;
}

// ==========================================
// ATTENDANCE FUNCTIONS
// ==========================================

// Initialize attendance in session
if (!isset($_SESSION['attendance'])) {
    $_SESSION['attendance'] = [
        ['meeting_id' => 1, 'member_id' => 1, 'member_name' => 'Hon. Maria Santos', 'status' => 'Present', 'time_in' => '14:00'],
        ['meeting_id' => 1, 'member_id' => 2, 'member_name' => 'Hon. Roberto Cruz', 'status' => 'Present', 'time_in' => '14:05'],
        ['meeting_id' => 1, 'member_id' => 3, 'member_name' => 'Hon. Lisa Tan', 'status' => 'Absent', 'time_in' => null],
    ];
}

/**
 * Get attendance for a meeting
 */
function getAttendance($meetingId)
{
    $attendance = $_SESSION['attendance'] ?? [];
    return array_filter($attendance, function ($record) use ($meetingId) {
        return $record['meeting_id'] == $meetingId;
    });
}

/**
 * Mark attendance
 */
function markAttendance($meetingId, $memberId, $memberName, $status, $timeIn = null)
{
    if (!isset($_SESSION['attendance'])) {
        $_SESSION['attendance'] = [];
    }

    // Check if attendance already exists
    $attendance = &$_SESSION['attendance'];
    foreach ($attendance as &$record) {
        if ($record['meeting_id'] == $meetingId && $record['member_id'] == $memberId) {
            $record['status'] = $status;
            $record['time_in'] = $timeIn ?? date('H:i');
            return true;
        }
    }

    // Add new attendance record
    $_SESSION['attendance'][] = [
        'meeting_id' => $meetingId,
        'member_id' => $memberId,
        'member_name' => $memberName,
        'status' => $status,
        'time_in' => $timeIn ?? ($status === 'Present' ? date('H:i') : null)
    ];

    return true;
}

/**
 * Get attendance by member
 */
function getAttendanceByMember($memberId)
{
    $attendance = $_SESSION['attendance'] ?? [];
    return array_filter($attendance, function ($record) use ($memberId) {
        return $record['member_id'] == $memberId;
    });
}

/**
 * Get attendance statistics for a member
 */
function getAttendanceStats($memberId)
{
    $records = getAttendanceByMember($memberId);
    $total = count($records);
    $present = count(array_filter($records, fn($r) => $r['status'] === 'Present'));
    $absent = count(array_filter($records, fn($r) => $r['status'] === 'Absent'));
    $excused = count(array_filter($records, fn($r) => $r['status'] === 'Excused'));

    return [
        'total' => $total,
        'present' => $present,
        'absent' => $absent,
        'excused' => $excused,
        'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0
    ];
}
?>