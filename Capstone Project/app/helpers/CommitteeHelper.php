<?php
/**
 * Committee Data Handler
 * Manages committee data in session storage
 */

// Initialize committees in session if not exists
if (!isset($_SESSION['committees'])) {
    $_SESSION['committees'] = [
        [
            'id' => 1,
            'name' => 'Committee on Finance',
            'type' => 'Standing',
            'chair' => 'Hon. Maria Santos',
            'vice_chair' => 'Hon. Roberto Cruz',
            'members_count' => 7,
            'jurisdiction' => 'Budget, appropriations, revenue measures, and financial matters',
            'description' => 'The Finance Committee oversees all financial matters of the city government including budget preparation, revenue generation, and fiscal policies.',
            'status' => 'Active',
            'meetings_held' => 12,
            'pending_referrals' => 3,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 2,
            'name' => 'Committee on Health',
            'type' => 'Standing',
            'chair' => 'Hon. Juan Dela Cruz',
            'vice_chair' => 'Hon. Linda Reyes',
            'members_count' => 5,
            'jurisdiction' => 'Public health, sanitation, and medical services',
            'description' => 'Responsible for health programs, hospital management, and public health initiatives.',
            'status' => 'Active',
            'meetings_held' => 8,
            'pending_referrals' => 2,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 3,
            'name' => 'Committee on Education',
            'type' => 'Standing',
            'chair' => 'Hon. Ana Reyes',
            'vice_chair' => 'Hon. Miguel Santos',
            'members_count' => 6,
            'jurisdiction' => 'Education, schools, and learning institutions',
            'description' => 'Oversees educational programs, school facilities, and educational policies.',
            'status' => 'Active',
            'meetings_held' => 10,
            'pending_referrals' => 5,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 4,
            'name' => 'Committee on Infrastructure',
            'type' => 'Standing',
            'chair' => 'Hon. Pedro Garcia',
            'vice_chair' => 'Hon. Carmen Lopez',
            'members_count' => 8,
            'jurisdiction' => 'Public works, roads, bridges, and infrastructure development',
            'description' => 'Manages infrastructure projects, public works, and urban development.',
            'status' => 'Active',
            'meetings_held' => 15,
            'pending_referrals' => 4,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 5,
            'name' => 'Committee on Public Safety',
            'type' => 'Standing',
            'chair' => 'Hon. Rosa Martinez',
            'vice_chair' => 'Hon. Antonio Diaz',
            'members_count' => 6,
            'jurisdiction' => 'Police, fire protection, and disaster preparedness',
            'description' => 'Handles public safety, law enforcement, and emergency response.',
            'status' => 'Active',
            'meetings_held' => 9,
            'pending_referrals' => 1,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 6,
            'name' => 'Special Committee on COVID-19 Response',
            'type' => 'Special',
            'chair' => 'Hon. Carlos Ramos',
            'vice_chair' => 'Hon. Elena Fernandez',
            'members_count' => 5,
            'jurisdiction' => 'Pandemic response and recovery measures',
            'description' => 'Temporary committee focused on COVID-19 response and recovery efforts.',
            'status' => 'Active',
            'meetings_held' => 6,
            'pending_referrals' => 2,
            'created_date' => '2024-03-01',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 7,
            'name' => 'Committee on Environment',
            'type' => 'Standing',
            'chair' => 'Hon. Teresa Aquino',
            'vice_chair' => 'Hon. Rafael Gomez',
            'members_count' => 6,
            'jurisdiction' => 'Environmental protection, waste management, and sustainability',
            'description' => 'Manages environmental programs, waste management, and green initiatives.',
            'status' => 'Active',
            'meetings_held' => 7,
            'pending_referrals' => 3,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 8,
            'name' => 'Committee on Social Services',
            'type' => 'Standing',
            'chair' => 'Hon. Gloria Mendoza',
            'vice_chair' => 'Hon. Francisco Torres',
            'members_count' => 5,
            'jurisdiction' => 'Social welfare, poverty alleviation, and community development',
            'description' => 'Oversees social welfare programs and community development initiatives.',
            'status' => 'Active',
            'meetings_held' => 11,
            'pending_referrals' => 4,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 9,
            'name' => 'Ad Hoc Committee on City Charter Review',
            'type' => 'Ad Hoc',
            'chair' => 'Hon. Benjamin Castillo',
            'vice_chair' => 'Hon. Patricia Navarro',
            'members_count' => 7,
            'jurisdiction' => 'Review and recommend amendments to city charter',
            'description' => 'Temporary committee for reviewing and proposing city charter amendments.',
            'status' => 'Active',
            'meetings_held' => 4,
            'pending_referrals' => 1,
            'created_date' => '2024-06-01',
            'created_by' => 'Admin User'
        ],
        [
            'id' => 10,
            'name' => 'Committee on Transportation',
            'type' => 'Standing',
            'chair' => 'Hon. Ricardo Villanueva',
            'vice_chair' => 'Hon. Angelica Morales',
            'members_count' => 6,
            'jurisdiction' => 'Public transportation, traffic management, and mobility',
            'description' => 'Manages public transportation systems and traffic policies.',
            'status' => 'Active',
            'meetings_held' => 8,
            'pending_referrals' => 2,
            'created_date' => '2024-01-15',
            'created_by' => 'Admin User'
        ]
    ];
}

