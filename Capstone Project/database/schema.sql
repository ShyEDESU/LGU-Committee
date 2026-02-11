-- ============================================================================
-- Legislative Services Committee Management System - Optimized Database Schema
-- ============================================================================
-- Created: January 16, 2026
-- Database: legislative_cms
-- Version: 2.0 (Optimized)
-- 
-- This is a clean, optimized schema ready for phpMyAdmin import.
-- Unused columns and tables have been removed.
-- New columns needed for the system have been added.
-- ============================================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `legislative_cms`;
USE `legislative_cms`;

-- ============================================================================
-- 1. ROLES TABLE - Role definitions with permissions
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
-- 2. USERS TABLE - Core user management
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role_id` INT NOT NULL,
  `notification_preferences` JSON DEFAULT NULL,
  `profile_picture` VARCHAR(255),
  `phone` VARCHAR(20),
  `department` VARCHAR(100),
  `position` VARCHAR(100),
  `bio` TEXT,
  `address` TEXT,
  `is_active` BOOLEAN DEFAULT FALSE,
  `email_verified` BOOLEAN DEFAULT FALSE,
  `verification_token` VARCHAR(255),
  `verification_token_expires` DATETIME,
  `password_reset_token` VARCHAR(255),
  `password_reset_expires` DATETIME,
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`),
  INDEX idx_users_role (role_id),
  INDEX idx_users_active (is_active),
  INDEX idx_users_email (email)
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
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX idx_audit_user (user_id),
  INDEX idx_audit_timestamp (timestamp),
  INDEX idx_audit_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. COMMITTEES TABLE - Committee profiles and metadata
-- ============================================================================
CREATE TABLE IF NOT EXISTS `committees` (
  `committee_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_name` VARCHAR(150) NOT NULL UNIQUE,
  `committee_type` ENUM('Standing', 'Special', 'Ad Hoc') DEFAULT 'Standing',
  `description` TEXT,
  `jurisdiction` TEXT COMMENT 'Areas of responsibility',
  `chairperson_id` INT,
  `vice_chair_id` INT,
  `secretary_id` INT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`chairperson_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`vice_chair_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`secretary_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  INDEX idx_committees_active (is_active),
  INDEX idx_committees_type (committee_type),
  INDEX idx_committees_chair (chairperson_id)
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
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX idx_committee_members_committee (committee_id),
  INDEX idx_committee_members_user (user_id),
  INDEX idx_committee_members_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. MEETINGS TABLE - Session and meeting scheduling
-- ============================================================================
CREATE TABLE IF NOT EXISTS `meetings` (
  `meeting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_id` INT NOT NULL,
  `meeting_title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `meeting_date` DATETIME NOT NULL,
  `meeting_end_time` DATETIME,
  `location` VARCHAR(200),
  `status` ENUM('Scheduled', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
  `agenda_status` ENUM('None', 'Draft', 'Under Review', 'Approved', 'Published') DEFAULT 'None',
  `is_public` BOOLEAN DEFAULT TRUE,
  `notes` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`),
  INDEX idx_meetings_committee (committee_id),
  INDEX idx_meetings_status (status),
  INDEX idx_meetings_date (meeting_date),
  INDEX idx_meetings_public (is_public),
  INDEX idx_meetings_agenda_status (agenda_status)
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
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX idx_meeting_invitations_meeting (meeting_id),
  INDEX idx_meeting_invitations_user (user_id)
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
  `uploaded_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`user_id`),
  INDEX idx_meeting_documents_meeting (meeting_id),
  INDEX idx_meeting_documents_type (document_type)
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
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`user_id`),
  INDEX idx_attendance_meeting (meeting_id),
  INDEX idx_attendance_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. AGENDA ITEMS TABLE - Individual agenda items for meetings
-- ============================================================================
CREATE TABLE IF NOT EXISTS `agenda_items` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT NOT NULL,
  `item_order` INT NOT NULL COMMENT 'Order of item in agenda',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `presenter` VARCHAR(100) COMMENT 'Person presenting this item',
  `duration` INT COMMENT 'Duration in minutes',
  `item_type` ENUM('Procedural', 'Presentation', 'Discussion', 'Voting', 'Report', 'Public Input', 'Other') NOT NULL,
  `referral_id` INT COMMENT 'Related referral if applicable',
  `notes` TEXT,
  `status` ENUM('Pending', 'In Progress', 'Completed', 'Deferred') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  INDEX idx_agenda_meeting (meeting_id),
  INDEX idx_agenda_order (meeting_id, item_order),
  INDEX idx_agenda_type (item_type),
  INDEX idx_agenda_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. LEGISLATIVE DOCUMENTS TABLE - Ordinances, resolutions, reports
