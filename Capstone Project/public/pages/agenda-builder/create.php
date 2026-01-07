<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Create Agenda';
include '../../includes/header.php';

// Get all committees and meetings
$committees = getAllCommittees();
$allMeetings = getAllMeetings();
$templates = getAllAgendaTemplates();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meetingId = $_POST['meeting_id'];
    $templateId = $_POST['template_id'] ?? null;

    // If template is selected, apply it
    if ($templateId) {
        applyTemplate($templateId, $meetingId);
    }

    // Add custom items if provided
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['title'])) {
                addAgendaItem($meetingId, [
                    'title' => $item['title'],
                    'description' => $item['description'] ?? '',
                    'duration' => $item['duration'] ?? 0,
                    'type' => $item['type'] ?? 'Discussion',
                    'presenter' => $item['presenter'] ?? ''
                ]);
            }
        }
    }

    // Update meeting agenda status
    $meetings = &$_SESSION['meetings'];
    foreach ($meetings as &$meeting) {
        if ($meeting['id'] == $meetingId) {
            $meeting['agenda_status'] = 'Draft';
            break;
        }
    }

    header('Location: view.php?id=' . $meetingId . '&created=1');
    exit();
}

// Get selected committee for filtering meetings
$selectedCommittee = $_GET['committee'] ?? '';
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Agenda</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Create a new agenda for a scheduled meeting</p>
        </div>
        <a href="index.php"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<form method="POST" id="createAgendaForm" class="space-y-6">
    <!-- Meeting Selection -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-calendar-event mr-2"></i> Select Meeting
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Committee <span class="text-red-600">*</span>
                </label>
                <select id="committeeSelect"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    onchange="filterMeetings()">
                    <option value="">Select Committee</option>
                    <?php foreach ($committees as $committee): ?>
                        <option value="<?php echo $committee['id']; ?>" <?php echo $selectedCommittee == $committee['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($committee['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Meeting <span class="text-red-600">*</span>
                </label>
                <select name="meeting_id" id="meetingSelect" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    onchange="displayMeetingDetails()">
                    <option value="">Select Meeting</option>
                    <?php foreach ($allMeetings as $meeting):
                        // Check if meeting already has agenda
                        $hasAgenda = !empty(getAgendaByMeeting($meeting['id']));
                        if ($hasAgenda)
                            continue; // Skip meetings that already have agendas
                        ?>
                        <option value="<?php echo $meeting['id']; ?>"
                            data-committee="<?php echo $meeting['committee_id']; ?>"
                            data-title="<?php echo htmlspecialchars($meeting['title']); ?>"
                            data-date="<?php echo $meeting['date']; ?>" data-time="<?php echo $meeting['time_start']; ?>"
                            data-venue="<?php echo htmlspecialchars($meeting['venue']); ?>">
                            <?php echo htmlspecialchars($meeting['title']); ?> -
                            <?php echo date('M j, Y', strtotime($meeting['date'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Meeting Details Display -->
        <div id="meetingDetails" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Meeting Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Date:</span>
                    <span id="detailDate" class="ml-2 text-gray-900 dark:text-white font-medium"></span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Time:</span>
                    <span id="detailTime" class="ml-2 text-gray-900 dark:text-white font-medium"></span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Venue:</span>
                    <span id="detailVenue" class="ml-2 text-gray-900 dark:text-white font-medium"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Selection (Optional) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="bi bi-file-earmark-text mr-2"></i> Use Template (Optional)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Template
                </label>
                <select name="template_id" id="templateSelect"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                    onchange="previewTemplate()">
                    <option value="">No Template (Add items manually)</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo $template['id']; ?>" data-items='<?php echo json_encode($template['items']); ?>'>
                            <?php echo htmlspecialchars($template['name']); ?> (
                            <?php echo count($template['items']); ?> items)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Template Preview -->
        <div id="templatePreview" class="mt-4 hidden">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Template Items Preview:</h4>
            <div id="templateItems" class="space-y-2"></div>
        </div>
    </div>

    <!-- Manual Items (if no template) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
        id="manualItemsSection">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="bi bi-list-check mr-2"></i> Add Agenda Items
            </h2>
            <button type="button" onclick="addItemRow()"
                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                <i class="bi bi-plus-lg mr-1"></i> Add Item
            </button>
        </div>

        <div id="itemsContainer" class="space-y-4">
            <!-- Items will be added here dynamically -->
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
            <i class="bi bi-info-circle mr-1"></i> You can add more items after creating the agenda
        </p>
    </div>

    <!-- Submit Buttons -->
    <div class="flex justify-end space-x-3">
        <a href="index.php"
            class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="bi bi-check-lg mr-2"></i> Create Agenda
        </button>
    </div>
</form>

<script>
    const allMeetings = <?php echo json_encode($allMeetings); ?>;
    let itemCounter = 0;

    function filterMeetings() {
        const committeeId = document.getElementById('committeeSelect').value;
        const meetingSelect = document.getElementById('meetingSelect');
        const options = meetingSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }

            const optionCommittee = option.getAttribute('data-committee');
            if (!committeeId || optionCommittee === committeeId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        meetingSelect.value = '';
        document.getElementById('meetingDetails').classList.add('hidden');
    }

    function displayMeetingDetails() {
        const meetingSelect = document.getElementById('meetingSelect');
        const selectedOption = meetingSelect.options[meetingSelect.selectedIndex];

        if (!selectedOption.value) {
            document.getElementById('meetingDetails').classList.add('hidden');
            return;
        }

        document.getElementById('detailDate').textContent = new Date(selectedOption.dataset.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('detailTime').textContent = selectedOption.dataset.time;
        document.getElementById('detailVenue').textContent = selectedOption.dataset.venue;
        document.getElementById('meetingDetails').classList.remove('hidden');
    }

    function previewTemplate() {
        const templateSelect = document.getElementById('templateSelect');
        const selectedOption = templateSelect.options[templateSelect.selectedIndex];
        const preview = document.getElementById('templatePreview');
        const itemsDiv = document.getElementById('templateItems');

        if (!selectedOption.value) {
            preview.classList.add('hidden');
            document.getElementById('manualItemsSection').classList.remove('hidden');
            return;
        }

        const items = JSON.parse(selectedOption.dataset.items || '[]');
        itemsDiv.innerHTML = '';

        items.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex items-center justify-between p-3 bg-gray-100 dark:bg-gray-700 rounded-lg';
            itemDiv.innerHTML = `
            <div class="flex-1">
                <span class="font-medium text-gray-900 dark:text-white">${index + 1}. ${item.title}</span>
                ${item.description ? `<p class="text-sm text-gray-600 dark:text-gray-400">${item.description}</p>` : ''}
            </div>
            <span class="text-sm text-gray-600 dark:text-gray-400">${item.duration} min</span>
        `;
            itemsDiv.appendChild(itemDiv);
        });

        preview.classList.remove('hidden');
        document.getElementById('manualItemsSection').classList.add('hidden');
    }

    function addItemRow() {
        itemCounter++;
        const container = document.getElementById('itemsContainer');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'grid grid-cols-1 md:grid-cols-12 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg';
        itemDiv.id = `item-${itemCounter}`;

        itemDiv.innerHTML = `
        <div class="md:col-span-5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
            <input type="text" name="items[${itemCounter}][title]" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                placeholder="e.g., Call to Order">
        </div>
        <div class="md:col-span-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <input type="text" name="items[${itemCounter}][description]" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                placeholder="Optional details">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (min)</label>
            <input type="number" name="items[${itemCounter}][duration]" value="15"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm">
        </div>
        <div class="md:col-span-1 flex items-end">
            <button type="button" onclick="removeItem(${itemCounter})" 
                class="w-full px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;

        container.appendChild(itemDiv);
    }

    function removeItem(id) {
        const item = document.getElementById(`item-${id}`);
        if (item) {
            item.remove();
        }
    }

    // Add one item by default
    addItemRow();
</script>

<?php include '../../includes/footer.php'; ?>