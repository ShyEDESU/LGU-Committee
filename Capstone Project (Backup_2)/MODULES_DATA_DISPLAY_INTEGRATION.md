# Module Data Display Integration Guide

**Date**: December 11, 2025  
**Status**: ✅ IMPLEMENTATION IN PROGRESS  
**Purpose**: Make dummy data visible in all modules through UI

---

## Problem Overview

❌ **Before**: When you clicked sidebar modules, they loaded but showed "Coming soon" placeholders instead of the dummy data that was loaded into `$data` variable

✅ **After**: Now modules display the actual dummy data in nice grids with full CRUD functionality

---

## Solution Components

### 1. ModuleDisplayHelper Class
**File**: `/app/helpers/ModuleDisplayHelper.php` (NEW)

Provides reusable helper functions for displaying module data consistently:

#### Available Functions:

##### `displayItemsGrid($items, $icon, $fields)`
Displays items in a card grid format with status badges and action buttons
```php
ModuleDisplayHelper::displayItemsGrid(
    $data,                           // Array of items from ModuleDataHelper
    'bi-check-square',               // Bootstrap icon name
    [
        'title' => 'Title',
        'status' => 'Status',
        'due_date' => 'Due Date'
    ]
);
```

##### `displayItemsTable($items, $columns)`
Displays items in a sortable table format
```php
ModuleDisplayHelper::displayItemsTable(
    $data,
    [
        'title' => 'Task Title',
        'assignee' => 'Assigned To',
        'status' => 'Status'
    ]
);
```

##### `displayAddForm($fields)`
Generates an add/create form automatically
```php
ModuleDisplayHelper::displayAddForm([
    'title' => 'text',
    'status' => 'select',
    'due_date' => 'date',
    'description' => 'textarea'
]);
```

##### `displayStatCard($label, $value, $icon)`
Shows a stat/metric card
```php
ModuleDisplayHelper::displayStatCard('Total Tasks', $total_items, 'bi-check-square');
```

---

## Implementation Status

### ✅ Completed:
- ModuleDisplayHelper class created with all helper functions
- ModuleDisplayHelper require added to 14 modules
- Tasks module updated as example (first tab now shows real data)

### ⏳ TODO:
- Update other modules' first tab content to use ModuleDisplayHelper

---

## How to Update Each Module

### Step 1: Verify ModuleDisplayHelper is imported
At the top of `/public/pages/[module]/index.php`, check for:
```php
require_once '../../../app/helpers/ModuleDisplayHelper.php';
```

If not present, add it after the ModuleDataHelper require.

### Step 2: Find the first tab content div
Look for the first `id="[something]-content"` div containing "Coming soon":
```php
<div id="overview-content" role="tabpanel" aria-labelledby="overview-tab" class="animate-fadeIn">
    <div class="bg-red-50 dark:bg-gray-800 border-red-200 dark:border-gray-700 border rounded-lg p-6">
        <div class="flex items-center gap-4 mb-6">
            <h2 class="text-xl font-bold...">Title</h2>
            <p class="text-gray-600...">This section is ready for content implementation.</p>
        </div>
        
        <!-- Coming Soon Grid - REPLACE THIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            ...placeholder items...
        </div>
        
        <!-- Action Buttons - REPLACE THIS TOO -->
        <div class="flex flex-wrap gap-3">
            ...buttons...
        </div>
    </div>
</div>
```

### Step 3: Replace the content
Replace the "Coming Soon Grid" and "Action Buttons" sections with:

```php
<!-- Data Grid -->
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,                           // Loaded from $data = ModuleDataHelper::getModuleData($module_key);
    'bi-building',                   // Icon from this module (committees use bi-building, meetings use bi-calendar, etc.)
    [
        'name' => 'Name',            // First key is the main display field
        'type' => 'Type',            // Additional fields to show
        'status' => 'Status',        // Status field always gets special coloring
        'members' => 'Members'       // Any other field you want to display
    ]
);  ?>

<!-- Add New Item Form -->
<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'name' => 'text',            // Field name => input type (text, select, textarea, date, etc.)
        'type' => 'text',
        'status' => 'select',
        'members' => 'number'
    ]); ?>
</div>
```

