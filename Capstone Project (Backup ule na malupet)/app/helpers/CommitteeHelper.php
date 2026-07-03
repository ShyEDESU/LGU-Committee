<?php
/**
 * Committee Data Handler - Database Version
 * Manages committee data using MySQL database
 */

// Require database connection
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AuditHelper.php';
require_once __DIR__ . '/UserHelper.php';
require_once __DIR__ . '/NotificationHelper.php';

/**
 * Synchronize committee lead roles (Chairperson, Vice-Chairperson, Secretary) with the member list
 * Internal helper function to ensure data consistency
 */
function syncCommitteeLeadRoles($committeeId, $data)
{
    global $conn;

    $leads = [
        'chairperson_id' => 'Chairperson',
        'vice_chair_id' => 'Vice-Chairperson',
        'secretary_id' => 'Secretary'
    ];

    foreach ($leads as $key => $position) {
        $userId = !empty($data[$key]) ? intval($data[$key]) : null;

        if ($userId) {
            // Demote anyone else currently holding this position to 'Member'
            $demoteStmt = $conn->prepare("UPDATE committee_members 
                SET position = 'Member' 
                WHERE committee_id = ? AND position = ? AND user_id != ? AND is_active = 1");
            $demoteStmt->bind_param("isi", $committeeId, $position, $userId);
            $demoteStmt->execute();

            // Add or update the new role holder
            addCommitteeMember($committeeId, $userId, $position);
        } else {
            // If the role is explicitly removed (set to null), 
            // find anyone with this position and demote them to 'Member'
            $demoteAllStmt = $conn->prepare("UPDATE committee_members 
                SET position = 'Member' 
                WHERE committee_id = ? AND position = ? AND is_active = 1");
            $demoteAllStmt->bind_param("is", $committeeId, $position);
            $demoteAllStmt->execute();
        }
    }

    return true;
}

/**
 * Get all committees from database
 */
function getAllCommittees($includeArchived = false)
{
    global $conn;

    $whereClause = $includeArchived ? "" : "WHERE c.is_active = 1";

    $sql = "SELECT 
                c.*,
                CONCAT(u1.first_name, ' ', u1.last_name) as chair_name,
                CONCAT(u2.first_name, ' ', u2.last_name) as vice_chair_name,
                CONCAT(u3.first_name, ' ', u3.last_name) as secretary_name
            FROM committees c
            LEFT JOIN users u1 ON c.chairperson_id = u1.user_id
            LEFT JOIN users u2 ON c.vice_chair_id = u2.user_id
            LEFT JOIN users u3 ON c.secretary_id = u3.user_id
            $whereClause
            ORDER BY c.is_active DESC, c.committee_name ASC";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("Error fetching committees: " . $conn->error);
        return [];
    }

    $committees = [];
    while ($row = $result->fetch_assoc()) {
        $committees[] = [
            'id' => $row['committee_id'],
            'name' => $row['committee_name'],
            'type' => $row['committee_type'],
            'description' => $row['description'],
            'jurisdiction' => $row['jurisdiction'],
            'chair' => $row['chair_name'] ?? 'Not Assigned',
            'chairperson_id' => $row['chairperson_id'],
            'vice_chair' => $row['vice_chair_name'] ?? 'Not Assigned',
            'vice_chair_id' => $row['vice_chair_id'],
            'secretary' => $row['secretary_name'] ?? 'Not Assigned',
            'secretary_id' => $row['secretary_id'],
            'status' => $row['is_active'] ? 'Active' : 'Archived',
            'is_active' => $row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    return $committees;
}

/**
 * Get committees that a specific user is allowed to see
 */
function getUserCommittees($userId, $includeArchived = false)
{
    global $conn;

    // Get user role
    $user = UserHelper_getUserById($userId);
    $roleName = $user['role_name'] ?? 'User';

    // Admins see everything
    if ($roleName === 'Super Admin' || $roleName === 'Admin') {
        return getAllCommittees($includeArchived);
    }

    // For others, return all committees so they can "view" them (transparency requirement)
    // Business rules will restrict Edit/Delete buttons in the UI based on ownership
    return getAllCommittees($includeArchived);

    if (!$result) {
        return [];
    }

    $committees = [];
    while ($row = $result->fetch_assoc()) {
        $committees[] = [
            'id' => $row['committee_id'],
            'name' => $row['committee_name'],
            'type' => $row['committee_type'],
            'description' => $row['description'],
            'jurisdiction' => $row['jurisdiction'],
            'chair' => $row['chair_name'] ?? 'Not Assigned',
            'chairperson_id' => $row['chairperson_id'],
            'vice_chair' => $row['vice_chair_name'] ?? 'Not Assigned',
            'vice_chair_id' => $row['vice_chair_id'],
            'secretary' => $row['secretary_name'] ?? 'Not Assigned',
            'secretary_id' => $row['secretary_id'],
            'status' => $row['is_active'] ? 'Active' : 'Inactive',
            'is_active' => $row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    return $committees;
}

/**
 * Get committee by ID
 */
function getCommitteeById($id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT 
                c.*,
                CONCAT(u1.first_name, ' ', u1.last_name) as chair_name,
                CONCAT(u2.first_name, ' ', u2.last_name) as vice_chair_name,
                CONCAT(u3.first_name, ' ', u3.last_name) as secretary_name
            FROM committees c
            LEFT JOIN users u1 ON c.chairperson_id = u1.user_id
            LEFT JOIN users u2 ON c.vice_chair_id = u2.user_id
            LEFT JOIN users u3 ON c.secretary_id = u3.user_id
            WHERE c.committee_id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return [
            'id' => $row['committee_id'],
            'name' => $row['committee_name'],
            'type' => $row['committee_type'],
            'description' => $row['description'],
            'jurisdiction' => $row['jurisdiction'],
            'chair' => $row['chair_name'] ?? 'Not Assigned',
            'chairperson_id' => $row['chairperson_id'],
            'vice_chair' => $row['vice_chair_name'] ?? 'Not Assigned',
            'vice_chair_id' => $row['vice_chair_id'],
            'secretary' => $row['secretary_name'] ?? 'Not Assigned',
            'secretary_id' => $row['secretary_id'],
            'status' => $row['is_active'] ? 'Active' : 'Inactive',
            'is_active' => $row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    return null;
}

/**
 * Create new committee
 */
function createCommittee($data)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO committees 
        (committee_name, committee_type, description, jurisdiction, chairperson_id, vice_chair_id, secretary_id, creation_authority, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $isActive = isset($data['is_active']) ? $data['is_active'] : true;
    $authority = $data['creation_authority'] ?? 'N/A';

    $stmt->bind_param(
        "ssssiiiis",
        $data['name'],
        $data['type'],
        $data['description'],
        $data['jurisdiction'],
        $data['chairperson_id'],
        $data['vice_chair_id'],
        $data['secretary_id'],
        $authority,
        $isActive
    );

    if ($stmt->execute()) {
        $committeeId = $conn->insert_id;

        // Automatically sync lead roles to member list
        syncCommitteeLeadRoles($committeeId, $data);

        // Automatic Notification for Admins
        // Find Admins (Role ID 1 is Super Admin, Role ID 2 is Admin in most standard schemas)
        // We'll notify all users who have 'Admin' in their role name
        $allUsers = UserHelper_getAllUsers();
        foreach ($allUsers as $user) {
            if (isset($user['role_name']) && (stripos($user['role_name'], 'Admin') !== false)) {
                $title = "New Committee Created";
                $message = "A new committee has been created: '{$data['name']}'";
                $type = 'committee_created';
                $priority = 'medium';
                $link = "pages/committee-profiles/view.php?id=" . $committeeId;
                createNotification($user['user_id'], $title, $message, $type, $priority, $link);
            }
        }

        // Log the action with details
        if (function_exists('logAuditAction')) {
            $details = "Created committee '{$data['name']}' (Type: {$data['type']})";
            if (!empty($data['chairperson_id'])) {
                $details .= " with chairperson ID: {$data['chairperson_id']}";
            }

            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'CREATE',
                'committees',
                $details
            );
        }

        return $committeeId;
    }

    error_log("Error creating committee: " . $conn->error);
    return false;
}

/**
 * Update committee
 */
function updateCommittee($id, $data)
{
    global $conn;

    // Get old values BEFORE updating to show what changed
    $oldCommittee = getCommitteeById($id);

    $stmt = $conn->prepare("UPDATE committees 
        SET committee_name = ?, 
            committee_type = ?, 
            description = ?, 
            jurisdiction = ?, 
            chairperson_id = ?, 
            vice_chair_id = ?, 
            secretary_id = ?,
            creation_authority = ?,
            is_active = ?
        WHERE committee_id = ?");

    $isActive = isset($data['is_active']) ? $data['is_active'] : true;
    $authority = $data['creation_authority'] ?? 'N/A';

    $stmt->bind_param(
        "ssssiiiisi",
        $data['name'],
        $data['type'],
        $data['description'],
        $data['jurisdiction'],
        $data['chairperson_id'],
        $data['vice_chair_id'],
        $data['secretary_id'],
        $authority,
        $isActive,
        $id
    );

    if ($stmt->execute()) {
        // Automatically sync lead roles to member list
        syncCommitteeLeadRoles($id, $data);

        // Build detailed change log
        $changes = [];

        if ($oldCommittee['name'] !== $data['name']) {
            $changes[] = "name from '{$oldCommittee['name']}' to '{$data['name']}'";
        }
        if ($oldCommittee['type'] !== $data['type']) {
            $changes[] = "type from '{$oldCommittee['type']}' to '{$data['type']}'";
        }
        if ($oldCommittee['description'] !== $data['description']) {
            $changes[] = "description";
        }
        if ($oldCommittee['jurisdiction'] !== $data['jurisdiction']) {
            $changes[] = "jurisdiction";
        }
        if ($oldCommittee['chairperson_id'] != $data['chairperson_id']) {
            $oldChair = $oldCommittee['chair'] ?? 'None';
            $changes[] = "chairperson from '{$oldChair}' to new chairperson";
        }
        if ($oldCommittee['vice_chair_id'] != $data['vice_chair_id']) {
            $oldVice = $oldCommittee['vice_chair'] ?? 'None';
            $changes[] = "vice chair from '{$oldVice}' to new vice chair";
        }
        if ($oldCommittee['secretary_id'] != $data['secretary_id']) {
            $oldSecretary = $oldCommittee['secretary'] ?? 'None';
            $changes[] = "secretary from '{$oldSecretary}' to new secretary";
        }

        // Log the action with details
        if (function_exists('logAuditAction') && !empty($changes)) {
            $changeDetails = "Updated " . implode(", ", $changes) . " for committee '{$data['name']}'";
            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'UPDATE',
                'committees',
                $changeDetails
            );
        }

        return true;
    }

    error_log("Error updating committee: " . $conn->error);
    return false;
}

/**
 * Archive committee (Professional Soft Delete)
 */
function deleteCommittee($id)
{
    global $conn;

    // Get committee name for logging
    $committee = getCommitteeById($id);
    if (!$committee)
        return false;

    $committeeName = $committee['name'] ?? "ID: $id";

    // Set is_active to 0 instead of deleting from database
    $stmt = $conn->prepare("UPDATE committees SET is_active = 0 WHERE committee_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Log the archiving action
        if (function_exists('logAuditAction')) {
            $details = "Archived committee '{$committeeName}'";
            if (isset($committee['type'])) {
                $details .= " (Type: {$committee['type']})";
            }

            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'ARCHIVE',
                'committees',
                $details
            );
        }

        return true;
    }

    error_log("Error archiving committee: " . $conn->error);
    return false;
}

/**
 * Restore an archived committee
 */
function restoreCommittee($id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE committees SET is_active = 1 WHERE committee_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/**
 * Get committee members with user details
 */
function getCommitteeMembers($committeeId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT 
                cm.*,
                u.first_name,
                u.last_name,
                u.email,
                u.position as user_position,
                u.department,
                CONCAT(u.first_name, ' ', u.last_name) as full_name
            FROM committee_members cm
            INNER JOIN users u ON cm.user_id = u.user_id
            WHERE cm.committee_id = ? AND cm.is_active = 1
            ORDER BY 
                CASE cm.position
                    WHEN 'Chairperson' THEN 1
                    WHEN 'Vice-Chairperson' THEN 2
                    WHEN 'Secretary' THEN 3
                    ELSE 4
                END,
                cm.membership_status DESC,
                u.last_name ASC");

    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'member_id' => $row['member_id'],
            'user_id' => $row['user_id'],
            'name' => $row['full_name'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'role' => $row['position'] ?? 'Member',
            'position' => $row['user_position'],
            'department' => $row['department'],
            'join_date' => $row['join_date'],
            'is_active' => $row['is_active'],
            'membership_status' => $row['membership_status'] ?? 'Active'
        ];
    }

    return $members;
}

/**
 * Add member to committee
 */
function addCommitteeMember($committeeId, $userId, $position = 'Member', $joinDate = null)
{
    global $conn;

    if ($joinDate === null) {
        $joinDate = date('Y-m-d');
    }

    // Check if member already exists
    $checkStmt = $conn->prepare("SELECT member_id FROM committee_members 
        WHERE committee_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $committeeId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    // Logic: If added by Secretary, status is Pending. If by Chair/Admin, status is Active.
    $currentUserRole = $_SESSION['user_role'] ?? 'User';
    $currentUserId = $_SESSION['user_id'] ?? 0;

    $committee = getCommitteeById($committeeId);
    $isChair = ($currentUserId == ($committee['chairperson_id'] ?? 0) || $currentUserId == ($committee['vice_chair_id'] ?? 0));
    $isAdmin = ($currentUserRole === 'Admin' || $currentUserRole === 'Super Admin');

    $status = ($isChair || $isAdmin) ? 'Active' : 'Pending';

    if ($checkResult->num_rows > 0) {
        // Update existing member
        $stmt = $conn->prepare("UPDATE committee_members 
            SET position = ?, join_date = ?, is_active = 1, membership_status = ?
            WHERE committee_id = ? AND user_id = ?");
        $stmt->bind_param("ssisi", $position, $joinDate, $status, $committeeId, $userId);
    } else {
        // Insert new member
        $stmt = $conn->prepare("INSERT INTO committee_members 
            (committee_id, user_id, position, join_date, is_active, membership_status) 
            VALUES (?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("iisss", $committeeId, $userId, $position, $joinDate, $status);
    }

    if ($stmt->execute()) {
        // Get user and committee names for detailed logging
        $userStmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userName = $userResult->fetch_assoc()['name'] ?? "User ID: $userId";

        $committee = getCommitteeById($committeeId);
        $committeeName = $committee['name'] ?? "Committee ID: $committeeId";

        // Log the action with details
        if (function_exists('logAuditAction')) {
            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'ADD_MEMBER',
                'committees',
                "Added '{$userName}' as {$position} to committee '{$committeeName}'"
            );
        }

        return true;
    }

    error_log("Error adding committee member: " . $conn->error);
    return false;
}

/**
 * Approve a pending committee member
 */
function approveCommitteeMember($committeeId, $memberId)
{
    global $conn;

    // Finalize appointment
    $stmt = $conn->prepare("UPDATE committee_members 
        SET membership_status = 'Active' 
        WHERE committee_id = ? AND member_id = ?");
    $stmt->bind_param("ii", $committeeId, $memberId);

    if ($stmt->execute()) {
        if (function_exists('logAuditAction')) {
            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'APPROVE_MEMBER',
                'committees',
                "Approved member appointment ID: {$memberId} for committee ID: {$committeeId}"
            );
        }
        return true;
    }
    return false;
}

/**
 * Remove member from committee
 */
function removeCommitteeMember($committeeId, $userId)
{
    global $conn;

    // Soft delete - set is_active to false
    $stmt = $conn->prepare("UPDATE committee_members 
        SET is_active = 0 
        WHERE committee_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $committeeId, $userId);

    if ($stmt->execute()) {
        // Get user and committee names for detailed logging
        $userStmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userName = $userResult->fetch_assoc()['name'] ?? "User ID: $userId";

        $committee = getCommitteeById($committeeId);
        $committeeName = $committee['name'] ?? "Committee ID: $committeeId";

        // NEW: Check if this user holds any leadership role in the committee
        $roleCleanup = [];
        if (($committee['chairperson_id'] ?? 0) == $userId) {
            $updateChair = $conn->prepare("UPDATE committees SET chairperson_id = NULL WHERE committee_id = ?");
            $updateChair->bind_param("i", $committeeId);
            $updateChair->execute();
            $roleCleanup[] = "Chairperson";
        }
        if (($committee['vice_chair_id'] ?? 0) == $userId) {
            $updateVice = $conn->prepare("UPDATE committees SET vice_chair_id = NULL WHERE committee_id = ?");
            $updateVice->bind_param("i", $committeeId);
            $updateVice->execute();
            $roleCleanup[] = "Vice-Chairperson";
        }
        if (($committee['secretary_id'] ?? 0) == $userId) {
            $updateSec = $conn->prepare("UPDATE committees SET secretary_id = NULL WHERE committee_id = ?");
            $updateSec->bind_param("i", $committeeId);
            $updateSec->execute();
            $roleCleanup[] = "Secretary";
        }

        // Log the action with details
        if (function_exists('logAuditAction')) {
            $details = "Removed '{$userName}' from committee '{$committeeName}'";
            if (!empty($roleCleanup)) {
                $details .= " (Automatic Role Removal: " . implode(", ", $roleCleanup) . ")";
            }
            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'REMOVE_MEMBER',
                'committees',
                $details
            );
        }

        return true;
    }

    error_log("Error removing committee member: " . $conn->error);
    return false;
}

/**
 * Update committee member
 */
function updateCommitteeMember($memberId, $data)
{
    global $conn;

    // Get old values first
    $oldStmt = $conn->prepare("SELECT cm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as member_name,
                c.committee_name
            FROM committee_members cm
            JOIN users u ON cm.user_id = u.user_id
            JOIN committees c ON cm.committee_id = c.committee_id
            WHERE cm.member_id = ?");
    $oldStmt->bind_param("i", $memberId);
    $oldStmt->execute();
    $oldMember = $oldStmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("UPDATE committee_members 
        SET position = ?, 
            join_date = ?
        WHERE member_id = ?");

    $position = $data['position'] ?? 'Member';
    $joinDate = $data['join_date'] ?? date('Y-m-d');

    $stmt->bind_param("ssi", $position, $joinDate, $memberId);

    if ($stmt->execute()) {
        // Build change log
        $changes = [];
        if ($oldMember && $oldMember['position'] !== $position) {
            $changes[] = "position from '{$oldMember['position']}' to '{$position}'";
        }
        if ($oldMember && $oldMember['join_date'] !== $joinDate) {
            $changes[] = "join date from '{$oldMember['join_date']}' to '{$joinDate}'";
        }

        // Log the action with details - always log if we have old member data
        if ($oldMember) {
            if (!empty($changes)) {
                $changeDetails = "Updated " . implode(", ", $changes) .
                    " for member '{$oldMember['member_name']}' in committee '{$oldMember['committee_name']}'";
            } else {
                // No changes detected, but update was called
                $changeDetails = "Updated member '{$oldMember['member_name']}' in committee '{$oldMember['committee_name']}' (no changes)";
            }

            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'UPDATE_MEMBER',
                'committees',
                $changeDetails
            );
        } else {
            error_log("updateCommitteeMember: Could not get old member data for member_id: $memberId");
        }

        return true;
    }

    error_log("Error updating committee member: " . $conn->error);
    return false;
}

/**
 * Get committee statistics dynamically from database
 */
function getCommitteeStatistics($committeeId)
{
    global $conn;

    // Count members
    $membersStmt = $conn->prepare("SELECT COUNT(*) as count FROM committee_members 
        WHERE committee_id = ? AND is_active = 1");
    $membersStmt->bind_param("i", $committeeId);
    $membersStmt->execute();
    $membersCount = $membersStmt->get_result()->fetch_assoc()['count'];
    $memberCount = $membersCount; // For compatibility

    // Count meetings
    $meetingsStmt = $conn->prepare("SELECT COUNT(*) as count FROM meetings 
        WHERE committee_id = ?");
    $meetingsStmt->bind_param("i", $committeeId);
    $meetingsStmt->execute();
    $meetingsCount = $meetingsStmt->get_result()->fetch_assoc()['count'];

    // Count pending referrals
    $referralsStmt = $conn->prepare("SELECT COUNT(*) as count FROM referrals r
        INNER JOIN legislative_documents ld ON r.document_id = ld.document_id
        WHERE r.to_committee_id = ? AND r.status = 'Pending'");
    $referralsStmt->bind_param("i", $committeeId);
    $referralsStmt->execute();
    $pendingReferrals = $referralsStmt->get_result()->fetch_assoc()['count'];

    // Count all referrals (not just pending)
    $allReferralsStmt = $conn->prepare("SELECT COUNT(*) as count FROM referrals r
        INNER JOIN legislative_documents ld ON r.document_id = ld.document_id
        WHERE r.to_committee_id = ?");
    $allReferralsStmt->bind_param("i", $committeeId);
    $allReferralsStmt->execute();
    $referralCount = $allReferralsStmt->get_result()->fetch_assoc()['count'];

    // Count agendas
    $agendasStmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda_items ai
        INNER JOIN meetings m ON ai.meeting_id = m.meeting_id
        WHERE m.committee_id = ?");
    $agendasStmt->bind_param("i", $committeeId);
    $agendasStmt->execute();
    $agendasCount = $agendasStmt->get_result()->fetch_assoc()['count'];

    // Count action items (tasks)
    $tasksStmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks 
        WHERE committee_id = ?");
    $tasksStmt->bind_param("i", $committeeId);
    $tasksStmt->execute();
    $tasksCount = $tasksStmt->get_result()->fetch_assoc()['count'];

    // Count documents
    $docStmt = $conn->prepare("SELECT COUNT(*) as count FROM legislative_documents 
        WHERE assigned_committee_id = ?"); // Changed from committee_id to assigned_committee_id
    $docStmt->bind_param("i", $committeeId);
    $docStmt->execute();
    $docCount = $docStmt->get_result()->fetch_assoc()['count'];

    return [
        'member_count' => $memberCount,
        'meetings_held' => $meetingsCount,
        'pending_referrals' => $pendingReferrals,
        'agendas_count' => $agendasCount,
        'action_items_count' => $tasksCount, // Renamed from tasks_count
        'referral_count' => $referralCount,
        'document_count' => $docCount
    ];
}


/**
 * Get users who are NOT yet members of a specific committee
 */
function getAvailableUsersForCommittee($committeeId)
{
    global $conn;

    $sql = "SELECT 
                user_id,
                CONCAT(first_name, ' ', last_name) as full_name,
                email,
                position,
                department,
                role_id
            FROM users
            WHERE is_active = 1
            AND role_id NOT IN (1, 2) -- Exclude Super Admin and Admin
            AND user_id NOT IN (
                SELECT user_id 
                FROM committee_members 
                WHERE committee_id = ? AND is_active = 1
            )
            ORDER BY last_name, first_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}

/**
 * Get committee history from audit logs
 */
function getCommitteeHistory($committeeId)
{
    global $conn;

    // Get committee name first
    $committee = getCommitteeById($committeeId);
    $committeeName = $committee['name'] ?? '';

    $stmt = $conn->prepare("SELECT 
                al.log_id,
                al.action,
                al.description,
                al.timestamp as date,
                CONCAT(u.first_name, ' ', u.last_name) as user,
                u.email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            WHERE al.module = 'committees' 
            AND (
                al.description LIKE CONCAT('%committee \\'', ?, '\\'%')
                OR al.description LIKE CONCAT('%ID: ', ?)
                OR al.description LIKE CONCAT('%committee ID: ', ?)
            )
            ORDER BY al.timestamp DESC");

    $stmt->bind_param("sii", $committeeName, $committeeId, $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'id' => $row['log_id'],
            'action' => $row['action'],
            'description' => $row['description'],
            'date' => $row['date'],
            'user' => $row['user'] ?? 'System'
        ];
    }

    return $history;
}

/**
 * Get committee documents (including meeting documents)
 */
function getCommitteeDocuments($committeeId)
{
    global $conn;

    // Get general committee documents
    $stmt = $conn->prepare("SELECT 
                ld.*,
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
            FROM legislative_documents ld
            LEFT JOIN users u ON ld.created_by = u.user_id
            WHERE ld.assigned_committee_id = ?
            ORDER BY ld.created_at DESC");

    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $fileInfo = json_decode($row['content'], true);
        $documents[] = [
            'id' => $row['document_id'],
            'title' => $row['title'],
            'type' => $row['document_type'],
            'description' => $row['description'],
            'status' => $row['status'],
            'uploaded_date' => $row['created_at'],
            'uploaded_by' => $row['uploaded_by_name'] ?? 'Unknown',
            'file_name' => $fileInfo['original_name'] ?? $row['document_number'],
            'file_path' => $fileInfo['file_path'] ?? null,
            'source' => 'Committee'
        ];
    }

    // Get meeting documents for this committee
    $stmt = $conn->prepare("SELECT 
                md.*,
                m.meeting_title as meeting_title,
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
            FROM meeting_documents md
            JOIN meetings m ON md.meeting_id = m.meeting_id
            LEFT JOIN users u ON md.uploaded_by = u.user_id
            WHERE m.committee_id = ? AND (md.document_type != 'minutes' OR (md.file_path IS NOT NULL AND md.file_path != ''))
            ORDER BY md.created_at DESC");

    $stmt->bind_param("i", $committeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $documents[] = [
            'id' => $row['document_id'],
            'title' => $row['title'],
            'type' => $row['document_type'],
            'description' => $row['content'], // Meeting docs use content for description
            'status' => 'Meeting Doc',
            'uploaded_date' => $row['created_at'],
            'uploaded_by' => $row['uploaded_by_name'] ?? 'Unknown',
            'file_name' => $row['file_name'] ?? $row['title'],
            'file_path' => $row['file_path'] ?? null,
            'source' => 'Meeting: ' . $row['meeting_title'],
            'is_meeting_doc' => true
        ];
    }

    // Sort all documents by date descending
    usort($documents, function ($a, $b) {
        return strtotime($b['uploaded_date']) - strtotime($a['uploaded_date']);
    });

    return $documents;
}

/**
 * Save committee document with file upload
 */
function saveCommitteeDocument($committeeId, $data, $file = null)
{
    global $conn;

    // Generate document number
    $prefix = 'DOC-' . date('Y');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM legislative_documents WHERE document_number LIKE ?");
    $searchPattern = $prefix . '%';
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $documentNumber = $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    $filePath = null;
    $fileSize = null;
    $mimeType = null;

    // Handle file upload if provided
    if ($file && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
        // Validate file
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        $fileSize = $file['size'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            error_log("Invalid file type: $mimeType");
            return false;
        }

        if ($fileSize > $maxSize) {
            error_log("File too large: $fileSize bytes");
            return false;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/committee-documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $documentNumber . '_' . time() . '.' . $extension;
        $filePath = 'uploads/committee-documents/' . $fileName;
        $fullPath = __DIR__ . '/../../' . $filePath;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            error_log("Failed to move uploaded file");
            return false;
        }
    }

    // Insert document
    $stmt = $conn->prepare("INSERT INTO legislative_documents 
        (document_number, document_type, title, description, assigned_committee_id, created_by, status, content) 
        VALUES (?, 'committee_report', ?, ?, ?, ?, 'Draft', ?)");

    $userId = $_SESSION['user_id'] ?? 1;
    $contentData = json_encode([
        'file_path' => $filePath,
        'file_size' => $fileSize,
        'mime_type' => $mimeType,
        'original_name' => $file['name'] ?? null
    ]);

    $stmt->bind_param(
        "sssiis",
        $documentNumber,
        $data['title'],
        $data['description'],
        $committeeId,
        $userId,
        $contentData
    );

    if ($stmt->execute()) {
        $documentId = $conn->insert_id;

        // Log the action
        $committee = getCommitteeById($committeeId);
        $committeeName = $committee['name'] ?? "Committee ID: $committeeId";

        if (function_exists('logAuditAction')) {
            $logMessage = "Uploaded document '{$data['title']}' to committee '{$committeeName}'";
            if ($filePath) {
                $logMessage .= " (File: " . basename($filePath) . ")";
            }

            logAuditAction(
                $userId,
                'UPLOAD_DOCUMENT',
                'committees',
                $logMessage
            );
        }

        return $documentId;
    }

    error_log("Error saving committee document: " . $conn->error);
    return false;
}

// End of file

/**
 * Remove committee document
 */
function removeCommitteeDocument($documentId)
{
    global $conn;

    // Get document info first
    $stmt = $conn->prepare("SELECT ld.*, c.committee_name 
            FROM legislative_documents ld
            LEFT JOIN committees c ON ld.assigned_committee_id = c.committee_id
            WHERE ld.document_id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $document = $result->fetch_assoc();

    if (!$document) {
        return false;
    }

    // Parse file info and delete file if exists
    $fileInfo = json_decode($document['content'], true);
    if ($fileInfo && isset($fileInfo['file_path'])) {
        $fullPath = __DIR__ . '/../../' . $fileInfo['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM legislative_documents WHERE document_id = ?");
    $deleteStmt->bind_param("i", $documentId);

    if ($deleteStmt->execute()) {
        // Log the action
        if (function_exists('logAuditAction')) {
            $committeeName = $document['committee_name'] ?? 'Unknown Committee';
            logAuditAction(
                $_SESSION['user_id'] ?? null,
                'DELETE_DOCUMENT',
                'committees',
                "Deleted document '{$document['title']}' from committee '{$committeeName}'"
            );
        }

        return true;
    }

    error_log("Error deleting document: " . $conn->error);
    return false;
}

/**
 * Check if a user is a member of a committee
 */
function isCommitteeMember($committeeId, $userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT member_id FROM committee_members WHERE committee_id = ? AND user_id = ? AND is_active = 1");
    $stmt->bind_param("ii", $committeeId, $userId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Get all approved resolutions for Legal Basis selection
 */
function getApprovedResolutions()
{
    global $conn;
    $sql = "SELECT document_id, document_number, title 
            FROM legislative_documents 
            WHERE document_type = 'resolution' AND status = 'Approved'
            ORDER BY created_at DESC";
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
 * Check if a Creation Authority (string) exists as a document
 */
function getDocumentByNumber($number)
{
    global $conn;
    $stmt = $conn->prepare("SELECT document_id, title FROM legislative_documents WHERE document_number = ?");
    $stmt->bind_param("s", $number);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

?>