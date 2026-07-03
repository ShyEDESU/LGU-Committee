<?php
/**
 * Authentication Controller - Handle login and logout operations
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../middleware/SessionManager.php');
require_once(__DIR__ . '/../helpers/MailHelper.php');

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
        if (!isset($_SESSION['login_attempts']) || !isset($_SESSION['lockout_count']) || !isset($_SESSION['failed_in_cycle'])) {
            $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
            $_SESSION['failed_in_cycle'] = $_SESSION['failed_in_cycle'] ?? 0;
            $_SESSION['lockout_count'] = $_SESSION['lockout_count'] ?? 0;
            $_SESSION['last_attempt_time'] = $_SESSION['last_attempt_time'] ?? null;
        }

        // Check for progressive lockout
        if (isset($_SESSION['failed_in_cycle']) && $_SESSION['failed_in_cycle'] >= 3) {
            $last_attempt = $_SESSION['last_attempt_time'] ?? time();
            $elapsed_time = time() - $last_attempt;
            $lockout_duration = $this->getLockoutDuration($_SESSION['lockout_count'] ?? 0);

            if ($elapsed_time < $lockout_duration) {
                $remaining = $lockout_duration - $elapsed_time;
                return [
                    'success' => false,
                    'message' => "Too many failed attempts. Please try again in $remaining seconds.",
                    'is_locked' => true,
                    'remaining_time' => $remaining
                ];
            } else {
                // Lockout period has passed - Reset cycle attempts
                $_SESSION['failed_in_cycle'] = 0;
            }
        }

        // Bypass OTP for Admin account (case-insensitive check)
        if (strtolower($email) === 'lgu@admin.com') {
            if ($this->sessionManager->authenticate($email, $password)) {
                // Reset attempts on success
                $_SESSION['login_attempts'] = 0;
                $_SESSION['failed_in_cycle'] = 0;
                $_SESSION['lockout_count'] = 0;
                $_SESSION['last_attempt_time'] = null;
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => '../public/dashboard.php'
                ];
            }
        } else {
            // Standard User - Verify credentials first
            $user = $this->sessionManager->verifyCredentials($email, $password);
            if ($user) {
                // Reset attempts on successful internal credential check
                $_SESSION['login_attempts'] = 0;
                $_SESSION['failed_in_cycle'] = 0;
                $_SESSION['lockout_count'] = 0;
                $_SESSION['last_attempt_time'] = null;

                // Generate 6-digit OTP
                $otp = sprintf("%06d", mt_rand(0, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Save OTP to database
                $query = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE user_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ssi", $otp, $expiry, $user['user_id']);

                if ($stmt->execute()) {
                    // Send OTP Email
                    if (sendOTPEmail($user['email'], $user['first_name'], $otp)) {
                        // Use SessionManager to set pre-auth state
                        $this->sessionManager->setPreAuthSession($user);
                        $_SESSION['otp_email_masked'] = $this->maskEmail($user['email']);

                        return [
                            'success' => true,
                            'requires_otp' => true,
                            'message' => 'A verification code has been sent to your email.',
                            'masked_email' => $_SESSION['otp_email_masked']
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Failed to send verification code. Please try again.'
                        ];
                    }
                }
            }
        }

        // Increment failed attempts and track time
        $_SESSION['login_attempts']++;
        $_SESSION['failed_in_cycle']++;
        $_SESSION['last_attempt_time'] = time();

        $is_now_locked = ($_SESSION['failed_in_cycle'] >= 3);

        // If we just hit the 3rd fail in this cycle, increment lockout_count
        if ($is_now_locked) {
            $_SESSION['lockout_count'] = ($_SESSION['lockout_count'] ?? 0) + 1;
        }

        $lockout_next = $this->getLockoutDuration($_SESSION['lockout_count'] ?? 0);

        $msg = 'Invalid email or password.';
        if ($is_now_locked) {
            $msg .= " Account locked for $lockout_next seconds.";
        }

        return [
            'success' => false,
            'message' => $msg,
            'attempts' => $_SESSION['login_attempts'] ?? 0,
            'is_locked' => $is_now_locked,
            'remaining_time' => $is_now_locked ? $lockout_next : 0
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
     * Verify OTP and finalize login
     */
    public function verifyOTP($otp)
    {
        if (!isset($_SESSION['is_otp_pending']) || !isset($_SESSION['otp_user_id'])) {
            return [
                'success' => false,
                'message' => 'Session expired. Please try logging in again.'
            ];
        }

        $user_id = $_SESSION['otp_user_id'];
        $otp = trim($otp);

        $query = "SELECT otp_code, otp_expiry FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if OTP matches and is not expired
            if ($user['otp_code'] === $otp && strtotime($user['otp_expiry']) > time()) {
                // Clear OTP from DB
                $clear_query = "UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE user_id = ?";
                $clear_stmt = $this->conn->prepare($clear_query);
                // Finalize login using SessionManager
                if ($this->sessionManager->completeAuthentication($user_id)) {
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['failed_in_cycle'] = 0;
                    $_SESSION['lockout_count'] = 0;
                    $_SESSION['last_attempt_time'] = null;

                    return [
                        'success' => true,
                        'message' => 'Verification successful',
                        'redirect' => '../public/dashboard.php'
                    ];
                }
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid or expired verification code'
        ];
    }

    /**
     * Resend OTP code
     */
    public function resendOTP()
    {
        if (!isset($_SESSION['is_otp_pending']) || !isset($_SESSION['otp_user_id'])) {
            return [
                'success' => false,
                'message' => 'Session expired'
            ];
        }

        $user_id = $_SESSION['otp_user_id'];

        $query = "SELECT first_name, email FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $update_query = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE user_id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bind_param("ssi", $otp, $expiry, $user_id);

            if ($update_stmt->execute()) {
                if (sendOTPEmail($user['email'], $user['first_name'], $otp)) {
                    return [
                        'success' => true,
                        'message' => 'A new code has been sent to your email.'
                    ];
                }
            }
        }

        return [
            'success' => false,
            'message' => 'Failed to resend code'
        ];
    }

    /**
     * Get progressive lockout duration in seconds
     */
    private function getLockoutDuration($lockouts)
    {
        if ($lockouts <= 0)
            return 0;

        switch ($lockouts) {
            case 1:
                return 10;   // 10 seconds
            case 2:
                return 30;   // 30 seconds
            case 3:
                return 300;  // 5 minutes
            case 4:
                return 900;  // 15 minutes
            default:
                return 1800; // 30 minutes
        }
    }

    /**
     * Mask email for display (e.g., u***@example.com)
     */
    private function maskEmail($email)
    {
        $parts = explode("@", $email);
        $name = $parts[0];
        $domain = $parts[1];
        $masked_name = substr($name, 0, 1) . str_repeat("*", max(0, strlen($name) - 2)) . substr($name, -1);
        return $masked_name . "@" . $domain;
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
    }
}

?>