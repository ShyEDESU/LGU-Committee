# Quick Reference Guide

## Login

```
URL: http://localhost/legislative-cms/login.php
Username: admin
Password: admin123
```

## Main Navigation

### Users & Roles
- **Manage Users**: Add, edit, deactivate users
- **Add User**: Create new user account
- **Roles & Permissions**: Configure role access

### Committees
- **All Committees**: List and search committees
- **Create Committee**: Create new committee
- **Directory**: View committee details and members

### Meetings
- **All Meetings**: List meetings with filters
- **Schedule Meeting**: Create new meeting
- **Calendar View**: Month/week calendar view

### Documents
- **All Documents**: Track legislative documents
- **Create Document**: Submit new ordinance/resolution
- **Track Documents**: Monitor document workflow

### Referrals
- **Incoming Referrals**: Receive referrals from council
- **Outgoing Referrals**: Send referrals to other committees
- **Endorsements**: Track endorsements

### Tasks
- **Task Tracker**: View and manage assigned tasks
- Progress tracking and deadline reminders

### Reports
- **Performance**: Committee statistics
- **Document Reports**: Document processing metrics
- **Export Reports**: Generate PDF/Excel reports

### Administration
- **General Settings**: System configuration
- **Backup & Restore**: Database backups
- **Audit Logs**: User action history
- **Error Logs**: System error tracking

### Account
- **My Profile**: Edit personal information
- **Change Password**: Update password

---

## Common Tasks

### Creating a User

1. Go to **Users & Roles** → **Add User**
2. Fill in personal information
3. Select role
4. Click **Save**
5. Share credentials with user

### Scheduling a Meeting

1. Go to **Meetings** → **Schedule Meeting**
2. Select committee
3. Enter meeting details
4. Select attendees
5. Set date and time
6. Click **Schedule**
7. System sends notifications automatically

### Creating a Committee

1. Go to **Committees** → **Create Committee**
2. Enter committee name and details
3. Assign chairperson
4. Add members
5. Click **Create**

### Uploading a Document

1. Go to **Documents** → **Create Document**
2. Enter document information
3. Select document type
4. Upload file (if needed)
5. Click **Submit**

### Assigning a Task

1. Go to **Tasks** → **New Task** (button on dashboard)
2. Select assignee
3. Enter task details
4. Set priority and deadline
5. Click **Assign**

### Generating a Report

1. Go to **Reports** → Select report type
2. Choose date range
3. Select filters (committee, status, etc.)
4. Click **Generate**
5. Download as PDF or Excel

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| Ctrl + S | Save current form |
| Esc | Close modal/dialog |
| Ctrl + F | Search in table |
| Ctrl + P | Print page |
| Ctrl + Shift + L | Logout |

---

## Role Permissions

### Administrator
- Full system access
- User management
- System settings
- All reports
- Backup/restore

### Committee Chair
- Create documents
- Schedule meetings
- Manage committee
- View all documents
- Assign tasks

### Committee Secretary
- Record attendance
- Create meeting minutes
- Upload documents
- Manage invitations
- View referrals

### Staff/Encoder
- Encode documents
- Track documents
- Upload files
- View assigned tasks
- Submit reports

### Public Viewer
- View public documents
- View calendar
- View ordinances
- View resolutions
- View roster

---

## Database

### Connection Info
```
Host: localhost
User: root
Password: (as configured)
Database: legislative_cms
Port: 3306
```

### Backup
```bash
# Backup database
mysqldump -u root -p legislative_cms > backup.sql

# Restore database
mysql -u root -p legislative_cms < backup.sql
```

---

## File Locations

| File/Directory | Purpose |
|---|---|
| `config/database.php` | Database configuration |
| `public/assets/css/style.css` | Stylesheet |
| `public/assets/js/main.js` | JavaScript |
| `public/uploads/` | Uploaded files |
| `app/controllers/` | Business logic |
| `app/models/` | Database models |
| `database/schema.sql` | Database schema |
| `docs/` | Documentation |

---

## Troubleshooting Quick Fixes

### Can't login
1. Check database connection in `config/database.php`
2. Verify user account exists in database
3. Check if account is active (is_active = 1)
4. Try admin/admin123

### Blank page
1. Check browser console for errors
2. Enable error reporting in config/database.php
3. Check PHP error logs
4. Verify file permissions

### Sidebar not showing
1. Clear browser cache
2. Check if CSS file loads
3. Enable JavaScript
4. Try different browser

### Slow performance
1. Check database query performance
2. Verify database indexes exist
3. Archive old data
4. Optimize tables

### File upload not working
1. Check uploads folder permissions (777)
2. Verify file size limit in PHP
3. Check available disk space
4. Verify file type allowed

---

## Important URLs

| Page | URL |
|---|---|
| Login | `/login.php` |
| Dashboard | `/public/dashboard.php` |
| Users | `/public/users/index.php` |
| Committees | `/public/committees/index.php` |
| Meetings | `/public/meetings/index.php` |
| Documents | `/public/documents/index.php` |
| phpMyAdmin | `http://localhost/phpmyadmin` |

---

## Security Reminders

✓ Change default admin password immediately
✓ Use HTTPS in production
✓ Regular backups
✓ Monitor audit logs
✓ Update PHP/MySQL regularly
✓ Set proper file permissions
✓ Use strong passwords
✓ Enable 2FA if available

---

## Support

For issues:
1. Check this quick reference
2. Read detailed documentation in `/docs/`
3. Check error logs in database
4. Review audit logs for actions
5. Contact system administrator

---

**Last Updated**: November 24, 2025
**Version**: 1.0
