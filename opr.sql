-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 26, 2025 at 07:39 AM
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
-- Database: `opr`
--

-- --------------------------------------------------------

--
-- Table structure for table `houses`
--

CREATE TABLE `houses` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `area` float DEFAULT 0,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','declined','rented') DEFAULT 'pending',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `property_manager_id` int(11) DEFAULT NULL,
  `is_rented` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `houses`
--

INSERT INTO `houses` (`id`, `owner_id`, `title`, `location`, `bedrooms`, `bathrooms`, `area`, `description`, `price`, `status`, `image_path`, `created_at`, `property_manager_id`, `is_rented`) VALUES
(6, 8, 'Apartment', 'Bole', 2, 2, 3212, NULL, 21321.00, 'approved', '1748145571_h2.jpg', '2025-05-25 03:59:31', 9, 0),
(7, 8, 'Apartment', 'Mexico', 3, 2, 3500, 'New House', 20000.00, 'approved', '1748145871_h1.jpg', '2025-05-25 04:04:31', 9, 0),
(8, 8, 'Apartment', 'Lafto', 2, 1, 1500, 'New House', 15000.00, 'approved', '1748146181_h3.jpg', '2025-05-25 04:09:41', 9, 0),
(9, 8, 'Apartment', 'Kera', 3, 1, 1809, 'New house', 21222.00, 'approved', '1748146324_h4.jpg', '2025-05-25 04:12:04', 9, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lease_agreements`
--

CREATE TABLE `lease_agreements` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `signed_by_tenant` tinyint(1) DEFAULT 0,
  `signed_by_owner` tinyint(1) DEFAULT 0,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(20) DEFAULT 'request',
  `status` varchar(20) DEFAULT 'pending',
  `house_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `type`, `status`, `house_id`) VALUES
(57, 11, 9, 'I would like to rent this house.', 0, '2025-05-26 03:39:28', 'request', 'pending', 6),
(58, 11, 9, 'I would like to rent this house.', 0, '2025-05-26 03:39:33', 'request', 'pending', 8),
(61, 8, 9, 'Owner has approved the rental request.', 0, '2025-05-26 05:07:31', 'response', 'approved', 8),
(62, 8, 9, 'Owner has approved the rental request.', 0, '2025-05-26 05:11:27', 'response', 'approved', 6),
(63, 11, 9, 'I would like to rent this house.', 0, '2025-05-26 05:12:04', 'request', 'pending', 7),
(64, 11, 9, 'I would like to rent this house.', 0, '2025-05-26 05:12:08', 'request', 'pending', 9),
(65, 8, 9, 'Owner has declined the rental request.', 0, '2025-05-26 05:13:37', 'response', 'declined', 7);

-- --------------------------------------------------------

--
-- Table structure for table `owner_profiles`
--

