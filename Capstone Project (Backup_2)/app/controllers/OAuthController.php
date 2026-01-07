<?php
/**
 * OAuth Controller - Handle OAuth authentication from Google, Microsoft, etc.
 * 
 * @package Legislative Services Committee Management System
 * @version 1.0
 */

require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../middleware/SessionManager.php');

class OAuthController {
    private $conn;
    private $sessionManager;
    
    // OAuth Configuration (set in production environment)
    private $google_client_id = '';
    private $google_client_secret = '';
    private $microsoft_client_id = '';
    private $microsoft_client_secret = '';
    private $redirect_uri = '';
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->sessionManager = new SessionManager($conn);
        $this->redirect_uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/app/controllers/OAuthController.php';
    }
    
    /**
     * Handle OAuth callback and authenticate user
     */
    public function handleCallback($provider, $code) {
        if ($provider === 'google') {
            return $this->handleGoogleCallback($code);
        } elseif ($provider === 'microsoft') {
            return $this->handleMicrosoftCallback($code);
        }
        
        return [
            'success' => false,
            'message' => 'Unknown OAuth provider'
        ];
    }
    
    /**
     * Handle Google OAuth callback
     */
    private function handleGoogleCallback($code) {
        if (empty($this->google_client_id) || empty($this->google_client_secret)) {
            return [
                'success' => false,
                'message' => 'Google OAuth not configured',
                'redirect' => '/login.php'
            ];
        }
        
        try {
            // Exchange code for token
            $tokenUrl = 'https://oauth2.googleapis.com/token';
            $postData = [
                'code' => $code,
                'client_id' => $this->google_client_id,
                'client_secret' => $this->google_client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            ];
            
            $response = $this->makePostRequest($tokenUrl, $postData);
            $tokens = json_decode($response, true);
            
            if (!isset($tokens['access_token'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token from Google',
                    'redirect' => '/login.php'
                ];
            }
            
            // Get user info
            $userInfo = $this->getGoogleUserInfo($tokens['access_token']);
            
            if (!$userInfo) {
                return [
                    'success' => false,
                    'message' => 'Failed to get user info from Google',
                    'redirect' => '/login.php'
                ];
            }
            
            return $this->authenticateOrRegisterUser($userInfo, 'google');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Google authentication error: ' . $e->getMessage(),
                'redirect' => '/login.php'
            ];
        }
    }
    
    /**
     * Handle Microsoft OAuth callback
     */
    private function handleMicrosoftCallback($code) {
        if (empty($this->microsoft_client_id) || empty($this->microsoft_client_secret)) {
            return [
                'success' => false,
                'message' => 'Microsoft OAuth not configured',
                'redirect' => '/login.php'
            ];
        }
        
        try {
            // Exchange code for token
            $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
            $postData = [
                'code' => $code,
                'client_id' => $this->microsoft_client_id,
                'client_secret' => $this->microsoft_client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code',
                'scope' => 'email profile openid'
            ];
            
            $response = $this->makePostRequest($tokenUrl, $postData);
            $tokens = json_decode($response, true);
            
            if (!isset($tokens['access_token'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token from Microsoft',
                    'redirect' => '/login.php'
                ];
            }
            
            // Get user info
            $userInfo = $this->getMicrosoftUserInfo($tokens['access_token']);
            
            if (!$userInfo) {
                return [
                    'success' => false,
                    'message' => 'Failed to get user info from Microsoft',
                    'redirect' => '/login.php'
                ];
            }
            
            return $this->authenticateOrRegisterUser($userInfo, 'microsoft');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Microsoft authentication error: ' . $e->getMessage(),
                'redirect' => '/login.php'
            ];
        }
    }
    
    /**
     * Get Google user info
     */
    private function getGoogleUserInfo($accessToken) {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;
        $response = file_get_contents($userInfoUrl);
        
        if ($response === false) {
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Microsoft user info
     */
    private function getMicrosoftUserInfo($accessToken) {
        $userInfoUrl = 'https://graph.microsoft.com/v1.0/me';
        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer $accessToken",
                'method' => 'GET'
            ]
        ]);
        
        $response = file_get_contents($userInfoUrl, false, $context);
        
        if ($response === false) {
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Make POST request to OAuth provider
     */
    private function makePostRequest($url, $data) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            ]
        ]);
        
        return file_get_contents($url, false, $context);
    }
    
    /**
     * Authenticate existing user or redirect to registration
     */
    private function authenticateOrRegisterUser($userInfo, $provider) {
        $email = null;
        $firstName = null;
        $lastName = null;
        $oauthId = null;
        
        // Extract data based on provider
        if ($provider === 'google') {
            $email = $userInfo['email'] ?? null;
            $firstName = $userInfo['given_name'] ?? 'User';
            $lastName = $userInfo['family_name'] ?? '';
            $oauthId = $userInfo['id'] ?? null;
        } elseif ($provider === 'microsoft') {
            $email = $userInfo['userPrincipalName'] ?? ($userInfo['mail'] ?? null);
            $firstName = $userInfo['givenName'] ?? 'User';
            $lastName = $userInfo['surname'] ?? '';
            $oauthId = $userInfo['id'] ?? null;
        }
        
        if (!$email || !$oauthId) {
            return [
                'success' => false,
                'message' => 'Could not retrieve email from ' . ucfirst($provider),
                'redirect' => '/login.php'
            ];
        }
        
        // Check if user exists
        $query = "SELECT user_id, email, is_active, email_verified FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User exists - authenticate them
            $user = $result->fetch_assoc();
            
            if (!$user['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact an administrator.',
                    'redirect' => '/login.php'
                ];
            }
            
            // Log in the user
            $this->sessionManager->authenticateByUserId($user['user_id']);
            $this->logAuditAction($user['user_id'], 'OAUTH_LOGIN', 'Authentication', "User logged in via " . ucfirst($provider));
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => '/public/dashboard.php'
            ];
        } else {
            // User doesn't exist - redirect to registration with pre-filled email
            // Store OAuth info in session temporarily
            session_start();
            $_SESSION['oauth_registration'] = [
                'provider' => $provider,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'oauth_id' => $oauthId
            ];
            
            return [
                'success' => false,
                'message' => 'No account found. Redirecting to registration...',
                'redirect' => '/register.php?oauth=' . $provider . '&email=' . urlencode($email)
            ];
        }
    }
    
    /**
     * Authenticate user by user ID (for OAuth)
     */
    private function authenticateByUserId($userId) {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.role_id, r.role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.role_id 
                  WHERE u.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['login_time'] = time();
            
            return true;
        }
        
        return false;
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

// Handle OAuth callback
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['provider']) && isset($_GET['code'])) {
    $provider = sanitize($_GET['provider']);
    $code = sanitize($_GET['code']);
    
    $oauth = new OAuthController();
    $result = $oauth->handleCallback($provider, $code);
    
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
    } else {
        // Store error in session and redirect
        session_start();
        $_SESSION['error'] = $result['message'];
        header('Location: ' . $result['redirect']);
    }
    exit;
}

// Helper function
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Handle direct access
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['code'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid OAuth request']);
    exit;
}
?>