// Initialize committee members if not exists
if (!isset($_SESSION['committee_members'])) {
    $_SESSION['committee_members'] = [
        // Finance Committee members (7 members)
        ['committee_id' => 1, 'member_id' => 1, 'name' => 'Hon. Maria Santos', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 1, 'member_id' => 2, 'name' => 'Hon. Roberto Cruz', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 1, 'member_id' => 3, 'name' => 'Hon. Lisa Tan', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 1, 'member_id' => 4, 'name' => 'Hon. Mark Bautista', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 4'],
        ['committee_id' => 1, 'member_id' => 5, 'name' => 'Hon. Sarah Lim', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 5'],
        ['committee_id' => 1, 'member_id' => 6, 'name' => 'Hon. James Garcia', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 6'],
        ['committee_id' => 1, 'member_id' => 7, 'name' => 'Hon. Patricia Reyes', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 7'],

        // Health Committee members (5 members)
        ['committee_id' => 2, 'member_id' => 8, 'name' => 'Hon. Juan Dela Cruz', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 2, 'member_id' => 9, 'name' => 'Hon. Linda Reyes', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 2, 'member_id' => 10, 'name' => 'Hon. David Wong', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 2, 'member_id' => 11, 'name' => 'Hon. Angela Martinez', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 4'],
        ['committee_id' => 2, 'member_id' => 12, 'name' => 'Hon. Carlos Fernandez', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 5'],

        // Education Committee members (6 members)
        ['committee_id' => 3, 'member_id' => 13, 'name' => 'Hon. Ana Reyes', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 3, 'member_id' => 14, 'name' => 'Hon. Miguel Santos', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 4'],
        ['committee_id' => 3, 'member_id' => 15, 'name' => 'Hon. Sofia Lopez', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 3, 'member_id' => 16, 'name' => 'Hon. Daniel Torres', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 3, 'member_id' => 17, 'name' => 'Hon. Emma Gonzales', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 5'],
        ['committee_id' => 3, 'member_id' => 18, 'name' => 'Hon. Victor Ramos', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 6'],

        // Infrastructure Committee members (8 members)
        ['committee_id' => 4, 'member_id' => 19, 'name' => 'Hon. Pedro Garcia', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 4'],
        ['committee_id' => 4, 'member_id' => 20, 'name' => 'Hon. Carmen Lopez', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 5'],
        ['committee_id' => 4, 'member_id' => 21, 'name' => 'Hon. Ricardo Mendoza', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 4, 'member_id' => 22, 'name' => 'Hon. Isabel Cruz', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 4, 'member_id' => 23, 'name' => 'Hon. Antonio Diaz', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 4, 'member_id' => 24, 'name' => 'Hon. Beatriz Santos', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 6'],
        ['committee_id' => 4, 'member_id' => 25, 'name' => 'Hon. Fernando Reyes', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 7'],
        ['committee_id' => 4, 'member_id' => 26, 'name' => 'Hon. Gabriela Morales', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 8'],

        // Public Safety Committee members (6 members)
        ['committee_id' => 5, 'member_id' => 27, 'name' => 'Hon. Rosa Martinez', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 5'],
        ['committee_id' => 5, 'member_id' => 28, 'name' => 'Hon. Antonio Diaz', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 6'],
        ['committee_id' => 5, 'member_id' => 29, 'name' => 'Hon. Luis Navarro', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 5, 'member_id' => 30, 'name' => 'Hon. Maria Castillo', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 5, 'member_id' => 31, 'name' => 'Hon. Jorge Ramirez', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 5, 'member_id' => 32, 'name' => 'Hon. Diana Flores', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 4'],
    ];
}

// Initialize committee documents if not exists
if (!isset($_SESSION['committee_documents'])) {
    $_SESSION['committee_documents'] = [
        // Finance Committee documents
        [
            'id' => 1,
            'committee_id' => 1,
            'title' => '2025 Budget Proposal',
            'type' => 'Proposal',
            'file_name' => '2025_Budget_Proposal.pdf',
            'file_size' => '2.4 MB',
            'description' => 'Comprehensive budget proposal for fiscal year 2025 including all departmental allocations',
            'uploaded_date' => '2025-11-15',
            'uploaded_by' => 'Admin User'
        ],
        [
            'id' => 2,
            'committee_id' => 1,
            'title' => 'Financial Report Q3 2025',
            'type' => 'Report',
            'file_name' => 'Financial_Report_Q3_2025.pdf',
            'file_size' => '1.8 MB',
            'description' => 'Quarterly financial report covering July-September 2025',
            'uploaded_date' => '2025-10-01',
            'uploaded_by' => 'Admin User'
        ],
        [
            'id' => 3,
            'committee_id' => 1,
            'title' => 'Revenue Collection Analysis',
            'type' => 'Analysis',
            'file_name' => 'Revenue_Analysis_2025.xlsx',
            'file_size' => '856 KB',
            'description' => 'Detailed analysis of revenue collection trends and projections',
            'uploaded_date' => '2025-09-15',
            'uploaded_by' => 'Finance Staff'
        ],

        // Health Committee documents
        [
            'id' => 4,
            'committee_id' => 2,
            'title' => 'Health Program Guidelines',
            'type' => 'Guidelines',
            'file_name' => 'Health_Program_Guidelines_2025.pdf',
            'file_size' => '3.2 MB',
            'description' => 'Comprehensive guidelines for implementing health programs in the city',
            'uploaded_date' => '2025-09-20',
            'uploaded_by' => 'Admin User'
        ],
        [
            'id' => 5,
            'committee_id' => 2,
            'title' => 'Hospital Inspection Report',
            'type' => 'Report',
            'file_name' => 'Hospital_Inspection_Report_Sept2025.pdf',
            'file_size' => '1.5 MB',
            'description' => 'Findings from the September 2025 hospital facility inspection',
            'uploaded_date' => '2025-09-28',
            'uploaded_by' => 'Health Inspector'
        ],
        [
            'id' => 6,
            'committee_id' => 2,
            'title' => 'Vaccination Program Data',
            'type' => 'Data',
            'file_name' => 'Vaccination_Data_2025.xlsx',
            'file_size' => '645 KB',
            'description' => 'Statistical data on vaccination coverage across all barangays',
            'uploaded_date' => '2025-10-05',
            'uploaded_by' => 'Health Department'
        ],

        // Education Committee documents
        [
            'id' => 7,
            'committee_id' => 3,
            'title' => 'School Infrastructure Assessment',
            'type' => 'Assessment',
            'file_name' => 'School_Infrastructure_Assessment.pdf',
            'file_size' => '4.1 MB',
            'description' => 'Comprehensive assessment of all public school facilities and infrastructure needs',
            'uploaded_date' => '2025-08-10',
            'uploaded_by' => 'Education Committee'
        ],
        [
            'id' => 8,
            'committee_id' => 3,
            'title' => 'Student Performance Report 2024-2025',
            'type' => 'Report',
            'file_name' => 'Student_Performance_2024-2025.pdf',
            'file_size' => '2.7 MB',
            'description' => 'Annual report on student performance metrics and achievement data',
            'uploaded_date' => '2025-07-20',
            'uploaded_by' => 'DepEd Liaison'
        ],
    ];
}

// Initialize committee history if not exists
if (!isset($_SESSION['committee_history'])) {
    $_SESSION['committee_history'] = [
        ['id' => 1, 'committee_id' => 1, 'action' => 'Created', 'description' => 'Committee created', 'user' => 'Admin User', 'date' => '2024-01-15 10:00:00'],
        ['id' => 2, 'committee_id' => 1, 'action' => 'Updated', 'description' => 'Updated jurisdiction description', 'user' => 'Admin User', 'date' => '2024-02-10 14:30:00'],
        ['id' => 3, 'committee_id' => 1, 'action' => 'Member Added', 'description' => 'Added Hon. Sarah Lim as member', 'user' => 'Admin User', 'date' => '2024-03-05 09:15:00'],
    ];
}

/**
 * Get all committees
 */
function getAllCommittees()
{
    return $_SESSION['committees'] ?? [];
}

/**
 * Get committee by ID
 */
function getCommitteeById($id)
{
    $committees = getAllCommittees();
    foreach ($committees as $committee) {
        if ($committee['id'] == $id) {
            return $committee;
        }
    }
    return null;
}

/**
 * Create new committee
 */
function createCommittee($data)
{
    $committees = getAllCommittees();
    $newId = empty($committees) ? 1 : max(array_column($committees, 'id')) + 1;

    $newCommittee = [
        'id' => $newId,
        'name' => $data['name'],
        'type' => $data['type'],
        'chair' => $data['chair'],
        'vice_chair' => $data['vice_chair'] ?? '',
        'members_count' => 0,
        'jurisdiction' => $data['jurisdiction'],
        'description' => $data['description'] ?? '',
        'status' => $data['status'] ?? 'Active',
        'meetings_held' => 0,
        'pending_referrals' => 0,
        'created_date' => date('Y-m-d'),
        'created_by' => $_SESSION['user_name'] ?? 'User'
    ];

    $_SESSION['committees'][] = $newCommittee;

    // Add to history
    addCommitteeHistory($newId, 'Created', 'Committee created', $_SESSION['user_name'] ?? 'User');

    return $newId;
}

/**
 * Update committee
 */
function updateCommittee($id, $data)
{
    $committees = &$_SESSION['committees'];
    foreach ($committees as &$committee) {
        if ($committee['id'] == $id) {
            $committee['name'] = $data['name'];
            $committee['type'] = $data['type'];
            $committee['chair'] = $data['chair'];
            $committee['vice_chair'] = $data['vice_chair'] ?? '';
            $committee['jurisdiction'] = $data['jurisdiction'];
            $committee['description'] = $data['description'] ?? '';
            $committee['status'] = $data['status'] ?? 'Active';

            // Add to history
            addCommitteeHistory($id, 'Updated', 'Committee information updated', $_SESSION['user_name'] ?? 'User');

            return true;
        }
    }
    return false;
}

/**
 * Delete committee
 */
function deleteCommittee($id)
{
    $committees = &$_SESSION['committees'];
    foreach ($committees as $key => $committee) {
        if ($committee['id'] == $id) {
            unset($committees[$key]);
            $_SESSION['committees'] = array_values($committees);
            return true;
        }
    }
    return false;
}

/**
 * Get committee members
 */
function getCommitteeMembers($committeeId)
{
    $members = $_SESSION['committee_members'] ?? [];
    return array_filter($members, function ($member) use ($committeeId) {
        return $member['committee_id'] == $committeeId;
    });
}

/**
 * Get committee documents
 */
function getCommitteeDocuments($committeeId)
{
    $documents = $_SESSION['committee_documents'] ?? [];
    return array_filter($documents, function ($doc) use ($committeeId) {
        return $doc['committee_id'] == $committeeId;
    });
}

/**
 * Get committee history
 */
function getCommitteeHistory($committeeId)
{
    $history = $_SESSION['committee_history'] ?? [];
    return array_filter($history, function ($item) use ($committeeId) {
        return $item['committee_id'] == $committeeId;
    });
}

/**
 * Add committee history entry
 */
function addCommitteeHistory($committeeId, $action, $description, $user)
{
    if (!isset($_SESSION['committee_history'])) {
        $_SESSION['committee_history'] = [];
    }

    $newId = empty($_SESSION['committee_history']) ? 1 : max(array_column($_SESSION['committee_history'], 'id')) + 1;

    $_SESSION['committee_history'][] = [
        'id' => $newId,
        'committee_id' => $committeeId,
        'action' => $action,
        'description' => $description,
        'user' => $user,
        'date' => date('Y-m-d H:i:s')
    ];
}

/**
 * Get committee statistics dynamically
 * Calculates real-time statistics from actual data
 */
function getCommitteeStatistics($committeeId)
{
    // Count actual members
    $members = getCommitteeMembers($committeeId);
    $memberCount = count($members);

    // Count actual documents
    $documents = getCommitteeDocuments($committeeId);
    $documentCount = count($documents);

    // Load DataHelper for other statistics
    require_once __DIR__ . '/DataHelper.php';

    // Count actual meetings
    $meetings = getMeetingsByCommittee($committeeId);
    $meetingsHeld = count($meetings);

    // Count actual referrals
    $allReferrals = getReferralsByCommittee($committeeId);
    $pendingReferrals = count(array_filter($allReferrals, function ($r) {
        return $r['status'] === 'Pending';
    }));

    // Count action items
    $actionItems = getActionItemsByCommittee($committeeId);
    $actionItemCount = count($actionItems);

    // Count agendas
    $agendas = getAgendasByCommittee($committeeId);
    $agendaCount = count($agendas);

    return [
        'members_count' => $memberCount,
        'pending_referrals' => $pendingReferrals,
        'meetings_held' => $meetingsHeld,
        'action_items_count' => $actionItemCount,
        'documents_count' => $documentCount,
        'agendas_count' => $agendaCount,
        'total_referrals' => count($allReferrals)
    ];
}

/**
 * Get committee with dynamic statistics
 * Returns committee data with real-time calculated statistics
 */
function getCommitteeWithStats($committeeId)
{
    $committee = getCommitteeById($committeeId);
    if (!$committee) {
        return null;
    }

    // Get dynamic statistics
    $stats = getCommitteeStatistics($committeeId);

    // Merge statistics into committee data
    return array_merge($committee, $stats);
}
?>