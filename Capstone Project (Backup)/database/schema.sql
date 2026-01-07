-- ============================================================================
-- Legislative Services Committee Management System - Database Schema
-- ============================================================================
-- Created: November 24, 2025
-- Database: legislative_cms
-- Version: 1.0
-- 
-- This schema includes all tables needed for the committee management system.
-- Import this file into your MySQL database using phpMyAdmin or mysql CLI.
-- ============================================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `legislative_cms`;
USE `legislative_cms`;

-- ============================================================================
-- 1. ROLES TABLE - Role definitions with permissions (created first)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `permissions` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. USERS TABLE - Core user management (Email-based authentication)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role_id` INT NOT NULL,
  `department` VARCHAR(100),
  `position` VARCHAR(100),
  `phone` VARCHAR(20),
  `address` TEXT,
  `employee_id` VARCHAR(50),
  `is_active` BOOLEAN DEFAULT FALSE,
  `email_verified` BOOLEAN DEFAULT FALSE,
  `verification_token` VARCHAR(255),
  `verification_token_expires` DATETIME,
  `password_reset_token` VARCHAR(255),
  `password_reset_expires` DATETIME,
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. AUDIT LOGS TABLE - Track user actions
-- ============================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(45),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. COMMITTEES TABLE - Committee profiles and metadata
-- ============================================================================
CREATE TABLE IF NOT EXISTS `committees` (
  `committee_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_name` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT,
  `mandate` TEXT,
  `functions` TEXT,
  `chairperson_id` INT,
  `vice_chair_id` INT,
  `secretary_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`chairperson_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`vice_chair_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`secretary_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. COMMITTEE MEMBERS TABLE - Many-to-many relationship
-- ============================================================================
CREATE TABLE IF NOT EXISTS `committee_members` (
  `member_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `position` VARCHAR(100),
  `join_date` DATE,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_committee_user` (`committee_id`, `user_id`),
  FOREIGN KEY (`committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. MEETINGS TABLE - Session and meeting scheduling
