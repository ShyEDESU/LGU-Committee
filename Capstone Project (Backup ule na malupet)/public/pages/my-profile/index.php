<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once '../../../config/database.php';
require_once '../../../app/helpers/AuditHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageTitle = 'My Profile';

// Fetch user data from database
$query = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// If user not found, show error but DON'T destroy session
if (!$user) {
    error_log("User not found in database: user_id = " . $userId);
    die("Error: User profile not found. Please contact administrator. User ID: " . $userId);
}

// Get user details
$userName = $user['first_name'] . ' ' . $user['last_name'];
$userEmail = $user['email'];
$userRole = $user['role_name'] ?? $user['user_role'] ?? 'User';
$userPhone = $user['phone'] ?? 'Not set';
$userDepartment = $user['department'] ?? 'Not set';
$userPosition = $user['position'] ?? 'Not set';
$userBio = $user['bio'] ?? '';
$profilePicture = $user['profile_picture'] ?? null;
$userInitials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$memberSince = date('M Y', strtotime($user['created_at'] ?? 'now'));

// Fetch user statistics
$statsQuery = "SELECT
(SELECT COUNT(*) FROM audit_logs WHERE user_id = ?) as activity_count,
(SELECT COUNT(*) FROM legislative_documents WHERE created_by = ?) as doc_count,
(SELECT timestamp FROM audit_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1) as last_active";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iii", $userId, $userId, $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result()->fetch_assoc();
$activityCount = $statsResult['activity_count'] ?? 0;
$docCount = $statsResult['doc_count'] ?? 0;
$lastActive = $statsResult['last_active'] ? date('M j, g:i A', strtotime($statsResult['last_active'])) : 'Never';

// Fetch recent activity
$recentActivities = getAuditLogs(5, 0, ['user_id' => $userId]);

// Fetch login/logout history
$loginHistoryQuery = "SELECT al.*, u.first_name, u.last_name FROM audit_logs al 
                      LEFT JOIN users u ON al.user_id = u.user_id 
                      WHERE al.user_id = ? AND (al.action = 'LOGIN' OR al.action = 'LOGOUT')
                      ORDER BY al.timestamp DESC LIMIT 20";
$loginHistoryStmt = $conn->prepare($loginHistoryQuery);
$loginHistoryStmt->bind_param("i", $userId);
$loginHistoryStmt->execute();
$loginHistoryResult = $loginHistoryStmt->get_result();
$loginHistory = [];
if ($loginHistoryResult) {
    while ($row = $loginHistoryResult->fetch_assoc()) {
        $loginHistory[] = $row;
    }
}
$loginHistoryStmt->close();

// Update session with latest data
$_SESSION['user_name'] = $userName;
$_SESSION['user_email'] = $userEmail;
$_SESSION['user_role'] = $userRole;
if ($profilePicture) {
    $_SESSION['profile_picture'] = $profilePicture;
}

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';

        $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssi", $firstName, $lastName, $phone, $userId);

        if ($updateStmt->execute()) {
            $message = '<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 animate-fade-in"><i
        class="bi bi-check-circle mr-2"></i>Profile updated successfully!</div>';
            // Refresh user data
            $userName = $firstName . ' ' . $lastName;
            $userPhone = $phone;
            $_SESSION['user_name'] = $userName;

            // Log the action
            logAuditAction($userId, 'UPDATE_PROFILE', 'user_profile', "Updated personal profile details");
        } else {
            $message = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"><i
        class="bi bi-x-circle mr-2"></i>Error updating profile</div>';
        }
        $updateStmt->close();

    } elseif ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $message = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"><i
        class="bi bi-x-circle mr-2"></i>Passwords do not match</div>';
        } elseif (strlen($newPassword) < 8) {
            $message = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"><i class="bi bi-x-circle mr-2"></i>Password must be at least 8 characters</div>'
            ;
        } elseif (password_verify($currentPassword, $user['password_hash'])) {
            $newHash = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );
            $pwdQuery = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $pwdStmt = $conn->
                prepare($pwdQuery);
            $pwdStmt->bind_param("si", $newHash, $userId);

            if ($pwdStmt->execute()) {
                $message = '<div
        class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 animate-fade-in"><i
            class="bi bi-check-circle mr-2"></i>Password changed successfully!</div>';

                // Log the action
                logAuditAction($userId, 'CHANGE_PASSWORD', 'user_profile', "Changed account password");
            } else {
                $message = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"><i
            class="bi bi-x-circle mr-2"></i>Error changing password</div>';
            }
            $pwdStmt->close();
        } else {
            $message = '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"><i
            class="bi bi-x-circle mr-2"></i>Current password is incorrect</div>';
        }
    }
}

