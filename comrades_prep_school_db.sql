-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2026 at 12:58 PM
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
-- Database: `comrades_dbb`
--
CREATE DATABASE IF NOT EXISTS `comrades_dbb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `comrades_dbb`;
--
-- Database: `comrades_prep_db`
--
CREATE DATABASE IF NOT EXISTS `comrades_prep_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `comrades_prep_db`;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`) VALUES
(1, 'Baby Class'),
(2, 'Middle Class'),
(4, 'Primary 1'),
(5, 'Primary 2'),
(6, 'Primary 3'),
(7, 'Primary 4'),
(3, 'Top Class');

-- --------------------------------------------------------

--
-- Table structure for table `pupils`
--

CREATE TABLE `pupils` (
  `pupil_id` int(11) NOT NULL,
  `admission_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `tuition_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `exam_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupils`
--

INSERT INTO `pupils` (`pupil_id`, `admission_no`, `first_name`, `last_name`, `gender`, `class_id`, `tuition_fee`, `amount_paid`, `exam_score`, `created_at`) VALUES
(1, 'CPS/2026/0005', 'Newton', 'Nabimanya', 'M', 7, 1235000.00, 855999.70, 86.00, '2026-11-06 09:02:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_name` (`class_name`);

--
-- Indexes for table `pupils`
--
ALTER TABLE `pupils`
  ADD PRIMARY KEY (`pupil_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `class_id` (`class_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pupils`
--
ALTER TABLE `pupils`
  MODIFY `pupil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pupils`
--
ALTER TABLE `pupils`
  ADD CONSTRAINT `pupils_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;
--
-- Database: `comrades_prep_school_db`
--
CREATE DATABASE IF NOT EXISTS `comrades_prep_school_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `comrades_prep_school_db`;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `class_teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `class_teacher_id`) VALUES
(1, 'Baby Class', NULL),
(2, 'Middle Class', NULL),
(3, 'Top Class', NULL),
(4, 'Primary 1', NULL),
(5, 'Primary 2', NULL),
(6, 'Primary 3', 2),
(7, 'Primary 4', 1),
(8, 'Primary 5', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_assignments`
--

CREATE TABLE `class_assignments` (
  `assignment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `work_type` enum('Homework','Class Work','Mid-Term Test','Revision') NOT NULL DEFAULT 'Homework',
  `instructions` text NOT NULL,
  `due_date` date DEFAULT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subject`
--

CREATE TABLE `class_subject` (
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subject`
--

INSERT INTO `class_subject` (`class_id`, `subject_id`) VALUES
(6, 8),
(6, 9),
(6, 11),
(6, 12),
(6, 13),
(6, 14),
(6, 18),
(7, 8),
(7, 9),
(7, 10),
(7, 11),
(7, 14),
(8, 8),
(8, 9),
(8, 10),
(8, 11);

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `exam_id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `term` varchar(20) NOT NULL,
  `academic_year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`exam_id`, `exam_name`, `term`, `academic_year`) VALUES
(1, 'Mid term 1', 'Term 1', 2026),
(2, 'End of term 1', 'Term 1', 2026),
(3, 'Mid term 1 (Mid-Term)', 'Term 1', 2026);

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` varchar(50) NOT NULL DEFAULT 'Cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`payment_id`, `pupil_id`, `amount_paid`, `payment_date`, `payment_mode`, `reference_no`, `received_by`, `notes`) VALUES
(1, 1, 850000.00, '2026-11-07', 'Cash', '', 'Bursar Office', NULL),
(2, 2, 1000000.00, '2026-11-07', 'Cash', '', 'Bursar Office', NULL),
(3, 2, 1000000.00, '2026-11-07', 'Cash', '', 'Bursar Office', NULL),
(4, 4, 500000.00, '2026-11-07', 'MTN MoMo', '', 'Bursar Office', NULL),
(5, 4, 500000.00, '2026-11-07', 'MTN MoMo', '', 'Bursar Office', NULL),
(6, 3, 500000.00, '2026-11-07', 'Cash', '', 'Bursar Office', NULL),
(7, 5, 973000.00, '2026-11-09', 'Cash', '', 'Bursar Office', NULL),
(8, 7, 850000.00, '2026-11-09', 'MTN MoMo', '', 'Bursar Office', NULL),
(9, 6, 100000.00, '2026-11-09', 'Cash', '', 'Bursar Office', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `fee_structure_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `term` varchar(20) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `total_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`fee_structure_id`, `class_id`, `term`, `academic_year`, `total_fee`) VALUES
(1, 7, 'Term 1', 2026, 950000.00),
(2, 6, 'Term 1', 2026, 950000.00),
(3, 2, 'Term 1', 2026, 750000.00),
(4, 8, 'Term 1', 2026, 1000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `parent_name`, `phone`, `address`) VALUES
(1, 'Jane', 'Nakato', 'Kitebi'),
(2, 'John', 'Kato', 'Kitende'),
(3, 'Mark', '0709676568', 'Kabawo'),
(4, 'Kato Ben', '07834267', 'Kitende'),
(5, 'Kevin Nantumbwe', '0765075588', 'Salaama'),
(6, 'Victor Kabanda', '0776546788', 'kistu'),
(7, 'BALIGEYA HERBERT', '0706779172', 'WANDEGEYA');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `payslip_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `pay_period` varchar(50) NOT NULL,
  `payment_type` enum('Monthly','Termly') NOT NULL DEFAULT 'Monthly',
  `base_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowance_notes` varchar(255) DEFAULT NULL,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deduction_notes` varchar(255) DEFAULT NULL,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_mode` varchar(50) NOT NULL DEFAULT 'Bank Transfer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`payslip_id`, `teacher_id`, `pay_period`, `payment_type`, `base_salary`, `allowances`, `allowance_notes`, `deductions`, `deduction_notes`, `net_pay`, `payment_date`, `payment_mode`, `created_at`) VALUES
