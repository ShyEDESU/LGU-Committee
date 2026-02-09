<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);
if (!$committee) {
    header('Location: index.php');
    exit();
}

$history = getCommitteeHistory($id);
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee History';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>"
                    class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">History</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($committee['name']); ?></h1>
            <p class="text-gray-600">Change History</p>
        </div>
        <a href="view.php?id=<?php echo $id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <?php if (empty($history)): ?>
            <div class="text-center py-12">
                <i class="bi bi-clock-history text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No history records found</p>
            </div>
        <?php else: ?>
            <div class="relative">
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                <div class="space-y-6">
                    <?php foreach ($history as $item): ?>
                        <div class="relative flex items-start">
                            <div
                                class="absolute left-6 w-4 h-4 bg-red-600 rounded-full border-4 border-white dark:border-gray-800">
                            </div>
                            <div class="ml-16 bg-gray-50 dark:bg-gray-700 rounded-lg p-4 flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($item['action']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="text-xs text-gray-500"><?php echo date('M d, Y g:i A', strtotime($item['date'])); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="bi bi-person-circle mr-2"></i>
                                    <span><?php echo htmlspecialchars($item['user']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</div> <!-- Closing container-fluid and module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>