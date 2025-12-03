<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

require_once(__DIR__ . '/../../../config/database.php');

// Get all roles
$query = "SELECT * FROM user_roles ORDER BY role_name";
$result = $conn->query($query);
$roles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Define role colors
$roleColors = [
    'admin' => 'red',
    'staff' => 'blue',
    'member' => 'purple',
    'viewer' => 'gray'
];

// Define role descriptions
$roleDescriptions = [
    'admin' => 'Full system access including user management and settings',
    'staff' => 'Can manage content and view reports',
    'member' => 'Can view and participate in committees',
    'viewer' => 'Read-only access to system information'
];

// Define permissions for each role
$rolePermissions = [
    'admin' => ['Create', 'Read', 'Update', 'Delete', 'Manage Users', 'View Reports', 'System Settings'],
    'staff' => ['Create', 'Read', 'Update', 'View Reports', 'Manage Content'],
    'member' => ['Read', 'Participate', 'Submit Documents'],
    'viewer' => ['Read']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Roles | CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="flex flex-col h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 shadow-sm sticky top-0 z-20">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-shield-alt text-red-600 mr-2"></i>User Roles
                    </h1>
                </div>
                <a href="all-users.php" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Role Management</h2>
                    <p class="text-gray-600 dark:text-gray-400">Define and manage user roles and their permissions</p>
                </div>

                <!-- Role Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Admin Role -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow hover:-translate-y-1 duration-300">
                        <div class="h-1 bg-red-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Administrator</h3>
                                <i class="fas fa-crown text-red-600 text-2xl opacity-30"></i>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4"><?php echo $roleDescriptions['admin']; ?></p>
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Key Permissions:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($rolePermissions['admin'], 0, 3) as $perm): ?>
                                        <span class="text-xs bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-1 rounded">
                                            <i class="fas fa-check mr-1"></i><?php echo $perm; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit Role
                            </button>
                        </div>
                    </div>

                    <!-- Staff Role -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow hover:-translate-y-1 duration-300">
                        <div class="h-1 bg-blue-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Staff</h3>
                                <i class="fas fa-briefcase text-blue-600 text-2xl opacity-30"></i>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4"><?php echo $roleDescriptions['staff']; ?></p>
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Key Permissions:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($rolePermissions['staff'], 0, 3) as $perm): ?>
                                        <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                                            <i class="fas fa-check mr-1"></i><?php echo $perm; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit Role
                            </button>
                        </div>
                    </div>

                    <!-- Member Role -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow hover:-translate-y-1 duration-300">
                        <div class="h-1 bg-purple-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Member</h3>
                                <i class="fas fa-user-circle text-purple-600 text-2xl opacity-30"></i>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4"><?php echo $roleDescriptions['member']; ?></p>
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Key Permissions:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($rolePermissions['member'], 0, 3) as $perm): ?>
                                        <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded">
                                            <i class="fas fa-check mr-1"></i><?php echo $perm; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit Role
                            </button>
                        </div>
                    </div>

                    <!-- Viewer Role -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow hover:-translate-y-1 duration-300">
                        <div class="h-1 bg-gray-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Viewer</h3>
                                <i class="fas fa-eye text-gray-600 text-2xl opacity-30"></i>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4"><?php echo $roleDescriptions['viewer']; ?></p>
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Key Permissions:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($rolePermissions['viewer'], 0, 3) as $perm): ?>
                                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 px-2 py-1 rounded">
                                            <i class="fas fa-check mr-1"></i><?php echo $perm; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit Role
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Permissions Matrix -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b dark:border-gray-700">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            <i class="fas fa-th-list text-red-600 mr-2"></i>Permissions Matrix
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-white">Permission</th>
                                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Admin</th>
                                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Staff</th>
                                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Member</th>
                                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Viewer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">View Content</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">Create Content</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">Edit Content</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">Delete Content</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">Manage Users</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">View Reports</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-semibold">System Settings</td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-check text-green-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                    <td class="px-6 py-4 text-center"><i class="fas fa-times text-red-600"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
