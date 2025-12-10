<?php
/**
 * Module Template with Functions and Dummy Data
 * 
 * This file demonstrates how to integrate ModuleDataHelper into any module
 * Use this as a template for all 16 modules
 */

session_start();
require_once '../../../app/helpers/ModuleDataHelper.php';

/**
 * Get data for this module
 * Change 'committee-structure' to your module's key
 */
$module_key = 'committee-structure'; // CHANGE THIS
$data = ModuleDataHelper::getModuleData($module_key);
$total_items = ModuleDataHelper::getItemCount($module_key);

/**
 * Handle CRUD operations
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    switch ($action) {
        case 'add':
            // Prepare data from form
            $new_item = [];
            foreach ($_POST as $key => $value) {
                if ($key !== 'action') {
                    $new_item[$key] = sanitize_input($value);
                }
            }
            ModuleDataHelper::addItem($module_key, $new_item);
            set_message('Item added successfully', 'success');
            break;
            
        case 'update':
            $id = (int)$_POST['id'];
            $updates = [];
            foreach ($_POST as $key => $value) {
                if ($key !== 'action' && $key !== 'id') {
                    $updates[$key] = sanitize_input($value);
                }
            }
            ModuleDataHelper::updateItem($module_key, $id, $updates);
            set_message('Item updated successfully', 'success');
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            ModuleDataHelper::deleteItem($module_key, $id);
            set_message('Item deleted successfully', 'success');
            break;
            
        case 'search':
            $field = $_POST['field'] ?? 'name';
            $value = $_POST['value'] ?? '';
            $data = ModuleDataHelper::searchItems($module_key, $field, $value);
            break;
    }
    
    // Refresh data
    $data = ModuleDataHelper::getModuleData($module_key);
}

/**
 * Utility functions
 */
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function set_message($message, $type = 'info') {
    $_SESSION['module_message'] = [
        'text' => $message,
        'type' => $type
    ];
}

function get_message() {
    if (isset($_SESSION['module_message'])) {
        $message = $_SESSION['module_message'];
        unset($_SESSION['module_message']);
        return $message;
    }
    return null;
}

function display_message() {
    $message = get_message();
    if (!$message) return;
    
    $bg_color = $message['type'] === 'success' ? 'bg-green-50' : 'bg-blue-50';
    $border_color = $message['type'] === 'success' ? 'border-green-200' : 'border-blue-200';
    $text_color = $message['type'] === 'success' ? 'text-green-700' : 'text-blue-700';
    $icon = $message['type'] === 'success' ? 'bi-check-circle' : 'bi-info-circle';
    
    echo <<<HTML
    <div class="mb-6 p-4 rounded-lg border $bg_color $border_color">
        <div class="flex items-center gap-3">
            <i class="bi $icon $text_color text-xl"></i>
            <p class="$text_color font-medium">{$message['text']}</p>
        </div>
    </div>
    HTML;
}

/**
 * Format data for display
 */
function format_field($value, $type = 'text') {
    switch ($type) {
        case 'date':
            return date('M d, Y', strtotime($value));
        case 'status':
            $colors = [
                'Active' => 'bg-green-100 text-green-700',
                'Pending' => 'bg-yellow-100 text-yellow-700',
                'Completed' => 'bg-blue-100 text-blue-700',
                'Draft' => 'bg-gray-100 text-gray-700',
                'In Progress' => 'bg-purple-100 text-purple-700',
            ];
            $class = $colors[$value] ?? 'bg-gray-100 text-gray-700';
            return "<span class='px-2 py-1 rounded-full text-xs font-medium $class'>$value</span>";
        default:
            return htmlspecialchars($value);
    }
}
?>

<!-- Now you can use these functions in your HTML -->
<!-- Example Table Display: -->
<?php
function display_data_table($data, $columns = []) {
    if (empty($data)) {
        echo '<p class="text-gray-600 dark:text-gray-400 py-8 text-center">No data available</p>';
        return;
    }
    
    echo '<div class="overflow-x-auto">';
    echo '<table class="w-full text-sm">';
    echo '<thead class="bg-gray-100 dark:bg-gray-700">';
    echo '<tr>';
    
    // Display column headers
    if (!empty($columns)) {
        foreach ($columns as $column) {
            echo "<th class='px-4 py-2 text-left font-semibold text-gray-900 dark:text-white'>$column</th>";
        }
    } else {
        // Auto-detect columns from first item
        $first = reset($data);
        foreach (array_keys($first) as $key) {
            echo "<th class='px-4 py-2 text-left font-semibold text-gray-900 dark:text-white'>" . ucfirst(str_replace('_', ' ', $key)) . "</th>";
        }
    }
    
    echo '<th class="px-4 py-2 text-center font-semibold text-gray-900 dark:text-white">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($data as $item) {
        echo '<tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">';
        
        foreach ($item as $key => $value) {
            if ($key !== 'id') {
                $is_status = strpos($key, 'status') !== false;
                $formatted = $is_status ? format_field($value, 'status') : format_field($value);
                echo "<td class='px-4 py-3 text-gray-900 dark:text-gray-100'>$formatted</td>";
            }
        }
        
        echo '<td class="px-4 py-3 text-center">';
        echo '<div class="flex gap-2 justify-center">';
        echo '<button class="text-blue-600 hover:text-blue-700 text-sm font-medium" onclick="editItem(' . $item['id'] . ')">Edit</button>';
        echo '<button class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="deleteItem(' . $item['id'] . ')">Delete</button>';
        echo '</div>';
        echo '</td>';
        
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
?>
