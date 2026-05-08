-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 07:09 AM
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
-- Database: `rholance_pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `landmark` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slot_id` int(11) DEFAULT NULL,
  `source` enum('Walk-in','Online') DEFAULT 'Online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `customer_name`, `appointment_date`, `appointment_time`, `address`, `landmark`, `contact_person`, `branch_id`, `user_id`, `status`, `created_at`, `slot_id`, `source`) VALUES
(3, 'jim', '2026-04-24', '9:00 pm', 'San Manuel II', 'Dasma west higschool', 'jimmy', 1, 2, 'Pending', '2026-04-06 15:49:05', NULL, 'Online'),
(5, 'simple', '2026-04-10', '9:00 pm', 'San Manuel II', 'Dasma west higschool', 'jimmy', 1, 2, 'Rejected', '2026-04-06 15:54:33', NULL, 'Online'),
(6, 'Angelica', '2026-04-28', '9:00 pm', 'Blk 33 Lot 09 Sta. Maria', 'JESOMA', 'SImple', 1, 2, 'Approved', '2026-04-06 16:45:39', NULL, 'Online'),
(7, 'Simple Perez', '2026-04-14', '5:00 PM', 'Blk 33 Lot 09 Sta. Maria', 'Dasma west higschool', NULL, 1, 3, 'Pending', '2026-04-07 05:34:31', NULL, 'Online'),
(8, 'Simple Perez', '2026-04-15', '9:00 pm', 'Blk 33 Lot 09 Sta. Maria', 'JESOMA', NULL, 1, 3, 'Pending', '2026-04-07 06:02:52', NULL, 'Online'),
(9, 'Simple Perez', '2026-04-21', '09:00 AM', 'Block 33 Lot 8 Sta. Maria, Dasma.', 'Dasma West Highschool', NULL, NULL, 3, 'Pending', '2026-04-21 07:42:49', NULL, 'Online'),
(10, 'Simple Perez', '2026-04-21', '01:00 PM', 'Block 33 Lot 09, Sta. Maria, Dasma.', 'Dasma West Highschool', NULL, NULL, 3, 'Pending', '2026-04-21 07:58:13', NULL, 'Online'),
(11, 'Simple Perez', '2026-04-21', '03:00 PM', 'santa maria', 'Dasma west higschool', NULL, NULL, 3, 'Pending', '2026-04-21 08:29:11', NULL, 'Online'),
(12, 'Simple Perez', '2026-04-21', '09:00 AM', 'Block 33 Lot 09, Sta. Maria, Dasma.', 'Dasma West Highschool', NULL, NULL, 3, 'Pending', '2026-04-21 14:25:52', NULL, 'Online'),
(25, 'simple', '2026-04-08', '9:00 pm', 'Blk 33 Lot 09 Sta. Maria', 'Dasma west higschool', 'jimmy', 1, 2, 'Approved', '2026-04-26 08:24:16', NULL, 'Online'),
(26, 'Simple Perez', '2026-04-26', '09:00 AM', 'Block 33 lot 09', 'school', NULL, NULL, 3, 'Rejected', '2026-04-26 08:27:04', NULL, 'Online'),
(27, 'John Doe', '2026-04-27', '10:00 AM', 'Dasma', 'Dasma west', 'Jimmy', NULL, NULL, 'Approved', '2026-04-26 15:13:31', NULL, 'Online'),
(28, 'Juan Dela Cruz', '2026-04-27', '10:00 AM', NULL, NULL, NULL, 1, NULL, 'Pending', '2026-04-27 02:01:04', NULL, 'Online'),
(29, 'Maria Santos', '2026-04-28', '2:00 PM', NULL, NULL, NULL, 1, NULL, 'Approved', '2026-04-27 02:01:04', NULL, 'Online'),
(30, 'Pedro Reyes', '2026-04-29', '1:00 PM', NULL, NULL, NULL, 1, NULL, 'Pending', '2026-04-27 02:01:04', NULL, 'Online'),
(31, 'Simple Perez', '2026-04-27', '01:00 PM', 'bulacan', 'school', NULL, NULL, 3, '', '2026-04-27 04:31:38', NULL, 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_slots`
--

CREATE TABLE `appointment_slots` (
  `id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(50) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('Available','Booked') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_slots`
--

INSERT INTO `appointment_slots` (`id`, `appointment_date`, `appointment_time`, `branch_id`, `status`) VALUES
(1, '2026-04-21', '09:00 AM', NULL, 'Available'),
(2, '2026-04-21', '01:00 PM', NULL, 'Available'),
(3, '2026-04-21', '03:00 PM', NULL, 'Available'),
(4, '2026-04-26', '09:00 AM', NULL, 'Available'),
(5, '2026-04-26', '10:00 AM', NULL, 'Available'),
(6, '2026-04-26', '11:00 AM', NULL, 'Available'),
(7, '2026-04-26', '01:00 PM', NULL, 'Available'),
(8, '2026-04-26', '02:00 PM', NULL, 'Available'),
(9, '2026-04-26', '03:00 PM', NULL, 'Available'),
(10, '2026-04-27', '01:00 PM', NULL, 'Available'),
(11, '2026-04-27', '10:00 AM', NULL, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`) VALUES
(1, 'Bautista'),
(2, 'Laguna');

