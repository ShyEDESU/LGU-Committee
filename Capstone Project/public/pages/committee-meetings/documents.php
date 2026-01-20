<?php
// Suppress all errors to prevent output corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    // Don't redirect - let page load with error message
    $meeting = [
        'id' => $meetingId,
        'title' => 'Meeting Not Found',
        'committee_id' => 0,
        'committee_name' => 'Unknown',
        'date' => date('Y-m-d'),
        'time_start' => '00:00',
        'status' => 'Unknown'
    ];
    // header('Location: index.php');
    // exit();
}

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_document'])) {
    $data = [
        'name' => $_POST['name'],
        'category' => $_POST['category'],
        'description' => $_POST['description'] ?? ''
    ];

    $file = $_FILES['document_file'] ?? null;

    if (addMeetingDocument($meetingId, $data, $file)) {
        $_SESSION['success_message'] = 'Document added successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to add document';
    }

    header('Location: documents.php?id=' . $meetingId);
    exit();
}

// Handle document delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document'])) {
    $docId = $_POST['document_id'];
    deleteMeetingDocument($docId);
    $_SESSION['success_message'] = 'Document deleted successfully';
    header('Location: documents.php?id=' . $meetingId);
    exit();
}

// Get data
$committee = getCommitteeById($meeting['committee_id']);
$documents = getMeetingDocuments($meetingId);

// Group by category
$documentsByCategory = [];
foreach ($documents as $doc) {
    $category = $doc['category'];
    if (!isset($documentsByCategory[$category])) {
        $documentsByCategory[$category] = [];
    }
    $documentsByCategory[$category][] = $doc;
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Documents';
include '../../includes/header.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-green-700 dark:text-green-300">
            <?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </p>
    </div>
<?php endif; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meeting Documents</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to Meeting
            </a>
            <button onclick="openAddDocumentModal()"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-plus-circle mr-2"></i>Add Document
            </button>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Details
            </a>
            <a href="attendance.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Attendance
            </a>
            <a href="minutes.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Minutes
            </a>
            <a href="documents.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Documents
            </a>
        </nav>
    </div>
</div>


<!-- Documents by Category -->
<?php if (empty($documents)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-file-earmark-x text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Documents Yet</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            Upload documents related to this meeting
        </p>
        <button onclick="openAddDocumentModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
            <i class="bi bi-plus-circle mr-2"></i>Add First Document
        </button>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($documentsByCategory as $category => $docs): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-folder mr-2"></i>
                        <?php echo htmlspecialchars($category); ?>
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Document
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Description
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Size
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Uploaded
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($docs as $doc): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="bi bi-file-earmark-pdf text-2xl text-red-500 mr-3"></i>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($doc['name']); ?>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Version
                                                    <?php echo $doc['version']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($doc['description']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            <?php echo number_format($doc['file_size']); ?> KB
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($doc['uploaded_by']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo date('M j, Y g:i A', strtotime($doc['uploaded_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <button
                                                onclick="alert('Download functionality will be implemented with actual file storage')"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                <i class="bi bi-download mr-1"></i>Download
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this document?')">
                                                <input type="hidden" name="delete_document" value="1">
                                                <input type="hidden" name="document_id" value="<?php echo $doc['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                    <i class="bi bi-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Document Modal -->
<div id="addDocumentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Document</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_document" value="1">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Document Name *
                    </label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Meeting Agenda.pdf">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Category *
                    </label>
                    <select name="category" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="Agenda">Agenda</option>
                        <option value="Minutes">Minutes</option>
                        <option value="Reports">Reports</option>
                        <option value="Presentations">Presentations</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Brief description of the document..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload File *
                    </label>
                    <input type="file" name="document_file" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOCX, JPG, PNG</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAddDocumentModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="bi bi-upload mr-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function openAddDocumentModal() {
        document.getElementById('addDocumentModal').classList.remove('hidden');
    }

    function closeAddDocumentModal() {
        document.getElementById('addDocumentModal').classList.add('hidden');
    }

    // Close modal on outside click
    document.getElementById('addDocumentModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeAddDocumentModal();
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>