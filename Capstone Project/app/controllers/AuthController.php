<?php
/**
 * Authentication Controller - Handle login and logout operations
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../middleware/SessionManager.php');

class AuthController
{
    private $conn;
    private $sessionManager;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        $this->sessionManager = new SessionManager($conn);
    }

    /**
     * Handle login request
     */
    public function login($email, $password)
    {
        // Sanitize input
        $email = htmlspecialchars(trim($email));
        $password = trim($password);

        // Validate input
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email and password are required'
            ];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Initialize login attempts tracking if not set
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['first_attempt_time'] = null;
        }

        // Check if account is currently locked
        if ($_SESSION['login_attempts'] >= 5) {
            if ($_SESSION['first_attempt_time'] === null) {
                $_SESSION['first_attempt_time'] = time();
            }

            $elapsed_time = time() - $_SESSION['first_attempt_time'];
            $lockout_duration = 10; // 10 seconds for testing

            if ($elapsed_time < $lockout_duration) {
                // Still locked
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed login attempts. Please try again later.',
                    'locked' => true
                ];
            } else {
                // Lockout period expired, reset attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['first_attempt_time'] = null;
            }
        }

        // Authenticate
        if ($this->sessionManager->authenticate($email, $password)) {
            // Reset attempts on successful login
            $_SESSION['login_attempts'] = 0;
            $_SESSION['first_attempt_time'] = null;

            return [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => '../public/dashboard.php'
            ];
        }

        // Increment failed attempts
        $_SESSION['login_attempts']++;

        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        $this->sessionManager->logout();
        return [
            'success' => true,
            'message' => 'Logout successful',
            'redirect' => '../index.php'
        ];
    }

    /**
     * Change user password
     */
    public function changePassword($user_id, $current_password, $new_password, $confirm_password)
    {
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            return [
                'success' => false,
                'message' => 'All fields are required'
            ];
        }

        if ($new_password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'New passwords do not match'
            ];
        }

        if (strlen($new_password) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long'
            ];
        }

        // Get current password
        $query = "SELECT password_hash FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $user = $result->fetch_assoc();

        // Verify current password
        if (!password_verify($current_password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }

        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password
        $update_query = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_password_hash, $user_id);

        if ($update_stmt->execute()) {
            // Log action
            $this->logAuditAction($user_id, 'CHANGE_PASSWORD', 'Account', 'User changed password');

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to change password'
        ];
    }

    /**
     * Log audit action
     */
    private function logAuditAction($user_id, $action, $module, $description)
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $query = "INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $action, $module, $description, $ip_address);
        return $stmt->execute();
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set JSON response header
    header('Content-Type: application/json');

    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $authController = new AuthController();

    if ($action === 'login') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $response = $authController->login($email, $password);
        echo json_encode($response);
    } elseif ($action === 'logout') {
        $response = $authController->logout();
        echo json_encode($response);
    } elseif ($action === 'change_password') {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $response = $authController->changePassword($_SESSION['user_id'], $current_password, $new_password, $confirm_password);
        echo json_encode($response);
    }
}

?>