-- --------------------------------------------------------

--
-- Table structure for table `custom_orders`
--

CREATE TABLE `custom_orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `status` enum('Appointment','Initial Payment','On-going','For Delivery','Backjobs','Cancelled','Completed') DEFAULT 'Appointment',
  `estimated_completion` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_image` varchar(255) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `order_type` enum('walk-in','online') DEFAULT 'walk-in',
  `expected_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `customer_id`, `branch_id`, `material`, `dimensions`, `instructions`, `status`, `estimated_completion`, `created_at`, `reference_image`, `appointment_date`, `user_id`, `customer_name`, `order_type`, `expected_date`, `image`, `project_name`, `category`, `description`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 'Appointment', NULL, '2026-04-17 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(2, 2, 1, NULL, NULL, NULL, 'Initial Payment', NULL, '2026-04-19 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(3, 3, 1, NULL, NULL, NULL, 'On-going', NULL, '2026-04-21 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(4, 1, 1, NULL, NULL, NULL, 'For Delivery', NULL, '2026-04-23 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(5, 2, 1, NULL, NULL, NULL, 'Completed', NULL, '2026-04-25 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(6, 3, 1, NULL, NULL, NULL, 'Cancelled', NULL, '2026-04-26 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(7, 2, 1, NULL, NULL, NULL, 'Backjobs', NULL, '2026-04-24 01:59:57', NULL, NULL, NULL, NULL, 'walk-in', NULL, NULL, NULL, NULL, NULL, '2026-04-27 01:59:57'),
(35, 3, NULL, NULL, '6ft x 4ft', NULL, 'On-going', NULL, '2026-04-22 18:58:30', NULL, NULL, NULL, NULL, 'walk-in', NULL, 'uploads/Stainless sliding gate.png', 'Stainless Sliding Gate', 'Gates', 'Modern stainless swing gate with vertical bars and durable frame', '2026-04-22 19:09:59');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_id`, `branch_id`, `current_stock`) VALUES
(1, 1, 1, 6),
(2, 2, 1, 12),
(3, 3, 1, 18),
(4, 4, 1, 11),
(5, 5, 1, 14),
(6, 6, 1, 11),
(7, 7, 1, 11),
(8, 8, 1, 16),
(9, 9, 1, 21),
(10, 10, 1, 460),
(11, 11, 1, 10),
(12, 12, 1, 18),
(13, 13, 1, 18),
(14, 14, 1, 8),
(15, 15, 1, 24),
(16, 16, 1, 10),
(17, 17, 1, 12),
(18, 18, 1, 9),
(19, 19, 1, 21),
(20, 20, 1, 15),
(21, 21, 1, 6),
(22, 22, 1, 23),
(23, 23, 1, 13),
(24, 24, 1, 10),
(25, 25, 1, 6),
(26, 26, 1, 14),
(27, 27, 1, 9),
(28, 28, 1, 19),
(29, 29, 1, 22),
(30, 30, 1, 10),
(31, 31, 1, 18),
(32, 32, 1, 15),
(33, 33, 1, 15),
(34, 34, 1, 8),
(35, 35, 1, 11),
(36, 36, 1, 24),
(37, 37, 1, 23),
(38, 38, 1, 21),
(39, 39, 1, 10),
(40, 40, 1, 24),
(41, 41, 1, 6),
(42, 42, 1, 12),
(43, 43, 1, 20),
(44, 44, 1, 16),
(45, 45, 1, 18),
(46, 46, 1, 19),
(47, 47, 1, 14),
(48, 48, 1, 10),
(49, 49, 1, 21),
(50, 50, 1, 11),
(51, 51, 1, 9),
(52, 52, 1, 6),
(53, 53, 1, 20),
(54, 54, 1, 16),
(55, 55, 1, 16),
(56, 56, 1, 8),
(57, 57, 1, 9),
(58, 58, 1, 14),
(59, 59, 1, 17),
(60, 60, 1, 21),
(61, 61, 1, 10),
(62, 62, 1, 23),
(63, 63, 1, 20),
(64, 64, 1, 8),
(65, 65, 1, 13),
(66, 66, 1, 18),
(67, 67, 1, 7),
(68, 68, 1, 14),
(69, 69, 1, 7),
(70, 70, 1, 7),
(71, 71, 1, 12),
(72, 72, 1, 12),
(73, 73, 1, 21),
(74, 74, 1, 23),
(75, 75, 1, 9),
(76, 76, 1, 10),
(77, 77, 1, 17),
(78, 78, 1, 12),
(79, 79, 1, 6),
(80, 80, 1, 7),
(81, 81, 1, 12),
(82, 82, 1, 16),
(83, 83, 1, 21),
(84, 84, 1, 12),
(85, 85, 1, 10),
(86, 86, 1, 10),
(87, 87, 1, 18),
(88, 88, 1, 13),
(89, 89, 1, 9),
(90, 90, 1, 21),
(91, 91, 1, 15),
(92, 92, 1, 5),
(93, 93, 1, 17),
(94, 94, 1, 6),
(95, 95, 1, 12),
(96, 96, 1, 20),
(97, 97, 1, 17),
(98, 98, 1, 22),
(99, 99, 1, 14),
(100, 100, 1, 19),
(101, 101, 1, 7),
(102, 102, 1, 15),
(103, 103, 1, 9),
(104, 104, 1, 16),
(105, 105, 1, 9),
(106, 106, 1, 13),
(107, 107, 1, 14),
(108, 108, 1, 7),
(109, 109, 1, 7),
(110, 110, 1, 11),
(111, 111, 1, 8),
(112, 112, 1, 23),
(113, 113, 1, 6),
(114, 114, 1, 19),
(115, 115, 1, 10),
(116, 116, 1, 8),
(117, 117, 1, 5),
(118, 118, 1, 17),
(119, 119, 1, 8),
(120, 120, 1, 22),
(121, 121, 1, 22),
(122, 122, 1, 21),
(123, 123, 1, 14),
(124, 124, 1, 24),
(125, 125, 1, 12),
(126, 126, 1, 5),
(127, 127, 1, 22),
(128, 128, 1, 13),
(129, 129, 1, 16),
(130, 130, 1, 13),
(131, 131, 1, 13),
(132, 132, 1, 23),
(133, 133, 1, 11),
(134, 134, 1, 21),
(135, 135, 1, 8),
(136, 136, 1, 13),
(137, 137, 1, 16),
(138, 138, 1, 18),
(139, 139, 1, 18),
(140, 140, 1, 11),
(141, 141, 1, 15),
(142, 142, 1, 16),
(143, 143, 1, 13),
(144, 144, 1, 13),
(145, 145, 1, 19),
(146, 146, 1, 14),
(147, 147, 1, 7),
(148, 148, 1, 10),
(149, 149, 1, 5),
(150, 150, 1, 10),
(151, 151, 1, 10),
(152, 152, 1, 16),
(153, 153, 1, 5),
(154, 154, 1, 15),
(155, 155, 1, 12),
(156, 156, 1, 13),
(157, 157, 1, 23),
(158, 158, 1, 11),
(159, 159, 1, 23);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `stock`, `image`, `price`) VALUES
(1, 'Gate Fabrication', 'Industrial Materials', 99, 'metal_beam.jpg', 100.00),
(2, 'Window Installation', 'Industrial Materials', 199, 'metal_washers.jpg', 200.00),
(3, 'Steel Railing Design', 'Fabricated Product', 20, 'stainless_grill.jpg', 300.00),
(4, 'Stainless Sink', 'Fabricated Product', 15, 'stainless_sink.jpg', 400.00),
(5, '304 Stainless Steel Threaded Pipe Elbows', 'Industrial Materials', 100, '304_stainless_steel_threaded_pipe_elbows.jpg', 150.00),
(6, 'Abrasive Flap Wheel', 'Industrial Materials', 100, 'abrasive_flap_wheel.jpg', 120.00),
(7, 'Ball caps', 'Industrial Materials', 100, 'ball_caps.jpg', 80.00),
(8, 'Ball finial', 'Industrial Materials', 100, 'ball_finial.jpg', 90.00),
(9, 'Black Drywall Screws', 'Industrial Materials', 200, 'black_drywall_screws.jpg', 50.00),
(10, 'Blind Rivet', 'Industrial Materials', 200, 'blind_rivet.jpg', 60.00),
(11, 'Buffing pad', 'Industrial Materials', 150, 'buffing_pad.jpg', 70.00),
(12, 'Carbide Multi-Wheel Cutting Disc', 'Industrial Materials', 100, 'carbide_multi_wheel_cutting_disc.jpg', 180.00),
(13, 'Chalk line reel', 'Industrial Materials', 100, 'chalk_line_reel.jpg', 110.00),
(14, 'Combination Wrench', 'Industrial Materials', 80, 'combination_wrench.jpg', 200.00),
(15, 'Conical finials', 'Industrial Materials', 100, 'conical_finials.jpg', 95.00),
(16, 'Corrugated Roof', 'Industrial Materials', 50, 'corrugated_roof.jpg', 500.00),
(17, 'Cutting Wheel (Abrasive Cutting Disc)', 'Industrial Materials', 150, 'cutting_wheel_abrasive_cutting_disc.jpg', 130.00),
(18, 'Decorative socket collars', 'Industrial Materials', 100, 'decorative_socket_collars.jpg', 85.00),
(19, 'Diamond Cutting Disc', 'Industrial Materials', 120, 'diamond_cutting_disc.jpg', 250.00),
(20, 'E308 Welding Rod', 'Industrial Materials', 200, 'e308.jpg', 300.00),
(21, 'E309 Welding Rod', 'Industrial Materials', 200, 'e309.jpg', 320.00),
(22, 'E6011 Welding Rod', 'Industrial Materials', 200, 'e6011.jpg', 280.00),
(23, 'E7018 Welding Rod', 'Industrial Materials', 200, 'e7018.jpg', 290.00),
(24, 'Expand nails with screw', 'Industrial Materials', 200, 'expand_nails_with_screw.jpg', 60.00),
(25, 'Filler rod', 'Industrial Materials', 150, 'filler_rod.jpg', 140.00),
(26, 'Flange base', 'Industrial Materials', 100, 'flange_base.jpg', 180.00),
(27, 'Flange cover', 'Industrial Materials', 100, 'flange_cover.jpg', 170.00),
(28, 'Flange nuts', 'Industrial Materials', 200, 'flange_nuts.jpg', 75.00),
(29, 'Flange square cover', 'Industrial Materials', 100, 'flange_square_cover.jpg', 160.00),
(30, 'Flat washers', 'Industrial Materials', 200, 'flat_washers.jpg', 40.00),
(31, 'Galvanized Eye Bolts', 'Industrial Materials', 150, 'galvanized_eye_bolts.jpg', 110.00),
(32, 'Galvanized Steel Pipe Clamp', 'Industrial Materials', 120, 'galvanized_steel_pipe_clamp.jpg', 130.00),
(33, 'Hand Riveter', 'Industrial Materials', 80, 'hand_riveter.jpg', 350.00),
(34, 'Hex Bolts', 'Industrial Materials', 200, 'hex_bolts.jpg', 90.00),
(35, 'Hex Coupling', 'Industrial Materials', 150, 'hex_coupling.jpg', 120.00),
(36, 'Hose ferrules', 'Industrial Materials', 150, 'hose_ferrules.jpg', 85.00),
(37, 'Locking rings', 'Industrial Materials', 150, 'locking_rings.jpg', 70.00),
(38, 'L-Type Socket Wrench', 'Industrial Materials', 100, 'l_type_socket_wrench.jpg', 220.00),
(39, 'Magnetic Nut Setter Set', 'Industrial Materials', 80, 'magnetic_nut_setter_set.jpg', 260.00),
(40, 'Metal Pall Rings', 'Industrial Materials', 150, 'metal_pall_rings.jpg', 140.00),
(41, 'Gate Fabrication', 'Industrial Materials', 50, 'metal_beam.jpg', 100.00),
(42, 'Window Installation', 'Industrial Materials', 150, 'metal_washers.jpg', 200.00),
(43, 'Ornamental baluster', 'Industrial Materials', 100, 'ornamental_baluster.jpg', 300.00),
(44, 'Ornamental metal finials', 'Industrial Materials', 100, 'ornamental_metal_finials.jpg', 250.00),
(45, 'Ornamental scrolls', 'Industrial Materials', 100, 'ornamental_scrolls.jpg', 200.00),
(46, 'Paint brush', 'Industrial Materials', 120, 'paint_brush.jpg', 60.00),
(47, 'Perforated metal plates', 'Industrial Materials', 80, 'perforated_metal_plates.jpg', 350.00),
(48, 'Pipe Clamp', 'Industrial Materials', 100, 'pipe_clamp.jpg', 130.00),
(49, 'Pipe ferrules', 'Industrial Materials', 150, 'pipe_ferrules.jpg', 90.00),
(50, 'Roller Chain', 'Industrial Materials', 80, 'roller_chain.jpg', 400.00),
(51, 'Roof ridge cap', 'Industrial Materials', 50, 'roof_ridge_cap.jpg', 300.00),
(52, 'Round tube', 'Industrial Materials', 70, 'round_tube.jpg', 250.00),
(53, 'Self-Tapping Screws', 'Industrial Materials', 200, 'self_tapping_screws.jpg', 60.00),
(54, 'Silicone Sealant', 'Industrial Materials', 120, 'silicone_sealant.jpg', 180.00),
(55, 'Sintered Diamond Cutting Disc', 'Industrial Materials', 100, 'sintered_diamond_cutting_disc.jpg', 260.00),
(56, 'Sliding Window Roller Assembly (35mm)', 'Industrial Materials', 80, 'sliding_window_roller_assembly_35mm.jpg', 150.00),
(57, 'Silver spring', 'Industrial Materials', 150, 'silver_spring.jpg', 75.00),
(58, 'Square tube', 'Industrial Materials', 70, 'square_tube.jpg', 260.00),
(59, 'Stainless plain sheet', 'Industrial Materials', 60, 'stainless_plain_sheet.jpg', 500.00),
(60, 'Stainless Steel Ball Finial', 'Industrial Materials', 100, 'stainless_steel_ball_finial.jpg', 200.00),
(61, 'Stainless Steel Barrel Bolt', 'Industrial Materials', 120, 'stainless_steel_barrel_bolt.jpg', 150.00),
(62, 'Stainless Steel Butt Hinge (4x4)', 'Industrial Materials', 120, 'stainless_steel_butt_hinge_4x4.jpg', 140.00),
(63, 'Stainless steel elbow pipe fittings stack', 'Industrial Materials', 100, 'stainless_steel_elbow_pipe_fittings_stack.jpg', 300.00),
(64, 'Stainless Steel Hasp and Staple Lock', 'Industrial Materials', 100, 'stainless_steel_hasp_and_staple_lock.jpg', 180.00),
(65, 'Stainless Steel Hose Clamp', 'Industrial Materials', 150, 'stainless_steel_hose_clamp.jpg', 120.00),
(66, 'Stainless steel numbers', 'Industrial Materials', 100, 'stainless_steel_numbers.jpg', 90.00),
(67, 'Stainless Steel U-Bolts with Plate and Nuts', 'Industrial Materials', 120, 'stainless_steel_u_bolts_with_plate_and_nuts.jpg', 200.00),
(68, 'Steel Railing Design', 'Industrial Materials', 20, 'stainless_grill.jpg', 3000.00),
(69, 'Stainless Sink', 'Industrial Materials', 20, 'stainless_sink.jpg', 2500.00),
(70, 'Steel Coupling', 'Industrial Materials', 150, 'steel_coupling.jpg', 130.00),
(71, 'Tek screws', 'Industrial Materials', 200, 'tek_screws.jpg', 60.00),
(72, 'Tie Wire', 'Industrial Materials', 200, 'tie_wire.jpg', 50.00),
(73, 'Toggle Clamp Latch', 'Industrial Materials', 100, 'toggle_clamp_latch.jpg', 220.00),
(74, 'Triple Basin Kitchen Sink', 'Industrial Materials', 10, 'triple_basin_kitchen_sink.jpg', 4500.00),
(75, 'Twisted Steel Bar', 'Industrial Materials', 50, 'twisted_steel_bar.jpg', 350.00),
(76, 'Wall flashing', 'Industrial Materials', 60, 'wall_flashing.jpg', 280.00),
(77, 'Welded wire mesh panels', 'Industrial Materials', 40, 'welded_wire_mesh_panels.jpg', 600.00),
(78, 'Welding Rod', 'Industrial Materials', 200, 'welding_rod.jpg', 300.00),
(79, 'Wire Nails', 'Industrial Materials', 200, 'wire_nails.jpg', 40.00),
(80, 'Gate Fabrication', NULL, 0, NULL, NULL),
(81, 'Window Installation', NULL, 0, NULL, NULL),
(82, 'Steel Railing Design', NULL, 0, NULL, NULL),
(83, 'Custom Welding Service', NULL, 0, NULL, NULL),
(84, 'Metal Frame Assembly', NULL, 0, NULL, NULL),
(85, '304 Stainless Steel Threaded Pipe Elbows', NULL, 0, NULL, NULL),
(86, 'Abrasive Flap Wheel', NULL, 0, NULL, NULL),
(87, 'Ball caps', NULL, 0, NULL, NULL),
(88, 'Ball finial', NULL, 0, NULL, NULL),
(89, 'Black Drywall Screws', NULL, 0, NULL, NULL),
(90, 'Blind Rivet', NULL, 0, NULL, NULL),
(91, 'Buffing pad', NULL, 0, NULL, NULL),
(92, 'Carbide Multi-Wheel Cutting Disc', NULL, 0, NULL, NULL),
(93, 'Chalk line reel', NULL, 0, NULL, NULL),
(94, 'Combination Wrench', NULL, 0, NULL, NULL),
(95, 'Conical finials', NULL, 0, NULL, NULL),
(96, 'Corrugated Roof', NULL, 0, NULL, NULL),
(97, 'Cutting Wheel (Abrasive Cutting Disc)', NULL, 0, NULL, NULL),
(98, 'Decorative socket collars', NULL, 0, NULL, NULL),
(99, 'Diamond Cutting Disc', NULL, 0, NULL, NULL),
(100, 'E308', NULL, 0, NULL, NULL),
(101, 'E309', NULL, 0, NULL, NULL),
(102, 'E6011', NULL, 0, NULL, NULL),
(103, 'E7018', NULL, 0, NULL, NULL),
(104, 'Expand nails with screw', NULL, 0, NULL, NULL),
(105, 'Filler rod', NULL, 0, NULL, NULL),
(106, 'Flange base', NULL, 0, NULL, NULL),
(107, 'Flange cover', NULL, 0, NULL, NULL),
(108, 'Flange Nuts', NULL, 0, NULL, NULL),
(109, 'Flange square cover', NULL, 0, NULL, NULL),
(110, 'Flat Washers', NULL, 0, NULL, NULL),
(111, 'Galvanized Eye Bolts', NULL, 0, NULL, NULL),
(112, 'Galvanized Steel Pipe Clamp', NULL, 0, NULL, NULL),
(113, 'Hand Riveter', NULL, 0, NULL, NULL),
(114, 'Hex Bolts', NULL, 0, NULL, NULL),
(115, 'Hex Coupling', NULL, 0, NULL, NULL),
(116, 'Hose ferrules', NULL, 0, NULL, NULL),
(117, 'Locking rings', NULL, 0, NULL, NULL),
(118, 'L-Type Socket Wrench', NULL, 0, NULL, NULL),
(119, 'Magnetic Nut Setter Set', NULL, 0, NULL, NULL),
(120, 'Metal Pall Rings', NULL, 0, NULL, NULL),
(121, 'metal_beam', NULL, 0, NULL, NULL),
(122, 'metal_washers', NULL, 0, NULL, NULL),
(123, 'Ornamental baluster', NULL, 0, NULL, NULL),
(124, 'Ornamental metal finials', NULL, 0, NULL, NULL),
(125, 'Ornamental scrolls', NULL, 0, NULL, NULL),
(126, 'Paint brush', NULL, 0, NULL, NULL),
(127, 'Perforated metal plates', NULL, 0, NULL, NULL),
(128, 'Pipe Clamp', NULL, 0, NULL, NULL),
(129, 'Pipe ferrules', NULL, 0, NULL, NULL),
(130, 'Roller Chain', NULL, 0, NULL, NULL),
(131, 'Roof ridge cap', NULL, 0, NULL, NULL),
(132, 'Round tube', NULL, 0, NULL, NULL),
(133, 'Self-Tapping Screws', NULL, 0, NULL, NULL),
(134, 'Silicone Sealant', NULL, 0, NULL, NULL),
(135, 'Sintered Diamond Cutting Disc', NULL, 0, NULL, NULL),
(136, 'Sliding Window Roller Assembly (35mm)', NULL, 0, NULL, NULL),
(137, 'Slyer spring', NULL, 0, NULL, NULL),
(138, 'Square tube', NULL, 0, NULL, NULL),
(139, 'Stainless plain sheet', NULL, 0, NULL, NULL),
(140, 'Stainless Steel Ball Finial', NULL, 0, NULL, NULL),
(141, 'Stainless Steel Barrel Bolt', NULL, 0, NULL, NULL),
(142, 'Stainless Steel Butt Hinge (4x4)', NULL, 0, NULL, NULL),
(143, 'Stainless steel elbow pipe fittings stack', NULL, 0, NULL, NULL),
(144, 'Stainless Steel Hasp and Staple Lock', NULL, 0, NULL, NULL),
(145, 'Stainless Steel Hose Clamp', NULL, 0, NULL, NULL),
(146, 'Stainless steel numbers', NULL, 0, NULL, NULL),
(147, 'Stainless Steel U-Bolts with Plate and Nuts', NULL, 0, NULL, NULL),
(148, 'stainless_grill', NULL, 0, NULL, NULL),
(149, 'stainless_sink', NULL, 0, NULL, NULL),
(150, 'Steel Coupling', NULL, 0, NULL, NULL),
(151, 'Tekscrews', NULL, 0, NULL, NULL),
(152, 'Tie Wire', NULL, 0, NULL, NULL),
(153, 'Toggle Clamp Latch', NULL, 0, NULL, NULL),
(154, 'triple-basin kitchen sink', NULL, 0, NULL, NULL),
(155, 'Twisted Steel Bars', NULL, 0, NULL, NULL),
(156, 'Wall flashing', NULL, 0, NULL, NULL),
(157, 'Welded wire mesh panels', NULL, 0, NULL, NULL),
(158, 'Welding Rod', NULL, 0, NULL, NULL),
(159, 'Wire Nails', NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `quantity`, `price`, `total_amount`, `created_at`) VALUES
(1, 1, 4, 1, 400.00, 400.00, '2026-03-17 23:43:21'),
(2, 1, 1, 1, 100.00, 100.00, '2026-03-17 23:43:26'),
(3, 1, 2, 1, 200.00, 200.00, '2026-03-17 23:52:50'),
(4, 1, 1, 4, 100.00, 400.00, '2026-03-17 23:53:04'),
(5, 1, 4, 1, 400.00, 400.00, '2026-03-17 23:54:37'),
(6, 1, 1, 1, 100.00, 100.00, '2026-03-18 00:00:35'),
(7, 0, 1, 6, 100.00, 600.00, '2026-03-18 00:00:55'),
(8, 0, 1, 1, 100.00, 100.00, '2026-03-18 00:04:39'),
(9, 0, 1, 1, 100.00, 100.00, '2026-03-18 00:04:39'),
(10, 0, 1, 1, 100.00, 100.00, '2026-03-18 00:08:20'),
(11, 1, 4, 1, 400.00, 400.00, '2026-03-18 00:08:32'),
(12, 4, 1, 1, 100.00, 100.00, '2026-03-18 00:18:04'),
(13, 5, 1, 1, 100.00, 100.00, '2026-03-18 00:18:36'),
(14, 6, 1, 1, 100.00, 100.00, '2026-03-18 00:28:44'),
(15, 7, 1, 1, 100.00, 100.00, '2026-03-18 01:28:20'),
(16, 8, 2, 1, 200.00, 200.00, '2026-03-18 01:44:42'),
(17, 9, 1, 1, 100.00, 100.00, '2026-03-18 07:00:36'),
(18, 10, 4, 2, 400.00, 800.00, '2026-03-18 14:22:53'),
(19, 11, 4, 2, 400.00, 800.00, '2026-03-18 14:33:13'),
(20, 11, 4, 2, 400.00, 800.00, '2026-03-18 14:33:13'),
(21, 12, 4, 2, 400.00, 800.00, '2026-03-18 14:34:31'),
(22, 12, 4, 2, 400.00, 800.00, '2026-03-18 14:34:31'),
(23, 13, 2, 1, 200.00, 200.00, '2026-03-18 14:46:17'),
(24, 13, 2, 1, 200.00, 200.00, '2026-03-18 14:46:17'),
(25, 14, 2, 1, 200.00, 200.00, '2026-03-18 14:46:31'),
(26, 14, 2, 1, 200.00, 200.00, '2026-03-18 14:46:31'),
(27, 15, 2, 1, 200.00, 200.00, '2026-03-18 14:46:47'),
(28, 16, 4, 1, 400.00, 400.00, '2026-03-18 14:47:05'),
(29, 17, 1, 2, 100.00, 200.00, '2026-03-18 14:59:46'),
(30, 18, 1, 1, 100.00, 100.00, '2026-03-18 15:11:16'),
(31, 19, 1, 1, 100.00, 100.00, '2026-03-18 15:12:22'),
(32, 20, 4, 1, 400.00, 400.00, '2026-03-19 02:09:18'),
(33, 21, 4, 60, 400.00, 24000.00, '2026-03-19 02:09:26'),
(34, 22, 1, 53, 100.00, 5300.00, '2026-03-19 02:10:15'),
(35, 23, 1, 1, 100.00, 100.00, '2026-03-21 10:55:46'),
(36, 24, 1, 1, 100.00, 100.00, '2026-03-21 11:26:13'),
(37, 25, 1, 1, 100.00, 100.00, '2026-03-21 11:26:32'),
(38, 26, 1, 1, 100.00, 100.00, '2026-03-21 11:26:39'),
(39, 31, 1, 1, 100.00, 100.00, '2026-03-21 11:44:28'),
(40, 33, 2, 1, 200.00, 200.00, '2026-04-07 19:21:52'),
(41, 34, 1, 1, 100.00, 100.00, '2026-04-09 06:05:17'),
(42, 1, 1, 5, 0.00, 5000.00, '2026-04-27 02:00:37'),
(43, 2, 2, 3, 0.00, 3000.00, '2026-04-27 02:00:37'),
(44, 3, 3, 7, 0.00, 7000.00, '2026-04-27 02:00:37'),
(45, 4, 1, 4, 0.00, 4000.00, '2026-04-27 02:00:37'),
(46, 5, 2, 6, 0.00, 6000.00, '2026-04-27 02:00:37'),
(47, 5, 3, 2, 0.00, 2000.00, '2026-04-27 02:00:37'),
(48, 6, 1, 2, 0.00, 2000.00, '2026-04-27 02:00:37'),
(49, 7, 2, 1, 0.00, 1000.00, '2026-04-27 02:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `payment` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `task_name` varchar(100) DEFAULT NULL,
  `status` enum('Pending','In Progress','For Release','Completed') DEFAULT 'Pending',
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `staff_id`, `remarks`, `created_at`, `total_amount`, `status`) VALUES
(1, 27, NULL, NULL, '2026-03-21 19:31:47', 100.00, 'Pending'),
(2, 27, NULL, NULL, '2026-03-21 19:31:47', 100.00, 'Pending'),
(3, 28, NULL, NULL, '2026-03-21 19:31:56', 100.00, 'Pending'),
(4, 28, NULL, NULL, '2026-03-21 19:31:56', 100.00, 'Pending'),
(5, 29, NULL, NULL, '2026-03-21 19:36:19', 100.00, 'Pending'),
(6, 29, NULL, NULL, '2026-03-21 19:36:19', 100.00, 'Pending'),
(7, 30, NULL, NULL, '2026-03-21 19:44:21', 100.00, 'Pending'),
(8, 30, NULL, NULL, '2026-03-21 19:44:21', 100.00, 'Pending'),
(9, 32, NULL, NULL, '2026-03-22 20:39:54', 100.00, 'Pending'),
(10, 32, NULL, NULL, '2026-03-22 20:39:54', 100.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','welder','customer') NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('active','blocked','archived') DEFAULT 'active',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(6) DEFAULT NULL,
  `code_expiry` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `branch_id`, `status`, `reset_token`, `token_expiry`, `is_verified`, `verification_code`, `code_expiry`, `avatar`) VALUES
