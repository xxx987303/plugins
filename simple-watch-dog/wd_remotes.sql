-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 02, 2026 at 06:10 AM
-- Server version: 12.3.2-MariaDB
-- PHP Version: 8.5.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yb-watch-dog`
--

-- --------------------------------------------------------

--
-- Table structure for table `wd_remotes`
--

CREATE TABLE `wd_remotes` (
  `r_time` datetime NOT NULL DEFAULT current_timestamp(),
  `r_remote` varchar(32) DEFAULT NULL,
  `r_domain` varchar(32) DEFAULT NULL,
  `r_user_id` varchar(16) DEFAULT NULL,
  `r_country` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wd_remotes`
--

INSERT INTO `wd_remotes` (`r_time`, `r_remote`, `r_domain`, `r_user_id`, `r_country`) VALUES
('2025-04-20 05:48:14', '130.237.181.133', 'wg-prod-srv03.it.su.se', '1', 'Sweden'),
('2025-04-18 23:09:47', '130.237.181.141', 'wg-prod-srv04.it.su.se', '1', 'Sweden'),
('2026-07-19 06:16:26', '176.126.133.217', '', '7', 'Ukraine'),
('2025-05-13 08:35:51', '176.223.173.229', '', '1', 'Switzerland'),
('2026-05-27 15:45:27', '185.176.246.223', 'h246-223.internetbolaget.nu', '1', 'Sweden'),
('2024-07-16 15:45:32', '185.176.246.72', 'h246-72.internetbolaget.nu', '1', 'Sweden'),
('2024-11-26 17:05:24', '194.103.157.80', 'host-157-103-194-80.internetnord', '1', 'Sweden'),
('2024-07-19 08:45:51', '194.103.157.93', 'host-157-103-194-93.internetnord', '1', 'Sweden'),
('2024-07-17 10:26:39', '194.230.146.126', 'mob-194-230-146-126.cgn.sunrise.', '2', 'Switzerland'),
('2026-09-01 18:33:29', '91.78.36.225', 'ppp91-78-36-225.pppoe.mtu-net.ru', '9', 'Russia'),
('2026-09-01 18:34:10', '91.79.37.8', 'ppp91-79-37-8.pppoe.mtu-net.ru', '9', 'Russia'),
('2026-09-01 18:38:56', '212.233.85.247', '', '3', 'Russia'),
('2026-09-01 18:39:11', '93.158.130.173', '', '4', 'Russia'),
('2025-10-07 15:33:43', '46.252.1.30', 'host-46-252-1-30.internetbolaget', '1', 'Sweden'),
('2026-07-11 12:21:58', '194.230.146.141', 'mob-194-230-146-141.cgn.sunrise.', '2', 'Switzerland'),
('2026-07-09 12:48:37', '46.252.8.1', 'host-46-252-8-1.internetbolaget.', '1', 'Sweden'),
('2026-08-15 01:00:47', '31.41.90.36', 'cache.google.com', '7', 'Ukraine'),
('2026-07-16 22:41:37', '195.5.50.69', '', '7', 'Ukraine'),
('2026-07-19 09:01:44', '31.41.90.5', 'cache.google.com', '7', 'Ukraine');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
