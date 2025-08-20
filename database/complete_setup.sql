-- ============================================================================
-- FRIENDS AND MOMOS RESTAURANT MANAGEMENT SYSTEM
-- COMPLETE DATABASE SETUP SCRIPT
-- ============================================================================
-- This script creates the complete database structure for the project
-- Run this single file to set up the entire system without any errors
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS `friends_momo`;
CREATE DATABASE `friends_momo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `friends_momo`;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','staff','admin') DEFAULT 'customer',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_attempt` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_login_attempts` (`login_attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Items Table
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `preparation_time` int(11) DEFAULT 15,
  `prep_time` int(11) DEFAULT 15,
  `calories` int(11) DEFAULT NULL,
  `spice_level` enum('mild','medium','spicy','very_spicy') DEFAULT 'mild',
  `is_vegetarian` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `ingredients` text DEFAULT NULL,
  `allergens` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_available` (`is_available`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `fk_menu_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurant Tables
CREATE TABLE `restaurant_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(10) NOT NULL UNIQUE,
  `capacity` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_table_number` (`table_number`),
  KEY `idx_capacity` (`capacity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `order_type` enum('dine_in','takeaway','delivery') DEFAULT 'takeaway',
  `status` enum('pending','confirmed','preparing','ready','delivered','cancelled','completed') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_delivery_time` datetime DEFAULT NULL,
  `actual_delivery_time` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_menu_item_id` (`menu_item_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations Table
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `guest_count` int(11) NOT NULL,
  `party_size` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `confirmation_sent` tinyint(1) DEFAULT 0,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_table_id` (`table_id`),
  KEY `idx_reservation_date` (`reservation_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_reservations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reservations_table` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADDITIONAL TABLES
-- ============================================================================

-- Activity Logs Table
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loyalty Points Table
CREATE TABLE `loyalty_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_used` int(11) DEFAULT 0,
  `transaction_type` enum('earned','redeemed','expired','bonus') DEFAULT 'earned',
  `description` varchar(255) DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  CONSTRAINT `fk_loyalty_points_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loyalty_points_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `title` varchar(100) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `response` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_menu_item_id` (`menu_item_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_is_approved` (`is_approved`),
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons Table
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount','free_delivery') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage Table
CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coupon_id` (`coupon_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  CONSTRAINT `fk_coupon_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coupon_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_coupon_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT SETTINGS
-- ============================================================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('restaurant_name', 'Friends and Momos', 'Restaurant name'),
('restaurant_address', 'Gungahlin, ACT, Australia', 'Restaurant address'),
('restaurant_phone', '+61 2 1234 5678', 'Restaurant phone number'),
('restaurant_email', 'info@friendsandmomos.com', 'Restaurant email'),
('delivery_fee', '5.00', 'Standard delivery fee'),
('free_delivery_minimum', '50.00', 'Minimum order for free delivery'),
('tax_rate', '0.10', 'Tax rate (GST)'),
('reservation_time_slots', '11:00,11:30,12:00,12:30,13:00,13:30,14:00,14:30,17:00,17:30,18:00,18:30,19:00,19:30,20:00,20:30,21:00', 'Available reservation time slots'),
('max_advance_booking_days', '30', 'Maximum days in advance for bookings'),
('notification_email', 'notifications@friendsandmomos.com', 'Email for notifications'),
('currency_symbol', '$', 'Currency symbol'),
('timezone', 'Australia/Sydney', 'Restaurant timezone'),
('loyalty_points_rate', '1', 'Points earned per dollar spent'),
('loyalty_redemption_rate', '100', 'Points needed for $1 discount');

-- ============================================================================
-- INSERT SAMPLE USERS
-- ============================================================================
-- Default admin user (Password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `password_hash`, `phone`, `address`, `role`, `email_verified`, `login_attempts`, `is_active`) VALUES
('Admin', 'User', 'admin@friendsandmomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 2 1234 5678', 'Gungahlin, ACT, Australia', 'admin', 1, 0, 1);

-- Sample staff user (Password: staff123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `password_hash`, `phone`, `role`, `email_verified`, `login_attempts`, `is_active`) VALUES
('Kitchen', 'Staff', 'kitchen@friendsandmomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 2 1234 5679', 'staff', 1, 0, 1);

-- Sample customer user (Password: customer123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `password_hash`, `phone`, `address`, `role`, `email_verified`, `login_attempts`, `is_active`) VALUES
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 400 123 456', '123 Sample Street, Gungahlin ACT 2912', 'customer', 1, 0, 1);

-- ============================================================================
-- INSERT RESTAURANT TABLES
-- ============================================================================
INSERT INTO `restaurant_tables` (`table_number`, `capacity`, `location`, `is_available`, `description`) VALUES
('T01', 2, 'Window Side', 1, 'Intimate table for two by the window'),
('T02', 2, 'Window Side', 1, 'Cozy window table for couples'),
('T03', 4, 'Main Hall', 1, 'Family table in the main dining area'),
('T04', 4, 'Main Hall', 1, 'Central table perfect for families'),
('T05', 4, 'Main Hall', 1, 'Spacious table in main dining area'),
('T06', 6, 'Main Hall', 1, 'Large table for groups'),
('T07', 6, 'Corner', 1, 'Corner table with privacy for larger groups'),
('T08', 8, 'Private Area', 1, 'Large table in semi-private area'),
('T09', 4, 'Patio', 1, 'Outdoor patio table (weather permitting)'),
('T10', 4, 'Patio', 1, 'Outdoor dining with garden view'),
('T11', 2, 'Bar Area', 1, 'High table near the bar counter'),
('T12', 2, 'Bar Area', 1, 'Bar-style seating for quick meals');

-- ============================================================================
-- INSERT CATEGORIES
-- ============================================================================
INSERT INTO `categories` (`name`, `description`, `image`, `sort_order`, `display_order`, `is_active`) VALUES
('Main Dishes', 'Our signature main dishes featuring traditional Himalayan cuisine', 'main-dishes.jpg', 1, 1, 1),
('Momos', 'Traditional Nepalese dumplings steamed or fried to perfection', 'momos.jpg', 2, 2, 1),
('Noodles', 'Authentic Himalayan noodle dishes with rich flavors', 'noodles.jpg', 3, 3, 1),
('Street Food', 'Popular street food favorites from the Himalayas', 'street-food.jpg', 4, 4, 1),
('Beverages', 'Traditional and modern beverages to complement your meal', 'beverages.jpg', 5, 5, 1);

-- ============================================================================
-- INSERT MENU ITEMS
-- ============================================================================

-- Momos Category (ID: 2)
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `image_url`, `is_available`, `is_featured`, `preparation_time`, `prep_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`, `display_order`) VALUES
(2, 'Steam Momo (Chicken)', 'Traditional steamed chicken dumplings served with spicy tomato chutney', 12.99, 'momo-chicken-steam.jpg', 'momo-chicken-steam.jpg', 1, 1, 20, 20, 280, 'medium', 0, 0, 0, 'Chicken mince, flour, onion, ginger, garlic, spices', 'Gluten, may contain traces of soy', 1, 1),
(2, 'Steam Momo (Vegetable)', 'Fresh vegetables wrapped in delicate dumpling skin, steamed to perfection', 11.99, 'momo-veg-steam.jpg', 'momo-veg-steam.jpg', 1, 0, 18, 18, 220, 'mild', 1, 1, 0, 'Mixed vegetables, flour, onion, ginger, garlic, spices', 'Gluten', 2, 2),
(2, 'Fried Momo (Chicken)', 'Crispy golden fried chicken dumplings with a crunchy exterior', 13.99, 'momo-chicken-fried.jpg', 'momo-chicken-fried.jpg', 1, 0, 25, 25, 320, 'medium', 0, 0, 0, 'Chicken mince, flour, onion, ginger, garlic, spices', 'Gluten, may contain traces of soy', 3, 3),
(2, 'Fried Momo (Vegetable)', 'Crispy fried vegetable dumplings with a perfect golden crunch', 12.99, 'momo-veg-fried.jpg', 'momo-veg-fried.jpg', 1, 0, 23, 23, 260, 'mild', 1, 1, 0, 'Mixed vegetables, flour, onion, ginger, garlic, spices', 'Gluten', 4, 4),
(2, 'Chilly Momo (Chicken)', 'Spicy stir-fried chicken momos tossed in our signature chili sauce', 14.99, 'momo-chicken-chilly.jpg', 'momo-chicken-chilly.jpg', 1, 1, 30, 30, 380, 'spicy', 0, 0, 0, 'Chicken momo, bell peppers, onions, chili sauce, spices', 'Gluten, soy, may contain traces of nuts', 5, 5),
(2, 'Chilly Momo (Vegetable)', 'Spicy stir-fried vegetable momos in aromatic chili sauce', 13.99, 'momo-veg-chilly.jpg', 'momo-veg-chilly.jpg', 1, 0, 28, 28, 320, 'spicy', 1, 0, 0, 'Vegetable momo, bell peppers, onions, chili sauce, spices', 'Gluten, soy', 6, 6);

-- Noodles Category (ID: 3)
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `image_url`, `is_available`, `is_featured`, `preparation_time`, `prep_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`, `display_order`) VALUES
(3, 'Chicken Chowmein', 'Stir-fried noodles with tender chicken and fresh vegetables', 15.99, 'chowmein-chicken.jpg', 'chowmein-chicken.jpg', 1, 1, 25, 25, 450, 'medium', 0, 0, 0, 'Noodles, chicken, mixed vegetables, soy sauce, spices', 'Gluten, soy, may contain eggs', 1, 1),
(3, 'Vegetable Chowmein', 'Colorful mixed vegetables stir-fried with authentic Himalayan noodles', 13.99, 'chowmein-veg.jpg', 'chowmein-veg.jpg', 1, 0, 20, 20, 380, 'mild', 1, 1, 0, 'Noodles, mixed vegetables, soy sauce, spices', 'Gluten, soy', 2, 2),
(3, 'Buff Chowmein', 'Traditional buffalo meat noodles with rich, savory flavors', 14.99, 'chowmein-buff.jpg', 'chowmein-buff.jpg', 1, 0, 30, 30, 480, 'medium', 0, 0, 0, 'Noodles, buffalo meat, vegetables, soy sauce, spices', 'Gluten, soy', 3, 3);

