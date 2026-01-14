<?php
/**
 * Sample Data Initialization Script
 * 
 * This script creates interconnected sample data for the entire Committee Management System.
 * Run this once to populate the system with realistic test data.
 * 
 * Data Flow:
 * 1. Committees → 2. Members → 3. Meetings → 4. Agendas → 5. Attendance → 6. Minutes → 7. Action Items → 8. Referrals
 */

require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../app/helpers/DataHelper.php';

// Clear existing data
$_SESSION['committees'] = [];
$_SESSION['committee_members'] = [];
$_SESSION['meetings'] = [];
$_SESSION['agenda_items'] = [];
$_SESSION['attendance'] = [];
$_SESSION['minutes'] = [];
$_SESSION['action_items'] = [];
$_SESSION['referrals'] = [];

echo "<!DOCTYPE html><html><head><title>Initializing Sample Data</title></head><body>";
echo "<h1>Initializing Sample Data...</h1>";

// ========================================
// 1. CREATE COMMITTEES
// ========================================
echo "<h2>Creating Committees...</h2>";

$committeeIds = [];

// Committee 1: Finance Committee
$committeeIds[1] = createCommittee([
    'name' => 'Finance and Budget Committee',
    'type' => 'Standing',
    'chair' => 'Hon. Maria Santos',
    'vice_chair' => 'Hon. Juan Dela Cruz',
    'jurisdiction' => 'Oversight of financial matters, budget appropriations, revenue generation, and fiscal policies',
    'description' => 'Responsible for reviewing and approving the annual budget, monitoring expenditures, and ensuring fiscal responsibility',
    'status' => 'Active'
]);

// Committee 2: Education Committee
$committeeIds[2] = createCommittee([
    'name' => 'Education and Culture Committee',
    'type' => 'Standing',
    'chair' => 'Hon. Pedro Reyes',
    'vice_chair' => 'Hon. Ana Garcia',
    'jurisdiction' => 'Education policies, cultural programs, libraries, and youth development',
    'description' => 'Oversees educational institutions, cultural preservation, and youth empowerment programs',
    'status' => 'Active'
]);

// Committee 3: Infrastructure Committee
$committeeIds[3] = createCommittee([
    'name' => 'Infrastructure and Public Works Committee',
    'type' => 'Standing',
    'chair' => 'Hon. Roberto Cruz',
    'vice_chair' => 'Hon. Linda Ramos',
    'jurisdiction' => 'Roads, bridges, public buildings, and infrastructure development',
    'description' => 'Manages infrastructure projects, public works maintenance, and urban development',
    'status' => 'Active'
]);

echo "✓ Created 3 committees<br>";

// ========================================
// 2. ADD COMMITTEE MEMBERS
// ========================================
echo "<h2>Adding Committee Members...</h2>";

$_SESSION['committee_members'] = [];
$memberIdCounter = 1;

// Finance Committee Members
$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[1],
    'name' => 'Hon. Maria Santos',
    'position' => 'Chairperson',
    'role' => 'Member',
    'district' => 'District 1',
    'contact_number' => '09171234567',
    'email' => 'maria.santos@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[1],
    'name' => 'Hon. Juan Dela Cruz',
    'position' => 'Vice-Chairperson',
    'role' => 'Member',
    'district' => 'District 2',
    'contact_number' => '09181234567',
    'email' => 'juan.delacruz@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[1],
    'name' => 'Hon. Carmen Lopez',
    'position' => 'Member',
    'role' => 'Member',
    'district' => 'District 3',
    'contact_number' => '09191234567',
    'email' => 'carmen.lopez@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[1],
    'name' => 'Hon. Ricardo Tan',
    'position' => 'Member',
    'role' => 'Member',
    'district' => 'District 4',
    'contact_number' => '09201234567',
    'email' => 'ricardo.tan@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[1],
    'name' => 'Hon. Sofia Mendoza',
    'position' => 'Secretary',
    'role' => 'Member',
    'district' => 'District 5',
    'contact_number' => '09211234567',
    'email' => 'sofia.mendoza@legislature.gov'
];

