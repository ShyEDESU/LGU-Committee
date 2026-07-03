<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';

// Public access - no authentication required
$pageTitle = 'Public Referrals';
$isPublic = true;

// Get only public referrals
$allReferrals = getAllReferrals();
$publicReferrals = array_filter($allReferrals, function ($ref) {
    return ($ref['is_public'] ?? true) === true;
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle; ?> - Committee Management System
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50">
    <!-- Public Header -->
    <header class="bg-red-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Committee Management System</h1>
                    <p class="text-red-100 text-sm mt-1">Public Referrals Portal</p>
                </div>
                <a href="../../../auth/login.php"
                    class="px-4 py-2 bg-white text-red-600 rounded-lg hover:bg-red-50 transition">
                    <i class="bi bi-box-arrow-in-right mr-2"></i>Staff Login
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Search Bar -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold mb-4"><i class="bi bi-search mr-2"></i>Search Public Referrals</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" id="searchInput" placeholder="Search by title..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                <select id="typeFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option value="">All Types</option>
                    <option value="Ordinance">Ordinance</option>
                    <option value="Resolution">Resolution</option>
                    <option value="Communication">Communication</option>
                </select>
                <select id="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Under Review">Under Review</option>
                    <option value="In Committee">In Committee</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                <p class="text-sm text-gray-600">Total Public</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo count($publicReferrals); ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-500">
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo count(array_filter($publicReferrals, fn($r) => $r['status'] === 'Pending')); ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-600">Approved</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo count(array_filter($publicReferrals, fn($r) => $r['status'] === 'Approved')); ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                <p class="text-sm text-gray-600">In Committee</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php echo count(array_filter($publicReferrals, fn($r) => $r['status'] === 'In Committee')); ?>
                </p>
            </div>
        </div>

        <!-- Referrals List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold"><i class="bi bi-list-ul mr-2"></i>Public Referrals</h2>
            </div>

            <?php if (empty($publicReferrals)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="bi bi-inbox text-5xl mb-3"></i>
                    <p>No public referrals available</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200" id="referralsList">
                    <?php foreach ($publicReferrals as $ref): ?>
                        <div class="p-6 hover:bg-gray-50 transition referral-item"
                            data-title="<?php echo strtolower($ref['title']); ?>" data-type="<?php echo $ref['type']; ?>"
                            data-status="<?php echo $ref['status']; ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($ref['title']); ?>
                                    </h3>
                                    <p class="text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($ref['description']); ?>
                                    </p>
                                    <div class="flex items-center gap-3 mt-3 text-sm">
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">
                                            <?php echo $ref['type']; ?>
                                        </span>
                                        <span
                                            class="px-2 py-1 rounded-full <?php echo $ref['status'] === 'Pending' ? 'bg-gray-100 text-gray-800' :
                                                ($ref['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'); ?>">
                                            <?php echo $ref['status']; ?>
                                        </span>
                                        <span class="text-gray-500">
                                            <i class="bi bi-building mr-1"></i>
                                            <?php echo htmlspecialchars($ref['committee_name']); ?>
                                        </span>
                                        <?php if (!empty($ref['deadline'])): ?>
                                            <span class="text-gray-500">
                                                <i class="bi bi-calendar-x mr-1"></i>
                                                <?php echo date('M j, Y', strtotime($ref['deadline'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Box -->
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mt-6">
            <h3 class="font-semibold text-red-900 mb-2"><i class="bi bi-info-circle mr-2"></i>About Public Referrals
            </h3>
            <p class="text-sm text-red-800">This portal provides public access to committee referrals. Only referrals
                marked as public are displayed here. For full access and management features, please log in as staff.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy;
                <?php echo date('Y'); ?> Committee Management System. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const referralItems = document.querySelectorAll('.referral-item');

        function filterReferrals() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedType = typeFilter.value;
            const selectedStatus = statusFilter.value;

            referralItems.forEach(item => {
                const title = item.dataset.title;
                const type = item.dataset.type;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesType = !selectedType || type === selectedType;
                const matchesStatus = !selectedStatus || status === selectedStatus;

                if (matchesSearch && matchesType && matchesStatus) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterReferrals);
        typeFilter.addEventListener('change', filterReferrals);
        statusFilter.addEventListener('change', filterReferrals);
    </script>
</body>

</html>
