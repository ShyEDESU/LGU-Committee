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
            'description' => 'An ordinance appropriating funds for infrastructure projects',
            'priority' => 'High',
            'status' => 'Pending',
            'date_received' => '2025-11-15',
            'deadline' => '2025-12-31',
            'assigned_to' => 'Hon. Maria Santos'
        ],
        [
            'id' => 2,
            'committee_id' => 2,
            'committee_name' => 'Committee on Health',
            'title' => 'Resolution No. 2025-045',
            'description' => 'Resolution supporting mental health programs',
            'priority' => 'Medium',
            'status' => 'Under Review',
            'date_received' => '2025-11-20',
            'deadline' => '2026-01-15',
            'assigned_to' => 'Hon. Juan Dela Cruz'
        ]
    ];
}

// Initialize action items
if (!isset($_SESSION['action_items'])) {
    $_SESSION['action_items'] = [
        [
            'id' => 1,
            'meeting_id' => 3,
            'title' => 'Prepare budget report',
            'description' => 'Compile Q4 financial data',
            'assigned_to' => 'Finance Staff',
            'due_date' => '2025-12-20',
            'priority' => 'High',
            'status' => 'In Progress'
        ],
        [
            'id' => 2,
            'meeting_id' => 2,
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
?>