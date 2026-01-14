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
            'committee_id' => 1,  // Finance Committee
            'meeting_id' => 1,  // Finance Committee meeting
            'title' => 'Review budget proposal',
            'description' => 'Review and provide feedback on proposed budget',
            'assigned_to' => 'Finance Committee Staff',
            'due_date' => '2025-12-15',
            'priority' => 'High',
            'status' => 'In Progress'
        ],
        [
            'id' => 2,
            'committee_id' => 2,  // Health Committee
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
        // Core fields
        'id' => $newId,
        'title' => $data['title'],
        'description' => $data['description'] ?? '',

        // Assignment
        'assigned_to' => $data['assigned_to'] ?? '',
        'created_by' => $_SESSION['user_name'] ?? 'System',

        // Dates
        'created_date' => date('Y-m-d H:i:s'),
        'due_date' => $data['due_date'] ?? '',
        'completed_date' => null,

        // Status & Priority
        'status' => $data['status'] ?? 'To Do',
        'priority' => $data['priority'] ?? 'Medium',
        'progress' => $data['progress'] ?? 0,

        // Categorization
        'category' => $data['category'] ?? 'General',
        'tags' => $data['tags'] ?? [],

        // Time tracking
        'estimated_hours' => $data['estimated_hours'] ?? null,
        'actual_hours' => $data['actual_hours'] ?? null,

        // Relationships
        'committee_id' => $data['committee_id'] ?? null,
        'meeting_id' => $data['meeting_id'] ?? null,
        'agenda_item_id' => $data['agenda_item_id'] ?? null,
        'referral_id' => $data['referral_id'] ?? null,
        'dependencies' => $data['dependencies'] ?? [],

        // Additional info
        'notes' => $data['notes'] ?? '',
        'attachments' => $data['attachments'] ?? [],
        'reminders' => $data['reminders'] ?? [],

        // Recurring
        'is_recurring' => $data['is_recurring'] ?? false,
        'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
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
            // Update only provided fields
            if (isset($data['title']))
                $item['title'] = $data['title'];
            if (isset($data['description']))
                $item['description'] = $data['description'];
            if (isset($data['assigned_to']))
                $item['assigned_to'] = $data['assigned_to'];
            if (isset($data['due_date']))
                $item['due_date'] = $data['due_date'];
            if (isset($data['status']))
                $item['status'] = $data['status'];
            if (isset($data['priority']))
                $item['priority'] = $data['priority'];
            if (isset($data['progress']))
                $item['progress'] = $data['progress'];
            if (isset($data['category']))
                $item['category'] = $data['category'];
            if (isset($data['tags']))
                $item['tags'] = $data['tags'];
            if (isset($data['estimated_hours']))
                $item['estimated_hours'] = $data['estimated_hours'];
            if (isset($data['actual_hours']))
                $item['actual_hours'] = $data['actual_hours'];
            if (isset($data['committee_id']))
                $item['committee_id'] = $data['committee_id'];
            if (isset($data['meeting_id']))
                $item['meeting_id'] = $data['meeting_id'];
            if (isset($data['agenda_item_id']))
                $item['agenda_item_id'] = $data['agenda_item_id'];
            if (isset($data['referral_id']))
                $item['referral_id'] = $data['referral_id'];
            if (isset($data['dependencies']))
                $item['dependencies'] = $data['dependencies'];
            if (isset($data['notes']))
                $item['notes'] = $data['notes'];
            if (isset($data['attachments']))
                $item['attachments'] = $data['attachments'];
            if (isset($data['reminders']))
                $item['reminders'] = $data['reminders'];
            if (isset($data['is_recurring']))
                $item['is_recurring'] = $data['is_recurring'];
            if (isset($data['recurrence_pattern']))
                $item['recurrence_pattern'] = $data['recurrence_pattern'];

            // Auto-set completed_date when status changes to Done
            if (isset($data['status']) && $data['status'] === 'Done' && empty($item['completed_date'])) {
                $item['completed_date'] = date('Y-m-d H:i:s');
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

/**
 * Get action items by committee
 */
function getActionItemsByCommittee($committeeId)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($committeeId) {
        return isset($item['committee_id']) && $item['committee_id'] == $committeeId;
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
 * Get action items by priority
 */
function getActionItemsByPriority($priority)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($priority) {
        return $item['priority'] === $priority;
    });
}

/**
 * Get action items by assignee
 */
function getActionItemsByAssignee($assignee)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($assignee) {
        return $item['assigned_to'] === $assignee;
    });
}

