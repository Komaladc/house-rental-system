<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚀 Real Email System Test</h2>";

// Load configuration
require_once 'config/config.php';
include_once 'config/gmail_config.php';

echo "<h3>📋 Configuration Status:</h3>";
echo "<p><strong>Test Mode:</strong> " . (defined('EMAIL_TEST_MODE') ? (EMAIL_TEST_MODE ? 'ON (test)' : 'OFF (real)') : 'Unknown') . "</p>";
echo "<p><strong>Gmail User:</strong> " . (defined('GMAIL_SMTP_USER') ? GMAIL_SMTP_USER : 'Not set') . "</p>";
echo "<p><strong>From Address:</strong> " . (defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : 'Not set') . "</p>";

if (defined('EMAIL_TEST_MODE') && !EMAIL_TEST_MODE) {
    echo "<p style='color:green;'>✅ <strong>REAL EMAIL MODE ENABLED</strong></p>";
    
    // Test the email system
    echo "<h3>🧪 Testing Real Email Sending:</h3>";
    
    try {
        require_once 'classes/EmailOTP.php';
        
        $emailOTP = new EmailOTP();
        
        // Generate OTP
        $otp = $emailOTP->generateOTP();
        echo "<p>Generated OTP: <strong>{$otp}</strong></p>";
        
        // Try to send to the configured Gmail address
        $testEmail = "bistak297@gmail.com";
        echo "<p>Sending test OTP to: <strong>{$testEmail}</strong></p>";
        
        $result = $emailOTP->sendOTP($testEmail, $otp);
        
        if ($result) {
            echo "<p style='color:green;font-size:18px;'>🎉 <strong>SUCCESS!</strong></p>";
            echo "<p>✅ Real email sent successfully!</p>";
            echo "<p>📧 Check your Gmail inbox: {$testEmail}</p>";
            echo "<p>🔑 Look for OTP: <strong>{$otp}</strong></p>";
        } else {
            echo "<p style='color:red;font-size:18px;'>❌ <strong>FAILED!</strong></p>";
            echo "<p>Email sending failed. Check logs below.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    // Show logs
    echo "<h3>📝 Email Logs:</h3>";
    
    if (file_exists('real_email_log.txt')) {
        echo "<h4>Real Email Attempts:</h4>";
        $log = file_get_contents('real_email_log.txt');
        echo "<pre style='background:#f0f0f0;padding:10px;max-height:300px;overflow-y:scroll;'>" . htmlspecialchars($log) . "</pre>";
    }
    
    if (file_exists('email_log.txt')) {
        echo "<h4>General Email Log (last 10 lines):</h4>";
        $lines = file('email_log.txt');
        $recent = array_slice($lines, -10);
        echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars(implode('', $recent)) . "</pre>";
    }
    
} else {
    echo "<p style='color:orange;'>⚠️ <strong>TEST MODE IS STILL ON</strong></p>";
    echo "<p>To enable real emails, set EMAIL_TEST_MODE = false in gmail_config.php</p>";
}

echo "<hr>";
echo "<p><a href='signup_with_verification.php'>← Try Real Signup Form</a></p>";
echo "<p><a href='configure_xampp_smtp.php'>← XAMPP Configuration</a></p>";
echo "<p><a href='email_debug.php'>← Email Debug Page</a></p>";
?>