// Include shared header
include '../../includes/header.php';
?>

<?php echo $message; ?>

<!-- Profile Header Banner -->
<div class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl shadow-xl p-8 mb-6 text-white animate-fade-in">
    <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
        <!-- Profile Picture -->
        <div class="relative">
            <div id="profilePictureContainer"
                class="w-32 h-32 rounded-full bg-white border-4 border-white shadow-lg flex items-center justify-center overflow-hidden">
                <?php if ($profilePicture): ?>
                    <img src="../../<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture"
                        class="w-full h-full object-cover" onerror="this.src='../../assets/images/default-avatar.png'">
                <?php else: ?>
                    <img src="../../assets/images/default-avatar.png" alt="Default Avatar"
                        class="w-full h-full object-cover">
                <?php endif; ?>
            </div>
            <button onclick="openUploadModal()"
                class="absolute bottom-0 right-0 bg-white text-red-600 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-100 transform hover:scale-110 transition-all duration-200 shadow-lg">
                <i class="bi bi-camera-fill"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="flex-1 text-center md:text-left">
            <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($userName); ?></h1>
            <p class="text-red-100 text-lg mb-3"><?php echo htmlspecialchars($userEmail); ?></p>
            <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                    <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($userRole); ?>
                </span>
                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($userDepartment); ?>
                </span>
                <span class="bg-green-400 bg-opacity-90 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> Active
                </span>
            </div>
        </div>

        <!-- Edit Profile Button -->
        <div class="flex items-center">
            <button onclick="toggleEditMode()"
                class="bg-white text-red-600 px-6 py-2 rounded-lg font-medium hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg flex items-center gap-2">
                <i class="bi bi-pencil"></i> Edit Profile
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-100 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 dark:text-gray-400 text-sm">Documents</span>
            <i class="bi bi-file-earmark-text text-2xl text-red-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $docCount; ?></p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 dark:text-gray-400 text-sm">Activities</span>
            <i class="bi bi-activity text-2xl text-green-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $activityCount; ?></p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-300 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 dark:text-gray-400 text-sm">Member Since</span>
            <i class="bi bi-calendar-check text-2xl text-purple-600"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $memberSince; ?></p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-400 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 dark:text-gray-400 text-sm">Last Active</span>
            <i class="bi bi-clock-history text-2xl text-red-600"></i>
        </div>
        <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo $lastActive; ?></p>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Personal Information -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-500 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <i class="bi bi-person-circle text-red-600 text-2xl mr-3"></i>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Personal Information</h2>
            </div>

            <form method="POST" id="profileForm">
                <input type="hidden" name="action" value="update_profile">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First
                            Name</label>
                        <input type="text" name="first_name"
                            value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="last_name"
                            value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address
                            <span class="text-xs text-gray-500">(Cannot be changed)</span></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 cursor-not-allowed text-gray-500 dark:text-gray-400"
                            disabled readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone
                            Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department <span
                                class="text-xs text-gray-500">(Cannot be changed)</span></label>
                        <input type="text" name="department" value="<?php echo htmlspecialchars($userDepartment); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 cursor-not-allowed text-gray-500 dark:text-gray-400"
                            disabled readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position <span
                                class="text-xs text-gray-500">(Cannot be changed)</span></label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($userPosition); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 cursor-not-allowed text-gray-500 dark:text-gray-400"
                            disabled readonly>
                    </div>
                </div>
                <div class="mt-6 hidden" id="saveButtonContainer">
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        <i class="bi bi-check-circle mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Activity Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-600 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <i class="bi bi-clock-history text-red-600 text-2xl mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Activity</h2>
                </div>
                <a href="../audit-logs/index.php"
                    class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium">View
                    All</a>
            </div>

            <div class="space-y-4">
                <?php if (empty($recentActivities)): ?>
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">No recent activity found.</p>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div
                            class="flex items-start gap-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition">
                            <div class="rounded-full p-2 <?php
                            switch ($activity['action']) {
                                case 'UPDATE_PROFILE':
                                    echo 'bg-red-50 dark:bg-red-900/30';
                                    break;
                                case 'CHANGE_PASSWORD':
                                    echo 'bg-yellow-50 dark:bg-yellow-900/30';
                                    break;
                                case 'UPDATE_PICTURE':
                                    echo 'bg-purple-50 dark:bg-purple-900/30';
                                    break;
                                case 'REMOVE_PICTURE':
                                    echo 'bg-red-50 dark:bg-red-900/30';
                                    break;
                                default:
                                    echo 'bg-red-50 dark:bg-red-900/30';
                            }
                            ?>">
                                <i class="bi <?php
                                switch ($activity['action']) {
                                    case 'UPDATE_PROFILE':
                                        echo 'bi-pencil text-red-600 dark:text-red-400';
                                        break;
                                    case 'CHANGE_PASSWORD':
                                        echo 'bi-shield-lock text-yellow-600 dark:text-yellow-400';
                                        break;
                                    case 'UPDATE_PICTURE':
                                        echo 'bi-image text-purple-600 dark:text-purple-400';
                                        break;
                                    case 'REMOVE_PICTURE':
                                        echo 'bi-trash text-red-600 dark:text-red-400';
                                        break;
                                    default:
                                        echo 'bi-clock text-red-600 dark:text-red-400';
                                }
                                ?>"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Security & Quick Links -->
    <div class="space-y-6">
        <!-- Account Security Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-700 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <i class="bi bi-shield-check text-red-600 text-2xl mr-3"></i>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Account Security</h2>
            </div>

            <div class="space-y-4">
                <button onclick="openChangePasswordModal()"
                    class="w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-key text-red-600 dark:text-red-400 text-xl"></i>
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Change Password</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Update your password</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </button>

                <button
                    class="w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-shield-lock text-red-600 dark:text-red-400 text-xl"></i>
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Two-Factor Auth</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Not enabled</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </button>

                <button onclick="openLoginHistoryModal()"
                    class="w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-clock-history text-purple-600 dark:text-purple-400 text-xl"></i>
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Login History</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">View recent logins</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </button>
            </div>
        </div>

        <!-- Quick Links Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-800 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <i class="bi bi-link-45deg text-red-600 text-2xl mr-3"></i>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Quick Links</h2>
            </div>

            <div class="space-y-2">
                <a href="../../dashboard.php"
                    class="flex items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="bi bi-speedometer2 text-red-600 dark:text-red-400 mr-3"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Dashboard</span>
                </a>
                <a href="../user-management/index.php"
                    class="flex items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="bi bi-people text-red-600 dark:text-red-400 mr-3"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">User Management</span>
                </a>
                <a href="../audit-logs/index.php"
                    class="flex items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="bi bi-clock-history text-red-600 dark:text-red-400 mr-3"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Audit Logs</span>
                </a>
                <a href="#"
                    class="flex items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="bi bi-question-circle text-red-600 dark:text-red-400 mr-3"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Help Center</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Upload Profile Picture Modal -->
