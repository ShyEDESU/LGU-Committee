<?php
session_start();

// Initialize login attempts tracking
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = null;
}

// Check if account is locked
$is_locked = false;
$remaining_time = 0;

if ($_SESSION['login_attempts'] >= 5) {
    if ($_SESSION['first_attempt_time'] === null) {
        $_SESSION['first_attempt_time'] = time();
    }
    
    $elapsed_time = time() - $_SESSION['first_attempt_time'];
    $lockout_duration = 15 * 60; // 15 minutes
    
    if ($elapsed_time < $lockout_duration) {
        $is_locked = true;
        $remaining_time = $lockout_duration - $elapsed_time;
    } else {
        // Reset after lockout period
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_attempt_time'] = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Management System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cms-red': '#dc2626',
                        'cms-dark': '#b91c1c',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-in': 'slideIn 0.3s ease-in',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-box {
            animation: slideUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .security-alert {
            animation: slideDown 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center p-4">
    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-gray-900 opacity-5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <!-- Login Card -->
        <div class="login-box bg-white rounded-2xl shadow-2xl p-8 md:p-12 border-t-4 border-cms-red">
            <!-- Header -->
            <div class="text-center mb-8">
                <!-- Logo -->
                <div class="flex justify-center mb-4">
                    <div class="w-24 h-24 bg-gradient-to-br from-cms-red to-cms-dark rounded-2xl shadow-lg flex items-center justify-center hover:-translate-y-1 transition-transform duration-300">
                        <img src="../public/assets/images/logo.png" alt="System Logo" class="w-full h-full object-contain rounded-2xl" />
                    </div>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Committee Management</h1>
                <p class="text-gray-600 font-medium mb-1">Legislative Services</p>
                <p class="text-cms-red font-semibold text-sm">City Government of Valenzuela</p>
            </div>

            <!-- Security Alert - Shows when account is locked -->
            <?php if ($is_locked): ?>
            <div class="security-alert bg-red-50 border-l-4 border-cms-red rounded-lg p-4 mb-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-lock text-cms-red text-xl mt-1 mr-3 flex-shrink-0 animate-pulse"></i>
                    <div class="flex-1">
                        <h3 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                            <i class="fas fa-shield-alt text-red-600"></i>
                            Account Temporarily Locked
                        </h3>
                        <p class="text-red-800 text-sm mb-3">Too many failed login attempts detected. For security, your account has been locked.</p>
                        <div class="bg-white bg-opacity-50 rounded-lg p-3 border border-red-200">
                            <p class="text-red-900 font-bold text-center text-2xl mb-1" id="lockoutTimer"><?php echo str_pad(floor($remaining_time / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($remaining_time % 60, 2, '0', STR_PAD_LEFT); ?></p>
                            <p class="text-red-700 text-xs text-center font-semibold">Time remaining</p>
                        </div>
                        <p class="text-red-700 text-xs mt-3 mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Please wait for the timer to expire before attempting to log in again.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Demo Credentials Box -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-900 font-semibold text-sm mb-3">
                    <i class="fas fa-info-circle mr-2 text-green-600"></i> Demo Credentials
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Email:</span>
                        <code class="bg-white px-3 py-1 rounded border border-green-300 text-cms-red font-semibold font-mono">LGU@admin.com</code>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Password:</span>
                        <code class="bg-white px-3 py-1 rounded border border-green-300 text-cms-red font-semibold font-mono">admin123</code>
                    </div>
                </div>
            </div>
            
            <!-- Logout Success Notification -->
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div id="logoutAlert" class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6 animate-fade-in transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1">
                        <i class="fas fa-check-circle text-green-600 text-xl mt-1 mr-3 flex-shrink-0"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold text-green-900 mb-1">Logged Out Successfully</h3>
                            <p class="text-green-800 text-sm">You have been successfully logged out. See you next time!</p>
                        </div>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                        <button type="button" onclick="dismissLogoutAlert()" class="text-green-600 hover:text-green-900 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-3 h-1 bg-green-200 rounded overflow-hidden">
                    <div id="logoutProgressBar" class="h-full bg-green-500 transition-all" style="width: 100%;"></div>
                </div>
                <p class="text-green-700 text-xs mt-2 text-center font-semibold">Closing in <span id="logoutTimer">5</span> seconds...</p>
            </div>
            <?php endif; ?>

            <!-- Error Notification -->
            <div id="errorAlert" class="hidden bg-red-50 border-l-4 border-cms-red rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-cms-red text-xl mt-1 mr-3 flex-shrink-0"></i>
                    <div class="flex-1">
                        <h3 class="font-semibold text-red-900 mb-1">Login Failed</h3>
                        <p class="text-red-800 text-sm" id="errorMessage"></p>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <form class="login-form" id="loginForm" <?php echo $is_locked ? 'style="display: none;"' : ''; ?>>
                <input type="hidden" name="action" value="login">
                
                <!-- Email Field -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide" for="email">
                        <i class="fas fa-envelope text-cms-red mr-2"></i> Email Address
                    </label>
                    <input 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:border-cms-red focus:bg-white focus:ring-4 focus:ring-red-100 transition-all duration-300" 
                        id="email" 
                        name="email" 
                        type="email" 
                        placeholder="Enter your email address"
                        required
                        autofocus
                    >
                </div>
                
                <!-- Password Field -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide" for="password">
                        <i class="fas fa-lock text-cms-red mr-2"></i> Password
                    </label>
                    <input 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:border-cms-red focus:bg-white focus:ring-4 focus:ring-red-100 transition-all duration-300" 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <!-- Forgot Password Link -->
                <div class="text-right mb-6">
                    <a href="reset_password.php" class="text-cms-red hover:text-cms-dark font-semibold text-sm transition-colors">
                        <i class="fas fa-question-circle mr-1"></i> Forgot Password?
                    </a>
                </div>
                
                <!-- Sign In Button -->
                <button type="submit" class="login-btn w-full bg-gradient-to-r from-cms-red to-cms-dark text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300 uppercase tracking-wide mb-4 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
                
                <!-- Terms & Conditions Checkbox -->
                <div class="flex items-start mb-4">
                    <input type="checkbox" id="termsCheckbox" class="w-4 h-4 mt-1 mr-2 text-cms-red bg-gray-100 border-gray-300 rounded focus:ring-cms-red cursor-pointer" required>
                    <label for="termsCheckbox" class="text-gray-700 text-sm">
                        I agree to the 
                        <button type="button" onclick="openTermsModal()" class="text-cms-red hover:text-cms-dark font-semibold underline">Terms & Conditions</button>
                    </label>
                </div>
            </form>
            
            <!-- OAuth / Social Login -->
            <?php if (!$is_locked): ?>
            <div class="relative flex items-center my-6">
                <div class="flex-grow border-t-2 border-gray-300"></div>
                <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">Or continue with</span>
                <div class="flex-grow border-t-2 border-gray-300"></div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                <button type="button" class="flex items-center justify-center gap-2 border-2 border-gray-300 bg-white text-gray-700 py-3 px-4 rounded-lg font-semibold hover:border-cms-red hover:text-cms-red hover:-translate-y-1 hover:shadow-lg transition-all duration-300" id="googleLoginBtn">
                    <i class="fab fa-google text-lg"></i>
                    <span class="hidden sm:inline">Google</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-2 border-2 border-gray-300 bg-white text-gray-700 py-3 px-4 rounded-lg font-semibold hover:border-blue-500 hover:text-blue-600 hover:-translate-y-1 hover:shadow-lg transition-all duration-300" id="microsoftLoginBtn">
                    <i class="fab fa-microsoft text-lg"></i>
                    <span class="hidden sm:inline">Microsoft</span>
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="border-t border-gray-200 pt-4 text-center">
                <p class="text-gray-600 text-xs font-medium">© 2025 Legislative Services Committee Management System</p>
                <p class="text-gray-500 text-xs mt-1">City Government of Valenzuela</p>
                <p class="text-gray-500 text-xs mt-2">
                    <a href="terms.php" target="_blank" class="text-cms-red hover:text-cms-dark font-semibold">View Full Terms</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div id="termsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Terms & Conditions</h2>
                <button type="button" onclick="closeTermsModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body - Scrollable -->
            <div id="termsContent" class="flex-1 overflow-y-auto p-6 text-gray-700 text-sm">
                <div class="space-y-4">
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">1. Acceptance of Terms</h3>
                        <p>By accessing and using the Legislative Services Committee Management System, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">2. Use License</h3>
                        <p>Permission is granted to temporarily download one copy of the materials (information or software) on the Legislative Services Committee Management System for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                        <ul class="list-disc ml-6 mt-2 space-y-1">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to decompile or reverse engineer any software contained on the system</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                            <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
                        </ul>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">3. Disclaimer</h3>
                        <p>The materials on the Legislative Services Committee Management System are provided on an 'as is' basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">4. Limitations</h3>
                        <p>In no event shall the Legislative Services Committee Management System or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on the system.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">5. Accuracy of Materials</h3>
                        <p>The materials appearing on the Legislative Services Committee Management System could include technical, typographical, or photographic errors. We do not warrant that any of the materials on the system are accurate, complete, or current. We may make changes to the materials contained on the system at any time without notice.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">6. Links</h3>
                        <p>We have not reviewed all of the sites linked to our website and are not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by us of the site. Use of any such linked website is at the user's own risk.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">7. Modifications</h3>
                        <p>We may revise these terms and conditions for our system at any time without notice. By using this system, you are agreeing to be bound by the then current version of these terms and conditions.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">8. Governing Law</h3>
                        <p>These terms and conditions are governed by and construed in accordance with the laws of the Republic of the Philippines, and you irrevocably submit to the exclusive jurisdiction of the courts in that location.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">9. Account Security</h3>
                        <p>You are responsible for maintaining the confidentiality of your account and password and for restricting access to your computer. You agree to accept responsibility for all activities that occur under your account or password.</p>
                    </section>
                    
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">10. Data Privacy</h3>
                        <p>We are committed to protecting your privacy and personal data. All information collected through this system will be processed in accordance with applicable data protection laws and our privacy policy.</p>
                    </section>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                <button type="button" onclick="closeTermsModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 font-semibold">
                    Close
                </button>
                <button type="button" onclick="closeTermsModal(); document.getElementById('termsCheckbox').checked = true;" class="px-4 py-2 text-white bg-cms-red rounded-lg hover:bg-cms-dark font-semibold">
                    I Accept
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Terms & Conditions Modal Functions
        function openTermsModal() {
            document.getElementById('termsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
        
        function closeTermsModal() {
            document.getElementById('termsModal').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Restore background scroll
        }
        
        // Close modal when clicking outside
        document.getElementById('termsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTermsModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('termsModal').classList.contains('hidden')) {
                closeTermsModal();
            }
        });
        
        // Logout notification auto-dismiss timer
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
        let logoutSeconds = 5;
        const logoutAlertElement = document.getElementById('logoutAlert');
        const logoutTimerElement = document.getElementById('logoutTimer');
        const logoutProgressBar = document.getElementById('logoutProgressBar');
        
        function updateLogoutTimer() {
            logoutTimerElement.textContent = logoutSeconds;
            
            // Update progress bar width
            const progressPercent = (logoutSeconds / 5) * 100;
            logoutProgressBar.style.width = progressPercent + '%';
            
            if (logoutSeconds > 0) {
                logoutSeconds--;
                setTimeout(updateLogoutTimer, 1000);
            } else {
                // Fade out and remove
                logoutAlertElement.style.opacity = '0';
                logoutAlertElement.style.maxHeight = '0';
                logoutAlertElement.style.marginBottom = '0';
                logoutAlertElement.style.padding = '0';
                setTimeout(() => {
                    logoutAlertElement.style.display = 'none';
                    // Clean URL to remove logout parameter
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 300);
            }
        }
        
        updateLogoutTimer();
        <?php endif; ?>
        
        // Dismiss logout alert manually
        function dismissLogoutAlert() {
            const logoutAlert = document.getElementById('logoutAlert');
            if (logoutAlert) {
                logoutAlert.style.opacity = '0';
                logoutAlert.style.maxHeight = '0';
                logoutAlert.style.marginBottom = '0';
                logoutAlert.style.padding = '0';
                setTimeout(() => {
                    logoutAlert.style.display = 'none';
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 300);
            }
        }
        
        // Lockout timer countdown
        <?php if ($is_locked): ?>
        let remainingSeconds = <?php echo $remaining_time; ?>;
        const timerElement = document.getElementById('lockoutTimer');
        
        function updateLockoutTimer() {
            const minutes = Math.ceil(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            
            // Format as MM:SS for better clarity
            const formattedTime = `${String(Math.floor(remainingSeconds / 60)).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            timerElement.textContent = formattedTime;
            
            if (remainingSeconds > 0) {
                remainingSeconds--;
                setTimeout(updateLockoutTimer, 1000);
            } else {
                // Refresh page to remove lockout
                location.reload();
            }
        }
        
        updateLockoutTimer();
        
        // Auto-refresh when lockout expires
        setTimeout(() => {
            location.reload();
        }, <?php echo $remaining_time * 1000; ?>);
        <?php endif; ?>

        // Form submission handling with AJAX
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const termsCheckbox = document.getElementById('termsCheckbox');
            const btn = document.querySelector('.login-btn');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            // Validate inputs
            if (!email || !password) {
                errorMessage.textContent = 'Please enter both email and password.';
                errorAlert.classList.remove('hidden');
                return;
            }
            
            // Validate terms checkbox
            if (!termsCheckbox.checked) {
                errorMessage.textContent = 'Please agree to the Terms & Conditions to continue.';
                errorAlert.classList.remove('hidden');
                return;
            }
            
            // Hide error alert initially
            errorAlert.classList.add('hidden');
            
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            
            // Send login request via AJAX
            fetch('../app/controllers/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Successful login - redirect to dashboard
                    errorAlert.classList.add('hidden');
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Redirecting...';
                    setTimeout(() => {
                        window.location.href = '../public/dashboard.php';
                    }, 1000);
                } else if (data.locked) {
                    // Account is locked - reload page immediately to show lockout alert
                    errorMessage.textContent = data.message || 'Account is temporarily locked.';
                    errorAlert.classList.remove('hidden');
                    errorAlert.style.position = 'fixed';
                    errorAlert.style.top = '20px';
                    errorAlert.style.left = '50%';
                    errorAlert.style.transform = 'translateX(-50%)';
                    errorAlert.style.zIndex = '9999';
                    errorAlert.style.width = '90%';
                    errorAlert.style.maxWidth = '500px';
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-lock"></i> Account Locked';
                    
                    // Reload page immediately to show lockout screen
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    // Failed login - show error
                    errorMessage.textContent = data.message || 'Invalid email or password.';
                    errorAlert.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                    
                    // Auto-hide error after 5 seconds
                    setTimeout(() => {
                        errorAlert.classList.add('hidden');
                    }, 5000);
                }
            })
            .catch(error => {
                // Handle network and parsing errors
                console.error('Error:', error);
                errorMessage.textContent = 'Network error or server issue. Please try again.';
                errorAlert.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
            });
        });

        // OAuth handlers
        document.getElementById('googleLoginBtn').addEventListener('click', function() {
            alert('Google Sign-In not yet configured. Please use email login.');
        });
        
        document.getElementById('microsoftLoginBtn').addEventListener('click', function() {
            alert('Microsoft Sign-In not yet configured. Please use email login.');
        });
    </script>
</body>
</html>
