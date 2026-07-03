# Deployment Guide - Committee Management System

## Overview

This guide provides three different methods to run your Committee Management System without copying the entire project folder to XAMPP's htdocs directory.

---

## Option 1: PHP Built-in Server (Recommended for Development)

The simplest way to run your application for development and testing.

### Prerequisites
- PHP 7.4 or higher installed
- MySQL/MariaDB running (via XAMPP or standalone)

### Steps

1. **Open PowerShell or Command Prompt**
   ```powershell
   # Navigate to your project directory
   cd "d:\Desktop\2nd Year\Capstone Project"
   ```

2. **Start the PHP Built-in Server**
   ```powershell
   # Run from the project root, serving the 'public' folder
   php -S localhost:8000 -t public
   ```

3. **Access Your Application**
   - Open your browser and go to: `http://localhost:8000`
   - Login page: `http://localhost:8000/../auth/login.php`
   - Dashboard: `http://localhost:8000/dashboard.php`

### Advantages
✅ No configuration needed
✅ Quick to start/stop
✅ Perfect for development
✅ No file copying required

### Disadvantages
❌ Not suitable for production
❌ Single-threaded (slower for multiple users)
❌ Stops when you close the terminal

### Stopping the Server
Press `Ctrl + C` in the terminal window

---

## Option 2: Apache Virtual Host (Recommended for Production-like Testing)

Configure Apache to serve your project from its current location.

### Prerequisites
- XAMPP installed with Apache running
- Administrator access to edit configuration files

### Steps

1. **Open Apache Virtual Hosts Configuration**
   - File location: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
   - Open with administrator privileges (Notepad++ or VS Code)

2. **Add Virtual Host Entry**
   Add this at the end of the file:
   ```apache
   <VirtualHost *:80>
       ServerName cms.local
       ServerAlias www.cms.local
       DocumentRoot "d:/Desktop/2nd Year/Capstone Project/public"
       
       <Directory "d:/Desktop/2nd Year/Capstone Project/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog "logs/cms-error.log"
       CustomLog "logs/cms-access.log" common
   </VirtualHost>
   ```

3. **Edit Windows Hosts File**
   - File location: `C:\Windows\System32\drivers\etc\hosts`
   - Open with administrator privileges
   - Add this line:
   ```
   127.0.0.1    cms.local
   ```

4. **Enable Virtual Hosts in Apache**
   - Open: `C:\xampp\apache\conf\httpd.conf`
   - Find this line (around line 477):
   ```apache
   #Include conf/extra/httpd-vhosts.conf
   ```
   - Remove the `#` to uncomment it:
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```

5. **Restart Apache**
   - Open XAMPP Control Panel
   - Stop Apache
   - Start Apache again

6. **Access Your Application**
   - Open browser and go to: `http://cms.local`
   - Or: `http://www.cms.local`

### Advantages
✅ Production-like environment
✅ Supports .htaccess files
✅ Better performance
✅ Professional URL (cms.local)
✅ Runs in background

### Disadvantages
❌ Requires configuration
❌ Needs administrator access
❌ More complex setup

---

## Option 3: Symbolic Link (Quick Alternative)

Create a symbolic link in htdocs that points to your project folder.

### Prerequisites
- XAMPP installed
- Administrator access

### Steps

1. **Open Command Prompt as Administrator**
   - Press `Win + X`
   - Select "Command Prompt (Admin)" or "PowerShell (Admin)"

2. **Create Symbolic Link**
   ```cmd
   mklink /D "C:\xampp\htdocs\cms" "d:\Desktop\2nd Year\Capstone Project\public"
   ```

3. **Verify the Link**
   ```cmd
   dir "C:\xampp\htdocs"
   ```
   You should see `cms` listed as a `<SYMLINKD>`

4. **Access Your Application**
   - Open browser and go to: `http://localhost/cms`
   - Or: `http://127.0.0.1/cms`

### Advantages
✅ Easy to set up
✅ Works with existing XAMPP configuration
✅ No file copying
✅ Changes reflect immediately

### Disadvantages
❌ Requires administrator access
❌ Link breaks if source folder moves
❌ May have permission issues on some systems

### Removing the Symbolic Link
```cmd
rmdir "C:\xampp\htdocs\cms"
```
*Note: This only removes the link, not your actual project files*

---

## Database Configuration

Regardless of which option you choose, ensure your database is configured:

1. **Start MySQL in XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" next to MySQL

2. **Verify Database Connection**
   - Check `config/database.php` for correct credentials:
   ```php
   $host = 'localhost';
   $dbname = 'cms_database';
   $username = 'root';
   $password = ''; // Usually empty for XAMPP
   ```

3. **Import Database** (if needed)
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `cms_database`
   - Import your SQL file

---

## Troubleshooting

### Issue: "Port 80 already in use"
**Solution for Option 1 (PHP Server):**
```powershell
# Use a different port
php -S localhost:8080 -t public
```
Access at: `http://localhost:8080`

**Solution for Option 2 (Virtual Host):**
- Stop Skype, IIS, or other services using port 80
- Or change Apache port in `httpd.conf`

### Issue: "Access Denied" or Permission Errors
**Solution:**
1. Run Command Prompt/PowerShell as Administrator
2. Check folder permissions:
   - Right-click project folder → Properties → Security
   - Ensure your user has "Full Control"

### Issue: CSS/JS Files Not Loading
**Solution:**
- Check file paths in your HTML/PHP files
- Ensure paths are relative to the public folder
- Clear browser cache (Ctrl + F5)

### Issue: Database Connection Failed
**Solution:**
1. Verify MySQL is running in XAMPP
2. Check database credentials in `config/database.php`
3. Ensure database exists in phpMyAdmin

---

## Recommended Setup for Different Scenarios

### For Daily Development
**Use Option 1 (PHP Built-in Server)**
- Quick to start
- Easy to stop
- No configuration needed

### For Team Collaboration / Testing
**Use Option 2 (Virtual Host)**
- Professional setup
- Better performance
- Easier to share (just share the domain name)

### For Quick Testing
**Use Option 3 (Symbolic Link)**
- Fastest setup
- Works with existing XAMPP knowledge

---

## Security Notes

⚠️ **Important for Production:**
- Never use PHP built-in server in production
- Always use proper Apache/Nginx configuration
- Enable HTTPS with SSL certificates
- Set proper file permissions
- Disable error display in production
- Use environment variables for sensitive data

---

## Additional Resources

- **PHP Documentation**: https://www.php.net/manual/en/features.commandline.webserver.php
- **Apache Virtual Hosts**: https://httpd.apache.org/docs/2.4/vhosts/
- **XAMPP Documentation**: https://www.apachefriends.org/docs/

---

## Quick Reference Commands

```powershell
# Start PHP Server
php -S localhost:8000 -t public

# Stop PHP Server
Ctrl + C

# Create Symbolic Link (Admin CMD)
mklink /D "C:\xampp\htdocs\cms" "d:\Desktop\2nd Year\Capstone Project\public"

# Remove Symbolic Link
rmdir "C:\xampp\htdocs\cms"

# Check PHP Version
php -v

# Check if port is in use
netstat -ano | findstr :8000
```

---

**Need Help?** Check the troubleshooting section or consult your instructor/team lead.
