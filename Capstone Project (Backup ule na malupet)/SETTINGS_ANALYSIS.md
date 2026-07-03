# System Settings vs User Profile - Overlap Analysis

## Overview

This document analyzes the overlap between the **System Settings** microsystem and **User Profile** settings, providing recommendations for consolidation.

---

## Current System Settings (from system-settings/index.php)

### 1. General Settings
- **System Name**: "Legislative Records Management System"
- **Organization**: "City Government of Valenzuela"
- **System Time Zone**: Asia/Manila (UTC+8)
- **Date Format**: MM/DD/YYYY, DD/MM/YYYY, YYYY-MM-DD

### 2. Security Settings
- **Two-Factor Authentication**: Enable/Disable
- **Session Timeout**: 30 minutes (configurable 5-120 minutes)
- **Password Policy**: Enforce strong passwords (checkbox)

### 3. Email Configuration
- **SMTP Server**: smtp.gmail.com
- **SMTP Port**: 587
- **Sender Email**: noreply@valenzuela.gov.ph

### 4. Backup & Recovery
- **Create Backup**: Manual backup trigger
- **Download Latest Backup**: Download backup file
- **Last Backup Info**: Timestamp and size

---

## Analysis: What Should Stay in System Settings vs User Profile

### ✅ KEEP in System Settings (Admin-Only, System-Wide)

These settings affect the entire system and should only be accessible to administrators:

1. **System Name** ✅
   - **Reason**: Affects all users, appears in headers, emails, reports
   - **Access**: Admin only
   - **Scope**: System-wide

2. **Organization** ✅
   - **Reason**: Legal entity name, appears in official documents
   - **Access**: Admin only
   - **Scope**: System-wide

3. **System Time Zone** ✅
   - **Reason**: Default timezone for all timestamps, meetings, deadlines
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Note**: Users can have personal timezone preferences in their profile

4. **Date Format** ✅
   - **Reason**: Default format for system-generated documents
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Note**: Users can have personal date format preferences in their profile

5. **Email Configuration (SMTP)** ✅
   - **Reason**: System email server configuration
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Security**: Contains sensitive server credentials

6. **Backup & Recovery** ✅
   - **Reason**: System maintenance and disaster recovery
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Security**: Critical system function

7. **Session Timeout** ✅
   - **Reason**: Security policy for all users
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Security**: Enforced security measure

8. **Password Policy** ✅
   - **Reason**: Security requirement for all users
   - **Access**: Admin only
   - **Scope**: System-wide
   - **Security**: Enforced security measure

---

### ⚠️ DUAL IMPLEMENTATION (Both System Settings AND User Profile)

These settings should exist in both places with different purposes:

#### Two-Factor Authentication (2FA)

**In System Settings (Admin):**
- **Setting**: "Enforce 2FA for all users" (Yes/No toggle)
- **Purpose**: Admin decides if 2FA is mandatory
- **Options**:
  - ✅ Mandatory for all users
  - ⚠️ Optional (users can choose)
  - ❌ Disabled system-wide

**In User Profile (Individual User):**
- **Setting**: "Setup Two-Factor Authentication"
- **Purpose**: User configures their own 2FA method
- **Options**:
  - Authenticator App (Google Authenticator, Microsoft Authenticator)
  - SMS verification
  - Email verification
  - Backup codes
- **Visibility**: Only shown if system allows 2FA (not disabled)
- **Required**: If admin enforces 2FA, user must set it up

**Example Flow:**
1. Admin enables "Enforce 2FA" in System Settings
2. Users see "2FA Setup Required" banner in their profile
3. Users configure their preferred 2FA method
4. Users cannot access system until 2FA is configured

---

### ➕ ADD to User Profile (Not Currently in System Settings)

These settings should be in the User Profile, not System Settings:

1. **Personal Information**
   - Full Name
   - Email Address
   - Phone Number
   - Profile Picture
   - Position/Role
   - Department

2. **Display Preferences**
   - **Theme**: Light/Dark mode preference
   - **Language**: English, Filipino, etc.
   - **Sidebar**: Expanded/Collapsed by default
   - **Items per page**: 10, 25, 50, 100

