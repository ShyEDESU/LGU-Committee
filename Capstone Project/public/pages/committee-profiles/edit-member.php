<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['committee_id'] ?? 0;
$memberId = $_GET['member_id'] ?? 0;

$committee = getCommitteeById($committeeId);
if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Find the member to edit
$members = $_SESSION['committee_members'] ?? [];
$member = null;
foreach ($members as $m) {
    if ($m['member_id'] == $memberId && $m['committee_id'] == $committeeId) {
        $member = $m;
        break;
    }
}

if (!$member) {
    $_SESSION['error_message'] = 'Member not found';
    header('Location: members.php?id=' . $committeeId);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update member in session
    foreach ($_SESSION['committee_members'] as &$m) {
        if ($m['member_id'] == $memberId && $m['committee_id'] == $committeeId) {
            $m['name'] = $_POST['name'];
            $m['role'] = $_POST['role'];
            $m['position'] = $_POST['position'];
            $m['district'] = $_POST['district'] ?? '';
            $m['contact_number'] = $_POST['contact_number'] ?? '';
            $m['email'] = $_POST['email'] ?? '';
            break;
        }
    }

    $_SESSION['success_message'] = 'Member updated successfully';
    header('Location: members.php?id=' . $committeeId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Member';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $committeeId; ?>" class="text-red-600">
                    <?php echo htmlspecialchars($committee['name']); ?>
                </a></li>
            <li class="breadcrumb-item"><a href="members.php?id=<?php echo $committeeId; ?>"
                    class="text-red-600">Members</a></li>
            <li class="breadcrumb-item active">Edit Member</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Member</h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?php echo htmlspecialchars($committee['name']); ?>
            </p>
        </div>
        <a href="members.php?id=<?php echo $committeeId; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back to Members
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Full Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($member['name']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Hon. Juan Dela Cruz">
                </div>

                <!-- Committee Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Role <span class="text-red-600">*</span>
                    </label>
                    <select name="role" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Role</option>
                        <option value="Chairperson" <?php echo $member['role'] === 'Chairperson' ? 'selected' : ''; ?>
                            >Chairperson</option>
                        <option value="Vice-Chairperson" <?php echo $member['role'] === 'Vice-Chairperson' ? 'selected' : ''; ?>>Vice-Chairperson</option>
                        <option value="Member" <?php echo $member['role'] === 'Member' ? 'selected' : ''; ?>>Member
                        </option>
                        <option value="Secretary" <?php echo $member['role'] === 'Secretary' ? 'selected' : ''; ?>
                            >Secretary</option>
                        <option value="Ex-Officio" <?php echo $member['role'] === 'Ex-Officio' ? 'selected' : ''; ?>
                            >Ex-Officio</option>
                    </select>
                </div>

                <!-- Position/Job Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Position/Job Title <span class="text-red-600">*</span>
                    </label>
                    <select name="position" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Position</option>
                        <option value="Councilor" <?php echo $member['position'] === 'Councilor' ? 'selected' : ''; ?>
                            >Councilor</option>
                        <option value="Mayor" <?php echo $member['position'] === 'Mayor' ? 'selected' : ''; ?>>Mayor
                        </option>
                        <option value="Vice Mayor" <?php echo $member['position'] === 'Vice Mayor' ? 'selected' : ''; ?>
                            >Vice Mayor</option>
                        <option value="Board Member" <?php echo $member['position'] === 'Board Member' ? 'selected' : ''; ?>>Board Member</option>
                        <option value="Barangay Captain" <?php echo $member['position'] === 'Barangay Captain' ? 'selected' : ''; ?>>Barangay Captain</option>
                        <option value="SK Chairperson" <?php echo $member['position'] === 'SK Chairperson' ? 'selected' : ''; ?>>SK Chairperson</option>
                        <option value="Department Head" <?php echo $member['position'] === 'Department Head' ? 'selected' : ''; ?>>Department Head</option>
                        <option value="City Administrator" <?php echo $member['position'] === 'City Administrator' ? 'selected' : ''; ?>>City Administrator</option>
                        <option value="Legal Officer" <?php echo $member['position'] === 'Legal Officer' ? 'selected' : ''; ?>>Legal Officer</option>
                        <option value="Budget Officer" <?php echo $member['position'] === 'Budget Officer' ? 'selected' : ''; ?>>Budget Officer</option>
                        <option value="Other" <?php echo $member['position'] === 'Other' ? 'selected' : ''; ?>>Other
                        </option>
                    </select>
                </div>

                <!-- District -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        District
                    </label>
                    <input type="text" name="district"
                        value="<?php echo htmlspecialchars($member['district'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="District 1">
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Contact Number
                    </label>
                    <input type="tel" name="contact_number"
                        value="<?php echo htmlspecialchars($member['contact_number'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="09171234567">
                </div>

                <!-- Email -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="member@legislature.gov">
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="members.php?id=<?php echo $committeeId; ?>"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-check-circle mr-2"></i>Update Member
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>