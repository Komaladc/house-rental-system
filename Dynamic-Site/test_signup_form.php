<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Signup Form Test</h2>";

require_once 'config/config.php';
require_once 'classes/EmailOTP.php';
require_once 'classes/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>Form Submitted - Processing...</h3>";
    
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    
    echo "<p>Name: " . htmlspecialchars($name) . "</p>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    
    try {
        $emailOTP = new EmailOTP();
        
        echo "<p>📧 Attempting to send OTP...</p>";
        
        // Generate OTP first
        $otp = $emailOTP->generateOTP();
        echo "<p>Generated OTP: <strong>$otp</strong></p>";
        
        $result = $emailOTP->sendOTP($email, $otp);
        
        if ($result) {
            echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> OTP sending process completed</p>";
            
            // Check if it's test mode
            include_once 'config/gmail_config.php';
            if (defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE) {
                echo "<p>📝 <strong>TEST MODE:</strong> Email was logged to file</p>";
                echo "<p>Check <code>email_log.txt</code> for the OTP</p>";
                
                // Show the OTP from logs
                if (file_exists('email_log.txt')) {
                    $logContent = file_get_contents('email_log.txt');
                    if (preg_match('/Your OTP is: <strong>(\d{6})<\/strong>/', $logContent, $matches)) {
                        echo "<p>🔑 <strong>Your OTP is: {$matches[1]}</strong></p>";
                    }
                }
            } else {
                echo "<p>📧 <strong>REAL MODE:</strong> Email should be sent to your inbox</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ <strong>FAILED!</strong> OTP sending failed</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<br><hr><br>";
}
?>

<form method="POST" style="max-width:400px;">
    <h3>Test Signup Form</h3>
    <p>
        <label>Name:</label><br>
        <input type="text" name="name" value="Test User" required style="width:100%;padding:8px;">
    </p>
    <p>
        <label>Email:</label><br>
        <input type="email" name="email" value="test@example.com" required style="width:100%;padding:8px;">
    </p>
    <p>
        <button type="submit" style="background:#0066cc;color:white;padding:10px 20px;border:none;">
            Send OTP
        </button>
    </p>
</form>

<hr>
<p><a href="signup_with_verification.php">← Back to Real Signup Form</a></p>
<p><a href="email_debug.php">Email Debug Page →</a></p>
