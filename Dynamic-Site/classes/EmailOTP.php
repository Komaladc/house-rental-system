<?php
/**
 * Email Configuration and OTP System
 * For Nepal House Rental System
 */

// Include timezone configuration for Nepal time
include_once dirname(__DIR__) . '/config/timezone.php';
include_once dirname(__DIR__) . '/helpers/NepalTime.php';

if (!class_exists('EmailOTP')) {
// Include email services
include_once 'MockEmailService.php';
include_once 'RealEmailService.php';
include_once dirname(__DIR__) . '/config/gmail_config.php';

class EmailOTP {
    private $db;
    private $from_email = "bistak297@gmail.com"; // Your actual Gmail address
    private $from_name = "Property Finder Nepal";
    private $realEmailService;
    private $mockEmailService;
    private $use_real_email = false; // Will be determined by configuration
    
    public function __construct($existingDb = null) {
        // Use existing database connection if provided, otherwise assume it's already included
        if ($existingDb && is_object($existingDb)) {
            $this->db = $existingDb;
        } else {
            // Assume Database class is already included by caller
            $this->db = new Database();
        }
        
        // Initialize email services
        $this->realEmailService = new RealEmailService();
        $this->mockEmailService = new MockEmailService();
        
        // Determine which email service to use
        $this->use_real_email = $this->shouldUseRealEmail();
    }
    
    /**
     * Determine if we should use real email or mock email
     */
    private function shouldUseRealEmail() {
        // Check if test mode is disabled and Gmail is configured
        if (defined('EMAIL_TEST_MODE') && !EMAIL_TEST_MODE) {
            $configStatus = $this->realEmailService->getConfigurationStatus();
            return $configStatus['configured'] && $configStatus['gmail_pass_set'];
        }
        return false;
    }
    
    /**
     * Generate 6-digit OTP
     */
    public function generateOTP() {
        return sprintf("%06d", mt_rand(100000, 999999));
    }
    
    /**
     * Store OTP in database with expiry time
     */
    public function storeOTP($email, $otp, $purpose = 'registration') {
        // Delete any existing OTP for this email and purpose
        $deleteQuery = "DELETE FROM tbl_otp WHERE email = '$email' AND purpose = '$purpose'";
        $this->db->delete($deleteQuery);
        
        // Store new OTP (expires in 20 minutes for better reliability)
        // Using Nepal time (Asia/Kathmandu)
        $currentTime = NepalTime::now();
        $expiry = NepalTime::addMinutes(20);
        
        $insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, expires_at, created_at) 
                       VALUES ('$email', '$otp', '$purpose', '$expiry', '$currentTime')";
        
        // Debug logging with Nepal time
        error_log("Storing OTP (Nepal Time) - Email: $email, OTP: $otp, Created: $currentTime, Expires: $expiry");
        
        return $this->db->insert($insertQuery);
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($email, $otp, $purpose = 'registration') {
        $currentTime = NepalTime::now();
        
        // Sanitize inputs
        $email = mysqli_real_escape_string($this->db->link, trim($email));
        $otp = mysqli_real_escape_string($this->db->link, trim($otp));
        $purpose = mysqli_real_escape_string($this->db->link, trim($purpose));
        
        // Debug logging with Nepal time
        error_log("verifyOTP called (Nepal Time) - Email: $email, OTP: $otp, Purpose: $purpose, Current Time: $currentTime");
        
        $query = "SELECT * FROM tbl_otp 
                 WHERE email = '$email' 
                 AND otp = '$otp' 
                 AND purpose = '$purpose' 
                 AND expires_at > '$currentTime' 
                 AND is_used = 0";
        
        error_log("OTP Query: $query");
        
        $result = $this->db->select($query);
        
        if($result && $result->num_rows > 0) {
            // Mark OTP as used
            $updateQuery = "UPDATE tbl_otp SET is_used = 1 WHERE email = '$email' AND otp = '$otp' AND purpose = '$purpose'";
            $this->db->update($updateQuery);
            error_log("OTP verification SUCCESS for $email");
            return true;
        } else {
            // Debug: Check what OTPs exist for this email
            $debugQuery = "SELECT * FROM tbl_otp WHERE email = '$email' AND purpose = '$purpose' ORDER BY created_at DESC LIMIT 3";
            $debugResult = $this->db->select($debugQuery);
            
            if ($debugResult && $debugResult->num_rows > 0) {
                error_log("Available OTPs for $email:");
                while ($row = $debugResult->fetch_assoc()) {
                    error_log("  OTP: {$row['otp']}, Used: {$row['is_used']}, Expires: {$row['expires_at']}, Created: {$row['created_at']}");
                }
            } else {
                error_log("No OTPs found for $email with purpose $purpose");
            }
            
            error_log("OTP verification FAILED for $email");
            return false;
        }
    }
    
    /**
     * Send OTP via email using PHP mail function
     * Note: For production, consider using PHPMailer or other robust email libraries
     */
    public function sendOTP($email, $otp, $purpose = 'registration') {
        $subject = "";
        $message = "";
        
        switch($purpose) {
            case 'registration':
                $subject = "Email Verification - Property Finder Nepal";
                $message = $this->getRegistrationEmailTemplate($otp, $email);
                break;
                
            case 'password_reset':
                $subject = "Password Reset - Property Finder Nepal";
                $message = $this->getPasswordResetEmailTemplate($otp, $email);
                break;
                
            default:
                $subject = "Verification Code - Property Finder Nepal";
                $message = $this->getDefaultEmailTemplate($otp);
        }
        
        // Email headers (for fallback mail function)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        
        // Send email using appropriate service
        if ($this->use_real_email) {
            // Use real Gmail SMTP
            $result = $this->realEmailService->sendEmail($email, $subject, $message);
            return $result['success'];
        } else {
            // Use mock service for testing
            return MockEmailService::sendEmail($email, $subject, $message, $headers);
        }
    }
    
    /**
     * Email template for registration verification
     */
    private function getRegistrationEmailTemplate($otp, $email = '') {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .otp-code { font-size: 32px; font-weight: bold; color: #e74c3c; text-align: center; 
                           background: white; padding: 20px; margin: 20px 0; border: 2px dashed #e74c3c; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .warning { color: #e74c3c; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏠 Property Finder Nepal</h1>
                    <h2>Email Verification Required</h2>
                </div>
                
                <div class='content'>
                    <h3>नमस्कार! Welcome to Property Finder Nepal!</h3>
                    
                    <p>Thank you for registering with Property Finder Nepal, your trusted platform for finding rental properties across beautiful Nepal.</p>
                    
                    <div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <h4>📧 Email Verification Required</h4>
                        <p><strong>We need to verify that you have access to this email address:</strong></p>
                        <p style='font-size: 18px; font-weight: bold; color: #2c3e50;'>📧 " . htmlspecialchars($email) . "</p>
                        <p>This email was sent from <strong>bistak297@gmail.com</strong> (Property Finder Nepal) to verify your ownership of the above email address.</p>
                    </div>
                    
                    <p>To complete your registration and verify your email address, please use the following One-Time Password (OTP):</p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <p><strong>⚠️ Important Instructions:</strong></p>
                    <ul>
                        <li>This OTP is valid for <strong>10 minutes only</strong></li>
                        <li>Enter this code on our verification page</li>
                        <li>Do not share this code with anyone</li>
                        <li>This email was sent to verify your ownership of this email address</li>
                    </ul>
                    
                    <p class='warning'>⚠️ If you did not request this verification, please ignore this email.</p>
                    
                    <p>Once verified, you'll be able to:</p>
                    <ul>
                        <li>🔍 Search for rental properties across Nepal</li>
                        <li>💝 Save properties to your wishlist</li>
                        <li>📞 Contact property owners directly</li>
                        <li>🏘️ List your own properties (for owners)</li>
                    </ul>
                    
                    <p>Thank you for choosing Property Finder Nepal!</p>
                </div>
                
                <div class='footer'>
                    <p>Property Finder Nepal Ltd. | Thamel Marg, Kathmandu-44600, Nepal</p>
                    <p>📧 info@propertyfindernepal.com | 📞 +977-1-4567890</p>
                    <p>This is an automated email. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Email template for password reset
     */
    private function getPasswordResetEmailTemplate($otp, $email = '') {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .otp-code { font-size: 32px; font-weight: bold; color: #e74c3c; text-align: center; 
                           background: white; padding: 20px; margin: 20px 0; border: 2px dashed #e74c3c; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .warning { color: #e74c3c; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                    <h2>Property Finder Nepal</h2>
                </div>
                
                <div class='content'>
                    <h3>Password Reset Verification</h3>
                    
                    <p>You have requested to reset your password for your Property Finder Nepal account.</p>
                    
                    <p>Please use the following verification code to proceed with password reset:</p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <p><strong>Security Information:</strong></p>
                    <ul>
                        <li>This code expires in <strong>10 minutes</strong></li>
                        <li>Use this code only on our official website</li>
                        <li>Never share this code with anyone</li>
                    </ul>
                    
                    <p class='warning'>⚠️ If you did not request a password reset, please ignore this email and ensure your account is secure.</p>
                </div>
                
                <div class='footer'>
                    <p>Property Finder Nepal Ltd. | Thamel Marg, Kathmandu-44600, Nepal</p>
                    <p>📧 info@propertyfindernepal.com | 📞 +977-1-4567890</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Default email template
     */
    private function getDefaultEmailTemplate($otp) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .otp-code { font-size: 24px; font-weight: bold; color: #2c3e50; text-align: center; 
                           background: #ecf0f1; padding: 15px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Verification Code - Property Finder Nepal</h2>
                <p>Your verification code is:</p>
                <div class='otp-code'>$otp</div>
                <p>This code expires in 10 minutes.</p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Send verification email with both OTP and verification link
     */
    public function sendVerificationEmail($email, $otp, $verificationToken) {
        $subject = "Email Verification Required - Property Finder Nepal";
        $verificationLink = "http://localhost/house-rental-system/Dynamic-Site/verify_registration.php?email=" . urlencode($email) . "&token=" . $verificationToken;
        
        $message = $this->getPreRegistrationEmailTemplate($otp, $verificationLink, $email);
        
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        
        // Send email using appropriate service
        if ($this->use_real_email) {
            // Use real Gmail SMTP
            $result = $this->realEmailService->sendEmail($email, $subject, $message);
            return $result['success'];
        } else {
            // Use mock service for testing
            return MockEmailService::sendEmail($email, $subject, $message, $headers);
        }
    }
    
    /**
     * Email template for pre-registration verification
     */
    private function getPreRegistrationEmailTemplate($otp, $verificationLink, $email = '') {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .otp-code { font-size: 32px; font-weight: bold; color: #e74c3c; text-align: center; 
                           background: white; padding: 20px; margin: 20px 0; border: 2px dashed #e74c3c; }
                .verify-button { background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; 
                               border-radius: 5px; font-weight: bold; display: inline-block; margin: 10px 0; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .warning { color: #e74c3c; font-weight: bold; }
                .step-box { background: #e8f5e8; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #27ae60; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏠 Property Finder Nepal</h1>
                    <h2>📧 Email Verification Required</h2>
                </div>
                
                <div class='content'>
                    <h3>नमस्कार! Welcome to Property Finder Nepal!</h3>
                    
                    <p>Thank you for your interest in joining Property Finder Nepal, your trusted platform for finding rental properties across beautiful Nepal.</p>
                    
                    <div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <h4>📧 Email Verification Required</h4>
                        <p><strong>We need to verify that you have access to this email address:</strong></p>
                        <p style='font-size: 18px; font-weight: bold; color: #2c3e50;'>📧 " . htmlspecialchars($email) . "</p>
                        <p>This email was sent from <strong>bistak297@gmail.com</strong> (Property Finder Nepal) to verify your ownership of the above email address.</p>
                    </div>
                    
                    <p><strong>🔐 To complete your registration, please verify your email address using one of the methods below:</strong></p>
                    
                    <div class='step-box'>
                        <h4>✅ Method 1: One-Click Verification (Recommended)</h4>
                        <p>Click the button below to instantly verify your email and create your account:</p>
                        <div style='text-align: center;'>
                            <a href='$verificationLink' class='verify-button'>🚀 Verify Email & Create Account</a>
                        </div>
                    </div>
                    
                    <div class='step-box'>
                        <h4>🔢 Method 2: OTP Verification</h4>
                        <p>Alternatively, you can enter this verification code on our website:</p>
                        <div class='otp-code'>$otp</div>
                        <p style='text-align: center;'>
                            <a href='http://localhost/house-rental-system/Dynamic-Site/verify_registration.php' style='color: #2c3e50; font-weight: bold;'>📱 Enter OTP Code Here</a>
                        </p>
                    </div>
                    
                    <p><strong>⚠️ Important Security Information:</strong></p>
                    <ul>
                        <li>This verification link and code are valid for <strong>1 hour only</strong></li>
                        <li>Your account will NOT be created until you verify your email</li>
                        <li>Do not share this email or verification code with anyone</li>
                        <li>If you did not request this verification, please ignore this email</li>
                    </ul>
                    
                    <div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4>🛡️ Why We Require Email Verification:</h4>
                        <ul>
                            <li>✅ Ensures you have access to your email for important notifications</li>
                            <li>✅ Prevents spam and fake accounts</li>
                            <li>✅ Protects your account security</li>
                            <li>✅ Enables secure password recovery</li>
                        </ul>
                    </div>
                    
                    <p><strong>🎯 After verification, you'll be able to:</strong></p>
                    <ul>
                        <li>🔍 Search for rental properties across Nepal</li>
                        <li>💝 Save properties to your wishlist</li>
                        <li>📞 Contact property owners directly</li>
                        <li>🏘️ List your own properties (for owners)</li>
                        <li>💬 Access our support system</li>
                    </ul>
                    
                    <p>Thank you for choosing Property Finder Nepal!</p>
                </div>
                
                <div class='footer'>
                    <p>Property Finder Nepal Ltd. | Thamel Marg, Kathmandu-44600, Nepal</p>
                    <p>📧 info@propertyfindernepal.com | 📞 +977-1-4567890</p>
                    <p>This is an automated email. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Clean up expired OTPs - Using Nepal time
     */
    public function cleanupExpiredOTPs() {
        $currentTime = NepalTime::now();
        $query = "DELETE FROM tbl_otp WHERE expires_at < '$currentTime'";
        return $this->db->delete($query);
    }
}
} // End of class_exists check
?>
