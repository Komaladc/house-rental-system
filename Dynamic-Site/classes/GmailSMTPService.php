<?php
/**
 * Gmail SMTP Email Service
 * Uses cURL to send emails via Gmail's SMTP (for production)
 * For development, logs emails to file
 */

// Include Gmail configuration
$config_path = dirname(__DIR__) . '/config/gmail_config.php';
if (file_exists($config_path)) {
    include_once $config_path;
}

if (!class_exists('GmailSMTPService')) {
    class GmailSMTPService {
        private $gmail_user;
        private $gmail_pass;
        private $from_name;
        private $from_address;
        
        public function __construct() {
            $this->gmail_user = defined('GMAIL_SMTP_USER') ? GMAIL_SMTP_USER : '';
            $this->gmail_pass = defined('GMAIL_SMTP_PASS') ? GMAIL_SMTP_PASS : '';
            $this->from_name = defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'Property Finder Nepal';
            $this->from_address = defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : 'bistak297@gmail.com';
        }
        
        public function sendEmail($to, $subject, $body) {
            // Check if we should send real emails or just log
            $testMode = defined('EMAIL_TEST_MODE') ? EMAIL_TEST_MODE : true;
            
            if ($testMode) {
                // Test mode: just log the email
                $this->logEmail($to, $subject, $body, true);
                
                return array(
                    'success' => true,
                    'message' => 'Email logged in test mode',
                    'method' => 'test_mode_log',
                    'from' => $this->from_address,
                    'to' => $to
                );
            }
            
            // Production mode: send real email
            if ($this->isConfigured()) {
                // Try to send real email using PHP mail with proper headers
                $success = $this->sendRealEmail($to, $subject, $body);
                
                // Log the email with actual status
                $this->logEmail($to, $subject, $body, $success);
                
                if ($success) {
                    return array(
                        'success' => true,
                        'message' => 'Email sent successfully via Gmail SMTP',
                        'method' => 'gmail_smtp',
                        'from' => $this->from_address,
                        'to' => $to
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => 'Failed to send email via Gmail SMTP',
                        'method' => 'gmail_smtp_failed',
                        'from' => $this->from_address,
                        'to' => $to
                    );
                }
            } else {
                // Log the email with failure status
                $this->logEmail($to, $subject, $body, false);
                
                return array(
                    'success' => false,
                    'message' => 'Gmail SMTP not configured',
                    'method' => 'fallback_log'
                );
            }
        }
        
        private function sendRealEmail($to, $subject, $body) {
            // Use the real Gmail SMTP implementation
            require_once 'RealGmailSMTP.php';
            
            $realGmail = new RealGmailSMTP();
            $result = $realGmail->sendEmail($to, $subject, $body);
            
            return $result['success'];
        }
        
        private function isConfigured() {
            return !empty($this->gmail_user) && 
                   !empty($this->gmail_pass) && 
                   $this->gmail_user !== 'your-email@gmail.com' &&
                   $this->gmail_pass !== 'your-app-password';
        }
        
        private function logEmail($to, $subject, $body, $success = false) {
            $status = $success ? '✅ SENT VIA GMAIL SMTP' : '📝 LOGGED (SMTP NOT AVAILABLE)';
            
            $logEntry = "\n=== EMAIL: $status ===\n";
            $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $logEntry .= "From: {$this->from_name} <{$this->from_address}>\n";
            $logEntry .= "To: $to\n";
            $logEntry .= "Subject: $subject\n";
            $logEntry .= "SMTP User: {$this->gmail_user}\n";
            $logEntry .= "Method: " . ($success ? 'Gmail SMTP' : 'File Log') . "\n";
            $logEntry .= "Message:\n$body\n";
            $logEntry .= "===================\n";
            
            file_put_contents('email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
        }
        
        public function getStatus() {
            return array(
                'configured' => $this->isConfigured(),
                'gmail_user' => $this->gmail_user,
                'gmail_pass_set' => !empty($this->gmail_pass) && $this->gmail_pass !== 'your-app-password',
                'from_address' => $this->from_address,
                'from_name' => $this->from_name
            );
        }
    }
}
