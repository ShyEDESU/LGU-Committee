<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legislative Services Committee Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 1rem;
        }
        
        .login-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            animation: slideUp 0.5s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #999;
            font-size: 0.95rem;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .login-form .form-group {
            margin-bottom: 0;
        }
        
        .login-form .form-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .login-form .form-control {
            padding: 0.9rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .login-form .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .login-form .form-control::placeholder {
            color: #bbb;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .remember-forgot a {
            color: #3498db;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .remember-forgot a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
            font-size: 0.9rem;
            color: #999;
        }
        
        .demo-credentials {
            background: #ecf0f1;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            text-align: center;
        }
        
        .demo-credentials strong {
            color: #2c3e50;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #999;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 1rem;
            font-size: 0.85rem;
        }
        
        .oauth-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .oauth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #2c3e50;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 44px;
        }
        
        .oauth-btn:hover {
            border-color: #3498db;
            background: #f9f9f9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }
        
        .oauth-btn i {
            font-size: 1.3rem;
        }
        
        .oauth-btn span {
            display: none;
        }
        
        /* Show text on larger screens */
        @media (min-width: 500px) {
            .oauth-btn span {
                display: inline;
            }
        }
        
        .oauth-btn.google {
            border-color: #ea4335;
            color: #ea4335;
        }
        
        .oauth-btn.google:hover {
            background: #fafafa;
            border-color: #ea4335;
            box-shadow: 0 4px 12px rgba(234, 67, 53, 0.15);
        }
        
        .oauth-btn.microsoft {
            border-color: #00a4ef;
            color: #00a4ef;
        }
        
        .oauth-btn.microsoft:hover {
            background: #fafafa;
            border-color: #00a4ef;
            box-shadow: 0 4px 12px rgba(0, 164, 239, 0.15);
        }
        
        .forgot-password-link {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .forgot-password-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .forgot-password-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1>Legislative Services</h1>
                <p>Committee Management System</p>
            </div>
            
            <div class="demo-credentials">
                <strong>Demo Credentials:</strong><br>
                Email: <code>LGU@admin.com</code><br>
                Password: <code>admin123</code>
            </div>
            
            <form class="login-form" id="loginForm" data-ajax="true" action="../app/controllers/AuthController.php">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        type="email" 
                        placeholder="Enter your email address"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <div class="forgot-password-link">
                    <a href="reset_password.php">
                        <i class="fas fa-question-circle"></i> Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <!-- OAuth / Social Login -->
            <div class="divider">
                <span>Or continue with</span>
            </div>
            
            <div class="oauth-buttons">
                <button type="button" class="oauth-btn google" id="googleLoginBtn">
                    <i class="fab fa-google"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="oauth-btn microsoft" id="microsoftLoginBtn">
                    <i class="fab fa-microsoft"></i>
                    <span>Microsoft</span>
                </button>
            </div>
            
            <div class="login-footer">
                <p>© 2025 Legislative Services Committee Management System</p>
                <p style="margin-top: 1rem;">
                    Don't have an account? <a href="register.php" style="color: #3498db; font-weight: 600;">Register here</a>
                </p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    By logging in, you agree to our <a href="terms.php" style="color: #3498db;">Terms & Conditions</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="../public/assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Google OAuth Login
        document.getElementById('googleLoginBtn').addEventListener('click', function() {
            const googleClientId = ''; // Will be set in production
            const redirectUri = window.location.origin + '/app/controllers/OAuthController.php?provider=google';
            
            if (!googleClientId) {
                AlertManager.warning('Google Sign-In not yet configured. Please use email login or register.');
                return;
            }
            
            const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${googleClientId}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=code&scope=email%20profile`;
            window.location.href = authUrl;
        });
        
        // Microsoft OAuth Login
        document.getElementById('microsoftLoginBtn').addEventListener('click', function() {
            const microsoftClientId = ''; // Will be set in production
            const redirectUri = window.location.origin + '/app/controllers/OAuthController.php?provider=microsoft';
            
            if (!microsoftClientId) {
                AlertManager.warning('Microsoft Sign-In not yet configured. Please use email login or register.');
                return;
            }
            
            const authUrl = `https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=${microsoftClientId}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=code&scope=email%20profile%20openid`;
            window.location.href = authUrl;
        });
    </script>
</body>
</html>
