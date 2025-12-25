-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 03:12 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clearance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_records`
--

CREATE TABLE `academic_records` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(30) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `courses_passed` int(11) DEFAULT NULL,
  `courses_failed` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `academic_records`
--

INSERT INTO `academic_records` (`id`, `matric_no`, `academic_year`, `semester`, `cgpa`, `courses_passed`, `courses_failed`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'CS/2019/001', '2023/2024', 'First', '3.75', 8, 0, 'Excellent performance', '2025-12-14 20:12:13', '2025-12-14 20:12:13'),
(2, 'CS/2019/001', '2023/2024', 'Second', '3.82', 7, 1, 'Good performance, one course to retake', '2025-12-14 20:12:13', '2025-12-14 20:12:13'),
(3, 'CS/2019/002', '2023/2024', 'First', '3.45', 7, 1, 'Good performance', '2025-12-14 20:12:13', '2025-12-14 20:12:13'),
(4, 'CS/2019/002', '2023/2024', 'Second', '3.68', 8, 0, 'Improved performance', '2025-12-14 20:12:13', '2025-12-14 20:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `matric_no` varchar(30) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `certificate_type` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_by_name` varchar(100) NOT NULL,
  `download_count` int(11) DEFAULT 0,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('active','revoked','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clearance_history`
--

CREATE TABLE `clearance_history` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `action` enum('requested','approved','rejected','commented','updated','referred_to_registry','registry_approved','certificate_issued') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_by_name` varchar(100) DEFAULT NULL,
  `performed_role` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `clearance_history`
--

INSERT INTO `clearance_history` (`id`, `request_id`, `action`, `performed_by`, `performed_by_name`, `performed_role`, `notes`, `created_at`) VALUES
(1, 4, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:29'),
(2, 5, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:29'),
(3, 6, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(4, 7, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(5, 8, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(6, 9, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(7, 10, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(8, 11, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(9, 12, 'requested', 8, NULL, 'student', 'Student requested clearance', '2025-12-14 20:17:30'),
(10, 10, 'approved', 3, NULL, 'library', 'Clearance approved. Comments: ', '2025-12-14 20:33:43'),
(11, 10, 'rejected', 3, NULL, 'library', 'Clearance rejected. Comments: ', '2025-12-14 20:33:51'),
(12, 10, 'approved', 3, 'Library Staff', 'library', 'Clearance approved. Comments: ', '2025-12-14 20:39:36'),
(13, 10, 'rejected', 3, 'Library Staff', 'library', 'Clearance rejected. Comments: ', '2025-12-14 20:39:48'),
(14, 10, 'approved', 3, 'Library Staff', 'library', 'Clearance approved. Comments: ', '2025-12-14 20:44:05'),
(15, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 20:56:15'),
(16, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 20:56:21'),
(17, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 20:57:21'),
(18, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 20:58:22'),
(19, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 20:59:23'),
(20, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:12:19'),
(21, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:12:28'),
(22, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:12:49'),
(23, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:12:55'),
(24, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:13:55'),
(25, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:14:42'),
(26, 10, 'rejected', 3, NULL, 'library', '', '2025-12-14 21:14:46'),
(27, 10, 'approved', 3, NULL, 'library', '', '2025-12-14 21:15:14'),
(28, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:19:57'),
(29, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:20:59'),
(30, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:21:14'),
(31, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:22:15'),
(32, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:22:23'),
(33, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:23:23'),
(34, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:24:24'),
(35, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:25:25'),
(36, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:26:26'),
(37, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:27:27'),
(38, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:28:29'),
(39, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:29:30'),
(40, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:30:34'),
(41, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:31:37'),
(42, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:32:38'),
(43, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:33:39'),
(44, 4, 'approved', 4, NULL, 'bursary', '', '2025-12-14 21:34:40');

-- --------------------------------------------------------

--
-- Table structure for table `clearance_requests`
--

CREATE TABLE `clearance_requests` (
  `id` int(11) NOT NULL,
  `request_code` varchar(20) NOT NULL,
  `matric_no` varchar(30) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_department` varchar(100) DEFAULT NULL,
  `student_faculty` varchar(100) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `unit_code` varchar(20) DEFAULT NULL,
  `unit_name` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected','on_hold','referred_to_registry') DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approver_name` varchar(100) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registry_reviewed` tinyint(1) DEFAULT 0,
  `registry_comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `clearance_requests`
--

INSERT INTO `clearance_requests` (`id`, `request_code`, `matric_no`, `student_name`, `student_department`, `student_faculty`, `unit_id`, `unit_code`, `unit_name`, `status`, `comments`, `approved_by`, `approver_name`, `approved_at`, `requested_at`, `updated_at`, `registry_reviewed`, `registry_comments`) VALUES
(4, 'CLR-20251214-CS-4535', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 2, NULL, 'Bursary Department', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:29', '2025-12-14 20:17:29', 0, NULL),
(5, 'CLR-20251214-CS-9344', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 3, NULL, 'Departmental Head', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:29', '2025-12-14 20:17:29', 0, NULL),
(6, 'CLR-20251214-CS-8628', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 7, NULL, 'Examination Unit', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:29', '2025-12-14 20:17:29', 0, NULL),
(7, 'CLR-20251214-CS-1424', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 4, NULL, 'Faculty Officer', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL),
(8, 'CLR-20251214-CS-1000', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 5, NULL, 'Head of Department', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL),
(9, 'CLR-20251214-CS-4117', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 8, NULL, 'Hostel Affairs', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL),
(10, 'CLR-20251214-CS-5746', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 1, NULL, 'Library Department', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL),
(11, 'CLR-20251214-CS-1011', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 9, NULL, 'Medical Center', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL),
(12, 'CLR-20251214-CS-6070', 'CS/2019/002', 'Jane Smith', 'Computer Science', NULL, 6, NULL, 'Registry Department', 'pending', NULL, NULL, NULL, NULL, '2025-12-14 20:17:30', '2025-12-14 20:17:30', 0, NULL);

--
-- Triggers `clearance_requests`
--
DELIMITER $$
CREATE TRIGGER `update_registry_status` AFTER UPDATE ON `clearance_requests` FOR EACH ROW BEGIN
    DECLARE total_units INT;
    DECLARE cleared_units INT;
    DECLARE student_matric VARCHAR(30);
    
    -- Get student matric number
    SET student_matric = NEW.matric_no;
    
    -- Count total active units
    SELECT COUNT(*) INTO total_units 
    FROM clearance_units 
    WHERE is_active = TRUE;
    
    -- Count approved clearance units for this student
    SELECT COUNT(*) INTO cleared_units 
    FROM clearance_requests cr
    JOIN clearance_units cu ON cr.unit_id = cu.id
    WHERE cr.matric_no = student_matric 
    AND cr.status = 'approved'
    AND cu.is_active = TRUE;
    
    -- Update registry if all units are cleared
    IF cleared_units >= total_units THEN
        UPDATE registry 
        SET all_units_cleared = TRUE,
            clearance_status = 'processing',
            updated_at = CURRENT_TIMESTAMP
        WHERE matric_no = student_matric;
    END IF;
    
    -- If registry approval is required and unit is approved, mark as referred to registry
    IF NEW.status = 'approved' THEN
        SELECT requires_registry_approval INTO @requires_registry 
        FROM clearance_units 
        WHERE id = NEW.unit_id;
        
        IF @requires_registry = 1 THEN
            UPDATE clearance_requests 
            SET status = 'referred_to_registry'
            WHERE id = NEW.id;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `clearance_units`
--

CREATE TABLE `clearance_units` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `approval_role` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `requires_registry_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `clearance_units`
--

INSERT INTO `clearance_units` (`id`, `unit_code`, `unit_name`, `description`, `approval_role`, `is_active`, `requires_registry_approval`) VALUES
(1, 'LIB', 'Library Department', 'Check for borrowed books and outstanding fines', 'library', 1, 0),
(2, 'BUR', 'Bursary Department', 'Check for outstanding school fees and payments', 'bursary', 1, 0),
(3, 'DEP', 'Departmental Head', 'Academic and departmental clearance', 'department', 1, 0),
(4, 'FAC', 'Faculty Officer', 'Faculty-level clearance and approval', 'faculty', 1, 0),
(5, 'HOD', 'Head of Department', 'Final departmental approval', 'department', 1, 0),
(6, 'REG', 'Registry Department', 'Final registration and certificate processing', 'registry', 1, 1),
(7, 'EXM', 'Examination Unit', 'Examination clearance and results verification', 'admin', 1, 0),
(8, 'HOS', 'Hostel Affairs', 'Hostel dues and property clearance', 'admin', 1, 0),
(9, 'MED', 'Medical Center', 'Medical and health clearance', 'admin', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','registry') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(10, 8, 'Clearance approved', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:33:43'),
(11, 8, 'Clearance rejected', 'Your Library Department clearance has been rejected. ', 'error', 0, '2025-12-14 20:33:51'),
(12, 8, 'Clearance approved', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:39:36'),
(13, 8, 'Clearance rejected', 'Your Library Department clearance has been rejected. ', 'error', 0, '2025-12-14 20:39:48'),
(14, 8, 'Clearance approved', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:44:05'),
(15, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:56:15'),
(16, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:56:21'),
(17, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:57:22'),
(18, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:58:23'),
(19, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 20:59:23'),
(20, 8, 'Clearance Update', 'Your library clearance has been approved. ', 'success', 0, '2025-12-14 21:12:19'),
(21, 8, 'Clearance Update', 'Your library clearance has been approved. ', 'success', 0, '2025-12-14 21:12:28'),
(22, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 21:12:49'),
(23, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 21:12:55'),
(24, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 21:13:56'),
(25, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 21:14:42'),
(26, 8, 'Clearance Update', 'Your Library Department clearance has been rejected. ', 'error', 0, '2025-12-14 21:14:46'),
(27, 8, 'Clearance Update', 'Your Library Department clearance has been approved. ', 'success', 0, '2025-12-14 21:15:14'),
(28, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:19:57'),
(29, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:21:00'),
(30, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:21:15'),
(31, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:22:15'),
(32, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:22:23'),
(33, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:23:24'),
(34, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:24:24'),
(35, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:25:26'),
(36, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:26:27'),
(37, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:27:27'),
(38, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:28:29'),
(39, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:29:31'),
(40, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:30:36'),
(41, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:31:38'),
(42, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:32:39'),
(43, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:33:39'),
(44, 8, 'Clearance Update', 'Your Bursary Department clearance has been approved. ', 'success', 0, '2025-12-14 21:34:40');

-- --------------------------------------------------------

--
-- Table structure for table `registry`
--

CREATE TABLE `registry` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(30) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `year_of_graduation` year(4) NOT NULL,
  `certificate_type` enum('bachelor','master','phd','diploma','certificate') DEFAULT 'bachelor',
  `clearance_status` enum('pending','processing','approved','rejected','completed') DEFAULT 'pending',
  `all_units_cleared` tinyint(1) DEFAULT 0,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_number` varchar(50) DEFAULT NULL,
  `certificate_issue_date` date DEFAULT NULL,
  `registry_officer_id` int(11) DEFAULT NULL,
  `registry_officer_name` varchar(100) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `registry`
--

INSERT INTO `registry` (`id`, `matric_no`, `student_name`, `department`, `faculty`, `year_of_graduation`, `certificate_type`, `clearance_status`, `all_units_cleared`, `certificate_issued`, `certificate_number`, `certificate_issue_date`, `registry_officer_id`, `registry_officer_name`, `comments`, `created_at`, `updated_at`) VALUES
(1, 'CS/2019/001', 'John Doe', 'Computer Science', 'Faculty of Science', 2024, 'bachelor', 'pending', 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-14 20:12:13', '2025-12-14 20:12:13'),
(2, 'CS/2019/002', 'Jane Smith', 'Computer Science', 'Faculty of Science', 2024, 'bachelor', 'pending', 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-14 20:12:13', '2025-12-14 20:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('student','library','bursary','department','faculty','admin','registry') NOT NULL,
  `matric_no` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `matric_no`, `phone`, `department`, `faculty`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'admin', 'admin@university.edu', '$2y$10$6R2ndlmXOXLOn2yHkDBjq.vsfxKV7uvP5dFg9QcqaDBFCy2mBOOKi', 'System Administrator', 'admin', NULL, '+1234567890', 'Administration', 'General', '2025-12-14 20:12:13', NULL, 1),
(2, 'registry.staff', 'registry@university.edu', '$2y$10$a3GMQ/qw1T5Q.N2pQW0b4./90CEiCEjvGrg0O6c98lRpEUtLv7Aba', 'Registry Officer', 'registry', NULL, '+1234567891', 'Registry Department', 'General', '2025-12-14 20:12:13', '2025-12-25 01:41:35', 1),
(3, 'lib.staff', 'library@university.edu', '$2y$10$0T/aVtQ4SwvPck0K8.ul4OyCarWm175uqaak5b63Y0nMCkbNyFahu', 'Library Staff', 'library', NULL, '+1234567892', 'Library', 'General', '2025-12-14 20:12:13', '2025-12-14 20:55:52', 1),
(4, 'bursary.staff', 'bursary@university.edu', '$2y$10$jdmWpyfTIh83bAV8ynaWwuxIWQ8eQInc3Jf1jJZvmaNFOSTeRDKFu', 'Bursary Staff', 'bursary', NULL, '+1234567893', 'Bursary', 'General', '2025-12-14 20:12:13', '2025-12-14 21:19:50', 1),
(5, 'dept.staff', 'department@university.edu', '$2y$10$NUahO6tWUAxePsJx3CIwnuMc3NZtbR5UcUnv.yEd1r4x.migdrIlS', 'Department Officer', 'department', NULL, '+1234567894', 'Computer Science', 'General', '2025-12-14 20:12:13', NULL, 1),
(6, 'faculty.staff', 'faculty@university.edu', '$2y$10$aqK3yELuFY/tAHKzw2q9gevfbXE.bb.Ak58oxqDLqEKsE0llDW4Rq', 'Faculty Officer', 'faculty', NULL, '+1234567895', 'Faculty of Science', 'Faculty of Science', '2025-12-14 20:12:13', NULL, 1),
(7, 'student1', 'student1@university.edu', '$2y$10$tNzKS2Pql1x2.CTGnT4DX.dORYfEpoVcucGGjnEELLWG7Lu/4PqfK', 'John Doe', 'student', 'CS/2019/001', '+1234567896', 'Computer Science', 'Faculty of Science', '2025-12-14 20:12:13', NULL, 1),
(8, 'student2', 'student2@university.edu', '$2y$10$tNzKS2Pql1x2.CTGnT4DX.dORYfEpoVcucGGjnEELLWG7Lu/4PqfK', 'Jane Smith', 'student', 'CS/2019/002', '+1234567897', 'Computer Science', 'Faculty of Science', '2025-12-14 20:12:13', '2025-12-25 01:40:22', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_matric` (`matric_no`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `idx_matric` (`matric_no`),
  ADD KEY `idx_cert_number` (`certificate_number`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `clearance_history`
--
ALTER TABLE `clearance_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`);

--
-- Indexes for table `clearance_requests`
--
ALTER TABLE `clearance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_code` (`request_code`),
  ADD KEY `idx_matric_status` (`matric_no`,`status`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_unit` (`unit_id`);

--
-- Indexes for table `clearance_units`
--
ALTER TABLE `clearance_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_code` (`unit_code`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username_time` (`username`,`attempt_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `registry`
--
ALTER TABLE `registry`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `idx_matric` (`matric_no`),
  ADD KEY `idx_clearance_status` (`clearance_status`),
  ADD KEY `idx_certificate_number` (`certificate_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matric_no` (`matric_no`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_matric` (`matric_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_records`
--
ALTER TABLE `academic_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clearance_history`
--
ALTER TABLE `clearance_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `clearance_requests`
--
ALTER TABLE `clearance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `clearance_units`
--
ALTER TABLE `clearance_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `registry`
--
ALTER TABLE `registry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD CONSTRAINT `academic_records_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clearance_history`
--
ALTER TABLE `clearance_history`
  ADD CONSTRAINT `clearance_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `clearance_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clearance_requests`
--
ALTER TABLE `clearance_requests`
  ADD CONSTRAINT `clearance_requests_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `clearance_requests_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `clearance_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registry`
--
ALTER TABLE `registry`
  ADD CONSTRAINT `registry_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
