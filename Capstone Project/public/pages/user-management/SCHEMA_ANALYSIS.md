# Database Schema Analysis for User Management

## Actual Users Table Structure (from schema.sql):

```sql
CREATE TABLE users (
  user_id INT PRIMARY KEY,
  email VARCHAR(100) UNIQUE NOT NULL,
  profile_picture VARCHAR(255),
  phone VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  role_id INT NOT NULL,                    -- FK to roles table
  department VARCHAR(100),
  position VARCHAR(100),
  bio TEXT,
  address TEXT,
  employee_id VARCHAR(50),
  is_active BOOLEAN DEFAULT FALSE,         -- NOT 'status' ENUM
  email_verified BOOLEAN DEFAULT FALSE,
  last_login DATETIME,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## Key Differences from User Management Code:

| Code Expected | Actual Schema | Fix Needed |
|--------------|---------------|------------|
| `role_name` (VARCHAR) | `role_id` (INT FK) | JOIN with roles table |
| `status` (ENUM) | `is_active` (BOOLEAN) | Convert to boolean |
| `username` | NOT EXISTS | Already removed ✅ |

## Required Changes:

### 1. All SELECT queries need JOIN:
```sql
SELECT u.*, r.role_name 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.role_id
```

### 2. Status field mapping:
- `status = 'active'` → `is_active = TRUE`
- `status = 'inactive'` → `is_active = FALSE`
- `status = 'suspended'` → Need new column OR use is_active = FALSE

### 3. INSERT/UPDATE queries:
- Use `role_id` instead of `role_name`
- Use `is_active` (boolean) instead of `status` (enum)

### 4. Filter queries:
- Role filter: `r.role_name = ?` (after JOIN)
- Status filter: `u.is_active = ?` (boolean)

## Implementation Strategy:

1. Update `getUsers()` - Add JOIN, change status to is_active
2. Update `getUserById()` - Add JOIN
3. Update `createUser()` - Use role_id, is_active
4. Update `updateUser()` - Use role_id, is_active
5. Update `getRoles()` - Query roles table
6. Update UI - Status dropdown (Active/Inactive only)
7. Update AJAX handlers - Handle role_id conversion
