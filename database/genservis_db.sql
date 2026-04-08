-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 08, 2026 at 08:14 AM
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
  `overtime` time DEFAULT NULL,
  `extra_hours` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `personnel_id`, `date`, `time_in`, `time_out`, `undertime`, `status`, `created_at`, `overtime`, `extra_hours`) VALUES
(101, 22, '2026-04-08', '06:00:00', '18:00:00', NULL, NULL, '2026-04-08 00:10:08', NULL, '04:00:00'),
(102, 21, '2026-04-08', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL),
(103, 21, '2026-04-01', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL),
(104, 21, '2026-04-02', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL),
(105, 21, '2026-04-03', NULL, NULL, NULL, 'Rest Day (No Work)', '2026-04-08 05:46:53', NULL, NULL),
(106, 21, '2026-04-04', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL),
(107, 21, '2026-04-05', NULL, NULL, NULL, 'Rest Day (No Work)', '2026-04-08 05:46:53', NULL, NULL),
(108, 21, '2026-04-06', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL),
(109, 21, '2026-04-07', NULL, NULL, NULL, 'Absent', '2026-04-08 05:46:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `created_at`) VALUES
(1, 5, 'Approved leave ID: 2', 'Leave', '2026-04-08 02:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `cto_summary`
--

CREATE TABLE `cto_summary` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) DEFAULT NULL,
  `total_hours` time DEFAULT NULL,
  `equivalent_days` decimal(5,2) DEFAULT NULL,
  `month` varchar(7) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_hours` decimal(6,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cto_summary`
--

INSERT INTO `cto_summary` (`id`, `personnel_id`, `total_hours`, `equivalent_days`, `month`, `status`, `created_at`, `used_hours`) VALUES
(1, 22, '08:00:00', 1.00, '2026-04', 'Approved', '2026-04-08 00:11:56', 8.00),
(3, 21, '16:00:00', 2.00, '2026-04', 'Pending', '2026-04-08 01:46:38', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Liquident Cleaners', 'All-Purpose Cleaner, Disinfectant Solution, Glass Cleaner, Floor Cleaner, Toilet Bowl Cleaner', '2026-03-26 06:56:53'),
(2, 'Soaps & Detergents', 'Liquid Soap\r\nDishwashing Liquid\r\nLaundry Detergent (Powder)\r\nFabric Conditioner', '2026-03-26 06:57:15'),
(3, 'Cleaning Tools', 'Sponge\r\nScrub Brush\r\nBroom (Soft/Hard)\r\nDustpan\r\nMop (Wet/Dry)', '2026-03-26 06:57:25'),
(4, 'Paper & Wipes', 'Tissue Paper\r\nPaper Towels\r\nWet Wipes\r\nDisinfectant Wipes', '2026-03-26 06:57:34');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unit_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `category_id`, `quantity`, `min_stock`, `created_at`, `unit_id`) VALUES
(1, 'walis', NULL, 5, 5, '2026-03-23 09:44:42', 1),
(2, 'kingrox', 1, 4, 5, '2026-03-26 06:58:16', 6),
(3, 'surf', 2, 4, 5, '2026-03-26 07:06:48', 1),
(4, 'tide', 2, 3, 5, '2026-03-26 07:13:02', 1),
(5, 'surf', 1, 5, 5, '2026-03-27 08:23:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `item_id`, `action`, `quantity`, `user_id`, `created_at`, `request_id`) VALUES
(1, 3, 'added', 5, 5, '2026-03-26 07:06:48', NULL),
(2, 4, 'added', 3, 5, '2026-03-26 07:13:02', NULL),
(3, 5, 'added', 5, 5, '2026-03-27 08:23:16', NULL),
(4, 2, 'updated', 5, 5, '2026-03-27 08:32:52', NULL),
(5, 2, 'updated', 5, 5, '2026-03-27 08:38:13', NULL),
(6, 2, 'deducted', 1, 5, '2026-03-30 03:06:23', NULL),
(7, 3, 'deducted', 1, 5, '2026-03-30 03:06:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_requests`
--

CREATE TABLE `inventory_requests` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `request_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_requests`
--

INSERT INTO `inventory_requests` (`id`, `personnel_id`, `status`, `request_date`, `approved_by`, `approved_at`, `rejection_reason`) VALUES
(1, 21, 'rejected', '2026-03-23', NULL, NULL, NULL),
(2, 21, 'Rejected', '2026-03-30', 5, '2026-03-30 10:03:49', 'wa'),
(3, 21, 'Rejected', '2026-03-30', 5, '2026-03-30 10:52:49', 'none!'),
(4, 21, 'Rejected', '2026-03-30', 5, '2026-03-30 10:52:57', 'isa lang'),
(5, 21, 'Rejected', '2026-03-30', 5, '2026-03-30 10:53:02', 'tama na!'),
(6, 21, 'Approved', '2026-03-30', 5, '2026-03-30 11:06:23', NULL),
(8, 21, 'Pending', '2026-03-30', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_request_items`
--

CREATE TABLE `inventory_request_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_request_items`
--

INSERT INTO `inventory_request_items` (`id`, `request_id`, `item_id`, `quantity`) VALUES
(1, 2, 2, 1),
(2, 2, 3, 1),
(3, 3, 2, 1),
(4, 3, 3, 1),
(5, 4, 2, 1),
(6, 4, 3, 1),
(7, 5, 2, 1),
(8, 5, 3, 1),
(9, 6, 2, 1),
(10, 6, 3, 1),
(11, 8, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_units`
--

CREATE TABLE `inventory_units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `abbreviation` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_units`
--

INSERT INTO `inventory_units` (`id`, `unit_name`, `abbreviation`, `created_at`) VALUES
(1, 'pcs', '', '2026-03-27 08:10:37'),
(2, 'boxes', '', '2026-03-27 08:10:37'),
(3, 'bottles', '', '2026-03-27 08:10:37'),
(4, 'liters', '', '2026-03-27 08:10:37'),
(5, 'ml', '', '2026-03-27 08:10:37'),
(6, 'kg', '', '2026-03-27 08:10:37'),
(7, 'grams', '', '2026-03-27 08:10:37'),
(8, 'gallon', '', '2026-03-27 08:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) DEFAULT NULL,
  `requested_days` decimal(5,2) DEFAULT NULL,
  `equivalent_hours` time DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `personnel_id`, `requested_days`, `equivalent_hours`, `reason`, `status`, `approved_by`, `created_at`, `approved_at`) VALUES
(1, 22, 1.00, '08:00:00', 'bday', 'Approved', 5, '2026-04-08 02:32:28', '2026-04-08 02:42:35'),
(2, 22, 1.00, '08:00:00', 'bday again', 'Approved', 5, '2026-04-08 02:43:28', '2026-04-08 02:45:23');

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
(21, 'UTL20260323681', 'Test6 M Banan', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-03-23 05:09:02', 24),
(22, 'UTL20260408581', 'Aldrin B Justimbaste', 'Utility Staff', 'Maintenance', NULL, 'Active', '2026-04-08 00:01:23', 25);

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
(33, 22, '4th Floor'),
(34, 22, 'Building A'),
(35, 22, 'Building B'),
(36, 21, 'Library');

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
(24, 'Test6', 'M', 'Banan', 'Test6 M Banan', '1987-09-12', 'Male', 'test6', '$2y$10$zVM6rBMsi4ll1XRtd97uquCtcQv/UU/1y1gj3w2lyxl.k4H8YdvR2', 'personnel', '2026-03-23 05:09:02', NULL, 'approved'),
(25, 'Aldrin', 'B', 'Justimbaste', 'Aldrin B Justimbaste', '1996-01-12', 'Male', 'aldrin', '$2y$10$ICU4ajCCPVHIzk33a//83.6FJfmysQKjgmINqsIVRf0rWHDmGu9H6', 'personnel', '2026-04-08 00:01:23', NULL, 'approved');

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
(59, 'Building A, Building B, Library', 'REST', '2026-03-27', NULL, NULL, 11),
(60, 'Admin Office', 'Morning', '2026-03-31', '06:00:00', '15:00:00', 21),
(61, 'Admin Office', 'Morning', '2026-04-01', '06:00:00', '15:00:00', 21),
(62, 'Admin Office', 'Morning', '2026-04-02', '06:00:00', '15:00:00', 21),
(63, 'Admin Office', 'REST', '2026-04-03', NULL, NULL, 21),
(64, 'Admin Office', 'Morning', '2026-04-04', '06:00:00', '15:00:00', 21),
(65, 'Admin Office', 'REST', '2026-04-05', NULL, NULL, 21),
(66, 'Admin Office', 'Morning', '2026-04-06', '06:00:00', '15:00:00', 21),
(67, 'Admin Office', 'Morning', '2026-04-07', '06:00:00', '15:00:00', 21),
(68, 'Admin Office', 'Morning', '2026-04-08', '06:00:00', '15:00:00', 21),
(69, 'Admin Office', 'Morning', '2026-04-09', '06:00:00', '15:00:00', 21),
(70, 'Admin Office', 'REST', '2026-04-10', NULL, NULL, 21),
(71, 'Admin Office', 'Morning', '2026-04-11', '06:00:00', '15:00:00', 21),
(72, 'Admin Office', 'REST', '2026-04-12', NULL, NULL, 21),
(73, 'Admin Office', 'Morning', '2026-04-13', '06:00:00', '15:00:00', 21),
(74, 'Admin Office', 'Morning', '2026-04-14', '06:00:00', '15:00:00', 21),
(75, 'Admin Office', 'Morning', '2026-04-15', '06:00:00', '15:00:00', 21),
(76, 'Admin Office', 'Morning', '2026-04-16', '06:00:00', '15:00:00', 21),
(77, 'Admin Office', 'REST', '2026-04-17', NULL, NULL, 21),
(78, 'Admin Office', 'Morning', '2026-04-18', '06:00:00', '15:00:00', 21),
(79, 'Admin Office', 'REST', '2026-04-19', NULL, NULL, 21),
(80, 'Admin Office', 'Morning', '2026-04-20', '06:00:00', '15:00:00', 21),
(81, 'Admin Office', 'Morning', '2026-04-21', '06:00:00', '15:00:00', 21),
(82, 'Admin Office', 'Morning', '2026-04-22', '06:00:00', '15:00:00', 21),
(83, 'Admin Office', 'Morning', '2026-04-23', '06:00:00', '15:00:00', 21),
(84, 'Admin Office', 'REST', '2026-04-24', NULL, NULL, 21),
(85, 'Admin Office', 'Morning', '2026-04-25', '06:00:00', '15:00:00', 21),
(86, 'Admin Office', 'REST', '2026-04-26', NULL, NULL, 21),
(87, 'Admin Office', 'Morning', '2026-04-27', '06:00:00', '15:00:00', 21),
(88, 'Admin Office', 'Morning', '2026-04-28', '06:00:00', '15:00:00', 21),
(89, 'Admin Office', 'Morning', '2026-04-29', '06:00:00', '15:00:00', 21),
(90, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-07', '08:00:00', '17:00:00', 22),
(91, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-08', '08:00:00', '17:00:00', 22),
(92, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-09', '08:00:00', '17:00:00', 22),
(93, 'Building B, Building A, 4th Floor', 'REST', '2026-04-10', NULL, NULL, 22),
(94, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-11', '08:00:00', '17:00:00', 22),
(95, 'Building B, Building A, 4th Floor', 'REST', '2026-04-12', NULL, NULL, 22),
(96, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-13', '08:00:00', '17:00:00', 22),
(97, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-14', '08:00:00', '17:00:00', 22),
(98, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-15', '08:00:00', '17:00:00', 22),
(99, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-16', '08:00:00', '17:00:00', 22),
(100, 'Building B, Building A, 4th Floor', 'REST', '2026-04-17', NULL, NULL, 22),
(101, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-18', '08:00:00', '17:00:00', 22),
(102, 'Building B, Building A, 4th Floor', 'REST', '2026-04-19', NULL, NULL, 22),
(103, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-20', '08:00:00', '17:00:00', 22),
(104, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-21', '08:00:00', '17:00:00', 22),
(105, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-22', '08:00:00', '17:00:00', 22),
(106, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-23', '08:00:00', '17:00:00', 22),
(107, 'Building B, Building A, 4th Floor', 'REST', '2026-04-24', NULL, NULL, 22),
(108, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-25', '08:00:00', '17:00:00', 22),
(109, 'Building B, Building A, 4th Floor', 'REST', '2026-04-26', NULL, NULL, 22),
(110, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-27', '08:00:00', '17:00:00', 22),
(111, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-28', '08:00:00', '17:00:00', 22),
(112, 'Building B, Building A, 4th Floor', '2nd Shift', '2026-04-29', '08:00:00', '17:00:00', 22);

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
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cto_summary`
--
ALTER TABLE `cto_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cto` (`personnel_id`,`month`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`),
  ADD KEY `fk_unit` (`unit_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory_units`
--
ALTER TABLE `inventory_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cto_summary`
--
ALTER TABLE `cto_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory_units`
--
ALTER TABLE `inventory_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `personnel_areas`
--
ALTER TABLE `personnel_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `work_schedule`
--
ALTER TABLE `work_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit` FOREIGN KEY (`unit_id`) REFERENCES `inventory_units` (`id`);

--
-- Constraints for table `inventory_requests`
--
ALTER TABLE `inventory_requests`
  ADD CONSTRAINT `inventory_requests_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`);

--
-- Constraints for table `inventory_request_items`
--
ALTER TABLE `inventory_request_items`
  ADD CONSTRAINT `inventory_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `inventory_requests` (`id`),
  ADD CONSTRAINT `inventory_request_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

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