-- Street Food Category (ID: 4)
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `image_url`, `is_available`, `is_featured`, `preparation_time`, `prep_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`, `display_order`) VALUES
(4, 'Laphing (Jhol)', 'Cold, spicy noodles in tangy broth - a Tibetan street food favorite', 11.99, 'laphing-jhol.jpg', 'laphing-jhol.jpg', 1, 1, 15, 15, 280, 'spicy', 1, 1, 0, 'Mung bean noodles, tomato, cucumber, spicy broth', 'May contain traces of gluten', 1, 1),
(4, 'Laphing (Dry)', 'Spicy cold noodles mixed with vegetables and aromatic spices', 10.99, 'laphing-dry.jpg', 'laphing-dry.jpg', 1, 0, 12, 12, 250, 'very_spicy', 1, 1, 0, 'Mung bean noodles, tomato, cucumber, dry spices', 'May contain traces of gluten', 2, 2),
(4, 'Aaloo Nimki', 'Crispy potato snack seasoned with traditional Himalayan spices', 8.99, 'aaloo-nimki.jpg', 'aaloo-nimki.jpg', 1, 0, 15, 15, 220, 'mild', 1, 1, 1, 'Potato, spices, oil', 'None', 3, 3),
(4, 'Pani Puri (6 pieces)', 'Crispy hollow shells filled with spiced water and tangy chutneys', 9.99, 'pani-puri.jpg', 'pani-puri.jpg', 1, 0, 10, 10, 180, 'medium', 1, 1, 0, 'Semolina shells, spiced water, chickpeas, chutneys', 'Gluten', 4, 4),
(4, 'Samosa (2 pieces)', 'Golden fried pastries filled with spiced potato and peas', 7.99, 'samosa.jpg', 'samosa.jpg', 1, 0, 20, 20, 280, 'mild', 1, 1, 0, 'Flour, potato, peas, spices, oil', 'Gluten', 5, 5);

