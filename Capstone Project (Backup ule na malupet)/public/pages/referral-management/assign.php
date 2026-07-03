<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/ReferralHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/UserHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_assign']) && !empty($_POST['referral_ids'])) {
        $userId = $_POST['assigned_to_user_id'];
        $referralIds = $_POST['referral_ids'];

        foreach ($referralIds as $id) {
            $referral = getReferralById($id);
            if ($referral) {
                updateReferral($id, [
                    'assigned_member_id' => $userId,
                    'status' => 'Under Review',
                    'committee_id' => $referral['committee_id']
                ] + $referral);
            }
        }
        $_SESSION['success_message'] = count($referralIds) . ' referrals assigned successfully';
    } elseif (isset($_POST['referral_id'])) {
        $referralId = $_POST['referral_id'];
        $userId = $_POST['user_id'];

        $referral = getReferralById($referralId);
        if ($referral) {
            updateReferral($referralId, [
                'assigned_member_id' => $userId,
                'status' => 'Under Review',
                'committee_id' => $referral['committee_id']
            ] + $referral);
            $_SESSION['success_message'] = 'Referral assigned successfully';
        }
    }

    header('Location: assign.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Referral Assignment';
include '../../includes/header.php';

// Get all referrals and committees
$allReferrals = getAllReferrals();
$unassignedReferrals = array_filter($allReferrals, function ($ref) {
    return empty($ref['assigned_member_id']);
});
$assignedReferrals = array_filter($allReferrals, function ($ref) {
    return !empty($ref['assigned_to']);
});
$committees = getAllCommittees();
$users = UserHelper_getAllUsers();
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Referral Assignment</h1>
            <p class="text-gray-600 mt-1">Assign referrals to committee members</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-list"></i> All Referrals
        </a>
        <a href="tracking.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-graph-up"></i> Tracking
        </a>
        <a href="assign.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-person-plus"></i> Assignment
        </a>
        <a href="deadlines.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="bi bi-calendar-x"></i> Deadlines
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Unassigned</p>
            <i class="bi bi-exclamation-circle text-2xl text-orange-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?php echo count($unassignedReferrals); ?></p>
        <p class="text-xs text-orange-600 mt-1">Needs assignment</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Assigned</p>
            <i class="bi bi-person-check text-2xl text-green-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?php echo count($assignedReferrals); ?></p>
        <p class="text-xs text-green-600 mt-1">Currently assigned</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">Total</p>
            <i class="bi bi-list-check text-2xl text-red-500"></i>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?php echo count($allReferrals); ?></p>
        <p class="text-xs text-red-600 mt-1">All referrals</p>
    </div>
</div>

<!-- Bulk Assignment Form -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold mb-4"><i class="bi bi-people-fill mr-2"></i>Bulk Assignment</h2>
    <form method="POST" id="bulkAssignForm">
        <input type="hidden" name="bulk_assign" value="1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                <select name="assigned_to_user_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option value="">Select Member</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            (<?php echo htmlspecialchars($user['role_name'] ?? 'Member'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="bi bi-check-lg mr-2"></i>Assign Selected Referrals
                </button>
            </div>
        </div>

        <!-- Unassigned Referrals Table -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 text-red-600 border-gray-300 rounded">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Committee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($unassignedReferrals)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="bi bi-check-circle text-4xl mb-2"></i>
                                <p>All referrals are assigned</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($unassignedReferrals as $ref): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="referral_ids[]" value="<?php echo $ref['id']; ?>"
                                        class="referral-checkbox w-4 h-4 text-red-600 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900"><?php echo htmlspecialchars($ref['title']); ?>
                                </td>
                                <td class="px-4 py-3 text-gray-900"><?php echo htmlspecialchars($ref['committee_name']); ?></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                            ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                                        <?php echo $ref['priority']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        <?php echo $ref['status']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-900">
                                    <?php echo !empty($ref['deadline']) ? date('M j, Y', strtotime($ref['deadline'])) : 'No deadline'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Currently Assigned Referrals -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-bold mb-4"><i class="bi bi-person-check-fill mr-2"></i>Currently Assigned</h2>
    <?php if (empty($assignedReferrals)): ?>
        <p class="text-gray-500 text-center py-8">No assigned referrals yet</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($assignedReferrals as $ref): ?>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($ref['title']); ?></h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="bi bi-building mr-1"></i><?php echo htmlspecialchars($ref['committee_name']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                <i class="bi bi-person-badge mr-1"></i><?php echo htmlspecialchars($ref['assigned_to']); ?>
                            </p>
                            <span
                                class="inline-block mt-1 px-2 py-1 text-xs rounded-full <?php echo $ref['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                                    ($ref['status'] === 'Under Review' ? 'bg-red-100 text-red-800' :
                                        ($ref['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800')); ?>">
                                <?php echo $ref['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-3 text-sm text-gray-600">
                        <span
                            class="px-2 py-1 rounded-full <?php echo $ref['priority'] === 'High' ? 'bg-red-100 text-red-800' :
                                ($ref['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                            <?php echo $ref['priority']; ?> Priority
                        </span>
                        <?php if (!empty($ref['deadline'])): ?>
                            <span><i class="bi bi-calendar-x mr-1"></i>Due:
                                <?php echo date('M j, Y', strtotime($ref['deadline'])); ?></span>
                        <?php endif; ?>
                        <a href="view.php?id=<?php echo $ref['id']; ?>" class="text-red-600 hover:text-red-700 ml-auto">
                            View Details →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Select all checkbox functionality
    document.getElementById('selectAll').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.referral-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select all when individual checkboxes change
    document.querySelectorAll('.referral-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allCheckboxes = document.querySelectorAll('.referral-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.referral-checkbox:checked');
            document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
        });
    });
</script>

<div
    class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing <span class="font-medium">1</span> to
        <span class="font-medium"><?php echo count($unassignedReferrals) + count($assignedReferrals); ?></span> of
        <span class="font-medium"><?php echo count($unassignedReferrals) + count($assignedReferrals); ?></span> total
        referral(s)
    </div>
    <div class="text-sm text-gray-500 italic">
        Module: Referral Assignment
    </div>
</div>
</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>