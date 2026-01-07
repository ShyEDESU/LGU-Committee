<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$meeting = getMeetingById($id);

if (!$meeting) {
    $_SESSION['error_message'] = 'Meeting not found';
    header('Location: index.php');
    exit();
}

// Handle delete
if (isset($_POST['delete'])) {
    deleteMeeting($id);
    $_SESSION['success_message'] = 'Meeting deleted successfully';
    header('Location: index.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = $meeting['title'];
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Meetings</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($meeting['title']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700"><?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($meeting['title']); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($meeting['committee_name']); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-arrow-left mr-2"></i>Back to List
            </a>
            <a href="edit.php?id=<?php echo $id; ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-pencil mr-2"></i>Edit
            </a>
            <a href="../committee-profiles/view.php?id=<?php echo $meeting['committee_id']; ?>"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="bi bi-building mr-2"></i>View Committee
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Meeting Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Committee</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($meeting['committee_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <span
                            class="inline-block px-3 py-1 <?php echo $meeting['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-800' : ($meeting['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?> rounded-full text-sm">
                            <?php echo htmlspecialchars($meeting['status']); ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Date</p>
                        <p class="font-semibold"><?php echo date('F j, Y', strtotime($meeting['date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Time</p>
                        <p class="font-semibold">
                            <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                            <?php if (!empty($meeting['time_end'])): ?>
                                - <?php echo date('g:i A', strtotime($meeting['time_end'])); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Venue</p>
                        <p class="font-semibold"><i
                                class="bi bi-geo-alt text-red-600 mr-2"></i><?php echo htmlspecialchars($meeting['venue']); ?>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Visibility</p>
                        <span
                            class="inline-block px-3 py-1 <?php echo $meeting['is_public'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> rounded-full text-sm">
                            <i class="bi bi-<?php echo $meeting['is_public'] ? 'globe' : 'lock'; ?> mr-1"></i>
                            <?php echo $meeting['is_public'] ? 'Public' : 'Private'; ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($meeting['description'])): ?>
                    <div class="mt-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Description</p>
                        <p class="text-gray-900 dark:text-white">
                            <?php echo nl2br(htmlspecialchars($meeting['description'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Agenda Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Meeting Agenda</h2>
                    <button class="text-red-600 hover:text-red-700 cursor-not-allowed" disabled title="Coming Soon">
                        <i class="bi bi-plus-circle mr-1"></i>Add Item
                    </button>
                </div>
                <p class="text-gray-500 text-center py-8">No agenda items yet</p>
            </div>

            <!-- Minutes Section -->
            <?php if ($meeting['status'] === 'Completed'): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold">Meeting Minutes</h2>
                        <button class="text-red-600 hover:text-red-700 cursor-not-allowed" disabled title="Coming Soon">
                            <i class="bi bi-file-earmark-text mr-1"></i>Add Minutes
                        </button>
                    </div>
                    <p class="text-gray-500 text-center py-8">No minutes recorded yet</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Quick Info</h2>
                <div class="space-y-3">
                    <div class="flex items-center text-sm">
                        <i class="bi bi-calendar-event text-red-600 w-6"></i>
                        <span class="text-gray-600 dark:text-gray-400">Created:
                            <?php echo date('M j, Y', strtotime($meeting['created_date'])); ?></span>
                    </div>
                    <div class="flex items-center text-sm">
                        <i class="bi bi-person text-red-600 w-6"></i>
                        <span class="text-gray-600 dark:text-gray-400">By:
                            <?php echo htmlspecialchars($meeting['created_by']); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Actions</h2>
                <div class="space-y-2">
                    <button
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg cursor-not-allowed"
                        disabled title="Coming Soon">
                        <i class="bi bi-file-earmark-pdf mr-2"></i>Download Agenda
                    </button>
                    <?php if ($meeting['status'] === 'Completed'): ?>
                        <button
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg cursor-not-allowed"
                            disabled title="Coming Soon">
                            <i class="bi bi-file-earmark-text mr-2"></i>Download Minutes
                        </button>
                    <?php endif; ?>
                    <button
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg cursor-not-allowed"
                        disabled title="Coming Soon">
                        <i class="bi bi-calendar-plus mr-2"></i>Add to Calendar
                    </button>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this meeting?');"
                        class="mt-4">
                        <button type="submit" name="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            <i class="bi bi-trash mr-2"></i>Delete Meeting
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>