-- ============================================================================
CREATE TABLE IF NOT EXISTS `legislative_documents` (
  `document_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_number` VARCHAR(50) NOT NULL UNIQUE,
  `document_type` ENUM('ordinance', 'resolution', 'committee_report', 'endorsement') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `content` LONGTEXT,
  `status` ENUM('Draft', 'Under Review', 'Approved', 'Rejected') DEFAULT 'Draft',
  `assigned_committee_id` INT,
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `is_public` BOOLEAN DEFAULT TRUE,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`),
  INDEX idx_legislative_documents_status (status),
  INDEX idx_legislative_documents_committee (assigned_committee_id),
  INDEX idx_legislative_documents_priority (priority),
  INDEX idx_legislative_documents_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. REFERRALS TABLE - Incoming/outgoing referrals
-- ============================================================================
CREATE TABLE IF NOT EXISTS `referrals` (
  `referral_id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `referral_type` ENUM('incoming', 'outgoing') NOT NULL,
  `to_committee_id` INT,
  `assigned_to_user_id` INT COMMENT 'User assigned to handle this referral',
  `assigned_date` DATETIME NOT NULL,
  `deadline_date` DATE,
  `status` ENUM('Pending', 'Under Review', 'In Committee', 'Approved', 'Rejected', 'Deferred') DEFAULT 'Pending',
  `notes` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `legislative_documents`(`document_id`) ON DELETE CASCADE,
  FOREIGN KEY (`to_committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`),
  INDEX idx_referrals_document (document_id),
  INDEX idx_referrals_to_committee (to_committee_id),
  INDEX idx_referrals_assigned_user (assigned_to_user_id),
  INDEX idx_referrals_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. TASKS TABLE - Action item tracking
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_id` INT COMMENT 'Related committee',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `assigned_to` INT,
  `due_date` DATE NOT NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
  `status` ENUM('Pending', 'To Do', 'In Progress', 'Done', 'On Hold', 'Cancelled') DEFAULT 'Pending',
  `progress` INT DEFAULT 0 COMMENT 'Progress percentage (0-100)',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` DATETIME,
  FOREIGN KEY (`committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`),
  INDEX idx_tasks_assigned_to (assigned_to),
  INDEX idx_tasks_status (status),
  INDEX idx_tasks_due_date (due_date),
  INDEX idx_tasks_committee (committee_id),
  CONSTRAINT chk_tasks_progress CHECK (progress >= 0 AND progress <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. NOTIFICATIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `notification_type` ENUM('reminder', 'alert', 'info', 'task_assigned', 'referral_assigned', 'committee_created', 'meeting', 'action_item', 'referral', 'document', 'deadline', 'system', 'comment') NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `is_read` BOOLEAN DEFAULT FALSE,
  `action_link` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX idx_notifications_user (user_id),
  INDEX idx_notifications_read (is_read),
  INDEX idx_notifications_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. SYSTEM SETTINGS TABLE - Configuration and customization
-- ============================================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `lgu_name` VARCHAR(150),
  `lgu_address` TEXT,
  `lgu_contact` VARCHAR(20),
  `lgu_email` VARCHAR(100),
  `lgu_logo_path` VARCHAR(255),
  `theme_color` VARCHAR(20) DEFAULT '#dc2626',
  `timezone` VARCHAR(50) DEFAULT 'Asia/Manila',
  `auto_backup_enabled` BOOLEAN DEFAULT TRUE,
  `backup_frequency` ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
  `maintenance_mode` TINYINT(1) DEFAULT 0,
  `session_timeout` INT DEFAULT 30,
  `min_password_length` INT DEFAULT 8,
  `require_special_chars` TINYINT(1) DEFAULT 0,
  `smtp_host` VARCHAR(255) NULL,
  `smtp_port` INT NULL,
  `smtp_user` VARCHAR(255) NULL,
  `smtp_pass` VARCHAR(255) NULL,
  `smtp_encryption` VARCHAR(10) DEFAULT 'tls',
  `log_retention_days` INT DEFAULT 90,
  `system_title` VARCHAR(255) DEFAULT 'CMS - Committee Management System',
  `system_acronym` VARCHAR(20) DEFAULT 'CMS',
  `default_language` VARCHAR(10) DEFAULT 'en',
  `date_format` VARCHAR(50) DEFAULT 'M j, Y',
  `time_format` VARCHAR(50) DEFAULT 'H:i',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. BACKUP LOGS TABLE
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
-- 17. ERROR LOGS TABLE - System error logging
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
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  INDEX idx_error_logs_severity (severity),
  INDEX idx_error_logs_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT ROLES
-- ============================================================================
INSERT INTO `roles` (`role_name`, `description`, `permissions`) VALUES
('Super Admin', 'Complete system access - reserves for future automation/API', JSON_OBJECT('all_modules', true, 'user_management', true, 'system_settings', true, 'user_approval', true, 'role_management', true, 'super_admin_panel', true, 'agendas', true)),
('Admin', 'Full system access for LGU admins', JSON_OBJECT('all_modules', true, 'user_management', true, 'system_settings', true, 'user_approval', true, 'agendas', true)),
('Committee Chairman', 'Can manage assigned committees, meetings, and agendas', JSON_OBJECT('committee_management', true, 'document_creation', true, 'meeting_scheduling', true, 'agendas', true)),
('Vice Committee Chairman', 'Deputy head of a committee with management privileges', JSON_OBJECT('committee_management', true, 'document_creation', true, 'meeting_scheduling', true, 'agendas', true)),
('User', 'Committee member access - can view and participate in meetings', JSON_OBJECT('view_public_documents', true, 'view_calendar', true, 'view_ordinances', true, 'vote', true, 'agendas', true));

-- ============================================================================
-- INSERT DEFAULT ADMIN USERS
-- ============================================================================
-- Super Admin (Password: admin123) - INACTIVE by default
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `role_id`, `department`, `position`, `is_active`, `email_verified`) VALUES
('super.admin@legislative-services.gov', '$2y$10$ywS1emacPWJslIQxDSbwnOCY/5KXEqmbcqqqTA5VJABnsxturerL.', 'Super', 'Admin', 1, 'National', 'Central Authority', FALSE, TRUE);

-- LGU Admin (Password: admin123) - ACTIVE focus
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `role_id`, `department`, `position`, `is_active`, `email_verified`) VALUES
('LGU@admin.com', '$2y$10$ywS1emacPWJslIQxDSbwnOCY/5KXEqmbcqqqTA5VJABnsxturerL.', 'LGU', 'Admin', 2, 'Administrative Services', 'LGU Administrator', TRUE, TRUE);