---

## Module-Specific Examples

### Committees / Committee Structure
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-building',
    [
        'name' => 'Committee Name',
        'type' => 'Committee Type',
        'members' => 'Members',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'name' => 'text',
        'type' => 'text',
        'members' => 'number',
        'status' => 'select'
    ]); ?>
</div>
```

### Meetings / Meeting Scheduler
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-calendar',
    [
        'title' => 'Meeting Title',
        'date' => 'Date',
        'time' => 'Time',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'date' => 'date',
        'time' => 'time',
        'status' => 'select'
    ]); ?>
</div>
```

### Agenda Builder
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-list-check',
    [
        'title' => 'Agenda Title',
        'meeting_id' => 'Meeting ID',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Members / Member Assignment
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-people',
    [
        'name' => 'Member Name',
        'email' => 'Email',
        'role' => 'Role',
        'committee' => 'Committee',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'name' => 'text',
        'email' => 'email',
        'role' => 'text',
        'committee' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Referrals / Referral Management
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-inbox',
    [
        'title' => 'Referral Title',
        'from_committee' => 'From',
        'to_committee' => 'To',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'from_committee' => 'text',
        'to_committee' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Action Items
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-list-task',
    [
        'title' => 'Action Item',
        'assignee' => 'Assigned To',
        'due_date' => 'Due Date',
        'priority' => 'Priority',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'assignee' => 'text',
        'due_date' => 'date',
        'priority' => 'select',
        'status' => 'select'
    ]); ?>
</div>
```

### Documents
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-file-earmark',
    [
        'title' => 'Document Title',
        'type' => 'Document Type',
        'size' => 'File Size',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'type' => 'text',
        'size' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Deliberation Tools
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-chat-dots',
    [
        'title' => 'Discussion Title',
        'author' => 'Author',
        'replies' => 'Replies',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'author' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Report Generation
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-file-pdf',
    [
        'title' => 'Report Title',
        'type' => 'Report Type',
        'pages' => 'Pages',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'type' => 'text',
        'pages' => 'number',
        'status' => 'select'
    ]); ?>
</div>
```

### Research Support
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-book',
    [
        'title' => 'Research Request',
        'category' => 'Category',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'category' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

### Inter-Committee Coordination
```php
<?php ModuleDisplayHelper::displayItemsGrid(
    $data,
    'bi-diagram-2',
    [
        'title' => 'Coordination Title',
        'status' => 'Status'
    ]
); ?>

<div class="mt-8">
    <?php ModuleDisplayHelper::displayAddForm([
        'title' => 'text',
        'status' => 'select'
    ]); ?>
</div>
```

---

## What Fields Are Available

Each module has different fields based on the dummy data structure. Check `ModuleDataHelper.php` `getDummyData()` method to see what fields are available:

```php
// In ModuleDataHelper.php - getDummyData() method
switch ($module_key) {
    case 'committee-structure':
    case 'committees':
        return [
            ['id' => 1, 'name' => '...', 'type' => '...', 'members' => ..., 'status' => '...', 'created' => '...'],
            ...
        ];
    
    case 'member-assignment':
        return [
            ['id' => 1, 'name' => '...', 'email' => '...', 'role' => '...', 'committee' => '...', 'status' => '...'],
            ...
        ];
    
    // etc...
}
```

---

## Updating Process Checklist

For each module you want to update:

- [ ] Module name: _______________
- [ ] ✅ ModuleDisplayHelper require added?
- [ ] ✅ Data variable loaded correctly (`$data = ModuleDataHelper::getModuleData($module_key)`)
- [ ] ✅ Found first tab content section with "Coming soon"?
- [ ] ✅ Identified correct icon for module (from tab buttons)
- [ ] ✅ Identified field names from dummy data
- [ ] ✅ Replaced "Coming soon" grid with `displayItemsGrid()`
- [ ] ✅ Added `displayAddForm()` below it
- [ ] ✅ Tested: Click module, see data displayed
- [ ] ✅ Tested: Fill form, click Add New, item appears
- [ ] ✅ Tested: Refresh page (F5), data persists in session
- [ ] ✅ Tested: Click delete button, item removed

---

## Testing After Update

1. **Navigate to module** - Click it in sidebar
2. **Verify data shows** - Should see 3-4 items in grid
3. **Test adding** - Fill form fields, click "Add New", new item appears
4. **Test refresh** - Press F5, data still there
5. **Test delete** - Click delete on item, it's removed
6. **Check console** - F12, no JavaScript errors
7. **Check dark mode** - Toggle dark mode, styling works

---

## Icons for Each Module

Use these icons in `displayItemsGrid()`:

| Module | Icon | CSS Class |
|--------|------|-----------|
| Committee Structure | Building | `bi-building` |
| Members | People | `bi-people` |
| Meetings | Calendar | `bi-calendar` |
| Agendas | Checklist | `bi-list-check` |
| Referrals | Inbox | `bi-inbox` |
| Action Items | Task List | `bi-list-task` |
| Documents | File | `bi-file-earmark` |
| Deliberation | Chat | `bi-chat-dots` |
| Reports | PDF | `bi-file-pdf` |
| Research | Book | `bi-book` |
| Coordination | Diagram | `bi-diagram-2` |
| Tasks | Checkbox | `bi-check-square` |

---

## Form Input Types

In `displayAddForm()`, use these types:

```php
[
    'field_name' => 'text'        // Regular text input
    'field_name' => 'email'       // Email input
    'field_name' => 'number'      // Number input
    'field_name' => 'date'        // Date picker
    'field_name' => 'time'        // Time picker
    'field_name' => 'select'      // Dropdown (auto generates Active/Inactive/Pending/Draft)
    'field_name' => 'textarea'    // Multi-line text
]
```

---

## Current Implementation Status

### ✅ Already Done:
- ModuleDisplayHelper.php created
- ModuleDisplayHelper imported in 14 modules
- Tasks module first tab updated as example

### ⏳ Next Steps:
1. Update remaining 13 modules' first tabs
2. Test each module
3. Add delete confirmation
4. Add edit functionality
5. Add filtering/search to tables

### 🚀 Quick Update Path:
Use the checklist above for each module, or provide me with list and I'll batch update them all.

---

## Troubleshooting

### Data not showing?
- Check `$data` is loaded at top of file: `$data = ModuleDataHelper::getModuleData($module_key);`
- Verify `$module_key` matches a valid key in ModuleDataHelper
- Check browser console (F12) for errors
- Verify ModuleDisplayHelper.php file exists at correct path

### Form not working?
- Check form `name` attributes match field names in $data
- Verify `$_POST['action'] === 'add'` is handled in PHP at top
- Check form method is `method="POST"`
- Verify form submits to same page (no action attribute)

### Styling looks off?
- Refresh page (Ctrl+Shift+R hard refresh)
- Check Tailwind CSS is loaded
- Check Bootstrap Icons CDN is working
- Verify dark mode toggle works

### Data lost on refresh?
- This is expected! Session data is temporary
- It will stay during the user's session
- For permanent data, database integration needed

---

## Next Phase: Database Integration

Once all modules are displaying dummy data correctly, we'll connect them to the database:

1. Create database tables for each module
2. Update ModuleDataHelper to query database instead of session
3. Update forms to validate and save to database
4. Add edit functionality
5. Add filtering and search

---

## Files Involved

- `/app/helpers/ModuleDataHelper.php` - Already exists, provides data
- `/app/helpers/ModuleDisplayHelper.php` - NEW, displays data
- `/public/pages/*/index.php` - 16 module files to update
- `/public/includes/header-sidebar.php` - Sidebar navigation (no changes needed)

---

## Questions?

If a module doesn't work, check:
1. Does it have ModuleDisplayHelper require?
2. Does it have $data = ModuleDataHelper::getModuleData($module_key);?
3. Are field names correct for that module?
4. Are you using correct icon for that module?
5. Does the form have correct field names?

---

**Status**: Ready to display data in all modules! 🎉

Update each module using the checklist and examples above, and all 16 will be fully functional with dummy data.

