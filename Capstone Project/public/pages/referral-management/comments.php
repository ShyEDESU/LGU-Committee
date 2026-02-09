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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $_SESSION['success_message'] = 'Comment added successfully';
    header('Location: comments.php?id=' . $referralId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Comments - ' . $referral['title'];
include '../../includes/header.php';

// Simulated comments (in production, would come from database)
$comments = [
    [
        'id' => 1,
        'user' => $userName,
        'comment' => 'This referral requires immediate attention from the finance committee.',
        'is_internal' => false,
        'created_date' => date('Y-m-d'),
        'created_time' => '10:30 AM'
    ],
    [
        'id' => 2,
        'user' => 'Hon. Maria Santos',
        'comment' => 'I have reviewed the budget implications. We should schedule a meeting to discuss this further.',
        'is_internal' => false,
        'created_date' => date('Y-m-d'),
        'created_time' => '02:15 PM'
    ]
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Comments</h1>
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

<!-- Add Comment Form -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold mb-4"><i class="bi bi-chat-left-text mr-2"></i>Add Comment</h2>
    <form method="POST">
        <input type="hidden" name="add_comment" value="1">
        <div class="mb-4">
            <textarea name="comment" rows="4" required placeholder="Share your thoughts, feedback, or questions..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 resize-none"></textarea>
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_internal"
                    class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="ml-2 text-sm text-gray-700">
                    <i class="bi bi-lock mr-1"></i>Internal note (visible only to staff)
                </span>
            </label>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="bi bi-send mr-2"></i>Post Comment
            </button>
        </div>
    </form>
</div>

<!-- Comments List -->
<div class="space-y-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold"><i class="bi bi-chat-dots mr-2"></i>Discussion (
            <?php echo count($comments); ?>)
        </h2>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="bi bi-funnel mr-1"></i>All Comments
            </button>
            <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="bi bi-lock mr-1"></i>Internal Only
            </button>
        </div>
    </div>

    <?php if (empty($comments)): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
            <i class="bi bi-chat text-5xl mb-3"></i>
            <p>No comments yet</p>
            <p class="text-sm mt-1">Be the first to share your thoughts</p>
        </div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div
                class="bg-white rounded-lg shadow-sm p-6 <?php echo $comment['is_internal'] ? 'border-l-4 border-yellow-500' : ''; ?>">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-person-fill text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($comment['user']); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($comment['created_date'])); ?> at
                                <?php echo $comment['created_time']; ?>
                                <?php if ($comment['is_internal']): ?>
                                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs">
                                        <i class="bi bi-lock"></i> Internal
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="text-gray-400 hover:text-red-600 transition">
                            <i class="bi bi-reply"></i>
                        </button>
                        <button class="text-gray-400 hover:text-gray-600 transition">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                    </div>
                </div>
                <p class="text-gray-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                </p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Comment Guidelines -->
<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mt-6">
    <h3 class="font-semibold text-red-900 mb-2"><i class="bi bi-info-circle mr-2"></i>Comment Guidelines</h3>
    <ul class="text-sm text-red-800 space-y-1">
        <li><i class="bi bi-check-circle mr-1"></i>Keep comments professional and relevant to the referral</li>
        <li><i class="bi bi-check-circle mr-1"></i>Use internal notes for sensitive information</li>
        <li><i class="bi bi-check-circle mr-1"></i>Tag specific members using @mention for direct responses</li>
    </ul>
</div>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span class="font-medium"><?php echo count($comments); ?></span> of
        <span class="font-medium"><?php echo count($comments); ?></span> comment(s)
    </div>
    <div class="text-sm text-gray-500 italic">
        Referral ID: <?php echo htmlspecialchars($id); ?>
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>