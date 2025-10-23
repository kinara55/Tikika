-- phpMyAdmin SQL Dump
-- version 6.0.0-dev
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 23, 2025 at 10:13 AM
-- Server version: 12.0.2-MariaDB
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tikika_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Music', 'Concerts and music shows', '2025-10-12 19:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `organizer_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `status` enum('draft','published','cancelled') NOT NULL DEFAULT 'draft',
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `organizer_id`, `title`, `description`, `venue`, `start_datetime`, `end_datetime`, `status`, `capacity`, `category_id`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'AfroBeats Night', 'Get ready for a night of amazing AfroBeats music with top artists from across Africa', 'Nairobi Arena', '2025-10-15 19:00:00', '2025-10-15 23:00:00', 'published', 1000, 1, NULL, '2025-10-12 19:11:02', '2025-10-12 19:11:02'),
(2, 1, 'Jazz Festival', 'Experience smooth jazz under the stars with renowned performers from around the world', 'Uhuru Gardens', '2025-11-02 18:00:00', '2025-11-02 22:00:00', 'published', 800, 1, NULL, '2025-10-12 19:11:02', '2025-10-12 19:11:02'),
(3, 1, 'Bambika na 3 men Army', 'Ready to crack your ribssssssss!', 'Beer District', '2025-12-10 20:00:00', '2025-12-10 23:30:00', 'published', 500, 1, NULL, '2025-10-12 19:11:02', '2025-10-12 19:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_provider` varchar(100) DEFAULT NULL,
  `provider_reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `payment_provider`, `provider_reference`, `status`, `created_at`) VALUES
(1, 1, 4000.00, NULL, NULL, 'pending', '2025-10-12 19:07:28'),
(2, 1, 4000.00, NULL, NULL, 'pending', '2025-10-12 19:08:57'),
(3, 1, 5500.00, NULL, NULL, 'pending', '2025-10-12 19:09:14'),
(4, 1, 5500.00, NULL, NULL, 'pending', '2025-10-12 19:11:58'),
(5, 1, 7000.00, NULL, NULL, 'pending', '2025-10-12 19:12:11'),
(6, 1, 4000.00, NULL, NULL, 'pending', '2025-10-12 19:13:37'),
(7, 1, 8000.00, NULL, NULL, 'pending', '2025-10-12 19:13:55'),
(8, 1, 8000.00, NULL, NULL, 'pending', '2025-10-12 19:14:53'),
(9, 1, 8000.00, NULL, NULL, 'pending', '2025-10-12 19:16:00'),
(10, 1, 8000.00, NULL, NULL, 'pending', '2025-10-12 19:17:14'),
(11, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:18:26'),
(12, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:21:03'),
(13, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:22:19'),
(14, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:40:57'),
(15, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:49:41'),
(16, 1, 12000.00, NULL, NULL, 'pending', '2025-10-12 19:51:26'),
(17, 1, 3000.00, NULL, NULL, 'paid', '2025-10-12 16:52:43'),
(18, 1, 3000.00, NULL, NULL, 'pending', '2025-10-12 19:53:35'),
(19, 1, 3000.00, NULL, NULL, 'pending', '2025-10-12 19:54:56'),
(20, 1, 3000.00, NULL, NULL, 'pending', '2025-10-12 19:55:14'),
(21, 1, 3000.00, NULL, NULL, 'pending', '2025-10-13 08:19:22'),
(22, 2, 6000.00, NULL, NULL, 'pending', '2025-10-14 08:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `ticket_type` varchar(50) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `event_id`, `ticket_type`, `quantity`, `unit_price`, `created_at`) VALUES
(4, 4, 2, 'vip', 1, 4000.00, '2025-10-12 19:11:58'),
(5, 4, 1, 'regular', 1, 1500.00, '2025-10-12 19:11:58'),
(6, 5, 2, 'vip', 1, 4000.00, '2025-10-12 19:12:11'),
(7, 5, 1, 'regular', 2, 1500.00, '2025-10-12 19:12:11'),
(8, 6, 2, 'vip', 1, 4000.00, '2025-10-12 19:13:37'),
(9, 7, 2, 'vip', 1, 4000.00, '2025-10-12 19:13:55'),
(10, 7, 3, 'vip', 2, 2000.00, '2025-10-12 19:13:55'),
(11, 8, 2, 'vip', 1, 4000.00, '2025-10-12 19:14:53'),
(12, 8, 3, 'vip', 2, 2000.00, '2025-10-12 19:14:53'),
(13, 9, 2, 'vip', 1, 4000.00, '2025-10-12 19:16:00'),
(14, 9, 3, 'vip', 2, 2000.00, '2025-10-12 19:16:00'),
(15, 10, 2, 'vip', 1, 4000.00, '2025-10-12 19:17:14'),
(16, 10, 3, 'vip', 2, 2000.00, '2025-10-12 19:17:14'),
(17, 11, 2, 'vip', 1, 4000.00, '2025-10-12 19:18:26'),
(18, 11, 3, 'vip', 2, 2000.00, '2025-10-12 19:18:26'),
(19, 11, 2, 'regular', 2, 2000.00, '2025-10-12 19:18:26'),
(20, 12, 2, 'vip', 1, 4000.00, '2025-10-12 19:21:03'),
(21, 12, 3, 'vip', 2, 2000.00, '2025-10-12 19:21:03'),
(22, 12, 2, 'regular', 2, 2000.00, '2025-10-12 19:21:03'),
(23, 13, 2, 'vip', 1, 4000.00, '2025-10-12 19:22:19'),
(24, 13, 3, 'vip', 2, 2000.00, '2025-10-12 19:22:19'),
(25, 13, 2, 'regular', 2, 2000.00, '2025-10-12 19:22:19'),
(26, 14, 2, 'vip', 1, 4000.00, '2025-10-12 19:40:57'),
(27, 14, 3, 'vip', 2, 2000.00, '2025-10-12 19:40:57'),
(28, 14, 2, 'regular', 2, 2000.00, '2025-10-12 19:40:57'),
(29, 15, 2, 'vip', 1, 4000.00, '2025-10-12 19:49:41'),
(30, 15, 3, 'vip', 2, 2000.00, '2025-10-12 19:49:41'),
(31, 15, 2, 'regular', 2, 2000.00, '2025-10-12 19:49:41'),
(32, 16, 2, 'vip', 1, 4000.00, '2025-10-12 19:51:26'),
(33, 16, 3, 'vip', 2, 2000.00, '2025-10-12 19:51:26'),
(34, 16, 2, 'regular', 2, 2000.00, '2025-10-12 19:51:26'),
(35, 17, 1, 'VIP', 2, 1500.00, '2025-10-12 19:52:43'),
(36, 18, 1, 'regular', 2, 1500.00, '2025-10-12 19:53:35'),
(37, 19, 1, 'VIP', 2, 1500.00, '2025-10-12 19:54:56'),
(38, 20, 1, 'regular', 2, 1500.00, '2025-10-12 19:55:14'),
(39, 21, 1, 'regular', 2, 1500.00, '2025-10-13 08:19:22'),
(40, 22, 1, 'vip', 2, 3000.00, '2025-10-14 08:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `code`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 2, '837199', '2025-10-22 07:38:47', NULL, '2025-10-22 10:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Full administrative access', '2025-10-12 19:06:06'),
(2, 'organizer', 'Can create and manage events', '2025-10-12 19:06:06'),
(3, 'attendee', 'Buy tickets and attend events', '2025-10-12 19:06:06');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `data` text DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sold` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `event_id`, `type`, `price`, `quantity`, `sold`, `created_at`) VALUES
(1, 1, 'Regular', 1500.00, 500, 0, '2025-10-12 19:11:02'),
(2, 1, 'VIP', 3000.00, 200, 0, '2025-10-12 19:11:02'),
(3, 1, 'VVIP', 4500.00, 100, 0, '2025-10-12 19:11:02'),
(4, 2, 'Regular', 2000.00, 400, 0, '2025-10-12 19:11:02'),
(5, 2, 'VIP', 4000.00, 150, 0, '2025-10-12 19:11:02'),
(6, 2, 'VVIP', 6000.00, 50, 0, '2025-10-12 19:11:02'),
(7, 3, 'Regular', 1000.00, 300, 0, '2025-10-12 19:11:02'),
(8, 3, 'VIP', 2000.00, 100, 0, '2025-10-12 19:11:02'),
(9, 3, 'VVIP', 3000.00, 50, 0, '2025-10-12 19:11:02'),
(10, 1, 'Regular', 1500.00, 500, 0, '2025-10-12 19:11:12'),
(11, 1, 'VIP', 3000.00, 200, 0, '2025-10-12 19:11:12'),
(12, 1, 'VVIP', 4500.00, 100, 0, '2025-10-12 19:11:12'),
(13, 2, 'Regular', 2000.00, 400, 0, '2025-10-12 19:11:12'),
(14, 2, 'VIP', 4000.00, 150, 0, '2025-10-12 19:11:12'),
(15, 2, 'VVIP', 6000.00, 50, 0, '2025-10-12 19:11:12'),
(16, 3, 'Regular', 1000.00, 300, 0, '2025-10-12 19:11:12'),
(17, 3, 'VIP', 2000.00, 100, 0, '2025-10-12 19:11:12'),
(18, 3, 'VVIP', 3000.00, 50, 0, '2025-10-12 19:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT 3,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_banned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `is_verified`, `created_at`, `updated_at`, `is_banned`) VALUES
(1, 3, 'Zakariya Mohamed', 'Boochimio8@gmail.com', '$2y$10$9/DXGgkBd7KSGtoL3YxQL.FaMS6s2Q/AWGTvnefqioASB3VE38TCK', '0783281584', 1, '2025-10-12 19:06:06', '2025-10-12 19:07:02', 0),
(2, 3, 'Zakariya Mohamed Mohamed', 'Boochimo9@gmail.com', '$2y$10$IyjknEcQCpW1Zw8q.d4hWubEpD8e11FE3iAnqazbGoUyNcdDFlzB6', '0783281584', 1, '2025-10-14 08:14:40', '2025-10-14 08:14:56', 0),
(3, 3, 'Abel', 'bridgetwanyama0@gmail.com', '$2y$10$wVgcX46t7/euKtIA5wusFO3QQZk7IL31vZUTvgcQ87wvUO6ZadfaW', '0743694406', 1, '2025-10-22 11:51:20', '2025-10-22 11:51:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `user_id`, `code`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, '966296', '2025-10-12 16:21:06', '2025-10-12 16:07:02', '2025-10-12 19:06:06'),
(2, 2, '906210', '2025-10-14 05:29:40', '2025-10-14 05:14:56', '2025-10-14 08:14:40'),
(3, 3, '950566', '2025-10-22 09:06:20', '2025-10-22 08:51:55', '2025-10-22 11:51:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_events_start` (`start_datetime`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tickets_event` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
