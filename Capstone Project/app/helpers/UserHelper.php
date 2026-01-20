<?php
/**
 * User Helper
 * Manages user data and operations
 */
require_once __DIR__ . '/../../config/database.php';

/**
 * Get all users
 */
function getAllUsers()
{
    global $conn;
    $sql = "SELECT u.*, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.role_id 
            ORDER BY u.first_name ASC";
    $result = $conn->query($sql);

    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

/**
 * Get user by ID
 */
function getUserById($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT u.*, r.role_name 
                            FROM users u 
                            LEFT JOIN roles r ON u.role_id = r.role_id 
                            WHERE u.user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get users by role
 */
function getUsersByRole($roleId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT u.*, r.role_name 
                            FROM users u 
                            LEFT JOIN roles r ON u.role_id = r.role_id 
                            WHERE u.role_id = ?");
    $stmt->bind_param("i", $roleId);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

/**
 * Get active users
 */
function getActiveUsers()
{
    global $conn;
    $sql = "SELECT u.*, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.role_id 
            WHERE u.is_active = 1";
    $result = $conn->query($sql);

    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
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