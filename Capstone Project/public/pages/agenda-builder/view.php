<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get agenda ID from URL
$meetingId = $_GET['id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    header('Location: index.php');
    exit();
}

// Get agenda items
$agendaItems = getAgendaByMeeting($meetingId);
$committee = getCommitteeById($meeting['committee_id']);

// Calculate total duration
$totalDuration = array_sum(array_column($agendaItems, 'duration'));

// Get meeting status
$agendaStatus = $meeting['agenda_status'] ?? 'Draft';

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'View Agenda';
include '../../includes/header.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-700 dark:text-green-300 text-xl mr-3"></i>
            <p class="text-green-700 dark:text-green-300 font-medium">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">View Agenda</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </p>
        </div>
        <div class="flex space-x-2">
            <!-- Status Change Dropdown (AJAX-based) -->
            <div class="inline">
                <select onchange="changeStatus(this.value)" id="statusSelect"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600 transition">
                    <option value="">Change Status...</option>
                    <?php if ($agendaStatus === 'Draft'): ?>
                        <option value="Under Review">📤 Submit for Review</option>
                    <?php endif; ?>
                    <?php if ($agendaStatus === 'Draft' || $agendaStatus === 'Under Review'): ?>
                        <option value="Approved">✅ Approve</option>
                    <?php endif; ?>
                    <?php if ($agendaStatus === 'Approved'): ?>
                        <option value="Published">📢 Publish</option>
                    <?php endif; ?>
                    <?php if ($agendaStatus !== 'Draft'): ?>
                        <option value="Draft">↩️ Revert to Draft</option>
                    <?php endif; ?>
                    <?php if ($agendaStatus !== 'Archived'): ?>
                        <option value="Archived">📁 Archive</option>
                    <?php endif; ?>
                    <?php if ($agendaStatus === 'Archived'): ?>
                        <option value="Draft">↩️ Unarchive (to Draft)</option>
                    <?php endif; ?>
                </select>
            </div>

            <button onclick="window.print()"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
            <a href="items.php?meeting_id=<?php echo $meetingId; ?>"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-pencil mr-2"></i> Edit Items
            </a>
            <a href="index.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>

<?php
// Check for active votes for this meeting
$activeVotes = getActiveVotesByMeeting($meetingId);
$userId = $_SESSION['user_id'] ?? 0;
$currCommitteeId = $meeting['committee_id'] ?? 0;
// We need CommitteeHelper for isCommitteeMember
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
$isCommitteeMember = isCommitteeMember($currCommitteeId, $userId);

if (!empty($activeVotes) && $isCommitteeMember): ?>
    <div class="bg-red-600 rounded-lg shadow-lg p-4 mb-6 text-white flex items-center justify-between">
        <div class="flex items-center">
            <div class="bg-red-500 rounded-full p-2 mr-4">
                <i class="bi bi-hand-thumbs-up-fill text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Active Voting in Progress</h3>
                <p class="text-blue-100 text-sm">There are active motions for this meeting that require your vote.</p>
            </div>
        </div>
        <a href="member-vote.php?meeting_id=<?php echo $meetingId; ?>"
            class="px-6 py-2 bg-white text-red-600 font-bold rounded-lg hover:bg-red-50 transition shadow-sm">
            Vote Now
        </a>
    </div>
<?php endif; ?>

<!-- Navigation Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="border-red-500 text-red-600 dark:text-red-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium">
                <i class="bi bi-list-ol mr-1"></i>Agenda Items
            </a>
            <a href="comments.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-chat-dots mr-1"></i>Comments
            </a>
            <a href="distribute.php?id=<?php echo $meetingId; ?>"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium transition">
                <i class="bi bi-send mr-1"></i>Distribution
            </a>
        </nav>
    </div>
</div>

<!-- Meeting Information -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Committee</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['committee_name']); ?>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Date & Time</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo !empty($meeting['date']) ? date('M j, Y', strtotime($meeting['date'])) : 'Not Set'; ?><br>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <?php echo !empty($meeting['time_start']) ? date('g:i A', strtotime($meeting['time_start'])) : ''; ?>
                </span>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['venue']); ?>
            </p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</h3>
            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full 
                <?php
                echo $agendaStatus === 'Draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                    ($agendaStatus === 'Under Review' ? 'bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300' :
                        ($agendaStatus === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                            ($agendaStatus === 'Published' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' :
                                'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300')));
                ?>">
                <?php echo $agendaStatus; ?>
            </span>
        </div>
    </div>
</div>

<!-- Agenda Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($agendaItems); ?>
                </p>
            </div>
            <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3">
                <i class="bi bi-list-check text-red-600 dark:text-red-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Estimated Duration</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo $totalDuration; ?> <span class="text-lg">min</span>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-clock text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">End Time (Est.)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php
                    $endTime = strtotime($meeting['time_start']) + ($totalDuration * 60);
                    echo date('g:i A', $endTime);
                    ?>
                </p>
            </div>
            <div class="bg-red-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-check text-red-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Agenda Items -->
