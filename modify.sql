-- Rholance Trading Project Management System (PMS)
-- Database Modification Script
-- 
-- Date: May 19, 2026
-- Description: Added address, phone, welder_id, progress_percent, progress_details, and requested_project columns to update database.
-- 
-- Run the following SQL queries in your phpMyAdmin to update the live database:

ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `address` TEXT DEFAULT NULL;
ALTER TABLE `appointments` ADD COLUMN `welder_id` INT DEFAULT NULL;

-- Progress tracking for custom projects
ALTER TABLE `custom_orders` ADD COLUMN `progress_percent` INT(11) DEFAULT 10;
ALTER TABLE `custom_orders` ADD COLUMN `progress_details` TEXT DEFAULT NULL;

-- Appointment selected templates
ALTER TABLE `appointments` ADD COLUMN `requested_project` VARCHAR(255) DEFAULT NULL;
