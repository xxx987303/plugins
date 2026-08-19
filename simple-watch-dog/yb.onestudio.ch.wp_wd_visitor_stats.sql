/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for osx10.21 (arm64)
--
-- Host: localhost    Database: yb_restor
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `wd_visitor_stats`
--

DROP TABLE IF EXISTS `wd_visitor_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wd_visitor_stats` (
  `id` mediumint(9) DEFAULT 0,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `remote` varchar(32) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `uri` varchar(255) DEFAULT NULL,
  `mode` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wd_visitor_stats`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wd_visitor_stats` WRITE;
/*!40000 ALTER TABLE `wd_visitor_stats` DISABLE KEYS */;
INSERT INTO `wd_visitor_stats` VALUES
(113,'2024-07-17 10:09:34','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',6,'/restor/','prod'),
(114,'2024-07-17 10:09:40','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',18,'/restor/','prod'),
(115,'2024-07-17 10:09:58','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',192,'/restor/stat/','prod'),
(116,'2024-07-17 10:13:10','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',9,'/restor/','prod'),
(117,'2024-07-17 10:13:19','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',34,'/restor/soriano/','prod'),
(118,'2024-07-17 10:13:53','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',4,'/restor/stat/','prod'),
(119,'2024-07-17 10:13:57','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',630,'/restor/stat/','prod'),
(120,'2024-07-17 10:24:27','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',8,'/restor/','prod'),
(121,'2024-07-17 10:24:35','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',247,'/restor/stat/','prod'),
(122,'2024-07-17 10:26:39','185.214.97.147',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',9,'/restor/','prod'),
(123,'2024-07-17 10:26:48','185.214.97.147',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',9,'/restor/fridge/','prod'),
(124,'2024-07-17 10:26:57','185.214.97.147',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',14,'/restor/soriano/','prod'),
(125,'2024-07-17 10:27:11','185.214.97.147',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',0,'/restor/tougin/','prod'),
(126,'2024-07-17 10:28:42','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/stat/','prod'),
(127,'2024-07-17 10:50:43','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',7,'/restor/','prod'),
(128,'2024-07-17 10:50:50','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',97,'/restor/fridge/','prod'),
(129,'2024-07-17 10:51:05','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/stat/','prod'),
(130,'2024-07-17 10:52:27','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',24,'/restor/fridge/','prod'),
(131,'2024-07-17 10:52:51','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',583,'/restor/bat/','prod'),
(132,'2024-07-17 11:02:34','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',33,'/restor/stat/','prod'),
(133,'2024-07-17 11:03:07','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',7,'/restor/','prod'),
(134,'2024-07-17 11:03:14','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',0,'/restor/stat/','prod'),
(135,'2024-07-17 12:16:34','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',15,'/restor/','prod'),
(136,'2024-07-17 12:16:49','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',0,'/restor/stat/','prod'),
(137,'2024-07-17 14:04:11','185.225.28.204',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',0,'/restor/stat/','prod'),
(138,'2024-07-17 15:54:41','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',9,'/restor/','prod'),
(139,'2024-07-17 15:54:50','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',76,'/restor/stat/','prod'),
(140,'2024-07-17 15:56:06','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',0,'/restor/stat/','prod'),
(141,'2024-07-17 16:22:29','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',0,'/restor/stat/','prod'),
(142,'2024-07-18 10:40:33','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',0,'/restor/stat/','prod'),
(143,'2024-07-19 08:45:53','194.103.157.93',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',0,'/restor/stat/','prod'),
(144,'2024-07-21 19:04:34','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',9,'/restor/','prod'),
(145,'2024-07-21 19:04:43','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',314,'/restor/clock1896/','prod'),
(146,'2024-07-21 19:09:57','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',13,'/restor/clock1896/','prod'),
(147,'2024-07-21 19:10:10','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',13,'/restor/','prod'),
(148,'2024-07-21 19:10:23','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',346,'/restor/soriano/','prod'),
(149,'2024-07-21 19:16:09','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',12,'/restor/','prod'),
(150,'2024-07-21 19:16:21','185.176.246.72',1,'yb','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',0,'/restor/bat/','prod'),
(151,'2024-07-23 09:32:26','194.103.157.93',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0',0,'/restor/','prod'),
(152,'2024-07-24 15:25:01','194.230.146.126',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',0,'/restor/','prod'),
(153,'2024-07-25 15:22:20','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',13,'/restor/','prod'),
(154,'2024-07-25 15:22:33','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',11,'/restor/bat/','prod'),
(155,'2024-07-25 15:22:44','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',10,'/restor/','prod'),
(156,'2024-07-25 15:22:54','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/device/','prod'),
(163,'2024-07-26 16:00:15','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',14,'/restor/','prod'),
(164,'2024-07-26 16:00:29','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/soriano/','prod'),
(165,'2024-07-26 22:07:43','185.176.246.72',1,'yb','Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.1',21,'/restor/','prod'),
(166,'2024-07-26 22:08:04','185.176.246.72',1,'yb','Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.1',0,'/restor/','prod'),
(167,'2024-10-16 11:37:55','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:131.0) Gecko/20100101 Firefox/131.0',4,'/restor/','prod'),
(168,'2024-10-16 11:37:59','185.176.246.72',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:131.0) Gecko/20100101 Firefox/131.0',0,'/restor/clock1896/','prod'),
(169,'2024-10-18 14:47:57','185.176.246.72',1,'yb','Mozilla/5.0 (iPhone; CPU iPhone OS 17_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/130.1  Mobile/15E148 Safari/605.1.15',15,'/restor/','prod'),
(170,'2024-10-18 14:48:12','185.176.246.72',1,'yb','Mozilla/5.0 (iPhone; CPU iPhone OS 17_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/130.1  Mobile/15E148 Safari/605.1.15',0,'/restor/sad2/','prod'),
(171,'2024-11-26 18:05:20','194.103.157.80',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:132.0) Gecko/20100101 Firefox/132.0',5,'/restor/','prod'),
(172,'2024-11-26 18:05:25','194.103.157.80',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:132.0) Gecko/20100101 Firefox/132.0',0,'/restor/table1946/','prod'),
(173,'2025-04-18 23:09:47','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',12,'/restor/','prod'),
(174,'2025-04-18 23:09:59','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',220,'/restor/clock1896/','prod'),
(175,'2025-04-18 23:13:39','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',4,'/restor/','prod'),
(176,'2025-04-18 23:13:43','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',128,'/restor/fridge/','prod'),
(177,'2025-04-18 23:15:51','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',3,'/restor/','prod'),
(178,'2025-04-18 23:15:54','130.237.181.141',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',0,'/restor/soriano/','prod'),
(179,'2025-04-20 05:48:14','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',4,'/restor/','prod'),
(180,'2025-04-20 05:48:18','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',14,'/restor/','prod'),
(181,'2025-04-20 05:48:32','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',0,'/restor/chair/','prod'),
(182,'2025-04-20 05:48:32','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',37,'/restor/chair/','prod'),
(183,'2025-04-20 05:49:09','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',24,'/restor/fridge/','prod'),
(184,'2025-04-20 05:49:33','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',12,'/restor/','prod'),
(185,'2025-04-20 05:49:45','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',42,'/restor/sad2/','prod'),
(186,'2025-04-20 05:50:27','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',16,'/restor/sad2/','prod'),
(187,'2025-04-20 05:50:43','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',4,'/restor/sad2/','prod'),
(188,'2025-04-20 05:50:47','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',0,'/restor/','prod'),
(189,'2025-04-20 05:50:47','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',274,'/restor/','prod'),
(190,'2025-04-20 05:55:21','130.237.181.133',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',0,'/restor/','prod'),
(191,'2025-04-20 05:56:06','130.237.181.133',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',15,'/restor/','prod'),
(192,'2025-04-20 05:56:21','130.237.181.133',2,'Миша','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.11 Safari/605.1.15',0,'/restor/fridge/','prod'),
(193,'2025-05-13 08:35:51','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',34,'/restor/','prod'),
(194,'2025-05-13 08:36:25','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',8,'/restor/','prod'),
(195,'2025-05-13 08:36:33','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/soriano/','prod'),
(196,'2025-05-13 08:56:32','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',43,'/restor/','prod'),
(197,'2025-05-13 08:57:15','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',5,'/restor/table1946/','prod'),
(198,'2025-05-13 08:57:20','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',306,'/restor/','prod'),
(199,'2025-05-13 09:02:26','176.223.173.229',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',0,'/restor/','prod'),
(200,'2025-05-30 11:41:21','194.103.157.80',4,'Антон','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',47,'/restor/stat/','prod'),
(201,'2025-05-30 11:42:08','194.103.157.80',4,'Антон','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',53,'/restor/clock1896/','prod'),
(202,'2025-05-30 11:43:01','194.103.157.80',4,'Антон','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',73,'/restor/table1946/','prod'),
(203,'2025-05-30 11:44:14','194.103.157.80',4,'Антон','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',15,'/restor/stat/','prod'),
(204,'2025-05-30 11:44:29','194.103.157.80',4,'Антон','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',26,'/restor/table1946/','prod'),
(205,'2025-05-30 11:44:55','194.103.157.80',4,'Антон','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',31,'/restor/fridge/','prod'),
(206,'2025-05-30 11:45:26','194.103.157.80',4,'Антон','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',9,'/restor/table1946/','prod'),
(207,'2025-05-30 11:45:35','194.103.157.80',4,'Антон','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',0,'/restor/chair/','prod'),
(208,'2025-10-05 16:22:04','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',658,'/restor/stat/','prod'),
(209,'2025-10-05 16:33:02','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',3,'/restor/','prod'),
(210,'2025-10-05 16:33:05','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',1,'/restor/fridge/','prod'),
(211,'2025-10-05 16:33:06','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',21,'/restor/fridge/','prod'),
(212,'2025-10-05 16:33:27','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',9,'/restor/clock1896/','prod'),
(213,'2025-10-05 16:33:36','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',17,'/restor/table1946/','prod'),
(214,'2025-10-05 16:33:53','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',30,'/restor/soriano/','prod'),
(215,'2025-10-05 16:34:23','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',17,'/restor/tougin/','prod'),
(216,'2025-10-05 16:34:40','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',252,'/restor/sad2/','prod'),
(217,'2025-10-05 16:38:52','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',7,'/restor/','prod'),
(218,'2025-10-05 16:38:59','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',8,'/restor/bath/','prod'),
(219,'2025-10-05 16:39:07','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',16,'/restor/soriano/','prod'),
(220,'2025-10-05 16:39:23','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',5,'/restor/sad2/','prod'),
(221,'2025-10-05 16:39:28','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',16,'/restor/fridge/','prod'),
(222,'2025-10-05 16:39:44','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',3,'/restor/fridge/','prod'),
(223,'2025-10-05 16:39:47','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',16,'/restor/bat/','prod'),
(224,'2025-10-05 16:40:03','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',77,'/restor/tougin/','prod'),
(225,'2025-10-05 16:41:20','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',0,'/restor/','prod'),
(226,'2025-10-05 17:01:46','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',46,'/restor/table1946/','prod'),
(227,'2025-10-05 17:02:32','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',48,'/restor/stat/','prod'),
(228,'2025-10-05 17:03:20','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',36,'/restor/stat/','prod'),
(229,'2025-10-05 17:03:56','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',11,'/restor/stat/','prod'),
(230,'2025-10-05 17:04:07','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',13,'/restor/','prod'),
(231,'2025-10-05 17:04:20','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',5,'/restor/','prod'),
(232,'2025-10-05 17:04:25','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',6,'/restor/clock1896/','prod'),
(233,'2025-10-05 17:04:31','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',15,'/restor/','prod'),
(234,'2025-10-05 17:04:46','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',10,'/restor/','prod'),
(235,'2025-10-05 17:04:56','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',21,'/restor/tougin/','prod'),
(236,'2025-10-05 17:05:17','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',27,'/restor/table1946/','prod'),
(237,'2025-10-05 17:05:44','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',4,'/restor/tougin/','prod'),
(238,'2025-10-05 17:05:48','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',5,'/restor/soriano/','prod'),
(239,'2025-10-05 17:05:53','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',11,'/restor/chair/','prod'),
(240,'2025-10-05 17:06:04','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',14,'/restor/fridge/','prod'),
(241,'2025-10-05 17:06:18','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',33,'/restor/sad2/','prod'),
(242,'2025-10-05 17:06:51','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',7,'/restor/','prod'),
(243,'2025-10-05 17:06:58','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',3,'/restor/stul/','prod'),
(244,'2025-10-05 17:07:01','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',44,'/restor/soriano/','prod'),
(245,'2025-10-05 17:07:45','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',13,'/restor/fridge/','prod'),
(246,'2025-10-05 17:07:58','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',248,'/restor/table1946/','prod'),
(247,'2025-10-05 17:12:06','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',38,'/restor/soriano/','prod'),
(248,'2025-10-05 17:12:44','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',10,'/restor/sad2/','prod'),
(249,'2025-10-05 17:12:54','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',5,'/restor/','prod'),
(250,'2025-10-05 17:12:59','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',6,'/restor/sad2/','prod'),
(251,'2025-10-05 17:13:05','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',7,'/restor/fridge/','prod'),
(252,'2025-10-05 17:13:12','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',60,'/restor/sad2/','prod'),
(253,'2025-10-05 17:14:12','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',68,'/restor/fridge/','prod'),
(254,'2025-10-05 17:15:20','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',166,'/restor/fridge/','prod'),
(255,'2025-10-05 17:18:06','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',42,'/restor/sad2/','prod'),
(256,'2025-10-05 17:18:48','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',514,'/restor/sad2/pp2024/','prod'),
(257,'2025-10-05 17:27:22','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',15,'/restor/bat/','prod'),
(258,'2025-10-05 17:27:37','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',15,'/restor/sad2/','prod'),
(259,'2025-10-05 17:27:52','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',63,'/restor/bat/','prod'),
(260,'2025-10-05 17:28:55','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',100,'/restor/soriano/','prod'),
(261,'2025-10-05 17:30:35','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',137,'/restor/tougin/','prod'),
(262,'2025-10-05 17:32:52','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',8,'/restor/','prod'),
(263,'2025-10-05 17:33:00','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',381,'/restor/sad2/','prod'),
(264,'2025-10-05 17:39:21','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',35,'/restor/tougin/?unapproved=3&moderation-hash=fcc2f88ff0b8c571b69d43dbbf7f3489','prod'),
(265,'2025-10-05 17:39:56','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',51,'/restor/tougin/?unapproved=4&moderation-hash=de8d5c23e46d9a6440bde232f17788fa','prod'),
(266,'2025-10-05 17:40:47','212.233.85.247',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',0,'/restor/chair/','prod'),
(267,'2025-10-07 15:33:43','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',4,'/restor/','prod'),
(268,'2025-10-07 15:33:47','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',40,'/restor/tougin/','prod'),
(269,'2025-10-07 15:34:27','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',25,'/restor/tougin/','prod'),
(270,'2025-10-07 15:34:52','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',57,'/restor/clock1896/','prod'),
(271,'2025-10-07 15:35:49','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',18,'/restor/clock1896/?trashed=1&ids=2','prod'),
(272,'2025-10-07 15:36:07','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',2,'/restor/','prod'),
(273,'2025-10-07 15:36:09','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',11,'/restor/table1946/','prod'),
(274,'2025-10-07 15:36:20','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',0,'/restor/sad2/','prod'),
(275,'2025-10-08 09:34:06','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',219,'/restor/','prod'),
(276,'2025-10-08 09:37:45','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',0,'/restor/tougin/','prod'),
(277,'2025-10-08 11:51:17','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',0,'/restor/tougin/','prod'),
(278,'2025-10-10 14:15:44','46.252.1.30',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0',0,'/restor/?p=1010&preview=true','prod'),
(279,'2025-10-14 18:24:34','84.17.46.88',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',24,'/restor/stat/','prod'),
(280,'2025-10-14 18:24:58','84.17.46.88',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',27,'/restor/','prod'),
(281,'2025-10-14 18:25:25','84.17.46.88',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',11,'/restor/bt/','prod'),
(282,'2025-10-14 18:25:36','84.17.46.88',3,'Тима','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',0,'/restor/bt/','prod'),
(3481,'2026-06-10 11:59:00','185.176.246.223',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',0,'/restor/?p=1010&preview=true','prod'),
(3569,'2026-06-12 18:49:58','185.176.246.223',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0',37,'/restor/','prod'),
(3570,'2026-06-12 18:50:35','185.176.246.223',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0',19,'/restor/stat/','prod'),
(3571,'2026-06-12 18:50:54','185.176.246.223',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0',3,'/restor/','prod'),
(3572,'2026-06-12 18:50:57','185.176.246.223',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0',0,'/restor/clock1896/','prod'),
(5123,'2026-07-31 09:57:17','46.252.8.1',7,'Дмитрий','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',14,'/restor/','prod'),
(5124,'2026-07-31 09:57:31','46.252.8.1',7,'Дмитрий','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',0,'/restor/tougin/','prod'),
(5191,'2026-08-01 12:44:24','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',30,'/restor/stat/','prod'),
(5192,'2026-08-01 12:44:54','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',9,'/restor/bat/','prod'),
(5193,'2026-08-01 12:45:03','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',469,'/restor/fridge/','prod'),
(5194,'2026-08-01 12:52:52','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',105,'/restor/stat/','prod'),
(5195,'2026-08-01 12:54:37','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',5,'/restor/','prod'),
(5196,'2026-08-01 12:54:42','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',-1,'/restor/table1946/','prod'),
(5197,'2026-08-01 12:54:51','46.252.8.1',1,'yb','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:153.0) Gecko/20100101 Firefox/153.0',-1,'/restor/clock1896/','prod');
/*!40000 ALTER TABLE `wd_visitor_stats` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-14 10:37:22
