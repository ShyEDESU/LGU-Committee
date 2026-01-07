<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageTitle = 'User Management';

// Fetch user's role from database (JOIN with roles table)
$roleQuery = "SELECT r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?";
$roleStmt = $conn->prepare($roleQuery);
$roleStmt->bind_param("i", $userId);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$currentUserData = $roleResult->fetch_assoc();
$roleStmt->close();

$userRole = $currentUserData['role_name'] ?? 'User';

// Check if user is admin (accept both 'Admin' and 'Administrator')
$isAdmin = (strtolower($userRole) === 'admin' || strtolower($userRole) === 'administrator');

// DEBUG: Remove this after testing
// echo "User Role from DB: " . $userRole . "<br>";
// echo "Is Admin: " . ($isAdmin ? 'YES' : 'NO') . "<br>";
// exit();

// Redirect non-admins to My Profile
if (!$isAdmin) {
    header('Location: ../my-profile/index.php');
    exit();
}

// Dummy user data
$users = [
    ['user_id' => 1, 'email' => 'admin@lgu.gov.ph', 'first_name' => 'John', 'last_name' => 'Administrator', 'phone' => '09123456789', 'department' => 'IT Department', 'position' => 'Administrator', 'is_active' => true, 'created_at' => '2025-01-15', 'role' => 'Administrator'],
    ['user_id' => 2, 'email' => 'mary@lgu.gov.ph', 'first_name' => 'Mary', 'last_name' => 'Johnson', 'phone' => '09234567890', 'department' => 'Legislative Division', 'position' => 'Legislative Officer', 'is_active' => true, 'created_at' => '2025-02-10', 'role' => 'Officer'],
    ['user_id' => 3, 'email' => 'robert@lgu.gov.ph', 'first_name' => 'Robert', 'last_name' => 'Martinez', 'phone' => '09345678901', 'department' => 'Committee Services', 'position' => 'Committee Coordinator', 'is_active' => true, 'created_at' => '2025-03-05', 'role' => 'Officer'],
    ['user_id' => 4, 'email' => 'angela@lgu.gov.ph', 'first_name' => 'Angela', 'last_name' => 'Santos', 'phone' => '09456789012', 'department' => 'Research Division', 'position' => 'Research Analyst', 'is_active' => true, 'created_at' => '2025-04-12', 'role' => 'Analyst'],
    ['user_id' => 5, 'email' => 'carlos@lgu.gov.ph', 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'phone' => '09567890123', 'department' => 'IT Department', 'position' => 'IT Support', 'is_active' => false, 'created_at' => '2025-05-20', 'role' => 'Support'],
];

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
    <p class="text-gray-600 mt-1">Manage system users and permissions</p>
</div>

<!-- User Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Total Users</h3>
            <i class="bi bi-people text-2xl text-blue-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?php echo count($users); ?></p>
        <p class="text-sm text-gray-600 mt-2">Registered accounts</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Active Users</h3>
            <i class="bi bi-check-circle text-2xl text-green-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?>
        </p>
        <p class="text-sm text-green-600 mt-2">Currently active</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Administrators</h3>
            <i class="bi bi-shield-check text-2xl text-red-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">
            <?php echo count(array_filter($users, fn($u) => $u['role'] === 'Administrator')); ?>
        </p>
        <p class="text-sm text-gray-600 mt-2">Admin accounts</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-400">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">New This Month</h3>
            <i class="bi bi-person-plus text-2xl text-purple-600"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900">2</p>
        <p class="text-sm text-gray-600 mt-2">Recent additions</p>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-500">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
            <input type="text" id="searchUsers" placeholder="Search by name or email..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent text-base">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select id="filterRole"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent">
                <option value="">All Roles</option>
                <option value="Administrator">Administrator</option>
                <option value="Officer">Officer</option>
                <option value="Analyst">Analyst</option>
                <option value="Support">Support</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select id="filterStatus"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-600">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">All Users</h2>
            <p class="text-sm text-gray-600">Manage all registered users</p>
        </div>
        <button onclick="alert('Create user feature coming soon')"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            Add User
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div
                                    class="bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold text-sm mr-3">
                                    <?php echo strtoupper(substr($user['first_name'][0], 0, 1) . substr($user['last_name'][0], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['position']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $user['role'] === 'Administrator' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($user['department']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button onclick="editUser(<?php echo $user['user_id']; ?>)"
                                class="text-blue-600 hover:text-blue-700" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="viewUser(<?php echo $user['user_id']; ?>)"
                                class="text-green-600 hover:text-green-700" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button onclick="deleteUser(<?php echo $user['user_id']; ?>)"
                                class="text-red-600 hover:text-red-700" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($users); ?></span>
            of <span class="font-medium"><?php echo count($users); ?></span> users
        </div>
        <div class="flex gap-2">
            <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-100">Previous</button>
            <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-100">Next</button>
        </div>
    </div>
</div>

<script>
    function editUser(userId) {
        alert('Edit user ' + userId + ' - Feature coming soon');
    }

    function viewUser(userId) {
        alert('View user ' + userId + ' details - Feature coming soon');
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            alert('Delete user ' + userId + ' - Feature coming soon');
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>