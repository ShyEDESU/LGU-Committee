<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'User';
$pageTitle = 'Committee Profiles';

// Include shared header
include '../../includes/header.php';

// Hardcoded committee data
$committees = [
    [
        'id' => 1,
        'name' => 'Committee on Finance',
        'type' => 'Standing',
        'chair' => 'Hon. Maria Santos',
        'members' => 7,
        'jurisdiction' => 'Budget, appropriations, revenue measures, and financial matters',
        'status' => 'Active',
        'meetings_held' => 12,
        'pending_referrals' => 3
    ],
    [
        'id' => 2,
        'name' => 'Committee on Health',
        'type' => 'Standing',
        'chair' => 'Hon. Juan Dela Cruz',
        'members' => 5,
        'jurisdiction' => 'Public health, sanitation, and medical services',
        'status' => 'Active',
        'meetings_held' => 8,
        'pending_referrals' => 2
    ],
    [
        'id' => 3,
        'name' => 'Committee on Education',
        'type' => 'Standing',
        'chair' => 'Hon. Ana Reyes',
        'members' => 6,
        'jurisdiction' => 'Education, schools, and learning institutions',
        'status' => 'Active',
        'meetings_held' => 10,
        'pending_referrals' => 5
    ],
    [
        'id' => 4,
        'name' => 'Committee on Infrastructure',
        'type' => 'Standing',
        'chair' => 'Hon. Pedro Garcia',
        'members' => 8,
        'jurisdiction' => 'Public works, roads, bridges, and infrastructure development',
        'status' => 'Active',
        'meetings_held' => 15,
        'pending_referrals' => 4
    ],
    [
        'id' => 5,
        'name' => 'Committee on Public Safety',
        'type' => 'Standing',
        'chair' => 'Hon. Rosa Martinez',
        'members' => 6,
        'jurisdiction' => 'Police, fire protection, and disaster preparedness',
        'status' => 'Active',
        'meetings_held' => 9,
        'pending_referrals' => 1
    ],
    [
        'id' => 6,
        'name' => 'Special Committee on COVID-19 Response',
        'type' => 'Special',
        'chair' => 'Hon. Carlos Ramos',
        'members' => 5,
        'jurisdiction' => 'Pandemic response and recovery measures',
        'status' => 'Active',
        'meetings_held' => 6,
        'pending_referrals' => 2
    ]
];

// Filter and search
$search = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

if ($search || $typeFilter || $statusFilter) {
    $committees = array_filter($committees, function($committee) use ($search, $typeFilter, $statusFilter) {
        $matchesSearch = empty($search) || 
            stripos($committee['name'], $search) !== false || 
            stripos($committee['chair'], $search) !== false;
        $matchesType = empty($typeFilter) || $committee['type'] === $typeFilter;
        $matchesStatus = empty($statusFilter) || $committee['status'] === $statusFilter;
        
        return $matchesSearch && $matchesType && $matchesStatus;
    });
}
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Profiles</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage committee information and membership</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                <i class="bi bi-printer"></i>
                <span class="hidden sm:inline ml-2">Print</span>
            </button>
            <button onclick="exportData()" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                <i class="bi bi-download"></i>
                <span class="hidden sm:inline ml-2">Export</span>
            </button>
            <a href="create.php" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition flex items-center space-x-2">
                <i class="bi bi-plus-lg"></i>
                <span>New Committee</span>
            </a>
        </div>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name or chair..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                <option value="">All Types</option>
                <option value="Standing" <?php echo $typeFilter === 'Standing' ? 'selected' : ''; ?>>Standing</option>
                <option value="Special" <?php echo $typeFilter === 'Special' ? 'selected' : ''; ?>>Special</option>
                <option value="Ad Hoc" <?php echo $typeFilter === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                <option value="">All Status</option>
                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="md:col-span-4 flex justify-end space-x-2">
            <a href="index.php" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Committees</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($committees); ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                <i class="bi bi-building text-2xl text-blue-600 dark:text-blue-400"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Standing Committees</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($committees, fn($c) => $c['type'] === 'Standing')); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                <i class="bi bi-bookmark-check text-2xl text-green-600 dark:text-green-400"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Members</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo array_sum(array_column($committees, 'members')); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-lg">
                <i class="bi bi-people text-2xl text-purple-600 dark:text-purple-400"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending Referrals</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo array_sum(array_column($committees, 'pending_referrals')); ?>
                </p>
            </div>
            <div class="bg-orange-100 dark:bg-orange-900 p-3 rounded-lg">
                <i class="bi bi-inbox text-2xl text-orange-600 dark:text-orange-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Committee Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($committees as $committee): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </h3>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            <?php echo $committee['type'] === 'Standing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                       ($committee['type'] === 'Special' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'); ?>">
                            <?php echo $committee['type']; ?>
                        </span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <?php echo $committee['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3 mb-4">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="bi bi-person-badge w-5"></i>
                    <span class="ml-2"><?php echo htmlspecialchars($committee['chair']); ?></span>
                </div>
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="bi bi-people w-5"></i>
                    <span class="ml-2"><?php echo $committee['members']; ?> Members</span>
                </div>
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="bi bi-calendar-check w-5"></i>
                    <span class="ml-2"><?php echo $committee['meetings_held']; ?> Meetings Held</span>
                </div>
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="bi bi-inbox w-5"></i>
                    <span class="ml-2"><?php echo $committee['pending_referrals']; ?> Pending Referrals</span>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                <?php echo htmlspecialchars($committee['jurisdiction']); ?>
            </p>
            
            <div class="flex space-x-2">
                <a href="view.php?id=<?php echo $committee['id']; ?>" 
                   class="flex-1 px-4 py-2 bg-cms-red hover:bg-cms-dark text-white text-center rounded-lg transition text-sm font-semibold">
                    View Details
                </a>
                <a href="edit.php?id=<?php echo $committee['id']; ?>" 
                   class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($committees)): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
    <i class="bi bi-inbox text-6xl text-gray-400 mb-4"></i>
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Committees Found</h3>
    <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your search or filters</p>
    <a href="index.php" class="inline-block px-6 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
        Clear Filters
    </a>
</div>
<?php endif; ?>

<script>
    function exportData() {
        alert('Export functionality will be implemented soon!');
    }
</script>

<?php include '../../includes/footer.php'; ?>
