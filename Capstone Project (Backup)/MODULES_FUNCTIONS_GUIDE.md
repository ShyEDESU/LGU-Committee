# 🎯 Module Functions & Dummy Data Integration Guide

## Overview
This document explains how to add functions and dummy data to all 16 modules in your system.

---

## ✅ What's Been Done

### 1. **ModuleDataHelper Class** (`app/helpers/ModuleDataHelper.php`)
Core helper class that provides:
- ✅ Dummy data storage in session
- ✅ CRUD operations (Create, Read, Update, Delete)
- ✅ Search functionality
- ✅ Data counting
- ✅ Overall statistics

### 2. **Committee Structure Module** (Updated)
First module updated with:
- ✅ Functions to get/add/update/delete committees
- ✅ Dummy data displayed in Overview tab
- ✅ Form submission handling
- ✅ Real-time data refresh

### 3. **ModuleTemplate Helper** (`app/helpers/ModuleTemplate.php`)
Template file containing:
- ✅ CRUD operation handlers
- ✅ Data sanitization
- ✅ Message system
- ✅ Table display functions
- ✅ Data formatting utilities

---

## 🔧 How to Use in Any Module

### Step 1: Include the Helper at the Top of Your Module
```php
<?php 
session_start(); 
require_once '../../../app/helpers/ModuleDataHelper.php';

// Get data for your module
$module_key = 'your-module-name'; // e.g., 'member-assignment'
$data = ModuleDataHelper::getModuleData($module_key);
$total_items = ModuleDataHelper::getItemCount($module_key);
?>
```

### Step 2: Handle Form Submissions (Optional)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            ModuleDataHelper::addItem($module_key, [
                'name' => $_POST['name'],
                'status' => $_POST['status'],
                'date' => date('Y-m-d')
            ]);
        }
    }
    // Refresh data
    $data = ModuleDataHelper::getModuleData($module_key);
}
```

### Step 3: Display Data in HTML
```php
<?php foreach ($data as $item): ?>
    <div class="card">
        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
        <p>Status: <?php echo htmlspecialchars($item['status']); ?></p>
    </div>
<?php endforeach; ?>
```

---

## 📋 Available Functions in ModuleDataHelper

### Get Data
```php
// Get all items for a module
$data = ModuleDataHelper::getModuleData('committee-structure');

// Get count of items
$count = ModuleDataHelper::getItemCount('committee-structure');

// Search items
$results = ModuleDataHelper::searchItems('committee-structure', 'name', 'Finance');

// Get overall stats
$stats = ModuleDataHelper::getOverallStats();
```

### Create/Update/Delete
```php
// Add new item
ModuleDataHelper::addItem('committee-structure', [
    'name' => 'New Committee',
    'type' => 'Standing',
    'members' => 5,
    'status' => 'Active',
    'created' => '2025-12-11'
]);

// Update item
ModuleDataHelper::updateItem('committee-structure', 1, [
    'status' => 'Inactive'
]);

// Delete item
ModuleDataHelper::deleteItem('committee-structure', 1);
```

---

## 🗂️ Module Keys & Data Types

Map your module to its data key for ModuleDataHelper:

| Module | Key | Data Type | Primary Fields |
|--------|-----|-----------|----------------|
| Committee Structure | `committee-structure` | committees | name, type, members, status, created |
| Member Assignment | `member-assignment` | members | name, email, role, committee, status |
| Meeting Scheduler | `meeting-scheduler` | meetings | title, date, time, location, status |
| Agenda Builder | `agenda-builder` | agendas | title, meeting_id, items, status, created |
| Referral Management | `referral-management` | referrals | title, from_committee, to_committee, status |
| Action Items | `action-items` | action_items | title, assignee, due_date, priority, status |
| Documents | `documents` | documents | title, type, size, uploaded, status |
| Deliberation Tools | `deliberation-tools` | discussions | title, author, replies, status, created |
| Report Generation | `report-generation` | reports | title, type, generated, pages, status |
| Research Support | `research-support` | research | title, category, status, requested |

---

## 📝 Dummy Data Structure

### Committees
```json
{
    "id": 1,
    "name": "Finance Committee",
    "type": "Standing",
    "members": 7,
    "status": "Active",
    "created": "2025-01-15"
}
```

### Members
```json
{
    "id": 1,
    "name": "John Smith",
    "email": "john@example.com",
    "role": "Chairperson",
    "committee": "Finance Committee",
    "status": "Active"
}
```

### Meetings
```json
{
    "id": 1,
    "title": "Finance Committee Meeting",
    "date": "2025-12-15",
    "time": "10:00 AM",
    "location": "Conference Room A",
    "status": "Scheduled"
}
```

---

## 🚀 Integration Steps for Each Module

### For Each of the 16 Modules:

1. **Open Module File**: `/public/pages/[module-name]/index.php`

2. **Add at Top** (after `<?php session_start(); ?>`):
```php
require_once '../../../app/helpers/ModuleDataHelper.php';