-- ============================================================================
CREATE TABLE IF NOT EXISTS `meetings` (
  `meeting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_id` INT NOT NULL,
  `meeting_title` VARCHAR(200) NOT NULL,
  `meeting_date` DATETIME NOT NULL,
  `location` VARCHAR(200),
  `agenda` TEXT,
  `status` ENUM('scheduled', 'ongoing', 'completed', 'cancelled', 'postponed') DEFAULT 'scheduled',
  `notes` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. MEETING INVITATIONS TABLE - Track attendees
-- ============================================================================
CREATE TABLE IF NOT EXISTS `meeting_invitations` (
  `invitation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `status` ENUM('pending', 'accepted', 'declined', 'no_response') DEFAULT 'pending',
  `response_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_meeting_user` (`meeting_id`, `user_id`),
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. MEETING DOCUMENTS TABLE - Attendance, minutes, resolutions
-- ============================================================================
CREATE TABLE IF NOT EXISTS `meeting_documents` (
  `document_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT NOT NULL,
  `document_type` ENUM('agenda', 'minutes', 'resolution', 'recommendation', 'supporting_doc') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `content` LONGTEXT,
  `file_path` VARCHAR(255),
  `file_size` INT,
  `mime_type` VARCHAR(50),
  `uploaded_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. ATTENDANCE RECORDS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `status` ENUM('present', 'absent', 'excused') DEFAULT 'absent',
  `check_in_time` DATETIME,
  `check_out_time` DATETIME,
  `recorded_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_attendance` (`meeting_id`, `user_id`),
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. LEGISLATIVE DOCUMENTS TABLE - Ordinances, resolutions, reports
-- ============================================================================
CREATE TABLE IF NOT EXISTS `legislative_documents` (
  `document_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_number` VARCHAR(50) NOT NULL UNIQUE,
  `document_type` ENUM('ordinance', 'resolution', 'committee_report', 'endorsement') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `content` LONGTEXT,
  `status` ENUM('draft', 'in_committee', 'under_review', 'approved', 'finalized', 'rejected') DEFAULT 'draft',
  `assigned_committee_id` INT,
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. DOCUMENT VERSIONS TABLE - Version control
-- ============================================================================
CREATE TABLE IF NOT EXISTS `document_versions` (
  `version_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `version_number` INT NOT NULL,
  `content` LONGTEXT,
  `changes_made` TEXT,
  `editor_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`) ON DELETE CASCADE,
  FOREIGN KEY (`editor_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. REFERRALS TABLE - Incoming/outgoing referrals
-- ============================================================================
CREATE TABLE IF NOT EXISTS `referrals` (
  `referral_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `referral_type` ENUM('incoming', 'outgoing') NOT NULL,
  `from_committee_id` INT,
  `to_committee_id` INT,
  `assigned_date` DATETIME NOT NULL,
  `deadline_date` DATE,
  `status` ENUM('pending', 'acknowledged', 'in_progress', 'completed', 'returned') DEFAULT 'pending',
  `notes` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`),
  FOREIGN KEY (`from_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`to_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. ENDORSEMENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `endorsements` (
  `endorsement_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `endorsement_type` ENUM('committee_to_committee', 'to_higher_level') NOT NULL,
  `from_committee_id` INT NOT NULL,
  `to_committee_id` INT,
  `to_level` VARCHAR(100),
  `endorsement_date` DATETIME NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `notes` TEXT,
  `attachments` VARCHAR(255),
  `endorsed_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`),
  FOREIGN KEY (`from_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`to_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`endorsed_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. CALENDAR EVENTS TABLE - For calendar dashboard
-- ============================================================================
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_title` VARCHAR(200) NOT NULL,
  `event_type` ENUM('meeting', 'deadline', 'hearing', 'reminder') NOT NULL,
  `event_date` DATETIME NOT NULL,
  `description` TEXT,
  `related_committee_id` INT,
  `related_document_id` INT,
  `visibility` ENUM('internal', 'public') DEFAULT 'internal',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`related_committee_id`) REFERENCES `committees`(`committee_id`),
  FOREIGN KEY (`related_document_id`) REFERENCES `legislative_documents`(`document_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. NOTIFICATIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `notification_type` ENUM('reminder', 'alert', 'info', 'task_assigned') NOT NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `action_link` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. TASKS TABLE - Action item tracking
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `task_type` ENUM('assigned_task', 'action_item', 'follow_up') NOT NULL,
  `assigned_to_id` INT NOT NULL,
  `related_meeting_id` INT,
  `related_document_id` INT,
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `status` ENUM('pending', 'in_progress', 'completed', 'on_hold', 'cancelled') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_to_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`related_meeting_id`) REFERENCES `meetings`(`meeting_id`),
  FOREIGN KEY (`related_document_id`) REFERENCES `legislative_documents`(`document_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. SYSTEM SETTINGS TABLE - Configuration and customization
-- ============================================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `lgu_name` VARCHAR(150),
  `lgu_address` TEXT,
  `lgu_contact` VARCHAR(20),
  `lgu_email` VARCHAR(100),
  `lgu_logo_path` VARCHAR(255),
  `theme_color` VARCHAR(20) DEFAULT '#007bff',
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `auto_backup_enabled` BOOLEAN DEFAULT TRUE,
  `backup_frequency` ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. BACKUP LOGS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `backup_logs` (
  `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_date` DATETIME NOT NULL,
  `backup_size` BIGINT,
  `backup_path` VARCHAR(255),
  `backup_type` ENUM('manual', 'automatic') DEFAULT 'automatic',
  `status` ENUM('success', 'failed') DEFAULT 'success',
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. ERROR LOGS TABLE - System error logging
-- ============================================================================
CREATE TABLE IF NOT EXISTS `error_logs` (
  `error_id` INT AUTO_INCREMENT PRIMARY KEY,
  `error_type` VARCHAR(100),
  `error_message` LONGTEXT,
  `error_file` VARCHAR(255),
  `error_line` INT,
  `user_id` INT,
  `ip_address` VARCHAR(45),
  `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. PUBLIC DOCUMENTS VIEW TABLE - For transparency portal
-- ============================================================================
CREATE TABLE IF NOT EXISTS `public_documents` (
  `public_doc_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `is_published` BOOLEAN DEFAULT FALSE,
  `published_date` DATETIME,
  `published_by` INT,
  `visibility_expiry` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_published_doc` (`document_id`),
  FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`) ON DELETE CASCADE,
  FOREIGN KEY (`published_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT ROLES
-- ============================================================================
INSERT INTO `roles` (`role_name`, `description`, `permissions`) VALUES
('Super Administrator', 'Complete system access - can manage all users and settings', JSON_OBJECT('all_modules', true, 'user_management', true, 'system_settings', true, 'user_approval', true, 'role_management', true, 'super_admin_panel', true)),
('Administrator', 'Full system access for LGU admins', JSON_OBJECT('all_modules', true, 'user_management', true, 'system_settings', true, 'user_approval', true)),
('Committee Chair', 'Can manage committee and create documents', JSON_OBJECT('committee_management', true, 'document_creation', true, 'meeting_scheduling', true)),
('Committee Secretary', 'Can record minutes, attendance, and manage meetings', JSON_OBJECT('meeting_management', true, 'document_upload', true, 'attendance_recording', true)),
('Staff/Encoder', 'Can encode and track documents', JSON_OBJECT('document_encoding', true, 'document_tracking', true)),
('Public Viewer', 'Can view public documents and information', JSON_OBJECT('view_public_documents', true, 'view_calendar', true, 'view_ordinances', true));

-- ============================================================================
-- INSERT DEFAULT ADMIN USERS
-- ============================================================================
-- Super Admin (Central Authority - for multi-LGU integration)
-- Password: admin123
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `role_id`, `department`, `position`, `is_active`, `email_verified`) VALUES
('super.admin@legislative-services.gov', '$2y$10$ywS1emacPWJslIQxDSbwnOCY/5KXEqmbcqqqTA5VJABnsxturerL.', 'Super', 'Administrator', 1, 'National', 'Central Authority', TRUE, TRUE);

-- LGU Admin (Local Government Unit Admin)
-- Password: admin123
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `role_id`, `department`, `position`, `is_active`, `email_verified`) VALUES
('LGU@admin.com', '$2y$10$ywS1emacPWJslIQxDSbwnOCY/5KXEqmbcqqqTA5VJABnsxturerL.', 'LGU', 'Administrator', 2, 'Administrative Services', 'LGU Administrator', TRUE, TRUE);

-- ============================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================================================
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_committees_chairperson ON committees(chairperson_id);
CREATE INDEX idx_committee_members_committee ON committee_members(committee_id);
CREATE INDEX idx_committee_members_user ON committee_members(user_id);
CREATE INDEX idx_meetings_committee ON meetings(committee_id);
CREATE INDEX idx_meetings_status ON meetings(status);
CREATE INDEX idx_meeting_invitations_meeting ON meeting_invitations(meeting_id);
CREATE INDEX idx_meeting_invitations_user ON meeting_invitations(user_id);
CREATE INDEX idx_meeting_documents_meeting ON meeting_documents(meeting_id);
CREATE INDEX idx_legislative_documents_status ON legislative_documents(status);
CREATE INDEX idx_legislative_documents_committee ON legislative_documents(assigned_committee_id);
CREATE INDEX idx_referrals_document ON referrals(document_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_timestamp ON audit_logs(timestamp);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);

-- ============================================================================
-- DATABASE SETUP COMPLETE
-- ============================================================================
-- Next Steps:
-- 1. Update database credentials in config/database.php
-- 2. Run this schema using phpMyAdmin or MySQL CLI
-- 3. Change default admin password in production
-- 4. Configure email and notification settings
-- ============================================================================
