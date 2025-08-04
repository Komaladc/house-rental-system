<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Email System Diagnostic Test</h2>";

// Step 1: Check configuration
echo "<h3>1. Configuration Check</h3>";
require_once 'config/config.php';

echo "<p>✓ Config loaded</p>";

// Check if gmail config is loaded
if (file_exists('config/gmail_config.php')) {
    include_once 'config/gmail_config.php';
    echo "<p>✓ Gmail config file exists</p>";
    echo "<p>Test Mode: " . (defined('EMAIL_TEST_MODE') ? (EMAIL_TEST_MODE ? 'ON (logging only)' : 'OFF (real emails)') : 'Not defined') . "</p>";
    echo "<p>Gmail User: " . (defined('GMAIL_SMTP_USER') ? GMAIL_SMTP_USER : 'Not defined') . "</p>";
} else {
    echo "<p>❌ Gmail config file missing</p>";
}

// Step 2: Test class loading
echo "<h3>2. Class Loading Test</h3>";
try {
    require_once 'classes/EmailOTP.php';
    echo "<p>✓ EmailOTP class loaded</p>";
    
    $emailOTP = new EmailOTP();
    echo "<p>✓ EmailOTP object created</p>";
} catch (Exception $e) {
    echo "<p>❌ Error loading EmailOTP: " . $e->getMessage() . "</p>";
}

// Step 3: Test OTP generation
echo "<h3>3. OTP Generation Test</h3>";
try {
    $otp = $emailOTP->generateOTP();
    echo "<p>✓ OTP Generated: <strong>{$otp}</strong></p>";
} catch (Exception $e) {
    echo "<p>❌ Error generating OTP: " . $e->getMessage() . "</p>";
}

// Step 4: Test email sending
echo "<h3>4. Email Sending Test</h3>";
try {
    $testEmail = "test@example.com";
    echo "<p>Attempting to send OTP to: {$testEmail}</p>";
    
    $result = $emailOTP->sendOTP($testEmail, $otp);
    
    if ($result) {
        echo "<p>✓ <strong>Email sending returned TRUE</strong></p>";
    } else {
        echo "<p>❌ <strong>Email sending returned FALSE</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error sending email: " . $e->getMessage() . "</p>";
}

// Step 5: Check log file
echo "<h3>5. Log File Check</h3>";
if (file_exists('email_log.txt')) {
    $logContent = file_get_contents('email_log.txt');
    $logLines = explode("\n", $logContent);
    $recentLines = array_slice($logLines, -10);
    
    echo "<p>✓ Email log file exists</p>";
    echo "<p>Recent log entries (last 10 lines):</p>";
    echo "<pre style='background:#f5f5f5;padding:10px;'>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
} else {
    echo "<p>❌ No email log file found</p>";
}

// Step 6: Manual email test
echo "<h3>6. Manual Email Function Test</h3>";
$to = "test@example.com";
$subject = "Test Email from XAMPP";
$message = "<html><body><h2>Test Email</h2><p>This is a test email from your XAMPP server.</p></body></html>";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Property Finder Nepal <bistak297@gmail.com>" . "\r\n";

$mailResult = @mail($to, $subject, $message, $headers);

if ($mailResult) {
    echo "<p>✓ PHP mail() function returned TRUE</p>";
} else {
    echo "<p>❌ PHP mail() function returned FALSE</p>";
}

echo "<p><strong>Last error:</strong> " . (error_get_last() ? error_get_last()['message'] : 'None') . "</p>";

echo "<br><a href='signup_with_verification.php'>← Back to Signup</a>";
echo " | <a href='email_debug.php'>Debug Page →</a>";
?>
