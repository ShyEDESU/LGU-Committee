# User Management Module - Functions Summary

## Overview
The User Management module has been completely redesigned with three main user-facing tabs and one admin-only tab for full user control.

---

## 1. MY PROFILE TAB ✅

### Features:
- **Edit Personal Information**
  - First Name (editable)
  - Last Name (editable)
  - Email (read-only - cannot be changed)
  - Phone Number (editable)
  - Department (editable)
  - Position (editable)

### Functionality:
- Form submission with backend validation
- Database update using prepared statements (SQL injection safe)
- Session update upon successful change
- Success/Error messages displayed
- All changes persisted to database

### Database Action:
```sql
UPDATE users SET first_name=?, last_name=?, phone=?, department=?, position=? WHERE user_id=?
```

---

## 2. SETTINGS TAB ✅

### A. Change Password Section
- **Current Password** - Validation required
- **New Password** - Minimum 8 characters required
- **Confirm Password** - Must match new password
- **Security Features:**
  - Compares current password with hash using `password_verify()`
  - New password hashed using `PASSWORD_DEFAULT` algorithm
  - Password history in database

### B. Account Settings Section
- **Email Notifications** - Toggle to receive email updates (toggle UI)
- **Login Alerts** - Toggle to get notified of new login attempts (toggle UI)
- **Activity Summary** - Toggle for weekly activity summaries (toggle UI)

### Functionality:
- Form validation for password requirements
- Secure password hashing
- Error handling for incorrect current password
- Success confirmation messages
- Account preference toggles (UI ready for backend integration)

### Database Action:
```sql
UPDATE users SET password_hash=? WHERE user_id=?
```

---

## 3. HELP & SUPPORT TAB ✅

### A. Frequently Asked Questions (FAQs)
- **What is included:**
  - How do I reset my password?
  - How do I update my profile information?
  - What are the password requirements?
  - How can I enable login alerts?
  - How do I contact support?

### Features:
- HTML `<details>` elements for expandable Q&A
- Hover effects on questions
- Dark mode compatible

### B. Contact Support Section
- **Support Form with:**
  - Subject field
  - Message textarea
  - Send button

### Features:
- Simple contact form interface
- Ready for backend email integration

### C. Resources Section
- **User Manual** - Link placeholder
- **Video Tutorials** - Link placeholder
- **Phone Support** - +63 2 1234 5678
- **Live Chat** - Available 9AM - 5PM

### Features:
- Grid layout with 4 resource cards
- Icon-based visual indicators
- Hoverable cards

---

## 4. ALL USERS TAB (Admin Only) ✅

### Availability:
- Only visible to users with role 'admin' or 'administrator'
- Automatically hidden for regular users

### Features:
- **User Table with columns:**
  - Name (First + Last)
  - Email
  - Status (Active/Inactive badge)
  - Joined Date (formatted as "MMM dd, YYYY")

### Functionality:
- Fetches all users from database
- Status badge color-coded (Green for Active, Red for Inactive)
- Responsive table design
- Dark mode compatible

### Database Query:
```sql
SELECT user_id, email, first_name, last_name, role_id, is_active, created_at 
FROM users 
ORDER BY created_at DESC
```

---

## Technical Implementation

### Backend:
- **Session Management:** User data fetched from `$_SESSION`
- **Database Queries:** All using prepared statements for security
- **Form Handling:** POST method with action validation
- **Error Handling:** Try-catch style validation with user feedback
- **Authentication:** Session-based with redirect if not logged in

### Frontend:
- **Tab Navigation:** JavaScript-based tab switching
- **URL State:** Query parameter `?tab=profile|settings|help|all-users`
- **Persistence:** Active tab saved to localStorage
- **Dark Mode:** Full dark mode support with `dark:` classes
- **Responsive:** Mobile-first design with grid layouts

### Security Features:
- Prepared statements to prevent SQL injection
- Password hashing with `PASSWORD_DEFAULT`
- Session-based authentication check
- Role-based access control for admin features
- Disabled email field (read-only for security)

---

## Tab Navigation

### URL Parameters:
- `?tab=profile` - My Profile (default)
- `?tab=settings` - Settings
- `?tab=help` - Help & Support
- `?tab=all-users` - All Users (admin only)

### Example URLs:
```
/public/pages/user-management/index.php                    # Default = Profile
/public/pages/user-management/index.php?tab=settings       # Settings
/public/pages/user-management/index.php?tab=help           # Help & Support
/public/pages/user-management/index.php?tab=all-users      # All Users (admin)
```

---

## Messages & Feedback

### Success Messages:
- Profile updated successfully! ✓
- Password changed successfully! ✓

### Error Messages:
- Error updating profile ✗
- Passwords do not match ✗
- Password must be at least 8 characters ✗
- Current password is incorrect ✗
- Error changing password ✗

---

## Integration Points

### With Header-Sidebar Profile Menu:
- **My Profile** → `?tab=profile`
- **Settings** → `?tab=settings`
- **Help & Support** → `?tab=help`

All three links now navigate to User Management with the appropriate tab.

---

## Ready for Future Enhancement

The following features can be easily added:
- Email notification backend integration
- Login alerts implementation
- Activity summary email scheduling
- Support ticket system integration
- File upload for profile picture
- Two-factor authentication setup
- Login history viewing
- Device management
- API token generation

---

## Testing Checklist

- [ ] Profile form saves data correctly
- [ ] Email field is read-only
- [ ] Password change validates correctly
- [ ] Current password verification works
- [ ] All tabs switch correctly
- [ ] Tab state persists on page reload
- [ ] FAQs expand/collapse
- [ ] All Users table shows only for admin
- [ ] Dark mode works for all tabs
- [ ] Forms work on mobile devices
- [ ] Success/error messages display correctly

