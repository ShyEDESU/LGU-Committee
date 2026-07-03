<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Legislative Services Committee Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../public/assets/images/logo.png">
    <link rel="apple-touch-icon" href="../public/assets/images/logo.png">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .help-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 2rem 1rem;
        }
        
        .help-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
            animation: slideUp 0.5s ease;
        }
        
        .help-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .help-header h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .help-header p {
            color: #999;
            font-size: 1rem;
        }
        
        .help-section {
            margin-bottom: 2rem;
        }
        
        .help-section h2 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            border-left: 3px solid #dc2626;
            padding-left: 1rem;
        }
        
        .faq-item {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f5f5f5;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }
        
        .faq-question {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .faq-question:hover {
            color: #dc2626;
        }
        
        .faq-answer {
            color: #555;
            line-height: 1.6;
            margin-top: 0.5rem;
        }
        
        .help-section p, .help-section li {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .help-section ul {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }
        
        .help-section ul li {
            margin-bottom: 0.5rem;
        }
        
        .contact-box {
            background-color: #f0f7ff;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .contact-box h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .contact-item i {
            color: #dc2626;
            font-size: 1.2rem;
            margin-top: 0.25rem;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #b91c1c;
            text-decoration: underline;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); min-height: 100vh; padding: 2rem 1rem;">
    <div class="help-container">
        <div class="help-content">
            <a href="login.php" class="back-link"><i class="fas fa-arrow-left mr-2"></i>Back to Login</a>
            
            <div class="help-header">
                <h1><i class="fas fa-question-circle mr-2"></i>Help & Support</h1>
                <p>Get Answers to Common Questions</p>
            </div>
            
            <div class="help-section">
                <h2>Getting Started</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        How do I create an account?
                    </div>
                    <div class="faq-answer">
                        New user accounts are created by system administrators. If you need an account, contact your system administrator or submit a request through the help desk.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        How do I reset my password?
                    </div>
                    <div class="faq-answer">
                        On the login page, click "Forgot Password?" and follow the instructions sent to your registered email address. If you don't receive the email, check your spam folder or contact support.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        What are the system requirements?
                    </div>
                    <div class="faq-answer">
                        The System works on any modern web browser including Chrome, Firefox, Safari, and Edge. We recommend using the latest version of your browser for the best experience. JavaScript must be enabled.
                    </div>
                </div>
            </div>
            
            <div class="help-section">
                <h2>Account Security</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        Why is my account locked after multiple failed login attempts?
                    </div>
                    <div class="faq-answer">
                        The System locks accounts after 5 failed login attempts for security purposes. This prevents unauthorized access through brute force attacks. The account will automatically unlock after 15 minutes.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        What should I do if I suspect my account has been compromised?
                    </div>
                    <div class="faq-answer">
                        Immediately log out of the System and contact your system administrator. Change your password once your account has been secured. Do not share your credentials with anyone.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        How often should I change my password?
                    </div>
                    <div class="faq-answer">
                        We recommend changing your password every 60-90 days or immediately if you suspect compromise. Use a strong password with a combination of uppercase, lowercase, numbers, and special characters.
                    </div>
                </div>
            </div>
            
            <div class="help-section">
                <h2>Troubleshooting</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        I'm having trouble logging in. What should I do?
                    </div>
                    <div class="faq-answer">
                        <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                            <li>Verify your email and password are correct</li>
                            <li>Check that Caps Lock is not enabled</li>
                            <li>Clear your browser cookies and cache</li>
                            <li>Try a different browser</li>
                            <li>If your account is locked, wait 15 minutes before trying again</li>
                            <li>Contact support if the problem persists</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        The page is loading very slowly. What can I do?
                    </div>
                    <div class="faq-answer">
                        <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                            <li>Check your internet connection speed</li>
                            <li>Close unnecessary browser tabs and applications</li>
                            <li>Clear your browser cache</li>
                            <li>Disable browser extensions</li>
                            <li>Try accessing from a different network</li>
                            <li>Contact your IT department if the issue persists</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-right"></i>
                        I'm receiving error messages. What does this mean?
                    </div>
                    <div class="faq-answer">
                        Error messages provide helpful information about what went wrong. Read the message carefully and follow the suggested steps. If you receive an unusual error, note the error code and contact support with this information.
                    </div>
                </div>
            </div>
            
            <div class="help-section">
                <h2>Need Additional Help?</h2>
                <p>If your question is not answered above, please contact our support team:</p>
                
                <div class="contact-box">
                    <h3>Contact Support</h3>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email:</strong> support@lgu-valenzuela.gov.ph
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Phone:</strong> +63 (2) XXXX-XXXX
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Address:</strong> City Government of Valenzuela, Valenzuela City, Philippines
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM (Philippine Time)
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="help-section">
                <h2>Related Pages</h2>
                <ul>
                    <li><a href="terms.php" target="_blank" style="color: #dc2626; text-decoration: none;">Terms & Conditions</a></li>
                    <li><a href="privacy.php" target="_blank" style="color: #dc2626; text-decoration: none;">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
