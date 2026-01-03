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
    $committees = array_filter($committees, function ($committee) use ($search, $typeFilter, $statusFilter) {
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
            <button onclick="window.print()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-printer"></i>
                <span class="hidden sm:inline ml-2">Print</span>
            </button>
            <button onclick="exportData()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-download"></i>
                <span class="hidden sm:inline ml-2">Export</span>
            </button>
            <button onclick="openModal('createModal')"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center space-x-2">
                <i class="bi bi-plus-lg"></i>
                <span>New Committee</span>
            </button>
        </div>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by name or chair..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select name="type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                <option value="">All Types</option>
                <option value="Standing" <?php echo $typeFilter === 'Standing' ? 'selected' : ''; ?>>Standing</option>
                <option value="Special" <?php echo $typeFilter === 'Special' ? 'selected' : ''; ?>>Special</option>
                <option value="Ad Hoc" <?php echo $typeFilter === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                <option value="">All Status</option>
                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="md:col-span-4 flex justify-end space-x-2">
            <a href="index.php" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Committees</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($committees); ?></p>
            </div>
            <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3">
                <i class="bi bi-building text-red-600 dark:text-red-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Active Committees</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($committees, fn($c) => $c['status'] === 'Active')); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-check-circle text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Members</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo array_sum(array_column($committees, 'members')); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-people text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending Referrals</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo array_sum(array_column($committees, 'pending_referrals')); ?>
                </p>
            </div>
            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3">
                <i class="bi bi-inbox text-orange-600 dark:text-orange-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Committee Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $delay = 100;
    foreach ($committees as $committee):
        ?>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden animate-fade-in-up animation-delay-<?php echo $delay; ?>">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                            <?php echo htmlspecialchars($committee['name']); ?>
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php echo $committee['type'] === 'Standing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                    ($committee['type'] === 'Special' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' :
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'); ?>">
                                <?php echo $committee['type']; ?>
                            </span>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <?php echo $committee['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-person-badge w-5"></i>
                        <span class="ml-2"><?php echo htmlspecialchars($committee['chair']); ?></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-people w-5"></i>
                        <span class="ml-2"><?php echo $committee['members']; ?> Members</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-calendar-check w-5"></i>
                        <span class="ml-2"><?php echo $committee['meetings_held']; ?> Meetings Held</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-inbox w-5"></i>
                        <span class="ml-2"><?php echo $committee['pending_referrals']; ?> Pending Referrals</span>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                    <?php echo htmlspecialchars($committee['jurisdiction']); ?>
                </p>

                <div class="flex space-x-2">
                    <button onclick="viewCommittee(<?php echo $committee['id']; ?>)"
                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-center rounded-lg transition text-sm font-semibold">
                        <i class="bi bi-eye mr-1"></i> View Details
                    </button>
                    <button onclick="editCommittee(<?php echo $committee['id']; ?>)"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="deleteCommittee(<?php echo $committee['id']; ?>)"
                        class="px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg transition">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
        $delay += 100;
        if ($delay > 900)
            $delay = 100; // Reset after 9 cards
    endforeach; ?>
</div>

<?php if (empty($committees)): ?>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Committees Found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your search or filters</p>
        <a href="index.php" class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            Clear Filters
        </a>
    </div>
<?php endif; ?>

<!-- View Committee Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto animate-fade-in-up">
        <div
            class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white" id="viewModalTitle">Committee Details</h2>
            <button onclick="closeModal('viewModal')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="viewModalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<!-- Edit Committee Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-fade-in-up">
        <div
            class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Committee</h2>
            <button onclick="closeModal('editModal')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <form class="p-6" id="editForm" onsubmit="saveCommittee(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Committee
                        Name</label>
                    <input type="text" id="editName" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                    <select id="editType" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="Standing">Standing</option>
                        <option value="Special">Special</option>
                        <option value="Ad Hoc">Ad Hoc</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="editStatus" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Chairperson</label>
                    <input type="text" id="editChair" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jurisdiction</label>
                    <textarea id="editJurisdiction" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('editModal')"
                    class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg mr-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full animate-bounce-in">
        <div class="p-6">
            <div
                class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Delete Committee?</h3>
            <p class="text-gray-600 dark:text-gray-400 text-center mb-6" id="deleteMessage">
                Are you sure you want to delete this committee? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeModal('deleteModal')"
                    class="flex-1 px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="flex-1 btn-danger">
                    <i class="bi bi-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Committee Modal -->
<div id="createModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-fade-in-up">
        <div
            class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Committee</h2>
            <button onclick="closeModal('createModal')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <form class="p-6" id="createForm" onsubmit="createCommittee(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="createName" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base"
                        placeholder="e.g., Committee on Finance">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Type <span class="text-red-600">*</span>
                    </label>
                    <select id="createType" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Standing">Standing</option>
                        <option value="Special">Special</option>
                        <option value="Ad Hoc">Ad Hoc</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select id="createStatus" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Chairperson <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="createChair" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base"
                        placeholder="e.g., Hon. Maria Santos">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jurisdiction <span class="text-red-600">*</span>
                    </label>
                    <textarea id="createJurisdiction" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Describe the committee's areas of responsibility..."></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('createModal')"
                    class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-plus-lg mr-2"></i> Create Committee
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const committeesData = <?php echo json_encode($committees); ?>;
    let currentCommitteeId = null;

    function viewCommittee(id) {
        const committee = committeesData.find(c => c.id === id);
        if (!committee) return;

        document.getElementById('viewModalTitle').textContent = committee.name;
        document.getElementById('viewModalContent').innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                    <p class="text-gray-900 dark:text-white font-semibold">${committee.type}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full ${committee.status === 'Active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'}">${committee.status}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Chairperson</label>
                    <p class="text-gray-900 dark:text-white font-semibold">${committee.chair}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Members</label>
                    <p class="text-gray-900 dark:text-white font-semibold">${committee.members}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Jurisdiction</label>
                    <p class="text-gray-900 dark:text-white">${committee.jurisdiction}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Meetings Held</label>
                    <p class="text-gray-900 dark:text-white font-semibold">${committee.meetings_held}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pending Referrals</label>
                    <p class="text-gray-900 dark:text-white font-semibold">${committee.pending_referrals}</p>
                </div>
            </div>
        `;
        openModal('viewModal');
    }

    function editCommittee(id) {
        const committee = committeesData.find(c => c.id === id);
        if (!committee) return;

        currentCommitteeId = id;
        document.getElementById('editName').value = committee.name;
        document.getElementById('editType').value = committee.type;
        document.getElementById('editStatus').value = committee.status;
        document.getElementById('editChair').value = committee.chair;
        document.getElementById('editJurisdiction').value = committee.jurisdiction;
        openModal('editModal');
    }

    function deleteCommittee(id) {
        const committee = committeesData.find(c => c.id === id);
        if (!committee) return;

        currentCommitteeId = id;
        document.getElementById('deleteMessage').textContent =
            `Are you sure you want to delete "${committee.name}"? This action cannot be undone.`;
        openModal('deleteModal');
    }

    function saveCommittee(event) {
        event.preventDefault();
        // In real implementation, this would send data to server
        alert('Committee updated successfully! (This will be connected to database)');
        closeModal('editModal');
    }

    function createCommittee(event) {
        event.preventDefault();
        // In real implementation, this would send data to server
        alert('Committee created successfully! (This will be connected to database)');
        closeModal('createModal');
        document.getElementById('createForm').reset();
    }

    function confirmDelete() {
        // In real implementation, this would delete from database
        alert('Committee deleted successfully! (This will be connected to database)');
        closeModal('deleteModal');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modals on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('viewModal');
            closeModal('editModal');
            closeModal('deleteModal');
            closeModal('createModal');
        }
    });

    function exportData() {
        // Get all committee data
        const committees = committeesData;

        // Create CSV content
        let csv = 'Committee Name,Type,Chair,Members,Status,Meetings Held,Pending Referrals,Jurisdiction\n';

        committees.forEach(committee => {
            csv += `"${committee.name}","${committee.type}","${committee.chair}",${committee.members},"${committee.status}",${committee.meetings_held},${committee.pending_referrals},"${committee.jurisdiction}"\n`;
        });

        // Create download link
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'committee-profiles-' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>

<?php include '../../includes/footer.php'; ?>