3. **Personal Date/Time Preferences**
   - **Personal Timezone**: Override system timezone
   - **Personal Date Format**: Override system date format
   - **Time Format**: 12-hour vs 24-hour

4. **Notification Preferences**
   - **Email Notifications**: On/Off for different events
   - **In-App Notifications**: On/Off
   - **Notification Sound**: On/Off
   - **Notification Categories**:
     - New referral assigned
     - Meeting scheduled
     - Action item deadline approaching
     - Committee report submitted
     - Agenda item added
     - Member assignment updated

5. **Email Notification Settings**
   - Daily digest vs real-time
   - Notification frequency
   - Quiet hours (no notifications during specific times)

6. **Security Settings (Personal)**
   - Change Password
   - Setup 2FA (if allowed by system)
   - Active Sessions (view and revoke)
   - Login History
   - Security Questions

7. **Privacy Settings**
   - Profile visibility
   - Show online status
   - Allow others to see my activity

---

## Recommended Implementation

### System Settings Page Structure
```
System Settings (Admin Only)
├── General
│   ├── System Name
│   ├── Organization
│   ├── System Time Zone
│   └── Default Date Format
├── Security
│   ├── Enforce 2FA (Yes/No)
│   ├── Session Timeout
│   └── Password Policy
├── Email Configuration
│   ├── SMTP Server
│   ├── SMTP Port
│   └── Sender Email
└── Backup & Recovery
    ├── Create Backup
    └── Download Backup
```

### User Profile Page Structure
```
User Profile (Individual Users)
├── Profile
│   ├── Personal Information
│   ├── Profile Picture
│   └── Contact Details
├── Preferences
│   ├── Theme (Light/Dark)
│   ├── Language
│   ├── Personal Timezone
│   ├── Personal Date Format
│   └── Display Settings
├── Notifications
│   ├── Email Notifications
│   ├── In-App Notifications
│   └── Notification Categories
├── Security
│   ├── Change Password
│   ├── Two-Factor Authentication (if enabled by admin)
│   ├── Active Sessions
│   └── Login History
└── Privacy
    ├── Profile Visibility
    └── Activity Settings
```

---

## Migration Plan (If Implementing Changes)

### Phase 1: Add Missing User Profile Settings
1. Create new tabs in User Management page:
   - Preferences tab
   - Notifications tab
   - Security tab (personal)
   - Privacy tab

2. Implement settings storage:
   - Add `user_preferences` table
   - Add `user_notification_settings` table
   - Update `users` table with new fields

### Phase 2: Update System Settings
1. Keep current system settings as-is (they're correct)
2. Add "Enforce 2FA" toggle
3. Update documentation

### Phase 3: Implement 2FA Dual System
1. Admin toggle in System Settings
2. User setup in User Profile
3. Enforcement logic in authentication

---

## Summary

### Current System Settings: ✅ CORRECT
All current settings in `system-settings/index.php` are appropriate and should remain there. They are system-wide, admin-only settings.

### No Overlap Issues
There is **NO problematic overlap** between System Settings and User Profile. The current System Settings are all admin-level, system-wide configurations.

### Recommendation
**KEEP System Settings as-is** and **ADD** the missing user-level settings to the User Profile page.

### Two-Factor Authentication
The only setting that should exist in both places is 2FA:
- **System Settings**: Admin controls if 2FA is enforced
- **User Profile**: Users configure their 2FA method

---

## Questions for Decision

1. **Should users be able to override system timezone/date format?**
   - ✅ Recommended: Yes, for personal convenience
   - ❌ Alternative: No, enforce system-wide consistency

2. **Should 2FA be mandatory or optional?**
   - ✅ Recommended: Admin-configurable (can enforce or make optional)
   - ⚠️ Alternative: Always mandatory for security
   - ❌ Not Recommended: Always optional

3. **Should we implement all user profile settings now or later?**
   - ✅ Recommended: Implement in phases (start with critical ones)
   - ⚠️ Alternative: Implement all at once (more work upfront)

---

**Conclusion**: The current System Settings microsystem is well-designed and does not need to be removed or significantly changed. Instead, focus on adding comprehensive user-level settings to the User Profile page.
