<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once '../../../config/database.php';
require_once 'user_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageTitle = 'User Management';

// Check if user is admin
$userRoleLower = strtolower($_SESSION['user_role'] ?? 'User');
$isAdmin = ($userRoleLower === 'admin' || $userRoleLower === 'administrator' || $userRoleLower === 'super admin' || $userRoleLower === 'super administrator');

// Redirect non-admins
if (!$isAdmin) {
    header('Location: ../my-profile/index.php');
    exit();
}

// Get users from database
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$result = getUsers($search, $roleFilter, '', $statusFilter, $page, 20);
$users = $result['users'];
$totalPages = $result['totalPages'];

// Get filter options
$roles = getRoles();
$departments = getDepartments();

// Calculate stats
$totalUsers = $result['total'];
$activeUsers = 0;
$adminCount = 0;

foreach ($users as $user) {
    if ($user['status'] === 'active') $activeUsers++;
    $rLower = strtolower($user['role_name']);
    if ($rLower === 'admin' || $rLower === 'administrator' || $rLower === 'super admin' || $rLower === 'super administrator') $adminCount++;
}

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Manage system users and permissions</p>
</div>

<!-- User Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Total Users</h3>
            <i class="bi bi-people text-2xl text-red-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $totalUsers; ?></p>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Registered accounts</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Active Users</h3>
            <i class="bi bi-check-circle text-2xl text-green-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $activeUsers; ?></p>
        <p class="text-sm text-green-600 mt-2">Currently active</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Administrators</h3>
            <i class="bi bi-shield-check text-2xl text-red-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $adminCount; ?></p>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Admin accounts</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-400">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">This Month</h3>
            <i class="bi bi-person-plus text-2xl text-purple-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php 
            $thisMonth = count(array_filter($users, function($u) {
                return date('Y-m', strtotime($u['created_at'])) === date('Y-m');
            }));
            echo $thisMonth;
            ?>
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">New users</p>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-500">
    <form method="GET" id="searchForm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Users</label>
                <input type="text" name="search" id="searchUsers" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by name or email..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                <select name="role" id="filterRole"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role['role_name']); ?>" 
                            <?php echo $roleFilter === $role['role_name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" id="filterStatus"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" 
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-search"></i>
                    Search
                </button>
                <button type="button" onclick="clearFilters()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-600">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">All Users</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage all registered users</p>
            </div>
            <div class="flex gap-2">
                <button onclick="exportCSV()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    Export CSV
                </button>
                <button onclick="openCreateModal()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Add User
                </button>
            </div>
        </div>
        
        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <span id="selectedCount">0</span> selected
                </span>
            </div>
            <button onclick="bulkDelete()" 
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="bi bi-trash"></i>
                Delete Selected
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No users found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    foreach ($users as $user): 
                        // Check permissions
                        $permissions = canEditUser($userId, $user['user_id'], $userRole, $user['role_name']);
                        $canDelete = $permissions['can_delete'];
                        $canEdit = $permissions['can_edit'];
                        
                        // Check if profile picture exists
                        $profilePicPath = '';
                        if (!empty($user['profile_picture'])) {
                            $fullPath = __DIR__ . '/../../' . $user['profile_picture'];
                            if (file_exists($fullPath)) {
                                $profilePicPath = '../../' . $user['profile_picture'];
                            }
                        }
                        if (empty($profilePicPath)) {
                            $profilePicPath = '../../assets/images/default-avatar.png';
                        }
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4">
                                <?php if ($canDelete): ?>
                                    <input type="checkbox" class="user-checkbox w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500" 
                                           value="<?php echo $user['user_id']; ?>" onchange="updateBulkActions()">
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($profilePicPath); ?>" 
                                         alt="Profile" 
                                         class="w-10 h-10 rounded-full object-cover mr-3"
                                         onerror="this.src='../../assets/images/default-avatar.png'">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($user['position'] ?? 'No position'); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    <?php echo strtolower($user['role_name']) === 'administrator' || strtolower($user['role_name']) === 'super administrator' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-red-100 text-red-800 dark:bg-blue-900 dark:text-blue-200'; ?>">
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <?php if ($canEdit): ?>
                                    <button onclick="editUser(<?php echo $user['user_id']; ?>)"
                                        class="text-red-600 hover:text-red-700 dark:text-blue-400" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot edit this user">
                                        <i class="bi bi-pencil"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <button onclick="viewUser(<?php echo $user['user_id']; ?>)"
                                    class="text-green-600 hover:text-green-700 dark:text-green-400" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                <?php if ($canDelete): ?>
                                    <button onclick="deleteUser(<?php echo $user['user_id']; ?>)"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot delete this user">
                                        <i class="bi bi-trash"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing page <span class="font-medium"><?php echo $page; ?></span> of 
                <span class="font-medium"><?php echo $totalPages; ?></span>
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>"
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>"
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'modals.php'; ?>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>
