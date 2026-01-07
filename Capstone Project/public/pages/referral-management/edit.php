<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$referral = getReferralById($id);

if (!$referral) {
    $_SESSION['error_message'] = 'Referral not found';
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $committeeId = $_POST['committee_id'] ?? 0;
    $committee = getCommitteeById($committeeId);
    
    $data = [
        'committee_id' => $committeeId,
        'committee_name' => $committee['name'] ?? '',
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority' => $_POST['priority'] ?? 'Medium',
        'deadline' => $_POST['deadline'] ?? '',
        'assigned_to' => trim($_POST['assigned_to'] ?? '')
    ];
    
    // Validation
    $errors = [];
    if (empty($data['title'])) $errors[] = 'Title is required';
    if (empty($data['committee_id'])) $errors[] = 'Committee is required';
    if (empty($data['deadline'])) $errors[] = 'Deadline is required';
    
    if (empty($errors)) {
        updateReferral($id, $data);
        $_SESSION['success_message'] = 'Referral updated successfully!';
        header('Location: view.php?id=' . $id);
        exit();
    }
}

// Get committees for dropdown
$committees = getAllCommittees();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Referral';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Referrals</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600"><?php echo htmlspecialchars($referral['title']); ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Referral</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update referral details</p>
        </div>
        <a href="view.php?id=<?php echo $id; ?>" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
            <i class="bi bi-x-lg mr-2"></i>Cancel
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <ul class="list-disc list-inside text-red-700">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Referral Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($referral['title']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Committee <span class="text-red-500">*</span>
                </label>
                <select name="committee_id" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Committee</option>
                    <?php foreach ($committees as $committee): ?>
                        <option value="<?php echo $committee['id']; ?>" 
                            <?php echo $referral['committee_id'] == $committee['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($committee['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Priority <span class="text-red-500">*</span>
                </label>
                <select name="priority" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="High" <?php echo $referral['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
                    <option value="Medium" <?php echo $referral['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo $referral['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Deadline <span class="text-red-500">*</span>
                </label>
                <input type="date" name="deadline" required value="<?php echo htmlspecialchars($referral['deadline']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Assigned To
                </label>
                <input type="text" name="assigned_to" value="<?php echo htmlspecialchars($referral['assigned_to'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($referral['description'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="view.php?id=<?php echo $id; ?>" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="bi bi-check-circle mr-2"></i>Update Referral
            </button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
