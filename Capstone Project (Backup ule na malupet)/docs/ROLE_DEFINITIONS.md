# Role Definitions & Visibility

This document outlines the standard roles and what each user type can see and do within the Legislative Services Committee Management System.

---

## 1. Super Admin
**Scope:** Full System Access
- **Visibility:** Can see all modules, all committees, all reports, and the audit logs for every user.
- **Key Capabilities:**
  - Manage all users (including Admins).
  - Modify system settings and database configurations.
  - Access the "Super Admin Panel" for advanced diagnostics.
  - Approve new user registrations.

## 2. Admin
**Scope:** Full LGU/Operational Access
- **Visibility:** Can see all committees, meetings, and documents.
- **Key Capabilities:**
  - Create and manage Users (except Super Admins).
  - Manage Committee assignments.
  - Schedule any meeting across all committees.
  - Track progress on all Referrals and Action Items.
  - Access Audit Logs for general users.

## 3. Committee Chairman
**Scope:** Specific Committee Management
- **Visibility:** Full visibility into their assigned committee(s). Limited visibility into others (read-only for public documents).
- **Key Capabilities:**
  - Schedule and manage meetings for their assigned committee.
  - Build and approve meeting agendas.
  - Create and edit Committee Reports.
  - Assign Tasks and Action Items to committee members.
  - View member attendance records for their meetings.

## 4. Vice Committee Chairman
**Scope:** Deputy Committee Management
- **Visibility:** Same as Committee Chairman for their assigned committee(s).
- **Key Capabilities:**
  - Assist the Chairman in managing committee meetings and agendas.
  - Act as Chairman in their absence (if implemented in logic).
  - Create and edit Committee Reports and documents.
  - Assigned Tasks and Action Items.

## 5. User (Committee Member)
**Scope:** Participation & Viewing
- **Visibility:** Access to their assigned committees and public legislative documents.
- **Key Capabilities:**
  - View their personal task list and dashboard.
  - Receive notifications for upcoming meetings.
  - View published agendas and minutes.
  - Participate in voting during active meetings.
  - Update their own profile information.

---

## Summary Table

| Module | Super Admin | Admin | Committee Chairman | Vice Chairman | User |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **User Management** | Full | Full (except Super) | None | None | None |
| **System Settings** | Full | Full | None | None | None |
| **Committees** | Manage All | Manage All | Manage Assigned | Manage Assigned | View Assigned |
| **Meetings** | Full | Full | Create (Assigned) | Create (Assigned) | View/Participate |
| **Documents** | Full | Full | Create (Committee) | Create (Committee) | View |
| **Audit Logs** | Global | Limited | None | None | Own Actions Only |
| **Referrals** | Global | Global | Committee Level | Committee Level | View |
