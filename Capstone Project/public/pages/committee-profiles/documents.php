<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);
if (!$committee) {
    header('Location: index.php');
    exit();
}

$documents = getCommitteeDocuments($id);
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Documents';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>"
                    class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Documents</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($committee['name']); ?></h1>
            <p class="text-gray-600">Committee Documents</p>
        </div>
        <div class="flex gap-2">
            <a href="upload-document.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer inline-flex items-center">
                <i class="bi bi-upload mr-2"></i>Upload Document
            </a>
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <?php if (empty($documents)): ?>
            <div class="text-center py-12">
                <i class="bi bi-file-earmark text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No documents uploaded yet</p>
                <a href="upload-document.php?committee_id=<?php echo $id; ?>"
                    class="mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg cursor-pointer inline-block">
                    Upload First Document
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($documents as $doc): ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition">
                        <div class="flex items-start justify-between mb-3">
                            <?php
                            // Determine icon based on file type
                            $extension = pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION);
                            $iconClass = match(strtolower($extension)) {
                                'pdf' => 'bi-file-earmark-pdf text-red-600',
                                'xlsx', 'xls' => 'bi-file-earmark-excel text-green-600',
                                'docx', 'doc' => 'bi-file-earmark-word text-blue-600',
                                default => 'bi-file-earmark-text text-gray-600'
                            };
                            ?>
                            <i class="bi <?php echo $iconClass; ?> text-4xl"></i>
                            <span class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['file_size'] ?? 'N/A'); ?></span>
                        </div>
                        <h3 class="font-semibold mb-1 text-gray-900 dark:text-white"><?php echo htmlspecialchars($doc['title']); ?></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2"><?php echo htmlspecialchars($doc['file_name'] ?? 'document.pdf'); ?></p>
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded text-xs mb-2">
                            <?php echo htmlspecialchars($doc['type']); ?>
                        </span>
                        <?php if (!empty($doc['description'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($doc['description']); ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500 mb-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span><?php echo date('M d, Y', strtotime($doc['uploaded_date'])); ?></span>
                            <span><?php echo htmlspecialchars($doc['uploaded_by']); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="alert('Download feature will be implemented with actual file storage')" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition">
                                <i class="bi bi-download mr-1"></i>Download
                            </button>
                            <button onclick="return confirm('Delete this document?')" 
                                class="px-3 py-2 border border-red-600 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded text-sm transition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>