-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 03:35 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projek_ruangan`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `field` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `activity` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `room`, `date`, `time_start`, `time_end`, `field`, `user_id`, `status`, `activity`) VALUES
(41, 'Offline 2', '2025-01-08', '00:00:00', '01:00:00', 'Tata Usaha', 12, 'Approved', NULL),
(42, 'Offline 1', '2025-01-08', '00:00:00', '01:00:00', 'Tata Usaha', 12, 'Approved', NULL),
(44, 'Offline 1', '2025-01-11', '08:45:00', '09:45:00', 'Kemitraan', 12, 'Approved', NULL),
(45, 'Online (Zoom) 1', '2025-01-11', '09:15:00', '10:15:00', 'Kemitraan', 12, 'Rejected', NULL),
(47, 'Online (Zoom) 1', '2025-01-11', '09:00:00', '12:00:00', 'Penyelarasan', 12, 'Approved', NULL),
(49, 'Offline 1', '2025-01-16', '10:20:00', '11:20:00', 'Penyelarasan', 12, 'Approved', 'Optimasi 2025'),
(50, 'Offline 2', '2025-01-16', '11:00:00', '12:00:00', 'Kemitraan', 12, 'Approved', 'Agenda Tahun 2025\r\n'),
(51, 'Online (Zoom) 1', '2025-01-16', '16:00:00', '17:00:00', 'Tata Usaha', 12, 'Approved', 'Rekap Keuangan Tata Usaha');

-- --------------------------------------------------------

--
-- Table structure for table `rejection_log`
--

CREATE TABLE `rejection_log` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `rejected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rejection_log`
--

INSERT INTO `rejection_log` (`id`, `booking_id`, `reason`, `rejected_at`) VALUES
(9, 45, 'ruangan renovasi', '2025-01-11 02:09:49');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis` enum('offline','online') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `nama`, `jenis`) VALUES
(1, 'Ruang Rapat 1', 'offline'),
(2, 'Ruang Rapat 2', 'offline');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nip`, `nama`, `password`, `role`, `is_admin`) VALUES
(11, '1111', 'Rafly', '1111', 'admin', 0),
(12, '2222', 'Joshua', '2222', 'user', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_ibfk_1` (`user_id`);

--
-- Indexes for table `rejection_log`
--
ALTER TABLE `rejection_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `rejection_log`
--
ALTER TABLE `rejection_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rejection_log`
--
ALTER TABLE `rejection_log`
  ADD CONSTRAINT `rejection_log_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
