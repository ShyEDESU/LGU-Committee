<?php
require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/../app/helpers/SystemSettingsHelper.php';

// Fetch system settings for branding
$settings = getSystemSettings();
$themeColor = $settings['theme_color'] ?? '#dc2626';
$systemLogo = $settings['lgu_logo_path'] ?? 'assets/images/logo.png';
$logoPath = '../public/' . $systemLogo;

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

$login_attempts = isset($_SESSION['login_attempts']) ? (int) $_SESSION['login_attempts'] : 0;
$first_attempt_time = isset($_SESSION['first_attempt_time']) ? $_SESSION['first_attempt_time'] : null;

if ($login_attempts >= 5) {
    if ($first_attempt_time === null) {
        $first_attempt_time = time();
        $_SESSION['first_attempt_time'] = $first_attempt_time;
    }

    $elapsed_time = time() - $first_attempt_time;
    $lockout_duration = 300; // 5 minutes lockout

    if ($elapsed_time < $lockout_duration) {
        $is_locked = true;
        $remaining_time = $lockout_duration - $elapsed_time;
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_attempt_time'] = null;
        $is_locked = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="<?php echo $themeColor; ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Login - CMS | City of Valenzuela</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $logoPath; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logoPath; ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'v-navy': '#450a0a',
                        'v-red': '#D22B2B',
                        'v-gold': '#FFD700',
                    }
                }
            }
        }
    </script>

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
            box-shadow: 0 0 0 3px
                <?php echo $themeColor; ?>
                1a;
            /* 10% opacity */
        }

        /* Override dynamic primary colors */
        .bg-red-600 {
            background-color:
                <?php echo $themeColor; ?>
                !important;
        }

        .hover\:bg-red-700:hover {
            background-color:
                <?php echo $themeColor; ?>
                cc !important;
        }

        .text-red-600 {
            color:
                <?php echo $themeColor; ?>
                !important;
        }

        .dark\:text-red-400 {
            color:
                <?php echo $themeColor; ?>
                !important;
        }

        .border-red-600 {
            border-color:
                <?php echo $themeColor; ?>
                !important;
        }

        .focus\:ring-red-600:focus {
            --tw-ring-color:
                <?php echo $themeColor; ?>
                !important;
        }

        .underline.text-red-600 {
            color:
                <?php echo $themeColor; ?>
                !important;
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

    <script>
        // Initialize theme before page load
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body
    class="bg-white dark:bg-slate-950 min-h-screen flex items-center justify-center p-3 md:p-4 transition-colors relative overflow-x-hidden">

    <div class="w-full max-w-md relative z-10">
        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 animate-bounce-in">
                <div class="bg-white rounded-full shadow-xl flex items-center justify-center overflow-hidden transform hover:scale-105 transition-all duration-300"
                    style="width: 120px; height: 120px;">
                    <img src="<?php echo $logoPath; ?>" alt="Logo" class="w-full h-full object-contain p-2">
                </div>
            </div>
            <h1
                class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white animate-fade-in-up animation-delay-100">
                CMS</h1>
            <p
                class="text-sm md:text-base text-gray-600 dark:text-slate-400 mt-1 md:mt-2 animate-fade-in-up animation-delay-200">
                Committee Management System</p>
            <p class="text-xs md:text-sm text-red-600 font-semibold mt-1 animate-fade-in-up animation-delay-300">
                City Government of Valenzuela</p>
            <p class="text-xs text-gray-500 dark:text-slate-500 animate-fade-in-up animation-delay-400">Metropolitan
                Manila</p>
        </div>

        <!-- Login Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl shadow-2xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300 border border-white/10">
            <!-- Mobile Navigation & Theme Toggle -->
            <div class="mb-6 flex justify-between items-center">
                <a href="../index.php"
                    class="inline-flex items-center text-sm font-bold text-gray-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                    <i class="bi bi-arrow-left mr-2"></i> Back to Home
                </a>

                <!-- Theme Toggle -->
                <button onclick="toggleTheme()"
                    class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 transition-all border border-slate-200 dark:border-slate-600"
                    title="Toggle Theme">
                    <i class="bi bi-moon-fill dark:hidden"></i>
                    <i class="bi bi-sun-fill hidden dark:inline"></i>
                </button>
            </div>

            <div class="mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Welcome Back</h2>
                <p class="text-sm md:text-base text-gray-600 dark:text-slate-400 mt-1">Sign in to access your
                    account
                </p>
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
                            <p class="text-red-800 text-sm mb-3">Too many failed login attempts detected. For security,
                                your
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
                                <p class="text-green-800 text-sm">You have been successfully logged out. See you next
                                    time!
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
                        <div id="logoutProgressBar" class="h-full bg-green-500 transition-all" style="width: 100%;">
                        </div>
                    </div>
                    <p class="text-green-700 text-xs mt-2 text-center font-semibold">Closing in <span
                            id="logoutTimerText">5</span> seconds...</p>
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
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1 md:mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="your.email@lgu.gov.ph"
                        class="input-field w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base dark:bg-slate-700 dark:text-white">
                    <span class="text-red-500 text-xs hidden mt-1" id="email-error"></span>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password"
                        class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1 md:mb-2">
                        <i class="bi bi-lock mr-1"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder="Enter your password"
                            class="input-field w-full px-3 md:px-4 py-2.5 md:py-3 pr-12 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base dark:bg-slate-700 dark:text-white">
                        <button type="button" id="toggle-password"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                            <i class="bi bi-eye text-lg" id="eye-icon"></i>
                        </button>
                    </div>
                    <span class="text-red-500 text-xs hidden mt-1" id="password-error"></span>
                </div>

                <!-- Forgot Password -->
                <div class="flex items-center justify-end">
                    <a href="reset_password.php"
                        class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors">
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
                    <label for="termsCheckbox" class="text-gray-700 dark:text-slate-300 text-sm">
                        I agree to the
                        <button type="button" onclick="openTermsModal()"
                            class="text-red-600 dark:text-red-400 hover:text-red-700 font-semibold underline">Terms
                            &
                            Conditions</button>
                    </label>
                </div>
            </form>

            <!-- Divider -->
            <?php if (!$is_locked): ?>
                <div class="relative my-5 md:my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-slate-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-slate-800 text-gray-500 dark:text-slate-400">Or continue
                            with</span>
                    </div>
                </div>

                <!-- Alternative Login Options -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="microsoftLoginBtn"
                        class="flex items-center justify-center px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 hover:border-gray-400 dark:hover:border-slate-500 transition-all duration-200">
                        <i class="bi bi-microsoft text-lg mr-2 text-red-600 dark:text-red-400"></i>
                        <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Microsoft</span>
                    </button>
                    <button type="button" id="googleLoginBtn"
                        class="flex items-center justify-center px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 hover:border-gray-400 dark:hover:border-slate-500 transition-all duration-200">
                        <i class="bi bi-google text-lg mr-2 text-red-500 dark:text-red-400"></i>
                        <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Google</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 md:mt-8 text-center text-xs md:text-sm text-gray-600 dark:text-slate-500">
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
        <div
            class="bg-white dark:bg-slate-800 rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col border border-white/10">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Terms & Conditions</h2>
                <button type="button" onclick="closeTermsModal()"
                    class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200 text-2xl">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Body - Scrollable -->
            <div id="termsContent" class="flex-1 overflow-y-auto p-6 text-gray-700 dark:text-slate-300 text-sm">
                <div class="space-y-4">
                    <section>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">1. Acceptance of Terms</h3>
                        <p>By accessing and using the Legislative Services Committee Management System, you accept
                            and
                            agree to be bound by the terms and provision of this agreement.</p>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">2. Use License</h3>
                        <p>Permission is granted to temporarily download one copy of the materials (information or
                            software) on the Legislative Services Committee Management System for personal,
                            non-commercial transitory viewing only. This is the grant of a license, not a transfer
                            of
                            title, and under this license you may not:</p>
                        <ul class="list-disc ml-6 mt-2 space-y-1">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to decompile or reverse engineer any software contained on the system</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                            <li>Transfer the materials to another person or "mirror" the materials on any other
                                server
                            </li>
                        </ul>
                    </section>

                    <section>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">3. Disclaimer</h3>
                        <p>The materials on the Legislative Services Committee Management System are provided on an
                            'as
                            is' basis. We make no warranties, expressed or implied, and hereby disclaim and negate
                            all
                            other warranties including, without limitation, implied warranties or conditions of
                            merchantability, fitness for a particular purpose, or non-infringement of intellectual
                            property or other violation of rights.</p>
                    </section>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                <button type="button" onclick="closeTermsModal()"
                    class="px-4 py-2 text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 font-semibold transition">
                    Close
                </button>
                <button type="button"
                    onclick="closeTermsModal(); document.getElementById('termsCheckbox').checked = true;"
                    class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 font-semibold transition shadow-md">
                    I Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle Theme Function
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

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
            const logoutTimerTextElement = document.getElementById('logoutTimerText');
            const logoutProgressBar = document.getElementById('logoutProgressBar');

            function updateLogoutTimer() {
                logoutTimerTextElement.textContent = logoutSeconds;

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
                const formattedTime = `${String(Math.floor(remainingSeconds / 60)).padStart(2, '0')}:${String(remainingSeconds % 60).padStart(2, '0')}`;
                timerElement.textContent = formattedTime;

                if (remainingSeconds > 0) {
                    remainingSeconds--;
                    setTimeout(updateLockoutTimer, 1000);
                } else {
                    location.reload();
                }
            }

            updateLockoutTimer();
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.href = data.redirect;
                    } else {
                        showAlert(data.message || 'Login failed', 'error');
                        loginBtn.disabled = false;
                        loginBtnText.textContent = 'Sign In';
                        loginBtnIcon.classList.add('bi-arrow-right');
                        loginBtnIcon.classList.remove('spinner');
                        form.classList.add('animate-shake');
                        setTimeout(() => form.classList.remove('animate-shake'), 500);

                        if (data.is_locked) {
                            location.reload();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again later.', 'error');
                    loginBtn.disabled = false;
                    loginBtnText.textContent = 'Sign In';
                    loginBtnIcon.classList.add('bi-arrow-right');
                    loginBtnIcon.classList.remove('spinner');
                });
        });
    </script>
</body>

</html>