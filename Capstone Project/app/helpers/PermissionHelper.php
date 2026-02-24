<?php
/**
 * Permission Helper
 * Manages role-based access control
 */
require_once __DIR__ . '/UserHelper.php';

/**
 * Role definitions
 */
function getRoles()
{
    return [
        1 => 'Super Admin',
        2 => 'Admin',
        3 => 'Chairman',
        4 => 'Vice-Chair',
        5 => 'Committee Member',
        6 => 'Secretary'
    ];
}

/**
 * Permission matrix
 */
function getPermissionMatrix()
{
    return [
        'Super Admin' => [
            'committees' => ['create', 'read', 'update', 'delete', 'manage_all'],
            'meetings' => ['create', 'read', 'update', 'delete', 'approve', 'manage_all'],
            'referrals' => ['create', 'read', 'update', 'delete', 'approve', 'manage_all'],
            'action_items' => ['create', 'read', 'update', 'delete'],
            'reports' => ['create', 'read', 'update', 'delete', 'publish'],
            'users' => ['create', 'read', 'update', 'delete'],
            'settings' => ['read', 'update'],
            'audit_logs' => ['read'],
            'agendas' => ['create', 'read', 'update', 'delete', 'manage_all']
        ],
        'Admin' => [
            'committees' => ['create', 'read', 'update', 'delete', 'manage_all'],
            'meetings' => ['create', 'read', 'update', 'delete', 'approve', 'manage_all'],
            'referrals' => ['create', 'read', 'update', 'delete', 'approve', 'manage_all'],
            'action_items' => ['create', 'read', 'update', 'delete'],
            'reports' => ['create', 'read', 'update', 'delete', 'publish'],
            'users' => ['create', 'read', 'update', 'delete'],
            'settings' => ['read', 'update'],
            'audit_logs' => ['read'],
            'agendas' => ['create', 'read', 'update', 'delete', 'manage_all']
        ],
        'Chairman' => [
            'committees' => ['read', 'update'],
            'meetings' => ['create', 'read', 'update', 'delete', 'approve'], // 'delete' is Archive
            'referrals' => ['read', 'update'],
            'action_items' => ['create', 'read', 'update', 'delete'],
            'reports' => ['create', 'read', 'update', 'publish', 'delete'],
            'users' => [],
            'settings' => [],
            'agendas' => ['create', 'read', 'update', 'delete', 'approve'] // Chair can Approve
        ],
        'Vice-Chair' => [
            'committees' => ['read'],
            'meetings' => ['create', 'read', 'update'],
            'referrals' => ['read', 'update'],
            'action_items' => ['create', 'read', 'update'],
            'reports' => ['create', 'read', 'update'],
            'users' => [],
            'settings' => [],
            'agendas' => ['read', 'update']
        ],
        'Committee Member' => [
            'committees' => ['read'],
            'meetings' => ['read'],
            'referrals' => ['read'],
            'action_items' => ['read', 'update'], // Can update own action items
            'reports' => ['read'],
            'users' => [],
            'settings' => [],
            'agendas' => ['read']
        ],
        'Secretary' => [
            'committees' => ['read', 'update'],
            'meetings' => ['create', 'read', 'update'], // Secretary can schedule "By Order"
            'referrals' => ['read', 'update'],
            'action_items' => ['create', 'read', 'update'],
            'reports' => ['read'],
            'users' => [],
            'settings' => [],
            'agendas' => ['create', 'read', 'update', 'delete'] // Secretary drafts/archives
        ]
    ];
}

/**
 * Check if user has permission
 */
function hasPermission($userId, $module, $action)
{
    // Get user role
    $user = UserHelper_getUserById($userId);
    if (!$user) {
        return false;
    }

    $roleName = $user['role_name'] ?? 'Public';

    // Normalize role names for matrix lookup
    if ($roleName === 'Super Administrator')
        $roleName = 'Super Admin';
    if ($roleName === 'Administrator')
        $roleName = 'Admin';

    $matrix = getPermissionMatrix();

    if (!isset($matrix[$roleName])) {
        return false;
    }

    if (!isset($matrix[$roleName][$module])) {
        return false;
    }

    $result = in_array($action, $matrix[$roleName][$module]);
    if ($module === 'users') {
        error_log("Permission Check: UserID=$userId, Role=$roleName, Module=$module, Action=$action, Result=" . ($result ? 'TRUE' : 'FALSE'));
    }
    return $result;
}

/**
 * Get role permissions
 */
function getRolePermissions($roleId)
{
    $roles = getRoles();
    $roleName = $roles[$roleId] ?? null;

    if (!$roleName) {
        return [];
    }

    $matrix = getPermissionMatrix();
    return $matrix[$roleName] ?? [];
}

/**
 * Can create
 */
function canCreate($userId, $module)
{
    return hasPermission($userId, $module, 'create');
}

/**
 * Can read
 */
function canRead($userId, $module)
{
    return hasPermission($userId, $module, 'read');
}

/**
 * Can update
 */