/**
 * Get overdue action items
 */
function getOverdueActionItems()
{
    $items = getAllActionItems();
    $today = date('Y-m-d');
    return array_filter($items, function ($item) use ($today) {
        return !empty($item['due_date']) &&
            $item['due_date'] < $today &&
            $item['status'] !== 'Done';
    });
}

/**
 * Get upcoming action items within specified days
 */
function getUpcomingActionItems($days = 7)
{
    $items = getAllActionItems();
    $today = date('Y-m-d');
    $futureDate = date('Y-m-d', strtotime("+$days days"));

    return array_filter($items, function ($item) use ($today, $futureDate) {
        return !empty($item['due_date']) &&
            $item['due_date'] >= $today &&
            $item['due_date'] <= $futureDate &&
            $item['status'] !== 'Done';
    });
}

/**
 * Get action item progress
 */
function getActionItemProgress($id)
{
    $item = getActionItemById($id);
    return $item ? ($item['progress'] ?? 0) : 0;
}

/**
 * Update action item progress
 */
function updateActionItemProgress($id, $progress)
{
    $progress = max(0, min(100, (int) $progress)); // Ensure 0-100
    return updateActionItem($id, [
        'progress' => $progress,
        'status' => $progress === 100 ? 'Done' : ($progress > 0 ? 'In Progress' : 'To Do')
    ]);
}

/**
 * Complete action item
 */
