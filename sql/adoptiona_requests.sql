-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 05, 2025 at 05:35 AM
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
-- Database: `safepaws_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoption_requests`
--

CREATE TABLE `adoption_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `classification` enum('Cat','Dog') NOT NULL,
  `adopted_before` enum('Yes','No') NOT NULL,
  `reason` text DEFAULT NULL,
  `valid_id` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Denied') DEFAULT 'Pending',
  `request_date` datetime DEFAULT current_timestamp(),
  `interview_date` datetime DEFAULT NULL,
  `interview_type` enum('Online','Onsite') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_requests`
--

INSERT INTO `adoption_requests` (`request_id`, `user_id`, `user_name`, `pet_id`, `pet_name`, `first_name`, `last_name`, `email`, `address`, `phone`, `birth_date`, `occupation`, `company`, `social_media`, `classification`, `adopted_before`, `reason`, `valid_id`, `status`, `request_date`, `interview_date`, `interview_type`) VALUES
(12, 3, NULL, 6, 'Bapi', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2004-01-28', 'Student', 'N/A', '', 'Dog', 'No', '', NULL, 'Denied', '2025-10-28 19:34:46', NULL, NULL),
(16, 3, NULL, 7, 'Rigby', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2009-06-06', 'Student', 'N/A', '', 'Cat', 'No', 'nigga', NULL, 'Approved', '2025-11-02 17:56:27', '2025-11-05 10:10:00', 'Onsite'),
(17, 3, NULL, 6, 'Bapi', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2003-01-28', '', '', '', 'Dog', 'No', '', NULL, 'Pending', '2025-11-05 12:18:22', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_pet_id` (`pet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD CONSTRAINT `fk_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pet_id` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