// Education Committee Members
$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[2],
    'name' => 'Hon. Pedro Reyes',
    'position' => 'Chairperson',
    'role' => 'Member',
    'district' => 'District 6',
    'contact_number' => '09221234567',
    'email' => 'pedro.reyes@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[2],
    'name' => 'Hon. Ana Garcia',
    'position' => 'Vice-Chairperson',
    'role' => 'Member',
    'district' => 'District 7',
    'contact_number' => '09231234567',
    'email' => 'ana.garcia@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[2],
    'name' => 'Hon. Miguel Torres',
    'position' => 'Member',
    'role' => 'Member',
    'district' => 'District 8',
    'contact_number' => '09241234567',
    'email' => 'miguel.torres@legislature.gov'
];

// Infrastructure Committee Members
$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[3],
    'name' => 'Hon. Roberto Cruz',
    'position' => 'Chairperson',
    'role' => 'Member',
    'district' => 'District 9',
    'contact_number' => '09251234567',
    'email' => 'roberto.cruz@legislature.gov'
];

$_SESSION['committee_members'][] = [
    'id' => $memberIdCounter++,
    'committee_id' => $committeeIds[3],
    'name' => 'Hon. Linda Ramos',
    'position' => 'Vice-Chairperson',
    'role' => 'Member',
    'district' => 'District 10',
    'contact_number' => '09261234567',
    'email' => 'linda.ramos@legislature.gov'
];

echo "✓ Added 10 committee members<br>";

// ========================================
// 3. SCHEDULE MEETINGS
// ========================================
echo "<h2>Scheduling Meetings...</h2>";

$meetingIds = [];

// Finance Committee Meeting 1 (Completed)
$meetingIds[1] = createMeeting([
    'committee_id' => $committeeIds[1],
    'committee_name' => 'Finance and Budget Committee',
    'title' => '2026 First Quarter Budget Review',
    'description' => 'Review and approval of Q1 2026 budget allocations and expenditure reports',
    'date' => '2026-01-08',
    'time_start' => '09:00',
    'time_end' => '12:00',
    'venue' => 'Committee Room A, 3rd Floor',
    'status' => 'Completed',
    'is_public' => true,
    'agenda_status' => 'Approved'
]);

// Finance Committee Meeting 2 (Scheduled)
$meetingIds[2] = createMeeting([
    'committee_id' => $committeeIds[1],
    'committee_name' => 'Finance and Budget Committee',
    'title' => 'Revenue Enhancement Strategies Discussion',
    'description' => 'Discussion on new revenue generation strategies and tax policies',
    'date' => '2026-01-20',
    'time_start' => '14:00',
    'time_end' => '17:00',
    'venue' => 'Committee Room A, 3rd Floor',
    'status' => 'Scheduled',
    'is_public' => true,
    'agenda_status' => 'Published'
]);

// Education Committee Meeting (Scheduled)
$meetingIds[3] = createMeeting([
    'committee_id' => $committeeIds[2],
    'committee_name' => 'Education and Culture Committee',
    'title' => 'School Infrastructure Improvement Program',
    'description' => 'Review of proposed school building renovations and new classroom construction',
    'date' => '2026-01-15',
    'time_start' => '10:00',
    'time_end' => '13:00',
    'venue' => 'Committee Room B, 3rd Floor',
    'status' => 'Scheduled',
    'is_public' => true,
    'agenda_status' => 'Draft'
]);

// Infrastructure Committee Meeting (Scheduled)
$meetingIds[4] = createMeeting([
    'committee_id' => $committeeIds[3],
    'committee_name' => 'Infrastructure and Public Works Committee',
    'title' => 'Road Repair and Maintenance Program 2026',
    'description' => 'Planning for annual road maintenance and emergency repair fund allocation',
    'date' => '2026-01-18',
    'time_start' => '13:00',
    'time_end' => '16:00',
    'venue' => 'Committee Room C, 3rd Floor',
    'status' => 'Scheduled',
    'is_public' => true,
    'agenda_status' => 'Under Review'
]);

