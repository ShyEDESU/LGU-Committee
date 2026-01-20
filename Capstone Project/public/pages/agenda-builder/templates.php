<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/DataHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Handle template creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_template'])) {
    $items = json_decode($_POST['items_json'], true) ?? [];
    $templateData = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'committee_type' => $_POST['committee_type'] ?? 'General',
        'items' => $items
    ];
    
    $newId = createAgendaTemplate($templateData);
    if ($newId) {
        $_SESSION['success_message'] = 'Agenda template created successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to create template';
    }
    header('Location: templates.php');
    exit();
}

// Handle template deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template'])) {
    $templateId = $_POST['template_id'];
    if (deleteAgendaTemplate($templateId)) {
        $_SESSION['success_message'] = 'Template deleted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to delete template';
    }
    header('Location: templates.php');
    exit();
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Agenda Templates';
include '../../includes/header.php';

// Get all templates from database
$templates = getAllAgendaTemplates();
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Agenda Templates</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Create and manage reusable agenda templates</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="openCreateModal()"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-plus-lg mr-2"></i> Create Template
            </button>
            <a href="index.php"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-700 dark:text-green-300 text-xl mr-3"></i>
            <p class="text-green-700 dark:text-green-300 font-medium"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
        <div class="flex items-center">
            <i class="bi bi-exclamation-circle text-red-700 dark:text-red-300 text-xl mr-3"></i>
            <p class="text-red-700 dark:text-red-300 font-medium"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Templates Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($templates as $template):
        $itemCount = count($template['items']);
        $totalDuration = array_sum(array_column($template['items'], 'duration'));
        ?>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                            <?php echo htmlspecialchars($template['name']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            <?php echo htmlspecialchars($template['description']); ?>
                        </p>
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            <?php echo $template['committee_type'] ?? 'General'; ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-list-check w-5"></i>
                        <span class="ml-2">
                            <?php echo $itemCount; ?> Items
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <i class="bi bi-clock w-5"></i>
                        <span class="ml-2">
                            <?php echo $totalDuration; ?> Minutes Total
                        </span>
                    </div>
                </div>

                <!-- Template Items Preview -->
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Items:</h4>
                    <div class="space-y-1 max-h-40 overflow-y-auto">
                        <?php foreach ($template['items'] as $index => $item): ?>
                            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-between">
                                <span>
                                    <?php echo ($index + 1); ?>.
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </span>
                                <span class="text-xs">
                                    <?php echo $item['duration']; ?>m
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button onclick='viewTemplate(<?php echo json_encode($template); ?>)'
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition text-sm font-semibold">
                        <i class="bi bi-eye mr-1"></i> View
                    </button>
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this template?');">
                        <input type="hidden" name="delete_template" value="1">
                        <input type="hidden" name="template_id" value="<?php echo $template['template_id']; ?>">
                        <button type="submit"
                            class="px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg transition">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($templates)): ?>
        <div
            class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <i class="bi bi-inbox text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Templates Yet</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first agenda template to reuse across meetings</p>
            <button onclick="openCreateModal()"
                class="inline-block px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                <i class="bi bi-plus-lg mr-2"></i> Create Template
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Create Template Modal -->
<div id="createModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div
            class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create Template</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="createTemplateForm" class="p-6">
            <input type="hidden" name="create_template" value="1">
            <input type="hidden" name="items_json" id="itemsJson">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Template Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Standard Committee Meeting">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Brief description of this template"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Committee Type
                    </label>
                    <select name="committee_type"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:text-white">
                        <option value="All">All Committees</option>
                        <option value="Standing">Standing</option>
                        <option value="Special">Special</option>
                        <option value="Ad Hoc">Ad Hoc</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Template Items</h3>
                    <button type="button" onclick="addTemplateItem()"
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                        <i class="bi bi-plus-lg mr-1"></i> Add Item
                    </button>
                </div>

                <div id="templateItemsContainer" class="space-y-3">
                    <!-- Items will be added here -->
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCreateModal()"
                    class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="bi bi-check-lg mr-2"></i> Create Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Template Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div
            class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white" id="viewModalTitle"></h2>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="viewModalContent"></div>
    </div>
</div>

<script>
    let templateItemCounter = 0;

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        addTemplateItem(); // Add first item by default
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createTemplateForm').reset();
        document.getElementById('templateItemsContainer').innerHTML = '';
        templateItemCounter = 0;
    }

    function addTemplateItem() {
        templateItemCounter++;
        const container = document.getElementById('templateItemsContainer');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'grid grid-cols-12 gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg';
        itemDiv.id = `template-item-${templateItemCounter}`;

        itemDiv.innerHTML = `
        <div class="col-span-5">
            <input type="text" class="item-title w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm" placeholder="Item title" required>
        </div>
        <div class="col-span-4">
            <input type="text" class="item-description w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm" placeholder="Description">
        </div>
        <div class="col-span-2">
            <input type="number" class="item-duration w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm" placeholder="Min" value="15" min="1">
        </div>
        <div class="col-span-1 flex items-center">
            <button type="button" onclick="removeTemplateItem(${templateItemCounter})" class="w-full px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;

        container.appendChild(itemDiv);
    }

    function removeTemplateItem(id) {
        const item = document.getElementById(`template-item-${id}`);
        if (item) item.remove();
    }

    // Before form submission, collect all items into JSON
    document.getElementById('createTemplateForm').addEventListener('submit', function (e) {
        const items = [];
        const itemDivs = document.querySelectorAll('#templateItemsContainer > div');

        itemDivs.forEach(div => {
            const title = div.querySelector('.item-title').value;
            const description = div.querySelector('.item-description').value;
            const duration = parseInt(div.querySelector('.item-duration').value) || 15;

            if (title) {
                items.push({
                    title: title,
                    description: description,
                    duration: duration,
                    type: 'Discussion'
                });
            }
        });

        document.getElementById('itemsJson').value = JSON.stringify(items);
    });

    function viewTemplate(template) {
        document.getElementById('viewModalTitle').textContent = template.name;

        let content = `
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400">${template.description}</p>
            <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                ${template.committee_type}
            </span>
        </div>
        <div class="mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Template Items (${template.items.length}):</h3>
            <div class="space-y-2">
    `;

        template.items.forEach((item, index) => {
            content += `
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex-1">
                    <span class="font-medium text-gray-900 dark:text-white">${index + 1}. ${item.title}</span>
                    ${item.description ? `<p class="text-sm text-gray-600 dark:text-gray-400">${item.description}</p>` : ''}
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">${item.duration} min</span>
            </div>
        `;
        });

        content += `
            </div>
        </div>
    `;

        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

<?php include '../../includes/footer.php'; ?>