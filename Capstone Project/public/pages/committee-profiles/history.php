<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee History';
include '../../includes/header.php';

// Hardcoded history/timeline data
$history = [
    ['date' => '2024-12-10', 'type' => 'meeting', 'title' => '2025 Budget Review Meeting', 'committee' => 'Finance', 'description' => 'Reviewed and approved the 2025 annual budget proposal', 'user' => 'Hon. Maria Santos'],
    ['date' => '2024-11-25', 'type' => 'document', 'title' => 'Meeting Minutes Uploaded', 'committee' => 'Finance', 'description' => 'November 2024 meeting minutes uploaded by Secretary', 'user' => 'Secretary'],
    ['date' => '2024-11-20', 'type' => 'member', 'title' => 'New Member Added', 'committee' => 'Finance', 'description' => 'Hon. Carlos Mendoza joined as committee member', 'user' => 'Admin'],
    ['date' => '2024-10-15', 'type' => 'meeting', 'title' => 'Quarterly Financial Report', 'committee' => 'Finance', 'description' => 'Presented Q3 2024 financial performance', 'user' => 'Hon. Maria Santos'],
    ['date' => '2024-09-05', 'type' => 'referral', 'title' => 'Ordinance No. 2024-001 Referred', 'committee' => 'Finance', 'description' => 'Annual budget ordinance referred for review', 'user' => 'Council Secretary'],
    ['date' => '2024-08-20', 'type' => 'document', 'title' => 'Charter Document Updated', 'committee' => 'Finance', 'description' => 'Committee charter revised and approved', 'user' => 'Admin'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee History</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Timeline of committee activities and events</p>
        </div>
        <button onclick="openModal('filterModal')"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-funnel"></i>
            <span>Filter</span>
        </button>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-list"></i> All Committees
        </a>
        <a href="members.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="documents.php"
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Documents
        </a>
        <a href="history.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
            <i class="bi bi-clock-history"></i> History
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-100 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Events</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($history); ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-event text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Meetings</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'meeting')); ?>
                </p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-check text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-300 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Documents</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'document')); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-file-earmark text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 animate-fade-in-up animation-delay-400 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Referrals</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count(array_filter($history, fn($h) => $h['type'] === 'referral')); ?>
                </p>
            </div>
            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3">
                <i class="bi bi-inbox text-orange-600 dark:text-orange-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Timeline -->
<div
    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 animate-fade-in-up animation-delay-500">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 flex items-center">
        <i class="bi bi-clock-history mr-3 text-red-600"></i>
        Activity Timeline
    </h2>
    <div class="space-y-8">
        <?php
        $delay = 600;
        foreach ($history as $index => $event):
            ?>
            <div class="flex animate-fade-in-up animation-delay-<?php echo $delay; ?>">
                <div class="flex flex-col items-center mr-6">
                    <div
                        class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg
                    <?php echo $event['type'] === 'meeting' ? 'bg-blue-500 dark:bg-blue-600' :
                        ($event['type'] === 'document' ? 'bg-green-500 dark:bg-green-600' :
                            ($event['type'] === 'member' ? 'bg-purple-500 dark:bg-purple-600' : 'bg-orange-500 dark:bg-orange-600')); ?> text-white">
                        <i class="bi bi-<?php echo $event['type'] === 'meeting' ? 'calendar-check' :
                            ($event['type'] === 'document' ? 'file-earmark' :
                                ($event['type'] === 'member' ? 'person-plus' : 'inbox')); ?> text-2xl"></i>
                    </div>
                    <?php if ($index < count($history) - 1): ?>
                        <div class="w-1 flex-1 bg-gray-200 dark:bg-gray-700 mt-2 rounded-full"></div>
                    <?php endif; ?>
                </div>
                <div class="flex-1 pb-8">
                    <div
                        class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-600">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </h3>
                                <div class="flex items-center space-x-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center">
                                        <i class="bi bi-calendar3 mr-1"></i>
                                        <?php echo date('F j, Y', strtotime($event['date'])); ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="bi bi-person mr-1"></i>
                                        <?php echo $event['user']; ?>
                                    </span>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php echo $event['type'] === 'meeting' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                ($event['type'] === 'document' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    ($event['type'] === 'member' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' :
                                        'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300')); ?>">
                                <?php echo ucfirst($event['type']); ?>
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-3">
                            <?php echo htmlspecialchars($event['description']); ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span
                                class="px-3 py-1 text-sm rounded-full bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                <i class="bi bi-building mr-1"></i>
                                <?php echo $event['committee']; ?>
                            </span>
                            <button onclick="viewDetails(<?php echo $index; ?>)"
                                class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-semibold">
                                View Details <i class="bi bi-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $delay += 100;
            if ($delay > 1200)
                $delay = 600;
        endforeach;
        ?>
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full animate-fade-in-up">
        <div
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Filter Timeline</h2>
            <button onclick="closeModal('filterModal')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type</label>
                <select
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option>All Types</option>
                    <option>Meetings</option>
                    <option>Documents</option>
                    <option>Members</option>
                    <option>Referrals</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Committee</label>
                <select
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                    <option>All Committees</option>
                    <option>Finance</option>
                    <option>Health</option>
                    <option>Education</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                <input type="date"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex space-x-3 pt-4">
                <button onclick="closeModal('filterModal')"
                    class="flex-1 px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">Cancel</button>
                <button class="flex-1 btn-primary"><i class="bi bi-funnel mr-2"></i> Apply</button>
            </div>
        </div>
    </div>
</div>

<script>
    function viewDetails(index) {
        alert('View details for event ' + index + '\n(Will be implemented with database)');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('filterModal');
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>