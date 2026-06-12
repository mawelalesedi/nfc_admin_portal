-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: nfc_patrol_admin
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Master Database Initialization
CREATE DATABASE IF NOT EXISTS `nfc_patrol_admin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nfc_patrol_admin`;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
INSERT INTO `sites` (id, name, address, description, is_active) VALUES 
(1, 'Midland Estate', '123 Midland Drive, Johannesburg', 'Residential Complex', 1),
(2, 'North Warehouse', 'Industrial Area, Pretoria', 'Main Storage Facility', 1),
(3, 'Corporate HQ', 'Sandton Business District', 'Head Office Building', 1);
UNLOCK TABLES;

--
-- Table structure for table `guards`
--

DROP TABLE IF EXISTS `guards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(320) DEFAULT NULL,
  `assigned_site_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_site_ids`)),
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guards`
--

LOCK TABLES `guards` WRITE;
/*!40000 ALTER TABLE `guards` DISABLE KEYS */;
INSERT INTO `guards` (id, name, phone, email, status) VALUES 
(1, 'John Doe', '0123456789', 'john.doe@security.local', 'active'),
(2, 'Jane Smith', '0987654321', 'jane.smith@security.local', 'active'),
(3, 'Robert Brown', '0555123456', 'robert.b@security.local', 'active');
/*!40000 ALTER TABLE `guards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidents`
--

DROP TABLE IF EXISTS `incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `site_id` int(11) DEFAULT NULL,
  `guard_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_site_id` (`site_id`),
  KEY `idx_guard_id` (`guard_id`),
  KEY `idx_status` (`status`),
  KEY `idx_severity` (`severity`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`guard_id`) REFERENCES `guards` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidents`
--

LOCK TABLES `incidents` WRITE;
/*!40000 ALTER TABLE `incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nfc_tags`
--

DROP TABLE IF EXISTS `nfc_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nfc_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_uid` varchar(128) NOT NULL,
  `label` varchar(255) NOT NULL,
  `site_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_uid` (`tag_uid`),
  KEY `idx_site_id` (`site_id`),
  KEY `idx_tag_uid` (`tag_uid`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `nfc_tags_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nfc_tags`
--

LOCK TABLES `nfc_tags` WRITE;
/*!40000 ALTER TABLE `nfc_tags` DISABLE KEYS */;
INSERT INTO `nfc_tags` (tag_uid, label, site_id, latitude, longitude, description) VALUES 
('TAG001', 'Point 1', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG002', 'Point 2', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG003', 'Point 3', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG004', 'Point 4', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG005', 'Point 5', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG006', 'Point 6', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG007', 'Point 7', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG008', 'Point 8', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG009', 'Point 9', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG0010', 'Point 10', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG0011', 'Point 11', 1, -26.2041, 28.0473, 'Main gate checkpoint'),
('TAG0012', 'Point 12', 1, -26.2041, 28.0473, 'Main gate checkpoint');
/*!40000 ALTER TABLE `nfc_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patrol_tag_records`
--

DROP TABLE IF EXISTS `patrol_tag_records`;
CREATE TABLE `patrol_tag_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `site` varchar(150) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `raw_payload` json DEFAULT NULL,
  `received_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tag_id` (`tag_id`),
  KEY `idx_site` (`site`),
  KEY `idx_received_at` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patrol_tag_records`
--

LOCK TABLES `patrol_tag_records` WRITE;
INSERT INTO `patrol_tag_records` (tag_id, description, site, latitude, longitude, raw_payload, received_at)
VALUES 
('TAG-MAIN-001', 'Main entrance security checkpoint', 'Midland Estate', -26.2041000, 28.0473000, '{"device_id": "HANDHELD-A1", "battery": "98%"}', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
('TAG-NORTH-002', 'North perimeter fence check', 'Midland Estate', -26.2052000, 28.0485000, '{"device_id": "HANDHELD-A1", "battery": "95%"}', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
('TAG-WHSE-005', 'Warehouse loading dock scan', 'North Warehouse', -26.1500000, 28.1000000, '{"device_id": "HANDHELD-B2", "battery": "100%"}', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('TAG-CORP-010', 'Server room access verification', 'Corporate HQ', -26.1076000, 28.0567000, '{"device_id": "HANDHELD-C3", "battery": "92%"}', DATE_SUB(NOW(), INTERVAL 5 HOUR));
UNLOCK TABLES;

--
-- Table structure for table `patrol_logs`
--

DROP TABLE IF EXISTS `patrol_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patrol_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guard_id` int(11) NOT NULL,
  `nfc_tag_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guard_id` (`guard_id`),
  KEY `idx_site_id` (`site_id`),
  KEY `idx_nfc_tag_id` (`nfc_tag_id`),
  KEY `idx_scanned_at` (`scanned_at`),
  CONSTRAINT `patrol_logs_ibfk_1` FOREIGN KEY (`guard_id`) REFERENCES `guards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patrol_logs_ibfk_2` FOREIGN KEY (`nfc_tag_id`) REFERENCES `nfc_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patrol_logs_ibfk_3` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patrol_logs`
--

LOCK TABLES `patrol_logs` WRITE;
/*!40000 ALTER TABLE `patrol_logs` DISABLE KEYS */;
INSERT INTO `patrol_logs` (guard_id, nfc_tag_id, site_id, scanned_at, notes) VALUES 
(1, 1, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR), 'Main entrance clear'),
(1, 2, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 90 MINUTE), 'Perimeter check normal'),
(2, 3, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR), 'North fence secure'),
(2, 4, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE), 'All checkpoints verified'),
(3, 5, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE), 'Routine scan');
/*!40000 ALTER TABLE `patrol_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(320) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` (id, username, email, password_hash, role, is_active) VALUES (1,'admin','admin@nfcpatrol.local','$2y$10$wH6BXJ8tgM1ms9LMKTPZCevZUcfrgV7LGwN/y/yDmYd.M7/U0in7O','admin',1);
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-07 20:25:33
