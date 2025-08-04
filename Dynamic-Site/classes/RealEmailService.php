<?php
/**
 * Real Email Service using Gmail SMTP
 * Handles email sending with proper fallback for XAMPP development
 */

require_once 'GmailSMTPService.php';

if (!class_exists('RealEmailService')) {
    class RealEmailService {
        private $gmailService;
        private $isConfigured = false;
        
        public function __construct() {
            $this->gmailService = new GmailSMTPService();
            $this->isConfigured = $this->checkConfiguration();
        }
        
        private function checkConfiguration() {
            $status = $this->gmailService->getStatus();
            return $status['configured'];
        }
        
        private function getGmailUser() {
            // Check multiple sources for Gmail SMTP username (for authentication)
            if (defined('GMAIL_SMTP_USER')) {
                return GMAIL_SMTP_USER;
            }
            
            if (isset($_ENV['GMAIL_SMTP_USER'])) {
                return $_ENV['GMAIL_SMTP_USER'];
            }
            
            // Fallback to old config
            if (defined('GMAIL_USERNAME')) {
                return GMAIL_USERNAME;
            }
            
            // Default for development
            return 'your-email@gmail.com'; // CHANGE THIS TO YOUR GMAIL
        }
        
        private function getGmailPassword() {
            // Check multiple sources for Gmail app password
            if (defined('GMAIL_SMTP_PASS')) {
                return GMAIL_SMTP_PASS;
            }
            
            if (isset($_ENV['GMAIL_SMTP_PASS'])) {
                return $_ENV['GMAIL_SMTP_PASS'];
            }
            
            // Fallback to old config
            if (defined('GMAIL_PASSWORD')) {
                return GMAIL_PASSWORD;
            }
            
            // Default for development
            return 'your-app-password'; // CHANGE THIS TO YOUR GMAIL APP PASSWORD
        }
        
        public function sendEmail($to, $subject, $body, $fromName = 'Property Finder Nepal') {
            // Use the Gmail SMTP service instead of the old method
            return $this->gmailService->sendEmail($to, $subject, $body);
        }
        
        private function getFromAddress() {
            return defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : 'bistak297@gmail.com';
        }
        
        private function logEmail($to, $subject, $body) {
            // Fallback: log to file
            $logEntry = "\n=== EMAIL SENT ===\n";
            $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $logEntry .= "To: " . $to . "\n";
            $logEntry .= "Subject: " . $subject . "\n";
            $logEntry .= "Message:\n" . $body . "\n";
            $logEntry .= "===================\n";
            
            file_put_contents('email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
        }
        
        public function isConfigured() {
            return $this->isConfigured;
        }
        
        public function getConfigurationStatus() {
            $status = $this->gmailService->getStatus();
            return array(
                'configured' => $status['configured'],
                'gmail_user' => $status['gmail_user'],
                'gmail_pass_set' => $status['gmail_pass_set'],
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'from_address' => $status['from_address'],
                'from_name' => $status['from_name']
            );
        }
    }
}
