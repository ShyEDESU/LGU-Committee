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
    $query = "SELECT u.user_id, u.email, u.email_verified, u.first_name, u.last_name, u.phone, 
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
    require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
    $user = UserHelper_getUserById($userId);

    if ($user) {
        $user['status'] = $user['is_active'] ? 'active' : 'inactive';
    }
    return $user;
}

/**
 * Create new user
 */
function createUser($data)
{
    require_once __DIR__ . '/../../../app/helpers/UserHelper.php';

    // Map data to the format expected by UserHelper if necessary
    if (!isset($data['role_id']) && isset($data['role_name'])) {
        $data['role_id'] = getRoleIdByName($data['role_name']);
    }

    return UserHelper_createUser($data);
}

/**
 * Update user
 */
function updateUser($userId, $data)
{
    require_once __DIR__ . '/../../../app/helpers/UserHelper.php';

    if (isset($data['role_name'])) {
        $data['role_id'] = getRoleIdByName($data['role_name']);
    }

    return UserHelper_updateUser($userId, $data);
}

/**
 * Delete user
 */
function deleteUser($userId, $currentUserId)
{
    require_once __DIR__ . '/../../../app/helpers/UserHelper.php';
    return UserHelper_deleteUser($userId);
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
 * Validate if email domain has valid MX records
 */
function isValidEmailDomain($email)
{
    $domain = substr(strrchr($email, "@"), 1);
    if (empty($domain))
        return false;

    // Check for MX records (basic "real email" check)
    // Note: checkdnsrr might be slow or fail in some environments, so we use it as a secondary check
    if (function_exists('checkdnsrr')) {
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }
    return true; // Fallback if checkdnsrr is not available
}

/**
 * Validate user data
 */
function validateUserData($data, $isUpdate = false)
{
    $errors = [];
    $userId = $data['user_id'] ?? null;

    // Email validation
    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check uniqueness
        if (emailExists($data['email'], $userId)) {
            $errors[] = 'This email address is already registered';
        }

        // Basic domain check for "real" email
        if (!isValidEmailDomain($data['email'])) {
            $errors[] = 'The email domain does not appear to be valid';
        }
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
