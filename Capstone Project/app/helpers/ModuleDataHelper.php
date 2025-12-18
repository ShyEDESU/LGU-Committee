<?php
/**
 * Module Data Helper
 * 
 * Stores and manages dummy data for all modules
 * Data is stored in session and arrays for testing
 * Can be easily swapped with database queries later
 */

class ModuleDataHelper {
    
    /**
     * Initialize session data storage for modules
     */
    public static function initializeModuleData() {
        if (!isset($_SESSION['module_data'])) {
            $_SESSION['module_data'] = self::getDummyData();
        }
    }

    /**
     * Get dummy data for all modules
     */
    private static function getDummyData() {
        return [
            'committees' => [
                ['id' => 1, 'name' => 'Finance Committee', 'type' => 'Standing', 'members' => 7, 'status' => 'Active', 'created' => '2025-01-15'],
                ['id' => 2, 'name' => 'Public Safety', 'type' => 'Standing', 'members' => 5, 'status' => 'Active', 'created' => '2025-01-10'],
                ['id' => 3, 'name' => 'Parks & Recreation', 'type' => 'Special', 'members' => 4, 'status' => 'Active', 'created' => '2025-02-01'],
            ],
            'members' => [
                ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com', 'role' => 'Chairperson', 'committee' => 'Finance Committee', 'status' => 'Active'],
                ['id' => 2, 'name' => 'Mary Johnson', 'email' => 'mary@example.com', 'role' => 'Vice-Chair', 'committee' => 'Finance Committee', 'status' => 'Active'],
                ['id' => 3, 'name' => 'Robert Brown', 'email' => 'robert@example.com', 'role' => 'Member', 'committee' => 'Public Safety', 'status' => 'Active'],
            ],
            'meetings' => [
                ['id' => 1, 'title' => 'Finance Committee Meeting', 'date' => '2025-12-15', 'time' => '10:00 AM', 'location' => 'Conference Room A', 'status' => 'Scheduled'],
                ['id' => 2, 'title' => 'Public Safety Review', 'date' => '2025-12-16', 'time' => '2:00 PM', 'location' => 'Conference Room B', 'status' => 'Scheduled'],
                ['id' => 3, 'title' => 'Budget Review Session', 'date' => '2025-12-17', 'time' => '9:00 AM', 'location' => 'Board Room', 'status' => 'Completed'],
            ],
            'agendas' => [
                ['id' => 1, 'meeting_id' => 1, 'title' => 'Q4 Budget Review', 'items' => 5, 'status' => 'Draft', 'created' => '2025-12-01'],
                ['id' => 2, 'meeting_id' => 2, 'title' => 'Safety Protocol Updates', 'items' => 3, 'status' => 'Published', 'created' => '2025-12-05'],
                ['id' => 3, 'meeting_id' => 3, 'title' => 'Annual Report', 'items' => 7, 'status' => 'Completed', 'created' => '2025-11-20'],
            ],
            'referrals' => [
                ['id' => 1, 'title' => 'Budget Allocation Request', 'from_committee' => 'Finance', 'to_committee' => 'Executive', 'status' => 'Pending', 'created' => '2025-12-01'],
                ['id' => 2, 'title' => 'Policy Amendment Proposal', 'from_committee' => 'Public Safety', 'to_committee' => 'Legal Affairs', 'status' => 'Under Review', 'created' => '2025-11-28'],
                ['id' => 3, 'title' => 'Facility Upgrade Request', 'from_committee' => 'Parks & Rec', 'to_committee' => 'Finance', 'status' => 'Approved', 'created' => '2025-11-15'],
            ],
            'action_items' => [
                ['id' => 1, 'title' => 'Prepare Budget Report', 'assignee' => 'John Smith', 'due_date' => '2025-12-20', 'priority' => 'High', 'status' => 'In Progress'],
                ['id' => 2, 'title' => 'Review Safety Protocols', 'assignee' => 'Robert Brown', 'due_date' => '2025-12-18', 'priority' => 'High', 'status' => 'Not Started'],
                ['id' => 3, 'title' => 'Update Contact List', 'assignee' => 'Mary Johnson', 'due_date' => '2025-12-25', 'priority' => 'Medium', 'status' => 'Completed'],
            ],
            'documents' => [
                ['id' => 1, 'title' => 'Annual Budget 2025', 'type' => 'PDF', 'size' => '2.5 MB', 'uploaded' => '2025-12-01', 'status' => 'Published'],
                ['id' => 2, 'title' => 'Meeting Minutes Nov 2025', 'type' => 'PDF', 'size' => '1.2 MB', 'uploaded' => '2025-11-30', 'status' => 'Published'],
                ['id' => 3, 'title' => 'Policy Draft v2', 'type' => 'DOCX', 'size' => '0.8 MB', 'uploaded' => '2025-12-10', 'status' => 'Draft'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Budget Allocation Strategy', 'author' => 'John Smith', 'replies' => 5, 'status' => 'Active', 'created' => '2025-12-05'],
                ['id' => 2, 'title' => 'Safety Improvement Proposals', 'author' => 'Robert Brown', 'replies' => 3, 'status' => 'Active', 'created' => '2025-12-08'],
                ['id' => 3, 'title' => 'Park Renovation Plans', 'author' => 'Mary Johnson', 'replies' => 8, 'status' => 'Active', 'created' => '2025-12-10'],
            ],
            'reports' => [
                ['id' => 1, 'title' => 'Quarterly Summary Report', 'type' => 'Automated', 'generated' => '2025-12-10', 'pages' => 15, 'status' => 'Ready'],
                ['id' => 2, 'title' => 'Member Activity Report', 'type' => 'Custom', 'generated' => '2025-12-09', 'pages' => 8, 'status' => 'Ready'],
                ['id' => 3, 'title' => 'Budget Performance Report', 'type' => 'Automated', 'generated' => '2025-12-08', 'pages' => 12, 'status' => 'Ready'],
            ],
            'research' => [
                ['id' => 1, 'title' => 'Comparative Legislation Study', 'category' => 'Legal Analysis', 'status' => 'In Progress', 'requested' => '2025-12-01'],
                ['id' => 2, 'title' => 'Budget Benchmarking', 'category' => 'Policy Briefs', 'status' => 'Completed', 'requested' => '2025-11-20'],
                ['id' => 3, 'title' => 'Safety Best Practices', 'category' => 'Research Findings', 'status' => 'Completed', 'requested' => '2025-11-15'],
            ],
            'tasks' => [
                ['id' => 1, 'title' => 'Complete Budget Review', 'status' => 'In Progress', 'due_date' => '2025-12-20'],
                ['id' => 2, 'title' => 'Prepare Meeting Agenda', 'status' => 'Not Started', 'due_date' => '2025-12-15'],
                ['id' => 3, 'title' => 'Submit Committee Report', 'status' => 'Completed', 'due_date' => '2025-12-10'],
            ],
            'inter-committee' => [
                ['id' => 1, 'title' => 'Joint Budget Review', 'status' => 'Active', 'created' => '2025-12-01'],
                ['id' => 2, 'title' => 'Policy Coordination Meeting', 'status' => 'Active', 'created' => '2025-11-28'],
                ['id' => 3, 'title' => 'Cross-Committee Initiative', 'status' => 'Completed', 'created' => '2025-11-15'],
            ],
        ];
    }

    /**
     * Get data for a specific module
     * 
     * @param string $module Module name
     * @param string $data_type Type of data to retrieve
     * @return array
     */
    public static function getModuleData($module, $data_type = null) {
        self::initializeModuleData();
        
        $mapping = [
            'committee-structure' => 'committees',
            'committees' => 'committees',
            'member-assignment' => 'members',
            'meeting-scheduler' => 'meetings',
            'meetings' => 'meetings',
            'agenda-builder' => 'agendas',
            'referral-management' => 'referrals',
            'referrals' => 'referrals',
            'action-items' => 'action_items',
            'documents' => 'documents',
            'deliberation-tools' => 'discussions',
            'report-generation' => 'reports',
            'research-support' => 'research',
            'tasks' => 'tasks',
            'inter-committee' => 'inter-committee',
        ];

        $data_key = $mapping[$module] ?? null;
        
        if (!$data_key) {
            return [];
        }

        return $_SESSION['module_data'][$data_key] ?? [];
    }

    /**
     * Add a new item to module data
     * 
     * @param string $module Module name
     * @param array $item Item to add
     * @return bool
     */
    public static function addItem($module, $item) {
        self::initializeModuleData();
        
        $mapping = [
            'committee-structure' => 'committees',
            'committees' => 'committees',
            'member-assignment' => 'members',
            'meeting-scheduler' => 'meetings',
            'meetings' => 'meetings',
            'agenda-builder' => 'agendas',
            'referral-management' => 'referrals',
            'referrals' => 'referrals',
            'action-items' => 'action_items',
            'documents' => 'documents',
            'deliberation-tools' => 'discussions',
            'report-generation' => 'reports',
            'research-support' => 'research',
            'tasks' => 'tasks',
            'inter-committee' => 'inter-committee',
        ];

        $data_key = $mapping[$module] ?? null;
        
        if (!$data_key || !isset($_SESSION['module_data'][$data_key])) {
            return false;
        }

        // Generate new ID
        $max_id = 0;
        foreach ($_SESSION['module_data'][$data_key] as $existing_item) {
            if ($existing_item['id'] > $max_id) {
                $max_id = $existing_item['id'];
            }
        }
        
        $item['id'] = $max_id + 1;
        $_SESSION['module_data'][$data_key][] = $item;

        return true;
    }

    /**
     * Update an item in module data
     * 
     * @param string $module Module name
     * @param int $id Item ID
     * @param array $updates Updates to apply
     * @return bool
     */
    public static function updateItem($module, $id, $updates) {
        self::initializeModuleData();
        
        $mapping = [
            'committee-structure' => 'committees',
            'committees' => 'committees',
            'member-assignment' => 'members',
            'meeting-scheduler' => 'meetings',
            'meetings' => 'meetings',
            'agenda-builder' => 'agendas',
            'referral-management' => 'referrals',
            'referrals' => 'referrals',
            'action-items' => 'action_items',
            'documents' => 'documents',
            'deliberation-tools' => 'discussions',
            'report-generation' => 'reports',
            'research-support' => 'research',
            'tasks' => 'tasks',
            'inter-committee' => 'inter-committee',
        ];

        $data_key = $mapping[$module] ?? null;
        
        if (!$data_key || !isset($_SESSION['module_data'][$data_key])) {
            return false;
        }

        foreach ($_SESSION['module_data'][$data_key] as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $updates);
                return true;
            }
        }

