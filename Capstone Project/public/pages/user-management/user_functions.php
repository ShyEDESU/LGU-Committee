<?php
/**
 * User Management Helper Functions
 * ENHANCED: Added role-based permissions
 */

require_once __DIR__ . '/../../../config/database.php';

/**
 * Check if current user can edit target user
 */
function canEditUser($currentUserId, $targetUserId, $currentUserRole, $targetUserRole)
{
    // Can't edit yourself
    if ($currentUserId == $targetUserId) {
        return [
            'can_edit' => true,  // Can edit profile
            'can_edit_role' => false,  // But not own role
            'can_delete' => false
        ];
    }

    $currentRoleLower = strtolower($currentUserRole);
    $targetRoleLower = strtolower($targetUserRole);

    // Super Admin can edit anyone
    if ($currentRoleLower === 'super admin' || $currentRoleLower === 'super administrator') {
        return [
            'can_edit' => true,
            'can_edit_role' => true,
            'can_delete' => true
        ];
    }

    // Admin restrictions
    if ($currentRoleLower === 'admin' || $currentRoleLower === 'administrator') {
        // Can't edit Super Admin or other Admins
        if (strpos($targetRoleLower, 'super admin') !== false || $targetRoleLower === 'super administrator' || $targetRoleLower === 'admin' || $targetRoleLower === 'administrator') {
            return [
                'can_edit' => false,
                'can_edit_role' => false,
                'can_delete' => false
            ];
        }

        // Can edit other roles
        return [
            'can_edit' => true,
            'can_edit_role' => true,
            'can_delete' => true
        ];
    }

    // Default: no permissions
    return [
        'can_edit' => false,
        'can_edit_role' => false,
        'can_delete' => false
    ];
}

/**
 * Get all users with pagination and filters
 */
function getUsers($search = '', $roleFilter = '', $deptFilter = '', $statusFilter = '', $page = 1, $perPage = 20)
{
    global $conn;

    $offset = ($page - 1) * $perPage;

    // Build WHERE clause
    $where = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    if (!empty($roleFilter)) {
        $where[] = "r.role_name = ?";
        $params[] = $roleFilter;
        $types .= 's';
    }

    if (!empty($deptFilter)) {
        $where[] = "u.department = ?";
        $params[] = $deptFilter;
        $types .= 's';
    }

    if (!empty($statusFilter)) {
        // Convert status to boolean
        $isActive = ($statusFilter === 'active') ? 1 : 0;
        $where[] = "u.is_active = ?";
        $params[] = $isActive;
        $types .= 'i';
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countQuery = "SELECT COUNT(*) as total 
                   FROM users u 
                   LEFT JOIN roles r ON u.role_id = r.role_id 
                   $whereClause";
    $countStmt = $conn->prepare($countQuery);

    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];
    $countStmt->close();

    // Get users with role name via JOIN
    $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.phone, 
                     r.role_name, u.role_id, u.department, u.position, 
                     u.is_active, u.profile_picture, u.created_at, u.last_login
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.role_id
              $whereClause
              ORDER BY u.created_at DESC
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Convert is_active to status for UI compatibility
        $row['status'] = $row['is_active'] ? 'active' : 'inactive';
        $users[] = $row;
    }

    $stmt->close();

    return [
        'users' => $users,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}

/**
 * Get single user by ID
 */
function getUserById($userId)
{
    global $conn;

    $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.phone,
                     r.role_name, u.role_id, u.department, u.position, 
                     u.is_active, u.profile_picture, u.bio,
                     u.created_at, u.updated_at, u.last_login
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.role_id
              WHERE u.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Convert is_active to status for UI compatibility
        $user['status'] = $user['is_active'] ? 'active' : 'inactive';
    }

    return $user;
}

/**
 * Create new user
 */
function createUser($data)
{
    global $conn;

    // Validate required fields
    $required = ['email', 'password', 'first_name', 'last_name', 'role_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }

    // Check if email exists
    if (emailExists($data['email'])) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Get role_id from role_name
    $roleId = getRoleIdByName($data['role_name']);
    if (!$roleId) {
        return ['success' => false, 'message' => 'Invalid role'];
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Convert status to is_active
    $isActive = (!isset($data['status']) || $data['status'] === 'active') ? 1 : 0;

    $query = "INSERT INTO users (email, password_hash, first_name, last_name, 
                                 phone, role_id, department, position, is_active)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $phone = $data['phone'] ?? null;
    $department = $data['department'] ?? null;
    $position = $data['position'] ?? null;

    $stmt->bind_param(
        "sssssissi",
        $data['email'],
        $passwordHash,
        $data['first_name'],
        $data['last_name'],
        $phone,
        $roleId,
        $department,
        $position,
        $isActive
    );

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create user: ' . $error];
    }
}

