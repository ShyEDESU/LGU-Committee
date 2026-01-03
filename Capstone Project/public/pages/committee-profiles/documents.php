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
        <button onclick="openModal('uploadModal')"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-upload"></i>
            <span>Upload Document</span>
        </button>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-100 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Documents</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($documents); ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-files text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Reports</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($documents, fn($d) => $d['type'] === 'Report')); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-file-earmark-bar-graph text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-300 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Minutes</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($documents, fn($d) => $d['type'] === 'Minutes')); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-file-earmark-text text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-400 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Size</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">22 MB</p>
            </div>
            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3">
                <i class="bi bi-hdd text-orange-600 dark:text-orange-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Document Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $delay = 100;
    foreach ($documents as $doc):
        ?>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 p-6 animate-fade-in-up animation-delay-<?php echo $delay; ?>">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-file-earmark-pdf text-red-600 dark:text-red-400 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                            <?php echo htmlspecialchars($doc['name']); ?>
                        </h3>
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
                    <span
                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                        <?php echo $doc['type']; ?>
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Uploaded:</span>
                    <span
                        class="text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($doc['uploaded'])); ?></span>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="viewDocument(<?php echo $doc['id']; ?>)"
                    class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm">
                    <i class="bi bi-eye"></i> View
                </button>
                <button onclick="downloadDoc(<?php echo $doc['id']; ?>)"
                    class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition text-sm">
                    <i class="bi bi-download"></i>
                </button>
                <button onclick="deleteDoc(<?php echo $doc['id']; ?>)"
                    class="px-3 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg transition text-sm">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <?php
        $delay += 100;
        if ($delay > 900)
            $delay = 100;
    endforeach;
    ?>
</div>

<!-- Upload Document Modal -->
<div id="uploadModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full animate-fade-in-up">
        <div
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Document</h2>
            <button onclick="closeModal('uploadModal')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <form class="p-6" onsubmit="uploadDocument(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Committee <span
                            class="text-red-600">*</span></label>
                    <select required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Committee</option>
                        <option>Finance</option>
                        <option>Health</option>
                        <option>Education</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Type <span
                            class="text-red-600">*</span></label>
                    <select required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option>Charter</option>
                        <option>Report</option>
                        <option>Minutes</option>
                        <option>Guidelines</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File <span
                            class="text-red-600">*</span></label>
                    <div
                        class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-red-600 transition">
                        <i class="bi bi-cloud-upload text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600 dark:text-gray-400 mb-2">Drag and drop or click to browse</p>
                        <input type="file" class="hidden" id="fileInput" required>
                        <button type="button" onclick="document.getElementById('fileInput').click()"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                            Choose File
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('uploadModal')"
                    class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">Cancel</button>
                <button type="submit" class="btn-primary"><i class="bi bi-upload mr-2"></i> Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Document Modal -->
<div id="deleteDocModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full animate-bounce-in">
        <div class="p-6">
            <div
                class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Delete Document?</h3>
            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">This action cannot be undone.</p>
            <div class="flex space-x-3">
                <button onclick="closeModal('deleteDocModal')"
                    class="flex-1 px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">Cancel</button>
                <button onclick="confirmDelete()" class="flex-1 btn-danger"><i class="bi bi-trash mr-2"></i>
                    Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    const documentsData = <?php echo json_encode($documents); ?>;
    let currentDocId = null;

    function viewDocument(id) {
        const doc = documentsData.find(d => d.id === id);
        alert(`Viewing: ${doc.name}\n(Preview will be implemented with database)`);
    }

    function downloadDoc(id) {
        const doc = documentsData.find(d => d.id === id);
        alert(`Downloading: ${doc.name}\n(Download will be implemented with database)`);
    }

    function deleteDoc(id) {
        currentDocId = id;
        openModal('deleteDocModal');
    }

    function uploadDocument(event) {
        event.preventDefault();
        alert('Document uploaded successfully! (Will be connected to database)');
        closeModal('uploadModal');
    }

    function confirmDelete() {
        alert('Document deleted successfully! (Will be connected to database)');
        closeModal('deleteDocModal');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('uploadModal');
            closeModal('deleteDocModal');
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>