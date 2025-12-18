<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Documents';
include '../../includes/header.php';

// Hardcoded documents data
$documents = [
    ['id' => 1, 'name' => 'Finance Committee Charter.pdf', 'committee' => 'Finance', 'type' => 'Charter', 'size' => '2.4 MB', 'uploaded' => '2024-01-15', 'uploaded_by' => 'Admin'],
    ['id' => 2, 'name' => '2024 Budget Proposal.xlsx', 'committee' => 'Finance', 'type' => 'Report', 'size' => '5.1 MB', 'uploaded' => '2024-11-20', 'uploaded_by' => 'Hon. Maria Santos'],
    ['id' => 3, 'name' => 'Health Committee Guidelines.pdf', 'committee' => 'Health', 'type' => 'Guidelines', 'size' => '1.8 MB', 'uploaded' => '2024-02-10', 'uploaded_by' => 'Admin'],
    ['id' => 4, 'name' => 'Meeting Minutes - Nov 2024.docx', 'committee' => 'Finance', 'type' => 'Minutes', 'size' => '856 KB', 'uploaded' => '2024-11-25', 'uploaded_by' => 'Secretary'],
    ['id' => 5, 'name' => 'Infrastructure Project Plans.pdf', 'committee' => 'Infrastructure', 'type' => 'Report', 'size' => '12.3 MB', 'uploaded' => '2024-10-05', 'uploaded_by' => 'Hon. Pedro Garcia'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Documents</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage committee files and documents</p>
        </div>
        <button onclick="openUploadModal()" class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white rounded-lg transition">
            <i class="bi bi-upload"></i> Upload Document
        </button>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php" class="px-4 py-2 bg-cms-red text-white rounded-lg font-semibold">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Total Documents</p>
                <p class="text-3xl font-bold mt-1"><?php echo count($documents); ?></p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-files text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Reports</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($documents, fn($d) => $d['type'] === 'Report')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-file-earmark-bar-graph text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Minutes</p>
                <p class="text-3xl font-bold mt-1">
                    <?php echo count(array_filter($documents, fn($d) => $d['type'] === 'Minutes')); ?>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-file-earmark-text text-3xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Total Size</p>
                <p class="text-3xl font-bold mt-1">22 MB</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <i class="bi bi-hdd text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($documents as $doc): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-lg transition-shadow p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-cms-red to-cms-dark rounded-lg flex items-center justify-center text-white">
                    <i class="bi bi-file-earmark-pdf text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm"><?php echo htmlspecialchars($doc['name']); ?></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $doc['size']; ?></p>
                </div>
            </div>
        </div>
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Committee:</span>
                <span class="font-medium text-gray-900 dark:text-white"><?php echo $doc['committee']; ?></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Type:</span>
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <?php echo $doc['type']; ?>
                </span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Uploaded:</span>
                <span class="text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($doc['uploaded'])); ?></span>
            </div>
        </div>
        <div class="flex space-x-2">
            <button onclick="downloadDoc(<?php echo $doc['id']; ?>)" class="flex-1 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                <i class="bi bi-download"></i> Download
            </button>
            <button onclick="deleteDoc(<?php echo $doc['id']; ?>)" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-sm">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
    function openUploadModal() {
        alert('Upload document modal will open here');
    }
    function downloadDoc(id) {
        alert('Downloading document ' + id);
    }
    function deleteDoc(id) {
        if (confirm('Are you sure you want to delete this document?')) {
            alert('Document ' + id + ' deleted');
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>