/**
 * Update user
 */
function updateUser($userId, $data)
{
    global $conn;

    // Get current user data
    $currentUser = getUserById($userId);
    if (!$currentUser) {
        return ['success' => false, 'message' => 'User not found'];
    }

    // Check if email changed and if new email exists
    if (!empty($data['email']) && $data['email'] !== $currentUser['email']) {
        if (emailExists($data['email'], $userId)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
    }

    // Build update query
    $fields = [];
    $params = [];
    $types = '';

    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $params[] = $data['email'];
        $types .= 's';
    }

    if (isset($data['first_name'])) {
        $fields[] = "first_name = ?";
        $params[] = $data['first_name'];
        $types .= 's';
    }

    if (isset($data['last_name'])) {
        $fields[] = "last_name = ?";
        $params[] = $data['last_name'];
        $types .= 's';
    }

    if (isset($data['phone'])) {
        $fields[] = "phone = ?";
        $params[] = $data['phone'];
        $types .= 's';
    }

    if (isset($data['role_name'])) {
        $roleId = getRoleIdByName($data['role_name']);
        if ($roleId) {
            $fields[] = "role_id = ?";
            $params[] = $roleId;
            $types .= 'i';
        }
    }

    if (isset($data['department'])) {
        $fields[] = "department = ?";
        $params[] = $data['department'];
        $types .= 's';
    }

    if (isset($data['position'])) {
        $fields[] = "position = ?";
        $params[] = $data['position'];
        $types .= 's';
    }

    if (isset($data['status'])) {
        $isActive = ($data['status'] === 'active') ? 1 : 0;
        $fields[] = "is_active = ?";
        $params[] = $isActive;
        $types .= 'i';
    }

    // Update password if provided
    if (!empty($data['password'])) {
        $fields[] = "password_hash = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= 's';
    }

    if (empty($fields)) {
        return ['success' => false, 'message' => 'No fields to update'];
    }

    $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
    $params[] = $userId;
    $types .= 'i';

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'User updated successfully'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update user: ' . $error];
    }
}

/**
 * Delete user
 */
function deleteUser($userId, $currentUserId)
{
    global $conn;

    // Can't delete yourself
    if ($userId == $currentUserId) {
        return ['success' => false, 'message' => 'Cannot delete your own account'];
    }

    // Check if user is last admin
    $user = getUserById($userId);
    $roleLower = strtolower($user['role_name']);
    if ($user && ($roleLower === 'admin' || $roleLower === 'administrator')) {
        $adminCount = countAdmins();
        if ($adminCount <= 1) {
            return ['success' => false, 'message' => 'Cannot delete the last administrator'];
        }
    }

    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'User deleted successfully'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete user: ' . $error];
    }
}

/**
 * Check if email exists
 */
function emailExists($email, $excludeUserId = null)
{
    global $conn;

    $query = "SELECT user_id FROM users WHERE email = ?";
    if ($excludeUserId) {
        $query .= " AND user_id != ?";
    }

    $stmt = $conn->prepare($query);
    if ($excludeUserId) {
        $stmt->bind_param("si", $email, $excludeUserId);
    } else {
        $stmt->bind_param("s", $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

/**
 * Count total administrators
 */
function countAdmins()
{
    global $conn;

    $query = "SELECT COUNT(*) as count 
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.role_id
              WHERE LOWER(r.role_name) IN ('admin', 'administrator', 'super admin', 'super administrator')";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    return $row['count'];
}

/**
 * Get all unique departments
 */
function getDepartments()
{
    global $conn;

    $query = "SELECT DISTINCT department FROM users WHERE department IS NOT NULL ORDER BY department";
    $result = $conn->query($query);

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department'];
    }

    return $departments;
}

/**
 * Get all roles from roles table
 */
function getRoles()
{
    global $conn;

    $query = "SELECT role_id, role_name FROM roles ORDER BY role_name";
    $result = $conn->query($query);

    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }

    return $roles;
}

/**
 * Get role_id by role_name
 */
function getRoleIdByName($roleName)
{
    global $conn;

    $query = "SELECT role_id FROM roles WHERE role_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc();
    $stmt->close();

    return $role ? $role['role_id'] : null;
}

/**
 * Validate user data
 */
function validateUserData($data, $isUpdate = false)
{
    $errors = [];

    // Email validation
    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Password validation (only required for create)
    if (!$isUpdate && empty($data['password'])) {
        $errors[] = 'Password is required';
    } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    // Name validation
    if (empty($data['first_name'])) {
        $errors[] = 'First name is required';
    }

    if (empty($data['last_name'])) {
        $errors[] = 'Last name is required';
    }

    // Role validation
    if (empty($data['role_name'])) {
        $errors[] = 'Role is required';
    }

    return $errors;
}
