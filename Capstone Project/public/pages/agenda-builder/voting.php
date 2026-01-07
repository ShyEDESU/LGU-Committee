<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Voting Management';
include '../../includes/header.php';

// Get meeting ID from URL
$meetingId = $_GET['meeting_id'] ?? 0;
$meeting = $meetingId ? getMeetingById($meetingId) : null;

// Get all meetings for selection
$allMeetings = getAllMeetings();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_vote'])) {
        createVote($_POST['agenda_item_id'], [
            'motion_text' => $_POST['motion_text'],
            'voting_method' => $_POST['voting_method']
        ]);
        header('Location: voting.php?meeting_id=' . $_POST['meeting_id'] . '&created=1');
        exit();
    } elseif (isset($_POST['record_vote'])) {
        recordMemberVote($_POST['vote_id'], $_POST['member_id'], $_POST['vote']);
        header('Location: voting.php?meeting_id=' . $_POST['meeting_id'] . '&recorded=1');
        exit();
    }
}

// Get agenda items and votes if meeting is selected
$agendaItems = [];
$votes = [];
$committee = null;
$committeeMembers = [];

if ($meeting) {
    $agendaItems = getAgendaByMeeting($meetingId);
    $votes = getVotesByAgenda($meetingId);
    $committee = getCommitteeById($meeting['committee_id']);
    $committeeMembers = getCommitteeMembers($meeting['committee_id']);
}
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Voting Management</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Record and track voting on agenda items</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

<!-- Meeting Selection -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-calendar-event mr-2"></i> Select Meeting
    </h2>
    <form method="GET" class="flex items-end space-x-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meeting</label>
            <select name="meeting_id" required
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">Select a meeting...</option>
                <?php foreach ($allMeetings as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $meetingId == $m['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['title']); ?> - <?php echo date('M j, Y', strtotime($m['date'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            Load Meeting
        </button>
    </form>
</div>

<?php if ($meeting): ?>
    <!-- Meeting Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-info-circle text-blue-700 dark:text-blue-300 text-xl mr-3"></i>
            <div>
                <p class="text-blue-700 dark:text-blue-300 font-medium"><?php echo htmlspecialchars($meeting['title']); ?>
                </p>
                <p class="text-sm text-blue-600 dark:text-blue-400">
                    <?php echo htmlspecialchars($meeting['committee_name']); ?> •
                    <?php echo count($committeeMembers); ?> Members
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Votes List -->
        <div class="lg:col-span-2 space-y-4">
            <?php if (empty($votes)): ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <i class="bi bi-hand-thumbs-up text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Votes Recorded</h3>
                    <p class="text-gray-600 dark:text-gray-400">Create a vote using the form on the right</p>
                </div>
            <?php else: ?>
                <?php foreach ($votes as $vote):
                    $results = getVoteResults($vote['id']);
                    ?>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                                <?php echo htmlspecialchars($vote['motion_text']); ?>
                            </h3>
                            <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                <span><i class="bi bi-check-square mr-1"></i> <?php echo $vote['voting_method']; ?></span>
                                <span><i class="bi bi-clock mr-1"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($vote['created_date'])); ?></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <!-- Vote Results -->
                            <div class="grid grid-cols-4 gap-4 mb-4">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                        <?php echo $results['yes']; ?></div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Yes</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-red-600 dark:text-red-400"><?php echo $results['no']; ?>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">No</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                                        <?php echo $results['abstain']; ?></div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Abstain</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-400 dark:text-gray-500">
                                        <?php echo $results['absent']; ?></div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Absent</div>
                                </div>
                            </div>

                            <!-- Result Badge -->
                            <div class="text-center">
                                <span class="inline-block px-4 py-2 text-lg font-bold rounded-full 
                                    <?php
                                    echo $results['result'] === 'Passed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                        ($results['result'] === 'Failed' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300');
                                    ?>">
                                    <?php echo $results['result']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Create Vote Form -->
        <div class="lg:col-span-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-plus-lg mr-2"></i> Create Vote
                </h2>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="create_vote" value="1">
                    <input type="hidden" name="meeting_id" value="<?php echo $meetingId; ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Agenda Item <span class="text-red-600">*</span>
                        </label>
                        <select name="agenda_item_id" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select item...</option>
                            <?php foreach ($agendaItems as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motion Text <span class="text-red-600">*</span>
                        </label>
                        <textarea name="motion_text" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter the motion to be voted on..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Voting Method <span class="text-red-600">*</span>
                        </label>
                        <select name="voting_method" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                            <option value="Voice Vote">Voice Vote</option>
                            <option value="Roll Call">Roll Call</option>
                            <option value="Secret Ballot">Secret Ballot</option>
                            <option value="Show of Hands">Show of Hands</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        <i class="bi bi-plus-lg mr-2"></i> Create Vote
                    </button>
                </form>

                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <i class="bi bi-info-circle mr-1"></i> After creating a vote, you can record individual member votes
                        through the committee meeting interface.
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <i class="bi bi-hand-thumbs-up text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Select a Meeting</h3>
        <p class="text-gray-600 dark:text-gray-400">Choose a meeting above to manage voting</p>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>