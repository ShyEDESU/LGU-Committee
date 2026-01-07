<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$item = getActionItemById($id);

if (!$item) {
    $_SESSION['error_message'] = 'Action item not found';
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'meeting_id' => $_POST['meeting_id'] ?? null,
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'assigned_to' => trim($_POST['assigned_to'] ?? ''),
        'due_date' => $_POST['due_date'] ?? '',
        'priority' => $_POST['priority'] ?? 'Medium',
        'status' => $_POST['status'] ?? 'Pending'
    ];
    
    // Validation
    $errors = [];
    if (empty($data['title'])) $errors[] = 'Title is required';
    if (empty($data['assigned_to'])) $errors[] = 'Assigned to is required';
    
    if (empty($errors)) {
        updateActionItem($id, $data);
        $_SESSION['success_message'] = 'Action item updated successfully!';
        header('Location: view.php?id=' . $id);
        exit();
    }
}

// Get meetings for dropdown
$meetings = getAllMeetings();

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Action Item';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Action Items</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600"><?php echo htmlspecialchars($item['title']); ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Action Item</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update action item details</p>
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
                    Action Item Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($item['title']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Related Meeting (Optional)
                </label>
                <select name="meeting_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">No meeting</option>
                    <?php foreach ($meetings as $meeting): ?>
                        <option value="<?php echo $meeting['id']; ?>" 
                            <?php echo ($item['meeting_id'] ?? '') == $meeting['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($meeting['title']); ?> - <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Assigned To <span class="text-red-500">*</span>
                </label>
                <input type="text" name="assigned_to" required value="<?php echo htmlspecialchars($item['assigned_to']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Priority
                </label>
                <select name="priority" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="High" <?php echo $item['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
                    <option value="Medium" <?php echo $item['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo $item['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="Pending" <?php echo $item['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="In Progress" <?php echo $item['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo $item['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Due Date
                </label>
                <input type="date" name="due_date" value="<?php echo htmlspecialchars($item['due_date'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="view.php?id=<?php echo $id; ?>" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="bi bi-check-circle mr-2"></i>Update Action Item
            </button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
