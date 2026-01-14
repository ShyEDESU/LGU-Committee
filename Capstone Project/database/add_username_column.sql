-- Add username column to users table if it doesn't exist
-- Run this SQL in your database

ALTER TABLE users 
ADD COLUMN username VARCHAR(50) UNIQUE AFTER user_id;

-- Update existing users to have username based on email
UPDATE users 
SET username = SUBSTRING_INDEX(email, '@', 1) 
WHERE username IS NULL OR username = '';

-- Make username NOT NULL after populating
ALTER TABLE users 
MODIFY COLUMN username VARCHAR(50) UNIQUE NOT NULL;
