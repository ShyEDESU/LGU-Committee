<?php
/**
 * Session Manager - Session handling and authentication
 * 
 * Manages user sessions, login/logout, and session validation.
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../../config/session_config.php');

class SessionManager
{
    private $conn;

    public function __construct($database_connection)
    {
        $this->conn = $database_connection;
    }

    /**
     * Validate if user is logged in and has valid session
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Verify credentials without starting a session (Pre-authentication)
     */
    public function verifyCredentials($email, $password)
    {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.password_hash, u.role_id, u.email_verified, u.is_active, r.role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.role_id 
                  WHERE u.email = ? AND u.is_active = TRUE AND u.email_verified = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }

        // Log failed attempt
        $this->logAuditAction(0, 'PREAUTH_FAILED', 'Authentication', 'Failed pre-authentication attempt with email: ' . $email);
        return false;
    }

    /**
     * Authenticate user with credentials
     */
    public function authenticate($email, $password)
    {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.password_hash, u.role_id, u.email_verified, u.is_active, r.role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.role_id 
                  WHERE u.email = ? AND u.is_active = TRUE AND u.email_verified = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Update last login
                $this->updateLastLogin($user['user_id']);

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['login_time'] = time();

                // Frontend compatibility keys
                $_SESSION['user_name'] = $_SESSION['full_name'];
                $_SESSION['user_email'] = $_SESSION['email'];
                $_SESSION['user_role'] = $_SESSION['role_name'];

                // Log the login action
                $this->logAuditAction($user['user_id'], 'LOGIN', 'Authentication', 'User logged in successfully');

                return true;
            }
        }

        // Log failed login attempt with email
        $this->logAuditAction(0, 'LOGIN_FAILED', 'Authentication', 'Failed login attempt with email: ' . $email);

        return false;
    }

    /**
     * Set temporary session for OTP verification
     */
    public function setPreAuthSession($user)
    {
        // Unset any existing full session
        $_SESSION = array();

        $_SESSION['otp_user_id'] = $user['user_id'];
        $_SESSION['otp_email'] = $user['email'];
        $_SESSION['otp_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['is_otp_pending'] = true;
        $_SESSION['otp_start_time'] = time();

        return true;
    }

    /**
     * Complete authentication after OTP verification
     */
    public function completeAuthentication($user_id)
    {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.role_id, r.role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.role_id 
                  WHERE u.user_id = ? AND u.is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Clear OTP from DB
            $clear_query = "UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE user_id = ?";
            $clear_stmt = $this->conn->prepare($clear_query);
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();

            // Clear OTP session variables
            unset($_SESSION['otp_user_id']);
            unset($_SESSION['otp_email']);
            unset($_SESSION['otp_name']);
            unset($_SESSION['is_otp_pending']);
            unset($_SESSION['otp_start_time']);

            // Update last login
            $this->updateLastLogin($user['user_id']);

            // Set full session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['login_time'] = time();

            // Frontend compatibility keys
            $_SESSION['user_name'] = $_SESSION['full_name'];
            $_SESSION['user_email'] = $_SESSION['email'];
            $_SESSION['user_role'] = $_SESSION['role_name'];

            // Log the login action
            $this->logAuditAction($user['user_id'], 'LOGIN_OTP_VERIFIED', 'Authentication', 'User logged in successfully after OTP verification');

            return true;
        }

        return false;
    }

    /**
     * Logout user and destroy session
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->logAuditAction($_SESSION['user_id'], 'LOGOUT', 'Authentication', 'User logged out');
        }

        // Unset all session variables
        $_SESSION = array();

        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy session data on server
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return true;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($required_role)
    {
        if (!isset($_SESSION['role_name'])) {
            return false;
        }

        $currentRole = strtolower($_SESSION['role_name']);
        $targetRole = strtolower($required_role);

        // Admin gets full access (treat as Super Admin for now)
        if ($currentRole === 'admin' || $currentRole === 'super admin') {
            return true;
        }

        return $currentRole === $targetRole;
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $query = "SELECT permissions FROM roles WHERE role_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['role_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $permissions = json_decode($row['permissions'], true);
            return isset($permissions[$permission]) && $permissions[$permission] === true;
        }

        return false;
    }

    /**
     * Update last login timestamp
     */
    private function updateLastLogin($user_id)
    {
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    /**
     * Log audit action
     */
    private function logAuditAction($user_id, $action, $module, $description)
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $query = "INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Pass NULL for user_id if it's 0 (failed login attempt)
        $user_id = ($user_id === 0) ? null : $user_id;
        $stmt->bind_param("issss", $user_id, $action, $module, $description, $ip_address);
        return $stmt->execute();
    }

    /**
     * Get current user information
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Authenticate user by user ID (for OAuth flows)
     */
    public function authenticateByUserId($user_id)
    {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.role_id, u.is_active, r.role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.role_id 
                  WHERE u.user_id = ? AND u.is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Update last login
            $this->updateLastLogin($user['user_id']);

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['login_time'] = time();

            // Frontend compatibility keys
            $_SESSION['user_name'] = $_SESSION['full_name'];
            $_SESSION['user_email'] = $_SESSION['email'];
            $_SESSION['user_role'] = $_SESSION['role_name'];

            // Log the login action
            $this->logAuditAction($user['user_id'], 'OAUTH_LOGIN', 'Authentication', 'User logged in via OAuth provider');

            return true;
        }

        return false;
    }
}

?>