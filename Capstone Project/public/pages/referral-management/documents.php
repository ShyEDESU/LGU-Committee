<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$referralId = $_GET['id'] ?? 0;
$referral = getReferralById($referralId);

if (!$referral) {
    $_SESSION['error_message'] = 'Referral not found';
    header('Location: index.php');
    exit();
}

// Handle file upload (simulated - in production would save to filesystem)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $_SESSION['success_message'] = 'Document uploaded successfully';
    header('Location: documents.php?id=' . $referralId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Documents - ' . $referral['title'];
include '../../includes/header.php';

// Simulated documents (in production, would come from database/filesystem)
$documents = [
    [
        'id' => 1,
        'filename' => 'ordinance-2025-001-original.pdf',
        'category' => 'Original',
        'size' => '1.2 MB',
        'uploaded_by' => $userName,
        'uploaded_date' => date('Y-m-d'),
        'version' => 1
    ]
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Documents</h1>
            <p class="text-gray-600 mt-1">
                <?php echo htmlspecialchars($referral['title']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $referralId; ?>"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back to Referral
        </a>
    </div>
</div>

<!-- Upload Form -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold mb-4"><i class="bi bi-cloud-upload mr-2"></i>Upload Document</h2>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Document File</label>
            <input type="file" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                <option value="Original">Original Document</option>
                <option value="Amendment">Amendment</option>
                <option value="Report">Committee Report</option>
                <option value="Attachment">Supporting Attachment</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="bi bi-upload mr-2"></i>Upload Document
            </button>
        </div>
    </form>
</div>

<!-- Documents List -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold"><i class="bi bi-files mr-2"></i>Uploaded Documents</h2>
    </div>

    <?php if (empty($documents)): ?>
        <div class="p-8 text-center text-gray-500">
            <i class="bi bi-inbox text-5xl mb-3"></i>
            <p>No documents uploaded yet</p>
            <p class="text-sm mt-1">Upload documents using the form above</p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($documents as $doc): ?>
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="bi bi-file-earmark-pdf text-red-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($doc['filename']); ?>
                                </h3>
                                <div class="flex items-center gap-3 mt-1 text-sm text-gray-600">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        <?php echo $doc['category']; ?>
                                    </span>
                                    <span><i class="bi bi-hdd mr-1"></i>
                                        <?php echo $doc['size']; ?>
                                    </span>
                                    <span><i class="bi bi-person mr-1"></i>
                                        <?php echo htmlspecialchars($doc['uploaded_by']); ?>
                                    </span>
                                    <span><i class="bi bi-calendar mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($doc['uploaded_date'])); ?>
                                    </span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">v
                                        <?php echo $doc['version']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition text-sm">
                                <i class="bi bi-eye mr-1"></i>Preview
                            </button>
                            <button class="px-3 py-1 text-green-600 hover:bg-green-50 rounded-lg transition text-sm">
                                <i class="bi bi-download mr-1"></i>Download
                            </button>
                            <button class="px-3 py-1 text-red-600 hover:bg-red-50 rounded-lg transition text-sm">
                                <i class="bi bi-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Document Categories Info -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <p class="text-sm text-blue-600 font-semibold">Original Documents</p>
        <p class="text-2xl font-bold text-blue-900">1</p>
    </div>
    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
        <p class="text-sm text-purple-600 font-semibold">Amendments</p>
        <p class="text-2xl font-bold text-purple-900">0</p>
    </div>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-600 font-semibold">Reports</p>
        <p class="text-2xl font-bold text-green-900">0</p>
    </div>
    <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-lg">
        <p class="text-sm text-orange-600 font-semibold">Attachments</p>
        <p class="text-2xl font-bold text-orange-900">0</p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>