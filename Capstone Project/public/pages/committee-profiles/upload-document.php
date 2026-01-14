<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['committee_id'] ?? 0;
$committee = getCommitteeById($committeeId);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate new document ID
    $documents = $_SESSION['committee_documents'] ?? [];
    $newId = empty($documents) ? 1 : max(array_column($documents, 'id')) + 1;

    // Create new document
    $newDocument = [
        'id' => $newId,
        'committee_id' => $committeeId,
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'description' => $_POST['description'] ?? '',
        'uploaded_date' => date('Y-m-d'),
        'uploaded_by' => $_SESSION['user_name'] ?? 'User',
        'file_name' => $_FILES['document']['name'] ?? 'document.pdf', // For now, just store filename
        'file_size' => $_FILES['document']['size'] ?? 0
    ];

    // Add to session
    $_SESSION['committee_documents'][] = $newDocument;

    $_SESSION['success_message'] = 'Document uploaded successfully';
    header('Location: documents.php?id=' . $committeeId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Upload Document';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $committeeId; ?>" class="text-red-600">
                    <?php echo htmlspecialchars($committee['name']); ?>
                </a></li>
            <li class="breadcrumb-item"><a href="documents.php?id=<?php echo $committeeId; ?>"
                    class="text-red-600">Documents</a></li>
            <li class="breadcrumb-item active">Upload Document</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Document</h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <a href="documents.php?id=<?php echo $committeeId; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back to Documents
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Document Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="title" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Committee Charter 2026">
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Document Type <span class="text-red-600">*</span>
                    </label>
                    <select name="type" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Charter">Charter</option>
                        <option value="Bylaws">Bylaws</option>
                        <option value="Policy">Policy</option>
                        <option value="Procedure">Procedure</option>
                        <option value="Report">Report</option>
                        <option value="Proposal">Proposal</option>
                        <option value="Guidelines">Guidelines</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Brief description of the document..."></textarea>
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload File <span class="text-red-600">*</span>
                    </label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-red-500 transition">
                        <div class="space-y-1 text-center">
                            <i class="bi bi-cloud-upload text-4xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="file-upload"
                                    class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="document" type="file" class="sr-only" required
                                        accept=".pdf,.doc,.docx">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 10MB</p>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <i class="bi bi-info-circle mr-1"></i>
                        Note: For this demo, file info will be saved but actual file upload will be implemented with
                        database integration.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="documents.php?id=<?php echo $committeeId; ?>"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-upload mr-2"></i>Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // File upload preview
    document.getElementById('file-upload').addEventListener('change', function (e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const label = document.querySelector('label[for="file-upload"] span');
            label.textContent = fileName;
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>