(1, 2, 'term 1', 'Monthly', 307000.00, 90000.00, 'overtime', 0.00, '', 397000.00, '2026-11-09', 'Bank Transfer', '2026-11-09 09:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `pupils`
--

CREATE TABLE `pupils` (
  `pupil_id` int(11) NOT NULL,
  `admission_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `dob` date DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `status` enum('Active','Graduated','Transferred') NOT NULL DEFAULT 'Active',
  `photo` varchar(255) DEFAULT NULL,
  `custom_fee` decimal(10,2) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `tuition_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupils`
--

INSERT INTO `pupils` (`pupil_id`, `admission_no`, `first_name`, `last_name`, `gender`, `dob`, `class_id`, `status`, `photo`, `custom_fee`, `emergency_contact`, `medical_notes`, `religion`, `tuition_fee`, `amount_paid`, `created_at`) VALUES
(1, 'CPS/2026/0005', 'Newton', 'Nabimanya', 'M', '2003-07-04', 6, 'Active', 'uploads/1794052225_IMG-20260903-WA0002.jpg', NULL, '0709508697', 'none', 'Christian', 1234000.00, 897800.00, '2026-11-06 10:45:22'),
(2, 'CPS/2026/0006', 'Noble', 'Agaba', 'M', NULL, 7, 'Active', NULL, NULL, NULL, NULL, NULL, 1234000.00, 1139990.00, '2026-11-06 15:23:03'),
(3, 'CPS/2026/0007', 'Sarah', 'Kiboya', 'F', NULL, 2, 'Active', NULL, 550000.00, NULL, NULL, NULL, 1234000.00, 1234000.00, '2026-11-06 15:27:43'),
(4, 'CPS/2026/0008', 'Praise', 'Kincoco', 'F', NULL, 7, 'Active', NULL, NULL, NULL, NULL, NULL, 1235990.00, 1235990.00, '2026-11-06 16:43:10'),
(5, 'CPS/2026/0009', 'Sheillah', 'Nanyonga', 'F', '2014-07-27', 8, 'Active', NULL, 973000.00, '0772072991', 'None', 'Christian', 0.00, 0.00, '2026-11-09 09:25:32'),
(6, 'CPS/2026/0010', 'Joy', 'Mulungi', 'F', '2016-03-08', 6, 'Active', NULL, NULL, '0745075598', '', 'Christian', 0.00, 0.00, '2026-11-09 09:29:01'),
(7, 'CPS/2026/0011', 'BATWIDHIRE', 'JOHN', 'M', '2004-11-18', 8, 'Active', '', NULL, '0776100645', '', 'CATHOLIC', 0.00, 0.00, '2026-11-09 09:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `pupil_exam_comments`
--

CREATE TABLE `pupil_exam_comments` (
  `comment_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `teacher_comment` text DEFAULT NULL,
  `conduct_remark` varchar(100) DEFAULT 'Good',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupil_exam_comments`
--

INSERT INTO `pupil_exam_comments` (`comment_id`, `pupil_id`, `exam_id`, `teacher_comment`, `conduct_remark`, `updated_at`) VALUES
(1, 2, 3, 'good boy', 'Excellent', '2026-11-07 12:35:01'),
(2, 4, 3, '', 'Good', '2026-11-07 12:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `pupil_parent`
--

CREATE TABLE `pupil_parent` (
  `pupil_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `relationship` varchar(30) DEFAULT 'Guardian'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupil_parent`
--

INSERT INTO `pupil_parent` (`pupil_id`, `parent_id`, `relationship`) VALUES
(1, 1, 'Guardian'),
(2, 2, 'Guardian'),
(3, 3, 'Guardian'),
(4, 4, 'Guardian'),
(5, 5, 'Guardian'),
(6, 6, 'Guardian'),
(7, 7, 'Guardian');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `marks` decimal(5,2) NOT NULL CHECK (`marks` >= 0 and `marks` <= 100),
  `grade` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `pupil_id`, `subject_id`, `exam_id`, `marks`, `grade`) VALUES
(3, 2, 9, 1, 96.00, 'D1'),
(5, 2, 10, 1, 89.00, 'D1'),
(6, 2, 8, 1, 99.00, 'D1'),
(7, 2, 14, 1, 87.00, 'D1'),
(8, 2, 11, 1, 88.00, 'D1'),
(14, 2, 9, 2, 87.00, 'D1'),
(16, 2, 10, 2, 89.00, 'D1'),
(17, 2, 8, 2, 88.00, 'D1'),
(18, 2, 14, 2, 98.30, 'D1'),
(19, 2, 11, 2, 84.30, 'D1'),
(20, 4, 9, 2, 88.00, 'D1'),
(21, 4, 10, 2, 78.00, 'D2'),
(22, 4, 8, 2, 90.00, 'D1'),
(23, 4, 14, 2, 99.00, 'D1'),
(24, 4, 11, 2, 100.00, 'D1'),
(35, 2, 10, 3, 89.00, 'D1'),
(36, 4, 10, 3, 78.00, 'D2'),
(37, 2, 9, 3, 99.00, 'D1'),
(38, 4, 9, 3, 89.00, 'D1'),
(39, 2, 8, 3, 90.00, 'D1'),
(40, 4, 8, 3, 88.00, 'D1'),
(41, 2, 14, 3, 99.00, 'D1'),
(42, 4, 14, 3, 89.00, 'D1'),
(43, 2, 11, 3, 90.00, 'D1'),
(44, 4, 11, 3, 67.00, 'C3'),
(49, 5, 9, 3, 90.00, 'D1'),
(50, 5, 10, 3, 85.00, 'D1'),
(51, 5, 8, 3, 88.00, 'D1'),
(52, 5, 11, 3, 79.00, 'D2'),
(57, 5, 9, 2, 98.00, 'D1'),
(58, 5, 10, 2, 73.10, 'C3'),
(59, 5, 8, 2, 69.00, 'C3'),
(60, 5, 11, 2, 78.00, 'D2'),
(61, 7, 9, 3, 67.00, 'C3'),
(62, 7, 10, 3, 80.00, 'D1'),
(63, 7, 8, 3, 70.00, 'C3'),
(64, 7, 11, 3, 69.00, 'C3'),
(65, 7, 9, 2, 100.00, 'D1'),
(66, 7, 10, 2, 100.00, 'D1'),
(67, 7, 8, 2, 30.00, 'F9'),
(68, 7, 11, 2, 100.00, 'D1');

-- --------------------------------------------------------

--
-- Table structure for table `school_expenses`
--

CREATE TABLE `school_expenses` (
  `expense_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` enum('Academic Supplies','Kitchen & Food','Utilities & Bills','Maintenance & Repairs','Transport & Fuel','Staff Welfare','Miscellaneous') NOT NULL DEFAULT 'Academic Supplies',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_measure` varchar(50) DEFAULT 'Pcs',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term` varchar(50) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `purchased_by` varchar(150) DEFAULT NULL,
  `receipt_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_expenses`
--

INSERT INTO `school_expenses` (`expense_id`, `item_name`, `category`, `quantity`, `unit_measure`, `unit_cost`, `total_cost`, `term`, `academic_year`, `expense_date`, `purchased_by`, `receipt_no`, `description`, `recorded_at`) VALUES
(2, 'Rice', 'Kitchen & Food', 10.00, 'Pcs', 4500.00, 45000.00, 'Term 1', 2026, '2026-11-07', 'Tr John', '', 'for cooking', '2026-11-07 12:59:34'),
(3, 'Posho', 'Kitchen & Food', 10.00, 'Kgs', 4000.00, 40000.00, 'Term 1', 2026, '2026-11-07', 'Tr John', '', '', '2026-11-07 18:06:25'),
(4, 'Books', 'Academic Supplies', 100.00, 'Pcs', 3500.00, 350000.00, 'Term 1', 2026, '2026-11-07', 'Tr John', '', '', '2026-11-07 18:10:33'),
(5, 'fuel', 'Transport & Fuel', 10.00, 'Litres', 6900.00, 69000.00, 'Term 1', 2026, '2026-11-07', 'Uncle Newton', '', '', '2026-11-07 18:11:46'),
(6, 'pens', 'Academic Supplies', 120.00, 'Pcs', 400.00, 48000.00, 'Term 1', 2026, '2026-11-09', 'tr aaron', '', 'classes', '2026-11-09 09:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`) VALUES
(1, 'MATH', 'MTC001'),
(3, 'SST', 'SST001'),
(4, 'SCI', 'SCI001'),
(5, 'ENG', 'ENG001'),
(8, 'Mathematics', 'MTC'),
(9, 'English Language', 'ENG'),
(10, 'Integrated Science', 'SCI'),
(11, 'Social Studies', 'SST'),
(12, 'Literacy I', 'LIT1'),
(13, 'Literacy II', 'LIT2'),
(14, 'Religious Education', 'RE'),
(15, 'Luganda', 'LUG'),
(16, 'Creative Arts & Physical Education', 'CAPE'),
(17, 'Local Language', 'LL'),
(18, 'Reading', 'READ'),
(19, 'Numbers', 'NUM'),
(20, 'Health Habits', 'HH'),
(21, 'Social Development', 'SD'),
(22, 'Language & Vocabulary', 'LANG'),
(23, 'Writing & Fine Motor Skills', 'WRITE'),
(24, 'Environmental & General Knowledge', 'ENV'),
(25, 'Rhymes & Music', 'RHY');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `staff_code` varchar(30) DEFAULT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `role` varchar(100) NOT NULL DEFAULT 'Teacher',
  `assigned_class_id` int(11) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `termly_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `staff_code`, `teacher_name`, `photo`, `phone`, `emergency_contact`, `qualification`, `role`, `assigned_class_id`, `dob`, `national_id`, `address`, `salary`, `termly_salary`, `amount_paid`) VALUES
(1, 'TR-26-02', 'Sarah', '', '0709508967', '', 'Masters', 'Class Teacher', 6, '1996-07-31', '', 'Kabawo', 350000.00, 1234000.00, 850000.00),
(2, 'TR-26-01', 'Kizza Sam', '', '070950876', '', 'Masters', 'Class Teacher', 7, '1990-02-19', '', 'Kitebi', 307000.00, 750000.00, 847000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_name` (`class_name`),
  ADD KEY `class_teacher_id` (`class_teacher_id`);

--
-- Indexes for table `class_assignments`
--
ALTER TABLE `class_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD PRIMARY KEY (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `pupil_id` (`pupil_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`fee_structure_id`),
  ADD UNIQUE KEY `unique_class_term_year` (`class_id`,`term`,`academic_year`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`payslip_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `pupils`
--
ALTER TABLE `pupils`
  ADD PRIMARY KEY (`pupil_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `pupil_exam_comments`
--
ALTER TABLE `pupil_exam_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD UNIQUE KEY `unique_pupil_exam` (`pupil_id`,`exam_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `pupil_parent`
--
ALTER TABLE `pupil_parent`
  ADD PRIMARY KEY (`pupil_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `pupil_id` (`pupil_id`,`subject_id`,`exam_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `school_expenses`
--
ALTER TABLE `school_expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_name` (`subject_name`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `staff_code` (`staff_code`),
  ADD KEY `assigned_class_id` (`assigned_class_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `class_assignments`
--
ALTER TABLE `class_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `fee_structure_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `payslip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pupils`
--
ALTER TABLE `pupils`
  MODIFY `pupil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pupil_exam_comments`
--
ALTER TABLE `pupil_exam_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `school_expenses`
--
ALTER TABLE `school_expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`class_teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `class_assignments`
--
ALTER TABLE `class_assignments`
  ADD CONSTRAINT `class_assignments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD CONSTRAINT `class_subject_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `pupils`
--
ALTER TABLE `pupils`
  ADD CONSTRAINT `pupils_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;

--
-- Constraints for table `pupil_exam_comments`
--
ALTER TABLE `pupil_exam_comments`
  ADD CONSTRAINT `pupil_exam_comments_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pupil_exam_comments_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `examinations` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `pupil_parent`
--
ALTER TABLE `pupil_parent`
  ADD CONSTRAINT `pupil_parent_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pupil_parent_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_3` FOREIGN KEY (`exam_id`) REFERENCES `examinations` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`assigned_class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;
--
-- Database: `comrades_smis`
--
CREATE DATABASE IF NOT EXISTS `comrades_smis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `comrades_smis`;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `pupil_id`, `attendance_date`, `status`, `remarks`, `recorded_by`) VALUES
(1, 1, '2026-03-02', 'Present', NULL, 4),
(2, 2, '2026-03-02', 'Absent', 'Parent notified the school', 4),
(3, 3, '2026-03-02', 'Late', 'Arrived after assembly', 4);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(30) NOT NULL,
  `stream` varchar(30) NOT NULL DEFAULT 'Main',
  `class_teacher_id` int(11) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 50,
  `academic_year` year(4) NOT NULL
) ;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `stream`, `class_teacher_id`, `capacity`, `academic_year`) VALUES
(1, 'P.1', 'Main', 1, 50, '2026'),
(2, 'P.2', 'Main', 2, 50, '2026'),
(3, 'P.3', 'Main', 1, 50, '2026'),
(4, 'P.4', 'Main', 2, 50, '2026'),
(5, 'P.5', 'Main', 1, 50, '2026'),
(6, 'P.6', 'Main', 2, 50, '2026'),
(7, 'P.7', 'Main', 1, 50, '2026');

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `exam_id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `term` enum('Term 1','Term 2','Term 3') NOT NULL,
  `academic_year` year(4) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`exam_id`, `exam_name`, `term`, `academic_year`, `start_date`, `end_date`) VALUES
(1, 'Beginning of Term Test', 'Term 1', '2026', '2026-03-09', '2026-03-13'),
(2, 'End of Term Examination', 'Term 1', '2026', '2026-04-20', '2026-04-24');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `term` enum('Term 1','Term 2','Term 3') NOT NULL,
  `academic_year` year(4) NOT NULL,
  `amount_due` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) GENERATED ALWAYS AS (`amount_due` - `amount_paid`) STORED,
  `payment_date` date DEFAULT NULL
) ;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `pupil_id`, `term`, `academic_year`, `amount_due`, `amount_paid`, `payment_date`) VALUES
(1, 1, 'Term 1', '2026', 300000.00, 250000.00, '2026-02-20'),
(2, 2, 'Term 1', '2026', 300000.00, 300000.00, '2026-02-06'),
(3, 3, 'Term 1', '2026', 280000.00, 100000.00, '2026-02-08');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Mobile Money','Bank','Other') NOT NULL,
  `receipt_no` varchar(40) NOT NULL,
  `received_by` int(11) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`payment_id`, `fee_id`, `amount_paid`, `payment_date`, `payment_method`, `receipt_no`, `received_by`, `notes`, `created_at`) VALUES
(1, 1, 200000.00, '2026-02-05', 'Mobile Money', 'CPS-RCPT-0001', 3, 'First instalment', '2026-11-05 08:24:07'),
(2, 1, 50000.00, '2026-02-20', 'Cash', 'CPS-RCPT-0002', 3, 'Second instalment', '2026-11-05 08:24:07'),
(3, 2, 300000.00, '2026-02-06', 'Bank', 'CPS-RCPT-0003', 3, 'Paid in full', '2026-11-05 08:24:07'),
(4, 3, 100000.00, '2026-02-08', 'Cash', 'CPS-RCPT-0004', 3, 'First instalment', '2026-11-05 08:24:07');

--
-- Triggers `fee_payments`
--
DELIMITER $$
CREATE TRIGGER `trg_fee_payment_after_insert` AFTER INSERT ON `fee_payments` FOR EACH ROW BEGIN
    UPDATE fees
    SET amount_paid = amount_paid + NEW.amount_paid,
        payment_date = NEW.payment_date
    WHERE fee_id = NEW.fee_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_fee_payment_before_insert` BEFORE INSERT ON `fee_payments` FOR EACH ROW BEGIN
    DECLARE current_balance DECIMAL(12,2);

    SELECT balance INTO current_balance
    FROM fees
    WHERE fee_id = NEW.fee_id
    FOR UPDATE;

    IF current_balance IS NULL THEN
        SIGNAL SQLSTATE '45000'
          SET MESSAGE_TEXT = 'Fee account was not found.';
    ELSEIF NEW.amount_paid > current_balance THEN
        SIGNAL SQLSTATE '45000'
          SET MESSAGE_TEXT = 'Payment exceeds the outstanding fee balance.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `parent_name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `parent_name`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'Grace Namusoke', '0700000001', 'grace@example.com', 'Kitebi, Kampala', '2026-11-05 08:24:07'),
(2, 'Peter Ssemanda', '0700000002', 'peter@example.com', 'Nateete, Kampala', '2026-11-05 08:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `pupils`
--

CREATE TABLE `pupils` (
  `pupil_id` int(11) NOT NULL,
  `admission_no` varchar(30) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `admission_date` date NOT NULL,
  `class_id` int(11) NOT NULL,
  `status` enum('Active','Transferred','Completed','Withdrawn') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pupils`
--

INSERT INTO `pupils` (`pupil_id`, `admission_no`, `first_name`, `last_name`, `gender`, `date_of_birth`, `admission_date`, `class_id`, `status`, `created_at`) VALUES
(1, 'CPS/2026/001', 'Amina', 'Nabirye', 'Female', '2015-03-12', '2026-01-20', 4, 'Active', '2026-11-05 08:24:07'),
(2, 'CPS/2026/002', 'Brian', 'Ssekandi', 'Male', '2014-08-05', '2026-01-20', 4, 'Active', '2026-11-05 08:24:07'),
(3, 'CPS/2026/003', 'Diana', 'Nansubuga', 'Female', '2016-01-22', '2026-01-20', 3, 'Active', '2026-11-05 08:24:07'),
(4, 'CPS/2026/0005', 'newton', 'nabimanya', 'Male', '2014-06-05', '2026-11-05', 5, 'Active', '2026-11-05 08:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `pupil_parents`
--

CREATE TABLE `pupil_parents` (
  `pupil_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `relationship_to_pupil` varchar(40) NOT NULL DEFAULT 'Parent/Guardian',
  `is_primary_contact` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pupil_parents`
--

INSERT INTO `pupil_parents` (`pupil_id`, `parent_id`, `relationship_to_pupil`, `is_primary_contact`) VALUES
(1, 1, 'Mother', 1),
(2, 2, 'Father', 1),
(3, 1, 'Mother', 1);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `marks` decimal(5,2) NOT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `pupil_id`, `subject_id`, `exam_id`, `marks`, `grade`, `teacher_id`, `remarks`) VALUES
(1, 1, 1, 1, 78.00, 'D2', 1, 'Good work'),
(2, 1, 2, 1, 84.00, 'D1', 1, 'Very good'),
(3, 1, 3, 1, 72.00, 'D2', 2, 'Good effort'),
(4, 2, 1, 1, 55.00, 'C3', 1, 'Needs more practice'),
(5, 2, 2, 1, 63.00, 'C2', 1, 'Keep improving'),
(6, 2, 3, 1, 58.00, 'C3', 2, 'Satisfactory'),
(7, 3, 1, 1, 81.00, 'D1', 1, 'Very good');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(80) NOT NULL,
  `subject_code` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`) VALUES
(1, 'Mathematics', 'MTC'),
(2, 'English', 'ENG'),
(3, 'Science', 'SCI'),
(4, 'Social Studies', 'SST');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `teacher_name` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `qualification` varchar(120) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `teacher_name`, `phone`, `email`, `qualification`, `hire_date`, `user_id`) VALUES
(1, 'Sarah Akello', '0710000001', 'sarah@example.com', 'Diploma in Primary Education', '2022-02-01', 4),
(2, 'John Mugisha', '0710000002', 'john@example.com', 'Bachelor of Education', '2021-01-15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','headteacher','teacher','bursar','parent') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'newton@gmail.com', '$2y$10$VHzaXSCeD5/cTIPsivqxs.v5sKI09AIcVd45uG8ZoW0SzhtwaXZYC', 'admin', 1, '2026-11-05 08:24:07'),
(2, 'headteacher', '$2y$10$replace_this_with_a_real_PHP_password_hash', 'headteacher', 1, '2026-11-05 08:24:07'),
(3, 'bursar', '$2y$10$replace_this_with_a_real_PHP_password_hash', 'bursar', 1, '2026-11-05 08:24:07'),
(4, 'teacher1', '$2y$10$replace_this_with_a_real_PHP_password_hash', 'teacher', 1, '2026-11-05 08:24:07'),
(5, 'Jane', '$2y$10$VtO8u/hDDkfS6FH8T7DUP.UWG4AESHPx3.qiD7HL6xG1Y/7HflHxm', 'parent', 1, '2026-11-06 08:40:47');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_class_attendance_today`
-- (See below for the actual view)
--
CREATE TABLE `v_class_attendance_today` (
`class_name` varchar(30)
,`attendance_date` date
,`recorded_pupils` bigint(21)
,`present_count` decimal(23,0)
,`absent_count` decimal(23,0)
,`late_count` decimal(23,0)
,`excused_count` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_class_performance`
-- (See below for the actual view)
--
CREATE TABLE `v_class_performance` (
`class_name` varchar(30)
,`exam_name` varchar(100)
,`term` enum('Term 1','Term 2','Term 3')
,`academic_year` year(4)
,`subject_name` varchar(80)
,`pupils_assessed` bigint(21)
,`average_marks` decimal(6,2)
,`highest_mark` decimal(5,2)
,`lowest_mark` decimal(5,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pupil_fee_balances`
-- (See below for the actual view)
--
CREATE TABLE `v_pupil_fee_balances` (
`pupil_id` int(11)
,`admission_no` varchar(30)
,`pupil_name` varchar(121)
,`class_name` varchar(30)
,`term` enum('Term 1','Term 2','Term 3')
,`academic_year` year(4)
,`amount_due` decimal(12,2)
,`amount_paid` decimal(12,2)
,`balance` decimal(12,2)
,`payment_status` varchar(14)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pupil_report_cards`
-- (See below for the actual view)
--
CREATE TABLE `v_pupil_report_cards` (
`admission_no` varchar(30)
,`pupil_name` varchar(121)
,`class_name` varchar(30)
,`exam_name` varchar(100)
,`term` enum('Term 1','Term 2','Term 3')
,`academic_year` year(4)
,`subject_name` varchar(80)
,`marks` decimal(5,2)
,`grade` varchar(5)
,`remarks` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure for view `v_class_attendance_today`
--
DROP TABLE IF EXISTS `v_class_attendance_today`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_class_attendance_today`  AS SELECT `c`.`class_name` AS `class_name`, `a`.`attendance_date` AS `attendance_date`, count(0) AS `recorded_pupils`, sum(`a`.`status` = 'Present') AS `present_count`, sum(`a`.`status` = 'Absent') AS `absent_count`, sum(`a`.`status` = 'Late') AS `late_count`, sum(`a`.`status` = 'Excused') AS `excused_count` FROM ((`attendance` `a` join `pupils` `p` on(`p`.`pupil_id` = `a`.`pupil_id`)) join `classes` `c` on(`c`.`class_id` = `p`.`class_id`)) WHERE `a`.`attendance_date` = curdate() GROUP BY `c`.`class_id`, `c`.`class_name`, `a`.`attendance_date` ;

-- --------------------------------------------------------

--
-- Structure for view `v_class_performance`
--
DROP TABLE IF EXISTS `v_class_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_class_performance`  AS SELECT `c`.`class_name` AS `class_name`, `e`.`exam_name` AS `exam_name`, `e`.`term` AS `term`, `e`.`academic_year` AS `academic_year`, `s`.`subject_name` AS `subject_name`, count(`r`.`result_id`) AS `pupils_assessed`, round(avg(`r`.`marks`),2) AS `average_marks`, max(`r`.`marks`) AS `highest_mark`, min(`r`.`marks`) AS `lowest_mark` FROM ((((`results` `r` join `pupils` `p` on(`p`.`pupil_id` = `r`.`pupil_id`)) join `classes` `c` on(`c`.`class_id` = `p`.`class_id`)) join `subjects` `s` on(`s`.`subject_id` = `r`.`subject_id`)) join `examinations` `e` on(`e`.`exam_id` = `r`.`exam_id`)) GROUP BY `c`.`class_id`, `c`.`class_name`, `e`.`exam_id`, `e`.`exam_name`, `e`.`term`, `e`.`academic_year`, `s`.`subject_id`, `s`.`subject_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_pupil_fee_balances`
--
DROP TABLE IF EXISTS `v_pupil_fee_balances`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pupil_fee_balances`  AS SELECT `p`.`pupil_id` AS `pupil_id`, `p`.`admission_no` AS `admission_no`, concat(`p`.`first_name`,' ',`p`.`last_name`) AS `pupil_name`, `c`.`class_name` AS `class_name`, `f`.`term` AS `term`, `f`.`academic_year` AS `academic_year`, `f`.`amount_due` AS `amount_due`, `f`.`amount_paid` AS `amount_paid`, `f`.`balance` AS `balance`, CASE WHEN `f`.`balance` = 0 THEN 'Cleared' WHEN `f`.`amount_paid` = 0 THEN 'Not Paid' ELSE 'Partially Paid' END AS `payment_status` FROM ((`fees` `f` join `pupils` `p` on(`p`.`pupil_id` = `f`.`pupil_id`)) join `classes` `c` on(`c`.`class_id` = `p`.`class_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_pupil_report_cards`
--
DROP TABLE IF EXISTS `v_pupil_report_cards`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pupil_report_cards`  AS SELECT `p`.`admission_no` AS `admission_no`, concat(`p`.`first_name`,' ',`p`.`last_name`) AS `pupil_name`, `c`.`class_name` AS `class_name`, `e`.`exam_name` AS `exam_name`, `e`.`term` AS `term`, `e`.`academic_year` AS `academic_year`, `s`.`subject_name` AS `subject_name`, `r`.`marks` AS `marks`, `r`.`grade` AS `grade`, `r`.`remarks` AS `remarks` FROM ((((`results` `r` join `pupils` `p` on(`p`.`pupil_id` = `r`.`pupil_id`)) join `classes` `c` on(`c`.`class_id` = `p`.`class_id`)) join `examinations` `e` on(`e`.`exam_id` = `r`.`exam_id`)) join `subjects` `s` on(`s`.`subject_id` = `r`.`subject_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `uq_pupil_attendance_day` (`pupil_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `fk_attendance_user` (`recorded_by`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `uq_class_stream_year` (`class_name`,`stream`,`academic_year`),
  ADD KEY `fk_class_teacher` (`class_teacher_id`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`exam_id`),
  ADD UNIQUE KEY `uq_exam_term_year` (`exam_name`,`term`,`academic_year`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD UNIQUE KEY `uq_pupil_fee_term_year` (`pupil_id`,`term`,`academic_year`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `fk_payment_fee` (`fee_id`),
  ADD KEY `fk_payment_user` (`received_by`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`),
  ADD UNIQUE KEY `uq_parent_phone` (`phone`);

--
-- Indexes for table `pupils`
--
ALTER TABLE `pupils`
  ADD PRIMARY KEY (`pupil_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `idx_pupil_class` (`class_id`);

--
-- Indexes for table `pupil_parents`
--
ALTER TABLE `pupil_parents`
  ADD PRIMARY KEY (`pupil_id`,`parent_id`),
  ADD KEY `fk_pp_parent` (`parent_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `uq_pupil_subject_exam` (`pupil_id`,`subject_id`,`exam_id`),
  ADD KEY `idx_results_exam` (`exam_id`),
  ADD KEY `fk_result_subject` (`subject_id`),
  ADD KEY `fk_result_teacher` (`teacher_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_name` (`subject_name`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pupils`
--
ALTER TABLE `pupils`
  MODIFY `pupil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_pupil` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_class_teacher` FOREIGN KEY (`class_teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fk_fee_pupil` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON UPDATE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fk_payment_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`fee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pupils`
--
ALTER TABLE `pupils`
  ADD CONSTRAINT `fk_pupil_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pupil_parents`
--
ALTER TABLE `pupil_parents`
  ADD CONSTRAINT `fk_pp_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_pupil` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_result_exam` FOREIGN KEY (`exam_id`) REFERENCES `examinations` (`exam_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_pupil` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teacher_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
--
-- Database: `db202`
--
CREATE DATABASE IF NOT EXISTS `db202` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db202`;
--
-- Database: `db2026`
--
CREATE DATABASE IF NOT EXISTS `db2026` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db2026`;
--
-- Database: `hajjat_restaurant`
--
CREATE DATABASE IF NOT EXISTS `hajjat_restaurant` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hajjat_restaurant`;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `description`, `price`, `category`, `image_path`, `available`, `created_at`) VALUES
(1, 'Matoke', 'Steamed green bananas served with groundnut sauce', 15000.00, 'Main Course', 'images/matoke.jpg', 1, '2026-11-10 11:26:06'),
(2, 'Luwombo', 'Traditional chicken cooked in banana leaves with sauce', 25000.00, 'Main Course', 'images/luwombo.jpg', 1, '2026-11-10 11:26:06'),
(3, 'Rolex', 'Chapati with fried eggs and vegetables - Kampala favorite', 8000.00, 'Quick Bites', 'images/rolex.jpg', 1, '2026-11-10 11:26:06'),
(4, 'Chicken Tika', 'Grilled chicken marinated in spices', 20000.00, 'Main Course', 'images/chicken_tika.jpg', 1, '2026-11-10 11:26:06'),
(5, 'Chapati', 'Freshly made flatbread', 3000.00, 'Quick Bites', 'images/chapati.jpg', 1, '2026-11-10 11:26:06'),
(6, 'Ugali & Fish', 'Cornmeal with fried fish and vegetables', 18000.00, 'Main Course', 'images/ugali_fish.jpg', 1, '2026-11-10 11:26:06'),
(7, 'Katogo', 'Matoke mixed with beans, groundnut sauce, and meat', 12000.00, 'Breakfast', 'images/katogo.jpg', 1, '2026-11-10 11:26:06'),
(8, 'Pilau Rice', 'Spiced rice with beef or chicken', 16000.00, 'Main Course', 'images/pilau.jpg', 1, '2026-11-10 11:26:06'),
(9, 'Gnut Soup', 'Creamy groundnut soup with meat and vegetables', 14000.00, 'Soup', 'images/gnut_soup.jpg', 1, '2026-11-10 11:26:06'),
(10, 'Muchomo', 'Grilled meat (goat/beef) with side dishes', 22000.00, 'Main Course', 'images/muchomo.jpg', 1, '2026-11-10 11:26:06'),
(11, 'Mandazi', 'Sweet fried doughnuts - perfect for breakfast', 2000.00, 'Breakfast', 'images/mandazi.jpg', 1, '2026-11-10 11:26:06'),
(12, 'Fresh Juice', 'Mango, Passion, or Orange juice', 5000.00, 'Beverages', 'images/juice.jpg', 1, '2026-11-10 11:26:06'),
(13, 'African Tea', 'Spiced tea with milk', 3000.00, 'Beverages', 'images/tea.jpg', 1, '2026-11-10 11:26:06'),
(14, 'Fresh Salad', 'Mixed greens with local vegetables', 8000.00, 'Salads', 'images/salad.jpg', 1, '2026-11-10 11:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `cashier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `num_guests` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer','cashier') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `created_at`) VALUES
(1, 'mellisa', 'mellisa@hajjat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mellisa Nandyowa', '0777123456', 'admin', '2026-11-10 11:26:06'),
(2, 'chelsea', 'chelsea@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chelsea Customer', '0777654321', 'customer', '2026-11-10 11:26:06'),
(3, 'joel', 'joel@hajjat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joel Cashier', '0777999888', 'cashier', '2026-11-10 11:26:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
--
-- Database: `henriqshots`
--
CREATE DATABASE IF NOT EXISTS `henriqshots` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `henriqshots`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_type` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `photo_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `photo_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `session_name`, `price`) VALUES
(1, 'Portrait', 50.00),
(2, 'Wedding', 200.00),
(3, 'Event', 150.00),
(4, 'Product', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(1, 'ben kato', 'newtonnabimanya789@gmail.com', '0709508697', '$2y$10$AE7ubiOHEegv72HcFKF3xOLSO5W.bjZ2.ABLVibGgDTH./Z7WDlyC', 'user', '2026-02-06 09:11:49'),
(2, 'newton na', 'newtom@gmail.com', '000934787', '$2y$10$w4LT4CDG3Q3119rnUKdVYelm/5zH1..Oo/PfZgXx57/XNBkzl7zo.', 'user', '2026-10-21 11:48:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
--
-- Database: `hosiptal_db`
--
CREATE DATABASE IF NOT EXISTS `hosiptal_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hosiptal_db`;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `Dno` varchar(4) NOT NULL,
  `Dname` varchar(100) NOT NULL,
  `Location` varchar(50) NOT NULL,
  `Contact` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`Dno`, `Dname`, `Location`, `Contact`) VALUES
('D001', 'Dental', 'central', '098678987');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` varchar(5) NOT NULL,
  `fname` varchar(26) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `fname`) VALUES
('E0001', 'joy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`Dno`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);
--
-- Database: `kids`
--
CREATE DATABASE IF NOT EXISTS `kids` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kids`;

-- --------------------------------------------------------

--
-- Table structure for table `our_kids`
--

CREATE TABLE `our_kids` (
  `pupil_id` varchar(20) NOT NULL,
  `FULL NAMES` varchar(100) NOT NULL,
  `PARENTS NAME` varchar(100) NOT NULL,
  `PHONE NUMBER` int(10) NOT NULL,
  `AMOUNT TO PAY` int(11) NOT NULL,
  `AMOUNT PAID` int(11) NOT NULL,
  `BALANCE` int(11) NOT NULL,
  `PAYEMENT CODE` int(11) NOT NULL,
  `STUTAS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `our_kids`
--

INSERT INTO `our_kids` (`pupil_id`, `FULL NAMES`, `PARENTS NAME`, `PHONE NUMBER`, `AMOUNT TO PAY`, `AMOUNT PAID`, `BALANCE`, `PAYEMENT CODE`, `STUTAS`) VALUES
('001', 'ben kato', 'sam kutesa', 987654321, 5000000, 356000, 1200000, 10098765, 2),
('002', 'ben skato', 'psam kutesa', 987654321, 5000000, 356000, 1200000, 10098765, 2);
--
-- Database: `magazine_db`
--
CREATE DATABASE IF NOT EXISTS `magazine_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `magazine_db`;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `article_no` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT '',
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin123', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `article_no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Database: `magz`
--
CREATE DATABASE IF NOT EXISTS `magz` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `magz`;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `article_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `editor_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `issue_id` int(11) DEFAULT NULL,
  `featured_image_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','under_review','revision_requested','approved','published','archived') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_media`
--

CREATE TABLE `article_media` (
  `article_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_status_logs`
--

CREATE TABLE `article_status_logs` (
  `log_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `changed_by_user_id` int(11) NOT NULL,
  `previous_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `editorial_notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `issue_id` int(11) NOT NULL,
  `volume_number` int(11) NOT NULL,
  `issue_number` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `publication_date` date DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `pdf_download_url` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `media_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `caption` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(40) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `student_staff_id` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `editor_id` (`editor_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `article_media`
--
ALTER TABLE `article_media`
  ADD PRIMARY KEY (`article_id`,`media_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `article_status_logs`
--
ALTER TABLE `article_status_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `changed_by_user_id` (`changed_by_user_id`);

--
-- Indexes for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_staff_id` (`student_staff_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_status_logs`
--
ALTER TABLE `article_status_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `articles_ibfk_4` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`issue_id`) ON DELETE SET NULL;

--
-- Constraints for table `article_media`
--
ALTER TABLE `article_media`
  ADD CONSTRAINT `article_media_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_media_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE;

--
-- Constraints for table `article_status_logs`
--
ALTER TABLE `article_status_logs`
  ADD CONSTRAINT `article_status_logs_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_status_logs_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;
--
-- Database: `members`
--
CREATE DATABASE IF NOT EXISTS `members` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `members`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone_number` int(10) DEFAULT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `pledge_amount` decimal(10,2) NOT NULL,
  `items_to_donate` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contact_id`, `full_name`, `email`, `phone_number`, `message`, `remarks`, `pledge_amount`, `items_to_donate`) VALUES
(3, 'PETER DAN', 'peter123@gmail.com', 98765645, 'LOVED THEY WHAY YOU CARE ABOUT THE CHILDREN', '', 0.00, NULL),
(5, 'NABIMANYA NEWTON', 'peter123@gmail.com', NULL, NULL, NULL, 89976655.00, ''),
(7, 'NABIMANYA NEWTON', 'peter123@gmail.com', 709698765, 'good job lot', '', 0.00, NULL),
(8, 'NABIMANYA NEWTON', 'newtonnabimanya789@gmail.com', NULL, NULL, NULL, 99999999.99, ''),
(10, 'NEWTON', 'newtonnabimanya978@gmail.com', NULL, NULL, NULL, 10000.00, ''),
(11, 'NEWTON', 'newtonnabimanya978@gmail.com', NULL, NULL, NULL, 10000.00, ''),
(12, 'NEWTON', 'newtonnabimanya978@gmail.com', NULL, NULL, NULL, 250000.00, ''),
(13, 'MUKALAZI PIUS', 'mukalazipius79@gmail.com', 2147483647, 'i have made a clear and clean visit', '', 0.00, NULL),
(14, 'Sam Kutesa', 'sam@gmail.com', 2147483647, 'yes sir ', '', 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `our_members`
--

CREATE TABLE `our_members` (
  `donation_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `target_project` varchar(150) DEFAULT 'General Fund',
  `currency` varchar(10) DEFAULT 'UGX',
  `pledge_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `items_to_donate` varchar(300) DEFAULT NULL,
  `pickup_location` text DEFAULT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `agreed_to_gift` tinyint(1) DEFAULT 1,
  `edit_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `our_members`
--

INSERT INTO `our_members` (`donation_id`, `full_name`, `email`, `target_project`, `currency`, `pledge_amount`, `items_to_donate`, `pickup_location`, `pickup_time`, `agreed_to_gift`, `edit_count`) VALUES
(2, 'STAR TIMES', 'startimes@gmail.com', 'General Fund', 'UGX', 250000.00, 'free wifie', NULL, NULL, 1, 0),
(3, 'MUKALAZI PIUS', 'mukalazipius79@gmail.com', 'General Fund', 'UGX', 20000.00, 'my old clothes', NULL, NULL, 1, 0),
(5, 'AARON', 'atuhairweaaron@gmail.com', 'General Fund', 'UGX', 2000.00, 'boxer 15 used', NULL, NULL, 1, 0),
(6, 'NEWTON', 'newtonnabimanya978@gmail.com', 'General Fund', 'UGX', 2500000.00, 'and land in mengo', NULL, NULL, 1, 0),
(7, 'NEWTON NABIMANYA', 'newtonnabimanya978@gmail.com', 'General Fund', 'UGX', 100000.00, '20KG OF SUGAR', 'Ssentema Bbira', '2023-12-13 14:00:00', 1, 1),
(8, 'NEWTON NABIMANYA', 'newtonnabimanya978@gmail.com', 'General Fund', 'UGX', 50000.00, '20 bags of cement', 'mengo', '2026-12-03 14:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'NEWTON', 'newtonnabimanya@gmail.com', '$2y$10$J.MPOvh0zxSYHiTDTlwGienWrUI6es3tM9JoSQK3OvgSOWqDvCi.y', '2026-11-10 08:51:37'),
(2, 'NEWTON', 'newtonnabimanya978@gmail.com', '$2y$10$jDUVk8VPWRLlMF/YtSDrcechhJxV/CL9i2v0P1DlLgJOP7p9Fxmhq', '2026-11-10 08:52:40'),
(3, 'MUKALAZI PIUS', 'mukalazipius79@gmail.com', '$2y$10$t62gT8TcyyeZzuEdIwYsJuR7ZXIFnPS4tBaa0wzbD4DDBsnoGTSz.', '2026-11-10 09:39:04'),
(4, 'SAMUEL ANDREW WALIGGO', 'waliggosamuel@gmail.com', '$2y$10$ifR0PqcGbaNAlGHKf3HG.uZF5hPld.xWOtFdB3KXqPdyj8vxh5R26', '2026-11-10 13:31:25'),
(5, 'NAAPYO PATIENCE RUTH', 'patience@gmail.com', '$2y$10$CPoHSKBHViJKA.G6MuNk/eiIWG.dq30V669.7LQ.7IoId9Fx4YKde', '2026-11-10 13:32:45'),
(6, 'ATUHAIRWE AARON', 'atuhairweaaron@gmail.com', '$2y$10$a3UfSCMFwwSXnuOaU6hoMOa24mvbq8hvMVDNuZWH5QrTQevx/LzN2', '2026-11-10 13:34:11'),
(7, 'Mugalu Stuart', 'mugalustuart@gamil.com', '$2y$10$8K2iIZEClUj7eM/FZVt9OO6pCsumIZ8t1PJXHZVz6ytktC2vnipeS', '2026-11-10 13:38:40'),
(8, 'Sam Kutesa', 'sam@gmail.com', '$2y$10$lub51fxWbLDsFCKTqt5o7eai9XoxQIlhaGL.LwfANDpcTdhQ6ft/e', '2026-11-12 15:24:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `our_members`
--
ALTER TABLE `our_members`
  ADD PRIMARY KEY (`donation_id`);

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
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `our_members`
--
ALTER TABLE `our_members`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

--
-- Dumping data for table `pma__export_templates`
--

INSERT INTO `pma__export_templates` (`id`, `username`, `export_type`, `template_name`, `template_data`) VALUES
(1, 'root', 'server', 'comrades_prep_school_db', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"db_select[]\":[\"comrades_dbb\",\"comrades_prep_db\",\"comrades_prep_school_db\",\"comrades_smis\",\"db202\",\"db2026\",\"hajjat_restaurant\",\"henriqshots\",\"hosiptal_db\",\"kids\",\"magazine_db\",\"magz\",\"members\",\"phpmyadmin\",\"practice1\",\"schools_db\",\"school_db\",\"test\"],\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@SERVER@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Structure of table @TABLE@\",\"latex_structure_continued_caption\":\"Structure of table @TABLE@ (continued)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Content of table @TABLE@\",\"latex_data_continued_caption\":\"Content of table @TABLE@ (continued)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"yaml_structure_or_data\":\"data\",\"\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_drop_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_procedure_function\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"practice1\",\"table\":\"employees\"},{\"db\":\"kids\",\"table\":\"our_kids\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2026-11-13 11:56:21', '{\"Console\\/Mode\":\"collapse\",\"NavigationWidth\":0}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `practice1`
--
CREATE DATABASE IF NOT EXISTS `practice1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `practice1`;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `location`, `created_at`) VALUES
(1, 'IT', 'Building A, Floor 3', '2026-10-12 11:58:59'),
(2, 'Operations', 'Building B, Floor 1', '2026-10-12 11:58:59'),
(3, 'Analytics', 'Building A, Floor 2', '2026-10-12 11:58:59'),
(4, 'Human Resources', 'Building C, Floor 1', '2026-10-12 11:58:59'),
(5, 'Finance', 'Building C, Floor 2', '2026-10-12 11:58:59'),
(6, 'Marketing', 'Building B, Floor 2', '2026-10-12 11:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `job_title` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `manager_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone_number`, `job_title`, `department`, `salary`, `department_id`, `status`, `manager_id`) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', '555-0101', 'Operations Lead', 'IT', 95000.00, 2, NULL, NULL),
(2, 'Jane', 'Smith', 'jane.smith@example.com', '555-0102', 'Project Manager', 'Operations', 92000.00, 2, NULL, NULL),
(3, 'Michael', 'Brown', 'michael.brown@example.com', '555-0103', 'Data Analyst', 'Analytics', 68000.00, 3, NULL, NULL),
(4, 'Emily', 'Davis', 'emily.davis@example.com', '555-0104', 'HR Specialist', 'Human Resources', 62000.00, 4, NULL, NULL),
(5, 'David', 'Wilson', 'david.wilson@example.com', '555-0105', 'Financial Analyst', 'Finance', 75000.00, 5, NULL, NULL),
(6, 'Sarah', 'Taylor', 'sarah.taylor@example.com', '555-0106', 'QA Engineer', 'IT', 71000.00, 1, NULL, NULL),
(7, 'James', 'Anderson', 'james.anderson@example.com', '555-0107', 'DevOps Specialist', 'IT', 98000.00, 1, NULL, NULL),
(8, 'Laura', 'Martinez', 'laura.martinez@example.com', '555-0108', 'Marketing Lead', 'Marketing', 79000.00, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_employee_details`
-- (See below for the actual view)
--
CREATE TABLE `vw_employee_details` (
`employee_id` int(11)
,`first_name` varchar(50)
,`last_name` varchar(50)
,`job_title` varchar(50)
,`salary` decimal(10,2)
,`department_name` varchar(50)
,`location` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_employee_details`
--
DROP TABLE IF EXISTS `vw_employee_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_employee_details`  AS SELECT `e`.`employee_id` AS `employee_id`, `e`.`first_name` AS `first_name`, `e`.`last_name` AS `last_name`, `e`.`job_title` AS `job_title`, `e`.`salary` AS `salary`, `d`.`department_name` AS `department_name`, `d`.`location` AS `location` FROM (`employees` `e` left join `departments` `d` on(`e`.`department_id` = `d`.`department_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_dept_status` (`department_id`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE;
--
-- Database: `schools_db`
--
CREATE DATABASE IF NOT EXISTS `schools_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `schools_db`;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(20) NOT NULL,
  `stream` varchar(20) DEFAULT NULL,
  `academic_year` int(11) NOT NULL,
  `base_fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `stream`, `academic_year`, `base_fee`) VALUES
(1, 'Primary 1', 'Blue', 2026, 0.00),
(2, 'Primary 1', 'Red', 2026, 0.00),
(3, 'Primary 2', 'Blue', 2026, 0.00),
(4, 'Primary 2', 'Red', 2026, 0.00),
(5, 'Primary 3', 'Blue', 2026, 0.00),
(6, 'Primary 3', 'Red', 2026, 0.00),
(7, 'Primary 4', 'North', 2026, 0.00),
(8, 'Primary 4', 'South', 2026, 0.00),
(9, 'Primary 5', 'North', 2026, 0.00),
(10, 'Primary 5', 'South', 2026, 0.00),
(11, 'Primary 6', 'Eagle', 2026, 0.00),
(12, 'Primary 6', 'Falcon', 2026, 750000.00),
(13, 'Primary 7', 'Candidate', 2026, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `term` enum('Term 1','Term 2','Term 3') NOT NULL,
  `academic_year` int(11) NOT NULL,
  `continuous_assessment` decimal(5,2) DEFAULT 0.00,
  `exam_score` decimal(5,2) DEFAULT 0.00,
  `total_score` decimal(5,2) GENERATED ALWAYS AS (`continuous_assessment` + `exam_score`) STORED,
  `teacher_remarks` text DEFAULT NULL,
  `entered_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `user_id`, `phone_number`, `address`) VALUES
(1, 5, '0709508697', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Bank Deposit','Mobile Money') NOT NULL,
  `term` enum('Term 1','Term 2','Term 3') NOT NULL,
  `academic_year` int(11) NOT NULL,
  `recorded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `clearance_status` enum('Cleared','Partial','Blocked') DEFAULT 'Blocked',
  `custom_fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `parent_id`, `class_id`, `reg_no`, `first_name`, `last_name`, `gender`, `date_of_birth`, `clearance_status`, `custom_fee`) VALUES
(1, NULL, 1, 12, 'STU/2026/001', 'Newton', 'Nabimanya', 'Male', NULL, 'Blocked', 678000.00);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(50) NOT NULL,
  `subject_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`) VALUES
(1, 'Mathematics', 'MTH'),
(2, 'English Language', 'ENG'),
(3, 'Integrated Science', 'SCI'),
(4, 'Social Studies', 'SST'),
(5, 'Religious Education', 'RE'),
(6, 'Literacy', 'LIT'),
(7, 'Physical Education', 'PE');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','teacher','parent') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `full_name`, `email`, `created_at`) VALUES
(1, 'NABIMANYA', '$2y$10$rYjU/xizcD2yl2Jh.xkj4.PD/CyPK7P2RHZCEKyJcUkVaXwhhxUqi', 'teacher', 'NEWTON', 'newtonnabimanya978@gmail.com', '2026-11-09 13:18:51'),
(2, 'admin', '$2y$10$wT0lV3p8R5k9Q2g1vH7uZe7J8Y3X4W5V6U7T8S9R0P1O2N3M4L5K6', 'admin', 'System Administrator', 'admin@school.com', '2026-11-09 13:21:05'),
(4, 'ben', '$2y$10$jEmKh19Gh8lLjCWMcbNkJ.y2FfXXc4lZFPzpaIMkllO5tt4b7FXu2', 'teacher', 'NEWTON', 'newtonnabimanya978@gmail.com', '2026-11-09 13:56:53'),
(5, 'benn', '$2y$10$qDtQQoiRBGZRaFhANGAkeuhr0m6rkqfD7hKHkhRkdVLrm9SkY7TQa', 'parent', 'NEWTON', 'newtonnabimanya78@gmail.com', '2026-11-10 07:48:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `entered_by` (`entered_by`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `marks_ibfk_3` FOREIGN KEY (`entered_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;
--
-- Database: `school_db`
--
CREATE DATABASE IF NOT EXISTS `school_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `school_db`;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `pupil_id`, `date`, `status`) VALUES
(1, 1, '2026-11-02', 'Present'),
(2, 2, '2026-11-02', 'Late'),
(3, 3, '2026-11-02', 'Absent'),
(4, 1, '2026-11-03', 'Present'),
(5, 2, '2026-11-03', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `class_teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `class_teacher_id`) VALUES
(1, 'Grade 5A', 1),
(2, 'Grade 6B', 2);

-- --------------------------------------------------------

--
-- Table structure for table `class_subject`
--

CREATE TABLE `class_subject` (
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subject`
--

INSERT INTO `class_subject` (`class_id`, `subject_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `exam_id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `term` varchar(20) NOT NULL,
  `academic_year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`exam_id`, `exam_name`, `term`, `academic_year`) VALUES
(1, 'Mid-Term Exam', 'Term 1', '2026'),
(2, 'Final Exam', 'Term 1', '2026');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term` varchar(20) NOT NULL,
  `academic_year` year(4) NOT NULL,
  `payment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `pupil_id`, `amount_paid`, `balance`, `term`, `academic_year`, `payment_date`) VALUES
(1, 1, 500.00, 0.00, 'Term 1', '2026', '2026-10-15'),
(2, 2, 300.00, 200.00, 'Term 1', '2026', '2026-10-16'),
(3, 3, 250.00, 250.00, 'Term 1', '2026', '2026-10-20');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `parent_name`, `phone`, `address`, `created_at`) VALUES
(1, 'John & Mary Smith', '555-0201', '123 Oak Street, Springfield', '2026-11-06 08:54:25'),
(2, 'David Johnson', '555-0202', '456 Pine Avenue, Springfield', '2026-11-06 08:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `pupils`
--

CREATE TABLE `pupils` (
  `pupil_id` int(11) NOT NULL,
  `admission_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('M','F','Other') NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupils`
--

INSERT INTO `pupils` (`pupil_id`, `admission_no`, `first_name`, `last_name`, `gender`, `class_id`, `created_at`) VALUES
(1, 'ADM2026001', 'Alice', 'Smith', 'F', 1, '2026-11-06 08:54:25'),
(2, 'ADM2026002', 'Bob', 'Smith', 'M', 1, '2026-11-06 08:54:25'),
(3, 'ADM2026003', 'Charlie', 'Johnson', 'M', 2, '2026-11-06 08:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `pupil_parent`
--

CREATE TABLE `pupil_parent` (
  `pupil_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `relationship` varchar(30) DEFAULT 'Guardian'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pupil_parent`
--

INSERT INTO `pupil_parent` (`pupil_id`, `parent_id`, `relationship`) VALUES
(1, 1, 'Mother'),
(2, 1, 'Mother'),
(3, 2, 'Father');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `pupil_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `marks` decimal(5,2) DEFAULT NULL CHECK (`marks` >= 0 and `marks` <= 100),
  `grade` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `pupil_id`, `subject_id`, `exam_id`, `marks`, `grade`) VALUES
(1, 1, 1, 1, 92.50, 'A'),
(2, 1, 2, 1, 88.00, 'B'),
(3, 2, 1, 1, 74.00, 'C'),
(4, 2, 2, 1, 81.50, 'B'),
(5, 3, 1, 1, 65.00, 'D');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`) VALUES
(1, 'Mathematics', 'MATH101'),
(2, 'English Language', 'ENG101'),
(3, 'Science', 'SCI101');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `teacher_name`, `phone`, `qualification`, `created_at`) VALUES
(1, 'Sarah Jenkins', '555-0101', 'M.Ed. Mathematics', '2026-11-06 08:54:25'),
(2, 'Robert Chen', '555-0102', 'B.Sc. Physics', '2026-11-06 08:54:25'),
(3, 'Emily Davis', '555-0103', 'B.A. English Literature', '2026-11-06 08:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject`
--

CREATE TABLE `teacher_subject` (
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject`
--

INSERT INTO `teacher_subject` (`teacher_id`, `subject_id`) VALUES
(1, 1),
(2, 3),
(3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Teacher','Parent','Student') NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `person_id`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2a$12$eImiTXuWVxfM37uY4JANjO...hash', 'Admin', NULL, 1, '2026-11-06 08:54:26'),
(2, 'sjenkins', '$2a$12$eImiTXuWVxfM37uY4JANjO...hash', 'Teacher', 1, 1, '2026-11-06 08:54:26'),
(3, 'jsmith', '$2a$12$eImiTXuWVxfM37uY4JANjO...hash', 'Parent', 1, 1, '2026-11-06 08:54:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `pupil_id` (`pupil_id`,`date`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_name` (`class_name`),
  ADD UNIQUE KEY `class_teacher_id` (`class_teacher_id`);

--
-- Indexes for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD PRIMARY KEY (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `pupil_id` (`pupil_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`);

--
-- Indexes for table `pupils`
--
ALTER TABLE `pupils`
  ADD PRIMARY KEY (`pupil_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `pupil_parent`
--
ALTER TABLE `pupil_parent`
  ADD PRIMARY KEY (`pupil_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `pupil_id` (`pupil_id`,`subject_id`,`exam_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_name` (`subject_name`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD PRIMARY KEY (`teacher_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pupils`
--
ALTER TABLE `pupils`
  MODIFY `pupil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`class_teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD CONSTRAINT `class_subject_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE;

--
-- Constraints for table `pupils`
--
ALTER TABLE `pupils`
  ADD CONSTRAINT `pupils_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pupil_parent`
--
ALTER TABLE `pupil_parent`
  ADD CONSTRAINT `pupil_parent_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pupil_parent_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`pupil_id`) REFERENCES `pupils` (`pupil_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_3` FOREIGN KEY (`exam_id`) REFERENCES `examinations` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD CONSTRAINT `teacher_subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
