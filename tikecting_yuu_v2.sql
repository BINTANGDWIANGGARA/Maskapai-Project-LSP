-- Tikecting.yuu Expanded Database Schema
-- Updated: 2026-04-16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `payment_proofs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `passengers`;
DROP TABLE IF EXISTS `add_ons`;
DROP TABLE IF EXISTS `seats`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `flights`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `flights`
-- --------------------------------------------------------
CREATE TABLE `flights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flight_code` varchar(20) NOT NULL,
  `airline_name` varchar(100) NOT NULL,
  `airline_logo` varchar(255) DEFAULT NULL,
  `origin` varchar(100) NOT NULL,
  `origin_code` varchar(10) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `dest_code` varchar(10) NOT NULL,
  `depart_date` date NOT NULL,
  `depart_time` time NOT NULL,
  `arrive_time` time NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `transit` enum('direct','1_transit','2_transit') DEFAULT 'direct',
  `price` decimal(12,2) NOT NULL,
  `class` enum('economy','business','first') DEFAULT 'economy',
  `seats_available` int(11) DEFAULT 100,
  `baggage_capacity` varchar(50) DEFAULT '20kg',
  `refund_policy` text DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `delay_history` varchar(100) DEFAULT 'On Time',
  `rating` decimal(2,1) DEFAULT 4.5,
  `is_promo` tinyint(1) DEFAULT 0,
  `promo_badge` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','waiting_payment','pending_verification','paid','ticket_issued','cancelled','refund_requested','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `user_id` (`user_id`),
  KEY `flight_id` (`flight_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `passengers`
-- --------------------------------------------------------
CREATE TABLE `passengers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nik_passport` varchar(50) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `identity_file` varchar(255) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `seats`
-- --------------------------------------------------------
CREATE TABLE `seats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flight_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `type` enum('window','aisle','middle') DEFAULT 'middle',
  `is_premium` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `flight_id` (`flight_id`),
  CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('transfer','qris','e_wallet','va') NOT NULL,
  `status` enum('waiting','paid','failed','refunded') DEFAULT 'waiting',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `payment_proofs`
-- --------------------------------------------------------
CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `add_ons`
-- --------------------------------------------------------
CREATE TABLE `add_ons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `type` enum('baggage','meal','insurance','fast_track') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `add_ons_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `tickets` (E-Tickets)
-- --------------------------------------------------------
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `booking_id` (`booking_id`),
  KEY `passenger_id` (`passenger_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Sample Data for `users`
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `phone`, `role`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Administrator', 'admin@tikecting.yuu', '081234567890', 'admin'),
(2, 'cust', '4f21b18a4c743a5da01bb3a4955dea0a0294a0b4f7977b454c7259e37b2e6c19', 'Demo Customer', 'customer@tikecting.yuu', '081298765432', 'customer');

-- --------------------------------------------------------
-- Sample Data for `flights`
-- --------------------------------------------------------
INSERT INTO `flights` (`flight_code`, `airline_name`, `origin`, `origin_code`, `destination`, `dest_code`, `depart_date`, `depart_time`, `arrive_time`, `duration`, `transit`, `price`, `class`, `is_promo`, `promo_badge`) VALUES
('TY-101', 'Tikecting Air', 'Jakarta', 'CGK', 'Surabaya', 'SUB', '2026-05-01', '08:00:00', '09:35:00', '1j 35m', 'direct', 850000.00, 'economy', 1, 'Best Deal'),
('TY-202', 'Tikecting Air', 'Jakarta', 'CGK', 'Bali', 'DPS', '2026-05-02', '09:30:00', '12:00:00', '1j 30m', 'direct', 1200000.00, 'economy', 0, NULL),
('TY-303', 'Tikecting Air', 'Jakarta', 'CGK', 'Bandung', 'BDO', '2026-05-03', '10:00:00', '10:50:00', '50m', 'direct', 500000.00, 'economy', 0, NULL),
('TY-404', 'Tikecting Air', 'Jakarta', 'CGK', 'Nusantara', 'IKN', '2026-05-04', '12:00:00', '14:30:00', '2j 30m', 'direct', 1500000.00, 'business', 1, 'Business Promo');

COMMIT;
