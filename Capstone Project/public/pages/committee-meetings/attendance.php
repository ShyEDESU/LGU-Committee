<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

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

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $memberId = $_POST['member_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    markAttendance($meetingId, $memberId, $status, $notes);
    $_SESSION['success_message'] = 'Attendance marked successfully';
    header('Location: attendance.php?id=' . $meetingId);
    exit();
}

// Get committee and members
$committee = getCommitteeById($meeting['committee_id']);
$members = getCommitteeMembers($meeting['committee_id']);
$attendance = getMeetingAttendance($meetingId);
$stats = getAttendanceStats($meetingId);

// Create attendance lookup
$attendanceLookup = [];
foreach ($attendance as $record) {
    $attendanceLookup[$record['member_id']] = $record;
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Attendance';
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meeting Attendance</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
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
                class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
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
        </nav>
    </div>
</div>

<!-- Attendance Statistics -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Members</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?php echo $stats['total_members']; ?>
                </p>
            </div>
            <i class="bi bi-people text-3xl text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Present</p>
                <p class="text-2xl font-bold text-green-600">
                    <?php echo $stats['present']; ?>
                </p>
            </div>
            <i class="bi bi-check-circle text-3xl text-green-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Absent</p>
                <p class="text-2xl font-bold text-red-600">
                    <?php echo $stats['absent']; ?>
                </p>
            </div>
            <i class="bi bi-x-circle text-3xl text-red-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Excused</p>
                <p class="text-2xl font-bold text-yellow-600">
                    <?php echo $stats['excused']; ?>
                </p>
            </div>
            <i class="bi bi-dash-circle text-3xl text-yellow-400"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Attendance Rate</p>
                <p class="text-2xl font-bold text-blue-600">
                    <?php echo $stats['attendance_rate']; ?>%
                </p>
            </div>
            <i class="bi bi-graph-up text-3xl text-blue-400"></i>
        </div>
    </div>
</div>

<!-- Quorum Status -->
<div class="mb-6">
    <div
        class="bg-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-900/20 border-l-4 border-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-500 p-4">
        <div class="flex items-center">
            <i
                class="bi bi-<?php echo $stats['has_quorum'] ? 'check' : 'exclamation'; ?>-circle text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-600 dark:text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-400 text-xl mr-3"></i>
            <div>
                <p
                    class="font-semibold text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-900 dark:text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-300">
                    <?php echo $stats['has_quorum'] ? 'Quorum Achieved' : 'No Quorum'; ?>
                </p>
                <p
                    class="text-sm text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-700 dark:text-<?php echo $stats['has_quorum'] ? 'green' : 'red'; ?>-400">
                    <?php echo $stats['present']; ?> out of
                    <?php echo $stats['quorum_required']; ?> required members present
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Attendance List -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Member Attendance</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Member
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Position
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Notes
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($members as $member):
                    $attendanceRecord = $attendanceLookup[$member['member_id']] ?? null;
                    $status = $attendanceRecord['status'] ?? 'Not Marked';
                    $notes = $attendanceRecord['notes'] ?? '';
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">
                                        <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($member['position']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($status === 'Present'): ?>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <i class="bi bi-check-circle mr-1"></i> Present
                                </span>
                            <?php elseif ($status === 'Absent'): ?>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    <i class="bi bi-x-circle mr-1"></i> Absent
                                </span>
                            <?php elseif ($status === 'Excused'): ?>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                    <i class="bi bi-dash-circle mr-1"></i> Excused
                                </span>
                            <?php else: ?>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Not Marked
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                <?php echo htmlspecialchars($notes); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button
                                onclick="openAttendanceModal(<?php echo $member['member_id']; ?>, '<?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>', '<?php echo $status; ?>', '<?php echo htmlspecialchars($notes, ENT_QUOTES); ?>')"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                <i class="bi bi-pencil mr-1"></i>Mark
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Mark Attendance</h3>
            <form method="POST">
                <input type="hidden" name="mark_attendance" value="1">
                <input type="hidden" name="member_id" id="modal_member_id">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Member
                    </label>
                    <p id="modal_member_name" class="text-gray-900 dark:text-white font-semibold"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status *
                    </label>
                    <select name="status" id="modal_status" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Excused">Excused</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notes
                    </label>
                    <textarea name="notes" id="modal_notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Optional notes..."></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAttendanceModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAttendanceModal(memberId, memberName, status, notes) {
        document.getElementById('modal_member_id').value = memberId;
        document.getElementById('modal_member_name').textContent = memberName;
        document.getElementById('modal_status').value = status !== 'Not Marked' ? status : 'Present';
        document.getElementById('modal_notes').value = notes;
        document.getElementById('attendanceModal').classList.remove('hidden');
    }

    function closeAttendanceModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
    }

    // Close modal on outside click
    document.getElementById('attendanceModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeAttendanceModal();
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>