-- Main Dishes Category (ID: 1)
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `image_url`, `is_available`, `is_featured`, `preparation_time`, `prep_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`, `display_order`) VALUES
(1, 'Dal Bhat Set', 'Traditional Nepalese meal with lentil curry, rice, and accompaniments', 18.99, 'dal-bhat.jpg', 'dal-bhat.jpg', 1, 1, 35, 35, 520, 'mild', 1, 1, 1, 'Lentils, rice, vegetables, pickle, papad', 'None', 1, 1),
(1, 'Chicken Curry', 'Tender chicken cooked in aromatic Himalayan spices and herbs', 19.99, 'chicken-curry.jpg', 'chicken-curry.jpg', 1, 1, 40, 40, 480, 'medium', 0, 0, 1, 'Chicken, onion, tomato, spices, herbs', 'None', 2, 2),
(1, 'Mutton Curry', 'Slow-cooked goat meat in rich, flavorful traditional curry', 22.99, 'mutton-curry.jpg', 'mutton-curry.jpg', 1, 0, 50, 50, 580, 'medium', 0, 0, 1, 'Goat meat, onion, tomato, spices, herbs', 'None', 3, 3);

-- Beverages Category (ID: 5)
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `image_url`, `is_available`, `is_featured`, `preparation_time`, `prep_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`, `display_order`) VALUES
(5, 'Masala Chai', 'Traditional spiced tea brewed with aromatic herbs and spices', 4.99, 'masala-chai.jpg', 'masala-chai.jpg', 1, 0, 8, 8, 80, 'mild', 1, 0, 1, 'Tea, milk, sugar, cardamom, ginger, cinnamon', 'Dairy', 1, 1),
(5, 'Himalayan Tea', 'Premium high-altitude tea with a unique mountain flavor', 5.99, 'himalayan-tea.jpg', 'himalayan-tea.jpg', 1, 0, 5, 5, 5, 'mild', 1, 1, 1, 'High-altitude tea leaves', 'None', 2, 2),
(5, 'Lassi (Sweet)', 'Refreshing yogurt-based drink with a touch of sweetness', 6.99, 'lassi-sweet.jpg', 'lassi-sweet.jpg', 1, 0, 5, 5, 150, 'mild', 1, 0, 1, 'Yogurt, sugar, cardamom', 'Dairy', 3, 3),
(5, 'Mango Lassi', 'Creamy mango-flavored yogurt drink - perfect for summer', 7.99, 'mango-lassi.jpg', 'mango-lassi.jpg', 1, 1, 5, 5, 180, 'mild', 1, 0, 1, 'Yogurt, mango, sugar', 'Dairy', 4, 4),
(5, 'Fresh Lime Soda', 'Zesty lime drink with soda water and a hint of salt', 4.99, 'lime-soda.jpg', 'lime-soda.jpg', 1, 0, 3, 3, 60, 'mild', 1, 1, 1, 'Lime, soda water, salt, sugar', 'None', 5, 5);

