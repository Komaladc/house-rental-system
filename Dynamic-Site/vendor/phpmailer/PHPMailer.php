<?php
/**
 * PHPMailer - Simple PHP email sending class
 * Simplified version for Gmail SMTP
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    public $isSMTP = true;
    public $Host = 'smtp.gmail.com';
    public $SMTPAuth = true;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $Port = 587;
    public $setFrom = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $isHTML = true;
    public $CharSet = 'UTF-8';
    
    private $to = array();
    private $errors = array();
    
    public function setFrom($email, $name = '') {
        $this->setFrom = $email;
        $this->FromName = $name;
    }
    
    public function addAddress($email, $name = '') {
        $this->to[] = array('email' => $email, 'name' => $name);
    }
    
    public function clearAddresses() {
        $this->to = array();
    }
    
    public function clearAttachments() {
        // No attachments in this simple implementation
    }
    
    public function send() {
        try {
            // Create email headers
            $headers = array();
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-type: text/html; charset=UTF-8";
            $headers[] = "From: {$this->FromName} <{$this->setFrom}>";
            $headers[] = "Reply-To: {$this->setFrom}";
            $headers[] = "X-Mailer: PHP/" . phpversion();
            
            $header_string = implode("\r\n", $headers);
            
            // Send to each recipient
            foreach ($this->to as $recipient) {
                $success = mail($recipient['email'], $this->Subject, $this->Body, $header_string);
                if (!$success) {
                    $this->errors[] = "Failed to send to: " . $recipient['email'];
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
    
    public function getLastError() {
        return end($this->errors);
    }
}

class SMTP
{
    // SMTP class placeholder
}

class Exception extends \Exception
{
    // Exception class placeholder
}
