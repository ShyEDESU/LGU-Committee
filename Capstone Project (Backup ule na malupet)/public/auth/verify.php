<?php
/**
 * Verification Page - Handles account activation via token
 */
require_once __DIR__ . '/../../app/helpers/UserHelper.php';

$token = $_GET['token'] ?? '';
$result = ['success' => false, 'message' => 'No token provided.'];

if (!empty($token)) {
    $result = verifyToken($token);
}

// Get system settings for branding
require_once __DIR__ . '/../../app/helpers/SystemSettingsHelper.php';
require_once __DIR__ . '/../../config/mail.php';
$settings = getSystemSettings();
$baseUrl = defined('APP_URL') ? rtrim(APP_URL, '/') : 'http://localhost/Capstone%20Project';
$baseUrl = str_replace(' ', '%20', $baseUrl);
$loginUrl = $baseUrl . "/auth/login.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification |
        <?php echo htmlspecialchars($settings['lgu_name']); ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden animate-fade-in">
        <div class="p-8 text-center">
            <!-- Logo/Icon -->
            <div
                class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full <?php echo $result['success'] ? 'bg-green-100' : 'bg-red-100'; ?>">
                <i
                    class="bi <?php echo $result['success'] ? 'bi-patch-check-fill text-green-600' : 'bi-exclamation-triangle-fill text-red-600'; ?> text-4xl"></i>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                <?php echo $result['success'] ? 'Verification Successful!' : 'Verification Failed'; ?>
            </h1>

            <!-- Message -->
            <p class="text-gray-600 dark:text-gray-400 mb-8">
                <?php echo htmlspecialchars($result['message']); ?>
            </p>

            <!-- Action Button -->
            <?php if ($result['success']): ?>
                <a href="<?php echo $loginUrl; ?>"
                    class="block w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition duration-200 transform hover:scale-[1.02]">
                    Go to Login Page
                </a>
            <?php else: ?>
                <div class="space-y-3">
                    <a href="<?php echo $loginUrl; ?>"
                        class="block w-full py-3 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold rounded-xl transition">
                        Back to Login
                    </a>
                    <p class="text-sm text-gray-500">
                        If you believe this is an error, please contact your administrator.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Branding -->
        <div class="px-8 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                &copy;
                <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['lgu_name']); ?> Management System
            </p>
        </div>
    </div>
</body>

</html>