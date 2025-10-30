-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 30, 2025 at 05:16 AM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Staff') DEFAULT 'Staff',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `full_name`, `email`, `password`, `role`, `status`, `date_created`) VALUES
(1, 'SafePaws Admin', 'admin@safepaws.com', '$2y$10$EzOe.nNOUsq5S4Boer8JvOSxG3Y18ARPVqitLBdIlp.6isdG0R1B2', 'Super Admin', 'Active', '2025-10-29 11:14:50');

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
  `request_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_requests`
--

INSERT INTO `adoption_requests` (`request_id`, `user_id`, `user_name`, `pet_id`, `pet_name`, `first_name`, `last_name`, `email`, `address`, `phone`, `birth_date`, `occupation`, `company`, `social_media`, `classification`, `adopted_before`, `reason`, `valid_id`, `status`, `request_date`) VALUES
(12, 3, NULL, 6, 'Bapi', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2004-01-28', 'Student', 'N/A', '', 'Dog', 'No', '', NULL, 'Pending', '2025-10-28 19:34:46'),
(13, 3, NULL, 7, 'Rigby', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2004-01-27', '', '', '', 'Cat', 'Yes', '', NULL, 'Pending', '2025-10-28 19:53:25'),
(14, 3, NULL, 7, 'Rigby', 'Fiona Gene', 'Basa', 'fionagene@gmail.com', 'Bactad East', '09122159301', '2008-01-23', '', '', '', 'Cat', 'Yes', '', NULL, 'Pending', '2025-10-28 19:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `care_tips`
--

CREATE TABLE `care_tips` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('Published','Unpublished') NOT NULL DEFAULT 'Unpublished',
  `date_published` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `classification` enum('Dog','Cat') NOT NULL,
  `age` varchar(50) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `health_status` varchar(100) DEFAULT NULL,
  `temperament` varchar(100) DEFAULT NULL,
  `adoption_status` enum('Available','Adopted','Pending') DEFAULT 'Available',
  `date_sheltered` date DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `name`, `classification`, `age`, `breed`, `gender`, `color`, `health_status`, `temperament`, `adoption_status`, `date_sheltered`, `image_url`) VALUES
(6, 'Bapi', 'Dog', '2 years old', 'Chihuahua', 'Male', 'Brown', 'Vaccinated', 'Playful, Friendly', 'Available', '2025-10-21', '../assets/images/dog1.jpg'),
(7, 'Rigby', 'Cat', '2 years old', 'Ascat', 'Male', 'Grey', 'Vaccinated', 'Playful, Freaky', 'Available', '2024-10-21', '../assets/images/cat1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `password` varchar(255) NOT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `status`, `password`, `date_registered`) VALUES
(3, 'Fiona Gene Basa', 'fionagene@gmail.com', 'Active', '$2y$10$FjNERprk7LNOKG59c40qTuLfWjQOrACok5TmY713tZ/BxO3g5gDmi', '2025-10-26 08:30:46'),
(4, 'Yona Jin', 'yonajin@gmail.com', 'Active', '$2y$10$bm2oUd89YrVwj/.SRZDh6.0.rcXkpxL85C3ZemXsA8O5.2yZYdgCy', '2025-10-28 03:01:23'),
(8, 'John Doe', 'johndoe@gmail.com', 'Active', '$2y$10$yqV7A.iJ4JMY02sdm82OTeNUsQUjz/ISDu4au3C6HA12e7.HZKtcW', '2025-10-28 03:55:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_pet_id` (`pet_id`);

--
-- Indexes for table `care_tips`
--
ALTER TABLE `care_tips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `care_tips`
--
ALTER TABLE `care_tips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