function canUpdate($userId, $module, $itemId = null)
{
    // First check matrix permission
    if (!hasPermission($userId, $module, 'update')) {
        return false;
    }

    // If no specific item, we just check general permission
    if ($itemId === null) {
        return true;
    }

    // Admins can update anything
    $user = UserHelper_getUserById($userId);
    if ($user && ($user['role_name'] === 'Super Admin' || $user['role_name'] === 'Admin')) {
        return true;
    }

    // Item-specific ownership/leadership checks
    if ($module === 'committees') {
        require_once __DIR__ . '/CommitteeHelper.php';
        $committee = getCommitteeById($itemId);
        if ($committee) {
            // Chairperson, Vice Chairperson, and Secretary can update their committee
            return (
                $userId == ($committee['chairperson_id'] ?? 0) ||
                $userId == ($committee['vice_chair_id'] ?? 0) ||
                $userId == ($committee['secretary_id'] ?? 0)
            );
        }
    }

    return true; // Default to true if no specific ownership check defined but matrix allows
}

/**
 * Can delete
 */
function canDelete($userId, $module, $itemId = null)
{
    // First check matrix permission
    if (!hasPermission($userId, $module, 'delete')) {
        return false;
    }

    // If no specific item, we just check general permission
    if ($itemId === null) {
        return true;
    }

    // Admins can delete anything they have permission for
    $user = UserHelper_getUserById($userId);
    if ($user && ($user['role_name'] === 'Super Admin' || $user['role_name'] === 'Admin')) {
        return true;
    }

    // Ownership checks for specific records
    if ($module === 'committees') {
        require_once __DIR__ . '/CommitteeHelper.php';
        $committee = getCommitteeById($itemId);
        if ($committee) {
            // Only Chairperson can delete (or Admins handled above)
            return ($userId == ($committee['chairperson_id'] ?? 0));
        }
    }

    // Default to false for non-admins deleting specific items unless handled above
    return false;
}

/**
 * Can approve
 */
function canApprove($userId, $module, $itemId = null)
{
    if (!hasPermission($userId, $module, 'approve')) {
        return false;
    }

    if ($itemId === null) {
        return true;
    }

    $user = UserHelper_getUserById($userId);
    if ($user && ($user['role_name'] === 'Super Admin' || $user['role_name'] === 'Admin')) {
        return true;
    }

    // Item-specific ownership/leadership checks
    if ($module === 'meetings' || $module === 'agendas') {
        require_once __DIR__ . '/CommitteeHelper.php';

        // For agendas, we check the meeting first
        if ($module === 'agendas') {
            require_once __DIR__ . '/MeetingHelper.php';
            $meeting = getMeetingById($itemId);
            $committeeId = $meeting['committee_id'] ?? 0;
        } else {
            require_once __DIR__ . '/MeetingHelper.php';
            $meeting = getMeetingById($itemId);
            $committeeId = $meeting['committee_id'] ?? 0;
        }

        if ($committeeId) {
            $committee = getCommitteeById($committeeId);
            if ($committee) {
                // Only Chairperson can approve (Vice Chairman can be added if needed)
                return ($userId == ($committee['chairperson_id'] ?? 0));
            }
        }
    }

    return false;
}

/**
 * Can publish
 */
function canPublish($userId, $module, $itemId = null)
{
    if (!hasPermission($userId, $module, 'publish')) {
        return false;
    }

    if ($itemId === null) {
        return true;
    }

    $user = UserHelper_getUserById($userId);
    if ($user && ($user['role_name'] === 'Super Admin' || $user['role_name'] === 'Admin')) {
        return true;
    }

    return false; // Default to Admin-only for publishing unless explicit leadership logic added
}

/**
 * Check if user can edit specific item (ownership check)
 */
function canEdit($userId, $module, $itemId)
{
    // First check if user has update permission
    if (!canUpdate($userId, $module)) {
        return false;
    }

    // Admins can edit anything
    $user = UserHelper_getUserById($userId);
    if ($user && ($user['role_name'] === 'Super Admin' || $user['role_name'] === 'Admin')) {
        return true;
    }

    // Ownership checks
    if ($module === 'committees') {
        require_once __DIR__ . '/CommitteeHelper.php';
        $committee = getCommitteeById($itemId);
        if ($committee) {
            // Check if user is Chair, Vice Chair, or Secretary
            return (
                $userId == ($committee['chairperson_id'] ?? 0) ||
                $userId == ($committee['vice_chair_id'] ?? 0) ||
                $userId == ($committee['secretary_id'] ?? 0)
            );
        }
    }

    if ($module === 'meetings') {
        require_once __DIR__ . '/MeetingHelper.php';
        $meeting = getMeetingById($itemId);
        if ($meeting) {
            require_once __DIR__ . '/CommitteeHelper.php';
            $committee = getCommitteeById($meeting['committee_id']);
            if ($committee) {
                return (
                    $userId == ($committee['chairperson_id'] ?? 0) ||
                    $userId == ($committee['vice_chair_id'] ?? 0) ||
                    $userId == ($committee['secretary_id'] ?? 0)
                );
            }
        }
    }

    if ($module === 'referrals') {
        require_once __DIR__ . '/ReferralHelper.php';
        $referral = getReferralById($itemId);
        if ($referral) {
            require_once __DIR__ . '/CommitteeHelper.php';
            $committee = getCommitteeById($referral['committee_id']);
            if ($committee) {
                return (
                    $userId == ($committee['chairperson_id'] ?? 0) ||
                    $userId == ($committee['vice_chair_id'] ?? 0) ||
                    $userId == ($committee['secretary_id'] ?? 0)
                );
            }
        }
    }

    // For other modules, assume update permission is enough for now
    return true;
}

/**
 * Check if user can view module
 */
function canViewModule($userId, $module)
{
    return canRead($userId, $module);
}
?>