echo "✓ Created 4 meetings<br>";

// ========================================
// 4. CREATE AGENDA ITEMS
// ========================================
echo "<h2>Creating Agenda Items...</h2>";

// Meeting 1 Agenda Items (Finance - Completed)
$_SESSION['agenda_items'][] = [
    'id' => 1,
    'meeting_id' => $meetingIds[1],
    'title' => 'Call to Order',
    'description' => 'Opening remarks and roll call',
    'presenter' => 'Hon. Maria Santos',
    'duration' => 5,
    'type' => 'Call to Order',
    'order' => 1
];

$_SESSION['agenda_items'][] = [
    'id' => 2,
    'meeting_id' => $meetingIds[1],
    'title' => 'Approval of Previous Minutes',
    'description' => 'Review and approval of December 2025 meeting minutes',
    'presenter' => 'Hon. Sofia Mendoza',
    'duration' => 10,
    'type' => 'Approval',
    'order' => 2
];

$_SESSION['agenda_items'][] = [
    'id' => 3,
    'meeting_id' => $meetingIds[1],
    'title' => 'Q1 2026 Budget Presentation',
    'description' => 'Presentation of proposed Q1 budget allocations by department',
    'presenter' => 'Budget Officer',
    'duration' => 45,
    'type' => 'Presentation',
    'order' => 3
];

$_SESSION['agenda_items'][] = [
    'id' => 4,
    'meeting_id' => $meetingIds[1],
    'title' => 'Discussion and Deliberation',
    'description' => 'Committee discussion on budget proposals and amendments',
    'presenter' => 'All Members',
    'duration' => 60,
    'type' => 'Discussion',
    'order' => 4
];

$_SESSION['agenda_items'][] = [
    'id' => 5,
    'meeting_id' => $meetingIds[1],
    'title' => 'Voting on Budget Approval',
    'description' => 'Vote on Q1 2026 budget approval',
    'presenter' => 'Hon. Maria Santos',
    'duration' => 15,
    'type' => 'Voting',
    'order' => 5
];

$_SESSION['agenda_items'][] = [
    'id' => 6,
    'meeting_id' => $meetingIds[1],
    'title' => 'Adjournment',
    'description' => 'Closing remarks and next meeting schedule',
    'presenter' => 'Hon. Maria Santos',
    'duration' => 5,
    'type' => 'Adjournment',
    'order' => 6
];

// Meeting 2 Agenda Items (Finance - Upcoming)
$_SESSION['agenda_items'][] = [
    'id' => 7,
    'meeting_id' => $meetingIds[2],
    'title' => 'Call to Order',
    'description' => 'Opening remarks',
    'presenter' => 'Hon. Maria Santos',
    'duration' => 5,
    'type' => 'Call to Order',
    'order' => 1
];

$_SESSION['agenda_items'][] = [
    'id' => 8,
    'meeting_id' => $meetingIds[2],
    'title' => 'Revenue Enhancement Proposals',
    'description' => 'Presentation of new revenue generation strategies',
    'presenter' => 'Revenue Officer',
    'duration' => 40,
    'type' => 'Presentation',
    'order' => 2
];

$_SESSION['agenda_items'][] = [
    'id' => 9,
    'meeting_id' => $meetingIds[2],
    'title' => 'Tax Policy Review',
    'description' => 'Discussion on proposed tax policy amendments',
    'presenter' => 'Hon. Juan Dela Cruz',
    'duration' => 50,
    'type' => 'Discussion',
    'order' => 3
];

// Meeting 3 Agenda Items (Education)
$_SESSION['agenda_items'][] = [
    'id' => 10,
    'meeting_id' => $meetingIds[3],
    'title' => 'School Infrastructure Assessment',
    'description' => 'Review of current school building conditions',
    'presenter' => 'Education Officer',
    'duration' => 30,
    'type' => 'Presentation',
    'order' => 1
];

