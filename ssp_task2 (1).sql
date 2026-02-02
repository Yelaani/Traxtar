-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 01:15 PM
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
-- Database: `ssp_task2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT '',
  `address` text DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `created_at`, `password`, `phone`, `address`, `name`) VALUES
(1, 'admin@example.com', '2025-09-22 08:48:29', '$2y$10$I7eCSqAKB9fe7w.UAyLbEeCUXAJI207Dxvn8AOoTMqJeRGy1IQCAa', '', '', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Men', 'men', '2025-09-22 14:58:53'),
(2, 'Women', 'women', '2025-09-22 14:58:53'),
(3, 'Accessories', 'accessories', '2025-09-22 14:58:53'),
(4, 'Sale', 'sale', '2025-09-22 14:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'customer1', 'customer1@example.com', '$2y$10$iVk0RjbbtNVj6qf4iJmEpuWnpC2WjKI7GMrS0qqWJfoGrFHMeJ2SC', '', '', '2025-09-22 14:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_name` varchar(255) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `shipping_name`, `shipping_phone`, `shipping_address`, `total`, `status`, `created_at`) VALUES
(2, 10, 'done', '45678456789', 'ghsk', 2200.00, 'pending', '2025-09-22 07:29:40'),
(3, 10, 'man', '45678456789', 'ueqqgqe', 15000.00, 'pending', '2025-09-22 07:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `qty`, `created_at`) VALUES
(1, 2, 16, 'Breathable Sports Cap', 1100.00, 2, '2025-09-22 07:29:40'),
(2, 3, 20, 'Speed Jump Rope', 1500.00, 10, '2025-09-22 07:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `description`, `price`, `stock`, `category_id`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Women\'s Seamless Leggings', 'AW-W-001', 'High-rise seamless leggings for training and yoga.', 39.90, 48, 1, 'images/womens_seamless_leggings.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(2, 'Women\'s Medium-Support Sports Bra', 'AW-W-002', 'Breathable sports bra with removable pads.', 24.50, 36, 1, 'images/womens_sports_bra.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(3, 'Women\'s Airflow Tank', 'AW-W-003', 'Featherlight tank with perforated back panel.', 19.90, 52, 1, 'images/womens_airflow_tank.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(4, 'Women\'s Sprint 3\" Shorts', 'AW-W-004', 'Quick-dry running shorts with inner brief.', 22.00, 41, 1, 'images/womens_sprint_shorts.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(5, 'Women\'s Zip Hoodie', 'AW-W-005', 'Soft terry zip hoodie for warm-ups.', 44.00, 29, 1, 'images/womens_zip_hoodie.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(6, 'Women\'s Long-Sleeve Tech Tee', 'AW-W-006', 'Moisture-wicking LS tee with thumbholes.', 27.50, 33, 1, 'images/womens_ls_tech_tee.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(7, 'Women\'s Packable Windbreaker', 'AW-W-007', 'Water-resistant, packs into chest pocket.', 49.00, 25, 1, 'images/womens_packable_windbreaker.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(8, 'Men\'s Flex Training Shorts 7\"', 'AW-M-001', 'Stretch shorts with zip pocket.', 24.90, 47, 2, 'images/mens_flex_shorts.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(9, 'Men\'s Performance Tee', 'AW-M-002', 'Anti-odor knit for everyday training.', 18.90, 62, 2, 'images/mens_performance_tee.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(10, 'Men\'s Compression Top', 'AW-M-003', 'Second-skin base layer for support.', 26.00, 34, 2, 'images/mens_compression_top.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(11, 'Men\'s Tapered Joggers', 'AW-M-004', 'Brushed interior, ankle zips.', 39.00, 28, 2, 'images/mens_tapered_joggers.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(12, 'Men\'s Fleece Hoodie', 'AW-M-005', 'Midweight fleece with kangaroo pocket.', 42.00, 31, 2, 'images/mens_fleece_hoodie.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(13, 'Men\'s Court Polo', 'AW-M-006', 'Breathable knit polo for sport & casual.', 29.50, 26, 2, 'images/mens_court_polo.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(14, 'Men\'s Run Tights', 'AW-M-007', 'Reflective details, phone pocket.', 34.90, 24, 2, 'images/mens_run_tights.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(15, 'Insulated Water Bottle 750ml', 'AW-A-001', 'Double-wall stainless steel bottle.', 16.00, 58, 3, 'images/bottle_750.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(16, 'Aero Cap', 'AW-A-002', 'Lightweight cap with mesh panels.', 14.50, 45, 3, 'images/aero_cap.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(17, 'Cushion Crew Socks 3-Pack', 'AW-A-003', 'Arch support and breathable mesh.', 12.90, 73, 3, 'images/crew_socks_3pk.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(18, 'Convertible Gym Bag 35L', 'AW-A-004', 'Duffel-to-backpack with shoe garage.', 39.90, 27, 3, 'images/gym_bag_35l.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(19, 'Grip Yoga Mat 5mm', 'AW-A-005', 'Non-slip TPE surface, carry strap.', 24.00, 32, 3, 'images/yoga_mat_5mm.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35'),
(20, 'Sweat Wristbands 2-Pack', 'AW-A-006', 'Absorbent terry wristbands.', 8.50, 66, 3, 'images/wristbands_2pk.jpg', '2025-09-22 15:08:35', '2025-09-22 15:08:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `customers_email_unique` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_orders` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
