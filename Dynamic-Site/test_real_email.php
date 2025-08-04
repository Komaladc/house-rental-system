<?php
require_once 'config/config.php';
require_once 'classes/EmailOTP.php';

echo "<h2>🧪 Real Email Test</h2>";

// Force real email mode for this test
define('FORCE_REAL_EMAIL', true);

try {
    $emailOTP = new EmailOTP();
    
    echo "<p>Testing real email sending...</p>";
    
    // Test with a real email address
    $testEmail = "bistak297@gmail.com"; // Send to yourself for testing
    $otp = $emailOTP->generateOTP();
    
    echo "<p>Generated OTP: <strong>{$otp}</strong></p>";
    echo "<p>Sending to: <strong>{$testEmail}</strong></p>";
    
    $result = $emailOTP->sendOTP($testEmail, $otp);
    
    if ($result) {
        echo "<p style='color: green;'>✅ <strong>SUCCESS!</strong> Email sent successfully!</p>";
        echo "<p>Check your email inbox for the OTP.</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>FAILED!</strong> Email sending failed.</p>";
    }
    
    // Show recent logs
    if (file_exists('email_log.txt')) {
        echo "<h3>📝 Recent Email Log:</h3>";
        $lines = file('email_log.txt');
        $lastLines = array_slice($lines, -20);
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
        echo htmlspecialchars(implode("", $lastLines));
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><a href='signup_with_verification.php'>← Back to Signup</a>";
echo " | <a href='email_debug.php'>Debug Page →</a>";
?>
