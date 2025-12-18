<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Members';
include '../../includes/header.php';

// Hardcoded members data across all committees
$members = [
    ['id' => 1, 'name' => 'Hon. Maria Santos', 'committee' => 'Finance', 'role' => 'Chairperson', 'district' => 'District 1', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 2, 'name' => 'Hon. Roberto Cruz', 'committee' => 'Finance', 'role' => 'Vice-Chair', 'district' => 'District 2', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 3, 'name' => 'Hon. Linda Reyes', 'committee' => 'Finance', 'role' => 'Member', 'district' => 'District 3', 'status' => 'Active', 'joined' => '2023-02-01'],
    ['id' => 4, 'name' => 'Hon. Juan Dela Cruz', 'committee' => 'Health', 'role' => 'Chairperson', 'district' => 'District 4', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 5, 'name' => 'Hon. Ana Reyes', 'committee' => 'Education', 'role' => 'Chairperson', 'district' => 'District 5', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 6, 'name' => 'Hon. Pedro Garcia', 'committee' => 'Infrastructure', 'role' => 'Chairperson', 'district' => 'District 6', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 7, 'name' => 'Hon. Rosa Martinez', 'committee' => 'Public Safety', 'role' => 'Chairperson', 'district' => 'District 7', 'status' => 'Active', 'joined' => '2023-01-15'],
    ['id' => 8, 'name' => 'Hon. Carlos Ramos', 'committee' => 'COVID-19 Response', 'role' => 'Chairperson', 'district' => 'District 8', 'status' => 'Active', 'joined' => '2023-06-01'],
];

$search = $_GET['search'] ?? '';
$committeeFilter = $_GET['committee'] ?? '';
$roleFilter = $_GET['role'] ?? '';

if ($search || $committeeFilter || $roleFilter) {
    $members = array_filter($members, function($member) use ($search, $committeeFilter, $roleFilter) {
        $matchesSearch = empty($search) || stripos($member['name'], $search) !== false || stripos($member['district'], $search) !== false;
        $matchesCommittee = empty($committeeFilter) || $member['committee'] === $committeeFilter;
        $matchesRole = empty($roleFilter) || $member['role'] === $roleFilter;
        return $matchesSearch && $matchesCommittee && $matchesRole;
    });
}
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Members</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage committee member assignments and roles</p>
        </div>
        <button onclick="openAddMemberModal()" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-person-plus"></i>
            <span>Add Member</span>
        </button>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
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

<!-- Advanced Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-search"></i> Search Members
                </label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name or district..." 
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red focus:border-transparent dark:bg-gray-700 dark:text-white transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-building"></i> Committee
                </label>
                <select name="committee" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                    <option value="">All Committees</option>
                    <option value="Finance" <?php echo $committeeFilter === 'Finance' ? 'selected' : ''; ?>>Finance</option>
                    <option value="Health" <?php echo $committeeFilter === 'Health' ? 'selected' : ''; ?>>Health</option>
                    <option value="Education" <?php echo $committeeFilter === 'Education' ? 'selected' : ''; ?>>Education</option>
                    <option value="Infrastructure" <?php echo $committeeFilter === 'Infrastructure' ? 'selected' : ''; ?>>Infrastructure</option>
                    <option value="Public Safety" <?php echo $committeeFilter === 'Public Safety' ? 'selected' : ''; ?>>Public Safety</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-person-badge"></i> Role
                </label>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-cms-red dark:bg-gray-700 dark:text-white">
                    <option value="">All Roles</option>
                    <option value="Chairperson" <?php echo $roleFilter === 'Chairperson' ? 'selected' : ''; ?>>Chairperson</option>
                    <option value="Vice-Chair" <?php echo $roleFilter === 'Vice-Chair' ? 'selected' : ''; ?>>Vice-Chair</option>
                    <option value="Member" <?php echo $roleFilter === 'Member' ? 'selected' : ''; ?>>Member</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-2">
            <a href="members.php" class="px-6 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-x-circle"></i> Clear
            </a>
            <button type="submit" class="px-6 py-2.5 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
                <i class="bi bi-funnel"></i> Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Total Members</p>
                <p class="text-3xl font-bold mt-1"><?php echo count($members); ?></p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-people text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Chairpersons</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($members, fn($m) => $m['role'] === 'Chairperson')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-person-badge text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Vice-Chairs</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($members, fn($m) => $m['role'] === 'Vice-Chair')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-person-check text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Active Members</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($members, fn($m) => $m['status'] === 'Active')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-check-circle text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border-b-2 border-cms-red">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Committee</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">District</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($members as $member): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-cms-red to-cms-dark rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($member['name'], 5, 1)); ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($member['name']); ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Joined <?php echo date('M Y', strtotime($member['joined'])); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <?php echo $member['committee']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full 
                            <?php echo $member['role'] === 'Chairperson' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                       ($member['role'] === 'Vice-Chair' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'); ?>">
                            <?php echo $member['role']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium"><?php echo $member['district']; ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <i class="bi bi-check-circle"></i> <?php echo $member['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button onclick="editMember(<?php echo $member['id']; ?>)" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="removeMember(<?php echo $member['id']; ?>)" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (empty($members)): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center mt-6">
    <i class="bi bi-people text-6xl text-gray-400 mb-4"></i>
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Members Found</h3>
    <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your search or filters</p>
    <a href="members.php" class="inline-block px-6 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
        Clear Filters
    </a>
</div>
<?php endif; ?>

<script>
    function openAddMemberModal() {
        alert('Add Member modal will open here');
    }
    
    function editMember(id) {
        alert('Edit member ' + id);
    }
    
    function removeMember(id) {
        if (confirm('Are you sure you want to remove this member?')) {
            alert('Member ' + id + ' removed');
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>
