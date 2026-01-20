<?php
// Suppress all errors to prevent output corruption
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    // Don't redirect - let page load with error message
    $meeting = [
        'id' => $meetingId,
        'title' => 'Meeting Not Found',
        'committee_id' => 0,
        'committee_name' => 'Unknown',
        'date' => date('Y-m-d'),
        'time_start' => '00:00',
        'status' => 'Unknown'
    ];
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $userId = intval($_POST['user_id']);
    $status = $_POST['status'] ?? 'absent';

    if (markAttendance($meetingId, $userId, $status)) {
        $_SESSION['success_message'] = 'Attendance marked successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to mark attendance';
    }

    header('Location: attendance.php?id=' . $meetingId);
    exit();
}

// Handle syncing assigned members
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_members'])) {
    $assignedUserIds = $_POST['assigned_users'] ?? [];
    if (syncMeetingInvitees($meetingId, $assignedUserIds)) {
        $_SESSION['success_message'] = 'Meeting assignments updated successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to update meeting assignments';
    }
    header('Location: attendance.php?id=' . $meetingId);
    exit();
}

// Get assigned invitees (assigned members) and attendance records
$invitees = getMeetingInvitees($meetingId);
$committeeMembers = getCommitteeMembers($meeting['committee_id']);
$attendanceRecords = getAttendanceRecords($meetingId);
$stats = getAttendanceStats($meetingId);

// Create a map of attendance by user_id for easy lookup
$attendanceMap = [];
foreach ($attendanceRecords as $record) {
    $attendanceMap[$record['user_id']] = $record;
}

// Map of currently assigned user IDs for the modal
$assignedUserIdsList = array_column($invitees, 'user_id');

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Meeting Attendance';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Meetings</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $meetingId; ?>" class="text-red-600">
                    <?php echo htmlspecialchars($meeting['title']); ?>
                </a></li>
            <li class="breadcrumb-item active">Attendance</li>
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meeting Attendance</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <?php echo htmlspecialchars($meeting['title']); ?>
                </p>
            </div>
            <div class="flex gap-2">
                <button onclick="openManageMembersModal()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition border border-transparent flex items-center shadow-sm">
                    <i class="bi bi-person-plus-fill mr-2"></i>Manage Assigned Members
                </button>
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
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Member</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Position</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Check-in Time</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($invitees)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="bi bi-people text-6xl mb-4 block"></i>
                                No members assigned to this meeting. Use "Manage Assigned Members" to add them.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invitees as $invitee):
                            $attendance = $attendanceMap[$invitee['user_id']] ?? null;
                            $status = $attendance['status'] ?? 'not_marked';
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-red-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold">
                                                <?php echo strtoupper(substr($invitee['name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($invitee['name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($invitee['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($invitee['position'] ?? 'Member'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($status === 'present'): ?>
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            <i class="bi bi-check-circle mr-1"></i> Present
                                        </span>
                                    <?php elseif ($status === 'absent'): ?>
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                            <i class="bi bi-x-circle mr-1"></i> Absent
                                        </span>
                                    <?php elseif ($status === 'excused'): ?>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo $attendance && $attendance['check_in_time'] ? date('g:i A', strtotime($attendance['check_in_time'])) : '—'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        onclick="openAttendanceModal(<?php echo $invitee['user_id']; ?>, '<?php echo htmlspecialchars($invitee['name'], ENT_QUOTES); ?>', '<?php echo $status; ?>')"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <i class="bi bi-pencil mr-1"></i>Mark
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Mark Attendance</h3>
            <form method="POST" class="text-left">
                <input type="hidden" name="mark_attendance" value="1">
                <input type="hidden" name="user_id" id="modal_user_id">

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
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="excused">Excused</option>
                    </select>
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

<!-- Manage Members Modal -->
<div id="manageMembersModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Assigned Members</h3>
                <button onclick="closeManageMembersModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Select the members who are assigned to attend this specific meeting. Users checked below will appear in
                the attendance list.
            </p>
            <form method="POST">
                <input type="hidden" name="sync_members" value="1">
                <div class="max-h-[400px] overflow-y-auto mb-6 border border-gray-100 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Assigned
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($committeeMembers as $m): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="checkbox" name="assigned_users[]" value="<?php echo $m['user_id']; ?>"
                                            <?php echo in_array($m['user_id'], $assignedUserIdsList) ? 'checked' : ''; ?>
                                            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($m['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($m['role']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeManageMembersModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="bi bi-save mr-2"></i>Update Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAttendanceModal(userId, memberName, status) {
        document.getElementById('modal_user_id').value = userId;
        document.getElementById('modal_member_name').textContent = memberName;
        document.getElementById('modal_status').value = status !== 'not_marked' ? status : 'present';
        document.getElementById('attendanceModal').classList.remove('hidden');
    }

    function closeAttendanceModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
    }

    function openManageMembersModal() {
        document.getElementById('manageMembersModal').classList.remove('hidden');
    }

    function closeManageMembersModal() {
        document.getElementById('manageMembersModal').classList.add('hidden');
    }

    // Modal closing logic
    window.onclick = function (event) {
        const attendanceModal = document.getElementById('attendanceModal');
        const manageMembersModal = document.getElementById('manageMembersModal');
        if (event.target == attendanceModal) {
            closeAttendanceModal();
        }
        if (event.target == manageMembersModal) {
            closeManageMembersModal();
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>