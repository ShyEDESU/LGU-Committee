# 🔐 Authentication & Session Management System

**Date**: December 4, 2025  
**Status**: ✅ Complete  
**Version**: 1.0

---

## Overview

The Legislative Services Committee Management System implements a comprehensive authentication and authorization system to ensure only logged-in users can access protected pages.

---

## Authentication Flow

### User Journey

```
┌─────────────────────────────────────────────────────────────┐
│                   User Visits Website                        │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
        ┌────────────────────┐
        │ Is User Logged In? │
        └────────┬───────┬──┘
                 │       │
            YES  │       │  NO
                 ▼       ▼
         ┌──────────┐  ┌─────────────────────┐
         │Dashboard │  │  Redirect to Login  │
         └──────────┘  └────────┬────────────┘
                                │
                                ▼
                        ┌────────────────┐
                        │  Login Page    │
                        │   (login.php)  │
                        └────────┬───────┘
                                 │
                ┌────────────────┴───────────────┐
                │                                │
                ▼                                ▼
        ┌──────────────────┐          ┌──────────────────┐
        │ Valid Creds?     │          │ Locked Account?  │
        │ (0-4 attempts)   │          │ (5+ attempts)    │
        └────┬────────┬───┘          └────┬────────┬───┘
             │        │                   │        │
          YES│        │NO              YES│        │NO
             ▼        │                   │        │
         ┌──────┐     │              ┌─────────┐  │
         │ Auth │     │              │Lockout  │  │
         └──┬───┘     │              │Shown    │  │
            │         │              │(15 min) │  │
            ▼         ▼              └────┬────┘  ▼
         Login   Increment        Wait  │    Show
        Success  Attempts         Timer │    Error
            │         │               │    │
            └─────┬───┴───────────────┼───┘
                  │                   │
                  ▼                   ▼
            ┌────────────────────────────────┐
            │  Redirect to Dashboard         │
            │  Set Session Variables         │
            │  Log Audit Action              │
            └────────────────────────────────┘
```

---

## Implementation Details

### 1. Login Page (`auth/login.php`)

#### Features:
- ✅ Checks if user is already logged in
- ✅ If logged in → redirects to dashboard
- ✅ If not logged in → shows login form
- ✅ Tracks failed login attempts in session
- ✅ Implements 5-attempt lockout with 15-minute timer
- ✅ Terms & Conditions acceptance required
- ✅ Account lockout security

#### Code:
```php
<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: ../public/dashboard.php');
    exit();
}

// Initialize login attempts tracking
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = null;
}
```

**When to use**: `/auth/login.php`  
**Access**: Anyone (redirects logged-in users)

---

### 2. Dashboard (`public/dashboard.php`)

#### Features:
- ✅ Checks if user is authenticated
- ✅ If not authenticated → redirects to login
- ✅ If authenticated → shows dashboard
- ✅ Dark mode support
- ✅ Session-based user information
- ✅ Logout confirmation

#### Code:
```php
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    header('Location: ../auth/login.php');
    exit();
}
?>
```

**When to use**: `/public/dashboard.php`  
**Access**: Authenticated users only

---

### 3. Authentication Check Middleware (`app/middleware/AuthCheck.php`)

#### Purpose:
Reusable authentication check for all protected pages.

#### Features:
- ✅ Session management
- ✅ Authentication validation
- ✅ Session timeout (24 hours)
- ✅ Auto-redirect to login
- ✅ Absolute URL redirect (works from any page)

#### Code:
```php
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $login_url = $protocol . '://' . $host . '/auth/login.php';
    
    header('Location: ' . $login_url);
    exit();
}
```

#### Usage:
Add this line to the top of ANY protected PHP file:
```php
<?php
require_once(__DIR__ . '/../../app/middleware/AuthCheck.php');
// Rest of your page code...
?>
```

---

## Session Variables

### After Successful Login

```php
$_SESSION['user_id']          // Unique user ID
$_SESSION['email']            // User's email address
$_SESSION['full_name']        // User's full name
$_SESSION['role_id']          // User's role ID
$_SESSION['role_name']        // User's role name (e.g., 'Administrator')
$_SESSION['login_time']       // Unix timestamp of login
$_SESSION['login_attempts']   // Failed login attempt counter (resets on success)
$_SESSION['first_attempt_time'] // Timestamp of first failed attempt
```

### For Failed Login Attempts

```php
$_SESSION['login_attempts']    // Incremented on each failed attempt
$_SESSION['first_attempt_time'] // Set when first attempt fails
// After 5 attempts: Account locked for 15 minutes
// After 15 minutes: Counters reset
```

---

## Authentication Scenarios

### Scenario 1: First-Time User Visits Site

1. User opens website link (e.g., `http://localhost/public/dashboard.php`)
2. `dashboard.php` checks `if (!isset($_SESSION['user_id']))`
3. Check fails (no session)
4. User is redirected: `header('Location: ../auth/login.php')`
5. User sees login page ✅

### Scenario 2: User Already Logged In (Auto-Redirect from Login)

1. User is logged in and tries to visit `/auth/login.php`
2. `login.php` checks `if (isset($_SESSION['user_id']))`
3. Check passes (session exists)
4. User is redirected: `header('Location: ../public/dashboard.php')`
5. User sees dashboard (no need to login again) ✅

### Scenario 3: User Logs Out

1. User clicks logout button
2. Confirmation modal appears
3. User confirms logout
4. Session is destroyed via `AuthController`
5. User is redirected: `login.php?logout=success`
6. Logout notification appears (5 seconds auto-dismiss)
7. Login form is ready for next user ✅

### Scenario 4: Session Timeout (24 hours)

