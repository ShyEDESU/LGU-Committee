# Installation & Setup Guide

## Quick Start (5 Minutes)

### 1. Extract Files
Extract the project to your web server root:
- **Windows (XAMPP)**: `C:\xampp\htdocs\legislative-cms\`
- **Linux**: `/var/www/html/legislative-cms/`
- **Mac (MAMP)**: `/Applications/MAMP/htdocs/legislative-cms/`

### 2. Create Database
**Via phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Click "New" → Enter name: `legislative_cms` → Click "Create"
3. Select the new database
4. Click "Import" tab
5. Choose file: `database/schema.sql`
6. Click "Go"

**Via Command Line:**
```bash
mysql -u root -p < database/schema.sql
```

### 3. Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'legislative_cms');
```

### 4. Access the System
Open in browser: `http://localhost/legislative-cms/login.php`

### 5. Login with Default Credentials
```
Username: admin
Password: admin123
```

⚠️ **Change the admin password immediately!**

---

## Detailed Setup

### System Requirements

#### Hardware
- Processor: 1 GHz or faster
- RAM: 512 MB minimum (1 GB recommended)
- Disk Space: 100 MB minimum

#### Software
- **Web Server**: Apache 2.2+ with mod_rewrite
- **PHP**: 7.4+ (7.4, 8.0, 8.1, or 8.2 tested)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Browser**: Chrome, Firefox, Safari, Edge (latest versions)

#### PHP Extensions
Ensure these are enabled in `php.ini`:
```ini
extension=mysqli
extension=json
extension=openssl
extension=curl
extension=mbstring
extension=fileinfo
```

### Step-by-Step Installation

#### Step 1: Prepare Web Server

**XAMPP (Windows):**
```bash
# Start XAMPP Control Panel
# Enable Apache and MySQL
# Verify: http://localhost shows "It works!"
```

**Linux (Apache):**
```bash
# Install PHP and MySQL
sudo apt-get install php-cli php-mysql mysql-server apache2

# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

**Mac (MAMP):**
```bash
# Download and install MAMP
# Launch MAMP
# Select PHP version 7.4+
# Start servers
```

#### Step 2: Create Directory Structure

```bash
cd /path/to/web/root
mkdir legislative-cms
cd legislative-cms

# Copy project files here
# Ensure proper ownership
chown -R www-data:www-data .
chmod -R 755 .
```

#### Step 3: Database Setup

**Method A: phpMyAdmin (Easiest)**
1. Navigate to http://localhost/phpmyadmin
2. Login (default: username=root, password=empty)
3. Create new database named `legislative_cms`
4. Select database → Import
5. Upload `database/schema.sql`
6. Click Go

**Method B: MySQL Command Line**
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE legislative_cms;
USE legislative_cms;

# Import schema
SOURCE database/schema.sql;

# Verify tables
SHOW TABLES;
```

**Method C: MySQL Client (GUI)**
1. Open MySQL client
2. Connect to server
3. Create new database: `legislative_cms`
4. Open SQL editor
5. Import `database/schema.sql`

#### Step 4: Configure Application

Edit `config/database.php`:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');     // Usually localhost
define('DB_USER', 'root');          // MySQL username
define('DB_PASS', '');              // MySQL password (empty for default)
define('DB_NAME', 'legislative_cms'); // Database name
define('DB_PORT', 3306);            // MySQL port

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
```

**For Remote Database:**
```php
define('DB_HOST', 'your_server.com'); // Remote host
define('DB_USER', 'dbuser');          // Remote user
define('DB_PASS', 'dbpassword');      // Remote password
define('DB_NAME', 'database_name');   // Remote database
```

#### Step 5: Configure Web Server

**Apache .htaccess** (`legislative-cms/.htaccess`):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /legislative-cms/
    
    # Allow directories, files, links
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-l
    
    # Redirect index requests
    RewriteRule ^index\.html$ - [L]
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

**Nginx Configuration** (if using Nginx):
```nginx
location /legislative-cms/ {
    try_files $uri $uri/ /legislative-cms/index.php?$query_string;
}
```

#### Step 6: Set File Permissions

**Linux/Mac:**
```bash
# Set directory permissions
chmod -R 755 public/
chmod -R 755 app/
chmod -R 755 config/

# Set file permissions
chmod 644 config/database.php

# Writable directories
chmod -R 777 public/uploads/
chmod -R 777 storage/
chmod -R 777 temp/

