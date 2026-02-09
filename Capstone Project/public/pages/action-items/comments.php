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

// Initialize comments array if not exists
if (!isset($_SESSION['action_item_comments'])) {
    $_SESSION['action_item_comments'] = [];
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment = [
        'id' => count($_SESSION['action_item_comments']) + 1,
        'action_item_id' => $itemId,
        'user' => $_SESSION['user_name'] ?? 'User',
        'comment' => $_POST['comment'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    $_SESSION['action_item_comments'][] = $comment;
    header('Location: comments.php?id=' . $itemId . '&added=1');
    exit();
}

// Get comments for this action item
$comments = array_filter($_SESSION['action_item_comments'], function ($c) use ($itemId) {
    return $c['action_item_id'] == $itemId;
});

// Sort by newest first
usort($comments, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Action Item Comments';
include '../../includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Comments</h1>
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

<?php if (isset($_GET['added'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-green-800 dark:text-green-300">
            <i class="bi bi-check-circle mr-2"></i>Comment added successfully!
        </p>
    </div>
<?php endif; ?>

<!-- Add Comment Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-chat-left-text mr-2"></i>Add Comment
    </h2>
    <form method="POST">
        <textarea name="comment" rows="4" required placeholder="Write your comment here..."
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white mb-4"></textarea>
        <div class="flex justify-end">
            <button type="submit" name="add_comment"
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-send mr-2"></i>Post Comment
            </button>
        </div>
    </form>
</div>

<!-- Comments List -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
        <i class="bi bi-chat-dots mr-2"></i>Comments (
        <?php echo count($comments); ?>)
    </h2>

    <div class="space-y-6">
        <?php foreach ($comments as $comment): ?>
            <div class="flex items-start space-x-4 pb-6 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <i class="bi bi-person-fill text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($comment['user']); ?>
                        </p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                        </span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        <?php echo htmlspecialchars($comment['comment']); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($comments)): ?>
            <div class="text-center py-12">
                <i class="bi bi-chat-left-dots text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Comments Yet</h3>
                <p class="text-gray-600 dark:text-gray-400">Be the first to comment on this action item</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span class="font-medium"><?php echo count($comments); ?></span> of
        <span class="font-medium"><?php echo count($comments); ?></span> comment(s)
    </div>
    <div class="text-sm text-gray-500 italic">
        Item ID: <?php echo htmlspecialchars($id); ?>
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>