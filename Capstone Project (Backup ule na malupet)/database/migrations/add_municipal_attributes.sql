-- Migration: Municipal Grade Agenda Attributes
-- Added: February 24, 2026

USE `legislative_cms`;

-- Add Consent Calendar support to agenda items
ALTER TABLE `agenda_items` 
ADD COLUMN `is_consent` TINYINT(1) DEFAULT 0 AFTER `item_type`;

-- Add Legal Notice and Versioning support to meetings
ALTER TABLE `meetings` 
ADD COLUMN `posted_at` DATETIME NULL AFTER `agenda_status`,
ADD COLUMN `is_amended` TINYINT(1) DEFAULT 0 AFTER `posted_at`;
