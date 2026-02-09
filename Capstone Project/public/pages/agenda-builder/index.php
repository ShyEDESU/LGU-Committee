<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_agenda'])) {
    $agendaId = $_POST['agenda_id'];
    // Delete all agenda items for this meeting
    $items = getAgendaByMeeting($agendaId);
    foreach ($items as $item) {
        deleteAgendaItem($item['id'] ?? $item['item_id']);
    }

    // Update meeting status
    changeMeetingAgendaStatus($agendaId, 'None');

    header('Location: index.php?deleted=1');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Agendas & Deliberation';
include '../../includes/header.php';

// Get all meetings with agendas
$meetings = getAllMeetings();
$committees = getAllCommittees();

// Build agendas array from meetings with agenda items
$agendas = [];
foreach ($meetings as $meeting) {
    $items = getAgendaByMeeting($meeting['id']);
    if (!empty($items)) {
        $totalDuration = 0;
        foreach ($items as $item) {
            $totalDuration += ($item['duration'] ?? 0);
        }
        $agendas[] = [
            'id' => $meeting['id'],
            'meeting_id' => $meeting['id'], // Explicit meeting_id for items link
            'meeting' => $meeting['title'],
            'committee' => $meeting['committee_name'],
            'committee_id' => $meeting['committee_id'],
            'date' => $meeting['date'],
            'items' => $items, // Store actual items array, not count
            'item_count' => count($items),
            'duration' => $totalDuration,
            'status' => $meeting['agenda_status'] ?? 'Draft',
            'meeting_status' => $meeting['status']
        ];
    }
}

// Filters
$search = $_GET['search'] ?? '';
$committeeFilter = $_GET['committee'] ?? '';
$statusFilter = $_GET['status'] ?? '';

if ($search || $committeeFilter || $statusFilter) {
    $agendas = array_filter($agendas, function ($agenda) use ($search, $committeeFilter, $statusFilter) {
        $matchesSearch = empty($search) ||
            stripos($agenda['meeting'], $search) !== false ||
            stripos($agenda['committee'], $search) !== false;
        $matchesCommittee = empty($committeeFilter) || $agenda['committee_id'] == $committeeFilter;
        $matchesStatus = empty($statusFilter) || $agenda['status'] === $statusFilter;

        return $matchesSearch && $matchesCommittee && $matchesStatus;
    });
}

// Calculate statistics
$totalAgendas = count($agendas);
$draftCount = count(array_filter($agendas, fn($a) => $a['status'] === 'Draft'));
$publishedCount = count(array_filter($agendas, fn($a) => $a['status'] === 'Published'));
$approvedCount = count(array_filter($agendas, fn($a) => $a['status'] === 'Approved'));

// Pagination logic
$itemsPerPage = 10;
$totalAgendasCount = count($agendas);
$totalPages = ceil($totalAgendasCount / $itemsPerPage);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;
$paginatedAgendas = array_slice($agendas, $offset, $itemsPerPage);
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Agendas & Deliberation</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Build and manage meeting agendas</p>
        </div>
        <a href="create.php"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center space-x-2">
            <i class="bi bi-plus-lg"></i>
            <span>Create Agenda</span>
        </a>
    </div>
</div>

<!-- Sub-Module Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="index.php"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold transition hover:bg-red-700">
            <i class="bi bi-list"></i> All Agendas
        </a>
        <a href="items-list.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-card-list"></i> All Items
        </a>
        <a href="templates.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-file-earmark-text"></i> Templates
        </a>
        <a href="archive.php"
            class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <i class="bi bi-archive"></i> Archive
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by meeting or committee..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white text-base">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Committee</label>
            <select name="committee"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Committees</option>
                <?php foreach ($committees as $committee): ?>
                    <option value="<?php echo $committee['id']; ?>" <?php echo $committeeFilter == $committee['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($committee['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Status</option>
                <option value="Draft" <?php echo $statusFilter === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="Under Review" <?php echo $statusFilter === 'Under Review' ? 'selected' : ''; ?>>Under
                    Review</option>
                <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Published" <?php echo $statusFilter === 'Published' ? 'selected' : ''; ?>>Published
                </option>
            </select>
        </div>
        <div class="md:col-span-4 flex justify-end space-x-2">
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Agendas</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $totalAgendas; ?></p>
            </div>
            <div class="bg-red-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-file-earmark-text text-red-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Draft</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $draftCount; ?></p>
            </div>
            <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-3">
                <i class="bi bi-pencil text-yellow-600 dark:text-yellow-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Approved</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $approvedCount; ?></p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-check-circle text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Published</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $publishedCount; ?></p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-megaphone text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Agendas Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($agendas)): ?>
        <div class="p-12 text-center">
            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Agendas Found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                <?php if ($search || $committeeFilter || $statusFilter): ?>
                    Try adjusting your search or filters
                <?php else: ?>
                    Get started by creating your first agenda
                <?php endif; ?>
            </p>
            <?php if (!$search && !$committeeFilter && !$statusFilter): ?>
                <a href="create.php"
                    class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-plus-lg mr-2"></i> Create Agenda
                </a>
            <?php else: ?>
                <a href="index.php" class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Clear Filters
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border-b-2 border-red-600">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Meeting</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Committee</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Items</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Duration</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($paginatedAgendas as $agenda): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($agenda['meeting']); ?>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    <?php echo htmlspecialchars($agenda['committee']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo !empty($agenda['date']) ? date('M j, Y', strtotime($agenda['date'])) : 'Not Set'; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo $agenda['item_count']; ?> items
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                <?php echo $agenda['duration']; ?> min
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                <?php
                                echo $agenda['status'] === 'Draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                    ($agenda['status'] === 'Published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                        'bg-red-100 text-red-800 dark:bg-blue-900/30 dark:text-blue-300');
                                ?>">
                                    <?php echo htmlspecialchars($agenda['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="view.php?id=<?php echo $agenda['meeting_id']; ?>"
                                        class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 dark:text-blue-400 hover:bg-red-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                        title="View Agenda">
                                        <i class="bi bi-eye mr-1.5"></i> View
                                    </a>
                                    <a href="items.php?meeting_id=<?php echo $agenda['meeting_id']; ?>"
                                        class="inline-flex items-center px-3 py-1.5 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition"
                                        title="Manage Items">
                                        <i class="bi bi-pencil mr-1.5"></i> Edit
                                    </a>
                                    <form method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this agenda and all its items?');">
                                        <input type="hidden" name="delete_agenda" value="1">
                                        <input type="hidden" name="agenda_id" value="<?php echo $agenda['id']; ?>">
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-semibold"
                                            title="Delete Agenda">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div
            class="mt-6 flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to
                <span class="font-medium"><?php echo min($offset + $itemsPerPage, $totalAgendasCount); ?></span> of
                <span class="font-medium"><?php echo $totalAgendasCount; ?></span> agendas
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        Previous
                    </a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>