<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

require_once(__DIR__ . '/../../../config/database.php');

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'member');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    $errors = [];
    
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';

    if (empty($errors)) {
        // Check if email already exists
        $check_query = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error_message = 'This email address is already registered';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new user
            $insert_query = "INSERT INTO users (first_name, last_name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $role);
            
            if ($insert_stmt->execute()) {
                $success_message = 'User account created successfully!';
                // Clear form
                $first_name = $last_name = $email = $password = $confirm_password = '';
                $role = 'member';
            } else {
                $error_message = 'Failed to create user account. Please try again.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User | CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
    </script>
    <style>
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
        .slide-up { animation: slideUp 0.3s ease-in; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="flex flex-col h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 shadow-sm sticky top-0 z-20">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-user-plus text-red-600 mr-2"></i>Create User
                    </h1>
                </div>
                <a href="all-users.php" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-2xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create New User Account</h2>
                    <p class="text-gray-600 dark:text-gray-400">Add a new team member to the system</p>
                </div>

                <!-- Success Message -->
                <?php if ($success_message): ?>
                    <div class="slide-up bg-green-50 dark:bg-green-900 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl mt-1 mr-3 flex-shrink-0"></i>
                            <div class="flex-1">
                                <h3 class="font-semibold text-green-900 dark:text-green-200 mb-1">Success</h3>
                                <p class="text-green-800 dark:text-green-300 text-sm"><?php echo $success_message; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if ($error_message): ?>
                    <div class="slide-up bg-red-50 dark:bg-red-900 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-xl mt-1 mr-3 flex-shrink-0"></i>
                            <div class="flex-1">
                                <h3 class="font-semibold text-red-900 dark:text-red-200 mb-1">Error</h3>
                                <p class="text-red-800 dark:text-red-300 text-sm"><?php echo $error_message; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Create User Form -->
                <form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user text-red-600 mr-2"></i>First Name *
                            </label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                                placeholder="Enter first name"
                                value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                                required
                            >
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user text-red-600 mr-2"></i>Last Name *
                            </label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                                placeholder="Enter last name"
                                value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-envelope text-red-600 mr-2"></i>Email Address *
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                            placeholder="user@example.com"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required
                        >
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-briefcase text-red-600 mr-2"></i>User Role *
                        </label>
                        <select 
                            id="role" 
                            name="role" 
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                            required
                        >
                            <option value="member">Member</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Member: View-only access | Staff: Can manage content | Admin: Full access
                        </p>
                    </div>

                    <hr class="dark:border-gray-700 mb-6">

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-lock text-red-600 mr-2"></i>Password *
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                            placeholder="Enter password (min 8 characters)"
                            value="<?php echo htmlspecialchars($password ?? ''); ?>"
                            required
                        >
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-lock text-red-600 mr-2"></i>Confirm Password *
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-red-600 dark:focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all"
                            placeholder="Re-enter password"
                            value="<?php echo htmlspecialchars($confirm_password ?? ''); ?>"
                            required
                        >
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <p class="text-xs font-semibold text-blue-900 dark:text-blue-200 mb-2">Password Requirements:</p>
                        <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                            <li><i class="fas fa-check text-green-600 mr-2"></i>Minimum 8 characters</li>
                            <li><i class="fas fa-info text-blue-600 mr-2"></i>Mix of uppercase and lowercase letters</li>
                            <li><i class="fas fa-info text-blue-600 mr-2"></i>Include numbers and special characters</li>
                        </ul>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-check-circle"></i>Create Account
                        </button>
                        <a 
                            href="all-users.php" 
                            class="flex-1 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-bold py-3 px-6 rounded-lg shadow transition-colors duration-300 flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-times"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Initialize dark mode
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
