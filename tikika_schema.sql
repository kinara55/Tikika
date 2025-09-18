DROP DATABASE IF EXISTS `tikika_db`;
CREATE DATABASE `tikika_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tikika_db`;

## Roles (admin, organizer, attendee)
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

## Users
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 3,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30),
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

##Venues
CREATE TABLE `venues` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `address` VARCHAR(255),
  `city` VARCHAR(100),
  `capacity` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

##Categories (music, conference, meetup, etc.)
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

## Events
CREATE TABLE `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `organizer_id` INT UNSIGNED NOT NULL,
  `venue_id` INT UNSIGNED,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `start_datetime` DATETIME NOT NULL,
  `end_datetime` DATETIME,
  `status` ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
  `capacity` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`venue_id`) REFERENCES `venues`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

##Many-to-many: events <-> categories
CREATE TABLE `event_categories` (
  `event_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`event_id`,`category_id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

##Tickets types for an event (General, VIP, Early bird...)
CREATE TABLE `tickets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `sold` INT UNSIGNED NOT NULL DEFAULT 0,
  `available` INT AS (GREATEST(`quantity` - `sold`, 0)) STORED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

##Orders (purchase attempts / carts)
CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

##Items inside orders (which tickets & quantity)
CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `ticket_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

##Payments (external provider records)
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `provider` VARCHAR(100) NOT NULL,
  `provider_response` TEXT,
  `provider_reference` VARCHAR(255),
  `amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('initiated','success','failed','refunded') NOT NULL DEFAULT 'initiated',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

##Event images
CREATE TABLE `event_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `alt_text` VARCHAR(255),
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE INDEX `idx_users_email` ON `users`(`email`);
CREATE INDEX `idx_events_start` ON `events`(`start_datetime`);
CREATE INDEX `idx_tickets_event` ON `tickets`(`event_id`);


