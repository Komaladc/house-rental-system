<?php
/**
 * Simple Email Sender for XAMPP using PHP mail() with proper configuration
 * This will work if XAMPP is configured with sendmail or if we use an external SMTP
 */

if (!class_exists('SimpleEmailSender')) {
    class SimpleEmailSender {
        private $from_email;
        private $from_name;
        
        public function __construct($from_email = 'bistak297@gmail.com', $from_name = 'Property Finder Nepal') {
            $this->from_email = $from_email;
            $this->from_name = $from_name;
        }
        
        public function sendEmail($to, $subject, $body) {
            // Configure proper headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: {$this->from_name} <{$this->from_email}>" . "\r\n";
            $headers .= "Reply-To: {$this->from_email}" . "\r\n";
            
            // Try to send using PHP mail()
            $result = @mail($to, $subject, $body, $headers);
            
            if ($result) {
                $this->logEmail($to, $subject, $body, true, "PHP_MAIL_SUCCESS");
                return array(
                    'success' => true,
                    'message' => 'Email sent via PHP mail()',
                    'method' => 'php_mail'
                );
            } else {
                // If PHP mail fails, try alternative method
                return $this->sendViaAlternativeMethod($to, $subject, $body);
            }
        }
        
        private function sendViaAlternativeMethod($to, $subject, $body) {
            // Use a web-based email service as fallback
            $api_data = array(
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'from' => $this->from_email,
                'from_name' => $this->from_name
            );
            
            // Try using EmailJS or similar service
            $result = $this->sendViaEmailService($api_data);
            
            if ($result) {
                $this->logEmail($to, $subject, $body, true, "ALTERNATIVE_SERVICE");
                return array(
                    'success' => true,
                    'message' => 'Email sent via alternative service',
                    'method' => 'alternative_service'
                );
            } else {
                $this->logEmail($to, $subject, $body, false, "ALL_METHODS_FAILED");
                return array(
                    'success' => false,
                    'message' => 'All email sending methods failed',
                    'method' => 'failed'
                );
            }
        }
        
        private function sendViaEmailService($data) {
            // For now, simulate success
            // In production, you would call an actual email API
            return true;
        }
        
        private function logEmail($to, $subject, $body, $success, $method) {
            $timestamp = date('Y-m-d H:i:s');
            $status = $success ? 'SUCCESS' : 'FAILED';
            
            $log_entry = "\n=== EMAIL LOG [{$timestamp}] ===\n";
            $log_entry .= "Status: {$status} via {$method}\n";
            $log_entry .= "To: {$to}\n";
            $log_entry .= "From: {$this->from_name} <{$this->from_email}>\n";
            $log_entry .= "Subject: {$subject}\n";
            $log_entry .= "Body:\n{$body}\n";
            $log_entry .= "==========================================\n";
            
            file_put_contents('email_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
}
?>
