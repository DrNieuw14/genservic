-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 23, 2026 at 10:35 AM
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
-- Database: `genservis_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `area_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `area_name`) VALUES
(1, 'Building A'),
(2, 'Building B'),
(3, 'Laboratory'),
(4, 'Library'),
(5, 'Admin Office'),
(6, '4th Floor'),
(7, 'Campus Ground');

-- --------------------------------------------------------

--
-- Table structure for table `area_history`
--

CREATE TABLE `area_history` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) DEFAULT NULL,
  `area_name` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `undertime` time DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `overtime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `personnel_id`, `date`, `time_in`, `time_out`, `undertime`, `status`, `created_at`, `overtime`) VALUES
(1, 2, '2026-03-14', '01:03:59', '01:45:12', '15:14:48', 'Present', '2026-03-14 00:03:59', NULL),
(2, 1, '2026-03-14', '01:13:14', '01:24:23', NULL, 'Present', '2026-03-14 00:13:14', NULL),
(3, 1, '2026-03-18', '03:10:14', '03:10:19', '13:49:41', 'Present', '2026-03-18 02:10:14', NULL),
(4, 2, '2026-03-18', '04:20:31', '04:20:39', '12:39:21', 'Present', '2026-03-18 03:20:31', NULL),
(5, 5, '2026-03-18', '06:18:03', '06:18:06', '10:41:54', 'Present', '2026-03-18 05:18:03', NULL),
(6, 6, '2026-03-18', '07:25:38', NULL, NULL, 'Present', '2026-03-18 06:25:38', NULL),
(7, 7, '2026-03-18', '07:49:46', '09:16:42', '09:43:18', 'Present', '2026-03-18 06:49:46', NULL),
(8, 8, '2026-03-18', '09:28:51', '09:29:28', '05:30:32', 'Late', '2026-03-18 08:28:51', NULL),
(9, 10, '2026-03-18', '09:55:31', '10:03:02', '08:56:58', 'Early', '2026-03-18 08:55:31', NULL),
(10, 11, '2026-03-18', '10:12:49', '17:15:58', '01:44:02', 'Late', '2026-03-18 09:12:49', NULL),
(11, 12, '2026-03-18', '17:19:27', '17:19:37', '01:40:23', 'Late', '2026-03-18 09:19:27', NULL),
(12, 12, '2026-03-19', '09:09:34', '09:14:39', '09:45:21', 'Early', '2026-03-19 01:09:34', NULL),
(13, 13, '2026-03-19', '14:01:26', '14:01:31', '00:58:29', 'Late', '2026-03-19 06:01:26', NULL),
(14, 15, '2026-03-19', '15:02:07', '15:12:32', NULL, 'Late', '2026-03-19 07:02:07', '00:12:32'),
(15, 19, '2026-03-19', '15:29:48', NULL, NULL, 'Late', '2026-03-19 07:29:48', NULL),
(16, 20, '2026-03-23', '08:18:34', NULL, NULL, 'Late', '2026-03-23 00:18:34', NULL),
(17, 21, '2026-03-23', '13:10:23', NULL, NULL, 'Late', '2026-03-23 05:10:23', NULL),
(18, 11, '2026-03-23', '13:11:58', '13:13:06', '05:46:54', 'No Schedule', '2026-03-23 05:11:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit` varchar(20) DEFAULT NULL,
  `assigned_personnel_id` int(11) DEFAULT NULL,
  `min_stock` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `area_name` varchar(100) DEFAULT NULL,
  `quantity_used` int(11) DEFAULT NULL,
  `log_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_requests`
--

