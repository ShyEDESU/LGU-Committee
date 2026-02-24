<?php
// Suppress all errors to prevent output corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    $_SESSION['error_message'] = 'Meeting not found';
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$isCommitteeMember = isCommitteeMember($meeting['committee_id'], $userId);
$canManageVotes = canUpdate($userId, 'meetings', $meetingId);

// Handle Motion Creation (Chairman/Secretary)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_motion'])) {
    if (!$canManageVotes) {
        $_SESSION['error_message'] = 'Unauthorized: Only Committee Leadership or the Secretary can initiate motions.';
        header('Location: voting.php?id=' . $meetingId);
        exit();
    }

    if ($meeting['status'] !== 'Ongoing') {
        $_SESSION['error_message'] = 'Motions can only be initiated while the meeting is DELIBERATING (Ongoing).';
        header('Location: voting.php?id=' . $meetingId);
        exit();
    }

    $agendaItemId = $_POST['agenda_item_id'];
    $motionText = trim($_POST['motion_text']);
    $method = $_POST['voting_method'] ?? 'Voice Vote';

    if (empty($motionText)) {
        $_SESSION['error_message'] = 'Motion text cannot be empty.';
    } elseif (createVote($agendaItemId, ['motion_text' => $motionText, 'voting_method' => $method])) {
        $_SESSION['success_message'] = 'A new motion has been formally called for voting.';
    } else {
        $_SESSION['error_message'] = 'Failed to initiate motion.';
    }
    header('Location: voting.php?id=' . $meetingId);
    exit();
}

// Handle Member Vote Recording (Admin/Secretary)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_bulk_vote'])) {
    if (!$canManageVotes) {
        $_SESSION['error_message'] = 'Unauthorized: Only Committee Leadership or the Secretary can record official votes.';
        header('Location: voting.php?id=' . $meetingId);
        exit();
    }

    $voteId = $_POST['vote_id'];
    $votes = $_POST['member_votes'] ?? []; // Array of [userId => voteValue]

    foreach ($votes as $mId => $val) {
        if (!empty($val)) {
            recordMemberVote($voteId, $mId, $val);
        }
    }

    $_SESSION['success_message'] = 'Votes recorded successfully.';
    header('Location: voting.php?id=' . $meetingId);
    exit();
}

// Handle Finalizing Vote Result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize_vote'])) {
    if (!$canManageVotes) {
        $_SESSION['error_message'] = 'Unauthorized: Only Committee Leadership can finalize a vote.';
        header('Location: voting.php?id=' . $meetingId);
        exit();
    }

    $voteId = $_POST['vote_id'];
    $results = getVoteResults($voteId);

    // Update the vote record with the result
    global $conn;
    $stmt = $conn->prepare("UPDATE votes SET result = ? WHERE vote_id = ?");
    $stmt->bind_param("si", $results['result'], $voteId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Motion finalized with result: " . $results['result'];
    } else {
        $_SESSION['error_message'] = 'Failed to finalize vote.';
    }
    header('Location: voting.php?id=' . $meetingId);
    exit();
}

// Get data for display
$votes = getVotesByAgenda($meetingId);
$agendaItems = getAgendaByMeeting($meetingId);
$committeeMembers = getCommitteeMembers($meeting['committee_id']);
$stats = getAttendanceStats($meetingId);

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Voting';
include '../../includes/header.php';
?>

<nav class="mb-4" aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Meetings</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $meetingId; ?>" class="text-red-600">
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
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Voting & Resolutions</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition flex items-center shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i>Back to Meeting
            </a>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Details
            </a>
            <a href="attendance.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Attendance
            </a>
            <a href="minutes.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Minutes
            </a>
            <a href="documents.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Documents
            </a>
            <a href="voting.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                Voting
            </a>
        </nav>
    </div>
</div>