        return false;
    }

    /**
     * Delete an item from module data
     * 
     * @param string $module Module name
     * @param int $id Item ID
     * @return bool
     */
    public static function deleteItem($module, $id) {
        self::initializeModuleData();
        
        $mapping = [
            'committee-structure' => 'committees',
            'committees' => 'committees',
            'member-assignment' => 'members',
            'meeting-scheduler' => 'meetings',
            'meetings' => 'meetings',
            'agenda-builder' => 'agendas',
            'referral-management' => 'referrals',
            'referrals' => 'referrals',
            'action-items' => 'action_items',
            'documents' => 'documents',
            'deliberation-tools' => 'discussions',
            'report-generation' => 'reports',
            'research-support' => 'research',
            'tasks' => 'tasks',
            'inter-committee' => 'inter-committee',
        ];

        $data_key = $mapping[$module] ?? null;
        
        if (!$data_key || !isset($_SESSION['module_data'][$data_key])) {
            return false;
        }

        foreach ($_SESSION['module_data'][$data_key] as $key => $item) {
            if ($item['id'] === $id) {
                unset($_SESSION['module_data'][$data_key][$key]);
                $_SESSION['module_data'][$data_key] = array_values($_SESSION['module_data'][$data_key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Search items in module data
     * 
     * @param string $module Module name
     * @param string $field Field to search
     * @param string $value Value to search for
     * @return array
     */
    public static function searchItems($module, $field, $value) {
        self::initializeModuleData();
        $data = self::getModuleData($module);
        $results = [];

        foreach ($data as $item) {
            if (isset($item[$field]) && stripos($item[$field], $value) !== false) {
                $results[] = $item;
            }
        }

        return $results;
    }

    /**
     * Get count of items in module
     * 
     * @param string $module Module name
     * @return int
     */
    public static function getItemCount($module) {
        $data = self::getModuleData($module);
        return count($data);
    }

    /**
     * Get total statistics across all modules
     */
    public static function getOverallStats() {
        self::initializeModuleData();
        
        return [
            'total_committees' => count($_SESSION['module_data']['committees'] ?? []),
            'total_members' => count($_SESSION['module_data']['members'] ?? []),
            'total_meetings' => count($_SESSION['module_data']['meetings'] ?? []),
            'total_agendas' => count($_SESSION['module_data']['agendas'] ?? []),
            'total_referrals' => count($_SESSION['module_data']['referrals'] ?? []),
            'total_action_items' => count($_SESSION['module_data']['action_items'] ?? []),
            'total_documents' => count($_SESSION['module_data']['documents'] ?? []),
            'total_discussions' => count($_SESSION['module_data']['discussions'] ?? []),
            'total_reports' => count($_SESSION['module_data']['reports'] ?? []),
            'total_research' => count($_SESSION['module_data']['research'] ?? []),
        ];
    }

    // ============================================
    // NEW CORE MODULE DATA METHODS
    // ============================================

    /**
     * Get Committee Profiles & Membership data
     */
    public static function getCommitteeProfiles() {
        self::initializeModuleData();
        return array_map(function($committee) {
            $committee['members_count'] = rand(4, 12);
            $committee['description'] = 'Committee responsible for overseeing '.$committee['name'].' matters';
            return $committee;
        }, $_SESSION['module_data']['committees'] ?? []);
    }

    /**
     * Get Members data
     */
    public static function getMembers() {
        self::initializeModuleData();
        return array_map(function($member) {
            $member['position'] = ['Chairperson', 'Vice-Chair', 'Member', 'Secretary'][rand(0, 3)];
            return $member;
        }, $_SESSION['module_data']['members'] ?? []);
    }

    /**
     * Get Committee Meetings data
     */
    public static function getMeetings() {
        self::initializeModuleData();
        $meetings = $_SESSION['module_data']['meetings'] ?? [];
        return array_map(function($meeting) {
            $meeting['committee'] = ['Finance Committee', 'Public Safety', 'Parks & Recreation'][rand(0, 2)];
            return $meeting;
        }, $meetings);
    }

    /**
     * Get Agendas data
     */
    public static function getAgendas() {
        self::initializeModuleData();
        return array_map(function($agenda) {
            $agenda['committee'] = ['Finance Committee', 'Public Safety'][rand(0, 1)];
            $agenda['items_count'] = rand(3, 8);
            return $agenda;
        }, $_SESSION['module_data']['agendas'] ?? []);
    }

    /**
     * Get Referrals data for Referral Tracking module
     */
    public static function getReferrals() {
        self::initializeModuleData();
        return [
            [
                'id' => 1,
                'reference_number' => 'REF-2025-001',
                'subject' => 'Budget Allocation Request for Q1 2025',
                'from_department' => 'Finance Department',
                'assigned_to' => 'John Smith',
                'status' => 'In Review',
                'deadline' => '2025-12-20',
                'created' => '2025-12-01'
            ],
            [
                'id' => 2,
                'reference_number' => 'REF-2025-002',
                'subject' => 'Policy Amendment - Safety Protocols Update',
                'from_department' => 'Legal Affairs',
                'assigned_to' => 'Robert Brown',
                'status' => 'Pending',
                'deadline' => '2025-12-18',
                'created' => '2025-12-03'
            ],
            [
                'id' => 3,
                'reference_number' => 'REF-2025-003',
                'subject' => 'Infrastructure Development Proposal',
                'from_department' => 'Public Works',
                'assigned_to' => 'Mary Johnson',
                'status' => 'In Review',
                'deadline' => '2025-12-25',
                'created' => '2025-12-05'
            ],
        ];
    }

    /**
     * Get Action Items data
     */
    public static function getActionItems() {
        self::initializeModuleData();
        return [
            [
                'id' => 1,
                'title' => 'Review and Approve Budget Proposal',
                'description' => 'Complete financial review of the proposed 2025 Q1 budget',
                'assigned_to' => 'John Smith',
                'priority' => 'High',
                'due_date' => '2025-12-20',
                'progress' => 60,
                'status' => 'In Progress',
                'created' => '2025-12-01'
            ],
            [
                'id' => 2,
                'title' => 'Update Safety Compliance Documentation',
                'description' => 'Ensure all safety protocols are current and properly documented',
                'assigned_to' => 'Robert Brown',
                'priority' => 'High',
                'due_date' => '2025-12-18',
                'progress' => 30,
                'status' => 'In Progress',
                'created' => '2025-12-02'
            ],
            [
                'id' => 3,
                'title' => 'Prepare Monthly Committee Report',
                'description' => 'Compile and prepare December activity report for publication',
                'assigned_to' => 'Mary Johnson',
                'priority' => 'Medium',
                'due_date' => '2025-12-30',
                'progress' => 80,
                'status' => 'In Progress',
                'created' => '2025-12-05'
            ],
        ];
    }

    /**
     * Get Committee Reports data
     */
    public static function getReports() {
        self::initializeModuleData();
        return [
            [
                'id' => 1,
                'title' => 'Q4 2025 Financial Summary Report',
                'content' => 'Comprehensive overview of committee finances and budget utilization for Q4 2025, including detailed breakdown of expenses and recommendations.',
                'committee' => 'Finance Committee',
                'created_date' => '2025-12-10',
                'status' => 'Draft',
                'author' => 'John Smith'
            ],
            [
                'id' => 2,
                'title' => 'Safety and Compliance Review Report',
                'content' => 'Annual review of all safety protocols, compliance measures, and recommendations for improvements in the coming year.',
                'committee' => 'Public Safety',
                'created_date' => '2025-12-08',
                'status' => 'Draft',
                'author' => 'Robert Brown'
            ],
            [
                'id' => 3,
                'title' => 'Committee Performance and Achievements Report',
                'content' => 'Detailed report on committee activities, achievements, and impact during the reporting period with future recommendations.',
                'committee' => 'Finance Committee',
                'created_date' => '2025-12-05',
                'status' => 'Draft',
                'author' => 'Mary Johnson'
            ],
        ];
    }
}

// Initialize module data on every page load
ModuleDataHelper::initializeModuleData();
?>
