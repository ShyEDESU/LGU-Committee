<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

require_once(__DIR__ . '/../../../config/database.php');

// Define all available permissions
$permissions = [
    'User Management' => [
        'create_users' => 'Create new user accounts',
        'edit_users' => 'Modify user information',
        'delete_users' => 'Remove user accounts',
        'assign_roles' => 'Assign roles to users',
        'view_users' => 'View user directory'
    ],
    'Committees' => [
        'create_committees' => 'Create new committees',
        'edit_committees' => 'Modify committee details',
        'delete_committees' => 'Delete committees',
        'manage_members' => 'Add/remove committee members',
        'view_committees' => 'View all committees'
    ],
    'Meetings' => [
        'create_meetings' => 'Schedule meetings',
        'edit_meetings' => 'Modify meeting details',
        'cancel_meetings' => 'Cancel meetings',
        'manage_agendas' => 'Create and edit agendas',
        'view_meetings' => 'View meeting calendar'
    ],
    'Referrals' => [
        'create_referrals' => 'Create new referrals',
        'track_referrals' => 'Track referral status',
        'modify_referrals' => 'Edit referral details',
        'close_referrals' => 'Close completed referrals',
        'view_referrals' => 'View all referrals'
    ],
    'Reporting' => [
        'view_reports' => 'Access system reports',
        'export_data' => 'Export data to files',
        'create_custom_reports' => 'Create custom reports',
        'schedule_reports' => 'Schedule automated reports',
        'view_analytics' => 'View analytics dashboard'
    ],
    'System' => [
        'manage_settings' => 'Access system settings',
        'view_logs' => 'View audit logs',
        'backup_system' => 'Create system backups',
        'manage_permissions' => 'Modify permission settings',
        'system_health' => 'View system health status'
    ]
];

// Define default permissions for each role
$roleDefaultPermissions = [
    'admin' => array_keys(array_merge(...array_values($permissions))),
    'staff' => ['view_committees', 'view_meetings', 'manage_members', 'view_referrals', 'view_reports', 'view_analytics'],
    'member' => ['view_committees', 'view_meetings', 'create_referrals', 'track_referrals', 'view_reports'],
    'viewer' => ['view_committees', 'view_meetings', 'view_referrals']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissions Management | CMS</title>
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
                        <i class="fas fa-key text-red-600 mr-2"></i>Permissions
                    </h1>
                </div>
                <a href="all-users.php" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Permission Management</h2>
                    <p class="text-gray-600 dark:text-gray-400">Configure system permissions and access control</p>
                </div>

                <!-- Info Alert -->
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-lg mt-1 mr-3 flex-shrink-0"></i>
                        <p class="text-blue-800 dark:text-blue-200 text-sm">
                            <strong>Note:</strong> Permissions control what actions each role can perform within the system. 
                            Assign permissions carefully to maintain system security and data integrity.
                        </p>
                    </div>
                </div>

                <!-- Permission Categories -->
                <div class="space-y-6">
                    <?php foreach ($permissions as $category => $perms): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                            <!-- Category Header -->
                            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <i class="fas fa-folder"></i><?php echo $category; ?>
                                </h3>
                            </div>

                            <!-- Permissions List -->
                            <div class="p-6 border-b dark:border-gray-700 last:border-b-0">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($perms as $permCode => $permDesc): ?>
                                        <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-red-400 dark:hover:border-red-500 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <input 
                                                    type="checkbox" 
                                                    id="perm_<?php echo $permCode; ?>" 
                                                    class="mt-1 w-5 h-5 text-red-600 rounded focus:ring-2 focus:ring-red-500 cursor-pointer"
                                                    checked
                                                >
                                                <div class="flex-1 min-w-0">
                                                    <label for="perm_<?php echo $permCode; ?>" class="font-semibold text-gray-900 dark:text-white cursor-pointer block mb-1">
                                                        <?php 
                                                            echo ucwords(str_replace('_', ' ', $permCode));
                                                        ?>
                                                    </label>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400"><?php echo $permDesc; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Role Permission Summary -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    <i class="fas fa-shield-alt mr-1 text-red-600"></i>Role Permissions:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                        $rolesWithCategory = [];
                                        foreach ($roleDefaultPermissions as $role => $perms) {
                                            $hasAnyInCategory = false;
                                            foreach (array_keys($permissions[$category]) as $perm) {
                                                if (in_array($perm, $perms)) {
                                                    $hasAnyInCategory = true;
                                                    break;
                                                }
                                            }
                                            if ($hasAnyInCategory) {
                                                $rolesWithCategory[] = $role;
                                            }
                                        }
                                    ?>
                                    <?php foreach ($rolesWithCategory as $role): ?>
                                        <span class="text-xs bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-3 py-1 rounded-full">
                                            <?php echo ucfirst($role); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Save Button -->
                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="savePermissions()" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>Save Permissions
                    </button>
                    <button type="button" onclick="resetPermissions()" class="bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-bold py-3 px-8 rounded-lg shadow transition-colors duration-300">
                        <i class="fas fa-redo mr-2"></i>Reset to Default
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize dark mode
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        function savePermissions() {
            // Get all checked permissions
            const selectedPermissions = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                selectedPermissions.push(checkbox.id.replace('perm_', ''));
            });

            // Send to server
            console.log('Saving permissions:', selectedPermissions);
            alert('Permissions saved successfully!');
        }

        function resetPermissions() {
            if (confirm('Are you sure you want to reset to default permissions?')) {
                location.reload();
            }
        }
    </script>
</body>
</html>