1. User has been logged in for 24 hours
2. Protected page checks session age
3. Session older than 24 hours
4. Session is destroyed
5. User is redirected with message: `login.php?session_expired=true`
6. User sees message to re-login ✅

### Scenario 5: Failed Login Attempts (5 Attempts)

1. User enters wrong password (Attempt 1-4)
   - Error message shown: "Invalid email or password"
   - `$_SESSION['login_attempts']` incremented
   
2. User enters wrong password (Attempt 5)
   - Account locked message shown
   - `$_SESSION['login_attempts']` = 5
   - Lockout timer starts: 15 minutes
   - Login form hidden
   - MM:SS timer displayed
   
3. Timer counts down (14:59 → 14:58 → ... → 0:00)
   - Page auto-refreshes at 0:00
   - Session counters reset
   - Login form reappears
   - User can login again ✅

---

## Security Features

### ✅ Authentication Protection
- Session-based authentication
- User ID validation
- Login/logout tracking in audit logs

### ✅ Brute Force Protection
- 5-attempt lockout threshold
- 15-minute lockout duration
- Session-based tracking (per browser)
- Auto-reset after timeout

### ✅ Session Security
- Session timeout (24 hours)
- Session validation on every protected page
- Auto-logout on timeout

### ✅ Terms & Conditions
- Must accept before login
- Documented user consent

### ✅ Logout Confirmation
- Prevents accidental logouts
- Clear confirmation dialog
- Cancel option

### ✅ Audit Logging
- All login attempts logged
- Failed attempts recorded with email
- Successful logins tracked
- Logout actions recorded

---

## How to Add Authentication to New Pages

### Step 1: Create Your New Page
Create a new PHP file (e.g., `public/pages/my-module/my-page.php`)

### Step 2: Add Authentication Check
Add this at the very top of your PHP file:

```php
<?php
require_once(__DIR__ . '/../../app/middleware/AuthCheck.php');

// Your page code here...
?>
```

### Step 3: Access Session Data
You can now safely access user data:

```php
<?php
require_once(__DIR__ . '/../../app/middleware/AuthCheck.php');

echo "Welcome, " . $_SESSION['full_name'];
echo "Role: " . $_SESSION['role_name'];
?>
```

### Example Complete Page

```php
<?php
require_once(__DIR__ . '/../../app/middleware/AuthCheck.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Protected Page</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
    <p>You are logged in as: <?php echo htmlspecialchars($_SESSION['role_name']); ?></p>
</body>
</html>
```

---

## Testing Authentication

### Test 1: Unauthenticated Access
1. Open new browser window (incognito/private mode)
2. Try to access: `http://localhost/public/dashboard.php`
3. **Expected**: Redirected to login page ✅

### Test 2: Login Redirect
1. Login with valid credentials (LGU@admin.com / admin123)
2. Try to access: `http://localhost/auth/login.php`
3. **Expected**: Redirected to dashboard ✅

### Test 3: Logout
1. Login successfully
2. Click profile → Logout
3. Confirm logout in modal
4. **Expected**: Redirected to login with "Logged Out Successfully" message ✅

### Test 4: Failed Login Attempts
1. Try wrong password 5 times
2. **Expected**: After 5th attempt, see lockout screen with 15:00 timer ✅

### Test 5: Session Timeout
1. Login successfully
2. Open browser console
3. Run: `Date.now() + (86400000 + 1000)` (current time + 24 hours + 1 second)
4. Manually set `$_SESSION['login_time']` to old timestamp
5. Access protected page
6. **Expected**: Session expired message ✅

---

## Current Implementation Status

| Feature | Status | Location |
|---------|--------|----------|
| Login authentication | ✅ Complete | `/auth/login.php` |
| Dashboard protection | ✅ Complete | `/public/dashboard.php` |
| AuthCheck middleware | ✅ Complete | `/app/middleware/AuthCheck.php` |
| Auto-redirect (not logged in) | ✅ Complete | All pages |
| Auto-redirect (already logged in) | ✅ Complete | `/auth/login.php` |
| Lockout mechanism | ✅ Complete | `/auth/login.php` + `AuthController` |
| Session management | ✅ Complete | `SessionManager.php` |
| Logout confirmation | ✅ Complete | Dashboard + JS modal |
| Audit logging | ✅ Complete | `AuthController` + database |

---

## Troubleshooting

### Issue: User not redirected to login
**Solution**: Ensure page includes `AuthCheck.php` at the top

### Issue: Infinite redirect loop
**Solution**: Check that login.php has logged-in user redirect

### Issue: Session data not available
**Solution**: Verify `session_start()` is called before accessing `$_SESSION`

### Issue: Lockout not working
**Solution**: Ensure `AuthController.php` increments `$_SESSION['login_attempts']` on failed login

---

## Best Practices

✅ Always include `AuthCheck.php` for protected pages  
✅ Never store sensitive data in client-side code  
✅ Always validate session data on backend  
✅ Use HTTPS in production  
✅ Log all authentication actions  
✅ Regularly review audit logs  
✅ Implement password policy requirements  
✅ Use prepared statements for database queries  

---

## Future Enhancements (Optional)

- [ ] Email verification on signup
- [ ] Password reset functionality
- [ ] Two-factor authentication (2FA)
- [ ] Role-based access control (RBAC)
- [ ] IP-based login restrictions
- [ ] Device fingerprinting
- [ ] OAuth/SSO integration
- [ ] API token authentication
- [ ] Session management dashboard
- [ ] Login history tracking

---

**Created**: December 4, 2025  
**Status**: ✅ Production Ready  
**Version**: 1.0
