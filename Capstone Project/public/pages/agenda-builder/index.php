<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Agendas & Deliberation';
include '../../includes/header.php';

// Hardcoded agendas data
$agendas = [
    ['id' => 1, 'meeting' => 'Finance Committee - Budget Review', 'date' => '2024-12-15', 'items' => 5, 'status' => 'Draft'],
    ['id' => 2, 'meeting' => 'Health Committee - Healthcare Facilities', 'date' => '2024-12-14', 'items' => 3, 'status' => 'Final'],
    ['id' => 3, 'meeting' => 'Education Committee - School Infrastructure', 'date' => '2024-12-13', 'items' => 4, 'status' => 'Approved'],
    ['id' => 4, 'meeting' => 'Infrastructure Committee - Road Maintenance', 'date' => '2024-12-12', 'items' => 6, 'status' => 'Approved'],
    ['id' => 5, 'meeting' => 'Public Safety Committee - Disaster Prep', 'date' => '2024-12-11', 'items' => 4, 'status' => 'Approved'],
    ['id' => 6, 'meeting' => 'Finance Committee - Revenue Enhancement', 'date' => '2024-12-18', 'items' => 5, 'status' => 'Draft'],
];
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agendas & Deliberation</h1>
            <p class="text-gray-600 mt-1">Build and manage meeting agendas</p>
        </div>
        <a href="create.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"><i class="bi bi-plus-lg"></i> Create Agenda</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold"><i class="bi bi-list"></i> All Agendas</a>
        <a href="items.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i class="bi bi-list-check"></i> Items</a>
        <a href="deliberation.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i class="bi bi-chat-left-text"></i> Deliberation</a>
        <a href="voting.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"><i class="bi bi-hand-thumbs-up"></i> Voting</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Total Agendas</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo count($agendas); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Draft</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            <?php echo count(array_filter($agendas, fn($a) => $a['status'] === 'Draft')); ?>
        </p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-600">Approved</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            <?php echo count(array_filter($agendas, fn($a) => $a['status'] === 'Approved')); ?>
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meeting</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($agendas as $agenda): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold text-gray-900"><?php echo $agenda['meeting']; ?></td>
                <td class="px-6 py-4 text-gray-900"><?php echo date('M j, Y', strtotime($agenda['date'])); ?></td>
                <td class="px-6 py-4 text-gray-900"><?php echo $agenda['items']; ?> items</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?php echo $agenda['status'] === 'Draft' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($agenda['status'] === 'Final' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo $agenda['status']; ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="view.php?id=<?php echo $agenda['id']; ?>" class="text-red-600 hover:text-red-700">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>