function completeActionItem($id)
{
    return updateActionItem($id, [
        'status' => 'Done',
        'progress' => 100,
        'completed_date' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Get action items by date range
 */
function getActionItemsByDateRange($startDate, $endDate)
{
    $items = getAllActionItems();
    return array_filter($items, function ($item) use ($startDate, $endDate) {
        return !empty($item['due_date']) &&
            $item['due_date'] >= $startDate &&
            $item['due_date'] <= $endDate;
    });
}

/**
 * Get action item statistics
 */
function getActionItemStatistics()
{
    $items = getAllActionItems();

    $stats = [
        'total' => count($items),
        'by_status' => [
            'To Do' => 0,
            'In Progress' => 0,
            'Done' => 0
        ],
        'by_priority' => [
            'High' => 0,
            'Medium' => 0,
            'Low' => 0
        ],
        'overdue' => count(getOverdueActionItems()),
        'upcoming_7_days' => count(getUpcomingActionItems(7)),
        'upcoming_14_days' => count(getUpcomingActionItems(14)),
        'upcoming_30_days' => count(getUpcomingActionItems(30)),
        'avg_progress' => 0,
        'completion_rate' => 0
    ];

    $totalProgress = 0;
    foreach ($items as $item) {
        // Count by status
        $status = $item['status'] ?? 'To Do';
        if (isset($stats['by_status'][$status])) {
            $stats['by_status'][$status]++;
        }

        // Count by priority
        $priority = $item['priority'] ?? 'Medium';
        if (isset($stats['by_priority'][$priority])) {
            $stats['by_priority'][$priority]++;
        }

        // Calculate average progress
        $totalProgress += ($item['progress'] ?? 0);
    }

    if ($stats['total'] > 0) {
        $stats['avg_progress'] = round($totalProgress / $stats['total'], 1);
        $stats['completion_rate'] = round(($stats['by_status']['Done'] / $stats['total']) * 100, 1);
    }

    return $stats;
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
 * Meeting Attendance Functions
 */

// Initialize attendance data
if (!isset($_SESSION['attendance'])) {
    $_SESSION['attendance'] = [];
}

/**
 * Get attendance for a meeting
 */
function getMeetingAttendance($meetingId)
{
    $attendance = $_SESSION['attendance'] ?? [];
    return array_filter($attendance, function ($record) use ($meetingId) {
        return $record['meeting_id'] == $meetingId;
    });
}

/**
 * Mark attendance for a member
 */
function markAttendance($meetingId, $memberId, $status, $notes = '')
{
    $attendance = &$_SESSION['attendance'];

    // Check if already marked
    $found = false;
    foreach ($attendance as &$record) {
        if ($record['meeting_id'] == $meetingId && $record['member_id'] == $memberId) {
            $record['status'] = $status;
            $record['notes'] = $notes;
            $record['marked_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    // Add new record if not found
    if (!$found) {
        $ids = empty($attendance) ? [] : array_column($attendance, 'id');
        $newId = empty($ids) ? 1 : max($ids) + 1;
        $attendance[] = [
            'id' => $newId,
            'meeting_id' => $meetingId,
            'member_id' => $memberId,
            'status' => $status, // Present, Absent, Excused
            'notes' => $notes,
            'marked_at' => date('Y-m-d H:i:s'),
            'marked_by' => $_SESSION['user_name'] ?? 'System'
        ];
    }

    return true;
}

/**
 * Get attendance statistics for a meeting
 */
function getAttendanceStats($meetingId)
{
    $attendance = getMeetingAttendance($meetingId);
    $meeting = getMeetingById($meetingId);

    if (!$meeting) {
        return null;
    }

    // Get committee members
    $members = getCommitteeMembers($meeting['committee_id']);
    $totalMembers = count($members);

    $stats = [
        'total_members' => $totalMembers,
        'present' => 0,
        'absent' => 0,
        'excused' => 0,
        'not_marked' => $totalMembers,
        'attendance_rate' => 0,
        'has_quorum' => false
    ];

    foreach ($attendance as $record) {
        if ($record['status'] === 'Present') {
            $stats['present']++;
        } elseif ($record['status'] === 'Absent') {
            $stats['absent']++;
        } elseif ($record['status'] === 'Excused') {
            $stats['excused']++;
        }
    }

    $stats['not_marked'] = $totalMembers - count($attendance);
    $stats['attendance_rate'] = $totalMembers > 0 ? round(($stats['present'] / $totalMembers) * 100, 1) : 0;

    // Quorum is typically 50% + 1
    $quorumRequired = floor($totalMembers / 2) + 1;
    $stats['has_quorum'] = $stats['present'] >= $quorumRequired;
    $stats['quorum_required'] = $quorumRequired;

    return $stats;
}

/**
 * Delete attendance record
 */
function deleteAttendance($id)
{
    $attendance = &$_SESSION['attendance'];
    foreach ($attendance as $key => $record) {
        if ($record['id'] == $id) {
            unset($attendance[$key]);
            $_SESSION['attendance'] = array_values($attendance);
            return true;
        }
    }
    return false;
}

/**
 * Meeting Minutes Functions
 */

// Initialize minutes data
if (!isset($_SESSION['minutes'])) {
    $_SESSION['minutes'] = [];
}

/**
 * Get minutes for a meeting
 */
function getMeetingMinutes($meetingId)
{
    $minutes = $_SESSION['minutes'] ?? [];
    foreach ($minutes as $minute) {
        if ($minute['meeting_id'] == $meetingId) {
            return $minute;
        }
    }
    return null;
}

/**
 * Save meeting minutes
 */
function saveMinutes($meetingId, $data)
{
    $minutes = &$_SESSION['minutes'];
    $existing = getMeetingMinutes($meetingId);

    if ($existing) {
        // Update existing
        foreach ($minutes as &$minute) {
            if ($minute['meeting_id'] == $meetingId) {
                $minute['content'] = $data['content'] ?? $minute['content'];
                $minute['decisions'] = $data['decisions'] ?? $minute['decisions'];
                $minute['action_items'] = $data['action_items'] ?? $minute['action_items'];
                $minute['attendees'] = $data['attendees'] ?? $minute['attendees'];
                $minute['updated_at'] = date('Y-m-d H:i:s');
                $minute['updated_by'] = $_SESSION['user_name'] ?? 'System';
                return true;
            }
        }
    } else {
        // Create new
        $newId = empty($minutes) ? 1 : max(array_column($minutes, 'id')) + 1;
        $minutes[] = [
            'id' => $newId,
            'meeting_id' => $meetingId,
            'content' => $data['content'] ?? '',
            'decisions' => $data['decisions'] ?? [],
            'action_items' => $data['action_items'] ?? [],
            'attendees' => $data['attendees'] ?? [],
            'status' => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_name'] ?? 'System',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['user_name'] ?? 'System',
            'approved_at' => null,
            'approved_by' => null
        ];
        return true;
    }

    return false;
}

/**
 * Approve meeting minutes
 */
function approveMinutes($meetingId)
{
    $minutes = &$_SESSION['minutes'];
    foreach ($minutes as &$minute) {
        if ($minute['meeting_id'] == $meetingId) {
            $minute['status'] = 'Approved';
            $minute['approved_at'] = date('Y-m-d H:i:s');
            $minute['approved_by'] = $_SESSION['user_name'] ?? 'System';
            return true;
        }
    }
    return false;
}

/**
 * Delete meeting minutes
 */
function deleteMinutes($meetingId)
{
    $minutes = &$_SESSION['minutes'];
    foreach ($minutes as $key => $minute) {
        if ($minute['meeting_id'] == $meetingId) {
            unset($minutes[$key]);
            $_SESSION['minutes'] = array_values($minutes);
            return true;
        }
    }
    return false;
}

/**
 * Meeting Documents Functions
 */

// Initialize documents data
if (!isset($_SESSION['meeting_documents'])) {
    $_SESSION['meeting_documents'] = [];
}

/**
 * Get documents for a meeting
 */
function getMeetingDocuments($meetingId)
{
    $documents = $_SESSION['meeting_documents'] ?? [];
    return array_filter($documents, function ($doc) use ($meetingId) {
        return $doc['meeting_id'] == $meetingId;
    });
}

/**
 * Upload/Add document (simulated)
 */
function addMeetingDocument($meetingId, $data)
{
    $documents = &$_SESSION['meeting_documents'];
    $newId = empty($documents) ? 1 : max(array_column($documents, 'id')) + 1;

    $documents[] = [
        'id' => $newId,
        'meeting_id' => $meetingId,
        'name' => $data['name'],
        'category' => $data['category'] ?? 'Other',
        'description' => $data['description'] ?? '',
        'file_path' => $data['file_path'] ?? '', // Simulated path
        'file_size' => $data['file_size'] ?? 0,
        'file_type' => $data['file_type'] ?? '',
        'version' => 1,
        'uploaded_by' => $_SESSION['user_name'] ?? 'System',
        'uploaded_at' => date('Y-m-d H:i:s')
    ];

    return $newId;
}

/**
 * Update document
 */
function updateMeetingDocument($id, $data)
{
    $documents = &$_SESSION['meeting_documents'];
    foreach ($documents as &$doc) {
        if ($doc['id'] == $id) {
            if (isset($data['name']))
                $doc['name'] = $data['name'];
            if (isset($data['category']))
                $doc['category'] = $data['category'];
            if (isset($data['description']))
                $doc['description'] = $data['description'];
            return true;
        }
    }
    return false;
}

/**
 * Delete document
 */
function deleteMeetingDocument($id)
{
    $documents = &$_SESSION['meeting_documents'];
    foreach ($documents as $key => $doc) {
        if ($doc['id'] == $id) {
            unset($documents[$key]);
            $_SESSION['meeting_documents'] = array_values($documents);
            return true;
        }
    }
    return false;
}

/**
 * Get document by ID
 */
function getMeetingDocumentById($id)
{
    $documents = $_SESSION['meeting_documents'] ?? [];
    foreach ($documents as $doc) {
        if ($doc['id'] == $id) {
            return $doc;
        }
    }
    return null;
}

/**
 * Agenda Comments Functions
 */

// Initialize agenda comments
if (!isset($_SESSION['agenda_comments'])) {
    $_SESSION['agenda_comments'] = [];
}

/**
 * Get comments for an agenda/meeting
 */
function getAgendaComments($meetingId)
{
    $comments = $_SESSION['agenda_comments'] ?? [];
    return array_filter($comments, function ($c) use ($meetingId) {
        return $c['meeting_id'] == $meetingId;
    });
}

/**
 * Add comment to agenda item
 */
function addAgendaComment($meetingId, $itemId, $comment)
{
    $comments = &$_SESSION['agenda_comments'];
    $newId = empty($comments) ? 1 : max(array_column($comments, 'id')) + 1;

    $comments[] = [
        'id' => $newId,
        'meeting_id' => $meetingId,
        'item_id' => $itemId,
        'comment' => $comment,
        'author' => $_SESSION['user_name'] ?? 'User',
        'created_at' => date('Y-m-d H:i:s')
    ];

    return $newId;
}

/**
 * Agenda Builder Functions
 */
function getDistributionLog($agendaId)
{
    $distribution = $_SESSION['agenda_distribution'] ?? [];
    return array_filter($distribution, function ($record) use ($agendaId) {
        return $record['agenda_id'] == $agendaId;
    });
}

/**
 * Log agenda distribution
 */
function logAgendaDistribution($meetingId, $recipients, $method)
{
    if (!isset($_SESSION['agenda_distribution'])) {
        $_SESSION['agenda_distribution'] = [];
    }

    $distribution = &$_SESSION['agenda_distribution'];
    $newId = empty($distribution) ? 1 : max(array_column($distribution, 'id')) + 1;

    $distribution[] = [
        'id' => $newId,
        'agenda_id' => $meetingId,
        'recipients' => $recipients,
        'method' => $method,
        'distributed_by' => $_SESSION['user_name'] ?? 'System',
        'distributed_at' => date('Y-m-d H:i:s')
    ];

    return $newId;
}


// Initialize Action Items with sample data
if (!isset($_SESSION['action_items'])) {
    $_SESSION['action_items'] = [
        // Overdue items
        [
            'id' => 1,
            'title' => 'Review 2025 Budget Proposal',
            'description' => 'Comprehensive review of the proposed 2025 annual budget including all departmental allocations and revenue projections.',
            'assigned_to' => 'Hon. Maria Santos',
            'created_by' => 'Admin User',
            'created_date' => '2024-11-15 10:30:00',
            'due_date' => '2024-12-05',
            'completed_date' => null,
            'status' => 'In Progress',
            'priority' => 'High',
            'progress' => 65,
            'category' => 'Review',
            'tags' => ['budget', 'finance', 'urgent'],
            'estimated_hours' => 20,
            'actual_hours' => 15,
            'committee_id' => 1,
            'meeting_id' => 1,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Priority item for next committee meeting',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 2,
            'title' => 'Prepare Healthcare Facilities Report',
            'description' => 'Compile comprehensive report on current state of healthcare facilities in the city including recommendations for improvements.',
            'assigned_to' => 'Dr. Juan Cruz',
            'created_by' => 'Admin User',
            'created_date' => '2024-11-20 14:00:00',
            'due_date' => '2024-12-10',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'High',
            'progress' => 0,
            'category' => 'Report',
            'tags' => ['health', 'facilities', 'report'],
            'estimated_hours' => 30,
            'actual_hours' => null,
            'committee_id' => 2,
            'meeting_id' => 2,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Need to coordinate with Department of Health',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        // In Progress items
        [
            'id' => 3,
            'title' => 'Draft New Traffic Management Ordinance',
            'description' => 'Draft ordinance for improved traffic management in downtown area including parking regulations and one-way streets.',
            'assigned_to' => 'Atty. Pedro Reyes',
            'created_by' => 'Committee Secretary',
            'created_date' => '2024-12-01 09:00:00',
            'due_date' => '2025-01-15',
            'completed_date' => null,
            'status' => 'In Progress',
            'priority' => 'Medium',
            'progress' => 40,
            'category' => 'Draft',
            'tags' => ['traffic', 'ordinance', 'legal'],
            'estimated_hours' => 25,
            'actual_hours' => 10,
            'committee_id' => 3,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => 1,
            'dependencies' => [],
            'notes' => 'Awaiting input from traffic engineering department',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 4,
            'title' => 'Conduct School Infrastructure Assessment',
            'description' => 'Site visits and assessment of all public school buildings to identify repair and improvement needs.',
            'assigned_to' => 'Engr. Rosa Garcia',
            'created_by' => 'Education Committee',
            'created_date' => '2024-12-05 11:30:00',
            'due_date' => '2025-01-20',
            'completed_date' => null,
            'status' => 'In Progress',
            'priority' => 'High',
            'progress' => 55,
            'category' => 'Research',
            'tags' => ['education', 'infrastructure', 'assessment'],
            'estimated_hours' => 40,
            'actual_hours' => 22,
            'committee_id' => 3,
            'meeting_id' => 3,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => '12 out of 20 schools assessed so far',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 5,
            'title' => 'Research Best Practices for Waste Management',
            'description' => 'Study waste management programs from other cities and prepare recommendations for implementation.',
            'assigned_to' => 'Hon. Carlos Mendoza',
            'created_by' => 'Environment Committee',
            'created_date' => '2024-12-08 10:00:00',
            'due_date' => '2025-01-25',
            'completed_date' => null,
            'status' => 'In Progress',
            'priority' => 'Medium',
            'progress' => 30,
            'category' => 'Research',
            'tags' => ['environment', 'waste', 'research'],
            'estimated_hours' => 15,
            'actual_hours' => 5,
            'committee_id' => 4,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => 2,
            'dependencies' => [],
            'notes' => 'Focus on recycling programs and composting initiatives',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        // To Do items
        [
            'id' => 6,
            'title' => 'Organize Community Consultation on Park Development',
            'description' => 'Plan and execute community consultation meeting for proposed new park in District 5.',
            'assigned_to' => 'Ms. Ana Lopez',
            'created_by' => 'Parks Committee',
            'created_date' => '2024-12-10 14:30:00',
            'due_date' => '2025-02-01',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'Medium',
            'progress' => 0,
            'category' => 'Coordinate',
            'tags' => ['parks', 'community', 'consultation'],
            'estimated_hours' => 12,
            'actual_hours' => null,
            'committee_id' => 5,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => 3,
            'dependencies' => [],
            'notes' => 'Coordinate with barangay officials',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 7,
            'title' => 'Prepare Cost Estimate for Street Lighting Project',
            'description' => 'Detailed cost estimate for installation of LED street lights on Oak Avenue and surrounding streets.',
            'assigned_to' => 'Budget Officer',
            'created_by' => 'Finance Committee',
            'created_date' => '2024-12-11 09:15:00',
            'due_date' => '2025-02-05',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'Low',
            'progress' => 0,
            'category' => 'General',
            'tags' => ['budget', 'infrastructure', 'lighting'],
            'estimated_hours' => 8,
            'actual_hours' => null,
            'committee_id' => 1,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => 4,
            'dependencies' => [],
            'notes' => 'Waiting for engineering specifications',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 8,
            'title' => 'Review Proposed Zoning Changes',
            'description' => 'Review and provide recommendations on proposed zoning changes for commercial district expansion.',
            'assigned_to' => 'Hon. Lisa Tan',
            'created_by' => 'Urban Planning',
            'created_date' => '2024-12-12 11:00:00',
            'due_date' => '2025-02-10',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'Medium',
            'progress' => 0,
            'category' => 'Review',
            'tags' => ['zoning', 'planning', 'development'],
            'estimated_hours' => 18,
            'actual_hours' => null,
            'committee_id' => 6,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Public hearing required',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        // Completed items
        [
            'id' => 9,
            'title' => 'Submit Quarterly Financial Report',
            'description' => 'Prepare and submit Q3 2024 financial report to city council.',
            'assigned_to' => 'City Treasurer',
            'created_by' => 'Finance Committee',
            'created_date' => '2024-10-01 08:00:00',
            'due_date' => '2024-11-30',
            'completed_date' => '2024-11-28 16:45:00',
            'status' => 'Done',
            'priority' => 'High',
            'progress' => 100,
            'category' => 'Report',
            'tags' => ['finance', 'report', 'quarterly'],
            'estimated_hours' => 16,
            'actual_hours' => 14,
            'committee_id' => 1,
            'meeting_id' => 1,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Submitted ahead of schedule',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => true,
            'recurrence_pattern' => 'Quarterly'
        ],
        [
            'id' => 10,
            'title' => 'Coordinate with Department of Health on Vaccination Program',
            'description' => 'Arrange coordination meeting with DOH for citywide vaccination program rollout.',
            'assigned_to' => 'Dr. Juan Cruz',
            'created_by' => 'Health Committee',
            'created_date' => '2024-11-10 10:00:00',
            'due_date' => '2024-12-01',
            'completed_date' => '2024-11-30 14:00:00',
            'status' => 'Done',
            'priority' => 'High',
            'progress' => 100,
            'category' => 'Coordinate',
            'tags' => ['health', 'vaccination', 'coordination'],
            'estimated_hours' => 6,
            'actual_hours' => 5,
            'committee_id' => 2,
            'meeting_id' => 2,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Meeting held successfully, program scheduled for January',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 11,
            'title' => 'Inspect Flood Control Infrastructure',
            'description' => 'Conduct inspection of all flood control structures before rainy season.',
            'assigned_to' => 'Engr. Rosa Garcia',
            'created_by' => 'Public Works',
            'created_date' => '2024-10-15 09:00:00',
            'due_date' => '2024-11-20',
            'completed_date' => '2024-11-18 17:30:00',
            'status' => 'Done',
            'priority' => 'High',
            'progress' => 100,
            'category' => 'General',
            'tags' => ['infrastructure', 'flood', 'inspection'],
            'estimated_hours' => 24,
            'actual_hours' => 26,
            'committee_id' => 7,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Minor repairs identified and scheduled',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => true,
            'recurrence_pattern' => 'Annually'
        ],
        // More upcoming items
        [
            'id' => 12,
            'title' => 'Draft Resolution for Senior Citizen Benefits',
            'description' => 'Prepare resolution to enhance benefits and services for senior citizens.',
            'assigned_to' => 'Atty. Pedro Reyes',
            'created_by' => 'Social Services',
            'created_date' => '2024-12-13 10:30:00',
            'due_date' => '2025-01-30',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'Medium',
            'progress' => 0,
            'category' => 'Draft',
            'tags' => ['social', 'seniors', 'resolution'],
            'estimated_hours' => 10,
            'actual_hours' => null,
            'committee_id' => 8,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Consult with senior citizen organizations',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 13,
            'title' => 'Update Emergency Response Protocols',
            'description' => 'Review and update city emergency response protocols based on recent drills and incidents.',
            'assigned_to' => 'Disaster Risk Officer',
            'created_by' => 'Public Safety',
            'created_date' => '2024-12-14 13:00:00',
            'due_date' => '2025-02-15',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'High',
            'progress' => 0,
            'category' => 'Review',
            'tags' => ['safety', 'emergency', 'protocols'],
            'estimated_hours' => 20,
            'actual_hours' => null,
            'committee_id' => 9,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Coordinate with fire and police departments',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 14,
            'title' => 'Prepare Tourism Development Plan',
            'description' => 'Develop comprehensive plan for promoting local tourism and cultural heritage sites.',
            'assigned_to' => 'Tourism Officer',
            'created_by' => 'Economic Development',
            'created_date' => '2024-12-15 11:00:00',
            'due_date' => '2025-03-01',
            'completed_date' => null,
            'status' => 'To Do',
            'priority' => 'Low',
            'progress' => 0,
            'category' => 'General',
            'tags' => ['tourism', 'development', 'culture'],
            'estimated_hours' => 35,
            'actual_hours' => null,
            'committee_id' => 10,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Include stakeholder consultations',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => false,
            'recurrence_pattern' => null
        ],
        [
            'id' => 15,
            'title' => 'Review and Update IT Security Policies',
            'description' => 'Comprehensive review of IT security policies and procedures for city government systems.',
            'assigned_to' => 'IT Administrator',
            'created_by' => 'Admin Services',
            'created_date' => '2024-12-16 09:30:00',
            'due_date' => '2025-01-31',
            'completed_date' => null,
            'status' => 'In Progress',
            'priority' => 'High',
            'progress' => 25,
            'category' => 'Review',
            'tags' => ['IT', 'security', 'policies'],
            'estimated_hours' => 16,
            'actual_hours' => 4,
            'committee_id' => null,
            'meeting_id' => null,
            'agenda_item_id' => null,
            'referral_id' => null,
            'dependencies' => [],
            'notes' => 'Cybersecurity audit in progress',
            'attachments' => [],
            'reminders' => [],
            'is_recurring' => true,
            'recurrence_pattern' => 'Annually'
        ]
    ];
}
?>