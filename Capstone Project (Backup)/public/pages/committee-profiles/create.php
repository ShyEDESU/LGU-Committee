<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'type' => $_POST['type'] ?? '',
        'chair' => trim($_POST['chair'] ?? ''),
        'vice_chair' => trim($_POST['vice_chair'] ?? ''),
        'jurisdiction' => trim($_POST['jurisdiction'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'Active'
    ];

    // Validation
    $errors = [];
    if (empty($data['name']))
        $errors[] = 'Committee name is required';
    if (empty($data['type']))
        $errors[] = 'Committee type is required';
    if (empty($data['chair']))
        $errors[] = 'Chairperson is required';
    if (empty($data['jurisdiction']))
        $errors[] = 'Jurisdiction is required';

    if (empty($errors)) {
        $newId = createCommittee($data);
        $_SESSION['success_message'] = 'Committee created successfully!';
        header('Location: view.php?id=' . $newId);
        exit();
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Committee';
include '../../includes/header.php';
?>

<!-- Page Content -->
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php"
                    class="text-red-600 hover:text-red-700">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600 hover:text-red-700">Committee
                    Profiles</a></li>
            <li class="breadcrumb-item active">Create Committee</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Committee</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new committee to the system</p>
        </div>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="bi bi-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <i class="bi bi-exclamation-triangle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-red-800 dark:text-red-300">Please correct the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 dark:text-red-400 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <form method="POST" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Committee Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Committee on Finance">
                </div>

                <!-- Committee Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Standing" <?php echo ($_POST['type'] ?? '') === 'Standing' ? 'selected' : ''; ?>>
                            Standing Committee</option>
                        <option value="Special" <?php echo ($_POST['type'] ?? '') === 'Special' ? 'selected' : ''; ?>>
                            Special Committee</option>
                        <option value="Ad Hoc" <?php echo ($_POST['type'] ?? '') === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc
                            Committee</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="Active" <?php echo ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>
                            Inactive</option>
                    </select>
                </div>

                <!-- Chairperson -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Chairperson <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="chair" required
                        value="<?php echo htmlspecialchars($_POST['chair'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Hon. Maria Santos">
                </div>

                <!-- Vice-Chairperson -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Vice-Chairperson
                    </label>
                    <input type="text" name="vice_chair"
                        value="<?php echo htmlspecialchars($_POST['vice_chair'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Hon. Roberto Cruz">
                </div>

                <!-- Jurisdiction -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jurisdiction <span class="text-red-500">*</span>
                    </label>
                    <textarea name="jurisdiction" required rows="2"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Brief description of committee's jurisdiction"><?php echo htmlspecialchars($_POST['jurisdiction'] ?? ''); ?></textarea>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Detailed description of the committee's role and responsibilities"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="index.php"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="bi bi-check-circle mr-2"></i>Create Committee
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>