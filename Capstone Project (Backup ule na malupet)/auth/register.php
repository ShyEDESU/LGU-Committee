<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Legislative Services Committee Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 1rem 1rem 2rem 1rem;
        }
        
        .register-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 550px;
            padding: 2rem;
            animation: slideUp 0.5s ease;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-logo {
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
        
        .register-header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #999;
            font-size: 0.95rem;
        }
        
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .register-form .form-group {
            margin-bottom: 0;
        }
        
        .register-form .form-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .register-form .form-control {
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .register-form .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .register-form .form-control::placeholder {
            color: #bbb;
        }
        
        .password-requirements {
            background: #f0f7ff;
            border-left: 4px solid #3498db;
            padding: 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #555;
            margin-top: -1rem;
        }
        
        .password-requirements ul {
            margin: 0.5rem 0 0 1.5rem;
            padding: 0;
        }
        
        .password-requirements li {
            margin: 0.3rem 0;
        }
        
        .password-requirements .valid {
            color: #27ae60;
        }
        
        .password-requirements .invalid {
            color: #e74c3c;
        }
        
        .terms-checkbox {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #555;
        }
        
        .terms-checkbox input[type="checkbox"] {
            margin-top: 3px;
            cursor: pointer;
        }
        
        .terms-checkbox a {
            color: #3498db;
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
        }
        
        .register-btn {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
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
        
        .register-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.3);
        }
        
        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
            font-size: 0.9rem;
            color: #999;
        }
        
        .register-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .form-helper-text {
            font-size: 0.8rem;
            color: #999;
            margin-top: 0.3rem;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 0.3rem;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <div class="register-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1>Create Your Account</h1>
                <p>Legislative Services Management System</p>
            </div>
            
            <form class="register-form" id="registerForm" data-ajax="true">
                <input type="hidden" name="action" value="register">
                
                <!-- Full Name Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">
                            <i class="fas fa-user"></i> First Name
                        </label>
                        <input 
                            class="form-control" 
                            id="first_name" 
                            name="first_name" 
                            type="text" 
                            placeholder="First name"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="last_name">
                            <i class="fas fa-user"></i> Last Name
                        </label>
                        <input 
                            class="form-control" 
                            id="last_name" 
                            name="last_name" 
                            type="text" 
                            placeholder="Last name"
                            required
                        >
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        type="email" 
                        placeholder="your.email@lgu.gov"
                        required
                    >
                    <p class="form-helper-text">Must be a valid government email address</p>
                </div>
                
                <!-- Government Position Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="department">
                            <i class="fas fa-building"></i> Department
                        </label>
                        <input 
                            class="form-control" 
                            id="department" 
                            name="department" 
                            type="text" 
                            placeholder="e.g., Administrative Services"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="position">
                            <i class="fas fa-briefcase"></i> Position
                        </label>
                        <input 
                            class="form-control" 
                            id="position" 
                            name="position" 
                            type="text" 
                            placeholder="e.g., Administrator"
                            required
                        >
                    </div>
                </div>
                
                <!-- Employee ID -->
                <div class="form-group">
                    <label class="form-label" for="employee_id">
                        <i class="fas fa-id-card"></i> Employee ID
                    </label>
                    <input 
                        class="form-control" 
                        id="employee_id" 
                        name="employee_id" 
                        type="text" 
                        placeholder="Your government employee ID"
                        required
                    >
                    <p class="form-helper-text">This will be verified by administrators</p>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Create a strong password"
                        required
                    >
                    <div class="password-requirements">
                        <strong>Password must contain:</strong>
                        <ul>
                            <li id="req-length"><i class="fas fa-times-circle invalid"></i> At least 8 characters</li>
                            <li id="req-upper"><i class="fas fa-times-circle invalid"></i> At least one uppercase letter (A-Z)</li>
                            <li id="req-lower"><i class="fas fa-times-circle invalid"></i> At least one lowercase letter (a-z)</li>
                            <li id="req-number"><i class="fas fa-times-circle invalid"></i> At least one number (0-9)</li>
                            <li id="req-special"><i class="fas fa-times-circle invalid"></i> At least one special character (!@#$%)</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <input 
                        class="form-control" 
                        id="confirm_password" 
                        name="confirm_password" 
                        type="password" 
                        placeholder="Re-enter your password"
                        required
                    >
                    <p class="error-message" id="password-match-error">Passwords do not match</p>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="terms-checkbox">
                    <input 
                        type="checkbox" 
                        id="accept_terms" 
                        name="accept_terms" 
                        required
                    >
                    <label for="accept_terms">
                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and <a href="terms.php#privacy" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="register-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    Your registration will be reviewed by an administrator before activation
                </p>
            </div>
        </div>
    </div>
    
    <script src="../public/assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Password validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatchError = document.getElementById('password-match-error');
        
        const requirements = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };
        
        function validatePassword(password) {
            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            for (let key in requirements) {
                const req = requirements[key];
                const isValid = checks[key];
                if (isValid) {
                    req.innerHTML = '<i class="fas fa-check-circle valid"></i> ' + req.textContent.substring(1);
                    req.classList.add('valid');
                } else {
                    req.innerHTML = '<i class="fas fa-times-circle invalid"></i> ' + req.textContent.substring(1);
                    req.classList.remove('valid');
                }
            }
            
            return Object.values(checks).every(check => check);
        }
        
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            checkPasswordMatch();
        });
        
        function checkPasswordMatch() {
            if (confirmPasswordInput.value) {
                if (passwordInput.value === confirmPasswordInput.value) {
                    passwordMatchError.classList.remove('show');
                } else {
                    passwordMatchError.classList.add('show');
                }
            }
        }
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Final validation
            if (!validatePassword(passwordInput.value)) {
                AlertManager.danger('Password does not meet all requirements');
                return;
            }
            
            if (passwordInput.value !== confirmPasswordInput.value) {
                AlertManager.danger('Passwords do not match');
                return;
            }
            
            if (!document.getElementById('accept_terms').checked) {
                AlertManager.danger('You must accept the Terms & Conditions');
                return;
            }
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span> Creating account...';
            
            fetch('../app/controllers/RegistrationController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                button.innerHTML = originalText;
                
                if (data.success) {
                    AlertManager.success('Account created successfully! Redirecting to login...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    AlertManager.danger(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = originalText;
                AlertManager.danger('An error occurred during registration');
            });
        });
        
        // Handle OAuth pre-fill
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const oauthEmail = params.get('email');
            const oauthProvider = params.get('oauth');
            
            if (oauthEmail) {
                const emailField = document.getElementById('email');
                if (emailField) {
                    emailField.value = oauthEmail;
                    emailField.disabled = true; // Lock email field since it comes from OAuth
                    
                    if (oauthProvider) {
                        const message = document.createElement('p');
                        message.style.fontSize = '0.85rem';
                        message.style.color = '#27ae60';
                        message.style.marginTop = '0.3rem';
                        message.innerHTML = '<i class="fas fa-check-circle"></i> Email pre-filled from ' + oauthProvider.charAt(0).toUpperCase() + oauthProvider.slice(1);
                        emailField.parentElement.appendChild(message);
                    }
                }
            }
            
            // Check for session data
            fetch('../app/controllers/RegistrationController.php?action=getOAuthData')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.oauth_data) {
                        const oauth = data.oauth_data;
                        if (oauth.first_name) {
                            document.getElementById('first_name').value = oauth.first_name;
                        }
                        if (oauth.last_name) {
                            document.getElementById('last_name').value = oauth.last_name;
                        }
                    }
                })
                .catch(error => console.log('OAuth data check completed'));
        });
    </script>
</body>
</html>
