<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $committeeId = $_POST['committee_id'] ?? 0;
    $committee = getCommitteeById($committeeId);
    
    $data = [
        'committee_id' => $committeeId,
        'committee_name' => $committee['name'] ?? '',
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date' => $_POST['date'] ?? '',
        'time_start' => $_POST['time_start'] ?? '',
        'time_end' => $_POST['time_end'] ?? '',
        'venue' => $_POST['venue'] ?? '',
        'referral_id' => $_POST['referral_id'] ?? null,
        'is_public' => isset($_POST['is_public']) ? true : false
    ];
    
    // Validation
    $errors = [];
    if (empty($data['title'])) $errors[] = 'Meeting title is required';
    if (empty($data['committee_id'])) $errors[] = 'Committee is required';
    if (empty($data['date'])) $errors[] = 'Date is required';
    if (empty($data['time_start'])) $errors[] = 'Start time is required';
    if (empty($data['venue'])) $errors[] = 'Venue is required';
    
    if (empty($errors)) {
        $newId = createMeeting($data);
        $_SESSION['success_message'] = 'Meeting scheduled successfully!';
        header('Location: view.php?id=' . $newId);
        exit();
    }
}

// Get committees for dropdown
$committees = getAllCommittees();
$preselectedCommittee = $_GET['committee'] ?? '';

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Schedule Meeting';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Meetings</a></li>
            <li class="breadcrumb-item active">Schedule Meeting</li>
        </ol>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Schedule New Meeting</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Create a new committee meeting</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
            <i class="bi bi-x-lg mr-2"></i>Cancel
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <ul class="list-disc list-inside text-red-700">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Meeting Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                    placeholder="e.g., 2025 Budget Review"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Committee <span class="text-red-500">*</span>
                </label>
                <select name="committee_id" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Committee</option>
                    <?php foreach ($committees as $committee): ?>
                        <option value="<?php echo $committee['id']; ?>" 
                            <?php echo ($preselectedCommittee == $committee['id'] || ($_POST['committee_id'] ?? '') == $committee['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($committee['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Venue <span class="text-red-500">*</span>
                </label>
                <select name="venue" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Venue</option>
                    <option value="City Hall Conference Room A" <?php echo ($_POST['venue'] ?? '') === 'City Hall Conference Room A' ? 'selected' : ''; ?>>City Hall Conference Room A</option>
                    <option value="City Hall Conference Room B" <?php echo ($_POST['venue'] ?? '') === 'City Hall Conference Room B' ? 'selected' : ''; ?>>City Hall Conference Room B</option>
                    <option value="City Hall Main Hall" <?php echo ($_POST['venue'] ?? '') === 'City Hall Main Hall' ? 'selected' : ''; ?>>City Hall Main Hall</option>
                    <option value="Session Hall" <?php echo ($_POST['venue'] ?? '') === 'Session Hall' ? 'selected' : ''; ?>>Session Hall</option>
                    <option value="Virtual Meeting" <?php echo ($_POST['venue'] ?? '') === 'Virtual Meeting' ? 'selected' : ''; ?>>Virtual Meeting</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date" required value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Start Time <span class="text-red-500">*</span>
                </label>
                <input type="time" name="time_start" required value="<?php echo htmlspecialchars($_POST['time_start'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    End Time
                </label>
                <input type="time" name="time_end" value="<?php echo htmlspecialchars($_POST['time_end'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Related Referral (Optional)
                </label>
                <select name="referral_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option value="">None</option>
                    <?php
                    $pendingReferrals = getReferralsByStatus('Pending');
                    $underReviewReferrals = getReferralsByStatus('Under Review');
                    $allAvailableReferrals = array_merge($pendingReferrals, $underReviewReferrals);
                    foreach ($allAvailableReferrals as $ref):
                    ?>
                        <option value="<?php echo $ref['id']; ?>" <?php echo ($_POST['referral_id'] ?? '') == $ref['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ref['title']); ?> (<?php echo $ref['status']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Link this meeting to a specific referral for discussion</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" rows="4" placeholder="Meeting purpose and objectives..."
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_public" value="1" <?php echo isset($_POST['is_public']) ? 'checked' : 'checked'; ?>
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Public Meeting (visible on public portal)</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="index.php" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="bi bi-calendar-check mr-2"></i>Schedule Meeting
            </button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
