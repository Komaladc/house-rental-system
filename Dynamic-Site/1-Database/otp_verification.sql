-- OTP Table for Email Verification
-- Add this to your existing database

CREATE TABLE IF NOT EXISTS `tbl_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `purpose` enum('registration','password_reset','email_change') NOT NULL DEFAULT 'registration',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add email_verified column to existing user table
ALTER TABLE `tbl_user` ADD COLUMN `email_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `userEmail`;
ALTER TABLE `tbl_user` ADD COLUMN `verification_token` varchar(32) NULL AFTER `email_verified`;

-- Create index for better performance
CREATE INDEX idx_user_email_verified ON tbl_user(userEmail, email_verified);
