<?php 
session_start();
require_once '../../../config/database.php';
require_once '../../../app/helpers/ModuleDataHelper.php';

// Initialize module data for potential testing
ModuleDataHelper::initializeModuleData();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'] ?? 'User';
$userRole = isset($_SESSION['role_name']) ? $_SESSION['role_name'] : 'user';

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$currentUser = $userResult->fetch_assoc();
$stmt->close();

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

$dummyAccounts = [
    ['user_id' => 1, 'email' => 'admin@lgu.gov.ph', 'first_name' => 'John', 'last_name' => 'Administrator', 'phone' => '09123456789', 'department' => 'IT Department', 'position' => 'Administrator', 'is_active' => true, 'created_at' => '2025-01-15', 'role' => 'Administrator'],
    ['user_id' => 2, 'email' => 'mary@lgu.gov.ph', 'first_name' => 'Mary', 'last_name' => 'Johnson', 'phone' => '09234567890', 'department' => 'Legislative Division', 'position' => 'Legislative Officer', 'is_active' => true, 'created_at' => '2025-02-10', 'role' => 'Officer'],
    ['user_id' => 3, 'email' => 'robert@lgu.gov.ph', 'first_name' => 'Robert', 'last_name' => 'Martinez', 'phone' => '09345678901', 'department' => 'Committee Services', 'position' => 'Committee Coordinator', 'is_active' => true, 'created_at' => '2025-03-05', 'role' => 'Officer'],
    ['user_id' => 4, 'email' => 'angela@lgu.gov.ph', 'first_name' => 'Angela', 'last_name' => 'Santos', 'phone' => '09456789012', 'department' => 'Research Division', 'position' => 'Research Analyst', 'is_active' => true, 'created_at' => '2025-04-12', 'role' => 'Analyst'],
    ['user_id' => 5, 'email' => 'carlos@lgu.gov.ph', 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'phone' => '09567890123', 'department' => 'IT Department', 'position' => 'IT Support', 'is_active' => false, 'created_at' => '2025-05-20', 'role' => 'Support'],
];

$isAdmin = ($userRole === 'Admin' || $userRole === 'Administrator');

$profileMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $department = $_POST['department'] ?? '';
    $position = $_POST['position'] ?? '';
    
    $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, department = ?, position = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssi", $firstName, $lastName, $phone, $department, $position, $userId);
    
    if ($updateStmt->execute()) {
        $profileMessage = '<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">✓ Profile updated successfully!</div>';
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $currentUser['first_name'] = $firstName;
        $currentUser['last_name'] = $lastName;
        $currentUser['phone'] = $phone;
        $currentUser['department'] = $department;
        $currentUser['position'] = $position;
    } else {
        $profileMessage = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">✗ Error updating profile</div>';
    }
    $updateStmt->close();
}

$passwordMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        $passwordMessage = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">✗ Passwords do not match</div>';
    } elseif (strlen($newPassword) < 8) {
        $passwordMessage = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">✗ Password must be at least 8 characters</div>';
    } elseif (password_verify($currentPassword, $currentUser['password_hash'])) {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pwdQuery = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $pwdStmt = $conn->prepare($pwdQuery);
        $pwdStmt->bind_param("si", $newHash, $userId);
        
        if ($pwdStmt->execute()) {
            $passwordMessage = '<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">✓ Password changed successfully!</div>';
        } else {
            $passwordMessage = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">✗ Error changing password</div>';
        }
        $pwdStmt->close();
    } else {
        $passwordMessage = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">✗ Current password is incorrect</div>';
    }
}
?>
<?php include '../../../public/includes/header-sidebar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Module Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-50 border-red-200 border-2 rounded-lg p-3">
                <i class="bi bi-people-fill text-red-700 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo $isAdmin ? 'Manage and edit all users in your system.' : 'Manage your personal profile and account settings.'; ?></p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-8 bg-white dark:bg-gray-800">
        <nav class="flex gap-4 overflow-x-auto" role="tablist">
            <button role="tab" id="profile-tab" aria-selected="true" onclick="switchTab('profile', 'User Management')" class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap <?php echo $activeTab === 'profile' ? 'text-cms-red border-b-2 border-cms-red' : 'text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-900 dark:hover:text-white'; ?>">My Profile</button>
            <button role="tab" id="settings-tab" aria-selected="false" onclick="switchTab('settings', 'User Management')" class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap <?php echo $activeTab === 'settings' ? 'text-cms-red border-b-2 border-cms-red' : 'text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-900 dark:hover:text-white'; ?>">Settings</button>
            <button role="tab" id="help-tab" aria-selected="false" onclick="switchTab('help', 'User Management')" class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap <?php echo $activeTab === 'help' ? 'text-cms-red border-b-2 border-cms-red' : 'text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-900 dark:hover:text-white'; ?>">Help & Support</button>
            <?php if ($isAdmin): ?>
            <button role="tab" id="all-users-tab" aria-selected="false" onclick="switchTab('all-users', 'User Management')" class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap <?php echo $activeTab === 'all-users' ? 'text-cms-red border-b-2 border-cms-red' : 'text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-900 dark:hover:text-white'; ?>">All Users</button>
            <?php endif; ?>
        </nav>
    </div>

    <!-- ============ MY PROFILE TAB ============ -->
    <div id="profile-content" role="tabpanel" aria-labelledby="profile-tab" class="<?php echo $activeTab !== 'profile' ? 'hidden' : ''; ?> animate-fadeIn space-y-6">
        <?php echo $profileMessage . $passwordMessage; ?>
        
        <!-- Profile Header Card -->
        <div class="bg-gradient-to-r from-cms-red to-red-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 flex-1">
                    <!-- Avatar Circle -->
                    <div class="bg-white text-cms-red rounded-full w-20 h-20 flex items-center justify-center font-bold text-2xl flex-shrink-0">
                        <?php echo strtoupper(substr($currentUser['first_name'][0] ?? 'U', 0, 1) . substr($currentUser['last_name'][0] ?? 'U', 0, 1)); ?>
                    </div>
                    <!-- User Info -->
                    <div class="flex-1 pt-2">
                        <h2 class="text-2xl font-bold mb-1"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h2>
                        <p class="text-red-100 mb-3"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-white bg-opacity-30 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                <i class="bi bi-briefcase-fill"></i>
                                <?php echo htmlspecialchars($userRole); ?>
                            </span>
                            <span class="bg-white bg-opacity-30 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                <i class="bi bi-building"></i>
                                <?php echo htmlspecialchars($currentUser['department'] ?? 'N/A'); ?>
                            </span>
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Edit Profile Button -->
                <button onclick="toggleEditMode()" class="bg-white text-cms-red hover:bg-gray-100 px-6 py-2 rounded-lg font-semibold transition-colors flex items-center gap-2 flex-shrink-0 mt-2">
                    <i class="bi bi-pencil-fill"></i>
                    Edit Profile
                </button>
            </div>
        </div>
        
        <!-- Profile Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Documents</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">2</p>
                    </div>
                    <i class="bi bi-file-earmark text-cms-red text-3xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Activities</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">117</p>
                    </div>
                    <i class="bi bi-graph-up text-cms-red text-3xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Member Since</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">Nov 2025</p>
                    </div>
                    <i class="bi bi-calendar text-cms-red text-3xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Last Active</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">13m ago</p>
                    </div>
                    <i class="bi bi-clock-history text-cms-red text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Profile Information Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">
            <!-- Left Column - Personal Information -->
            <div class="lg:col-span-2 w-full overflow-x-hidden">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <i class="bi bi-person-circle text-cms-red text-2xl"></i>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Personal Information</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage your account details</p>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <div id="profile-view-mode" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Full Name</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Username</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">admin</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Email Address</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Phone Number</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['phone'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Department</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['department'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Position</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['position'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="profile-edit-mode" class="hidden space-y-4">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                                    <input type="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white cursor-not-allowed" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                    <input type="text" name="department" value="<?php echo htmlspecialchars($currentUser['department'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Position</label>
                                    <input type="text" name="position" value="<?php echo htmlspecialchars($currentUser['position'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent">
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="bg-cms-red hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                                    <i class="bi bi-check-circle"></i>
                                    Save Changes
                                </button>
                                <button type="button" onclick="toggleEditMode()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </button>
                            </div>

                            <!-- Change Password Section (In Edit Mode) -->
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-6">
                                    <i class="bi bi-lock-fill text-cms-red text-lg"></i>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Update Password</h3>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                                        <input type="password" name="current_password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                        <input type="password" name="new_password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent" required>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be at least 8 characters</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent" required>
                                    </div>

                                    <button type="submit" name="action" value="change_password" class="bg-cms-red hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                                        <i class="bi bi-lock"></i>
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Security, Quick Links & Recent Activity -->
            <div class="space-y-6 w-full overflow-x-hidden">
                <!-- Account Security -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <i class="bi bi-shield-check text-cms-red text-2xl"></i>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Account Security</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="javascript:void(0)" onclick="toggleEditMode()" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-lock text-cms-red"></i>
                                <div>
                                    <p class="font-semibold text-sm">Change Password</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Update your password</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-gray-400"></i>
                        </a>
                        <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-shield-exclamation text-cms-red"></i>
                                <div>
                                    <p class="font-semibold text-sm">Two-Factor Auth</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Not enabled</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-gray-400"></i>
                        </a>
                        <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock-history text-cms-red"></i>
                                <div>
                                    <p class="font-semibold text-sm">Login History</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">View recent logins</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-gray-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <i class="bi bi-link-45deg text-cms-red text-2xl"></i>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick Links</h3>
                    </div>
                    
                    <div class="space-y-2">
                        <a href="javascript:switchTab('settings', 'User Management')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                            <i class="bi bi-gear text-cms-red"></i>
                            <span class="text-sm font-semibold">Account Settings</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                <i class="bi bi-file-earmark text-cms-red"></i>
                                <span class="text-sm font-semibold">My Documents</span>
                            </a>
                            <a href="javascript:switchTab('help', 'User Management')" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-300">
                                <i class="bi bi-question-circle text-cms-red"></i>
                                <span class="text-sm font-semibold">Help Center</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- ============ SETTINGS TAB ============ -->
    <div id="settings-content" role="tabpanel" aria-labelledby="settings-tab" class="<?php echo $activeTab !== 'settings' ? 'hidden' : ''; ?> animate-fadeIn">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <i class="bi bi-gear-fill text-cms-red text-2xl"></i>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Account Settings</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage your account preferences</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Email Notifications</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Receive email updates about important activities</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cms-red rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cms-red"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Login Alerts</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Get notified of new login attempts</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cms-red rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cms-red"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Activity Summary</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Weekly summary of your system activities</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cms-red rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cms-red"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ HELP & SUPPORT TAB ============ -->
    <div id="help-content" role="tabpanel" aria-labelledby="help-tab" class="<?php echo $activeTab !== 'help' ? 'hidden' : ''; ?> animate-fadeIn space-y-6">
        <!-- FAQs Section -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <i class="bi bi-question-circle-fill text-cms-red text-2xl"></i>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Frequently Asked Questions</h2>
            </div>

            <div class="space-y-3">
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <summary class="font-semibold text-gray-900 dark:text-white flex items-center justify-between">How do I reset my password? <i class="bi bi-chevron-down"></i></summary>
                    <p class="text-gray-600 dark:text-gray-400 mt-4">Go to your profile and scroll to "Change Password" to update it. You'll need your current password.</p>
                </details>

                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <summary class="font-semibold text-gray-900 dark:text-white flex items-center justify-between">How do I update my profile? <i class="bi bi-chevron-down"></i></summary>
                    <p class="text-gray-600 dark:text-gray-400 mt-4">Click "Edit Profile" button on your profile tab to update your information.</p>
                </details>

                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <summary class="font-semibold text-gray-900 dark:text-white flex items-center justify-between">What are password requirements? <i class="bi bi-chevron-down"></i></summary>
                    <p class="text-gray-600 dark:text-gray-400 mt-4">Passwords must be at least 8 characters. Use uppercase, lowercase, numbers, and symbols for better security.</p>
                </details>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <i class="bi bi-envelope-fill text-cms-red text-2xl"></i>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Contact Support</h2>
            </div>

            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Subject</label>
                    <input type="text" placeholder="Describe your issue" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
                    <textarea placeholder="Describe your issue in detail..." rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red focus:border-transparent"></textarea>
                </div>

                <button type="submit" class="bg-cms-red hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-send"></i>
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- ============ ALL USERS TAB (Admin Only) ============ -->
    <?php if ($isAdmin): ?>
    <div id="all-users-content" role="tabpanel" aria-labelledby="all-users-tab" class="<?php echo $activeTab !== 'all-users' ? 'hidden' : ''; ?> animate-fadeIn">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <i class="bi bi-people-fill text-cms-red text-2xl"></i>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">All Users</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage all registered users</p>
                    </div>
                </div>
                <button onclick="alert('Create account feature coming soon')" class="bg-cms-red hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Create Account
                </button>
            </div>

            <!-- Users Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($dummyAccounts as $user): ?>
                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="bg-cms-red text-white rounded-full w-12 h-12 flex items-center justify-center font-bold text-sm">
                                <?php echo strtoupper(substr($user['first_name'][0], 0, 1) . substr($user['last_name'][0], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>

                    <div class="space-y-2 text-sm mb-4">
                        <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Position:</span> <?php echo htmlspecialchars($user['position']); ?></p>
                        <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Department:</span> <?php echo htmlspecialchars($user['department']); ?></p>
                        <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Role:</span> <?php echo htmlspecialchars($user['role']); ?></p>
                    </div>

                    <button onclick="editUser(<?php echo $user['user_id']; ?>)" class="w-full bg-cms-red hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <i class="bi bi-pencil-square"></i>
                        Edit User
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tabId, moduleName) {
    document.querySelectorAll('[role="tabpanel"]').forEach(panel => panel.classList.add('hidden'));
    const selectedPanel = document.getElementById(tabId + '-content');
    if (selectedPanel) selectedPanel.classList.remove('hidden');
    
    document.querySelectorAll('[role="tab"]').forEach(tab => {
        if (tab.id === tabId + '-tab') {
            tab.classList.remove('text-gray-600', 'dark:text-gray-400', 'border-transparent');
            tab.classList.add('text-cms-red', 'border-cms-red');
            tab.setAttribute('aria-selected', 'true');
        } else {
            tab.classList.remove('text-cms-red', 'border-cms-red');
            tab.classList.add('text-gray-600', 'dark:text-gray-400', 'border-transparent');
            tab.setAttribute('aria-selected', 'false');
        }
    });
    
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
}

function toggleEditMode() {
    document.getElementById('profile-view-mode').classList.toggle('hidden');
    document.getElementById('profile-edit-mode').classList.toggle('hidden');
}

function scrollToPassword() {
    document.getElementById('password-section').scrollIntoView({ behavior: 'smooth' });
}

function editUser(userId) {
    alert('Edit user ' + userId + ' - Feature coming soon with database integration');
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'profile';
    switchTab(activeTab, 'User Management');
});
</script>

<?php include '../../../public/includes/footer.php'; ?>