CREATE TABLE `inventory_requests` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `request_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--
-- Error reading structure for table genservis_db.leave_requests: #1932 - Table &#039;genservis_db.leave_requests&#039; doesn&#039;t exist in engine
-- Error reading data for table genservis_db.leave_requests: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `genservis_db`.`leave_requests`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `assigned_area` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `employee_id`, `fullname`, `position`, `department`, `assigned_area`, `status`, `created_at`, `user_id`) VALUES
(10, 'UTL010', 'Aileen Ai Last Ai', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 08:49:06', 16),
(11, 'UTL011', 'arnold M Ar', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 09:11:44', 17),
(12, 'UTL012', 'mak m last', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 09:18:43', 18),
(13, 'UTL850', 'mak2 mak2 aw', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-19 02:20:08', 19),
(15, 'UTL20260319746', 'Test R Ltest', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-19 06:42:27', 20),
(17, 'UTL20260319755', 'Test3 M Ltest3', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-19 07:23:23', 21),
(19, 'UTL20260319871', 'Test4 M Ltest4', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-19 07:28:07', 22),
(20, 'UTL20260323816', 'Test5 M Lastt5', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-23 00:16:50', 23),
(21, 'UTL20260323681', 'Test6 M Banan', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-23 05:09:02', 24);

-- --------------------------------------------------------

--
-- Table structure for table `personnel_areas`
--

CREATE TABLE `personnel_areas` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `area_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personnel_areas`
--

INSERT INTO `personnel_areas` (`id`, `personnel_id`, `area_name`) VALUES
(18, 10, '4th Floor'),
(20, 11, 'Building A'),
(21, 11, 'Building B'),
(22, 11, 'Library'),
(23, 12, 'Admin Office'),
(24, 13, 'Admin Office'),
(26, 15, 'Campus Ground'),
(27, 19, 'Campus Ground'),
(28, 19, 'Laboratory'),
(29, 17, '4th Floor'),
(31, 20, 'Laboratory'),
(32, 21, 'Admin Office');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_weekly_schedule`
--