$_SESSION['agenda_items'][] = [
    'id' => 11,
    'meeting_id' => $meetingIds[3],
    'title' => 'Proposed Renovation Projects',
    'description' => 'Discussion on priority renovation projects',
    'presenter' => 'Hon. Pedro Reyes',
    'duration' => 45,
    'type' => 'Discussion',
    'order' => 2
];

echo "✓ Created 11 agenda items<br>";

// ========================================
// 5. MARK ATTENDANCE (for completed meeting)
// ========================================
echo "<h2>Recording Attendance...</h2>";

$financeMembers = getCommitteeMembers($committeeIds[1]);
foreach ($financeMembers as $member) {
    markAttendance($meetingIds[1], $member['id'], 'Present', 'On time');
}

echo "✓ Marked attendance for 5 members<br>";

// ========================================
// 6. CREATE MINUTES (for completed meeting)
// ========================================
echo "<h2>Creating Meeting Minutes...</h2>";

saveMinutes($meetingIds[1], [
    'content' => "The Finance and Budget Committee convened on January 8, 2026, at 9:00 AM in Committee Room A.\n\nChairperson Hon. Maria Santos called the meeting to order with all members present. The minutes from the previous meeting were approved unanimously.\n\nThe Budget Officer presented the Q1 2026 budget allocations, highlighting key areas including education (30%), infrastructure (25%), health services (20%), and administrative costs (15%).\n\nExtensive deliberation followed, with Hon. Juan Dela Cruz proposing a 5% increase in education funding, which was seconded by Hon. Carmen Lopez. After discussion, the amendment was approved by majority vote.\n\nThe final Q1 2026 budget was approved with amendments at 11:45 AM.\n\nThe meeting was adjourned at 12:00 PM.",
    'decisions' => [
        'Approved Q1 2026 Budget with amendments',
        'Increased education funding by 5%',
        'Authorized emergency fund allocation of PHP 5M'
    ],
    'action_items' => [
        'Budget Officer to prepare detailed breakdown by January 15',
        'Finance team to implement new accounting system',
        'Schedule follow-up meeting for revenue discussion'
    ],
    'attendees' => array_column($financeMembers, 'name')
]);

approveMinutes($meetingIds[1]);

echo "✓ Created and approved minutes<br>";

// ========================================
// 7. CREATE ACTION ITEMS
// ========================================
echo "<h2>Creating Action Items...</h2>";

// Keep existing action items and add new ones linked to meetings
$actionItemId = count($_SESSION['action_items']) + 1;

$_SESSION['action_items'][] = [
    'id' => $actionItemId++,
    'title' => 'Prepare Detailed Budget Breakdown',
    'description' => 'Create comprehensive breakdown of Q1 2026 budget by department and line item',
    'committee_id' => $committeeIds[1],
    'committee_name' => 'Finance and Budget Committee',
    'assigned_to' => 'Budget Officer',
    'assigned_by' => 'Hon. Maria Santos',
    'priority' => 'High',
    'status' => 'In Progress',
    'due_date' => '2026-01-15',
    'created_at' => '2026-01-08 12:00:00',
    'progress' => 60,
    'meeting_id' => $meetingIds[1]
];

$_SESSION['action_items'][] = [
    'id' => $actionItemId++,
    'title' => 'Implement New Accounting System',
    'description' => 'Deploy and configure new financial management software across all departments',
    'committee_id' => $committeeIds[1],
    'committee_name' => 'Finance and Budget Committee',
    'assigned_to' => 'IT Department',
    'assigned_by' => 'Hon. Juan Dela Cruz',
    'priority' => 'High',
    'status' => 'To Do',
    'due_date' => '2026-02-01',
    'created_at' => '2026-01-08 12:00:00',
    'progress' => 0,
    'meeting_id' => $meetingIds[1]
];

