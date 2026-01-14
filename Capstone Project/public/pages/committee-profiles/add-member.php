<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['committee_id'] ?? 0;
$committee = getCommitteeById($committeeId);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate new member ID
    $members = $_SESSION['committee_members'] ?? [];
    $newId = empty($members) ? 1 : max(array_column($members, 'member_id')) + 1;

    // Create new member
    $newMember = [
        'member_id' => $newId,
        'committee_id' => $committeeId,
        'name' => $_POST['name'],
        'role' => $_POST['role'],  // Committee role (Chairperson, Member, etc.)
        'position' => $_POST['position'] ?? 'Councilor',  // Job title
        'district' => $_POST['district'] ?? '',
        'contact_number' => $_POST['contact_number'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];

    // Add to session
    $_SESSION['committee_members'][] = $newMember;

    // Update committee member count
    foreach ($_SESSION['committees'] as &$c) {
        if ($c['id'] == $committeeId) {
            $c['members'] = count(getCommitteeMembers($committeeId));
            break;
        }
    }

    $_SESSION['success_message'] = 'Member added successfully';
    header('Location: members.php?id=' . $committeeId);
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Add Member';
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
            <li class="breadcrumb-item active">Add Member</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Member</h1>
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
                    <input type="text" name="name" required
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
                        <option value="Chairperson">Chairperson</option>
                        <option value="Vice-Chairperson">Vice-Chairperson</option>
                        <option value="Member" selected>Member</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Ex-Officio">Ex-Officio</option>
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
                        <option value="Councilor" selected>Councilor</option>
                        <option value="Mayor">Mayor</option>
                        <option value="Vice Mayor">Vice Mayor</option>
                        <option value="Board Member">Board Member</option>
                        <option value="Barangay Captain">Barangay Captain</option>
                        <option value="SK Chairperson">SK Chairperson</option>
                        <option value="Department Head">Department Head</option>
                        <option value="City Administrator">City Administrator</option>
                        <option value="Legal Officer">Legal Officer</option>
                        <option value="Budget Officer">Budget Officer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- District -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        District
                    </label>
                    <input type="text" name="district"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="District 1">
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Contact Number
                    </label>
                    <input type="tel" name="contact_number"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="09171234567">
                </div>

                <!-- Email -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input type="email" name="email"
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
                    <i class="bi bi-plus-circle mr-2"></i>Add Member
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>