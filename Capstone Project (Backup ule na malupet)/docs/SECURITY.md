# Security Documentation

## Legislative Services Committee Management System

**Version:** 1.0  
**Last Updated:** January 2025

---

## Table of Contents

1. [Overview](#overview)
2. [Password Security](#password-security)
3. [Authentication System](#authentication-system)
4. [Email Verification](#email-verification)
5. [Password Reset](#password-reset)
6. [Session Management](#session-management)
7. [Access Control](#access-control)
8. [Data Encryption](#data-encryption)
9. [SQL Injection Prevention](#sql-injection-prevention)
10. [Cross-Site Scripting (XSS) Protection](#cross-site-scripting-xss-protection)
11. [CSRF Protection](#csrf-protection)
12. [Audit Logging](#audit-logging)
13. [Infrastructure Security](#infrastructure-security)
14. [Compliance](#compliance)

---

## Overview

The Legislative Services Committee Management System implements multiple layers of security to protect government data and ensure authorized access. All security measures follow industry best practices and government compliance standards.

### Security Principles

- **Defense in Depth:** Multiple security layers prevent unauthorized access
- **Least Privilege:** Users have only permissions necessary for their role
- **Confidentiality:** Sensitive data is encrypted and protected
- **Integrity:** Changes to data are logged and trackable
- **Availability:** System remains accessible to authorized users

---

## Password Security

### 1. Password Hashing

Passwords are never stored in plain text. Instead, they are hashed using **bcrypt** with the following specifications:

```
Algorithm: bcrypt (PASSWORD_BCRYPT)
Cost Parameter: 10
Output Length: 60 characters
Salt: Automatically generated for each password
```

**Why bcrypt?**
- Adaptive hashing algorithm that gets slower over time
- Built-in salt prevents rainbow table attacks
- Industry standard for government systems
- Resistant to GPU-based attacks

**Implementation:**
```php
// Password hashing during registration
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Password verification during login
password_verify($user_input_password, $stored_hash)
```

### 2. Password Requirements

All passwords must meet strict requirements:

- **Minimum 8 characters** - Increases entropy and attack time
- **At least one uppercase letter (A-Z)** - Increases character space
- **At least one lowercase letter (a-z)** - Increases character space
- **At least one number (0-9)** - Increases character space
- **At least one special character (!@#$%^&*)** - Increases character space

**Rationale:** These requirements ensure passwords have sufficient entropy (estimated minimum of 60 bits) to resist brute-force attacks.

### 3. Password Storage

- Passwords are hashed before storage
- Hashes are stored in the `users` table under `password_hash` column
- Original passwords are never logged or cached
- Password reset tokens are also hashed

---

## Authentication System

### 1. Email-Based Authentication

The system uses **email addresses as unique identifiers** instead of usernames:

**Advantages:**
- Reduces password reuse across systems
- Enables secure password reset via email
- Facilitates email verification workflows
- Industry standard for government systems
- Supports single sign-on (SSO) integration

**Implementation:**
```php
// Authentication query
SELECT u.user_id, u.email, u.password_hash, u.role_id, u.email_verified, u.is_active
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE u.email = ? AND u.is_active = TRUE AND u.email_verified = TRUE
```

### 2. Authentication Flow

```
1. User enters email and password
2. System validates email format
3. Database queries user by email
4. bcrypt verifies password against stored hash
5. System checks email_verified and is_active flags
6. Session variables set upon successful authentication
7. Last login timestamp updated
8. Login action logged to audit logs
```

### 3. Failed Login Handling

- Failed login attempts are logged with timestamp and IP address
- Account is NOT locked after failed attempts (DoS prevention)
- Failed login events are available for administrator review
- Rate limiting recommended at firewall/reverse proxy level

---

## Email Verification

### 1. Verification Process

During registration, users must verify their email address:

```
1. User submits registration form
2. System generates cryptographically secure verification token
3. Token stored in database: verification_token, verification_token_expires
4. Verification email sent to user's email address
5. User clicks verification link with token
6. System validates token and timestamp
7. Upon success: email_verified set to TRUE, token cleared
8. User account becomes active after admin approval
```

### 2. Verification Token Security

- **Generation:** `bin2hex(random_bytes(32))` = 64-character hex string
- **Expiration:** 24 hours from generation
- **One-time use:** Token deleted after verification
- **Database:** Stored in `verification_token` column
- **Transmission:** Sent via email (not SMS or other channels)

**Token Properties:**
- 256-bit random entropy (32 bytes)
- Hex-encoded for email transmission
- Not reusable after verification
- Expires automatically

### 3. Verification Email Security

- Links include secure token
- Email contains no sensitive information
- Links expire after 24 hours
- Server validates token before accepting
- Resend verification option available

---

## Password Reset

### 1. Password Reset Flow

```
1. User navigates to password reset page
2. User enters registered email address
3. System checks if email exists
4. If exists: generates reset token, sends email (no indication of user existence)
5. User clicks reset link in email
6. System validates token and timestamp
7. User enters new password (must meet requirements)
8. Upon success: password_hash updated, reset_token cleared
9. User receives confirmation email
10. Session terminated (user must re-login)
```

### 2. Reset Token Security

- **Generation:** `bin2hex(random_bytes(32))` = 64-character hex string
- **Expiration:** 1 hour from generation
- **One-time use:** Token deleted after password reset
- **Storage:** `password_reset_token` and `password_reset_expires` columns
- **Validation:** Both token and timestamp checked

### 3. Security Considerations

- **No user enumeration:** System sends same response regardless of email existence
- **Time-limited tokens:** Reset tokens expire after 1 hour
- **Single use:** Tokens cannot be reused
- **Email notification:** User receives confirmation of password change
- **Session logout:** User must re-authenticate with new password

---

## Session Management

### 1. Session Configuration

```php
// Session variables upon successful login
$_SESSION['user_id']   = integer unique identifier
$_SESSION['email']     = user's email address
$_SESSION['full_name'] = concatenated first and last name
$_SESSION['role_id']   = integer role identifier
$_SESSION['role_name'] = string role name
$_SESSION['login_time'] = unix timestamp of login
```

### 2. Session Security

- **Session timeout:** Recommended 30 minutes of inactivity
- **HTTPS only:** Cookies transmitted over encrypted connection
- **HttpOnly flag:** Session cookies not accessible via JavaScript
- **Secure flag:** Cookies only sent over HTTPS
- **SameSite attribute:** Prevents CSRF cookie submission

### 3. Session Validation

```php
// Check if session is valid
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Session is valid
}

// Logout (session destruction)
session_destroy();
unset($_SESSION);
```

### 4. Session Hijacking Prevention

- Session IDs are cryptographically strong (PHP default)
- Session regeneration recommended on privilege escalation
- IP address binding optional for additional security
- User-agent binding optional for additional security

---

## Access Control

### 1. Role-Based Access Control (RBAC)

The system implements role-based permissions:

| Role ID | Role Name | Purpose | Permissions |
|---------|-----------|---------|-------------|
| 0 | Super Administrator | Multi-LGU central authority | All permissions including user_approval, role_management |
| 1 | Administrator | LGU system administration | Full system access, user management, settings |
| 2 | Committee Chair | Committee leadership | Committee management, agenda control, decision recording |
| 3 | Committee Member | Committee participation | Attend meetings, view documents, provide input |
| 4 | Staff | Administrative support | Data entry, document management, basic operations |
| 5 | Email User | Pending approval | Limited access, waiting for admin activation |

### 2. Permission Storage

Permissions are stored as JSON in the `roles` table:

```json
{
  "dashboard_view": true,
  "committee_view": true,
  "committee_create": true,
  "committee_edit": true,
  "committee_delete": false,
  "member_view": true,
  "member_create": false,
  "document_view": true,
  "document_upload": true,
  "user_management": false,
  "user_approval": false,
  "role_management": false,
  "audit_logs_view": false,
  "system_settings": false
}
```

### 3. Permission Checking

```php
// Check single permission
if ($sessionManager->hasPermission('committee_create')) {
    // User has permission
}

// Check user role
if ($sessionManager->hasRole('Administrator')) {
    // User is administrator
}

// Check if super admin
if ($_SESSION['role_id'] === 0) {
    // User is super administrator
}
```

### 4. Principle of Least Privilege

- Users have only permissions required for their role
- Default deny: permissions not explicitly granted are denied
- Admin roles require explicit activation
- Super admin role restricted to central authority

---

## Data Encryption

### 1. Encryption in Transit

**HTTPS/TLS Protocol:**
- All data transmitted via encrypted HTTPS connection
- Minimum TLS 1.2 required
- Strong cipher suites only (AES-256-GCM preferred)
- Perfect Forward Secrecy (PFS) recommended

**Implementation:**
```
- Server certificate: Signed by trusted Certificate Authority
- Certificate chain: Complete and valid
- HSTS header: Enforces HTTPS for all future requests
- Mixed content prevention: No HTTP resources loaded from HTTPS page
```

### 2. Encryption at Rest

**Database Encryption:**
- MySQL transparent data encryption (TDE) recommended
- Entire database encrypted with hardware security module (HSM)
- Encryption key managed separately from database

**Backup Encryption:**
- All backups encrypted with AES-256
- Encryption keys stored separately
- Backup storage access restricted

### 3. Password Field Encryption

- Passwords hashed with bcrypt (not reversible encryption)
- Salt included in bcrypt hash
- Hash verified using password_verify()

---

## SQL Injection Prevention

### 1. Prepared Statements

**All database queries use prepared statements:**

```php
// Prepared statement with parameterized query
$query = "SELECT * FROM users WHERE email = ? AND is_active = TRUE";
$stmt = $conn->prepare($query);

// Bind parameters with type specification
$stmt->bind_param("s", $email);  // "s" = string type

// Execute with parameter substituted safely
$stmt->execute();
```

**Benefits:**
- User input never concatenated into SQL
- Database driver handles escaping
- Impossible for user input to change query structure
- Provides strongest protection against SQL injection

### 2. Query Parameterization Example

```php
// UNSAFE - DO NOT USE
$query = "SELECT * FROM users WHERE email = '" . $email . "'";  // VULNERABLE

// SAFE - USE THIS
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 3. Type Binding

All parameters have explicit types:
- `s` = string
- `i` = integer
- `d` = double/float
- `b` = blob/binary

---

## Cross-Site Scripting (XSS) Protection

### 1. Output Encoding

All user-provided data is encoded before output:

```php
// HTML encode all user input
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// Example
$email = htmlspecialchars(trim($email));
$first_name = htmlspecialchars(trim($first_name));
```

**htmlspecialchars() converts:**
- `&` → `&amp;`
- `"` → `&quot;`
- `'` → `&#039;`
- `<` → `&lt;`
- `>` → `&gt;`

### 2. Content Security Policy (CSP)

Recommended CSP header:
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;
```

### 3. Input Validation

- Validate input type and format
- Whitelist allowed characters
- Reject suspicious patterns
- Example: Email validation with `filter_var()`

```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Invalid email format
}
```

---

## CSRF Protection

### 1. CSRF Token Implementation

**Recommended approach:**
- Generate unique token for each form
- Store token in session
- Require token in POST requests
- Validate token before processing

```php
// Generate token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include in form
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validate on POST
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

### 2. SameSite Cookie Attribute

Modern CSRF protection via cookie attributes:
```
Set-Cookie: PHPSESSID=...; SameSite=Strict
```

Options:
- `Strict`: Cookie not sent with cross-site requests
- `Lax`: Cookie sent with top-level navigation
- `None`: Cookie sent with all cross-site requests (requires Secure flag)

---

## Audit Logging

### 1. Audit Log Structure

All user actions are logged to `audit_logs` table:

```
user_id: User performing action
action: Action type (LOGIN, LOGOUT, CREATE, UPDATE, DELETE, etc.)
module: System module affected (Authentication, Committees, Users, etc.)
description: Human-readable action description
ip_address: IP address of user
timestamp: Automatic timestamp of action
```

### 2. Logged Events

**Authentication Events:**
- User login (successful and failed)
- User logout
- Password change
- Password reset request
- Email verification

**Data Events:**
- Record creation
- Record modification
- Record deletion
- Document upload
- Export/download

**System Events:**
- Role changes
- Permission modifications
- Settings changes
- System configuration updates

### 3. Audit Log Access

- Only Super Administrators and Administrators can view audit logs
- Full audit trail accessible via database queries
- Audit logs cannot be deleted by regular users
- Retention: Minimum 7 years per government regulations

### 4. Audit Log Entry Example

```php
// Log function
$this->logAuditAction(
    $user_id,        // Who performed the action
    'CREATE',        // What action
    'Committees',    // Which module
    'Created committee: Budget Committee',  // Details
    $_SERVER['REMOTE_ADDR']  // From where
);
```

---

## Infrastructure Security

### 1. Server Configuration

**Recommended Apache Settings:**
```apache
# Disable directory listing
Options -Indexes

# Enable mod_rewrite for clean URLs
<IfModule mod_rewrite.c>
    RewriteEngine On
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.env|\.git|composer.json">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

### 2. PHP Configuration

**Recommended Settings:**
```ini
# Disable dangerous functions
disable_functions = exec, shell_exec, system, passthru, proc_open, popen

# Error handling
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

# Session security
session.secure = 1
session.httponly = 1
session.samesite = Strict

# Input limits
upload_max_filesize = 10M
post_max_size = 10M
max_input_vars = 1000
```

### 3. Database Server Configuration

**MySQL Security:**
```sql
-- Remove test databases
DROP DATABASE test;

-- Disable remote root login
DELETE FROM mysql.user WHERE user='root' AND host='%';

-- Create application user with limited privileges
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON legislative_db.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;

-- Enable SSL connections
REQUIRE SSL;
```

### 4. Firewall Rules

```
Port 80:   HTTP redirect to HTTPS (optional)
Port 443:  HTTPS (main access point)
Port 3306: MySQL (local only, not exposed externally)
Other:     All other ports DENY
```

---

## Compliance

### 1. Data Protection Laws

The system complies with:
- **Data Privacy Act (Philippines):** Personal data protection requirements
- **Government Information Management Standards:** Government data handling
- **Local Records Retention Laws:** Document retention requirements

### 2. Audit Trail Requirements

- All user actions logged
- Logs retained for 7 years minimum
- Unauthorized access attempts recorded
- Administrative actions tracked
- Data modification history available

### 3. Access Control Compliance

- Role-based access control enforced
- Principle of least privilege applied
- Super admin approval required for admin accounts
- User roles and permissions documented
- Regular access reviews recommended

### 4. Security Testing

Recommended annual activities:
- Penetration testing
- Vulnerability scanning
- Security code review
- Backup restoration testing
- Disaster recovery testing
- Social engineering assessments

---

## Security Best Practices for Users

### For All Users:

1. **Never share your password** - Keep credentials confidential
2. **Use strong passwords** - Follow password requirements
3. **Log out when finished** - Close sessions when not in use
4. **Report suspicious activity** - Notify administrators of concerns
5. **Keep software updated** - Use current browser versions
6. **Use secure networks** - Avoid public Wi-Fi for sensitive operations
7. **Verify URLs** - Ensure you're accessing the correct system

### For Administrators:

1. **Review audit logs regularly** - Monitor for unauthorized activity
2. **Approve registrations carefully** - Verify employee information
3. **Disable unused accounts** - Remove access for departed employees
4. **Rotate credentials** - Change default passwords immediately
5. **Test backups** - Verify recovery procedures work
6. **Monitor system logs** - Watch for errors or attacks
7. **Apply security updates** - Keep system software current

---

## Security Incident Response

### Incident Types:

1. **Unauthorized Access:** Account compromised or access gained without authorization
2. **Data Breach:** Confidential information accessed or disclosed
3. **Denial of Service:** System unavailable due to attack
4. **Malware:** Virus or malicious code detected
5. **Policy Violation:** User violating acceptable use policy

### Response Procedures:

```
1. Immediate: Isolate affected systems, notify administrators
2. Investigation: Gather evidence, review logs, determine scope
3. Containment: Disable affected accounts, apply patches, block attackers
4. Communication: Notify affected users, inform management
5. Recovery: Restore from backups, verify system integrity
6. Review: Post-incident analysis, update security measures
```

---

## Contact & Support

For security concerns or to report vulnerabilities:
- **Email:** security@lgu.gov
- **Phone:** [LGU Main Line]
- **In-Person:** Administrative Office, Government Building

**Security Response Time:**
- Critical: 24 hours
- High: 48 hours
- Medium: 1 week
- Low: 2 weeks

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | January 2025 | Initial security documentation |

---

**Document Classification:** Government Use Only  
**Last Updated:** January 2025  
**Next Review:** January 2026