$_SESSION['action_items'][] = [
    'id' => $actionItemId++,
    'title' => 'Review School Building Safety Standards',
    'description' => 'Conduct safety assessment of all public school buildings',
    'committee_id' => $committeeIds[2],
    'committee_name' => 'Education and Culture Committee',
    'assigned_to' => 'Engineering Department',
    'assigned_by' => 'Hon. Pedro Reyes',
    'priority' => 'High',
    'status' => 'To Do',
    'due_date' => '2026-01-25',
    'created_at' => '2026-01-10 10:00:00',
    'progress' => 0,
    'meeting_id' => $meetingIds[3]
];

echo "✓ Created 3 action items linked to meetings<br>";

// ========================================
// 8. CREATE REFERRALS
// ========================================
echo "<h2>Creating Referrals...</h2>";

$_SESSION['referrals'][] = [
    'id' => 1,
    'reference_number' => 'REF-2026-001',
    'subject' => 'Request for Budget Augmentation - Health Services',
    'description' => 'Department of Health requests additional funding for medical equipment and supplies',
    'from_office' => 'Department of Health',
    'committee_id' => $committeeIds[1],
    'committee_name' => 'Finance and Budget Committee',
    'priority' => 'High',
    'status' => 'In Progress',
    'date_received' => '2026-01-05',
    'date_referred' => '2026-01-06',
    'deadline' => '2026-01-30',
    'assigned_to' => 'Hon. Maria Santos',
    'created_at' => '2026-01-05 14:00:00'
];

$_SESSION['referrals'][] = [
    'id' => 2,
    'reference_number' => 'REF-2026-002',
    'subject' => 'Proposal for New School Construction',
    'description' => 'Request to construct new elementary school in District 5',
    'from_office' => 'Department of Education',
    'committee_id' => $committeeIds[2],
    'committee_name' => 'Education and Culture Committee',
    'priority' => 'Medium',
    'status' => 'Pending',
    'date_received' => '2026-01-07',
    'date_referred' => '2026-01-08',
    'deadline' => '2026-02-15',
    'assigned_to' => 'Hon. Pedro Reyes',
    'created_at' => '2026-01-07 09:00:00'
];

$_SESSION['referrals'][] = [
    'id' => 3,
    'reference_number' => 'REF-2026-003',
    'subject' => 'Road Repair Emergency Request',
    'description' => 'Urgent repair needed for damaged bridge in District 9',
    'from_office' => 'Public Works Department',
    'committee_id' => $committeeIds[3],
    'committee_name' => 'Infrastructure and Public Works Committee',
    'priority' => 'High',
    'status' => 'In Progress',
    'date_received' => '2026-01-06',
    'date_referred' => '2026-01-06',
    'deadline' => '2026-01-20',
    'assigned_to' => 'Hon. Roberto Cruz',
    'created_at' => '2026-01-06 11:00:00'
];

echo "✓ Created 3 referrals<br>";

// ========================================
// SUMMARY
// ========================================
echo "<h2 style='color: green;'>✓ Sample Data Initialization Complete!</h2>";
echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li>✓ 3 Committees created</li>";
echo "<li>✓ 10 Committee Members added</li>";
echo "<li>✓ 4 Meetings scheduled (1 completed, 3 upcoming)</li>";
echo "<li>✓ 11 Agenda Items created</li>";
echo "<li>✓ 5 Attendance records marked</li>";
echo "<li>✓ 1 Meeting minutes created and approved</li>";
echo "<li>✓ 3 Action Items created</li>";
echo "<li>✓ 3 Referrals created</li>";
echo "</ul>";

echo "<h3>Data Interconnections:</h3>";
echo "<ul>";
echo "<li>✓ All members linked to committees</li>";
echo "<li>✓ All meetings linked to committees</li>";
echo "<li>✓ All agenda items linked to meetings</li>";
echo "<li>✓ Attendance linked to meetings and members</li>";
echo "<li>✓ Minutes linked to meetings</li>";
echo "<li>✓ Action items linked to committees and meetings</li>";
echo "<li>✓ Referrals linked to committees</li>";
echo "</ul>";

echo "<br><a href='../public/dashboard.php' style='display: inline-block; padding: 10px 20px; background: #dc2626; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>";
echo "</body></html>";
?>