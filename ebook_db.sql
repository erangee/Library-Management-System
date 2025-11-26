-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 07:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ebook_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ebooks`
--

CREATE TABLE `ebooks` (
  `ebook_id` bigint(20) NOT NULL,
  `publisher_id` bigint(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `file_link` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Draft','Published','Unpublished','Blocked') NOT NULL DEFAULT 'Draft',
  `published_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ebook_reviews`
--

CREATE TABLE `ebook_reviews` (
  `review_id` bigint(20) NOT NULL,
  `reader_id` bigint(20) NOT NULL,
  `ebook_id` bigint(20) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites_list`
--

CREATE TABLE `favorites_list` (
  `reader_id` bigint(20) NOT NULL,
  `ebook_id` bigint(20) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `payment_id` bigint(20) NOT NULL,
  `subscription_id` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Success','Pending','Failed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reader_subscriptions`
--

CREATE TABLE `reader_subscriptions` (
  `subscription_id` bigint(20) NOT NULL,
  `reader_id` bigint(20) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL DEFAULT current_timestamp(),
  `expiry_date` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reading_log`
--

CREATE TABLE `reading_log` (
  `log_id` bigint(20) NOT NULL,
  `reader_id` bigint(20) NOT NULL,
  `ebook_id` bigint(20) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `plan_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`plan_id`, `name`, `fee`, `duration_days`, `description`) VALUES
(1, 'Monthly Subscription', 500.00, 30, 'Access all paid content for 30 days.'),
(2, 'Yearly Subscription', 5000.00, 365, 'Access all paid content for one year (Includes Rs. 1000 discount).');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `role_name` enum('Admin','Publisher','Reader') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD PRIMARY KEY (`ebook_id`),
  ADD KEY `publisher_id` (`publisher_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ebook_reviews`
--
ALTER TABLE `ebook_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`reader_id`,`ebook_id`),
  ADD KEY `ebook_id` (`ebook_id`);

--
-- Indexes for table `favorites_list`
--
ALTER TABLE `favorites_list`
  ADD PRIMARY KEY (`reader_id`,`ebook_id`),
  ADD KEY `ebook_id` (`ebook_id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `reader_subscriptions`
--
ALTER TABLE `reader_subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `reader_id` (`reader_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `reading_log`
--
ALTER TABLE `reading_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `reader_id` (`reader_id`),
  ADD KEY `ebook_id` (`ebook_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `ebook_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ebook_reviews`
--
ALTER TABLE `ebook_reviews`
  MODIFY `review_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `payment_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reader_subscriptions`
--
ALTER TABLE `reader_subscriptions`
  MODIFY `subscription_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reading_log`
--
ALTER TABLE `reading_log`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD CONSTRAINT `ebooks_ibfk_1` FOREIGN KEY (`publisher_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ebooks_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `ebook_reviews`
--
ALTER TABLE `ebook_reviews`
  ADD CONSTRAINT `ebook_reviews_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ebook_reviews_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`ebook_id`);

--
-- Constraints for table `favorites_list`
--
ALTER TABLE `favorites_list`
  ADD CONSTRAINT `favorites_list_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `favorites_list_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`ebook_id`);

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `reader_subscriptions` (`subscription_id`);

--
-- Constraints for table `reader_subscriptions`
--
ALTER TABLE `reader_subscriptions`
  ADD CONSTRAINT `reader_subscriptions_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reader_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`plan_id`);

--
-- Constraints for table `reading_log`
--
ALTER TABLE `reading_log`
  ADD CONSTRAINT `reading_log_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reading_log_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`ebook_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
