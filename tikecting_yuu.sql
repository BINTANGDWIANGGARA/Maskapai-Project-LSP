-- phpMyAdmin SQL Dump
-- Tikecting.yuu Database Schema
-- 
-- Host: 127.0.0.1
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
-- Database: `tikecting_yuu`
--
CREATE DATABASE IF NOT EXISTS `tikecting_yuu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tikecting_yuu`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `gender` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Default users (password: admin123 / cust123 - SHA256 hashed)
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `phone`, `nik`, `gender`, `birthdate`, `address`, `role`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Administrator', 'admin@tikecting.yuu', '081234567890', NULL, NULL, NULL, NULL, 'admin'),
(2, 'cust', '4f21b18a4c743a5da01bb3a4955dea0a0294a0b4f7977b454c7259e37b2e6c19', 'Demo Customer', 'customer@tikecting.yuu', '081298765432', '3201010101010001', 'Laki-laki', '1990-05-15', 'Jl. Sudirman No. 10, Jakarta Pusat', 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flight_code` varchar(20) NOT NULL,
  `airline` varchar(100) DEFAULT 'Tikecting Air',
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `depart_date` date NOT NULL,
  `depart_time` time NOT NULL,
  `arrive_time` time DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `seats` int(11) DEFAULT 100,
  `class` enum('economy','business','first') DEFAULT 'economy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample ticket data
--

INSERT INTO `tickets` (`id`, `flight_code`, `airline`, `origin`, `destination`, `depart_date`, `depart_time`, `arrive_time`, `price`, `seats`, `class`) VALUES
(1, 'TY-101', 'Tikecting Air', 'Jakarta (CGK)', 'Surabaya (SUB)', '2026-05-01', '08:00:00', '09:35:00', 850000.00, 100, 'economy'),
(2, 'TY-202', 'Tikecting Air', 'Jakarta (CGK)', 'Bali (DPS)', '2026-05-02', '09:30:00', '12:00:00', 1200000.00, 150, 'economy'),
(3, 'TY-303', 'Tikecting Air', 'Jakarta (CGK)', 'Bandung (BDO)', '2026-05-03', '10:00:00', '10:50:00', 500000.00, 50, 'economy'),
(4, 'TY-404', 'Tikecting Air', 'Jakarta (CGK)', 'Nusantara (IKN)', '2026-05-04', '12:00:00', '14:30:00', 1500000.00, 100, 'business'),
(5, 'TY-505', 'Tikecting Air', 'Surabaya (SUB)', 'Makassar (UPG)', '2026-05-05', '07:00:00', '09:15:00', 950000.00, 80, 'economy'),
(6, 'TY-606', 'Tikecting Air', 'Bali (DPS)', 'Lombok (LOP)', '2026-05-06', '14:00:00', '14:40:00', 450000.00, 60, 'economy'),
(7, 'TY-707', 'Tikecting Air', 'Jakarta (CGK)', 'Medan (KNO)', '2026-05-07', '06:30:00', '09:00:00', 1300000.00, 120, 'economy'),
(8, 'TY-808', 'Tikecting Air', 'Jakarta (CGK)', 'Yogyakarta (YIA)', '2026-05-08', '11:00:00', '12:15:00', 700000.00, 90, 'economy'),
(9, 'TY-909', 'Tikecting Air', 'Jakarta (CGK)', 'Semarang (SRG)', '2026-05-09', '13:00:00', '14:10:00', 650000.00, 80, 'economy'),
(10, 'TY-010', 'Tikecting Air', 'Jakarta (CGK)', 'Padang (PDG)', '2026-05-10', '15:00:00', '17:00:00', 1100000.00, 100, 'economy'),
(11, 'TY-111', 'Tikecting Air', 'Surabaya (SUB)', 'Balikpapan (BPN)', '2026-05-11', '08:30:00', '11:00:00', 1250000.00, 90, 'business'),
(12, 'TY-222', 'Tikecting Air', 'Bali (DPS)', 'Jakarta (CGK)', '2026-05-12', '17:00:00', '19:30:00', 1150000.00, 150, 'economy'),
(13, 'TY-333', 'Tikecting Air', 'Makassar (UPG)', 'Jayapura (DJJ)', '2026-05-13', '06:00:00', '10:30:00', 2200000.00, 70, 'business'),
(14, 'TY-444', 'Tikecting Air', 'Jakarta (CGK)', 'Batam (BTH)', '2026-05-14', '09:00:00', '10:40:00', 800000.00, 100, 'economy'),
(15, 'TY-555', 'Tikecting Air', 'Jakarta (CGK)', 'Palembang (PLM)', '2026-05-15', '07:30:00', '08:45:00', 600000.00, 85, 'economy'),
(16, 'TY-666', 'Tikecting Air', 'Yogyakarta (YIA)', 'Denpasar (DPS)', '2026-05-16', '12:00:00', '13:20:00', 750000.00, 70, 'economy'),
(17, 'TY-777', 'Tikecting Air', 'Medan (KNO)', 'Jakarta (CGK)', '2026-05-17', '18:00:00', '20:30:00', 1350000.00, 110, 'economy'),
(18, 'TY-888', 'Tikecting Air', 'Balikpapan (BPN)', 'Surabaya (SUB)', '2026-05-18', '10:00:00', '12:00:00', 1000000.00, 90, 'economy'),
(19, 'TY-999', 'Tikecting Air', 'Jakarta (CGK)', 'Pontianak (PNK)', '2026-05-19', '14:30:00', '16:45:00', 1050000.00, 95, 'economy'),
(20, 'TY-020', 'Tikecting Air', 'Lombok (LOP)', 'Jakarta (CGK)', '2026-05-20', '16:00:00', '18:30:00', 1200000.00, 80, 'business'),
(21, 'TY-121', 'Tikecting Air', 'Semarang (SRG)', 'Jakarta (CGK)', '2026-05-21', '06:45:00', '08:00:00', 650000.00, 75, 'economy'),
(22, 'TY-232', 'Tikecting Air', 'Palembang (PLM)', 'Batam (BTH)', '2026-05-22', '11:15:00', '12:45:00', 700000.00, 70, 'economy'),
(23, 'TY-343', 'Tikecting Air', 'Makassar (UPG)', 'Surabaya (SUB)', '2026-05-23', '13:30:00', '15:00:00', 900000.00, 85, 'economy'),
(24, 'TY-454', 'Tikecting Air', 'Jayapura (DJJ)', 'Makassar (UPG)', '2026-05-24', '07:00:00', '11:30:00', 2100000.00, 60, 'business'),
(25, 'TY-565', 'Tikecting Air', 'Batam (BTH)', 'Jakarta (CGK)', '2026-05-25', '19:00:00', '20:40:00', 850000.00, 100, 'economy');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `total` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'transfer',
  `status` enum('waiting','verified','rejected') DEFAULT 'waiting',
  `admin_note` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
