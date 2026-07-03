<?php
/**
 * User Helper
 * Manages user data and operations
 */
require_once __DIR__ . '/../../config/database.php';

/**
 * Get all users
 */
function UserHelper_getAllUsers()
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
function UserHelper_getUserById($id)
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
function UserHelper_getUsersByRole($roleId)
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
function UserHelper_getActiveUsers()
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
 * Create user with email verification
 */
function UserHelper_createUser($data)
{
    global $conn;

    // Validate email format and uniqueness
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    // Check if email exists
    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $data['email']);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        return ['success' => false, 'message' => 'Email address already registered'];
    }
    $stmt_check->close();

    // Basic domain check for "real" email
    $domain = substr(strrchr($data['email'], "@"), 1);
    if (function_exists('checkdnsrr')) {
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return ['success' => false, 'message' => 'The email domain does not appear to be valid'];
        }
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Generation of verification token
    $verificationToken = bin2hex(random_bytes(16)); // 32 chars
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Status (Default to inactive and unverified for new users)
    $isActive = isset($data['is_active']) ? $data['is_active'] : 0;
    $emailVerified = 0;

    $query = "INSERT INTO users (email, password_hash, first_name, last_name, 
                                 phone, role_id, department, position, is_active,
                                 email_verified, verification_token, verification_token_expires)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $phone = $data['phone'] ?? null;
    $department = $data['department'] ?? null;
    $position = $data['position'] ?? null;

    $stmt->bind_param(
        "sssssississs",
        $data['email'],
        $passwordHash,
        $data['first_name'],
        $data['last_name'],
        $phone,
        $data['role_id'],
        $department,
        $position,
        $isActive,
        $emailVerified,
        $verificationToken,
        $expires
    );

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();

        // Send verification email
        require_once __DIR__ . '/MailHelper.php';
        sendVerificationEmail($data['email'], $data['first_name'], $verificationToken);

        return ['success' => true, 'message' => 'User created successfully. A verification email has been sent.', 'user_id' => $userId];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create user: ' . $error];
    }
}

/**
 * Verify token and activate account
 */
function verifyToken($token)
{
    global $conn;

    // Find user by token and check expiry
    $stmt = $conn->prepare("SELECT user_id, email FROM users 
                            WHERE verification_token = ? 
                            AND verification_token_expires > NOW()
                            AND email_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid or expired verification link.'];
    }

    // Activate account
    $userId = $user['user_id'];
    $stmt = $conn->prepare("UPDATE users SET 
                            is_active = 1, 
                            email_verified = 1, 
                            verification_token = NULL, 
                            verification_token_expires = NULL 
                            WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Your account has been verified and activated! You can now log in.'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to activate account. Please contact the administrator.'];
    }
}

/**
 * Update user
 */
function UserHelper_updateUser($id, $data)
{
    global $conn;

    $fields = [];
    $params = [];
    $types = '';

    // Map of fields to update
    $allowedFields = [
        'email' => 's',
        'first_name' => 's',
        'last_name' => 's',
        'phone' => 's',
        'role_id' => 'i',
        'department' => 's',
        'position' => 's',
        'is_active' => 'i',
        'bio' => 's',
        'profile_picture' => 's'
    ];

    foreach ($allowedFields as $field => $type) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
            $types .= $type;
        }
    }

    // Special handling for password
    if (!empty($data['password'])) {
        $fields[] = "password_hash = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= 's';
    }

    if (empty($fields)) {
        return ['success' => false, 'message' => 'No fields to update'];
    }

    $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
    $params[] = $id;
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
function UserHelper_deleteUser($id)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);

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
 * Update user role
 */
function updateUserRole($userId, $roleId)
{
    return updateUser($userId, ['role_id' => $roleId]);
}

/**
 * Get all roles from roles table
 */
function UserHelper_getRoles()
{
    global $conn;

    $query = "SELECT role_id, role_name FROM roles ORDER BY role_name";
    $result = $conn->query($query);

    $roles = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
    }

    return $roles;
}
?>