$module_key = '[module-name]'; // From table above
$data = ModuleDataHelper::getModuleData($module_key);
$total_items = ModuleDataHelper::getItemCount($module_key);
```

3. **Add Form Handling** (optional, before HTML):
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Handle add action
        ModuleDataHelper::addItem($module_key, $_POST);
        $data = ModuleDataHelper::getModuleData($module_key);
    }
}
```

4. **Update Tab Content** to display `$data` variable:
```php
<?php foreach ($data as $item): ?>
    <!-- Display item -->
<?php endforeach; ?>
```

---

## ✨ Example: Complete Integration

Here's a complete example for a tab in Committee Structure:

```php
<?php
// At top of file
require_once '../../../app/helpers/ModuleDataHelper.php';
$committees = ModuleDataHelper::getModuleData('committee-structure');
$total = ModuleDataHelper::getItemCount('committee-structure');

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    ModuleDataHelper::addItem('committee-structure', [
        'name' => $_POST['name'],
        'type' => $_POST['type'],
        'members' => (int)$_POST['members'],
        'status' => 'Active',
        'created' => date('Y-m-d')
    ]);
    $committees = ModuleDataHelper::getModuleData('committee-structure');
}
?>

<!-- In HTML -->
<div id="overview-content">
    <h2>Total: <?php echo $total; ?> Committees</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($committees as $committee): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($committee['name']); ?></h3>
                <p>Type: <?php echo htmlspecialchars($committee['type']); ?></p>
                <p>Members: <?php echo $committee['members']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Committee Name">
        <select name="type">
            <option>Standing</option>
            <option>Special</option>
            <option>Ad Hoc</option>
        </select>
        <input type="number" name="members" placeholder="Number of Members">
        <button type="submit">Add Committee</button>
    </form>
</div>
```

---

## 🔄 Testing the System

### Test Data Flow:

1. **View**: Navigate to a module
   - Should display dummy data from `ModuleDataHelper`
   - Count should show total items

2. **Create**: Submit a form to add new item
   - Item added to session storage
   - Page refreshes and shows new item in list

3. **Search**: Use search function
   - Filter items by field
   - Results displayed

4. **Update**: Submit form to edit item
   - Item details updated in session
   - Display refreshes

5. **Delete**: Remove item
   - Item removed from session storage
   - List refreshes

---

## 💾 Later: Database Integration

When ready to integrate with database:

1. Modify `ModuleDataHelper` methods to query database instead of session
2. Keep same function names and return formats
3. No changes needed to module HTML/forms
4. Drop-in replacement for session storage

### Example Database Query:
```php
// Instead of: $_SESSION['module_data']['committees']
// Use: $result = $conn->query("SELECT * FROM committees");
// Return as array same as current dummy data
```

---

## 📊 Session Storage Example

Data is stored in `$_SESSION['module_data']`:

```php
$_SESSION['module_data'] = [
    'committees' => [
        ['id' => 1, 'name' => 'Finance', ...],
        ['id' => 2, 'name' => 'Parks', ...],
    ],
    'members' => [
        ['id' => 1, 'name' => 'John', ...],
    ],
    'meetings' => [...],
    // ... more data types
]
```

---

## 🎯 Next Steps

1. ✅ Copy ModuleDataHelper and ModuleTemplate to `/app/helpers/`
2. ⏳ Update each of the 16 module files to include the helper
3. ⏳ Replace "Coming Soon" content with real dummy data
4. ⏳ Add form handling for CRUD operations
5. ⏳ Test all modules work correctly
6. ⏳ When satisfied, integrate with database

---

## 🔧 Troubleshooting

### Data Not Showing?
- Check module key matches the mapping in ModuleDataHelper
- Verify require_once path is correct
- Check $_SESSION is started

### Forms Not Working?
- Verify form action points to same page
- Check $_POST values match field names
- Ensure ModuleDataHelper::addItem() call is before refresh

### Data Lost After Reload?
- Normal - session storage is temporary
- For permanent storage, switch to database
- Or use cookies/localStorage as alternative

---

## 📚 Files Created/Modified

| File | Action | Purpose |
|------|--------|---------|
| `/app/helpers/ModuleDataHelper.php` | ✅ Created | Core data management class |
| `/app/helpers/ModuleTemplate.php` | ✅ Created | Template and utility functions |
| `/public/pages/committee-structure/index.php` | ✅ Modified | First working example |
| *All other 15 modules* | ⏳ To Update | Use same pattern as Committee Structure |

---

## 💡 Pro Tips

1. **Use `htmlspecialchars()` when outputting user data** to prevent XSS
2. **Sanitize all form inputs** before storing
3. **Use `date('Y-m-d')` for consistent date format**
4. **Test CRUD operations** before deploying
5. **Keep dummy data realistic** for better testing

---

**Created**: December 11, 2025  
**Status**: Ready for Implementation  
**Version**: 1.0
