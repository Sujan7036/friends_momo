-- ============================================================================
-- UPDATE USERS TABLE - ADD MISSING COLUMNS
-- ============================================================================
-- This script adds the missing columns to the existing users table
-- Run this if you already have a database and don't want to recreate it
-- ============================================================================

USE `friends_momo`;

-- Add missing columns to users table
ALTER TABLE `users` 
ADD COLUMN `password_hash` varchar(255) DEFAULT NULL AFTER `password`,
ADD COLUMN `last_login_attempt` datetime DEFAULT NULL AFTER `last_login`,
ADD COLUMN `last_login_ip` varchar(45) DEFAULT NULL AFTER `last_login_attempt`,
ADD COLUMN `login_attempts` int(11) DEFAULT 0 AFTER `last_login_ip`;

-- Add index for login_attempts
ALTER TABLE `users` ADD KEY `idx_login_attempts` (`login_attempts`);

-- Update existing users to have password_hash same as password and login_attempts = 0
UPDATE `users` SET 
    `password_hash` = `password`,
    `login_attempts` = 0
WHERE `password_hash` IS NULL OR `login_attempts` IS NULL;

-- Success message
SELECT 'Users table updated successfully!' as message,
       'Added columns: password_hash, last_login_attempt, last_login_ip, login_attempts' as details;