(1, 'Lanie Eborde', 'owner@rholance.com', '$2y$10$y6ed/up6HhR6hnwlTj22MetNmY4Zdj9OWq44d58mP6dtg9ri3EVri', 'admin', 1, 'active', NULL, NULL, 1, NULL, NULL, '1776487516_2fcc9dab-681c-42ee-b7be-efc7adda03de.jpg'),
(2, 'Nichole Sanchez', 'staff@rholance.com', '$2y$10$FG5lJIxZpyye/FuYBUPHQeP13MU20uFOERVPDTsxUG5iCeI/OS1qO', 'staff', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(3, 'Simple Perez', 'saperez@kld.edu.ph', '$2y$10$1u/XwYh7.rjVXU3Xs/qum.hPz3f8dv6nu4jK9paVAKOrrLCEbeudS', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, '1776455419_photo_1x1_inch.png'),
(69, 'Lea', 'lea@rholance.com', '123456', 'staff', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(70, 'Ella', 'ella@rholance.com', '123456', 'staff', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(71, 'Ederlyn', 'ederlyn@rholance.com', '123456', 'staff', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(72, 'Nicole', 'nicole1@rholance.com', '123456', 'staff', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(73, 'Reymond D.', 'reymond@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(74, 'Manly', 'manly@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(75, 'Jeffrey', 'jeffrey@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(76, 'Allan', 'allan@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(77, 'Berto', 'berto@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(78, 'Poul', 'poul@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(79, 'Mundo', 'mundo@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(80, 'Itlog', 'itlog@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(81, 'Jared', 'jared1@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(82, 'Mac', 'mac@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(83, 'Mekaniko', 'mekaniko@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(84, 'Derick', 'derick@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(85, 'Bilog', 'bilog@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(86, 'Tiki', 'tiki@rholance.com', '123456', 'welder', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(87, 'Dondon', 'dondon@rholance.com', '123456', 'welder', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(88, 'Ryan', 'ryan@rholance.com', '123456', 'welder', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(89, 'Jovie', 'jovie@rholance.com', '123456', 'staff', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(90, 'Robert', 'robert@rholance.com', '123456', 'welder', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(91, 'Marlon', 'marlon@rholance.com', '123456', 'welder', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(92, 'Nicole', 'nicole2@rholance.com', '123456', 'staff', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(93, 'Lhoraine', 'lhoraine@rholance.com', '123456', 'staff', 2, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(94, 'Angelica', 'Angelicacr@rholance.com', '$2y$10$MjOvm8vsC.5cbk.Puw5CgOEntxDW6kFUo7GQXukvZ.OeHNjj8tgW6', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(95, 'Angelica', 'arc@rholance.com', '$2y$10$AhzLfvkDxDYHNQMCiN.ox.X/pRZqA169J9iWwQ.K3e15icAkI/yHG', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(96, 'Simple', 'perezsimple25@gmail.com', '$2y$10$upMiIQ.xWxKLvqCyoAWjw.bUcCNJa8WurLmLMdZLOIBlaW2ABojbq', 'customer', 1, 'active', '9d4e86ddc3c067c1d459f10004a9eff20755df715ca6e6f9d06ae87ddae27c45', '2026-04-11 19:07:58', 1, NULL, NULL, NULL),
(97, 'Pol', 'perezsimpl25@gmail.com', '$2y$10$QqWXIbYbst7Qf.Hjcdlb6.Ix1tj/tnrY8e4K.1MpeUGkzax6pUhGi', 'customer', 1, 'active', NULL, NULL, 1, '699269', '2026-04-13 12:21:11', NULL),
(98, 'Just', 'japerez@kld.edu.ph', '$2y$10$e9VikHdKdl7VGhHCR5TaRewhh4FVKCw65LMonlqcZpdn483edq242', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(99, 'Angel', 'arcarting@kld.edu.ph', '$2y$10$q5THDCdxO.CZDiMRrrkc5.VyKbwMLF6v9Yb9zQ4k2oIlxGhtVLddK', 'customer', 1, 'active', NULL, NULL, 1, '394846', '2026-04-13 13:17:54', NULL),
(100, 'Jomar', 'jacaponpon@kld.edu.ph', '$2y$10$YZI96W3ZhnVL8p3nE1aP5eTLyYy2hjuMH2QAXb8fidynO9jTAsnva', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL),
(101, 'sebiccai', 'perezsimple55@gmal.com', '$2y$10$81/pVe44CWb5AVFPDtbthurq..VbMzpOZlIYUiNHWzsbSKy4Q.X/O', 'customer', 1, 'active', NULL, NULL, 1, '734080', '2026-04-13 14:39:56', NULL),
(102, 'sebiccai', 'perezsimple55@gmail.com', '$2y$10$cB97cUN9ALXA1D/NKPEXueXwhiTimOlbyLsoTgfreeYkErC3LC6qW', 'customer', 1, 'active', NULL, NULL, 1, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment_slots`
--
ALTER TABLE `appointment_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `fk_customer` (`customer_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `appointment_slots`
--
ALTER TABLE `appointment_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `custom_orders_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `custom_orders` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
