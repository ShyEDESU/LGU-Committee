# 🚀 Quick Start - Add Functions to a Module (5 Minutes)

## What to Do in 5 Simple Steps

### Step 1: Open Your Module File
```
Example: /public/pages/member-assignment/index.php
```

### Step 2: Find This Line (Top of File)
```php
<?php session_start(); ?>
<?php include '../../../public/includes/header-sidebar.php'; ?>
```

### Step 3: Replace With This
```php
<?php 
session_start(); 
require_once '../../../app/helpers/ModuleDataHelper.php';

// Get data for this module
$module_key = 'member-assignment';  // CHANGE THIS!
$data = ModuleDataHelper::getModuleData($module_key);
$total_items = ModuleDataHelper::getItemCount($module_key);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        ModuleDataHelper::addItem($module_key, $_POST);
    } elseif ($_POST['action'] === 'update') {
        ModuleDataHelper::updateItem($module_key, (int)$_POST['id'], $_POST);
    } elseif ($_POST['action'] === 'delete') {
        ModuleDataHelper::deleteItem($module_key, (int)$_POST['id']);
    }
    $data = ModuleDataHelper::getModuleData($module_key);
}
?>
<?php include '../../../public/includes/header-sidebar.php'; ?>
```

### Step 4: Replace Tab Content
Find your first tab content (look for `<div id="...-content"`):

**OLD:**
```php
<!-- Coming Soon Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6...">
        <div class="text-gray-700 text-2xl">
            <i class="bi bi-box-seam"></i>
        </div>
        <h3>Item 1</h3>
        <p>Coming soon</p>
    </div>
    <!-- ... more coming soon items ... -->
</div>
```

**NEW:**
```php
<!-- Data Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <?php if (empty($data)): ?>
        <p class="text-gray-600 dark:text-gray-400">No data available</p>
    <?php else: ?>
        <?php foreach ($data as $item): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="text-blue-700 text-2xl">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($item['name'] ?? $item['title'] ?? 'Item'); ?>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <?php echo htmlspecialchars($item['type'] ?? $item['status'] ?? 'No type'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    ID: <?php echo $item['id']; ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
```

### Step 5: Test It!
1. Save the file
2. Open the module in your browser
3. You should see dummy data displayed
4. Click "Add New" and add an item
5. See it appear in the list

**Done! ✨**

---

## 🔑 Module Keys Reference

Copy/paste the correct `$module_key`:

```php
$module_key = 'committee-structure';        // 1
$module_key = 'member-assignment';          // 2
$module_key = 'meeting-scheduler';          // 3
$module_key = 'agenda-builder';             // 4
$module_key = 'referral-management';        // 5
$module_key = 'action-items';               // 6
$module_key = 'documents';                  // 7
$module_key = 'deliberation-tools';         // 8
$module_key = 'report-generation';          // 9
$module_key = 'research-support';           // 10
```

---

## 🎯 What Data Is Available?

### By Module Key:

**committee-structure** returns:
```php
Array (
    [0] => Array ( [id] => 1, [name] => "Finance Committee", [type] => "Standing", [members] => 7, [status] => "Active", [created] => "2025-01-15" )
    [1] => Array ( [id] => 2, [name] => "Public Safety", [type] => "Standing", [members] => 5, [status] => "Active", [created] => "2025-01-10" )
    [2] => Array ( [id] => 3, [name] => "Parks & Recreation", [type] => "Special", [members] => 4, [status] => "Active", [created] => "2025-02-01" )
)
```

**member-assignment** returns:
```php
Array (
    [0] => Array ( [id] => 1, [name] => "John Smith", [email] => "john@example.com", [role] => "Chairperson", [committee] => "Finance Committee", [status] => "Active" )
    [1] => Array ( [id] => 2, [name] => "Mary Johnson", [email] => "mary@example.com", [role] => "Vice-Chair", [committee] => "Finance Committee", [status] => "Active" )
    [2] => Array ( [id] => 3, [name] => "Robert Brown", [email] => "robert@example.com", [role] => "Member", [committee] => "Public Safety", [status] => "Active" )
)
```

**meeting-scheduler** returns:
```php
Array (
    [0] => Array ( [id] => 1, [title] => "Finance Committee Meeting", [date] => "2025-12-15", [time] => "10:00 AM", [location] => "Conference Room A", [status] => "Scheduled" )
    ...
)
```

**Same pattern for all 10 modules!**

---

## 🎨 Styling Tips

### Blue Module:
```php
<div class="text-blue-700 text-2xl">
    <i class="bi bi-people"></i>
</div>
```

### Green Module:
```php
<div class="text-green-700 text-2xl">
    <i class="bi bi-calendar"></i>
</div>
```

### Purple Module:
```php
<div class="text-purple-700 text-2xl">
    <i class="bi bi-megaphone"></i>
</div>
```

---

## 📝 Form Example

To add the "Add New" form:

```html
<form method="POST" class="mt-6 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Add New Item</h3>
    
    <input type="hidden" name="action" value="add">
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name/Title</label>
        <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type/Status</label>
        <input type="text" name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
    </div>
    
    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium">Add Item</button>
</form>
```

---

## 🧪 Testing Checklist

After each module update:

- [ ] Page loads without error
- [ ] Data displays in grid/table
- [ ] Item count shows (should be 3)
- [ ] Dark mode styling looks right
- [ ] "Add New" form visible
- [ ] Can submit form
- [ ] New item appears in list
- [ ] Refresh keeps data (session)
- [ ] No console errors (F12)

---

## ❓ Troubleshooting

### "Class not found" Error?
**Check**: Path in `require_once` is correct
```php
require_once '../../../app/helpers/ModuleDataHelper.php';
// Count levels: /public/pages/[module]/index.php → /app/helpers/
// That's 3 levels up: ../../.. ✓
```

### Data Not Showing?
**Check**: Module key is correct
```php
$module_key = 'member-assignment';  // Must match table
```

### Form Not Working?
**Check**: Form method is POST
```html
<form method="POST">
    <input type="hidden" name="action" value="add">
    ...
</form>
```

### Data Disappears on Refresh?
**Normal!** Session storage is temporary
- Data is in `$_SESSION['module_data']`
- Lost when browser closes
- To keep data: use database instead
- For now: Just for testing

---

## 🎯 Next: Integrate All 15 Modules

Time to update each module:

| Module | Est. Time | Module Key |
|--------|-----------|-----------|
| Committee Structure | ✅ Done | committee-structure |
| Member Assignment | 5 min | member-assignment |
| Meeting Scheduler | 5 min | meeting-scheduler |
| Agenda Builder | 5 min | agenda-builder |
| Referral Management | 5 min | referral-management |
| Action Items | 5 min | action-items |
| Documents | 5 min | documents |
| Deliberation Tools | 5 min | deliberation-tools |
| Report Generation | 5 min | report-generation |
| Research Support | 5 min | research-support |
| Inter-Committee | 5 min | inter-committee |
| Tasks | 5 min | tasks |
| Meetings | 5 min | meetings |
| Committees | 5 min | committees |
| *More...* | 5 min | *...* |

**Total Time**: ~75 minutes for all 15 remaining modules!

---

## 💡 Remember

- Copy the structure from Committee Structure module
- Change the module_key to your module
- Update the HTML to match your needs
- Test with dummy data
- When ready: switch to database

That's it! 🎉

---

**Version**: 1.0  
**Last Updated**: December 11, 2025  
**Status**: Ready to Use ✓
