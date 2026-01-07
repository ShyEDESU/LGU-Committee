<?php
/**
 * Session Manager - Session handling and authentication
 * 
 * Manages user sessions, login/logout, and session validation.
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

session_start();

class SessionManager {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Validate if user is logged in and has valid session
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Authenticate user with credentials
     */
    public function authenticate($email, $password) {
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
     * Logout user and destroy session
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logAuditAction($_SESSION['user_id'], 'LOGOUT', 'Authentication', 'User logged out');
        }
        
        // Destroy session
        session_destroy();
        return true;
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($required_role) {
        return isset($_SESSION['role_name']) && $_SESSION['role_name'] === $required_role;
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
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
    private function updateLastLogin($user_id) {
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    /**
     * Log audit action
     */
    private function logAuditAction($user_id, $action, $module, $description) {
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
    public function getCurrentUser() {
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
    public function authenticateByUserId($user_id) {
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
            
            // Log the login action
            $this->logAuditAction($user['user_id'], 'OAUTH_LOGIN', 'Authentication', 'User logged in via OAuth provider');
            
            return true;
        }
        
        return false;
    }
}

?>
