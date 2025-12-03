<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Check if user has admin privileges (simplified - would need proper role checking)
// For now, we'll allow access and display user role-based content

require_once(__DIR__ . '/../../../config/database.php');

// Get all users
$query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at FROM users u ORDER BY u.created_at DESC";
$result = $conn->query($query);
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Status badge colors
$statusColors = [
    'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
];

// Role badge colors
$roleColors = [
    'admin' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'staff' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'member' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    'viewer' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - All Users | CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .fade-in { animation: fadeIn 0.3s ease-in; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="flex flex-col h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 shadow-sm sticky top-0 z-20">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-users text-red-600 mr-2"></i>User Management
                    </h1>
                    <span class="text-gray-600 dark:text-gray-400 text-sm">All Users</span>
                </div>
                <button onclick="history.back()" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">User Directory</h2>
                        <p class="text-gray-600 dark:text-gray-400">Manage all user accounts and permissions</p>
                    </div>
                    <a href="create-user.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>Add New User
                    </a>
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Email</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Role</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Joined</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-inbox text-4xl opacity-50 mb-3"></i>
                                            <p class="text-lg font-semibold">No users found</p>
                                            <p class="text-sm">Start by creating a new user account</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white font-bold">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $roleColors[strtolower($user['role'])] ?? 'bg-gray-100 text-gray-800'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $statusColors[strtolower($user['status'])] ?? 'bg-gray-100 text-gray-800'; ?>">
                                                    <i class="fas fa-circle mr-1" style="font-size: 0.5em; vertical-align: middle;"></i>
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 text-xs">
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-2">
                                                    <button class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900 px-3 py-2 rounded transition-colors" title="Edit user">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900 px-3 py-2 rounded transition-colors" title="Delete user">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo count($users); ?></p>
                            </div>
                            <i class="fas fa-users text-red-600 text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Active Users</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></p>
                            </div>
                            <i class="fas fa-check-circle text-green-600 text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Admins</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></p>
                            </div>
                            <i class="fas fa-crown text-yellow-600 text-4xl opacity-20"></i>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Staff Members</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'staff')); ?></p>
                            </div>
                            <i class="fas fa-briefcase text-blue-600 text-4xl opacity-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dark mode support
        document.documentElement.classList.add('dark');
    </script>
</body>
</html>
