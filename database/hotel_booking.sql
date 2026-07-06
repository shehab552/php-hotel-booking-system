-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 16 يناير 2026 الساعة 00:02
-- إصدار الخادم: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_booking`
--

-- --------------------------------------------------------

--
-- بنية الجدول `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_nights` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `guests` int(11) DEFAULT 1,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'pay_at_hotel',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `hotel_id`, `check_in`, `check_out`, `total_nights`, `total_price`, `guests`, `status`, `special_requests`, `payment_method`, `booking_date`) VALUES
(21, 6, 6, 5, '2025-12-21', '2025-12-23', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:27:40'),
(22, 6, 6, 5, '2025-12-21', '2025-12-23', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:41:51'),
(23, 6, 6, 5, '2025-12-21', '2025-12-23', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:42:29'),
(24, 6, 6, 5, '2025-12-21', '2025-12-23', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:42:34'),
(25, 6, 1, 1, '2025-12-21', '2025-12-23', 2, 700.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:48:41'),
(26, 6, 1, 1, '2025-12-21', '2025-12-23', 2, 700.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:48:46'),
(27, 6, 1, 1, '2025-12-21', '2025-12-23', 2, 700.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:49:00'),
(28, 6, 1, 1, '2025-12-21', '2025-12-23', 2, 700.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:49:09'),
(29, 6, 1, 1, '2025-12-21', '2025-12-23', 2, 700.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:49:21'),
(30, 6, 2, 1, '2025-12-21', '2025-12-23', 2, 1200.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:49:37'),
(31, 6, 2, 1, '2025-12-21', '2025-12-23', 2, 1200.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:49:42'),
(32, 6, 10, 9, '2025-12-21', '2025-12-23', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 20:50:24'),
(33, 6, 10, 9, '2025-12-22', '2025-12-24', 2, 1000.00, 1, 'confirmed', '', 'pay_at_hotel', '2025-12-20 21:01:03'),
(34, 6, 10, 9, '2025-12-22', '2025-12-24', 2, 1000.00, 1, 'pending', '', 'pay_at_hotel', '2025-12-20 21:04:21'),
(35, 6, 9, 8, '2025-12-22', '2025-12-24', 2, 760.00, 1, 'cancelled', '', 'pay_at_hotel', '2025-12-20 21:38:44'),
(36, 5, 2, 1, '2026-01-17', '2026-01-19', 2, 1200.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:03:59'),
(45, 5, 10, 9, '2026-01-17', '2026-01-19', 2, 1000.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:06:34'),
(46, 5, 10, 9, '2026-01-17', '2026-01-19', 2, 1000.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:07:22'),
(50, 5, 5, 4, '2026-01-17', '2026-01-19', 2, 1500.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:19:13'),
(51, 5, 1, 1, '2025-12-22', '2025-12-24', 2, 700.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:21:09'),
(52, 5, 1, 1, '2025-12-22', '2025-12-24', 2, 700.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:24:19'),
(53, 5, 1, 1, '2025-12-22', '2025-12-24', 2, 700.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:24:26'),
(54, 5, 5, 4, '2026-01-17', '2026-01-19', 2, 1500.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:24:40'),
(55, 5, 1, 1, '2025-12-22', '2025-12-24', 2, 700.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:25:21'),
(58, 5, 3, 2, '2026-01-17', '2026-01-19', 2, 800.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:25:59'),
(59, 5, 3, 2, '2026-01-17', '2026-01-19', 2, 800.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:38:05'),
(60, 5, 3, 2, '2026-01-17', '2026-01-19', 2, 800.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:46:19'),
(61, 5, 3, 2, '2026-01-17', '2026-01-19', 2, 800.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:46:23'),
(64, 5, 8, 7, '2026-01-17', '2026-01-18', 1, 1200.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 21:52:45'),
(65, 17, 4, 3, '2026-01-17', '2026-01-18', 1, 200.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 22:07:59'),
(66, 17, 4, 3, '2026-01-17', '2026-01-18', 1, 200.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 22:11:58'),
(67, 17, 6, 5, '2026-01-17', '2026-01-18', 1, 500.00, 1, 'pending', '', 'pay_at_hotel', '2026-01-15 22:29:03');

-- --------------------------------------------------------

--
-- بنية الجدول `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `star_rating` int(11) DEFAULT 3,
  `manager_id` int(11) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `description`, `address`, `city`, `country`, `star_rating`, `manager_id`, `images`, `amenities`, `is_active`, `created_at`) VALUES
