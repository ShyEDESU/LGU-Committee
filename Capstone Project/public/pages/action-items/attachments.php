<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get action item ID
$itemId = $_GET['id'] ?? 0;
$item = getActionItemById($itemId);

if (!$item) {
    header('Location: index.php');
    exit();
}

// Get attachments from item
$attachments = $item['attachments'] ?? [];

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Item Attachments';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Attachments</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($item['title']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $item['id']; ?>"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <a href="assign.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-person-plus"></i> Assign
        </a>
        <a href="progress.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-graph-up"></i> Progress
        </a>
        <a href="deadlines.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
        <a href="reports.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-text"></i> Reports
        </a>
        <a href="history.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Upload Section -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-cloud-upload mr-2"></i>Upload Attachment
    </h2>
    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-12 text-center">
        <i class="bi bi-file-earmark-arrow-up text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Drag and drop files here</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">or click to browse</p>
        <button class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-folder2-open mr-2"></i>Browse Files
        </button>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
            Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG (Max 10MB)
        </p>
    </div>
</div>

<!-- Attachments List -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
        <i class="bi bi-paperclip mr-2"></i>Attachments (
        <?php echo count($attachments); ?>)
    </h2>

    <?php if (!empty($attachments)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($attachments as $attachment):
                $fileExt = pathinfo($attachment['name'] ?? 'file', PATHINFO_EXTENSION);
                $iconClass = match (strtolower($fileExt)) {
                    'pdf' => 'bi-file-pdf text-red-600',
                    'doc', 'docx' => 'bi-file-word text-blue-600',
                    'xls', 'xlsx' => 'bi-file-excel text-green-600',
                    'ppt', 'pptx' => 'bi-file-ppt text-orange-600',
                    'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image text-purple-600',
                    default => 'bi-file-earmark text-gray-600'
                };
                ?>
                <div
                    class="flex items-center space-x-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bi <?php echo $iconClass; ?> text-4xl"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            <?php echo htmlspecialchars($attachment['name'] ?? 'Untitled'); ?>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo isset($attachment['size']) ? number_format($attachment['size'] / 1024, 1) . ' KB' : 'Unknown size'; ?>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo isset($attachment['uploaded_at']) ? date('M j, Y', strtotime($attachment['uploaded_at'])) : 'Recently'; ?>
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/20 rounded"
                            title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                        <button class="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                            title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="bi bi-paperclip text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Attachments</h3>
            <p class="text-gray-600 dark:text-gray-400">Upload files to attach them to this action item</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Placeholder for file upload functionality
    // In production, implement actual file upload with AJAX
</script>

<?php include '../../includes/footer.php'; ?>