<div
    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            <i class="bi bi-list-ol mr-2"></i> Agenda Items
        </h2>
    </div>

    <?php if (empty($agendaItems)): ?>
        <div class="p-12 text-center">
            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Agenda Items</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">This agenda doesn't have any items yet</p>
            <a href="items.php?meeting_id=<?php echo $meetingId; ?>"
                class="inline-block px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                <i class="bi bi-plus-lg mr-2"></i> Add Items
            </a>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            $currentTime = strtotime($meeting['time_start']);
            foreach ($agendaItems as $index => $item):
                $itemNumber = $item['item_number'] ?? ($index + 1);
                $startTime = date('g:i A', $currentTime);
                $itemDuration = $item['duration'] ?? 0;
                $currentTime += ($itemDuration * 60);
                $endTime = date('g:i A', $currentTime);
                ?>
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                <span class="text-xl font-bold text-red-600 dark:text-red-400">
                                    <?php echo $itemNumber; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-gray-600 dark:text-gray-400 mb-2">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['presenter'])): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-500">
                                            <i class="bi bi-person-badge mr-1"></i> Presenter:
                                            <?php echo htmlspecialchars($item['presenter']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo $startTime; ?> -
                                        <?php echo $endTime; ?>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                                        <i class="bi bi-clock mr-1"></i>
                                        <?php echo ($itemDuration ?? 0); ?> minutes
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Meeting Description -->
<?php if (!empty($meeting['description'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
            <i class="bi bi-info-circle mr-2"></i> Meeting Description
        </h2>
        <p class="text-gray-700 dark:text-gray-300">
            <?php echo nl2br(htmlspecialchars($meeting['description'])); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="bi bi-lightning mr-2"></i> Quick Actions
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="deliberation.php?meeting_id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
            <i class="bi bi-chat-left-text mr-2"></i> Start Deliberation
        </a>
        <a href="voting.php?meeting_id=<?php echo $meetingId; ?>"
            class="flex items-center justify-center px-4 py-3 bg-red-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-red-100 dark:hover:bg-blue-900/30 transition">
            <i class="bi bi-hand-thumbs-up mr-2"></i> Manage Voting
        </a>
    </div>
</div>

<script>
    function changeStatus(newStatus) {
        if (newStatus) {
            if (confirm('Are you sure you want to change the agenda status to: ' + newStatus + '?')) {
                document.getElementById('newStatusInput').value = newStatus;
                document.getElementById('statusForm').submit();
            } else {
                // Reset dropdown
                document.querySelector('select[onchange]').value = '';
            }
        }
    }
</script>

<style>
    @media print {

        .no-print,
        nav,
        header,
        footer,
        button,
        select,
        a[href*="edit"],
        a[href*="Back"],
        .bi-lightning {
            display: none !important;
        }

        body {
            background: white;
        }

        .dark\:bg-gray-800,
        .dark\:bg-gray-700 {
            background: white !important;
        }

        .dark\:text-white,
        .dark\:text-gray-300 {
            color: black !important;
        }
    }
</style>

<script>
    // AJAX function to change status without page reload
    function changeStatus(newStatus) {
        if (!newStatus) return;

        // Confirm the change
        if (!confirm(`Are you sure you want to change the agenda status to: ${newStatus}?`)) {
            // Reset dropdown if user cancels
            document.getElementById('statusSelect').value = '';
            return;
        }

        // Show loading state
        const select = document.getElementById('statusSelect');
        const originalHTML = select.innerHTML;
        select.disabled = true;
        select.innerHTML = '<option>Updating...</option>';

        // Send AJAX request
        fetch('update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `meeting_id=<?php echo $meetingId; ?>&new_status=${encodeURIComponent(newStatus)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showMessage(data.message, 'success');

                    // Update the status badge on the page
                    updateStatusBadge(data.new_status);

                    // Reset and re-enable dropdown
                    select.disabled = false;
                    select.innerHTML = originalHTML;
                    select.value = '';

                    // Update dropdown options based on new status
                    updateDropdownOptions(data.new_status);
                } else {
                    // Show error message
                    showMessage(data.message || 'Failed to update status', 'error');

                    // Restore dropdown
                    select.disabled = false;
                    select.innerHTML = originalHTML;
                    select.value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while updating the status', 'error');

                // Restore dropdown
                select.disabled = false;
                select.innerHTML = originalHTML;
                select.value = '';
            });
    }

    // Function to update the status badge
    function updateStatusBadge(newStatus) {
        const statusBadge = document.querySelector('.inline-block.px-3.py-1.text-sm.font-semibold.rounded-full');
        if (statusBadge) {
            // Update badge text
            statusBadge.textContent = newStatus;

            // Update badge color based on status
            statusBadge.className = 'inline-block px-3 py-1 text-sm font-semibold rounded-full ';
            if (newStatus === 'Draft') {
                statusBadge.className += ' bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
            } else if (newStatus === 'Under Review') {
                statusBadge.className += ' bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300';
            } else if (newStatus === 'Approved') {
                statusBadge.className += ' bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
            } else if (newStatus === 'Published') {
                statusBadge.className += 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
            }
        }
    }

    // Function to update dropdown options based on current status
    function updateDropdownOptions(currentStatus) {
        const select = document.getElementById('statusSelect');
        let options = '<option value="">Change Status...</option>';

        if (currentStatus === 'Draft') {
            options += '<option value="Under Review">📤 Submit for Review</option>';
            options += '<option value="Approved">✅ Approve</option>';
        } else if (currentStatus === 'Under Review') {
            options += '<option value="Approved">✅ Approve</option>';
            options += '<option value="Draft">↩️ Revert to Draft</option>';
        } else if (currentStatus === 'Approved') {
            options += '<option value="Published">📢 Publish</option>';
            options += '<option value="Draft">↩️ Revert to Draft</option>';
        } else if (currentStatus === 'Published') {
            options += '<option value="Draft">↩️ Revert to Draft</option>';
        }

        // Always allow archiving unless already archived
        if (currentStatus !== 'Archived') {
            options += '<option value="Archived">📁 Archive</option>';
        } else {
            // If archived, maybe allow unarchiving?
            options += '<option value="Draft">↩️ Unarchive (to Draft)</option>';
        }

        select.innerHTML = options;
    }

    // Function to show toast messages
    function showMessage(message, type) {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-x-circle';

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2`;
        toast.innerHTML = `
            <i class="bi ${icon}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
</div> <!-- Closing module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>