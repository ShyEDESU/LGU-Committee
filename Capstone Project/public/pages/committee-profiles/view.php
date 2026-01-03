<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$committeeId = $_GET['id'] ?? 1;

// Hardcoded committee data with members
$committees = [
    1 => [
        'id' => 1,
        'name' => 'Committee on Finance',
        'type' => 'Standing',
        'chair' => 'Hon. Maria Santos',
        'vice_chair' => 'Hon. Roberto Cruz',
        'jurisdiction' => 'Budget, appropriations, revenue measures, and financial matters',
        'status' => 'Active',
        'created_date' => '2023-01-15',
        'members' => [
            ['name' => 'Hon. Maria Santos', 'role' => 'Chairperson', 'district' => 'District 1'],
            ['name' => 'Hon. Roberto Cruz', 'role' => 'Vice-Chair', 'district' => 'District 2'],
            ['name' => 'Hon. Linda Reyes', 'role' => 'Member', 'district' => 'District 3'],
            ['name' => 'Hon. Carlos Mendoza', 'role' => 'Member', 'district' => 'District 4'],
            ['name' => 'Hon. Elena Gomez', 'role' => 'Member', 'district' => 'District 5'],
        ],
        'recent_meetings' => [
            ['date' => '2024-12-10', 'topic' => '2025 Budget Review', 'status' => 'Completed'],
            ['date' => '2024-11-25', 'topic' => 'Revenue Enhancement Measures', 'status' => 'Completed'],
            ['date' => '2024-11-10', 'topic' => 'Quarterly Financial Report', 'status' => 'Completed'],
        ],
        'pending_referrals' => [
            ['title' => 'Ordinance No. 2024-001', 'type' => 'Ordinance', 'received' => '2024-12-01'],
            ['title' => 'Resolution No. 2024-045', 'type' => 'Resolution', 'received' => '2024-11-28'],
        ]
    ],
    // Add other committees similarly...
];

$committee = $committees[$committeeId] ?? $committees[1];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($committee['name']); ?> | CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/animations.css">
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-20">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-gray-600 dark:text-gray-400 hover:text-red-600 transition">
                    <i class="bi bi-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($committee['name']); ?></h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $committee['type']; ?> Committee</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="edit.php?id=<?php echo $committee['id']; ?>"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overview Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Committee Overview</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Chairperson</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($committee['chair']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Vice-Chairperson</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($committee['vice_chair']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                            <span
                                class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <?php echo $committee['type']; ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <span
                                class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <?php echo $committee['status']; ?>
                            </span>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Jurisdiction</p>
                            <p class="text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($committee['jurisdiction']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Committee Members</h2>
                    <div class="space-y-3">
                        <?php foreach ($committee['members'] as $member): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($member['name'], 5, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($member['name']); ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo $member['district']; ?></p>
                                    </div>
                                </div>
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <?php echo $member['role']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent Meetings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Meetings</h2>
                    <div class="space-y-3">
                        <?php foreach ($committee['recent_meetings'] as $meeting): ?>
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($meeting['topic']); ?></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <i class="bi bi-calendar"></i>
                                        <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo $meeting['status']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Members</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo count($committee['members']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Meetings Held</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo count($committee['recent_meetings']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Referrals</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo count($committee['pending_referrals']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Referrals -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Pending Referrals</h3>
                    <div class="space-y-3">
                        <?php foreach ($committee['pending_referrals'] as $referral): ?>
                            <div
                                class="p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                    <?php echo htmlspecialchars($referral['title']); ?></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    <?php echo $referral['type']; ?> • Received
                                    <?php echo date('M j', strtotime($referral['received'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</body>

</html>