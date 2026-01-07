# Settings Structure Recommendation

## Current Situation

You have a **Settings** page in the sidebar that currently contains mostly admin-level settings. You're asking whether regular users should have their own settings, and if so, where they should be located.

## ✅ **Recommendation: YES, Keep Settings Separate**

### For Administrators (System Settings Page)
**Location:** `pages/system-settings/index.php` (accessible from sidebar)

**Purpose:** System-wide configurations that affect all users

**Should Include:**
- System Name & Organization
- System Timezone & Date Format
- Email Configuration (SMTP settings)
- Security Policies (Password requirements, Session timeout)
- Backup & Recovery
- System-wide 2FA enforcement
- Module enable/disable toggles
- Default system preferences

**Access:** Admin only

---

### For Regular Users (User Profile Settings)
**Location:** `pages/user-management/index.php` → Profile Tab → Settings Section

**Purpose:** Personal preferences that only affect the individual user

**Should Include:**
1. **Display Preferences**
   - Theme (Light/Dark mode)
   - Language preference
   - Sidebar default state (expanded/collapsed)
   - Items per page in lists
   - Dashboard widget preferences

2. **Personal Information**
   - Profile picture
   - Contact information
   - Department/Position

3. **Notification Preferences**
   - Email notifications (on/off for different events)
   - In-app notification settings
   - Notification sound
   - Quiet hours
   - Notification categories to receive

4. **Personal Security**
   - Change password
   - Setup 2FA (if system allows)
   - View active sessions
   - Login history
   - Security questions

5. **Personal Date/Time**
   - Personal timezone (if different from system)
   - Preferred date format
   - Time format (12h vs 24h)

**Access:** All users (for their own profile)

---

## Why Keep Them Separate?

### System Settings (Admin)
- **Affects everyone** - Changes here impact all users
- **Requires admin knowledge** - Technical configurations
- **Security sensitive** - SMTP passwords, backup settings
- **Organizational decisions** - System name, policies

### User Profile Settings (Individual)
- **Personal choice** - Each user customizes their experience
- **No system impact** - Changes only affect that user
- **User-friendly** - Non-technical preferences
- **Convenience** - Quick access to personal settings

---

## Implementation Structure

```
Sidebar
├── Dashboard
├── [Core Modules...]
└── Support Systems
    ├── User Management
    │   └── Profile Tab
    │       ├── Personal Info
    │       ├── Settings (User Preferences) ← NEW
    │       ├── Notifications Preferences ← NEW
    │       └── Security
    └── Settings (System Settings) ← EXISTING (Admin only)
```

---

## User Experience Flow

### Regular User Wants to Change Theme:
1. Click their profile picture → "My Profile"
2. Go to "Settings" tab
3. Toggle Dark/Light mode
4. ✅ Only affects their view

### Admin Wants to Change System Name:
1. Click "Settings" in sidebar
2. Go to "General" tab
3. Update system name
4. ✅ Affects all users' view

---

## Summary

**YES**, you should have settings for regular users, **BUT** they should be in the **User Profile** section, NOT in the main Settings page.

- **Main Settings (Sidebar)** = Admin-only system configurations
- **User Profile Settings** = Individual user preferences

This separation:
- ✅ Prevents confusion
- ✅ Improves security (users can't access admin settings)
- ✅ Better user experience (users find their settings in their profile)
- ✅ Follows standard web application patterns

---

## Next Steps

1. Keep current "Settings" page as-is (admin only)
2. Add a "Settings" or "Preferences" tab to User Management → Profile
3. Move user-specific preferences there
4. Clearly label "System Settings" vs "My Preferences"
