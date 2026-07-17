<?php
require_once __DIR__ . '/../config/session_config.php';

// If user is not authenticated at all, redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// If OTP is not pending (already verified or old session without OTP flow), go to dashboard
if (!isset($_SESSION['otp_pending']) || $_SESSION['otp_pending'] !== true) {
    header('Location: ../public/dashboard.php');
    exit();
}

$email = $_SESSION['email'] ?? 'User';
$current_otp = $_SESSION['current_otp'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2-Step Verification - Valenzuela City LGU</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        red: {
                            650: '#c51f1f',
                            750: '#b11b1b'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .otp-input:focus {
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
            border-color: #dc2626;
        }

        #login-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(to right, #dc2626, #ef4444, #f97316);
            z-index: 999999;
            width: 0%;
            opacity: 1;
            transition: width 0.3s ease-out, opacity 0.3s ease-in-out;
            box-shadow: 0 0 10px rgba(220, 38, 38, 0.5);
            pointer-events: none;
        }

        #cms-page-loader {
            position: fixed;
            inset: 0;
            z-index: 999998;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(3px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
        }
        #cms-page-loader.visible {
            opacity: 1;
            pointer-events: all;
        }
        #cms-page-loader .loader-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            min-width: 160px;
        }
        .dark #cms-page-loader .loader-card {
            background: #1f2937;
        }
        #cms-page-loader .loader-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #fee2e2;
            border-top-color: #dc2626;
            border-radius: 50%;
            animation: cms-spin 0.7s linear infinite;
        }
        #cms-page-loader .loader-text {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark #cms-page-loader .loader-text {
            color: #9ca3af;
        }
        @keyframes cms-spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen justify-center items-center p-4">

    <!-- Developer Helper Banner -->
    <?php if (!empty($current_otp)): ?>
        <div class="w-full max-w-md bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 p-4 rounded-xl mb-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">Developer Verification Code Helper</p>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Code sent to: <span class="font-semibold"><?php echo htmlspecialchars($email); ?></span></p>
            </div>
            <div class="bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-100 text-lg font-black px-3 py-1.5 rounded-lg border border-amber-300 select-all cursor-pointer" title="Click to select code">
                <?php echo $current_otp; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Outer Form Card -->
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-50 dark:bg-red-950/30 text-red-650 dark:text-red-400 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border border-red-100 dark:border-red-900/50">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">2-Step Verification</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                We sent a 6-digit security code to your email. Enter it below to secure your session.
            </p>
        </div>

        <!-- Custom Alert Container -->
        <div id="alertBox" class="hidden mb-6 p-4 rounded-xl border flex items-center space-x-3 text-sm animate-fade-in">
            <i id="alertIcon" class="bi text-lg"></i>
            <p id="alertMsg" class="font-medium"></p>
        </div>

        <!-- OTP Form -->
        <form id="otpForm" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Verification Code</label>
                <!-- Inputs container -->
                <div class="flex justify-between gap-2" id="otp-inputs-container">
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition" autofocus>
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition">
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition">
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition">
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition">
                    <input type="text" maxlength="1" pattern="[0-9]" required class="otp-input w-12 h-14 text-center text-xl font-bold border border-gray-300 dark:border-gray-650 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-950 dark:text-white outline-none transition">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="verify-btn" class="w-full py-3.5 bg-red-600 hover:bg-red-750 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2 text-sm">
                <span>Verify & Continue</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <!-- Resend Footer Link -->
        <div class="text-center mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 text-sm">
            <span class="text-gray-500 dark:text-gray-400">Didn't receive the code?</span>
            <button id="resend-link" class="text-red-650 hover:underline font-bold ml-1 transition">
                Resend Code
            </button>
            <span id="resend-countdown" class="hidden text-gray-400 ml-1 font-semibold"></span>
        </div>
    </div>

    <!-- Back to Login -->
    <a href="logout.php" class="text-sm font-semibold text-gray-500 hover:text-red-600 mt-6 transition flex items-center space-x-1">
        <i class="bi bi-arrow-left"></i>
        <span>Return to Login</span>
    </a>

    <!-- Custom Verification Progress Loader Overlay -->
    <div id="cms-page-loader">
        <div class="loader-card">
            <div class="loader-spinner"></div>
            <span class="loader-text" id="loader-status-text">Verifying...</span>
        </div>
    </div>

    <!-- OTP Form Interactivity Scripts -->
    <script>
        // Custom UI Alert System
        function showAlert(message, type = 'error') {
            const alertBox = document.getElementById('alertBox');
            const alertIcon = document.getElementById('alertIcon');
            const alertMsg = document.getElementById('alertMsg');

            alertBox.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 text-sm animate-fade-in";
            
            if (type === 'error') {
                alertBox.classList.add('bg-red-50', 'dark:bg-red-950/20', 'border-red-200', 'dark:border-red-900/50', 'text-red-800', 'dark:text-red-300');
                alertIcon.className = 'bi bi-x-circle text-red-500';
            } else {
                alertBox.classList.add('bg-green-50', 'dark:bg-green-950/20', 'border-green-200', 'dark:border-green-900/50', 'text-green-800', 'dark:text-green-300');
                alertIcon.className = 'bi bi-check-circle text-green-500';
            }

            alertMsg.textContent = message;
            alertBox.classList.remove('hidden');
        }

        function hideAlert() {
            document.getElementById('alertBox').classList.add('hidden');
        }

        // Manage numeric digit key sequences automatically
        const inputs = document.querySelectorAll('.otp-input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Keep only numeric inputs
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Submit form
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            hideAlert();

            // Extract values
            let code = '';
            inputs.forEach(input => code += input.value);

            if (code.length !== 6) {
                showAlert('Please enter the complete 6-digit code.');
                return;
            }

            // Show page loader spinner
            const loader = document.getElementById('cms-page-loader');
            const loaderText = document.getElementById('loader-status-text');
            loaderText.textContent = "Verifying...";
            loader.classList.add('visible');

            // Inject top loading progress bar
            let loaderBar = document.getElementById('login-loading-bar');
            if (!loaderBar) {
                loaderBar = document.createElement('div');
                loaderBar.id = 'login-loading-bar';
                document.body.appendChild(loaderBar);
            }
            loaderBar.style.opacity = '1';
            loaderBar.style.width = '15%';

            // Progressive bar animation
            let progress = 15;
            const barInterval = setInterval(() => {
                if (progress < 85) {
                    progress += Math.random() * 8;
                    loaderBar.style.width = progress + '%';
                }
            }, 100);

            // POST to AuthController
            const formData = new FormData();
            formData.append('action', 'verify_otp');
            formData.append('otp', code);

            fetch('../app/controllers/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(barInterval);
                if (data.success) {
                    loaderBar.style.width = '100%';
                    loaderText.textContent = "Success!";
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 400);
                } else {
                    loader.classList.remove('visible');
                    loaderBar.style.opacity = '0';
                    setTimeout(() => { loaderBar.style.width = '0%'; }, 300);
                    showAlert(data.message || 'Verification failed. Please try again.');
                }
            })
            .catch(err => {
                clearInterval(barInterval);
                loader.classList.remove('visible');
                loaderBar.style.opacity = '0';
                setTimeout(() => { loaderBar.style.width = '0%'; }, 300);
                console.error(err);
                showAlert('An unexpected network error occurred.');
            });
        });

        // Resend Verification Code handler
        let resendCooldown = false;
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            if (resendCooldown) return;
            hideAlert();

            const link = this;
            const countdownEl = document.getElementById('resend-countdown');

            // Set visual resend loading
            link.classList.add('hidden');
            countdownEl.textContent = "Sending...";
            countdownEl.classList.remove('hidden');

            const formData = new FormData();
            formData.append('action', 'resend_otp');

            fetch('../app/controllers/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('A new verification code has been generated.', 'success');
                    // Reload to update the developer helper box with the new code
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(data.message || 'Failed to resend code.');
                    link.classList.remove('hidden');
                    countdownEl.classList.add('hidden');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Failed to resend code due to connection issues.');
                link.classList.remove('hidden');
                countdownEl.classList.add('hidden');
            });
        });
    </script>
</body>

</html>
