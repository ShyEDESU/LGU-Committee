<?php
/**
 * Registration Controller - Handle user registration for government employees
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../../config/database.php');

class RegistrationController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Handle registration request
     */
    public function register($email, $password, $confirm_password, $first_name, $last_name, $department, $position, $employee_id) {
        // Sanitize input
        $email = htmlspecialchars(trim($email));
        $first_name = htmlspecialchars(trim($first_name));
        $last_name = htmlspecialchars(trim($last_name));
        $department = htmlspecialchars(trim($department));
        $position = htmlspecialchars(trim($position));
        $employee_id = htmlspecialchars(trim($employee_id));
        $password = trim($password);
        $confirm_password = trim($confirm_password);
        
        // Validate input
        if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || 
            empty($department) || empty($position) || empty($employee_id)) {
            return [
                'success' => false,
                'message' => 'All fields are required'
            ];
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }
        
        // Validate password match
        if ($password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'Passwords do not match'
            ];
        }
        
        // Validate password strength
        if (!$this->validatePasswordStrength($password)) {
            return [
                'success' => false,
                'message' => 'Password does not meet security requirements'
            ];
        }
        
        // Check if email already exists
        if ($this->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email address already registered'
            ];
        }
        
        // Check if employee ID already exists
        if ($this->employeeIdExists($employee_id)) {
            return [
                'success' => false,
                'message' => 'Employee ID already registered'
            ];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Register user (with email role - role_id 5 by default)
        // User must be approved by administrator before accessing system
        $query = "INSERT INTO users (email, password_hash, first_name, last_name, role_id, department, position, employee_id, email_verified, verification_token, verification_token_expires, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->conn->error
            ];
        }
        
        $role_id = 5; // Email User role (to be approved)
        $is_active = 0; // Not active until verified
        $email_verified = 0; // Not verified until email confirmation
        
        $stmt->bind_param("ssssssssisis", 
            $email, 
            $password_hash, 
            $first_name, 
            $last_name, 
            $role_id, 
            $department, 
            $position, 
            $employee_id, 
            $email_verified, 
            $verification_token, 
            $verification_expires, 
            $is_active
        );
        
        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            
            // Send verification email
            $this->sendVerificationEmail($email, $first_name, $verification_token);
            
            // Log registration
            $this->logAuditAction($user_id, 'ACCOUNT_CREATED', 'Registration', 'New account registered: ' . $email);
            
            return [
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'email' => $email
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again later.'
        ];
    }
    
    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // At least one special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if email already exists
     */
    private function emailExists($email) {
        $query = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Check if employee ID already exists
     */
    private function employeeIdExists($employee_id) {
        $query = "SELECT user_id FROM users WHERE employee_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Send verification email
     */
    private function sendVerificationEmail($email, $first_name, $token) {
        // Email configuration
        $verification_link = "http://localhost/capstone/verify_email.php?token=" . $token;
        
        $to = $email;
        $subject = "Verify Your Email - Legislative Services Management System";
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4;'>
                <div style='background-color: white; margin: 20px auto; padding: 20px; max-width: 600px; border-radius: 8px;'>
                    <h1 style='color: #2c3e50;'>Welcome to Legislative Services Management System</h1>
                    
                    <p>Dear $first_name,</p>
                    
                    <p>Thank you for registering with our system. Please verify your email address by clicking the link below:</p>
                    
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$verification_link' style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px;'>
                            Verify Email Address
                        </a>
                    </p>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='word-break: break-all; color: #666;'>$verification_link</p>
                    
                    <p>This verification link will expire in 24 hours.</p>
                    
                    <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                    
                    <p><strong>Important:</strong> Your account will not be active until:</p>
                    <ol>
                        <li>You verify your email address</li>
                        <li>An administrator reviews and approves your registration</li>
                    </ol>
                    
                    <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                    
                    <p style='color: #999; font-size: 12px;'>
                        If you did not register for this account, please ignore this email.
                    </p>
                </div>
            </body>
        </html>
        ";
        
        // Headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@legislative-services.gov\r\n";
        
        // Send email
        // Note: In production, configure proper SMTP settings
        // For now, this is a placeholder that would be configured in the deployment environment
        // mail($to, $subject, $message, $headers);
    }
    
    /**
     * Log audit action
     */
    private function logAuditAction($user_id, $action, $module, $description) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $query = "INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $action, $module, $description, $ip_address);
        return $stmt->execute();
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration = new RegistrationController();
    
    $result = $registration->register(
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirm_password'] ?? '',
        $_POST['first_name'] ?? '',
        $_POST['last_name'] ?? '',
        $_POST['department'] ?? '',
        $_POST['position'] ?? '',
        $_POST['employee_id'] ?? ''
    );
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Handle GET request
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit;
?>
