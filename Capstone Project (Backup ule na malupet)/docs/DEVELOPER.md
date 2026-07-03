# Developer Guide

## Architecture Overview

### MVC Pattern
The system uses Model-View-Controller architecture:

```
REQUEST
   ↓
ROUTER (index.php or direct access)
   ↓
CONTROLLER (app/controllers/)
   ├→ Validates input
   ├→ Calls models
   └→ Prepares data
   ↓
MODEL (app/models/)
   ├→ Database queries
   ├→ Business logic
   └→ Returns data
   ↓
VIEW (public/[module]/)
   ├→ Renders HTML
   ├→ Displays data
   └→ Handles user interaction
   ↓
RESPONSE
```

### Directory Structure

```
app/
├── controllers/           # Request handlers
│   ├── AuthController.php
│   ├── UserController.php
│   ├── CommitteeController.php
│   ├── MeetingController.php
│   ├── DocumentController.php
│   └── ...
├── models/               # Database models
│   ├── User.php
│   ├── Committee.php
│   ├── Meeting.php
│   └── ...
└── middleware/           # Authentication, etc.
    └── SessionManager.php

public/
├── index.php
├── login.php
├── dashboard.php
├── users/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── ...
├── committees/
├── meetings/
├── documents/
└── assets/
    ├── css/
    │   └── style.css
    ├── js/
    │   └── main.js
    └── images/

config/
└── database.php          # Configuration

database/
└── schema.sql            # Database schema

docs/
├── README.md
├── INSTALLATION.md
├── API.md
└── DEVELOPER.md          # This file
```

## Creating a New Module

### Step 1: Create Database Tables

Add tables to `database/schema.sql`:

```sql
CREATE TABLE IF NOT EXISTS `new_module` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 2: Create Model

File: `app/models/NewModule.php`

```php
<?php
class NewModule {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    public function getAll() {
        $query = "SELECT * FROM new_module ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM new_module WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        $query = "INSERT INTO new_module (name, description) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $data['name'], $data['description']);
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $query = "UPDATE new_module SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $data['name'], $data['description'], $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM new_module WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
```

### Step 3: Create Controller

File: `app/controllers/NewModuleController.php`

```php
<?php
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../models/NewModule.php');
require_once(__DIR__ . '/../middleware/SessionManager.php');

class NewModuleController {
    private $model;
    private $sessionManager;
    
