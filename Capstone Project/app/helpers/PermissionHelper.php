<?php
/**
 * Permission Helper
 * Manages role-based access control
 */

/**
 * Role definitions
 */
function getRoles()
{
    return [
        1 => 'Administrator',
        2 => 'Legislative Secretary',
        3 => 'Committee Chair',
        4 => 'Committee Staff',
        5 => 'Committee Member',
        6 => 'Public'
    ];
}

/**
 * Permission matrix
 */
function getPermissionMatrix()
{
    return [
        'Administrator' => [
            'committees' => ['create', 'read', 'update', 'delete'],
            'meetings' => ['create', 'read', 'update', 'delete', 'approve'],
            'referrals' => ['create', 'read', 'update', 'delete', 'approve'],
            'action_items' => ['create', 'read', 'update', 'delete'],
            'reports' => ['create', 'read', 'update', 'delete', 'publish'],
            'users' => ['create', 'read', 'update', 'delete'],
            'settings' => ['read', 'update']
        ],
        'Legislative Secretary' => [
            'committees' => ['read'],
            'meetings' => ['create', 'read', 'update'],
            'referrals' => ['create', 'read', 'update', 'approve'],
            'action_items' => ['create', 'read', 'update'],
            'reports' => ['read'],
            'users' => ['read'],
            'settings' => []
        ],
        'Committee Chair' => [
            'committees' => ['read', 'update'],
            'meetings' => ['create', 'read', 'update'],
            'referrals' => ['read', 'update'],
            'action_items' => ['create', 'read', 'update'],
            'reports' => ['create', 'read', 'update', 'publish'],
            'users' => ['read'],
            'settings' => []
        ],
        'Committee Staff' => [
            'committees' => ['read'],
            'meetings' => ['read', 'update'],
            'referrals' => ['read', 'update'],
            'action_items' => ['create', 'read', 'update'],
            'reports' => ['create', 'read', 'update'],
            'users' => ['read'],
            'settings' => []
        ],
        'Committee Member' => [
            'committees' => ['read'],
            'meetings' => ['read'],
            'referrals' => ['read'],
            'action_items' => ['read'],
            'reports' => ['read'],
            'users' => [],
            'settings' => []
        ],
        'Public' => [
            'committees' => ['read'],
            'meetings' => ['read'],
            'referrals' => [],
            'action_items' => [],
            'reports' => ['read'],
            'users' => [],
            'settings' => []
        ]
    ];
}

/**
 * Check if user has permission
 */
function hasPermission($userId, $module, $action)
{
    // Get user role
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }

    $roleName = $user['role_name'] ?? 'Public';
    $matrix = getPermissionMatrix();

    if (!isset($matrix[$roleName])) {
        return false;
    }

    if (!isset($matrix[$roleName][$module])) {
        return false;
    }

    return in_array($action, $matrix[$roleName][$module]);
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
function canUpdate($userId, $module)
{
    return hasPermission($userId, $module, 'update');
}

/**
 * Can delete
 */
function canDelete($userId, $module)
{
    return hasPermission($userId, $module, 'delete');
}

/**
 * Can approve
 */
function canApprove($userId, $module)
{
    return hasPermission($userId, $module, 'approve');
}

/**
 * Can publish
 */
function canPublish($userId, $module)
{
    return hasPermission($userId, $module, 'publish');
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
    $user = getUserById($userId);
    if ($user && $user['role_name'] === 'Administrator') {
        return true;
    }

    // Additional ownership checks can be added here
    // For now, if user has update permission, they can edit
    return true;
}
?>