<div id="uploadPictureModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full animate-fade-in-up border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Update Profile Picture</h2>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6">
            <div class="space-y-4">
                <!-- Current Picture Preview -->
                <div class="flex justify-center">
                    <div id="previewContainer"
                        class="w-40 h-40 rounded-full bg-gray-100 dark:bg-gray-700 border-4 border-gray-200 dark:border-gray-600 flex items-center justify-center overflow-hidden">
                        <?php if ($profilePicture): ?>
                            <img id="currentPreview" src="../../<?php echo htmlspecialchars($profilePicture); ?>"
                                alt="Current" class="w-full h-full object-cover">
                        <?php else: ?>
                            <img id="currentPreview" src="../../assets/images/default-avatar.png" alt="Default"
                                class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Choose New
                        Picture</label>
                    <input type="file" id="profilePictureInput" accept="image/jpeg,image/png"
                        onchange="previewImage(this)"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 dark:file:bg-red-900/30 file:text-red-700 dark:file:text-red-400 hover:file:bg-red-100 cursor-pointer">
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG or PNG only. Max size: 5MB. Max
                        dimensions:
                        2000x2000px</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6">
                    <button onclick="uploadProfilePicture()" id="uploadBtn"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        <i class="bi bi-upload mr-2"></i>Upload
                    </button>
                    <?php if ($profilePicture): ?>
                        <button onclick="removeProfilePicture()"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-300 px-4 py-2 rounded-lg font-semibold transition">
                            <i class="bi bi-trash mr-2"></i>Remove
                        </button>
                    <?php endif; ?>
                    <button onclick="closeUploadModal()"
                        class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-300 px-4 py-2 rounded-lg font-semibold transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full animate-fade-in-up border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Change Password</h2>
                <button onclick="closeChangePasswordModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>

        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="change_password">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current
                        Password</label>
                    <input type="password" name="current_password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 text-base bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                    <input type="password" name="new_password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 text-base bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New
                        Password</label>
                    <input type="password" name="confirm_password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 text-base bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                    <i class="bi bi-key mr-2"></i>Update Password
                </button>
                <button type="button" onclick="closeChangePasswordModal()"
                    class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-300 px-4 py-2 rounded-lg font-semibold transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let isEditMode = false;

    function toggleEditMode() {
        isEditMode = !isEditMode;
        // Only enable first name, last name, and phone
        const editableInputs = document.querySelectorAll('#profileForm input[name="first_name"], #profileForm input[name="last_name"], #profileForm input[name="phone"]');
        const saveBtn = document.getElementById('saveButtonContainer');

        editableInputs.forEach(input => {
            input.disabled = !isEditMode;
            if (isEditMode) {
                input.classList.add('border-red-300', 'focus:border-red-500');
            } else {
                input.classList.remove('border-red-300', 'focus:border-red-500');
            }
        });

        if (isEditMode) {
            saveBtn.classList.remove('hidden');
        } else {
            saveBtn.classList.add('hidden');
        }
    }

    function openChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('hidden');
    }

    // Profile Picture Upload Functions
    function openUploadModal() {
        document.getElementById('uploadPictureModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadPictureModal').classList.add('hidden');
        document.getElementById('profilePictureInput').value = '';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('currentPreview');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    document.getElementById('previewContainer').innerHTML =
                        `<img id="currentPreview" src="${e.target.result}" class="w-full h-full object-cover">`;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function uploadProfilePicture() {
        const input = document.getElementById('profilePictureInput');
        if (!input.files || !input.files[0]) {
            alert('Please select an image first');
            return;
        }

        const file = input.files[0];

        // Client-side validation
        if (!file.type.match('image/(jpeg|jpg|png)')) {
            alert('Invalid file type. Only JPG and PNG images are allowed. GIF files are not supported.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('File size too large. Maximum size is 5MB. Please resize your image.');
            return;
        }

        const formData = new FormData();
        formData.append('profile_picture', file);

        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Uploading...';

        fetch('upload-picture.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update profile picture WITHOUT page reload
                    const newImageUrl = data.picture_url + '?t=' + new Date().getTime();

                    // Update main profile picture
                    const profileContainer = document.getElementById('profilePictureContainer');
                    profileContainer.innerHTML = `<img src="${newImageUrl}" alt="Profile Picture" class="w-full h-full object-cover" onerror="this.src='../../assets/images/default-avatar.png'">`;

                    // Update header profile pictures dynamically
                    const headerImages = document.querySelectorAll('img[alt="Profile"], img[alt="Default Avatar"]');
                    headerImages.forEach(img => {
                        img.src = newImageUrl;
                    });

                    // Close modal and show success message
                    closeUploadModal();
                    alert('Profile picture updated successfully!');

                    // Reset button
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-upload mr-2"></i>Upload';
                } else {
                    // Check if image needs cropping
                    if (data.needs_crop) {
                        alert(data.message + '\n\nPlease use an image editor to resize your image before uploading.');
                    } else {
                        alert(data.message || 'Failed to upload picture');
                    }
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-upload mr-2"></i>Upload';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-upload mr-2"></i>Upload';
            });
    }

    function removeProfilePicture() {
        if (!confirm('Are you sure you want to remove your profile picture?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'remove');

        fetch('upload-picture.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update to default avatar WITHOUT page reload
                    const profileContainer = document.getElementById('profilePictureContainer');
                    profileContainer.innerHTML = `<img src="../../assets/images/default-avatar.png" alt="Default Avatar" class="w-full h-full object-cover">`;

                    // Update header profile pictures to default avatar
                    const headerImages = document.querySelectorAll('img[alt="Profile"], img[alt="Default Avatar"]');
                    headerImages.forEach(img => {
                        img.src = '../../assets/images/default-avatar.png';
                    });

                    closeUploadModal();
                    alert('Profile picture removed successfully!');
                } else {
                    alert(data.message || 'Failed to remove picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
    }

    // Login History Modal Functions
    function openLoginHistoryModal() {
        const modal = document.getElementById('loginHistoryModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLoginHistoryModal() {
        const modal = document.getElementById('loginHistoryModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('loginHistoryModal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeLoginHistoryModal();
                }
            });
        }
    });
</script>

<!-- Login History Modal -->
<div id="loginHistoryModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full my-8">
        <!-- Modal Header -->
        <div
            class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <i class="bi bi-clock-history text-2xl text-purple-600 dark:text-purple-400"></i>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Login History</h2>
            </div>
            <button onclick="closeLoginHistoryModal()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <?php if (empty($loginHistory)): ?>
                <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <i class="bi bi-clock-history text-4xl mb-4 opacity-20 block"></i>
                    No login history records found.
                </div>
            <?php else: ?>
                <div class="p-6">
                    <div
                        class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent dark:before:via-gray-700">
                        <?php foreach ($loginHistory as $entry): ?>
                            <div class="relative flex items-center justify-between md:justify-start md:space-x-4 group">
                                <!-- Dot -->
                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-gray-800 shadow-sm shrink-0 z-10 transition-transform group-hover:scale-110 <?php echo $entry['action'] === 'LOGIN' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'; ?>">
                                    <i
                                        class="bi <?php echo $entry['action'] === 'LOGIN' ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right'; ?> text-base"></i>
                                </div>
                                <!-- Content Card -->
                                <div
                                    class="flex-1 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 ml-2 border border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition-all duration-300 shadow-sm">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <?php echo $entry['action']; ?>
                                                <span
                                                    class="px-2 py-0.5 rounded-md text-[9px] uppercase tracking-wider font-extrabold bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                                    SUCCESS
                                                </span>
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                    <i class="bi bi-cpu text-gray-400"></i>
                                                    <span
                                                        class="font-mono"><?php echo htmlspecialchars($entry['ip_address'] ?? 'Unknown'); ?></span>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                    <i class="bi bi-calendar3 text-gray-400"></i>
                                                    <?php echo date('M j, Y', strtotime($entry['timestamp'])); ?>
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                                    <i class="bi bi-clock text-gray-400"></i>
                                                    <?php echo date('g:i A', strtotime($entry['timestamp'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl text-center">
            <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">
                <i class="bi bi-shield-check mr-1"></i> Security Log • Last 20 Sessions
            </p>
        </div>
    </div>
</div>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>