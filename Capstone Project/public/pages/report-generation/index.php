<?php
session_start();
require_once '../../../app/helpers/ModuleDataHelper.php';
require_once '../../../app/helpers/ModuleDisplayHelper.php';

// Module data
$module_key = 'report-generation';
$data = ModuleDataHelper::getModuleData($module_key);
$total_items = ModuleDataHelper::getItemCount($module_key);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        ModuleDataHelper::addItem($module_key, [
            'title' => $_POST['title'] ?? 'New Report',
            'type' => $_POST['type'] ?? 'Automated',
            'status' => $_POST['status'] ?? 'Ready'
        ]);
    } elseif ($_POST['action'] === 'delete') {
        ModuleDataHelper::deleteItem($module_key, (int)$_POST['id']);
    }
    $data = ModuleDataHelper::getModuleData($module_key);
}
?>
<?php include '../../../public/includes/header-sidebar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Module Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-50 border-red-200 border-2 rounded-lg p-3">
                <i class="bi bi-file-pdf text-red-700 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Report Generation</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage and view all report generation in your system.</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-8 sticky top-0 bg-white dark:bg-gray-900 z-10">
        <nav class="flex gap-4 overflow-x-auto" role="tablist">
            <button
                role="tab"
                id="generate-report-tab"
                aria-selected="true"
                onclick="switchTab('generate-report', 'Report Generation')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-cms-red border-b-2 border-cms-red"
            >
                Generate Report
            </button>
            <button
                role="tab"
                id="templates-tab"
                aria-selected="false"
                onclick="switchTab('templates', 'Report Generation')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Templates
            </button>
            <button
                role="tab"
                id="recommendations-tab"
                aria-selected="false"
                onclick="switchTab('recommendations', 'Report Generation')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Recommendations
            </button>
            <button
                role="tab"
                id="approval-tab"
                aria-selected="false"
                onclick="switchTab('approval', 'Report Generation')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Approval
            </button>
            <button
                role="tab"
                id="minority-opinion-tab"
                aria-selected="false"
                onclick="switchTab('minority-opinion', 'Report Generation')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Minority Opinion
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div
        id="generate-report-content"
        role="tabpanel"
        aria-labelledby="generate-report-tab"
        class="animate-fadeIn"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-file-pdf text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reports</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Total Items: <strong><?php echo $total_items; ?></strong></p>
                </div>
            </div>

            <!-- Data Grid -->
            <?php ModuleDisplayHelper::displayItemsGrid(
                $data,
                'bi-file-pdf',
                [
                    'title' => 'Title',
                    'type' => 'Type',
                    'generated' => 'Generated',
                    'pages' => 'Pages',
                    'status' => 'Status'
                ]
            ); ?>

            <!-- Add New Item Form -->
            <div class="mt-8">
                <?php ModuleDisplayHelper::displayAddForm([
                    'title' => 'text',
                    'type' => 'select',
                    'generated' => 'date',
                    'pages' => 'number',
                    'status' => 'select'
                ]); ?>
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
        id="templates-content"
        role="tabpanel"
        aria-labelledby="templates-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-file-pdf text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Templates</h2>
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
        id="recommendations-content"
        role="tabpanel"
        aria-labelledby="recommendations-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-file-pdf text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recommendations</h2>
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
        id="approval-content"
        role="tabpanel"
        aria-labelledby="approval-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-file-pdf text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Approval</h2>
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
        id="minority-opinion-content"
        role="tabpanel"
        aria-labelledby="minority-opinion-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-file-pdf text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Minority Opinion</h2>
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
    const moduleName = 'Report Generation';
    const savedTab = localStorage.getItem('activeTab_' + moduleName);
    if (savedTab) {
        switchTab(savedTab, moduleName);
    }
});
</script>

<?php include '../../../public/includes/footer.php'; ?>