CREATE TABLE `owner_profiles` (
  `user_id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `id_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `owner_profiles`
--

INSERT INTO `owner_profiles` (`user_id`, `phone`, `address`, `id_photo`) VALUES
(8, '0098744456', 'adiss', 'b8d808edbed54581318e4d41092bb75c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `rental_requests`
--

CREATE TABLE `rental_requests` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `status` enum('pending','approved','declined','forwarded') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `message` text DEFAULT NULL,
  `pm_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rental_requests`
--

INSERT INTO `rental_requests` (`id`, `tenant_id`, `house_id`, `status`, `created_at`, `updated_at`, `message`, `pm_id`) VALUES
(18, 11, 6, 'approved', '2025-05-26 03:39:28', NULL, NULL, 9),
(19, 11, 8, 'approved', '2025-05-26 03:39:33', NULL, NULL, 9),
(20, 11, 7, 'declined', '2025-05-26 05:12:04', NULL, NULL, 9),
(21, 11, 9, 'pending', '2025-05-26 05:12:08', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `property_manager_id` int(11) NOT NULL,
  `flagged` tinyint(1) DEFAULT 0,
  `report_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_profiles`
--

CREATE TABLE `tenant_profiles` (
  `user_id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `id_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tenant_profiles`
--

INSERT INTO `tenant_profiles` (`user_id`, `phone`, `address`, `id_photo`) VALUES
(11, '091233433', 'Adiss Ababa', '1013b56c032e5cbf76582a116160e227.jpg'),
(12, '0911153000', 'Adiss ababa', '940ab44d0a9b70836163f78d89e64a34.png');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','tenant','owner','property_manager','government') NOT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(6, 'Main Admin', 'admin@example.com', '$2y$10$pfOTPXGZeY6TWT6055PLwOmD3M6cI0ofBg6ttzg5tiVLOagD2Nxuy', 'admin', 'active', '2025-05-23 19:12:10'),
(8, '', 'aa@a.com', '$2y$10$FrnUzYGEA4n46v4f6HxOr.KLrg8JoncpPxdoN7rtdl4li.bs3/dzS', 'owner', 'active', '2025-05-23 23:06:40'),
(9, 'Ermi', 'manager@test.com', '$2y$10$S6gda.mS/ycyB7BHJ.3Lfu8O3.6drh4RhV7QDtq.0MB9pYdCP.cdW', 'property_manager', 'active', '2025-05-23 23:46:34'),
(10, 'efrem', 'gov@et.com', '$2y$10$QLi8xKUAXbmXGlQyPBqdde90SDh1gkCM5kOT7AN6.T6UYcTQyVtom', 'government', 'active', '2025-05-24 00:16:06'),
(11, 'eyob', 'ey@o.com', '$2y$10$Mq7RSclq5kvzcTf0RC.3fe62Jm6kJ.HjqckyiSqto66QOsp.GoueC', 'tenant', 'active', '2025-05-24 10:50:10'),
(12, 'abiy', 'ab@y.com', '$2y$10$HIdlhBCc1COhREM3oDCyQuvvESxgTMW7w1CvFY3Fo6cYkJAQySWIq', 'tenant', 'active', '2025-05-25 04:31:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `houses`
--
ALTER TABLE `houses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_owner` (`owner_id`);

--
-- Indexes for table `lease_agreements`
--
ALTER TABLE `lease_agreements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lease_house` (`house_id`),
  ADD KEY `fk_lease_tenant` (`tenant_id`),
  ADD KEY `fk_lease_owner` (`owner_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notify_sender` (`sender_id`),
  ADD KEY `fk_notify_receiver` (`receiver_id`);

--
-- Indexes for table `owner_profiles`
--
ALTER TABLE `owner_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `rental_requests`
--
ALTER TABLE `rental_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_request_tenant` (`tenant_id`),
  ADD KEY `fk_request_house` (`house_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_report_transaction` (`transaction_id`),
  ADD KEY `fk_report_manager` (`property_manager_id`);

--
-- Indexes for table `tenant_profiles`
--
ALTER TABLE `tenant_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_tenant` (`tenant_id`),
  ADD KEY `fk_transaction_house` (`house_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `houses`
--
ALTER TABLE `houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lease_agreements`
--
ALTER TABLE `lease_agreements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `rental_requests`
--
ALTER TABLE `rental_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `houses`
--
ALTER TABLE `houses`
  ADD CONSTRAINT `fk_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lease_agreements`
--
ALTER TABLE `lease_agreements`
  ADD CONSTRAINT `fk_lease_house` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`),
  ADD CONSTRAINT `fk_lease_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_lease_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lease_agreements_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lease_agreements_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lease_agreements_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notify_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_notify_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `owner_profiles`
--
ALTER TABLE `owner_profiles`
  ADD CONSTRAINT `fk_owner_profile` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `owner_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_requests`
--
ALTER TABLE `rental_requests`
  ADD CONSTRAINT `fk_request_house` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`),
  ADD CONSTRAINT `fk_request_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rental_requests_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_requests_ibfk_2` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_report_manager` FOREIGN KEY (`property_manager_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_report_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`property_manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_profiles`
--
ALTER TABLE `tenant_profiles`
  ADD CONSTRAINT `fk_tenant_profile` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tenant_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_house` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`),
  ADD CONSTRAINT `fk_transaction_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
