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
        }
        if (!isset($_SESSION['lockout_until'])) {
            $_SESSION['lockout_until'] = null;
        }

        // Check if account is currently locked
        if ($_SESSION['lockout_until'] !== null) {
            $elapsed_time = $_SESSION['lockout_until'] - time();
            if ($elapsed_time > 0) {
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed login attempts. Please wait.',
                    'is_locked' => true,
                    'remaining_time' => $elapsed_time
                ];
            } else {
                $_SESSION['lockout_until'] = null;
            }
        }

        // Authenticate
        if ($this->sessionManager->authenticate($email, $password)) {
            // Reset attempts on successful login
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_until'] = null;

            // Generate 6-digit OTP
            $otpCode = mt_rand(100000, 999999);
            $_SESSION['current_otp'] = $otpCode;
            $_SESSION['otp_expires_at'] = time() + 300; // 5 minutes validity
            $_SESSION['otp_pending'] = true; // Flag: user must complete OTP before accessing system

            // Dispatch OTP Email
            require_once __DIR__ . '/../helpers/MailHelper.php';
            $userName = $_SESSION['full_name'] ?? 'User';
            sendOtpEmail($email, $userName, $otpCode);

            return [
                'success' => true,
                'message' => 'Login authenticated. Redirecting to 2-step verification...',
                'redirect' => '../auth/verify-otp.php'
            ];
        }

        // Increment failed attempts
        $_SESSION['login_attempts']++;
        
        $is_locked = false;
        $lockout_duration = 0;
        
        if ($_SESSION['login_attempts'] % 3 === 0) {
            $attempts = $_SESSION['login_attempts'];
            if ($attempts == 3) {
                $lockout_duration = 10;
            } elseif ($attempts == 6) {
                $lockout_duration = 30;
            } elseif ($attempts == 9) {
                $lockout_duration = 300; // 5 mins
            } elseif ($attempts == 12) {
                $lockout_duration = 1800; // 30 mins
            } elseif ($attempts == 15) {
                $lockout_duration = 3600; // 1 hour
            } else {
                $lockout_duration = 18000; // 5 hours
            }
            $_SESSION['lockout_until'] = time() + $lockout_duration;
            $is_locked = true;
        }

        return [
            'success' => false,
            'message' => $is_locked 
                ? 'Invalid email or password. Too many failed attempts, account temporarily locked.' 
                : 'Invalid email or password',
            'is_locked' => $is_locked,
            'remaining_time' => $lockout_duration
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

    /**
     * Verify OTP submission
     */
    public function verifyOTP($otp)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['current_otp']) || !isset($_SESSION['otp_expires_at'])) {
            return [
                'success' => false,
                'message' => 'No active verification code found. Please log in again.'
            ];
        }

        if (time() > $_SESSION['otp_expires_at']) {
            return [
                'success' => false,
                'message' => 'Verification code expired. Please request a new code.'
            ];
        }

        if (trim($otp) == $_SESSION['current_otp']) {
            $_SESSION['otp_pending'] = false;
            unset($_SESSION['otp_pending']);
            unset($_SESSION['current_otp']);
            unset($_SESSION['otp_expires_at']);

            $this->logAuditAction($_SESSION['user_id'], 'OTP_VERIFIED', 'Authentication', 'User successfully verified OTP code');

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'redirect' => '../public/dashboard.php'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid verification code. Please check and try again.'
        ];
    }

    /**
     * Resend a new OTP
     */
    public function resendOTP()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Session expired. Please log in again.'
            ];
        }

        $otpCode = mt_rand(100000, 999999);
        $_SESSION['current_otp'] = $otpCode;
        $_SESSION['otp_expires_at'] = time() + 300; // 5 minutes validity

        $this->logAuditAction($_SESSION['user_id'], 'OTP_RESEND', 'Authentication', 'User requested an OTP code resend');

        // Dispatch OTP Email
        if (!empty($userEmail)) {
            sendOtpEmail($userEmail, $userName, $otpCode);
        }

        return [
            'success' => true,
            'message' => 'A new code has been sent successfully.'
        ];
    }

    /**
     * Handle OAuth Login request
     */
    public function oauthLogin($email)
    {
        $email = htmlspecialchars(trim($email));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email address provided.'
            ];
        }

        // Authenticate via OAuth (by email comparison only)
        if ($this->sessionManager->oauthAuthenticate($email)) {
            // Check if OTP is required for security (Yes, since they just logged in, or we can auto-bypass since OAuth is verified)
            // Typically we still require OTP for maximum security, or bypass if the LGU desires.
            // Let's require OTP to keep standard security guard, but generate and dispatch it.
            $otpCode = mt_rand(100000, 999999);
            $_SESSION['current_otp'] = $otpCode;
            $_SESSION['otp_expires_at'] = time() + 300; // 5 minutes
            $_SESSION['otp_pending'] = true;

            // Dispatch OTP Email
            require_once __DIR__ . '/../helpers/MailHelper.php';
            $userName = $_SESSION['full_name'] ?? 'User';
            sendOtpEmail($email, $userName, $otpCode);

            return [
                'success' => true,
                'message' => 'Account recognized. Redirecting to 2-step verification...',
                'redirect' => '../auth/verify-otp.php'
            ];
        }

        return [
            'success' => false,
            'message' => 'This email is not registered in our system. Please contact your administrator.'
        ];
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
    } elseif ($action === 'oauth_login') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $response = $authController->oauthLogin($email);
        echo json_encode($response);
    } elseif ($action === 'verify_otp') {
        $otp = isset($_POST['otp']) ? $_POST['otp'] : '';
        $response = $authController->verifyOTP($otp);
        echo json_encode($response);
    } elseif ($action === 'resend_otp') {
        $response = $authController->resendOTP();
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
    } elseif ($action === 'heartbeat') {
        // Reset activity timestamp
        $_SESSION['LAST_ACTIVITY'] = time();
        echo json_encode(['success' => true, 'timestamp' => time()]);
    }
}

?>