# Create directories if not exist
mkdir -p public/uploads
mkdir -p storage
mkdir -p temp
```

**Windows:**
- Right-click folder → Properties → Security
- Select group → Edit → Full Control
- Apply to subfolders

#### Step 7: Test Installation

1. **Check Database Connection:**
   - Open http://localhost/legislative-cms/login.php
   - If database error appears, check `config/database.php`

2. **Test Login:**
   - Username: `admin`
   - Password: `admin123`
   - Should redirect to dashboard.php

3. **Check File Uploads:**
   - Try uploading profile picture in My Profile
   - Should save to `public/uploads/`

4. **Verify Email (Optional):**
   - Configure SMTP in settings
   - Send test notification

### Post-Installation Configuration

#### 1. Change Admin Password
1. Login as admin
2. Go to My Profile
3. Click "Change Password"
4. Enter new password (min 8 characters)
5. Confirm and save

#### 2. Update System Settings
1. Go to Settings → General Settings
2. Update LGU Information
3. Upload logo if available
4. Select theme color
5. Configure timezone
6. Save changes

#### 3. Create Initial Users
1. Go to Users & Roles → Add User
2. Fill user information
3. Select role
4. Activate user
5. Send login credentials to user

#### 4. Create Committees
1. Go to Committees → Create Committee
2. Fill committee details
3. Assign chairperson and members
4. Save

#### 5. Enable Backups
1. Go to Backup & Restore
2. Set backup frequency (Daily/Weekly/Monthly)
3. Enable auto-backup
4. Manual backup immediately

#### 6. Configure Notifications (Optional)
1. Go to Settings → Notifications
2. Configure email/SMS service
3. Set notification preferences
4. Send test notification

### Troubleshooting Installation

#### "Connection failed: No such file or directory"
**Cause**: Database server not running
**Solution**:
```bash
# Start MySQL
sudo service mysql start

# Or in XAMPP, click Start on MySQL module
```

#### "Access denied for user 'root'@'localhost'"
**Cause**: Wrong password in config
**Solution**:
- Check MySQL password
- Update `config/database.php`
- Reset MySQL root password if needed

#### "Table doesn't exist"
**Cause**: Schema not imported
**Solution**:
- Import `database/schema.sql` again
- Check for import errors
- Verify all tables created with `SHOW TABLES;`

#### "Cannot upload files"
**Cause**: Permission denied on uploads folder
**Solution**:
```bash
chmod -R 777 public/uploads/
chmod -R 777 storage/
```

#### "CSS/JS files not loading"
**Cause**: Incorrect path configuration
**Solution**:
- Check browser console for 404 errors
- Verify file exists in `public/assets/`
- Check .htaccess configuration
- Try clearing browser cache

#### "Sessions not working"
**Cause**: PHP session configuration
**Solution**:
```php
// Check php.ini
session.save_path = /tmp  # or Windows temp folder
session.use_cookies = 1
session.use_only_cookies = 1
```

#### "Blank page displayed"
**Cause**: PHP error or misconfiguration
**Solution**:
```php
// Enable error reporting temporarily in config/database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Security Hardening

After installation, implement these security measures:

#### 1. Change Default Credentials
```sql
-- Change admin password
UPDATE users SET password_hash = PASSWORD('new_secure_password') 
WHERE username = 'admin';
```

#### 2. Remove Demo Account (if exists)
```sql
-- Delete demo accounts
DELETE FROM users WHERE username = 'demo';
```

#### 3. Configure SSL/HTTPS
```apache
# Force HTTPS in .htaccess
<IfModule mod_rewrite.c>
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

#### 4. Set Secure Headers
```php
// Add to index file headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000");
```

#### 5. Regular Backups
```bash
# Automated daily backup script
# Add to cron job
0 2 * * * /usr/bin/mysqldump -u root -p[password] legislative_cms > /backup/db_$(date +\%Y\%m\%d).sql
```

#### 6. Database Backups
```bash
# Manual backup
mysqldump -u root -p legislative_cms > backup.sql

# Restore from backup
mysql -u root -p legislative_cms < backup.sql
```

### Verification Checklist

- [ ] Web server running and accessible
- [ ] PHP version 7.4+
- [ ] MySQL server running
- [ ] Database created: legislative_cms
- [ ] Database schema imported
- [ ] config/database.php configured
- [ ] Can access login page
- [ ] Can login with admin/admin123
- [ ] Dashboard displays correctly
- [ ] File uploads working
- [ ] Sidebar navigation working
- [ ] Responsive design on mobile
- [ ] Admin password changed
- [ ] Backup configured
- [ ] HTTPS enabled (production)

---

**Installation Complete!**

Your Legislative Services Committee Management System is now ready to use.

For next steps, see the main README.md file.
