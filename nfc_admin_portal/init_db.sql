CREATE DATABASE IF NOT EXISTS nfc_patrol_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nfc_patrol_admin;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(320) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS patrol_tag_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    site VARCHAR(150) NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    raw_payload JSON NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tag_id (tag_id),
    INDEX idx_site (site),
    INDEX idx_received_at (received_at)
);

-- Default admin login: admin / admin123
-- Change this password after first login.
INSERT INTO admin_users (username, password_hash)
SELECT 'admin', '$2y$10$wH6BXJ8tgM1ms9LMKTPZCevZUcfrgV7LGwN/y/yDmYd.M7/U0in7O'
WHERE NOT EXISTS (SELECT 1 FROM admin_users WHERE username = 'admin');

-- Sample Patrol Logs (Patrol Tag Records)
INSERT INTO patrol_tag_records (tag_id, description, site, latitude, longitude, raw_payload, received_at)
VALUES 
('TAG-MAIN-001', 'Main entrance security checkpoint', 'Midland Estate', -26.2041000, 28.0473000, '{"device_id": "HANDHELD-A1", "battery": "98%"}', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
('TAG-NORTH-002', 'North perimeter fence check', 'Midland Estate', -26.2052000, 28.0485000, '{"device_id": "HANDHELD-A1", "battery": "95%"}', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
('TAG-WHSE-005', 'Warehouse loading dock scan', 'North Warehouse', -26.1500000, 28.1000000, '{"device_id": "HANDHELD-B2", "battery": "100%"}', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('TAG-CORP-010', 'Server room access verification', 'Corporate HQ', -26.1076000, 28.0567000, '{"device_id": "HANDHELD-C3", "battery": "92%"}', DATE_SUB(NOW(), INTERVAL 5 HOUR));