(1, 'فندق السلام', 'فندق راقي يقع في وسط المدينة', 'شارع التحرير', 'الرياض', 'السعودية', 5, 1, 'hotel1.jpg', 'مسبح, واي فاي, مطعم', 1, '2025-12-19 21:30:40'),
(2, 'فندق الورود', 'فندق اقتصادي مناسب للعائلات', 'شارع الملك فهد', 'جدة', 'السعودية', 4, 1, 'hotel2.jpg', 'حديقة, مواقف سيارات', 1, '2025-12-19 21:30:40'),
(3, 'فندق الرمال', 'إقامة مريحة وأسعار مميزة', 'شارع الخليج', 'الدمام', 'السعودية', 3, 1, 'hotel3.jpg', 'واي فاي, إفطار مجاني', 1, '2025-12-19 21:30:40'),
(4, 'فندق المها', 'إطلالة مميزة وخدمات عالية', 'طريق المطار', 'أبها', 'السعودية', 4, 1, 'hotel4.jpg', 'سبا, مسبح, مطعم', 1, '2025-12-19 21:30:40'),
(5, 'فندق اليمن الدولي', 'فندق مميز قريب من الخدمات', 'شارع تعز', 'صنعاء', 'اليمن', 5, 1, 'hotel5.jpg', 'مسبح, ساونا', 1, '2025-12-19 21:30:40'),
(6, 'فندق عدن جراند', 'مناسب للسياحة والاستجمام', 'كورنيش عدن', 'عدن', 'اليمن', 4, 1, 'hotel6.jpg', 'مسبح, مطعم', 1, '2025-12-19 21:30:40'),
(7, 'فندق الخليج', 'تصميم حديث وموقع ممتاز', 'شارع الخليج', 'الدوحة', 'قطر', 5, 1, 'hotel7.jpg', 'واي فاي, قاعة مؤتمرات', 1, '2025-12-19 21:30:40'),
(8, 'فندق المنارة', 'فندق عائلي قريب من البحر', 'شارع البحر', 'مسقط', 'عمان', 3, 1, 'hotel8.jpg', 'شاطئ, مطعم', 1, '2025-12-19 21:30:40'),
(9, 'فندق برج الجزيرة', 'فندق فاخر بإطلالة بانورامية', 'طريق الملك عبدالله', 'الرياض', 'السعودية', 5, 1, 'hotel9.jpg', 'سبا, مسبح داخلي', 1, '2025-12-19 21:30:40'),
(10, 'فندق القمة', 'غرف واسعة وخدمة ممتازة', 'شارع العليا', 'الرياض', 'السعودية', 4, 1, 'hotel10.jpg', 'مطعم, نادي رياضي', 1, '2025-12-19 21:30:40');

-- --------------------------------------------------------

--
-- بنية الجدول `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `review_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `reviews`
--

