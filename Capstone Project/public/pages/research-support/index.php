<?php session_start(); ?>
<?php include '../../../public/includes/header-sidebar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Module Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-50 border-red-200 border-2 rounded-lg p-3">
                <i class="bi bi-book text-red-700 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Research Support</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage and view all research support in your system.</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-8 sticky top-0 bg-white dark:bg-gray-900 z-10">
        <nav class="flex gap-4 overflow-x-auto" role="tablist">
            <button
                role="tab"
                id="request-support-tab"
                aria-selected="true"
                onclick="switchTab('request-support', 'Research Support')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-cms-red border-b-2 border-cms-red"
            >
                Request Support
            </button>
            <button
                role="tab"
                id="policy-briefs-tab"
                aria-selected="false"
                onclick="switchTab('policy-briefs', 'Research Support')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Policy Briefs
            </button>
            <button
                role="tab"
                id="legal-analysis-tab"
                aria-selected="false"
                onclick="switchTab('legal-analysis', 'Research Support')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Legal Analysis
            </button>
            <button
                role="tab"
                id="comparative-study-tab"
                aria-selected="false"
                onclick="switchTab('comparative-study', 'Research Support')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Comparative Study
            </button>
            <button
                role="tab"
                id="research-findings-tab"
                aria-selected="false"
                onclick="switchTab('research-findings', 'Research Support')"
                class="px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent"
            >
                Research Findings
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div
        id="request-support-content"
        role="tabpanel"
        aria-labelledby="request-support-tab"
        class="animate-fadeIn"
    >
        <div class="bg-red-50 border-red-200 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-book text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Request Support</h2>
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
        id="policy-briefs-content"
        role="tabpanel"
        aria-labelledby="policy-briefs-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 border-red-200 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-book text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Policy Briefs</h2>
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
        id="legal-analysis-content"
        role="tabpanel"
        aria-labelledby="legal-analysis-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 border-red-200 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-book text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Legal Analysis</h2>
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
        id="comparative-study-content"
        role="tabpanel"
        aria-labelledby="comparative-study-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 border-red-200 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-book text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Comparative Study</h2>
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
        id="research-findings-content"
        role="tabpanel"
        aria-labelledby="research-findings-tab"
        class="animate-fadeIn hidden"
    >
        <div class="bg-red-50 border-red-200 border rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-3">
                    <i class="bi bi-book text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Research Findings</h2>
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
    const moduleName = 'Research Support';
    const savedTab = localStorage.getItem('activeTab_' + moduleName);
    if (savedTab) {
        switchTab(savedTab, moduleName);
    }
});
</script>

<?php include '../../../public/includes/footer.php'; ?>
