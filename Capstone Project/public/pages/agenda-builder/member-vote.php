<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['meeting_id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    die("Meeting not found.");
}

$userId = $_SESSION['user_id'];
$isMember = isCommitteeMember($meeting['committee_id'], $userId);

if (!$isMember) {
    $_SESSION['error_message'] = "You are not a member of this committee and cannot participate in voting.";
    header('Location: ../committee-meetings/view.php?id=' . $meetingId);
    exit();
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cast_vote'])) {
    $voteId = $_POST['vote_id'];
    $voteValue = $_POST['vote_value']; // Yes, No, Abstain

    if (recordMemberVote($voteId, $userId, $voteValue)) {
        $_SESSION['success_message'] = "Your vote has been recorded.";
    } else {
        $_SESSION['error_message'] = "Failed to record your vote.";
    }
    header('Location: member-vote.php?meeting_id=' . $meetingId);
    exit();
}

$activeVotes = getActiveVotesByMeeting($meetingId);
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Cast Your Vote';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../committee-meetings/index.php" class="text-red-600">Meetings</a></li>
            <li class="breadcrumb-item"><a href="../committee-meetings/view.php?id=<?php echo $meetingId; ?>"
                    class="text-red-600">
                    <?php echo htmlspecialchars($meeting['title']); ?>
                </a></li>
            <li class="breadcrumb-item active">Voting</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700 dark:text-green-300">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
            <p class="text-red-700 dark:text-red-300">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Active Voting Motions</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Cast your vote on open items for this meeting.</p>
    </div>

    <?php if (empty($activeVotes)): ?>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center border border-gray-200 dark:border-gray-700">
            <i class="bi bi-hand-thumbs-up text-6xl text-gray-300 mb-4 block"></i>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Active Votes</h2>
            <p class="text-gray-600 dark:text-gray-400">There are currently no open motions for voting in this meeting.</p>
            <div class="mt-6">
                <a href="../committee-meetings/view.php?id=<?php echo $meetingId; ?>"
                    class="inline-flex items-center text-red-600 hover:text-red-700 font-semibold">
                    <i class="bi bi-arrow-left mr-2"></i> Back to Meeting Details
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($activeVotes as $vote):
                $myVote = getMemberVote($vote['id'], $userId);
                ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mb-2 inline-block">
                                    <?php echo htmlspecialchars($vote['item_title']); ?>
                                </span>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($vote['motion_text']); ?>
                                </h2>
                            </div>
                            <div class="text-right">
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                    OPEN
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <i class="bi bi-info-circle mr-2"></i> <strong>Voting Method:</strong>
                                <?php echo htmlspecialchars($vote['voting_method']); ?>
                            </p>
                            <?php if ($myVote): ?>
                                <p class="mt-2 text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    <i class="bi bi-check2-circle mr-2"></i> You voted: <span class="uppercase">
                                        <?php echo $myVote; ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="grid grid-cols-3 gap-4">
                            <input type="hidden" name="cast_vote" value="1">
                            <input type="hidden" name="vote_id" value="<?php echo $vote['id']; ?>">

                            <button type="submit" name="vote_value" value="Yes"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all <?php echo $myVote === 'Yes' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700' : 'border-gray-100 dark:border-gray-700 hover:border-green-200 hover:bg-green-50/50 text-gray-600 dark:text-gray-400'; ?>">
                                <i class="bi bi-hand-thumbs-up-fill text-3xl mb-2"></i>
                                <span class="font-bold">YES</span>
                            </button>

                            <button type="submit" name="vote_value" value="No"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all <?php echo $myVote === 'No' ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700' : 'border-gray-100 dark:border-gray-700 hover:border-red-200 hover:bg-red-50/50 text-gray-600 dark:text-gray-400'; ?>">
                                <i class="bi bi-hand-thumbs-down-fill text-3xl mb-2"></i>
                                <span class="font-bold">NO</span>
                            </button>

                            <button type="submit" name="vote_value" value="Abstain"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all <?php echo $myVote === 'Abstain' ? 'border-gray-400 bg-gray-50 dark:bg-gray-700/50 text-gray-700' : 'border-gray-100 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 text-gray-600 dark:text-gray-400'; ?>">
                                <i class="bi bi-dash-circle-fill text-3xl mb-2"></i>
                                <span class="font-bold">ABSTAIN</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .rounded-xl {
        border-radius: 1rem;
    }
</style>

<?php include '../../includes/footer.php'; ?>