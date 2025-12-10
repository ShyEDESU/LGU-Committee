<?php 
session_start(); 
require_once '../../../app/helpers/ModuleDataHelper.php';

// Get committees data
$committees = ModuleDataHelper::getModuleData('committee-structure');
$total_committees = ModuleDataHelper::getItemCount('committee-structure');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            ModuleDataHelper::addItem('committee-structure', [
                'name' => $_POST['name'] ?? 'New Committee',
                'type' => $_POST['type'] ?? 'Standing',
                'members' => $_POST['members'] ?? 0,
                'status' => $_POST['status'] ?? 'Active',
                'created' => date('Y-m-d')
            ]);
        } elseif ($_POST['action'] === 'update') {
            ModuleDataHelper::updateItem('committee-structure', (int)$_POST['id'], [
                'status' => $_POST['status'] ?? 'Active'
            ]);
        } elseif ($_POST['action'] === 'delete') {
            ModuleDataHelper::deleteItem('committee-structure', (int)$_POST['id']);
        }
        // Refresh data after action
        $committees = ModuleDataHelper::getModuleData('committee-structure');
    }
}

// Get stats (keep existing logic)
$stats = ModuleDataHelper::getOverallStats();
?>
<?php include '../../../public/includes/header-sidebar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Module Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-50 border-red-200 border-2 rounded-lg p-3">
                <i class="bi bi-building text-red-700 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Committee Structure</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage and view all committee structure in your system.</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-8 sticky top-0 bg-white dark:bg-gray-900 z-10">
        <nav class="flex gap-4 overflow-x-auto" role="tablist">
            <button
                role="tab"
                id="overview-tab"
                aria-selected="true"
                onclick="switchTab('overview', 'Committee Structure')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-cms-red border-b-2 border-cms-red"
            >
                Overview
            </button>
            <button
                role="tab"
                id="create-committee-tab"
                aria-selected="false"
                onclick="switchTab('create-committee', 'Committee Structure')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Create Committee
            </button>
            <button
                role="tab"
                id="committee-types-tab"
                aria-selected="false"
                onclick="switchTab('committee-types', 'Committee Structure')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Committee Types
            </button>
            <button
                role="tab"
                id="charter-tab"
                aria-selected="false"
                onclick="switchTab('charter', 'Committee Structure')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Charter
            </button>
            <button
                role="tab"
                id="contacts-tab"
                aria-selected="false"
                onclick="switchTab('contacts', 'Committee Structure')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Contacts
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div
        id="overview-content"
        role="tabpanel"
        aria-labelledby="overview-tab"
        class="animate-fadeIn"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Overview</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Total Committees: <strong><?php echo $total_committees; ?></strong></p>
                </div>
            </div>

            <!-- Committees Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <?php foreach ($committees as $committee): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['name']); ?></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($committee['type']); ?> Committee</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Members:</span>
                                <p class="font-semibold text-gray-900 dark:text-white"><?php echo $committee['members']; ?></p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <p class="font-semibold"><span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700"><?php echo htmlspecialchars($committee['status']); ?></span></p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Created: <?php echo $committee['created']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2" data-toggle="add-committee-modal">
                    <i class="bi bi-plus-circle"></i>
                    Add New Committee
                </button>
                <button onclick="location.reload()" class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <div
        id="create-committee-content"
        role="tabpanel"
        aria-labelledby="create-committee-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create Committee</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">This section is ready for content implementation.</p>
                </div>
            </div>

            <!-- Coming Soon Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 1</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 2</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 3</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Add New
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <div
        id="committee-types-content"
        role="tabpanel"
        aria-labelledby="committee-types-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Committee Types</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">This section is ready for content implementation.</p>
                </div>
            </div>

            <!-- Coming Soon Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 1</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 2</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 3</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Add New
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <div
        id="charter-content"
        role="tabpanel"
        aria-labelledby="charter-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Charter</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">This section is ready for content implementation.</p>
                </div>
            </div>

            <!-- Coming Soon Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 1</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 2</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 3</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Add New
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <div
        id="contacts-content"
        role="tabpanel"
        aria-labelledby="contacts-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Contacts</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">This section is ready for content implementation.</p>
                </div>
            </div>

            <!-- Coming Soon Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 1</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 2</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="text-red-700 text-2xl">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Item 3</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coming soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    Add New
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
                <button class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Tab Switching Script -->
<script>
function switchTab(tabId, moduleName) {
    // Hide all tabs
    document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
        panel.classList.add('hidden');
    });
    
    // Show selected tab
    const selectedPanel = document.getElementById(tabId + '-content');
    if (selectedPanel) {
        selectedPanel.classList.remove('hidden');
    }
    
    // Update tab button states
    document.querySelectorAll('[role="tab"]').forEach(tab => {
        if (tab.id === tabId + '-tab') {
            tab.classList.remove('text-gray-600', 'dark:text-gray-400', 'border-transparent');
            tab.classList.add('text-cms-red', 'border-cms-red');
            tab.setAttribute('aria-selected', 'true');
        } else {
            tab.classList.remove('text-cms-red', 'border-cms-red');
            tab.classList.add('text-gray-600', 'dark:text-gray-400', 'border-transparent');
            tab.setAttribute('aria-selected', 'false');
        }
    });
    
    // Save to localStorage
    localStorage.setItem('activeTab_' + moduleName, tabId);
}

// Restore active tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const moduleName = 'Committee Structure';
    const savedTab = localStorage.getItem('activeTab_' + moduleName);
    if (savedTab) {
        switchTab(savedTab, moduleName);
    }
});
</script>

<?php include '../../../public/includes/footer.php'; ?>