-- ============================================================================
-- 18. AGENDA TEMPLATES & ITEMS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `agenda_templates` (
  `template_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `committee_type` VARCHAR(50) DEFAULT 'All',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agenda_template_items` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_id` INT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `duration` INT DEFAULT 15,
  `item_type` VARCHAR(50) DEFAULT 'Discussion',
  `item_order` INT,
  FOREIGN KEY (`template_id`) REFERENCES `agenda_templates`(`template_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` INT AUTO_INCREMENT PRIMARY KEY,
  `committee_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `report_type` VARCHAR(100) DEFAULT 'Committee Report',
  `content` LONGTEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`committee_id`) REFERENCES `committees`(`committee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `votes` (
  `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
  `agenda_item_id` INT,
  `motion_text` TEXT NOT NULL,
  `voting_method` ENUM('Voice Vote', 'Roll Call', 'Secret Ballot', 'Show of Hands') DEFAULT 'Voice Vote',
  `result` ENUM('Pending', 'Passed', 'Failed', 'Tied') DEFAULT 'Pending',
  `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`agenda_item_id`) REFERENCES `agenda_items`(`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_votes` (
  `member_vote_id` INT AUTO_INCREMENT PRIMARY KEY,
  `vote_id` INT,
  `user_id` INT,
  `vote` ENUM('Yes', 'No', 'Abstain', 'Absent'),
  FOREIGN KEY (`vote_id`) REFERENCES `votes`(`vote_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `deliberations` (
  `deliberation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `agenda_item_id` INT,
  `speaker` VARCHAR(100),
  `notes` TEXT,
  `duration` INT DEFAULT 0,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `recorded_by` INT,
  FOREIGN KEY (`agenda_item_id`) REFERENCES `agenda_items`(`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. AGENDA COMMENTS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `agenda_comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT,
  `item_id` INT,
  `comment` TEXT NOT NULL,
  `author_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `agenda_items`(`item_id`) ON DELETE CASCADE,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 21. AGENDA DISTRIBUTION
-- ============================================================================
CREATE TABLE IF NOT EXISTS `agenda_distribution` (
  `distribution_id` INT AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT,
  `method` VARCHAR(50),
  `distributed_by` INT,
  `distributed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE,
  FOREIGN KEY (`distributed_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agenda_distribution_recipients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `distribution_id` INT,
  `member_id` INT,
  FOREIGN KEY (`distribution_id`) REFERENCES `agenda_distribution`(`distribution_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATABASE SETUP COMPLETE
-- ============================================================================
-- Schema Version: 2.1 (Agenda Integrated)
-- Total Tables: 25
-- ============================================================================