-- ============================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================================================
CREATE INDEX idx_orders_status_date ON orders(status, created_at);
CREATE INDEX idx_reservations_date_status ON reservations(reservation_date, status);
CREATE INDEX idx_menu_items_featured_available ON menu_items(is_featured, is_available);
CREATE INDEX idx_loyalty_points_user_type ON loyalty_points(user_id, transaction_type);

-- ============================================================================
-- CREATE TRIGGERS FOR AUTO-GENERATION
-- ============================================================================
DELIMITER //

-- Trigger to generate order number
CREATE TRIGGER generate_order_number 
BEFORE INSERT ON orders 
FOR EACH ROW 
BEGIN 
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 2) AS UNSIGNED)), 0) + 1 INTO next_num 
    FROM orders WHERE order_number REGEXP '^O[0-9]+$';
    SET NEW.order_number = CONCAT('O', LPAD(next_num, 6, '0'));
END//

-- Update reservation party_size from guest_count
CREATE TRIGGER sync_reservation_party_size
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    IF NEW.party_size IS NULL OR NEW.party_size = 0 THEN
        SET NEW.party_size = NEW.guest_count;
    END IF;
    IF NEW.guest_count IS NULL OR NEW.guest_count = 0 THEN
        SET NEW.guest_count = NEW.party_size;
    END IF;
END//

-- Update order_items price fields
CREATE TRIGGER sync_order_item_prices
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    -- Set unit_price = item_price if not set
    IF NEW.unit_price IS NULL OR NEW.unit_price = 0 THEN
        SET NEW.unit_price = NEW.item_price;
    END IF;
    
    -- Set item_price = unit_price if not set
    IF NEW.item_price IS NULL OR NEW.item_price = 0 THEN
        SET NEW.item_price = NEW.unit_price;
    END IF;
    
    -- Set price = unit_price if not set
    IF NEW.price IS NULL OR NEW.price = 0 THEN
        SET NEW.price = NEW.unit_price;
    END IF;
    
    -- Calculate total_price
    SET NEW.total_price = NEW.unit_price * NEW.quantity;
END//

DELIMITER ;

-- ============================================================================
-- CREATE SAMPLE ORDERS AND RESERVATIONS
-- ============================================================================

-- Sample order
INSERT INTO `orders` (`user_id`, `customer_name`, `customer_email`, `customer_phone`, `order_type`, `status`, `total_amount`, `tax_amount`, `payment_status`, `payment_method`) VALUES
(3, 'John Doe', 'john.doe@example.com', '+61 400 123 456', 'dine_in', 'pending', 25.98, 2.36, 'pending', 'cash');

-- Sample order items
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `unit_price`, `item_price`, `price`, `total_price`) VALUES
(1, 1, 2, 12.99, 12.99, 12.99, 25.98);

-- Sample reservation
INSERT INTO `reservations` (`user_id`, `customer_name`, `customer_email`, `customer_phone`, `guest_count`, `party_size`, `reservation_date`, `reservation_time`, `status`) VALUES
(3, 'John Doe', 'john.doe@example.com', '+61 400 123 456', 4, 4, CURDATE() + INTERVAL 1 DAY, '18:00:00', 'pending');

-- ============================================================================
-- COMMIT TRANSACTION
-- ============================================================================
COMMIT;

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================
SELECT 'Friends and Momos Database Setup Complete!' as message,
       'Database: friends_momo' as database_name,
       'Admin Login: admin@friendsandmomos.com / admin123' as admin_login,
       'Staff Login: kitchen@friendsandmomos.com / staff123' as staff_login,
       'Customer Login: john.doe@example.com / customer123' as customer_login;
