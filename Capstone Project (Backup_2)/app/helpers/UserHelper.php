<?php
/**
 * User Helper
 * Manages user data and operations
 */

/**
 * Get all users
 */
function getAllUsers()
{
    // In production, query from database
    // For now, return dummy data
    return [
        [
            'user_id' => 1,
            'email' => 'admin@lgu.gov.ph',
            'first_name' => 'John',
            'last_name' => 'Administrator',
            'phone' => '09123456789',
            'department' => 'IT Department',
            'position' => 'Administrator',
            'role_id' => 1,
            'role_name' => 'Administrator',
            'is_active' => true,
            'created_at' => '2025-01-15'
        ],
        [
            'user_id' => 2,
            'email' => 'mary@lgu.gov.ph',
            'first_name' => 'Mary',
            'last_name' => 'Johnson',
            'phone' => '09234567890',
            'department' => 'Legislative Division',
            'position' => 'Legislative Officer',
            'role_id' => 2,
            'role_name' => 'Officer',
            'is_active' => true,
            'created_at' => '2025-02-10'
        ]
    ];
}

/**
 * Get user by ID
 */
function getUserById($id)
{
    $users = getAllUsers();
    foreach ($users as $user) {
        if ($user['user_id'] == $id) {
            return $user;
        }
    }
    return null;
}

/**
 * Get users by role
 */
function getUsersByRole($roleId)
{
    $users = getAllUsers();
    return array_filter($users, function ($user) use ($roleId) {
        return $user['role_id'] == $roleId;
    });
}

/**
 * Get active users
 */
function getActiveUsers()
{
    $users = getAllUsers();
    return array_filter($users, function ($user) {
        return $user['is_active'] === true;
    });
}

/**
 * Create user (placeholder for database integration)
 */
function createUser($data)
{
    // In production, insert into database
    // Required fields: email, first_name, last_name, password_hash, role_id
    return true;
}

/**
 * Update user (placeholder for database integration)
 */
function updateUser($id, $data)
{
    // In production, update database
    return true;
}

/**
 * Delete user (placeholder for database integration)
 */
function deleteUser($id)
{
    // In production, delete from database or set is_active = false
    return true;
}

/**
 * Update user role
 */
function updateUserRole($userId, $roleId)
{
    // In production, update database
    return true;
}
?>