    public function __construct() {
        global $conn;
        $this->model = new NewModule($conn);
        $this->sessionManager = new SessionManager($conn);
        
        // Check authorization
        if (!$this->sessionManager->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function getAll() {
        return $this->model->getAll();
    }
    
    public function store($data) {
        if ($this->model->create($data)) {
            return ['success' => true, 'message' => 'Created successfully'];
        }
        return ['success' => false, 'message' => 'Failed to create'];
    }
    
    public function update($id, $data) {
        if ($this->model->update($id, $data)) {
            return ['success' => true, 'message' => 'Updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update'];
    }
    
    public function delete($id) {
        if ($this->model->delete($id)) {
            return ['success' => true, 'message' => 'Deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete'];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $controller = new NewModuleController();
    
    if ($action === 'create') {
        $response = $controller->store($_POST);
        echo json_encode($response);
    } elseif ($action === 'update') {
        $response = $controller->update($_POST['id'], $_POST);
        echo json_encode($response);
    } elseif ($action === 'delete') {
        $response = $controller->delete($_POST['id']);
        echo json_encode($response);
    }
}
?>
```

### Step 4: Create Views

File: `public/new_module/index.php`

```php
<?php
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../../app/controllers/NewModuleController.php');

$controller = new NewModuleController();
$items = $controller->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Module - Legislative Services CMS</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <!-- Include header, sidebar, etc. -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">New Module</h1>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New
            </a>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                        Edit
                                    </a>
                                    <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script src="../../public/assets/js/main.js"></script>
    <script>
        function deleteItem(id) {
            if (confirm('Are you sure?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('../../app/controllers/NewModuleController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        AlertManager.success(data.message);
                        location.reload();
                    } else {
                        AlertManager.danger(data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
```

### Step 5: Add Sidebar Menu Item

Edit `public/dashboard.php`:

```php
<li class="sidebar-menu-item">
    <a href="new_module/index.php" class="sidebar-link">
        <i class="sidebar-icon fas fa-icon-name"></i>
        <span>New Module</span>
    </a>
</li>
```

## Code Style Guide

### Naming Conventions

```php
// Classes - PascalCase
class UserController {}
class Committee {}

// Functions/Methods - camelCase
function getUserData() {}
public function getName() {}

// Variables - snake_case
$user_id = 1;
$total_count = 0;

// Constants - UPPER_CASE
define('DB_HOST', 'localhost');
const MAX_FILE_SIZE = 5000000;

// File names - PascalCase for classes
UserController.php
Committee.php

// File names - kebab-case for views
user-list.php
committee-edit.php
```

### PHP Best Practices

```php
<?php
// Use prepared statements to prevent SQL injection
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Sanitize user input
$name = htmlspecialchars($_POST['name']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Check permissions
if (!$sessionManager->hasPermission('module_access')) {
    die("Access denied");
}

// Use meaningful variable names
$is_active = true;
$user_count = 5;

// Comment complex logic
// Calculate total with tax included
$total = ($subtotal * (1 + $tax_rate));

// Handle errors gracefully
try {
    // Code here
} catch (Exception $e) {
    error_log($e->getMessage());
    die("An error occurred");
}

// Always close database connections
$stmt->close();
$conn->close();
?>
```

### JavaScript Best Practices

```javascript
// Use const by default, let for reassignment
const API_URL = 'http://localhost/api';
let currentUser = null;

// Use meaningful names
const isUserLoggedIn = true;
const fetchUserData = (userId) => {};

// Arrow functions for modern syntax
const getUser = (id) => {
    return fetch(`${API_URL}/users/${id}`)
        .then(response => response.json());
};

// Template literals for strings
const message = `Hello, ${firstName}!`;

// Proper error handling
try {
    const data = await response.json();
} catch (error) {
    console.error('Parse error:', error);
}

// Comments for non-obvious code
// Debounce search input to avoid excessive requests
const debouncedSearch = Utils.debounce(search, 300);
```

### CSS Best Practices

```css
/* Use variables for consistency */
:root {
    --primary-color: #3498db;
    --spacing-unit: 1rem;
}

/* BEM naming convention */
.card { }
.card__header { }
.card__title { }
.card__content { }

.btn { }
.btn--primary { }
.btn--success { }

/* Mobile-first approach */
.element { /* Mobile styles */ }

@media (min-width: 768px) {
    .element { /* Tablet+ styles */ }
}

/* Avoid inline styles */
/* Bad: style="color: red;" */
/* Good: Use CSS class */

/* Group related properties */
.component {
    /* Display & Layout */
    display: flex;
    flex-direction: column;
    
    /* Spacing */
    margin: 1rem;
    padding: 0.5rem;
    
    /* Size */
    width: 100%;
    height: auto;
    
    /* Styling */
    background: white;
    color: #333;
}
```

## Database Query Patterns

### Select Queries

```php
// Single record
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Multiple records
$query = "SELECT * FROM committees ORDER BY name";
$result = $conn->query($query);
$committees = $result->fetch_all(MYSQLI_ASSOC);

// Count
$query = "SELECT COUNT(*) as total FROM meetings WHERE status = 'completed'";
$result = $conn->query($query);
$count = $result->fetch_assoc()['total'];

// With JOIN
$query = "SELECT u.name, r.role_name 
          FROM users u 
          JOIN roles r ON u.role_id = r.role_id";
```

### Insert/Update/Delete

```php
// Insert
$query = "INSERT INTO users (name, email, role_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $name, $email, $role_id);
$stmt->execute();
$last_id = $conn->insert_id;

// Update
$query = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $name, $email, $user_id);
$stmt->execute();

// Delete
$query = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Affected rows check
if ($stmt->affected_rows > 0) {
    // Success
}
```

## Testing

### Manual Testing Checklist

```
[ ] Login with valid credentials
[ ] Login with invalid credentials
[ ] Create new record
[ ] Edit existing record
[ ] Delete record
[ ] Search functionality
[ ] Sort table columns
[ ] Filter by status
[ ] Export to PDF
[ ] Export to Excel
[ ] Upload file
[ ] Mobile responsive
[ ] Sidebar navigation
[ ] Permission checking
[ ] Error handling
```

### Performance Testing

```php
// Measure query time
$start = microtime(true);
// ... query code ...
$end = microtime(true);
$time = ($end - $start) * 1000; // milliseconds
echo "Query took: {$time}ms";

// Log slow queries (> 1000ms)
if ($time > 1000) {
    error_log("Slow query: {$query} - {$time}ms");
}
```

## Debugging

### Enable Debug Mode

```php
// In config/database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Debug Output

```php
// Print variable
echo '<pre>';
print_r($data);
echo '</pre>';

// Var dump
var_dump($data);

// Debug log
error_log(print_r($data, true));

// Check query
echo "Query: " . $query;
```

### Browser DevTools

```javascript
// Log to console
console.log('Data:', data);
console.error('Error:', error);
console.warn('Warning:', warning);
console.table(dataArray);

// Breakpoint
debugger;

// Performance
console.time('timer');
// ... code ...
console.timeEnd('timer');
```

## Deployment

### Production Checklist

- [ ] Disable debug mode
- [ ] Update error_reporting
- [ ] Configure SSL/HTTPS
- [ ] Set secure permissions
- [ ] Enable backups
- [ ] Configure monitoring
- [ ] Set up logging
- [ ] Test all features
- [ ] Optimize database
- [ ] Minify CSS/JS
- [ ] Enable caching
- [ ] Configure firewall

### Deployment Steps

```bash
# 1. Backup current system
mysqldump -u root -p legislative_cms > backup.sql

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install

# 4. Run migrations
php migrate.php

# 5. Optimize
php optimize.php

# 6. Clear cache
php cache:clear

# 7. Test
php test.php

# 8. Deploy
rsync -av . user@production:/var/www/html/cms/
```

---

For more information, see README.md and INSTALLATION.md
