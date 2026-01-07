<?php
/**
 * Module Display Helper
 * Provides reusable functions to display module data in consistent format
 */

class ModuleDisplayHelper {
    
    /**
     * Display a grid of items with consistent styling
     * 
     * @param array $items Array of items to display
     * @param string $icon Bootstrap icon class name (e.g., 'bi-building')
     * @param array $fields Associative array of field => display_name mappings
     * @return void
     */
    public static function displayItemsGrid($items, $icon, $fields) {
        if (empty($items)) {
            echo '<div class="bg-white dark:bg-gray-800 rounded-lg p-8 border border-gray-200 dark:border-gray-700 text-center">';
            echo '<i class="bi ' . htmlspecialchars($icon) . ' text-gray-400 text-4xl mb-3 block"></i>';
            echo '<p class="text-gray-600 dark:text-gray-400">No items yet. Create one to get started!</p>';
            echo '</div>';
            return;
        }
        
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">';
        
        foreach ($items as $item) {
            echo '<div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">';
            
            // Header with icon and primary field
            echo '<div class="flex items-start justify-between mb-3">';
            echo '<div class="flex items-center gap-3 flex-1">';
            echo '<div class="text-red-700 text-2xl flex-shrink-0"><i class="bi ' . htmlspecialchars($icon) . '"></i></div>';
            echo '<div class="flex-1 min-w-0">';
            
            // Display main field (first field in the fields array)
            $firstField = array_key_first($fields);
            $displayName = htmlspecialchars($item[$firstField] ?? 'Untitled');
            echo '<h3 class="font-semibold text-gray-900 dark:text-white truncate">' . $displayName . '</h3>';
            
            // Display secondary info
            if (isset($item['type'])) {
                echo '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">' . htmlspecialchars($item['type']) . '</p>';
            } elseif (isset($item['category'])) {
                echo '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">' . htmlspecialchars($item['category']) . '</p>';
            }
            
            echo '</div></div></div>';
            
            // Body with fields
            echo '<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">';
            echo '<div class="grid grid-cols-2 gap-3 text-sm">';
            
            // Display configured fields
            $count = 0;
            foreach ($fields as $fieldKey => $fieldLabel) {
                if ($fieldKey === $firstField || $count >= 4) continue; // Skip primary field and limit display
                
                $value = htmlspecialchars($item[$fieldKey] ?? '-');
                
                // Format status with badge
                if ($fieldKey === 'status') {
                    $statusColor = self::getStatusColor($value);
                    echo '<div>';
                    echo '<span class="text-gray-600 dark:text-gray-400">' . htmlspecialchars($fieldLabel) . ':</span>';
                    echo '<p class="font-semibold"><span class="px-2 py-1 rounded-full text-xs ' . $statusColor . '">' . htmlspecialchars($value) . '</span></p>';
                    echo '</div>';
                } else {
                    echo '<div>';
                    echo '<span class="text-gray-600 dark:text-gray-400">' . htmlspecialchars($fieldLabel) . ':</span>';
                    echo '<p class="font-semibold text-gray-900 dark:text-white truncate">' . $value . '</p>';
                    echo '</div>';
                }
                
                $count++;
            }
            
            echo '</div>';
            
            // Footer with timestamp
            $dateField = $item['created'] ?? $item['date'] ?? null;
            if ($dateField) {
                echo '<p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Created: ' . htmlspecialchars($dateField) . '</p>';
            }
            
            // Action buttons
            echo '<div class="flex gap-2 mt-4">';
            echo '<button onclick="editItem(' . (int)($item['id'] ?? 0) . ')" class="flex-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 px-2 py-1 rounded transition-colors">';
            echo '<i class="bi bi-pencil"></i> Edit';
            echo '</button>';
            echo '<form method="POST" style="flex: 1;" onsubmit="return confirm(\'Are you sure?\');">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="id" value="' . (int)($item['id'] ?? 0) . '">';
            echo '<button type="submit" class="w-full text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 px-2 py-1 rounded transition-colors">';
            echo '<i class="bi bi-trash"></i> Delete';
            echo '</button>';
            echo '</form>';
            echo '</div>';
            
            echo '</div></div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Display a table of items
     * 
     * @param array $items Array of items to display
     * @param array $columns Associative array of column_key => column_header mappings
     * @return void
     */
    public static function displayItemsTable($items, $columns) {
        if (empty($items)) {
            echo '<div class="bg-white dark:bg-gray-800 rounded-lg p-8 border border-gray-200 dark:border-gray-700 text-center">';
            echo '<p class="text-gray-600 dark:text-gray-400">No items to display</p>';
            echo '</div>';
            return;
        }
        
        echo '<div class="overflow-x-auto">';
        echo '<table class="w-full">';
        
        // Header
        echo '<thead class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">';
        echo '<tr>';
        foreach ($columns as $key => $header) {
            echo '<th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($header) . '</th>';
        }
        echo '<th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>';
        echo '</tr>';
        echo '</thead>';
        
        // Body
        echo '<tbody class="divide-y divide-gray-200 dark:divide-gray-700">';
        foreach ($items as $item) {
            echo '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">';
            
            foreach ($columns as $key => $header) {
                $value = htmlspecialchars($item[$key] ?? '-');
                
                // Format status
                if ($key === 'status') {
                    $statusColor = self::getStatusColor($value);
                    echo '<td class="px-6 py-3"><span class="px-2 py-1 rounded-full text-xs ' . $statusColor . '">' . $value . '</span></td>';
                } else {
                    echo '<td class="px-6 py-3 text-sm text-gray-900 dark:text-white">' . $value . '</td>';
                }
            }
            
            // Actions
            echo '<td class="px-6 py-3 text-sm">';
            echo '<div class="flex gap-2">';
            echo '<button onclick="editItem(' . (int)($item['id'] ?? 0) . ')" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">';
            echo '<i class="bi bi-pencil"></i>';
            echo '</button>';
            echo '<form method="POST" style="display: inline;" onsubmit="return confirm(\'Delete this item?\');">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="id" value="' . (int)($item['id'] ?? 0) . '">';
            echo '<button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">';
            echo '<i class="bi bi-trash"></i>';
            echo '</button>';
            echo '</form>';
            echo '</div>';
            echo '</td>';
            
            echo '</tr>';
        }
        echo '</tbody>';
        
        echo '</table>';
        echo '</div>';
    }
    
    /**
     * Get CSS classes for status badge color
     * 
     * @param string $status The status value
     * @return string CSS class string
     */
    private static function getStatusColor($status) {
        $status = strtolower($status);
        
        $colors = [
            'active' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'draft' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'in progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            'inactive' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'archived' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
    
    /**
     * Display a mini stats card
     * 
     * @param string $label The label for the stat
     * @param mixed $value The value to display
     * @param string $icon Bootstrap icon class
     * @return void
     */
    public static function displayStatCard($label, $value, $icon = 'bi-bar-chart') {
        echo '<div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">';
        echo '<div class="flex items-center justify-between">';
        echo '<div>';
        echo '<p class="text-sm text-gray-600 dark:text-gray-400">' . htmlspecialchars($label) . '</p>';
        echo '<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">' . htmlspecialchars($value) . '</p>';
        echo '</div>';
        echo '<div class="text-red-600 text-3xl opacity-20"><i class="bi ' . htmlspecialchars($icon) . '"></i></div>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Display add item form
     * 
     * @param array $fields Associative array of field_name => field_type pairs
     * @return void
     */
    public static function displayAddForm($fields) {
        echo '<form method="POST" class="bg-red-50 dark:bg-gray-800 border border-red-200 dark:border-gray-700 rounded-lg p-6">';
        echo '<input type="hidden" name="action" value="add">';
        
        echo '<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add New Item</h3>';
        
        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">';
        
        foreach ($fields as $fieldName => $fieldType) {
            echo '<div>';
            echo '<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">';
            echo htmlspecialchars(ucfirst(str_replace('_', ' ', $fieldName)));
            echo '</label>';
            
            if ($fieldType === 'textarea') {
                echo '<textarea name="' . htmlspecialchars($fieldName) . '" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3"></textarea>';
            } elseif ($fieldType === 'select') {
                echo '<select name="' . htmlspecialchars($fieldName) . '" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">';
                echo '<option value="Active">Active</option>';
                echo '<option value="Inactive">Inactive</option>';
                echo '<option value="Pending">Pending</option>';
                echo '<option value="Draft">Draft</option>';
                echo '</select>';
            } else {
                echo '<input type="' . htmlspecialchars($fieldType) . '" name="' . htmlspecialchars($fieldName) . '" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        
        echo '<button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">';
        echo '<i class="bi bi-plus-circle"></i>';
        echo 'Add Item';
        echo '</button>';
        
        echo '</form>';
    }
}
?>
