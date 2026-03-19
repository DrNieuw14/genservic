-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 19, 2026 at 01:44 AM
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
(6, '4th Floor');

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
(11, 12, '2026-03-18', '17:19:27', '17:19:37', '01:40:23', 'Late', '2026-03-18 09:19:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--
-- Error reading structure for table genservis_db.inventory: #1932 - Table &#039;genservis_db.inventory&#039; doesn&#039;t exist in engine
-- Error reading data for table genservis_db.inventory: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `genservis_db`.`inventory`&#039; at line 1

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
(1, 'UTL001', 'Utility1', 'Utility Staff', 'Maintenance', 'Admin Office', 'Active', '2026-03-13 23:35:50', 2),
(2, 'UTL002', 'Utility2', 'Utility Staff', 'Maintenance', 'Library', 'Active', '2026-03-13 23:35:50', 3),
(3, 'UTL005', 'Utility5', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-16 01:58:28', 8),
(4, 'UTL004', 'test t test', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-16 02:12:42', 10),
(5, 'UTL005', 'Mak R Abril', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 05:08:45', 11),
(6, 'UTL006', 'Rony M Basilan', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 06:25:03', 12),
(7, 'UTL007', 'Arnold M Last Ar', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 06:48:41', 13),
(8, 'UTL008', 'Marnel M Last M', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 08:27:43', 14),
(9, 'UTL009', 'test2 test2 test2', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 08:39:44', 15),
(10, 'UTL010', 'Aileen Ai Last Ai', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 08:49:06', 16),
(11, 'UTL011', 'arnold M Ar', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 09:11:44', 17),
(12, 'UTL012', 'mak m last', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-18 09:18:43', 18);

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
(1, 1, 'Admin Office'),
(3, 1, 'Library'),
(4, 2, 'Admin Office'),
(5, 3, 'Laboratory'),
(6, 4, 'Building A'),
(7, 1, 'Building A'),
(8, 5, '4th Floor'),
(9, 5, 'Building A'),
(10, 6, 'Building B'),
(11, 6, 'Laboratory'),
(12, 6, 'Library'),
(13, 7, '4th Floor'),
(14, 7, 'Building A'),
(15, 8, '4th Floor'),
(16, 9, '4th Floor'),
(17, 9, 'Building A'),
(18, 10, '4th Floor'),
(19, 10, 'Building B'),
(20, 11, 'Building A'),
(21, 11, 'Building B'),
(22, 11, 'Library'),
(23, 12, 'Admin Office');

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
(18, 'mak', 'm', 'last', 'mak m last', '2020-06-09', 'Male', 'mak', '$2y$10$MayB8wR9tOx6hY.8/IPMJ.Ph4fW57AUuhzCeiElrF8Uf2b9yPOuSG', 'personnel', '2026-03-18 09:18:34', NULL, 'approved');

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
(9, 'Admin Office', 'REST', '2026-03-20', NULL, NULL, 12);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personnel_id` (`personnel_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `work_schedule`
--
ALTER TABLE `work_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  ADD CONSTRAINT `personnel_areas_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
