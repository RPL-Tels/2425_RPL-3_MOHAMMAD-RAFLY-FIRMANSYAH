-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 04:34 PM
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
  `status` enum('Pending','Approved','Rejected','Expired') DEFAULT 'Pending',
  `activity` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `room`, `date`, `time_start`, `time_end`, `field`, `user_id`, `status`, `activity`) VALUES
(91, 'Ruang Rapat Mitras DUDI', '2025-04-24', '21:00:00', '22:00:00', 'Penyelarasan', 24, 'Approved', 'Kegiatan Vocationomics'),
(92, 'Director Meeting Room', '2025-04-24', '21:00:00', '22:00:00', 'Kemitraan', 22, 'Rejected', 'Optimasi Kemitraan'),
(93, 'Ruang Rapat Mitras DUDI', '2025-04-24', '22:00:00', '23:00:00', 'Kemitraan', 22, 'Approved', 'Optimasi Kemitraan'),
(94, 'Zoom Meeting A', '2025-04-25', '08:00:00', '11:00:00', 'Kemitraan', 22, 'Approved', 'Sosialisasi SMK'),
(95, 'Zoom Meeting B', '2025-04-25', '10:00:00', '14:00:00', 'Penyelarasan', 24, 'Approved', 'Kegiatan Merdeka Belajar');

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
(19, 92, 'Maaf ruangan sedang tidak bisa digunakan', '2025-04-24 13:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis` enum('Online','Offline') NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `fasilitas` text NOT NULL,
  `gambar` varchar(255) DEFAULT 'default-room.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id`, `nama`, `jenis`, `kapasitas`, `fasilitas`, `gambar`, `created_at`, `updated_at`) VALUES
(15, 'Ruang Rapat Mitras DUDI', 'Offline', 20, 'Proyektor, AC, Wifi, Meja Konferensi, Soundsystem, Papan Tulis', '680a45ebe35b1.jpeg', '2025-04-14 03:38:34', '2025-04-24 14:08:43'),
(16, 'Director Meeting Room', 'Offline', 10, 'TV, AC, Wifi, Meja Konferensi, Soundsystem', '67fc83c645873.jpeg', '2025-04-14 03:40:54', '2025-04-14 03:40:54'),
(18, 'Zoom Meeting A', 'Online', 100, 'Screen Sharing, Record, Moderator', '67fc854c45e50.png', '2025-04-14 03:47:24', '2025-04-14 03:47:24'),
(19, 'Zoom Meeting B', 'Online', 100, 'Screen Sharing, Record, Moderator', '67fc857d2a2f2.png', '2025-04-14 03:48:13', '2025-04-14 03:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','User') NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nip`, `nama`, `jabatan`, `password`, `role`, `is_admin`) VALUES
(21, '222310165', 'Mohammad Rafly Firmansyah', 'Operator Room', '03012007', 'Admin', 1),
(22, '222310166', 'Keyko Yafiq Hamizan', 'Staff Kemitraan', '03012007', 'User', 0),
(24, '222310167', 'Pandu Rafa Hanggoro', 'Staff Penyelarasan', '03012007', 'User', 0);

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
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `rejection_log`
--
ALTER TABLE `rejection_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
