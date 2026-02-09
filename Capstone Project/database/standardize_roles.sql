-- Standardize Role Names
UPDATE `roles` SET `role_name` = 'Super Admin' WHERE `role_name` = 'Super Administrator';
UPDATE `roles` SET `role_name` = 'Admin' WHERE `role_name` = 'Administrator';
UPDATE `roles` SET `role_name` = 'Committee Chairman' WHERE `role_name` = 'Committee Chair';

-- Consolidate other roles into 'User'
-- Note: You might want to move these users manually if they have specific permissions, 
-- but following the plan:
INSERT IGNORE INTO `roles` (`role_name`, `description`, `permissions`) 
VALUES ('User', 'Standard member and public access', '{"view_public": true, "vote": true}');

UPDATE `users` SET `role_id` = (SELECT `role_id` FROM `roles` WHERE `role_name` = 'User')
WHERE `role_id` IN (SELECT `role_id` FROM `roles` WHERE `role_name` IN ('Committee Secretary', 'Staff/Encoder', 'Public Viewer'));

DELETE FROM `roles` WHERE `role_name` IN ('Committee Secretary', 'Staff/Encoder', 'Public Viewer');

-- Deactivate Default Super Admin for now
UPDATE `users` SET `is_active` = FALSE WHERE `email` = 'super.admin@legislative-services.gov';
