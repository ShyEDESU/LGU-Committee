<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    header('Location: index.php');
    exit();
}

// Handle add comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $itemId = $_POST['item_id'];
    $comment = $_POST['comment'];

    addAgendaComment($meetingId, $itemId, $comment);
    $_SESSION['success_message'] = 'Comment added successfully';
    header('Location: comments.php?id=' . $meetingId);
    exit();
}

// Get agenda items and comments
$agendaItems = getAgendaByMeeting($meetingId);
$comments = getAgendaComments($meetingId);

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Agenda Comments';
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Agenda Comments</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <a href="view.php?id=<?php echo $meetingId; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="bi bi-arrow-left mr-2"></i>Back
        </a>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-list-ol mr-1"></i>Agenda Items
            </a>
            <a href="comments.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 dark:text-red-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                <i class="bi bi-chat-dots mr-1"></i>Comments
            </a>
            <a href="distribute.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-send mr-1"></i>Distribution
            </a>
        </nav>
    </div>
</div>

<!-- Agenda Items with Comments -->
<?php if (empty($agendaItems)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
        <i class="bi bi-chat-text text-6xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Agenda Items</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Add agenda items to start commenting
        </p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($agendaItems as $item):
            $itemComments = array_filter($comments, function ($c) use ($item) {
                return $c['item_id'] == $item['id'];
            });
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span><i class="bi bi-person mr-1"></i>
                                    <?php echo htmlspecialchars($item['presenter']); ?>
                                </span>
                                <span><i class="bi bi-clock mr-1"></i>
                                    <?php echo $item['duration']; ?> min
                                </span>
                                <span><i class="bi bi-tag mr-1"></i>
                                    <?php echo htmlspecialchars($item['type'] ?? 'Discussion'); ?>
                                </span>
                            </div>
                        </div>
                        <button
                            onclick="openCommentModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>')"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="bi bi-plus-circle mr-2"></i>Add Comment
                        </button>
                    </div>
                </div>

                <!-- Comments List -->
                <div class="p-6">
                    <?php if (empty($itemComments)): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">
                            No comments yet. Be the first to comment!
                        </p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($itemComments as $comment): ?>
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <?php if (!empty($comment['profile_picture'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($comment['profile_picture']); ?>"
                                                    alt="<?php echo htmlspecialchars($comment['author_name']); ?>"
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <span class="text-gray-700 dark:text-gray-300 font-semibold">
                                                    <?php echo strtoupper(substr($comment['author_name'], 0, 1)); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($comment['author_name']); ?>
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                                </span>
                                            </div>
                                            <p class="text-gray-700 dark:text-gray-300">
                                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Comment Modal -->
<div id="commentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Comment</h3>
            <p id="modal_item_title" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>

            <form method="POST">
                <input type="hidden" name="add_comment" value="1">
                <input type="hidden" name="item_id" id="modal_item_id">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Your Comment *
                    </label>
                    <textarea name="comment" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter your comment..."></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeCommentModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="bi bi-send mr-2"></i>Post Comment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCommentModal(itemId, itemTitle) {
        document.getElementById('modal_item_id').value = itemId;
        document.getElementById('modal_item_title').textContent = 'Commenting on: ' + itemTitle;
        document.getElementById('commentModal').classList.remove('hidden');
    }

    function closeCommentModal() {
        document.getElementById('commentModal').classList.add('hidden');
    }

    // Close modal on outside click
    document.getElementById('commentModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeCommentModal();
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>