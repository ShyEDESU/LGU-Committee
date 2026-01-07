<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Get meeting ID from URL and validate BEFORE including header
$meetingId = $_GET['meeting_id'] ?? 0;
$meeting = getMeetingById($meetingId);

if (!$meeting) {
    header('Location: index.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Manage Agenda Items';
include '../../includes/header.php';


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        // Add new item
        addAgendaItem($meetingId, [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'duration' => $_POST['duration'] ?? 15,
            'type' => $_POST['type'] ?? 'Discussion',
            'presenter' => $_POST['presenter'] ?? '',
            'referral_id' => $_POST['referral_id'] ?? null
        ]);
        header('Location: items.php?meeting_id=' . $meetingId . '&added=1');
        exit();
    } elseif (isset($_POST['update_item'])) {
        // Update existing item
        $itemId = $_POST['item_id'];
        updateAgenda($itemId, [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'duration' => $_POST['duration'] ?? 15,
            'presenter' => $_POST['presenter'] ?? ''
        ]);
        header('Location: items.php?meeting_id=' . $meetingId . '&updated=1');
        exit();
    } elseif (isset($_POST['delete_item'])) {
        // Delete item
        $itemId = $_POST['item_id'];
        deleteAgenda($itemId);
        header('Location: items.php?meeting_id=' . $meetingId . '&deleted=1');
        exit();
    } elseif (isset($_POST['reorder_items'])) {
        // Handle reordering via AJAX
        $order = json_decode($_POST['order'], true);
        if ($order) {
            foreach ($order as $index => $itemId) {
                updateAgenda($itemId, ['item_number' => $index + 1]);
            }
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

// Get agenda items
$agendaItems = getAgendaByMeeting($meetingId);

// Sort by item number
usort($agendaItems, function ($a, $b) {
    return ($a['item_number'] ?? 0) - ($b['item_number'] ?? 0);
});

// Calculate total duration
$totalDuration = array_sum(array_column($agendaItems, 'duration'));

// Get editing item if specified
$editingItemId = $_GET['edit'] ?? 0;
$editingItem = null;
if ($editingItemId) {
    foreach ($agendaItems as $item) {
        if ($item['id'] == $editingItemId) {
            $editingItem = $item;
            break;
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Agenda Items</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($meeting['title']); ?></p>
        </div>
        <div class="flex space-x-2">
            <a href="view.php?id=<?php echo $meetingId; ?>"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="bi bi-eye mr-2"></i> View Agenda
            </a>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Success Messages -->
<?php if (isset($_GET['added'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-700 dark:text-green-300 text-xl mr-3"></i>
            <p class="text-green-700 dark:text-green-300 font-medium">Agenda item added successfully!</p>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-blue-700 dark:text-blue-300 text-xl mr-3"></i>
            <p class="text-blue-700 dark:text-blue-300 font-medium">Agenda item updated successfully!</p>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-red-700 dark:text-red-300 text-xl mr-3"></i>
            <p class="text-red-700 dark:text-red-300 font-medium">Agenda item deleted successfully!</p>
        </div>
    </div>
<?php endif; ?>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($agendaItems); ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <i class="bi bi-list-check text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Duration</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $totalDuration; ?> <span
                        class="text-lg">min</span></p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <i class="bi bi-clock text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Meeting Date</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                </p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <i class="bi bi-calendar-event text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Agenda Items List -->
    <div class="lg:col-span-2">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-list-ol mr-2"></i> Agenda Items
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="bi bi-info-circle mr-1"></i> Drag to reorder
                    </p>
                </div>
            </div>

            <?php if (empty($agendaItems)): ?>
                <div class="p-12 text-center">
                    <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Items Yet</h3>
                    <p class="text-gray-600 dark:text-gray-400">Add your first agenda item using the form</p>
                </div>
            <?php else: ?>
                <?php if (!empty($agendaItems)): ?>
                <div id="itemsList" class="space-y-3 p-4">
                    <?php foreach ($agendaItems as $index => $item): 
                        $itemNumber = $index + 1; // Restart numbering for each agenda
                    ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow cursor-move"
                            data-item-id="<?php echo $item['id']; ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4 flex-1">
                                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                        <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                            <?php echo $itemNumber; ?>
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h4>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        <i class="bi bi-clock mr-1"></i>
                                        <?php echo ($item['duration'] ?? 0); ?> min
                                    </div>
                                    <?php if (!empty($item['presenter'])): ?>
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <i class="bi bi-person-badge mr-1"></i>
                                            <?php echo htmlspecialchars($item['presenter']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-shrink-0 flex space-x-2">
                                    <a href="?meeting_id=<?php echo $meetingId; ?>&edit=<?php echo $item['id']; ?>"
                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                        title="Edit Item">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this item?');">
                                        <input type="hidden" name="delete_item" value="1">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit"
                                            class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                            title="Delete Item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="lg:col-span-1">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-<?php echo $editingItem ? 'pencil' : 'plus-lg'; ?> mr-2"></i>
                <?php echo $editingItem ? 'Edit Item' : 'Add New Item'; ?>
            </h2>

            <form method="POST" class="space-y-4">
                <?php if ($editingItem): ?>
                    <input type="hidden" name="update_item" value="1">
                    <input type="hidden" name="item_id" value="<?php echo $editingItem['id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="add_item" value="1">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="title" required
                        value="<?php echo $editingItem ? htmlspecialchars($editingItem['title']) : ''; ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Call to Order">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Optional details..."><?php echo $editingItem ? htmlspecialchars($editingItem['description']) : ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Duration (minutes) <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="duration" required min="1"
                        value="<?php echo $editingItem ? ($editingItem['duration'] ?? 15) : 15; ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Presenter/Speaker
                    </label>
                    <input type="text" name="presenter"
                        value="<?php echo $editingItem ? htmlspecialchars($editingItem['presenter'] ?? '') : ''; ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Optional">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Related Referral (Optional)
                    </label>
                    <select name="referral_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="">None</option>
                        <?php
                        $allReferrals = getAllReferrals();
                        foreach ($allReferrals as $ref):
                            if ($ref['status'] !== 'Approved' && $ref['status'] !== 'Rejected'):
                                ?>
                                <option value="<?php echo $ref['id']; ?>" <?php echo ($editingItem && isset($editingItem['referral_id']) && $editingItem['referral_id'] == $ref['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ref['title']); ?> (<?php echo $ref['status']; ?>)
                                </option>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Link this agenda item to a specific
                        referral for discussion</p>
                </div>

                <div class="md:col-span-2">
                    <?php if ($editingItem): ?>
                        <a href="?meeting_id=<?php echo $meetingId; ?>"
                            class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-center rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Update
                        </button>
                    <?php else: ?>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                            <i class="bi bi-plus-lg mr-2"></i> Add Item
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Initialize drag and drop
    const itemsList = document.getElementById('itemsList');
    if (itemsList) {
        new Sortable(itemsList, {
            animation: 150,
            handle: '.cursor-move',
            onEnd: function (evt) {
                // Get new order
                const items = itemsList.querySelectorAll('[data-item-id]');
                const order = Array.from(items).map(item => item.dataset.itemId);

                // Send to server
                fetch('items.php?meeting_id=<?php echo $meetingId; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'reorder_items=1&order=' + encodeURIComponent(JSON.stringify(order))
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message briefly
                            const successDiv = document.createElement('div');
                            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                            successDiv.innerHTML = '<i class="bi bi-check-circle mr-2"></i> Order updated!';
                            document.body.appendChild(successDiv);
                            setTimeout(() => successDiv.remove(), 2000);
                        }
                    });
            }
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>