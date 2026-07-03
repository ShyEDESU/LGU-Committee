-- Database Migration: Add email_verified column
-- Run this in your phpMyAdmin SQL tab or command line

ALTER TABLE `users` ADD COLUMN `email_verified` BOOLEAN DEFAULT FALSE AFTER `is_active`;
