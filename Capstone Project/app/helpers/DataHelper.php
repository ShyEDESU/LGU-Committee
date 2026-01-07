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
        [
            'id' => 1,
            'meeting_id' => 1,
            'title' => '2025 Annual Budget Review',
            'description' => 'Review and discussion of proposed 2025 budget',
            'items' => [
                [
                    'id' => 1,
                    'title' => 'Budget Presentation',
                    'presenter' => 'Finance Director',
                    'duration' => 30,
                    'type' => 'Presentation',
                    'referral_id' => 1,
                    'notes' => '',
                    'description' => 'Presentation of the proposed 2025 annual budget'
                ],
                [
                    'id' => 2,
                    'title' => 'Q&A Session',
                    'presenter' => 'All Members',
                    'duration' => 20,
                    'type' => 'Discussion',
                    'referral_id' => null,
                    'notes' => '',
                    'description' => 'Open discussion and questions about the budget'
                ],
                [
                    'id' => 3,
                    'title' => 'Budget Vote',
                    'presenter' => 'Committee Chair',
                    'duration' => 15,
                    'type' => 'Voting',
                    'referral_id' => 1,
                    'notes' => '',
                    'description' => 'Vote on budget approval'
                ]
            ],
            'status' => 'Published',
            'created_date' => '2025-12-01'
        ],
        [
            'id' => 2,
            'meeting_id' => 2,
            'title' => 'Infrastructure Development Plan',
            'description' => 'Discussion on proposed infrastructure projects for 2025',
            'items' => [
                [
                    'id' => 1,
                    'title' => 'Call to Order',
                    'presenter' => 'Committee Chair',
                    'duration' => 5,
                    'type' => 'Procedural',
                    'referral_id' => null,
                    'notes' => '',
                    'description' => 'Opening remarks and roll call'
                ],
                [
                    'id' => 2,
                    'title' => 'Road Improvement Project Overview',
                    'presenter' => 'Public Works Director',
                    'duration' => 25,
                    'type' => 'Presentation',
                    'referral_id' => 2,
                    'notes' => '',
                    'description' => 'Presentation on proposed road repairs and improvements'
                ],
                [
                    'id' => 3,
                    'title' => 'Bridge Maintenance Report',
                    'presenter' => 'Engineering Consultant',
                    'duration' => 20,
                    'type' => 'Report',
                    'referral_id' => null,
                    'notes' => '',
                    'description' => 'Status update on bridge inspections and maintenance needs'
                ],
                [
                    'id' => 4,
                    'title' => 'Public Comment Period',
                    'presenter' => 'Citizens',
                    'duration' => 30,
                    'type' => 'Public Input',
                    'referral_id' => null,
                    'notes' => '',
                    'description' => 'Open forum for public comments on infrastructure projects'
                ],
                [
                    'id' => 5,
                    'title' => 'Committee Discussion',
                    'presenter' => 'All Members',
                    'duration' => 25,
                    'type' => 'Discussion',
                    'referral_id' => 2,
                    'notes' => '',
                    'description' => 'Committee deliberation on infrastructure priorities'
                ]
            ],
            'status' => 'Draft',
            'created_date' => '2025-12-10'
        ],
        [
            'id' => 3,
            'meeting_id' => 3,
            'title' => 'Zoning Ordinance Amendment',
            'description' => 'Review and vote on proposed zoning changes',
            'items' => [
                [
                    'id' => 1,
                    'title' => 'Opening and Approval of Minutes',
                    'presenter' => 'Committee Secretary',
                    'duration' => 10,
                    'type' => 'Procedural',
                    'referral_id' => null,
                    'notes' => '',
                    'description' => 'Review and approve minutes from previous meeting'
                ],
                [
                    'id' => 2,
                    'title' => 'Zoning Amendment Presentation',
                    'presenter' => 'City Planner',
                    'duration' => 35,
                    'type' => 'Presentation',
                    'referral_id' => 3,
                    'notes' => '',
                    'description' => 'Detailed presentation on proposed zoning ordinance changes'
                ],
                [
                    'id' => 3,
                    'title' => 'Environmental Impact Review',
                    'presenter' => 'Environmental Consultant',
                    'duration' => 20,
                    'type' => 'Report',
                    'referral_id' => 3,
                    'notes' => '',
                    'description' => 'Assessment of environmental impacts from zoning changes'
                ],
                [
                    'id' => 4,
                    'title' => 'Committee Deliberation',
                    'presenter' => 'All Members',
                    'duration' => 30,
                    'type' => 'Discussion',
                    'referral_id' => 3,
                    'notes' => '',
                    'description' => 'Discussion and debate on zoning amendment'
                ],
                [
                    'id' => 5,
                    'title' => 'Final Vote',
                    'presenter' => 'Committee Chair',
                    'duration' => 10,
                    'type' => 'Voting',
                    'referral_id' => 3,
                    'notes' => '',
                    'description' => 'Roll call vote on zoning ordinance amendment'
                ]
            ],
            'status' => 'Finalized',
            'created_date' => '2025-12-15'
        ]
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
        ],
        [
            'id' => 3,
            'committee_id' => 3,
            'committee_name' => 'Committee on Education',
            'title' => 'Resolution No. 2025-052',
            'type' => 'Resolution',
            'description' => 'Resolution approving scholarship program for underprivileged students',
            'priority' => 'High',
            'status' => 'Approved',
            'date_received' => '2025-11-25',
            'deadline' => '2025-12-20',
            'assigned_to' => 'Hon. Ana Reyes',
            'submitted_by' => 'Education Department',
            'submitted_date' => '2025-11-25'
        ],
        [
            'id' => 4,
            'committee_id' => 4,
            'committee_name' => 'Committee on Infrastructure',
            'title' => 'Ordinance No. 2025-003',
            'type' => 'Ordinance',
            'description' => 'An ordinance authorizing road widening project',
            'priority' => 'Medium',
            'status' => 'Pending',
            'date_received' => '2025-12-01',
            'deadline' => '2026-01-30',
            'assigned_to' => 'Hon. Pedro Garcia',
            'submitted_by' => 'Public Works Department',
            'submitted_date' => '2025-12-01'
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
            // Update only provided fields
            if (isset($data['committee_id']))
                $meeting['committee_id'] = $data['committee_id'];
            if (isset($data['committee_name']))
                $meeting['committee_name'] = $data['committee_name'];
            if (isset($data['title']))
                $meeting['title'] = $data['title'];
            if (isset($data['description']))
                $meeting['description'] = $data['description'];
            if (isset($data['date']))
                $meeting['date'] = $data['date'];
            if (isset($data['time_start']))
                $meeting['time_start'] = $data['time_start'];
            if (isset($data['time_end']))
                $meeting['time_end'] = $data['time_end'];
            if (isset($data['venue']))
                $meeting['venue'] = $data['venue'];
            if (isset($data['is_public']))
                $meeting['is_public'] = $data['is_public'];
            if (isset($data['agenda_status']))
                $meeting['agenda_status'] = $data['agenda_status'];
            if (isset($data['status']))
                $meeting['status'] = $data['status'];
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

    $newReferral = [
        'id' => $newId,
        'committee_id' => $data['committee_id'],
        'committee_name' => $data['committee_name'],
        'title' => $data['title'],
        'type' => $data['type'] ?? 'Communication', // Ordinance, Resolution, Communication
        'description' => $data['description'] ?? '',
        'priority' => $data['priority'] ?? 'Medium', // High, Medium, Low
        'status' => 'Pending', // Pending, Under Review, In Committee, Approved, Rejected, Deferred
        'date_received' => $data['date_received'] ?? date('Y-m-d'),
        'deadline' => $data['deadline'] ?? '',
        'assigned_to' => $data['assigned_to'] ?? '',
        'assigned_member_id' => $data['assigned_member_id'] ?? null,
        'submitted_by' => $data['submitted_by'] ?? '',
        'submitted_date' => $data['submitted_date'] ?? date('Y-m-d'),
        'created_by' => $_SESSION['user_name'] ?? 'User',
        'created_date' => date('Y-m-d'),
        'updated_date' => date('Y-m-d'),
        // Integration fields
        'meeting_id' => $data['meeting_id'] ?? null,
        'agenda_item_id' => $data['agenda_item_id'] ?? null,
        'final_action' => null,
        'final_action_date' => null,
        'notes' => $data['notes'] ?? '',
        'is_public' => $data['is_public'] ?? true
    ];

    $_SESSION['referrals'][] = $newReferral;
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
            // Update only provided fields
            if (isset($data['committee_id']))
                $ref['committee_id'] = $data['committee_id'];
            if (isset($data['committee_name']))
                $ref['committee_name'] = $data['committee_name'];
            if (isset($data['title']))
                $ref['title'] = $data['title'];
            if (isset($data['type']))
                $ref['type'] = $data['type'];
            if (isset($data['description']))
                $ref['description'] = $data['description'];
            if (isset($data['priority']))
                $ref['priority'] = $data['priority'];
            if (isset($data['status']))
                $ref['status'] = $data['status'];
            if (isset($data['deadline']))
                $ref['deadline'] = $data['deadline'];
            if (isset($data['assigned_to']))
                $ref['assigned_to'] = $data['assigned_to'];
            if (isset($data['assigned_member_id']))
                $ref['assigned_member_id'] = $data['assigned_member_id'];
            if (isset($data['submitted_by']))
                $ref['submitted_by'] = $data['submitted_by'];
            if (isset($data['meeting_id']))
                $ref['meeting_id'] = $data['meeting_id'];
            if (isset($data['agenda_item_id']))
                $ref['agenda_item_id'] = $data['agenda_item_id'];
            if (isset($data['final_action']))
                $ref['final_action'] = $data['final_action'];
            if (isset($data['final_action_date']))
                $ref['final_action_date'] = $data['final_action_date'];
            if (isset($data['notes']))
                $ref['notes'] = $data['notes'];
            if (isset($data['is_public']))
                $ref['is_public'] = $data['is_public'];

            $ref['updated_date'] = date('Y-m-d');
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
 * Get referrals by type
 */
function getReferralsByType($type)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($type) {
        return $ref['type'] === $type;
    });
}

