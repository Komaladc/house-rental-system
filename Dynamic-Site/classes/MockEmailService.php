<?php
/**
 * Mock Email Service for Testing
 * Since XAMPP doesn't have mail configured by default
 */

class MockEmailService {
    private static $emailLog = [];
    
    /**
     * Mock email sending - stores email details instead of actually sending
     */
    public static function sendEmail($to, $subject, $message, $headers = '') {
        // Store email details for testing
        $emailData = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'sent'
        ];
        
        self::$emailLog[] = $emailData;
        
        // Save to file for debugging
        $logFile = 'email_log.txt';
        $logEntry = "=== EMAIL SENT ===\n";
        $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $logEntry .= "To: $to\n";
        $logEntry .= "Subject: $subject\n";
        $logEntry .= "Message:\n$message\n";
        $logEntry .= "===================\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Always return true for testing
        return true;
    }
    
    /**
     * Get all sent emails (for testing)
     */
    public static function getSentEmails() {
        return self::$emailLog;
    }
    
    /**
     * Get last sent email (for testing)
     */
    public static function getLastEmail() {
        return end(self::$emailLog);
    }
    
    /**
     * Clear email log
     */
    public static function clearLog() {
        self::$emailLog = [];
        if (file_exists('email_log.txt')) {
            unlink('email_log.txt');
        }
    }
}
?>
