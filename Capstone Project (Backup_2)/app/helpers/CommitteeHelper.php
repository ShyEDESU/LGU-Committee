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
        // Finance Committee members
        ['committee_id' => 1, 'member_id' => 1, 'name' => 'Hon. Maria Santos', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 1'],
        ['committee_id' => 1, 'member_id' => 2, 'name' => 'Hon. Roberto Cruz', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 1, 'member_id' => 3, 'name' => 'Hon. Lisa Tan', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 1, 'member_id' => 4, 'name' => 'Hon. Mark Bautista', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 4'],
        ['committee_id' => 1, 'member_id' => 5, 'name' => 'Hon. Sarah Lim', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 5'],

        // Health Committee members
        ['committee_id' => 2, 'member_id' => 6, 'name' => 'Hon. Juan Dela Cruz', 'role' => 'Chairperson', 'position' => 'Councilor', 'district' => 'District 2'],
        ['committee_id' => 2, 'member_id' => 7, 'name' => 'Hon. Linda Reyes', 'role' => 'Vice-Chairperson', 'position' => 'Councilor', 'district' => 'District 3'],
        ['committee_id' => 2, 'member_id' => 8, 'name' => 'Hon. David Wong', 'role' => 'Member', 'position' => 'Councilor', 'district' => 'District 1'],

        // Add more members for other committees as needed
    ];
}

// Initialize committee documents if not exists
if (!isset($_SESSION['committee_documents'])) {
    $_SESSION['committee_documents'] = [
        ['id' => 1, 'committee_id' => 1, 'title' => '2025 Budget Proposal', 'type' => 'Proposal', 'uploaded_date' => '2025-11-15', 'uploaded_by' => 'Admin User'],
        ['id' => 2, 'committee_id' => 1, 'title' => 'Financial Report Q3 2025', 'type' => 'Report', 'uploaded_date' => '2025-10-01', 'uploaded_by' => 'Admin User'],
        ['id' => 3, 'committee_id' => 2, 'title' => 'Health Program Guidelines', 'type' => 'Guidelines', 'uploaded_date' => '2025-09-20', 'uploaded_by' => 'Admin User'],
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
?>