-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 06:35 PM
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
-- Database: `ebhauz`
--

-- --------------------------------------------------------

--
-- Table structure for table `bh_photos`
--

CREATE TABLE `bh_photos` (
  `photo_id` int(11) NOT NULL,
  `permit_no` varchar(20) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bh_status`
--

CREATE TABLE `bh_status` (
  `pol_id` int(10) NOT NULL,
  `bh_id` varchar(20) NOT NULL,
  `pol_stat` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bh_status`
--

INSERT INTO `bh_status` (`pol_id`, `bh_id`, `pol_stat`) VALUES
(5, '12345', 'yes'),
(6, '12345', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `bh_table`
--

CREATE TABLE `bh_table` (
  `permit_no` varchar(20) NOT NULL,
  `owner_id` int(10) NOT NULL,
  `bh_name` varchar(60) NOT NULL,
  `bh_address` varchar(100) NOT NULL,
  `accred_status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bh_table`
--

INSERT INTO `bh_table` (`permit_no`, `owner_id`, `bh_name`, `bh_address`, `accred_status`) VALUES
('12345', 20, 'Hello World', 'Poblacion', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `notification_log`
--

CREATE TABLE `notification_log` (
  `stud_id` varchar(50) NOT NULL,
  `last_notified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_log`
--

INSERT INTO `notification_log` (`stud_id`, `last_notified`) VALUES
('12345', '2025-11-26 16:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `owner_table`
--

CREATE TABLE `owner_table` (
  `user_id` int(10) NOT NULL,
  `owner_id` int(10) NOT NULL,
  `cont_no` varchar(15) NOT NULL,
  `owner_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_table`
--

INSERT INTO `owner_table` (`user_id`, `owner_id`, `cont_no`, `owner_address`) VALUES
(22, 20, '09061101433', 'Patulangon');

-- --------------------------------------------------------

--
-- Table structure for table `owner_ver`
--

CREATE TABLE `owner_ver` (
  `owner_id` int(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `mid_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `verif_stat` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_ver`
--

INSERT INTO `owner_ver` (`owner_id`, `first_name`, `mid_name`, `last_name`, `verif_stat`) VALUES
(20, 'Jhun Michael', 'Gallo', 'Ababa', 'pendi');

-- --------------------------------------------------------

--
-- Table structure for table `pol_table`
--

CREATE TABLE `pol_table` (
  `pol_id` int(10) NOT NULL,
  `pol_desc` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pol_table`
--

INSERT INTO `pol_table` (`pol_id`, `pol_desc`) VALUES
(5, 'hello world'),
(6, 'bye world');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_table`
--

CREATE TABLE `tenant_table` (
  `stud_id` int(10) NOT NULL,
  `bh_id` varchar(20) NOT NULL,
  `stud_first_name` varchar(60) NOT NULL,
  `stud_mid_name` varchar(60) NOT NULL,
  `stud_last_name` varchar(60) NOT NULL,
  `guar_name` varchar(100) NOT NULL,
  `guar_cont_no` varchar(15) NOT NULL,
  `rent_stat` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_table`
--

INSERT INTO `tenant_table` (`stud_id`, `bh_id`, `stud_first_name`, `stud_mid_name`, `stud_last_name`, `guar_name`, `guar_cont_no`, `rent_stat`) VALUES
(12345, '12345', 'Shell Dy', 'Gallo', 'Sumangue', 'Vienna Michelle Sumangue', '09676788333', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `user_cred`
--

CREATE TABLE `user_cred` (
  `user_id` int(10) NOT NULL,
  `user_role` varchar(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `enc_pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cred`
--

INSERT INTO `user_cred` (`user_id`, `user_role`, `username`, `enc_pass`) VALUES
(21, 'admin', 'admin', '$2y$10$udyLqlA0MuW1X84w0GNPaufcEYr0j0QgCnqdbK5XBdRHB6Q92kkCi'),
(22, 'owner', 'Fish', '$2y$10$OtgEie6iU6dVXo1lPNC6YOcqIgaOPBYif0jxtG6o8bwFqJHplisv6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bh_photos`
--
ALTER TABLE `bh_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `permit_no` (`permit_no`);

--
-- Indexes for table `bh_status`
--
ALTER TABLE `bh_status`
  ADD PRIMARY KEY (`pol_id`,`bh_id`),
  ADD KEY `fk_stat_bh` (`bh_id`);

--
-- Indexes for table `bh_table`
--
ALTER TABLE `bh_table`
  ADD PRIMARY KEY (`permit_no`),
  ADD KEY `fk_bh_owner` (`owner_id`);

--
-- Indexes for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD PRIMARY KEY (`stud_id`);

--
-- Indexes for table `owner_table`
--
ALTER TABLE `owner_table`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_owner_ver` (`owner_id`);

--
-- Indexes for table `owner_ver`
--
ALTER TABLE `owner_ver`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `pol_table`
--
ALTER TABLE `pol_table`
  ADD PRIMARY KEY (`pol_id`);

--
-- Indexes for table `tenant_table`
--
ALTER TABLE `tenant_table`
  ADD PRIMARY KEY (`stud_id`) USING BTREE,
  ADD KEY `fk_tenant_bh` (`bh_id`);

--
-- Indexes for table `user_cred`
--
ALTER TABLE `user_cred`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bh_photos`
--
ALTER TABLE `bh_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owner_ver`
--
ALTER TABLE `owner_ver`
  MODIFY `owner_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pol_table`
--
ALTER TABLE `pol_table`
  MODIFY `pol_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_cred`
--
ALTER TABLE `user_cred`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bh_photos`
--
ALTER TABLE `bh_photos`
  ADD CONSTRAINT `bh_photos_ibfk_1` FOREIGN KEY (`permit_no`) REFERENCES `bh_table` (`permit_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bh_status`
--
ALTER TABLE `bh_status`
  ADD CONSTRAINT `fk_stat_bh` FOREIGN KEY (`bh_id`) REFERENCES `bh_table` (`permit_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stat_pol` FOREIGN KEY (`pol_id`) REFERENCES `pol_table` (`pol_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bh_table`
--
ALTER TABLE `bh_table`
  ADD CONSTRAINT `fk_bh_owner` FOREIGN KEY (`owner_id`) REFERENCES `owner_ver` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `owner_table`
--
ALTER TABLE `owner_table`
  ADD CONSTRAINT `fk_owner_ver` FOREIGN KEY (`owner_id`) REFERENCES `owner_ver` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_owner` FOREIGN KEY (`user_id`) REFERENCES `user_cred` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenant_table`
--
ALTER TABLE `tenant_table`
  ADD CONSTRAINT `fk_tenant_bh` FOREIGN KEY (`bh_id`) REFERENCES `bh_table` (`permit_no`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
