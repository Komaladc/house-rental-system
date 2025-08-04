<?php
/**
 * Gmail SMTP Configuration for Nepal House Rental System
 * Using bistak297@gmail.com as sender for OTP emails
 */

// ==== SMTP AUTHENTICATION (Your actual Gmail for sending) ====
define('GMAIL_SMTP_USER', 'bistak297@gmail.com'); // Your Gmail for SMTP auth
define('GMAIL_SMTP_PASS', 'gibyhyvtgifhtctc'); // Your Gmail app password

// ==== EMAIL APPEARANCE (What users see) ====
define('EMAIL_FROM_NAME', 'Property Finder Nepal');
define('EMAIL_FROM_ADDRESS', 'bistak297@gmail.com'); // Real sender address
define('EMAIL_REPLY_TO', 'bistak297@gmail.com'); // Reply to same address

// ==== SYSTEM SETTINGS ====
define('EMAIL_TEST_MODE', false); // Set to false for real emails
define('COMPANY_NAME', 'Property Finder Nepal');
define('COMPANY_ADDRESS', 'Thamel Marg, Kathmandu-44600, Nepal');
define('COMPANY_PHONE', '+977-1-4567890');
?>