/**
 * Get referrals by priority
 */
function getReferralsByPriority($priority)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($priority) {
        return $ref['priority'] === $priority;
    });
}

/**
 * Get referrals by deadline range
 */
function getReferralsByDeadline($startDate, $endDate)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($startDate, $endDate) {
        if (empty($ref['deadline']))
            return false;
        return $ref['deadline'] >= $startDate && $ref['deadline'] <= $endDate;
    });
}

/**
 * Get overdue referrals
 */
function getOverdueReferrals()
{
    $referrals = getAllReferrals();
    $today = date('Y-m-d');
    return array_filter($referrals, function ($ref) use ($today) {
        return !empty($ref['deadline']) &&
            $ref['deadline'] < $today &&
            !in_array($ref['status'], ['Approved', 'Rejected', 'Completed']);
    });
}

/**
 * Get referrals by meeting
 */
function getReferralsByMeeting($meetingId)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($meetingId) {
        return $ref['meeting_id'] == $meetingId;
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
    foreach ($agendas as $agenda) {
        if ($agenda['meeting_id'] == $meetingId) {
            return $agenda['items'] ?? [];
        }
    }
    return [];
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

// ==========================================
// ENHANCED AGENDA MANAGEMENT FUNCTIONS
// ==========================================

// Initialize agenda templates in session
if (!isset($_SESSION['agenda_templates'])) {
    $_SESSION['agenda_templates'] = [
        [
            'id' => 1,
            'name' => 'Standard Committee Meeting',
            'description' => 'Default template for regular committee meetings',
            'committee_type' => 'All',
            'items' => [
                ['title' => 'Call to Order', 'description' => '', 'duration' => 5, 'type' => 'Call to Order'],
                ['title' => 'Approval of Previous Minutes', 'description' => '', 'duration' => 10, 'type' => 'Approval'],
                ['title' => 'Main Discussion', 'description' => '', 'duration' => 60, 'type' => 'Discussion'],
                ['title' => 'Voting on Resolutions', 'description' => '', 'duration' => 15, 'type' => 'Voting'],
                ['title' => 'Adjournment', 'description' => '', 'duration' => 5, 'type' => 'Adjournment']
            ],
            'created_by' => 'System',
            'created_date' => '2025-01-01'
        ],
        [
            'id' => 2,
            'name' => 'Budget Review Session',
            'description' => 'Template for budget review and financial discussions',
            'committee_type' => 'Standing',
            'items' => [
                ['title' => 'Call to Order', 'description' => '', 'duration' => 5, 'type' => 'Call to Order'],
                ['title' => 'Budget Presentation', 'description' => 'Financial overview and budget details', 'duration' => 30, 'type' => 'Presentation'],
                ['title' => 'Q&A Session', 'description' => 'Questions and clarifications', 'duration' => 20, 'type' => 'Discussion'],
                ['title' => 'Budget Discussion', 'description' => 'Detailed discussion on budget items', 'duration' => 45, 'type' => 'Discussion'],
                ['title' => 'Voting on Budget', 'description' => '', 'duration' => 15, 'type' => 'Voting'],
                ['title' => 'Adjournment', 'description' => '', 'duration' => 5, 'type' => 'Adjournment']
            ],
            'created_by' => 'System',
            'created_date' => '2025-01-01'
        ]
    ];
}

// Initialize deliberations in session
if (!isset($_SESSION['deliberations'])) {
    $_SESSION['deliberations'] = [];
}

// Initialize votes in session
if (!isset($_SESSION['votes'])) {
    $_SESSION['votes'] = [];
}

// Initialize agenda distribution in session
if (!isset($_SESSION['agenda_distribution'])) {
    $_SESSION['agenda_distribution'] = [];
}

/**
 * Get all agenda templates
 */
function getAllAgendaTemplates()
{
    return $_SESSION['agenda_templates'] ?? [];
}

/**
 * Get agenda template by ID
 */
function getAgendaTemplateById($id)
{
    $templates = getAllAgendaTemplates();
    foreach ($templates as $template) {
        if ($template['id'] == $id) {
            return $template;
        }
    }
    return null;
}

/**
 * Create agenda template
 */
function createAgendaTemplate($data)
{
    $templates = getAllAgendaTemplates();
    $newId = empty($templates) ? 1 : max(array_column($templates, 'id')) + 1;

    $_SESSION['agenda_templates'][] = [
        'id' => $newId,
        'name' => $data['name'],
        'description' => $data['description'] ?? '',
        'committee_type' => $data['committee_type'] ?? 'All',
        'items' => $data['items'] ?? [],
        'created_by' => $_SESSION['user_name'] ?? 'User',
        'created_date' => date('Y-m-d')
    ];

    return $newId;
}

/**
 * Update agenda template
 */
function updateAgendaTemplate($id, $data)
{
    $templates = &$_SESSION['agenda_templates'];
    foreach ($templates as &$template) {
        if ($template['id'] == $id) {
            $template['name'] = $data['name'];
            $template['description'] = $data['description'] ?? '';
            $template['committee_type'] = $data['committee_type'] ?? 'All';
            $template['items'] = $data['items'] ?? [];
            return true;
        }
    }
    return false;
}

/**
 * Delete agenda template
 */
function deleteAgendaTemplate($id)
{
    $templates = &$_SESSION['agenda_templates'];
    foreach ($templates as $key => $template) {
        if ($template['id'] == $id) {
            unset($templates[$key]);
            $_SESSION['agenda_templates'] = array_values($templates);
            return true;
        }
    }
    return false;
}

/**
 * Apply template to create agenda items
 */
function applyTemplate($templateId, $meetingId)
{
    $template = getAgendaTemplateById($templateId);
    if (!$template) {
        return false;
    }

    // Create agenda items from template
    foreach ($template['items'] as $item) {
        addAgendaItem($meetingId, [
            'title' => $item['title'],
            'description' => $item['description'],
            'duration' => $item['duration'],
            'type' => $item['type']
        ]);
    }

    return true;
}

/**
 * Create full agenda with items
 */
function createFullAgenda($data)
{
    $meetings = getAllMeetings();
    $newId = empty($meetings) ? 1 : max(array_column($meetings, 'id')) + 1;

    // Create the agenda metadata
    $agendaId = $newId;

    // If items are provided, create them
    if (isset($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as $index => $item) {
            $item['meeting_id'] = $data['meeting_id'];
            $item['item_number'] = $index + 1;
            createAgenda($item);
        }
    }

    return $agendaId;
}

/**
 * Duplicate agenda
 */
function duplicateAgenda($id)
{
    $agenda = getAgendaById($id);
    if (!$agenda) {
        return false;
    }

    // Get all items for this agenda
    $items = getAgendaByMeeting($agenda['meeting_id']);

    // Note: This would need a new meeting ID to duplicate to
    // For now, return the agenda data for manual duplication
    return [
        'agenda' => $agenda,
        'items' => $items
    ];
}

/**
 * Publish agenda
 */
function publishAgenda($id)
{
    // This would update the agenda status
    // Since agendas are stored as items, we need a different approach
    // We'll add this to the meeting metadata
    $meetings = &$_SESSION['meetings'];
    foreach ($meetings as &$meeting) {
        if ($meeting['id'] == $id) {
            $meeting['agenda_status'] = 'Published';
            $meeting['agenda_published_date'] = date('Y-m-d H:i:s');
            $meeting['agenda_published_by'] = $_SESSION['user_name'] ?? 'User';
            return true;
        }
    }
    return false;
}

/**
 * Get agendas by committee
 */
function getAgendasByCommittee($committeeId)
{
    $meetings = getAllMeetings();
    $committeeMeetings = array_filter($meetings, function ($meeting) use ($committeeId) {
        return $meeting['committee_id'] == $committeeId;
    });

    // Get agendas for these meetings
    $agendas = [];
    foreach ($committeeMeetings as $meeting) {
        $items = getAgendaByMeeting($meeting['id']);
        if (!empty($items)) {
            $agendas[] = [
                'meeting' => $meeting,
                'items' => $items,
                'item_count' => count($items)
            ];
        }
    }

    return $agendas;
}

/**
 * Get agendas by status
 */
function getAgendasByStatus($status)
{
    $meetings = getAllMeetings();
    return array_filter($meetings, function ($meeting) use ($status) {
        return isset($meeting['agenda_status']) && $meeting['agenda_status'] === $status;
    });
}

/**
 * Create deliberation record
 */
function createDeliberation($agendaItemId, $data)
{
    if (!isset($_SESSION['deliberations'])) {
        $_SESSION['deliberations'] = [];
    }

    $deliberations = $_SESSION['deliberations'];
    $newId = empty($deliberations) ? 1 : max(array_column($deliberations, 'id')) + 1;

    $_SESSION['deliberations'][] = [
        'id' => $newId,
        'agenda_item_id' => $agendaItemId,
        'speaker' => $data['speaker'],
        'notes' => $data['notes'] ?? '',
        'duration' => $data['duration'] ?? 0,
        'timestamp' => date('Y-m-d H:i:s'),
        'recorded_by' => $_SESSION['user_name'] ?? 'User'
    ];

    return $newId;
}

/**
 * Update deliberation
 */
function updateDeliberation($id, $data)
{
    $deliberations = &$_SESSION['deliberations'];
    foreach ($deliberations as &$delib) {
        if ($delib['id'] == $id) {
            $delib['speaker'] = $data['speaker'];
            $delib['notes'] = $data['notes'] ?? '';
            $delib['duration'] = $data['duration'] ?? 0;
            return true;
        }
    }
    return false;
}

/**
 * Get deliberations by agenda
 */
function getDeliberationsByAgenda($agendaId)
{
    $deliberations = $_SESSION['deliberations'] ?? [];
    $agendaItems = getAgendaByMeeting($agendaId);
    $itemIds = array_column($agendaItems, 'id');

    return array_filter($deliberations, function ($delib) use ($itemIds) {
        return in_array($delib['agenda_item_id'], $itemIds);
    });
}

/**
 * Create vote
 */
function createVote($agendaItemId, $data)
{
    if (!isset($_SESSION['votes'])) {
        $_SESSION['votes'] = [];
    }

    $votes = $_SESSION['votes'];
    $newId = empty($votes) ? 1 : max(array_column($votes, 'id')) + 1;

    $_SESSION['votes'][] = [
        'id' => $newId,
        'agenda_item_id' => $agendaItemId,
        'motion_text' => $data['motion_text'],
        'voting_method' => $data['voting_method'] ?? 'Voice Vote',
        'votes_yes' => 0,
        'votes_no' => 0,
        'votes_abstain' => 0,
        'votes_absent' => 0,
        'result' => 'Pending',
        'member_votes' => [],
        'created_by' => $_SESSION['user_name'] ?? 'User',
        'created_date' => date('Y-m-d H:i:s')
    ];

    return $newId;
}

/**
 * Record member vote
 */
function recordMemberVote($voteId, $memberId, $vote)
{
    $votes = &$_SESSION['votes'];
    foreach ($votes as &$voteRecord) {
        if ($voteRecord['id'] == $voteId) {
            // Record the member's vote
            $voteRecord['member_votes'][$memberId] = $vote;

            // Update tallies
            $voteRecord['votes_yes'] = count(array_filter($voteRecord['member_votes'], fn($v) => $v === 'Yes'));
            $voteRecord['votes_no'] = count(array_filter($voteRecord['member_votes'], fn($v) => $v === 'No'));
            $voteRecord['votes_abstain'] = count(array_filter($voteRecord['member_votes'], fn($v) => $v === 'Abstain'));
            $voteRecord['votes_absent'] = count(array_filter($voteRecord['member_votes'], fn($v) => $v === 'Absent'));

            // Determine result
            if ($voteRecord['votes_yes'] > $voteRecord['votes_no']) {
                $voteRecord['result'] = 'Passed';
            } elseif ($voteRecord['votes_no'] > $voteRecord['votes_yes']) {
                $voteRecord['result'] = 'Failed';
            } else {
                $voteRecord['result'] = 'Tied';
            }

            return true;
        }
    }
    return false;
}

/**
 * Get votes by agenda
 */
function getVotesByAgenda($agendaId)
{
    $votes = $_SESSION['votes'] ?? [];
    $agendaItems = getAgendaByMeeting($agendaId);
    $itemIds = array_column($agendaItems, 'id');

    return array_filter($votes, function ($vote) use ($itemIds) {
        return in_array($vote['agenda_item_id'], $itemIds);
    });
}

/**
 * Get vote results
 */
function getVoteResults($voteId)
{
    $votes = $_SESSION['votes'] ?? [];
    foreach ($votes as $vote) {
        if ($vote['id'] == $voteId) {
            return [
                'motion_text' => $vote['motion_text'],
                'voting_method' => $vote['voting_method'],
                'yes' => $vote['votes_yes'],
                'no' => $vote['votes_no'],
                'abstain' => $vote['votes_abstain'],
                'absent' => $vote['votes_absent'],
                'result' => $vote['result'],
                'total_votes' => $vote['votes_yes'] + $vote['votes_no'] + $vote['votes_abstain']
            ];
        }
    }
    return null;
}

/**
 * Distribute agenda
 */
function distributeAgenda($agendaId, $recipients)
{
    if (!isset($_SESSION['agenda_distribution'])) {
        $_SESSION['agenda_distribution'] = [];
    }

    foreach ($recipients as $recipient) {
        $_SESSION['agenda_distribution'][] = [
            'agenda_id' => $agendaId,
            'recipient_id' => $recipient['id'],
            'recipient_name' => $recipient['name'],
            'recipient_email' => $recipient['email'],
            'sent_date' => date('Y-m-d H:i:s'),
            'read_date' => null,
            'status' => 'Sent'
        ];
    }

    return true;
}

/**
 * Track distribution action
 */
function trackDistribution($agendaId, $recipientId, $action)
{
    $distribution = &$_SESSION['agenda_distribution'];
    foreach ($distribution as &$record) {
        if ($record['agenda_id'] == $agendaId && $record['recipient_id'] == $recipientId) {
            if ($action === 'read') {
                $record['read_date'] = date('Y-m-d H:i:s');
                $record['status'] = 'Read';
            }
            return true;
        }
    }
    return false;
}

/**
 * Get distribution log
 */
function getDistributionLog($agendaId)
{
    $distribution = $_SESSION['agenda_distribution'] ?? [];
    return array_filter($distribution, function ($record) use ($agendaId) {
        return $record['agenda_id'] == $agendaId;
    });
}
?>