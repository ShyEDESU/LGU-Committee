<?php
require_once __DIR__ . '/../config/session_config.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: ../public/dashboard.php');
    exit();
}

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
    $lockout_duration = 10; // 10 seconds for testing

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#dc2626">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Login - CMS | City of Valenzuela</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../public/assets/images/logo.png">
    <link rel="apple-touch-icon" href="../public/assets/images/logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Animation Keyframes */
        @keyframes fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce-in {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            70% {
                opacity: 1;
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out forwards;
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
        }

        .animate-bounce-in {
            animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        .animation-delay-100 {
            animation-delay: 100ms;
        }

        .animation-delay-200 {
            animation-delay: 200ms;
        }

        .animation-delay-300 {
            animation-delay: 300ms;
        }

        .animation-delay-400 {
            animation-delay: 400ms;
        }

        /* Prevent zoom on input focus in iOS */
        @media screen and (max-width: 767px) {

            input,
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Custom focus styles */
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        /* Loading spinner */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-3 md:p-4">
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 animate-bounce-in">
                <div class="bg-white rounded-full shadow-xl flex items-center justify-center overflow-hidden transform hover:scale-105 transition-all duration-300"
                    style="width: 120px; height: 120px;">
                    <img src="../public/assets/images/logo.png" alt="City Government of Valenzuela"
                        class="w-full h-full object-contain p-2">
                </div>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 animate-fade-in-up animation-delay-100">CMS</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2 animate-fade-in-up animation-delay-200">Committee
                Management System</p>
            <p class="text-xs md:text-sm text-red-600 font-semibold mt-1 animate-fade-in-up animation-delay-300">City
                Government of Valenzuela</p>
            <p class="text-xs text-gray-500 animate-fade-in-up animation-delay-400">Metropolitan Manila</p>
        </div>

        <!-- Login Card -->
        <div
            class="bg-white rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <div class="mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-sm md:text-base text-gray-600 mt-1">Sign in to access your account</p>
            </div>

            <!-- Security Alert - Shows when account is locked -->
            <?php if ($is_locked): ?>
                <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-4 mb-6 shadow-md">
                    <div class="flex items-start">
                        <i class="bi bi-lock-fill text-red-600 text-xl mt-1 mr-3 flex-shrink-0 animate-pulse"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                                <i class="bi bi-shield-lock text-red-600"></i>
                                Account Temporarily Locked
                            </h3>
                            <p class="text-red-800 text-sm mb-3">Too many failed login attempts detected. For security, your
                                account has been locked.</p>
                            <div class="bg-white bg-opacity-50 rounded-lg p-3 border border-red-200">
                                <p class="text-red-900 font-bold text-center text-2xl mb-1" id="lockoutTimer">
                                    <?php echo str_pad(floor($remaining_time / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($remaining_time % 60, 2, '0', STR_PAD_LEFT); ?>
                                </p>
                                <p class="text-red-700 text-xs text-center font-semibold">Time remaining</p>
                            </div>
                            <p class="text-red-700 text-xs mt-3 mb-0">
                                <i class="bi bi-info-circle mr-1"></i>
                                Please wait for the timer to expire before attempting to log in again.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>



            <!-- Logout Success Notification -->
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div id="logoutAlert"
                    class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6 animate-fade-in transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start flex-1">
                            <i class="bi bi-check-circle text-green-600 text-xl mt-1 mr-3 flex-shrink-0"></i>
                            <div class="flex-1">
                                <h3 class="font-semibold text-green-900 mb-1">Logged Out Successfully</h3>
                                <p class="text-green-800 text-sm">You have been successfully logged out. See you next time!
                                </p>
                            </div>
                        </div>
                        <div class="ml-3 flex-shrink-0">
                            <button type="button" onclick="dismissLogoutAlert()"
                                class="text-green-600 hover:text-green-900 transition-colors">
                                <i class="bi bi-x-lg text-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 h-1 bg-green-200 rounded overflow-hidden">
                        <div id="logoutProgressBar" class="h-full bg-green-500 transition-all" style="width: 100%;"></div>
                    </div>
                    <p class="text-green-700 text-xs mt-2 text-center font-semibold">Closing in <span
                            id="logoutTimer">5</span> seconds...</p>
                </div>
            <?php endif; ?>

            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4 hidden">
                <div id="alert-message" class="px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm">
                    <i class="bi mr-2" id="alert-icon"></i>
                    <span id="alert-text"></span>
                </div>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-4 md:space-y-5" <?php echo $is_locked ? 'style="display: none;"' : ''; ?>>
                <input type="hidden" name="action" value="login">

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1 md:mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="your.email@lgu.gov.ph"
                        class="input-field w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                    <span class="text-red-500 text-xs hidden mt-1" id="email-error"></span>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1 md:mb-2">
                        <i class="bi bi-lock mr-1"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder="Enter your password"
                            class="input-field w-full px-3 md:px-4 py-2.5 md:py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                        <button type="button" id="toggle-password"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="bi bi-eye text-lg" id="eye-icon"></i>
                        </button>
                    </div>
                    <span class="text-red-500 text-xs hidden mt-1" id="password-error"></span>
                </div>

                <!-- Forgot Password -->
                <div class="flex items-center justify-end">
                    <a href="reset_password.php"
                        class="text-sm text-red-600 hover:text-red-700 font-medium transition-colors">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="login-btn"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 md:py-3 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg flex items-center justify-center">
                    <span id="login-btn-text">Sign In</span>
                    <i class="bi bi-arrow-right ml-2" id="login-btn-icon"></i>
                </button>

                <!-- Terms & Conditions Checkbox -->
                <div class="flex items-start">
                    <input type="checkbox" id="termsCheckbox"
                        class="w-4 h-4 mt-1 mr-2 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-600 cursor-pointer"
                        required>
                    <label for="termsCheckbox" class="text-gray-700 text-sm">
                        I agree to the
                        <button type="button" onclick="openTermsModal()"
                            class="text-red-600 hover:text-red-700 font-semibold underline">Terms & Conditions</button>
                    </label>
                </div>
            </form>

            <!-- Divider -->
            <?php if (!$is_locked): ?>
                <div class="relative my-5 md:my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <!-- Alternative Login Options -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="microsoftLoginBtn"
                        class="flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                        <i class="bi bi-microsoft text-lg mr-2 text-blue-600"></i>
                        <span class="text-sm font-medium text-gray-700">Microsoft</span>
                    </button>
                    <button type="button" id="googleLoginBtn"
                        class="flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                        <i class="bi bi-google text-lg mr-2 text-red-500"></i>
                        <span class="text-sm font-medium text-gray-700">Google</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 md:mt-8 text-center text-xs md:text-sm text-gray-600">
            <p>&copy; 2025 City Government of Valenzuela. All rights reserved.</p>
            <div class="mt-2 space-x-2 md:space-x-4">
                <a href="privacy.php" class="hover:text-red-600 transition-colors">Privacy Policy</a>
                <span>•</span>
                <a href="terms.php" class="hover:text-red-600 transition-colors">Terms of Service</a>
                <span>•</span>
                <a href="help.php" class="hover:text-red-600 transition-colors">Help</a>
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
                        <p>By accessing and using the Legislative Services Committee Management System, you accept and
                            agree to be bound by the terms and provision of this agreement.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">2. Use License</h3>
                        <p>Permission is granted to temporarily download one copy of the materials (information or
                            software) on the Legislative Services Committee Management System for personal,
                            non-commercial transitory viewing only. This is the grant of a license, not a transfer of
                            title, and under this license you may not:</p>
                        <ul class="list-disc ml-6 mt-2 space-y-1">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to decompile or reverse engineer any software contained on the system</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                            <li>Transfer the materials to another person or "mirror" the materials on any other server
                            </li>
                        </ul>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">3. Disclaimer</h3>
                        <p>The materials on the Legislative Services Committee Management System are provided on an 'as
                            is' basis. We make no warranties, expressed or implied, and hereby disclaim and negate all
                            other warranties including, without limitation, implied warranties or conditions of
                            merchantability, fitness for a particular purpose, or non-infringement of intellectual
                            property or other violation of rights.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">4. Limitations</h3>
                        <p>In no event shall the Legislative Services Committee Management System or its suppliers be
                            liable for any damages (including, without limitation, damages for loss of data or profit,
                            or due to business interruption) arising out of the use or inability to use the materials on
                            the system.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">5. Accuracy of Materials</h3>
                        <p>The materials appearing on the Legislative Services Committee Management System could include
                            technical, typographical, or photographic errors. We do not warrant that any of the
                            materials on the system are accurate, complete, or current. We may make changes to the
                            materials contained on the system at any time without notice.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">6. Links</h3>
                        <p>We have not reviewed all of the sites linked to our website and are not responsible for the
                            contents of any such linked site. The inclusion of any link does not imply endorsement by us
                            of the site. Use of any such linked website is at the user's own risk.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">7. Modifications</h3>
                        <p>We may revise these terms and conditions for our system at any time without notice. By using
                            this system, you are agreeing to be bound by the then current version of these terms and
                            conditions.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">8. Governing Law</h3>
                        <p>These terms and conditions are governed by and construed in accordance with the laws of the
                            Republic of the Philippines, and you irrevocably submit to the exclusive jurisdiction of the
                            courts in that location.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">9. Account Security</h3>
                        <p>You are responsible for maintaining the confidentiality of your account and password and for
                            restricting access to your computer. You agree to accept responsibility for all activities
                            that occur under your account or password.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">10. Data Privacy</h3>
                        <p>We are committed to protecting your privacy and personal data. All information collected
                            through this system will be processed in accordance with applicable data protection laws and
                            our privacy policy.</p>
                    </section>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                <button type="button" onclick="closeTermsModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 font-semibold">
                    Close
                </button>
                <button type="button"
                    onclick="closeTermsModal(); document.getElementById('termsCheckbox').checked = true;"
                    class="px-4 py-2 text-white bg-cms-red rounded-lg hover:bg-cms-dark font-semibold">
                    I Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });

        // Show alert message
        function showAlert(message, type = 'error') {
            const container = document.getElementById('alert-container');
            const alertMessage = document.getElementById('alert-message');
            const alertIcon = document.getElementById('alert-icon');
            const alertText = document.getElementById('alert-text');

            container.classList.remove('hidden');
            alertText.textContent = message;

            // Reset classes
            alertMessage.className = 'px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm';
            alertIcon.className = 'bi mr-2';

            if (type === 'success') {
                alertMessage.classList.add('bg-green-50', 'border', 'border-green-200', 'text-green-700');
                alertIcon.classList.add('bi-check-circle');
            } else if (type === 'error') {
                alertMessage.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
                alertIcon.classList.add('bi-exclamation-circle');
            } else if (type === 'warning') {
                alertMessage.classList.add('bg-yellow-50', 'border', 'border-yellow-200', 'text-yellow-700');
                alertIcon.classList.add('bi-exclamation-triangle');
            }
        }

        // Hide alert
        function hideAlert() {
            document.getElementById('alert-container').classList.add('hidden');
        }

        // Terms & Conditions Modal Functions
        function openTermsModal() {
            document.getElementById('termsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTermsModal() {
            document.getElementById('termsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('termsModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeTermsModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !document.getElementById('termsModal')?.classList.contains('hidden')) {
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

                const progressPercent = (logoutSeconds / 5) * 100;
                logoutProgressBar.style.width = progressPercent + '%';

                if (logoutSeconds > 0) {
                    logoutSeconds--;
                    setTimeout(updateLogoutTimer, 1000);
                } else {
                    logoutAlertElement.style.opacity = '0';
                    logoutAlertElement.style.maxHeight = '0';
                    logoutAlertElement.style.marginBottom = '0';
                    logoutAlertElement.style.padding = '0';
                    setTimeout(() => {
                        logoutAlertElement.style.display = 'none';
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

                const formattedTime = `${String(Math.floor(remainingSeconds / 60)).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                timerElement.textContent = formattedTime;

                if (remainingSeconds > 0) {
                    remainingSeconds--;
                    setTimeout(updateLockoutTimer, 1000);
                } else {
                    location.reload();
                }
            }

            updateLockoutTimer();

            setTimeout(() => {
                location.reload();
            }, <?php echo $remaining_time * 1000; ?>);
        <?php endif; ?>

        // Handle login form submission
        document.getElementById('loginForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('login-btn');
            const loginBtnText = document.getElementById('login-btn-text');
            const loginBtnIcon = document.getElementById('login-btn-icon');
            const form = document.getElementById('loginForm');
            const termsCheckbox = document.getElementById('termsCheckbox');

            // Clear previous errors
            hideAlert();

            // Basic validation
            if (!email) {
                showAlert('Please enter your email address', 'error');
                document.getElementById('email').focus();
                form.classList.add('animate-shake');
                setTimeout(() => form.classList.remove('animate-shake'), 500);
                return;
            }

            if (!password) {
                showAlert('Please enter your password', 'error');
                document.getElementById('password').focus();
                form.classList.add('animate-shake');
                setTimeout(() => form.classList.remove('animate-shake'), 500);
                return;
            }

            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'error');
                document.getElementById('email').focus();
                return;
            }

            // Validate terms checkbox
            if (!termsCheckbox.checked) {
                showAlert('Please agree to the Terms & Conditions to continue', 'error');
                form.classList.add('animate-shake');
                setTimeout(() => form.classList.remove('animate-shake'), 500);
                return;
            }

            // Show loading state
            loginBtn.disabled = true;
            loginBtnText.textContent = 'Signing in...';
            loginBtnIcon.classList.remove('bi-arrow-right');
            loginBtnIcon.classList.add('spinner');
            loginBtnIcon.innerHTML = '';

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
                        // Successful login
                        showAlert('Login successful! Redirecting...', 'success');
                        loginBtnText.textContent = 'Redirecting...';
                        loginBtnIcon.classList.remove('spinner');
                        loginBtnIcon.classList.add('bi-check-circle');
                        setTimeout(() => {
                            window.location.href = '../public/dashboard.php';
                        }, 1000);
                    } else if (data.locked) {
                        // Account is locked
                        showAlert(data.message || 'Account is temporarily locked.', 'error');
                        loginBtn.disabled = true;
                        loginBtnText.textContent = 'Account Locked';
                        loginBtnIcon.classList.remove('spinner');
                        loginBtnIcon.classList.add('bi-lock-fill');

                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        // Failed login
                        showAlert(data.message || 'Invalid email or password.', 'error');
                        loginBtn.disabled = false;
                        loginBtnText.textContent = 'Sign In';
                        loginBtnIcon.classList.remove('spinner');
                        loginBtnIcon.classList.add('bi-arrow-right');
                        loginBtnIcon.innerHTML = '';

                        form.classList.add('animate-shake');
                        setTimeout(() => form.classList.remove('animate-shake'), 500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Network error or server issue. Please try again.', 'error');
                    loginBtn.disabled = false;
                    loginBtnText.textContent = 'Sign In';
                    loginBtnIcon.classList.remove('spinner');
                    loginBtnIcon.classList.add('bi-arrow-right');
                    loginBtnIcon.innerHTML = '';
                });
        });

        // OAuth handlers
        document.getElementById('googleLoginBtn')?.addEventListener('click', function () {
            showAlert('Google Sign-In not yet configured. Please use email login.', 'warning');
        });

        document.getElementById('microsoftLoginBtn')?.addEventListener('click', function () {
            showAlert('Microsoft Sign-In not yet configured. Please use email login.', 'warning');
        });

        // Auto-focus email field
        document.getElementById('email')?.focus();
    </script>
</body>

</html>