INSERT INTO `reviews` (`id`, `hotel_id`, `user_id`, `rating`, `comment`, `created_at`, `review_date`) VALUES
(17, 1, 6, 5, 'فندق ممتاز جدًا، خدمة رائعة!', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(18, 1, 8, 4, 'تجربة جيدة جدًا ولكن السعر مرتفع قليلاً', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(19, 2, 9, 3, 'فندق متوسط، مناسب للعائلات لكن يحتاج تحسين', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(20, 3, 5, 4, 'إقامة جميلة ومريحة', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(21, 5, 6, 5, 'أفضل فندق في اليمن!', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(22, 6, 8, 4, 'غرف مريحة جداً وإطلالة جميلة', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(23, 9, 9, 5, 'فندق فاخر وإطلالة بانورامية رائعة', '2025-12-20 19:13:24', '2025-12-20 22:15:51'),
(24, 10, 13, 3, 'جيد ولكن الخدمات بطيئة قليلاً', '2025-12-20 19:13:24', '2025-12-20 22:15:51');

-- --------------------------------------------------------

--
-- بنية الجدول `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `room_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `total_rooms` int(11) NOT NULL DEFAULT 1,
  `available_rooms` int(11) NOT NULL DEFAULT 1,
  `amenities` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `rooms`
--

INSERT INTO `rooms` (`id`, `hotel_id`, `room_number`, `room_type`, `description`, `price_per_night`, `capacity`, `total_rooms`, `available_rooms`, `amenities`, `images`, `created_at`) VALUES
(1, 1, NULL, 'مزدوجة', 'غرفة مزدوجة مريحة', 350.00, 2, 5, 30, NULL, NULL, '2025-12-19 21:30:58'),
(2, 1, NULL, 'فاخرة', 'غرفة فاخرة بإطلالة', 600.00, 2, 3, 30, NULL, NULL, '2025-12-19 21:30:58'),
(3, 2, NULL, 'عائلية', 'غرفة عائلية واسعة', 400.00, 4, 4, 30, NULL, NULL, '2025-12-19 21:30:58'),
(4, 3, NULL, 'مفردة', 'غرفة اقتصادية مفردة', 200.00, 1, 6, 30, NULL, NULL, '2025-12-19 21:30:58'),
(5, 4, NULL, 'جناح', 'جناح فاخر', 750.00, 3, 2, 30, NULL, NULL, '2025-12-19 21:30:58'),
(6, 5, NULL, 'فاخرة', 'غرفة ممتازة', 500.00, 2, 4, 30, NULL, NULL, '2025-12-19 21:30:58'),
(7, 6, NULL, 'مزدوجة', 'غرفة مريحة بإطلالة بحرية', 450.00, 2, 5, 30, NULL, NULL, '2025-12-19 21:30:58'),
(8, 7, NULL, 'ملكية', 'جناح ملكي فاخر', 1200.00, 4, 1, 30, NULL, NULL, '2025-12-19 21:30:58'),
(9, 8, NULL, 'عائلية', 'غرفة عائلية خاصة', 380.00, 4, 3, 30, NULL, NULL, '2025-12-19 21:30:58'),
(10, 9, NULL, 'مزدوجة', 'مزدوجة بإطلالة', 500.00, 2, 5, 30, NULL, NULL, '2025-12-19 21:30:58'),
(11, 10, NULL, 'مفردة', 'غرفة مفردة نظيفة', 250.00, 1, 4, 30, NULL, NULL, '2025-12-19 21:30:58');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `user_type` enum('admin','manager','customer') DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT 'default.jpg',
  `birth_date` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `user_type`, `profile_image`, `birth_date`, `phone`, `address`, `is_active`, `created_at`) VALUES
(1, 'admin', 'admin@hotel.com', '$2y$10$YourHashHere', 'المدير العام', 'admin', 'default.jpg', NULL, NULL, NULL, 1, '2025-12-19 00:24:46'),
(5, 'shehab', 'shehabmtwan@gmail.com', '$2y$10$i.wf.zOxMw/HDrt5NGHoa.QLNgWhvIE99rhSjd8wFl8qQmjBVPjfa', 'Admin', 'admin', '69693e39b6dc7_1768504889.jpg', '2025-12-04', NULL, 'sana', 1, '2025-12-19 14:21:53'),
(6, 'ali', 'ali@gmail.com', '$2y$10$rl09Da46ddIvok6FygrY3eALkrEaxyo/1hyxay7Y0eSSEzZMN.4te', 'ali mohomed', 'customer', 'default.jpg', '2007-12-14', '01254596565', 'صنعاء', 1, '2025-12-19 20:07:35'),
(8, 'user2', 'u2@mail.com', '123', 'مستخدم 2', 'customer', 'default.jpg', NULL, NULL, NULL, 1, '2025-12-19 21:31:16'),
(9, 'user3', 'u3@mail.com', '123', 'مستخدم 3', 'customer', 'default.jpg', NULL, NULL, NULL, 1, '2025-12-19 21:31:16'),
(13, 'user11', 'u11@mail.com', '$2y$10$YourHashHere', 'مستخدم 11', 'manager', 'default.jpg', NULL, NULL, NULL, 0, '2025-12-19 21:34:59'),
(14, 'user22', 'u22@mail.com', '$2y$10$YourHashHere', 'مستخدم 22', 'customer', 'default.jpg', NULL, NULL, NULL, 1, '2025-12-19 21:34:59'),
(15, 'user33', 'u33@mail.com', '$2y$10$YourHashHere', 'مستخدم 33', 'customer', 'default.jpg', NULL, NULL, NULL, 1, '2025-12-19 21:34:59'),
(16, 'ayah', 'ayah@gmail.com', '$2y$10$U2GIJz6ML8ItZXoL0iypkOWVU6G4O8lUr2Zrrmn2qn2Yh174tz38q', 'ayah', 'admin', 'default.jpg', '0000-00-00', '', '', 0, '2026-01-15 20:44:12'),
(17, 'ayah1', 'ayah1@gmail.com', '$2y$10$oZiILOP0Z8F1qW.GdXsv7OXar8TCb.ifzGuh4Q9ZU2MJmhrp26BvW', 'ayah', 'admin', 'default.jpg', NULL, NULL, NULL, 1, '2026-01-15 20:47:11'),
(18, 'WW', 'WW@GMAIL.COM', '$2y$10$LRHb7kEGDaQvw2CJCwqlROudAMrUUqYf4MU6JPKPKPLJG/kg.kejO', 'WWW', 'customer', 'default.jpg', '2008-01-10', '454456546', 'sana', 1, '2026-01-15 22:38:13'),
(19, 'WW1', 'WW1@GMAIL.COM', '$2y$10$ffdk0/QRy0ZJJ9dAYHpgZebMVDBkD4t.KxE2o800CSojSUG6Qyv4i', 'WWW', 'customer', 'default.jpg', '2008-01-10', '454456546', 'sana', 1, '2026-01-15 22:44:06'),
(20, 'WW11', 'WW11@GMAIL.COM', '$2y$10$7Ow3tN/KX190VbT8ap8f0.jA2yeY/d4/tWB3IuNO9bsyzpK0s5jJu', 'WWW', 'customer', 'default.jpg', '2008-01-10', '454456546', 'sana', 1, '2026-01-15 22:48:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `hotels_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
