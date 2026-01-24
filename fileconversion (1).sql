-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2026 at 04:11 AM
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
-- Database: `fileconversion`
--

-- --------------------------------------------------------

--
-- Table structure for table `benefeciaries`
--

CREATE TABLE `benefeciaries` (
  `account_number` int(10) NOT NULL,
  `spouse_fname` varchar(255) DEFAULT NULL,
  `spouse_lname` varchar(255) DEFAULT NULL,
  `child_fname` varchar(255) DEFAULT NULL,
  `child_mname` varchar(255) DEFAULT NULL,
  `child_lname` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `other_fname` varchar(255) DEFAULT NULL,
  `other_mname` varchar(255) DEFAULT NULL,
  `other_lname` varchar(255) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certification`
--

CREATE TABLE `certification` (
  `account_number` int(10) NOT NULL,
  `printedName_path` varchar(255) DEFAULT NULL,
  `singature_path` int(11) DEFAULT NULL,
  `date_path` int(11) DEFAULT NULL,
  `thumb_path` varchar(255) DEFAULT NULL,
  `index_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overseas`
--

CREATE TABLE `overseas` (
  `account_number` int(10) NOT NULL,
  `profession_business` varchar(255) DEFAULT NULL,
  `business_started` varchar(255) DEFAULT NULL,
  `foreign_address` varchar(255) DEFAULT NULL,
  `flexi_fund` varchar(255) DEFAULT NULL,
  `monthly_earning` int(11) DEFAULT NULL,
  `Nws_ss_number` int(12) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_data`
--

CREATE TABLE `personal_data` (
  `id` int(11) NOT NULL,
  `account_number` int(10) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `sex` varchar(255) NOT NULL,
  `civil_status` varchar(255) NOT NULL,
  `nationality` varchar(255) NOT NULL,
  `pob` varchar(255) NOT NULL,
  `home_address` varchar(255) NOT NULL,
  `mobile_number` int(11) NOT NULL,
  `email_add` varchar(255) NOT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `father_fname` varchar(255) DEFAULT NULL,
  `father_mname` varchar(255) DEFAULT NULL,
  `father_lname` varchar(255) DEFAULT NULL,
  `father_suffix` varchar(255) DEFAULT NULL,
  `mother_fname` varchar(255) DEFAULT NULL,
  `mother_mname` varchar(255) DEFAULT NULL,
  `mother_lname` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `benefeciaries`
--
ALTER TABLE `benefeciaries`
  ADD KEY `account_number` (`account_number`);

--
-- Indexes for table `certification`
--
ALTER TABLE `certification`
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- Indexes for table `overseas`
--
ALTER TABLE `overseas`
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- Indexes for table `personal_data`
--
ALTER TABLE `personal_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_account_number` (`account_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `personal_data`
--
ALTER TABLE `personal_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `benefeciaries`
--
ALTER TABLE `benefeciaries`
  ADD CONSTRAINT `benefeciaries_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `personal_data` (`account_number`);

--
-- Constraints for table `certification`
--
ALTER TABLE `certification`
  ADD CONSTRAINT `certification_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `personal_data` (`account_number`);

--
-- Constraints for table `overseas`
--
ALTER TABLE `overseas`
  ADD CONSTRAINT `overseas_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `personal_data` (`account_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
