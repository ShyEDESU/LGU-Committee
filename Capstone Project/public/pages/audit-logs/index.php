<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once '../../../config/database.php';
require_once '../../../app/helpers/AuditHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

// Check if user has permission to view audit logs
$userId = $_SESSION['user_id'];
if (!canViewModule($userId, 'audit_logs')) {
    header('Location: ../../../dashboard.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Audit Logs';

// Filters
$filters = [
    'action' => $_GET['action'] ?? '',
    'user_search' => $_GET['user'] ?? '',
    'date' => $_GET['date'] ?? ''
];

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$auditLogs = getAuditLogs($limit, $offset, $filters);
$totalLogs = getAuditLogsCount($filters);
$totalPages = ceil($totalLogs / $limit);

// Include shared header
include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Track all system activities and changes</p>
</div>

<!-- Filters -->
<div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-100 border border-gray-200 dark:border-gray-700">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action Type</label>
            <select name="action" onchange="this.form.submit()"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Actions</option>
                <option value="create" <?php echo $filters['action'] === 'create' ? 'selected' : ''; ?>>Create</option>
                <option value="update" <?php echo $filters['action'] === 'update' ? 'selected' : ''; ?>>Update</option>
                <option value="delete" <?php echo $filters['action'] === 'delete' ? 'selected' : ''; ?>>Delete</option>
                <option value="login" <?php echo $filters['action'] === 'login' ? 'selected' : ''; ?>>Login</option>
                <option value="logout" <?php echo $filters['action'] === 'logout' ? 'selected' : ''; ?>>Logout</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User</label>
            <input type="text" name="user" value="<?php echo htmlspecialchars($filters['user_search']); ?>"
                placeholder="Search by user..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($filters['date']); ?>"
                onchange="this.form.submit()"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                <i class="bi bi-search mr-2"></i>Filter
            </button>
            <a href="index.php"
                class="flex-1 text-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg font-semibold transition">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Audit Logs Table -->
<div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-200 border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Timestamp
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        User</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Action
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Description</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        IP
                        Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($auditLogs)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="bi bi-inbox text-4xl mb-2 block"></i>
                            No audit logs found matching your criteria
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <?php echo date('M j, Y g:i A', strtotime($log['timestamp'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $action = strtolower($log['action']);
                                $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                switch ($action) {
                                    case 'create':
                                        $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                                        break;
                                    case 'update':
                                    case 'edit':
                                    case 'update_profile':
                                    case 'update_picture':
                                        $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                        break;
                                    case 'delete':
                                    case 'remove_picture':
                                        $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                        break;
                                    case 'login':
                                        $badgeClass = 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
                                        break;
                                    case 'logout':
                                        $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                        break;
                                    case 'change_password':
                                        $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
                                        break;
                                }
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($log['action']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <?php echo htmlspecialchars($log['description']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <?php echo htmlspecialchars($log['ip_address']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div
        class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-700 dark:text-gray-400">
            Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to
            <span class="font-medium"><?php echo min($offset + $limit, $totalLogs); ?></span> of
            <span class="font-medium"><?php echo $totalLogs; ?></span> logs
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Previous
                </a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>