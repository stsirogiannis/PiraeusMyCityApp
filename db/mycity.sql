-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 22, 2026 at 03:44 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mycity`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `full_name`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$Y1mqNEOYcnOulFivfuQbpOknfIVHZojRPBOigfvK7pCZ/LoY77IcC', 'Διαχειριστής Δήμου Πειραιά', 'admin@mycity-piraeus.gr', '2026-08-11 20:44:28');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `weight` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `weight`, `description`) VALUES
(1, 'Οδικές Υποδομές', 5, 'Λακκούβες, πεζοδρόμια, κράσπεδα, οδόστρωμα'),
(2, 'Δίκτυα Ύδρευσης & Αποχέτευσης', 5, 'Διαρροές, φρεάτια, αγωγοί, υπερχειλίσεις'),
(3, 'Φωτισμός & Ηλεκτρικές Εγκαταστάσεις', 4, 'Δημοτικός φωτισμός, κολώνες, καλωδιώσεις'),
(4, 'Δημόσιοι Χώροι & Περιβάλλον', 3, 'Απορρίμματα, πάρκα, παιδικές χαρές, κοινόχρηστοι χώροι'),
(5, 'Κυκλοφορία & Σήμανση', 3, 'Πινακίδες, διαγραμμίσεις, κολωνάκια, φανάρια'),
(6, 'Δημοτικός Εξοπλισμός', 2, 'Παγκάκια, κάδοι, στάσεις λεωφορείων, βρύσες'),
(7, 'Λοιπά Ζητήματα', 1, 'Λοιπά προβλήματα');

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
CREATE TABLE `issues` (
  `issue_id` int(11) NOT NULL,
  `ticket_id` varchar(10) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `status` enum('Υποβλήθηκε','Σε Εξέλιξη','Επιλύθηκε') DEFAULT 'Υποβλήθηκε',
  `priority` enum('Χαμηλή','Μεσαία','Υψηλή','Κρίσιμη') DEFAULT 'Μεσαία',
  `user` varchar(50) NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`issue_id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `issues`
--
ALTER TABLE `issues`
  ADD CONSTRAINT `issues_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