CREATE TABLE `personnel_weekly_schedule` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `shift` enum('shift1','shift2','shift3') DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `is_restday` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shift_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `personnel_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_initial`, `last_name`, `fullname`, `birthdate`, `gender`, `username`, `password`, `role`, `created_at`, `personnel_id`, `status`) VALUES
(1, 'System', 'A', 'Administrator', 'System Administrator', '2000-01-01', 'Male', 'admin', '$2y$10$0SyQopuv5Yzu2djunfJGyuMNLLR2VZcgS0EyyMt07ZfuIk0H.BFkS', 'supervisor', '2026-03-13 11:25:35', NULL, 'approved'),
(5, 'System', '', 'Supervisor', 'System Supervisor', '2000-01-01', 'Male', 'supervisor', '$2y$10$0SyQopuv5Yzu2djunfJGyuMNLLR2VZcgS0EyyMt07ZfuIk0H.BFkS', 'supervisor', '2026-03-14 02:15:25', NULL, 'approved'),
(16, 'Aileen', 'Ai', 'Last Ai', 'Aileen Ai Last Ai', '1951-05-05', 'Female', 'aileen', '$2y$10$Bma.17oMFV.Whr8qPW07f.h01Xb2mJCUYCvHW3r.88vLtcTLNQR1G', 'personnel', '2026-03-18 08:48:51', NULL, 'approved'),
(17, 'arnold', 'M', 'Ar', 'arnold M Ar', '1985-10-06', 'Male', 'arnold', '$2y$10$7INuu.LSJQx1Hrzo.df4J.yie/zYPuyBJfEXP.w7Gb9FengXqRmBK', 'personnel', '2026-03-18 09:11:32', NULL, 'approved'),
(18, 'mak', 'm', 'last', 'mak m last', '2020-06-09', 'Male', 'mak', '$2y$10$MayB8wR9tOx6hY.8/IPMJ.Ph4fW57AUuhzCeiElrF8Uf2b9yPOuSG', 'personnel', '2026-03-18 09:18:34', NULL, 'approved'),
(19, 'mak2', 'mak2', 'aw', 'mak2 mak2 aw', '1985-02-12', 'Male', 'mak2', '$2y$10$tgfQTIskDhKRTi8D7Inp9usLr5.6fioKIO7O3.VBcpQvusaVVU4iy', 'personnel', '2026-03-19 02:20:08', NULL, 'approved'),
(20, 'Test', 'R', 'Ltest', 'Test R Ltest', '2000-06-02', 'Male', 'test', '$2y$10$OI8NRVCv.Fp5bO1kff3sYe0zk7BsuEZUQ2QhCRX9ucZ/MSrbT5IgO', 'personnel', '2026-03-19 06:42:27', NULL, 'approved'),
(21, 'Test3', 'M', 'Ltest3', 'Test3 M Ltest3', '2000-05-26', 'Male', 'test3', '$2y$10$daNSuDJhTfk/d.inisuYbeuSGHNawHdb0/MSwbvTy.z8Vj84zaBIy', 'personnel', '2026-03-19 07:23:23', NULL, 'approved'),
(22, 'Test4', 'M', 'Ltest4', 'Test4 M Ltest4', '2000-06-05', 'Male', 'test4', '$2y$10$GHBrlSkjaUkyuUPwkbikpuMsKJ3fil8qV/Mzd1sjbI9hb4sUz/6N6', 'personnel', '2026-03-19 07:28:07', NULL, 'approved'),
(23, 'Test5', 'M', 'Lastt5', 'Test5 M Lastt5', '2000-06-15', 'Male', 'test5', '$2y$10$.lq7wvTshZfZUi/HFIxQu.kZR0md5txOugq.yXslPMre4gY85Jj7y', 'personnel', '2026-03-23 00:16:50', NULL, 'approved'),
(24, 'Test6', 'M', 'Banan', 'Test6 M Banan', '1987-09-12', 'Male', 'test6', '$2y$10$zVM6rBMsi4ll1XRtd97uquCtcQv/UU/1y1gj3w2lyxl.k4H8YdvR2', 'personnel', '2026-03-23 05:09:02', NULL, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedule`
--

CREATE TABLE `work_schedule` (
  `id` int(11) NOT NULL,
  `work_area` varchar(255) DEFAULT NULL,
  `shift` varchar(100) DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `personnel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_schedule`
--

INSERT INTO `work_schedule` (`id`, `work_area`, `shift`, `schedule_date`, `time_in`, `time_out`, `personnel_id`) VALUES
(1, '4th Floor, Building B', '3rd Shift', '2026-03-18', '10:00:00', '19:00:00', 10),
(2, '4th Floor, Building B', '3rd Shift', '2026-03-19', '10:00:00', '19:00:00', 10),
(3, '4th Floor, Building B', '3rd Shift', '2026-03-20', '10:00:00', '19:00:00', 10),
(4, 'Library, Building B, Building A', '3rd Shift', '2026-03-18', '10:00:00', '19:00:00', 11),
(5, 'Library, Building B, Building A', '3rd Shift', '2026-03-19', '10:00:00', '19:00:00', 11),
(6, 'Library, Building B, Building A', '3rd Shift', '2026-03-20', '10:00:00', '19:00:00', 11),
(7, 'Admin Office', '3rd Shift', '2026-03-18', '10:00:00', '19:00:00', 12),
(8, 'Admin Office', '3rd Shift', '2026-03-19', '10:00:00', '19:00:00', 12),
(9, 'Admin Office', 'REST', '2026-03-20', NULL, NULL, 12),
(10, 'Admin Office', 'Morning', '2026-03-19', '06:00:00', '15:00:00', 13),
(11, 'Admin Office', 'REST', '2026-03-20', NULL, NULL, 13),
(12, 'Admin Office', 'Morning', '2026-03-21', '06:00:00', '15:00:00', 13),
(13, 'Admin Office', 'REST', '2026-03-22', NULL, NULL, 13),
(14, 'Admin Office', 'Morning', '2026-03-23', '06:00:00', '15:00:00', 13),
(15, 'Admin Office', 'Morning', '2026-03-24', '06:00:00', '15:00:00', 13),
(16, 'Admin Office', 'Morning', '2026-03-25', '06:00:00', '15:00:00', 13),
(17, 'Admin Office', 'Morning', '2026-03-26', '06:00:00', '15:00:00', 13),
(18, 'Admin Office', 'REST', '2026-03-27', NULL, NULL, 13),
(19, 'Admin Office', 'Morning', '2026-03-28', '06:00:00', '15:00:00', 13),
(20, 'Admin Office', 'REST', '2026-03-29', NULL, NULL, 13),
(21, 'Admin Office', 'Morning', '2026-03-30', '06:00:00', '15:00:00', 13),
(22, 'Campus Ground', 'Morning', '2026-03-18', '06:00:00', '15:00:00', 15),
(23, 'Campus Ground', 'Morning', '2026-03-19', '06:00:00', '15:00:00', 15),
(24, 'Campus Ground', 'REST', '2026-03-20', NULL, NULL, 15),
(25, 'Campus Ground', 'Morning', '2026-03-21', '06:00:00', '15:00:00', 15),
(26, 'Campus Ground', 'REST', '2026-03-22', NULL, NULL, 15),
(27, 'Campus Ground', 'Morning', '2026-03-23', '06:00:00', '15:00:00', 15),
(28, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-18', '10:00:00', '19:00:00', 19),
(29, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-19', '10:00:00', '19:00:00', 19),
(30, 'Campus Ground, Laboratory', 'REST', '2026-03-20', NULL, NULL, 19),
(31, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-21', '10:00:00', '19:00:00', 19),
(32, 'Campus Ground, Laboratory', 'REST', '2026-03-22', NULL, NULL, 19),
(33, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-23', '10:00:00', '19:00:00', 19),
(34, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-24', '10:00:00', '19:00:00', 19),
(35, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-25', '10:00:00', '19:00:00', 19),
(36, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-26', '10:00:00', '19:00:00', 19),
(37, 'Campus Ground, Laboratory', 'REST', '2026-03-27', NULL, NULL, 19),
(38, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-28', '10:00:00', '19:00:00', 19),
(39, 'Campus Ground, Laboratory', 'REST', '2026-03-29', NULL, NULL, 19),
(40, 'Campus Ground, Laboratory', '3rd Shift', '2026-03-30', '10:00:00', '19:00:00', 19),
(41, 'Laboratory', 'REST', '2026-03-22', NULL, NULL, 20),
(42, 'Laboratory', 'Morning', '2026-03-23', '06:00:00', '15:00:00', 20),
(43, 'Laboratory', 'Morning', '2026-03-24', '06:00:00', '15:00:00', 20),
(44, 'Laboratory', 'Morning', '2026-03-25', '06:00:00', '15:00:00', 20),
(45, 'Laboratory', 'Morning', '2026-03-26', '06:00:00', '15:00:00', 20),
(46, 'Laboratory', 'REST', '2026-03-27', NULL, NULL, 20),
(47, 'Laboratory', 'Morning', '2026-03-28', '06:00:00', '15:00:00', 20),
(48, 'Admin Office', '3rd Shift', '2026-03-22', '10:00:00', '19:00:00', 21),
(49, 'Admin Office', '3rd Shift', '2026-03-23', '10:00:00', '19:00:00', 21),
(50, 'Admin Office', '3rd Shift', '2026-03-24', '10:00:00', '19:00:00', 21),
(51, 'Admin Office', '3rd Shift', '2026-03-25', '10:00:00', '19:00:00', 21),
(52, 'Admin Office', '3rd Shift', '2026-03-26', '10:00:00', '19:00:00', 21),
(53, 'Admin Office', 'REST', '2026-03-27', NULL, NULL, 21),
(54, 'Building A, Building B, Library', 'REST', '2026-03-22', NULL, NULL, 11),
(55, 'Building A, Building B, Library', '3rd Shift', '2026-03-23', '10:00:00', '19:00:00', 11),
(56, 'Building A, Building B, Library', '3rd Shift', '2026-03-24', '10:00:00', '19:00:00', 11),
(57, 'Building A, Building B, Library', '3rd Shift', '2026-03-25', '10:00:00', '19:00:00', 11),
(58, 'Building A, Building B, Library', '3rd Shift', '2026-03-26', '10:00:00', '19:00:00', 11),
(59, 'Building A, Building B, Library', 'REST', '2026-03-27', NULL, NULL, 11);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `area_history`
--
ALTER TABLE `area_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_personnel_id` (`assigned_personnel_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personnel_id` (`personnel_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personnel_areas_ibfk_1` (`personnel_id`);

--
-- Indexes for table `personnel_weekly_schedule`
--
ALTER TABLE `personnel_weekly_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `work_schedule`
--
ALTER TABLE `work_schedule`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `area_history`
--
ALTER TABLE `area_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `personnel_weekly_schedule`
--
ALTER TABLE `personnel_weekly_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `work_schedule`
--
ALTER TABLE `work_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`assigned_personnel_id`) REFERENCES `personnel` (`id`);

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`);

--
-- Constraints for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD CONSTRAINT `inventory_requests_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`),
  ADD CONSTRAINT `inventory_requests_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `fk_personnel_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  ADD CONSTRAINT `personnel_areas_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
