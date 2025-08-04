-- Additional tables for Admin Dashboard and User Verification System

-- Table for storing citizenship photos and pending verifications
CREATE TABLE IF NOT EXISTS `tbl_user_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_type` enum('agent','owner') NOT NULL,
  `citizenship_front` varchar(255) DEFAULT NULL,
  `citizenship_back` varchar(255) DEFAULT NULL,
  `business_license` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for admin activity logs
CREATE TABLE IF NOT EXISTS `tbl_admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for website analytics and statistics
CREATE TABLE IF NOT EXISTS `tbl_website_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `page_views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `new_registrations` int(11) DEFAULT 0,
  `property_listings` int(11) DEFAULT 0,
  `bookings` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add verification status to main user table
ALTER TABLE `tbl_user` 
ADD COLUMN `verification_status` enum('pending','verified','rejected') DEFAULT 'pending' AFTER `status`,
ADD COLUMN `requires_verification` tinyint(1) DEFAULT 0 AFTER `verification_status`,
ADD COLUMN `submitted_documents` tinyint(1) DEFAULT 0 AFTER `requires_verification`;

-- Add indexes for better performance
ALTER TABLE `tbl_user` ADD INDEX `verification_status` (`verification_status`);
ALTER TABLE `tbl_user` ADD INDEX `user_level` (`userLevel`);
ALTER TABLE `tbl_user` ADD INDEX `status` (`status`);

-- Create admin sessions table
CREATE TABLE IF NOT EXISTS `tbl_admin_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `admin_id` (`admin_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
