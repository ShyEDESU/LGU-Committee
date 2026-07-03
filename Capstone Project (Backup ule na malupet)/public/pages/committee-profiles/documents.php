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

// Handle document deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_document'])) {
    $documentId = $_POST['document_id'] ?? 0;
    $isMeetingDoc = isset($_POST['is_meeting_doc']) && $_POST['is_meeting_doc'] == '1';

    if ($documentId) {
        if ($isMeetingDoc) {
            require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
            $success = removeMeetingDocument($documentId);
        } else {
            $success = removeCommitteeDocument($documentId); // Assuming this also needs to be updated for consistency
        }

        if ($success) {
            $_SESSION['success_message'] = 'Document has been removed successfully from the records.';
        } else {
            $_SESSION['error_message'] = 'An administrative error occurred during removal.';
        }
    } else {
        $_SESSION['error_message'] = 'Invalid document ID.';
    }
    header('Location: documents.php?id=' . $id);
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

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agendas"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-journal-text mr-1"></i>Agendas
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=referrals"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-arrow-left-right mr-1"></i>Referrals
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
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
                            $extension = strtolower(pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION));
                            $iconClass = 'bi-file-earmark-text text-gray-600';
                            switch ($extension) {
                                case 'pdf':
                                    $iconClass = 'bi-file-earmark-pdf text-red-600';
                                    break;
                                case 'xlsx':
                                case 'xls':
                                    $iconClass = 'bi-file-earmark-excel text-green-600';
                                    break;
                                case 'docx':
                                case 'doc':
                                    $iconClass = 'bi-file-earmark-word text-red-600';
                                    break;
                            }
                            ?>
                            <i class="bi <?php echo $iconClass; ?> text-4xl"></i>
                            <span
                                class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['file_size'] ?? 'N/A'); ?></span>
                        </div>
                        <h3 class="font-semibold mb-1 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($doc['title']); ?>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <?php echo htmlspecialchars($doc['file_name'] ?? 'document.pdf'); ?>
                        </p>
                        <div class="flex flex-wrap gap-1 mb-2">
                            <span
                                class="inline-block px-2 py-1 bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300 rounded text-xs">
                                <?php echo htmlspecialchars($doc['type']); ?>
                            </span>
                            <?php if (isset($doc['source'])): ?>
                                <span
                                    class="inline-block px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded text-xs">
                                    <i class="bi bi-info-circle mr-1"></i><?php echo htmlspecialchars($doc['source']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($doc['description'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($doc['description']); ?>
                            </p>
                        <?php endif; ?>
                        <div
                            class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500 mb-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span><?php echo date('M d, Y', strtotime($doc['uploaded_date'])); ?></span>
                            <span><?php echo htmlspecialchars($doc['uploaded_by']); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!empty($doc['file_path'])): ?>
                                <a href="download-document.php?id=<?php echo $doc['id']; ?>"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm transition text-center">
                                    <i class="bi bi-download mr-1"></i>Download
                                </a>
                            <?php else: ?>
                                <button disabled class="flex-1 bg-gray-400 text-white px-3 py-2 rounded text-sm cursor-not-allowed">
                                    <i class="bi bi-download mr-1"></i>No File
                                </button>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;"
                                onsubmit="return confirm('Professional Record Adjustment: Remove this document from the records?');">
                                <input type="hidden" name="document_id" value="<?php echo $doc['id']; ?>">
                                <input type="hidden" name="remove_document" value="1">
                                <?php if (!empty($doc['is_meeting_doc'])): ?>
                                    <input type="hidden" name="is_meeting_doc" value="1">
                                <?php endif; ?>
                                <button type="submit" title="Remove Document"
                                    class="px-3 py-2 border border-red-600 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded text-sm transition">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</div> <!-- Closing container-fluid and module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>