<!-- Temporal Lock Banner -->
<?php if ($meeting['status'] === 'Scheduled'): ?>
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-lock-fill text-blue-600 dark:text-blue-400 text-xl mr-3"></i>
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-300">Voting Interface Locked</p>
                <p class="text-sm text-gray-700 dark:text-gray-400">The committee cannot entertain motions until the session
                    has been formally COMMENCED by the Chairperson.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Motions List (2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <i class="bi bi-hand-thumbs-up text-red-600 mr-2"></i>Active Motions
            </h2>

            <?php
            $activeMotions = array_filter($votes, fn($v) => $v['result'] === 'Pending');
            if (empty($activeMotions)):
                ?>
                <div class="text-center py-12 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-xl">
                    <i class="bi bi-clipboard-x text-5xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                    <p class="text-gray-500 dark:text-gray-400">No active motions require voting at this time.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($activeMotions as $motion): ?>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div
                                class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <div>
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded">MOTION
                                        IN PROGRESS</span>
                                    <h3 class="font-bold text-gray-900 dark:text-white mt-1">
                                        <?php echo htmlspecialchars($motion['motion_text']); ?>
                                    </h3>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    <p>Agenda Item:
                                        <?php echo htmlspecialchars($motion['item_title']); ?>
                                    </p>
                                    <p>Method:
                                        <?php echo htmlspecialchars($motion['voting_method']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="p-4">
                                <?php if ($canManageVotes && $meeting['status'] === 'Ongoing'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="record_bulk_vote" value="1">
                                        <input type="hidden" name="vote_id" value="<?php echo $motion['id']; ?>">

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <?php foreach ($committeeMembers as $member):
                                                $currentVote = getMemberVote($motion['id'], $member['user_id']);
                                                ?>
                                                <div
                                                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        <?php echo htmlspecialchars($member['name']); ?>
                                                    </span>
                                                    <select name="member_votes[<?php echo $member['user_id']; ?>]"
                                                        class="text-xs border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-800">
                                                        <option value="">Not Cast</option>
                                                        <option value="Yes" <?php echo $currentVote === 'Yes' ? 'selected' : ''; ?>>Yes
                                                        </option>
                                                        <option value="No" <?php echo $currentVote === 'No' ? 'selected' : ''; ?>>No
                                                        </option>
                                                        <option value="Abstain" <?php echo $currentVote === 'Abstain' ? 'selected' : ''; ?>>Abstain</option>
                                                        <option value="Absent" <?php echo $currentVote === 'Absent' ? 'selected' : ''; ?>>
                                                            Absent</option>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div
                                            class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                                            <button type="submit"
                                                class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                                                Update Vote Record
                                            </button>

                                            <button type="submit" name="finalize_vote" value="1"
                                                onclick="return confirm('Finalize this vote? This will calculate the result and close the motion.')"
                                                class="text-sm bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold shadow-sm">
                                                FINALIZE RESOLUTION
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-8 text-center rounded-lg">
                                        <p class="text-gray-500">Wait for the Committee Secretary to record and finalize this
                                            resolution.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <i class="bi bi-clock-history text-gray-500 mr-2"></i>Resolved Motions
            </h2>

            <?php
            $resolvedMotions = array_filter($votes, fn($v) => $v['result'] !== 'Pending');
            if (empty($resolvedMotions)):
                ?>
                <p class="text-center text-gray-500 py-4">No resolutions have been finalized yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($resolvedMotions as $motion):
                        $res = getVoteResults($motion['id']);
                        $badgeClass = $res['result'] === 'Passed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <div
                            class="flex items-center justify-between p-4 border border-gray-100 dark:border-gray-700 rounded-lg">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($motion['motion_text']); ?>
                                </h4>
                                <p class="text-xs text-gray-500">Agenda Item:
                                    <?php echo htmlspecialchars($motion['item_title']); ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right text-xs text-gray-500 mr-4">
                                    <span>Y:
                                        <?php echo $res['yes']; ?>
                                    </span> •
                                    <span>N:
                                        <?php echo $res['no']; ?>
                                    </span> •
                                    <span>A:
                                        <?php echo $res['abstain']; ?>
                                    </span>
                                </div>
                                <span class="px-4 py-1.5 rounded-full font-bold text-sm <?php echo $badgeClass; ?>">
                                    <?php echo strtoupper($res['result']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Tools (1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <?php if ($canManageVotes && $meeting['status'] === 'Ongoing'): ?>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-lightning-fill text-yellow-500 mr-2"></i>Initiate Motion
                </h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="create_motion" value="1">

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Related Agenda Item</label>
                        <select name="agenda_item_id" required
                            class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                            <?php foreach ($agendaItems as $item):
                                $selected = (isset($_GET['agenda_item_id']) && $_GET['agenda_item_id'] == $item['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $item['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Formal Motion Text</label>
                        <textarea name="motion_text" rows="4" required
                            class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white"
                            placeholder="e.g., I move that the committee adopts the proposed budget for FY2025 as presented."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Voting Method</label>
                        <select name="voting_method"
                            class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                            <option value="Voice Vote">Voice Vote</option>
                            <option value="Roll Call">Roll Call</option>
                            <option value="Secret Ballot">Secret Ballot</option>
                            <option value="Show of Hands">Show of Hands</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-sm transition">
                        CALL FOR VOTE
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">Legislative Quorum</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">A motion requires a majority of the present members
                    to carry.</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Quorum:</span>
                        <span class="font-bold <?php echo $stats['has_quorum'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $stats['has_quorum'] ? 'ACHIEVED' : 'NOT MET'; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>