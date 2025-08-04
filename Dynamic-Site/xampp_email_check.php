<?php
echo "<h2>📧 XAMPP Email Configuration Check</h2>";

echo "<h3>PHP Mail Settings</h3>";
echo "<p><strong>SMTP:</strong> " . ini_get('SMTP') . "</p>";
echo "<p><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</p>";
echo "<p><strong>sendmail_from:</strong> " . ini_get('sendmail_from') . "</p>";
echo "<p><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</p>";

echo "<h3>Mail Function Test</h3>";
if (function_exists('mail')) {
    echo "<p>✓ mail() function is available</p>";
    
    // Test basic mail function
    $test_result = @mail('test@example.com', 'Test Subject', 'Test message', 'From: test@example.com');
    echo "<p>Basic mail() test result: " . ($test_result ? 'TRUE (would send)' : 'FALSE (configuration issue)') . "</p>";
    
    $last_error = error_get_last();
    if ($last_error) {
        echo "<p><strong>Last PHP Error:</strong> " . $last_error['message'] . "</p>";
    }
} else {
    echo "<p>❌ mail() function is NOT available</p>";
}

echo "<h3>Alternative: Force Email Log Mode</h3>";
echo "<p>Since XAMPP mail() might not work, let's check the current test mode:</p>";

if (file_exists('config/gmail_config.php')) {
    include_once 'config/gmail_config.php';
    
    if (defined('EMAIL_TEST_MODE')) {
        if (EMAIL_TEST_MODE) {
            echo "<p>✓ <strong>EMAIL_TEST_MODE is ON</strong> - Emails will be logged to file</p>";
            echo "<p>This is actually GOOD for local development!</p>";
            
            // Show log content
            if (file_exists('email_log.txt')) {
                echo "<h4>Recent Email Logs:</h4>";
                $lines = file('email_log.txt');
                $recent = array_slice($lines, -15);
                echo "<pre style='background:#f0f0f0;padding:10px;max-height:300px;overflow-y:scroll;'>";
                echo htmlspecialchars(implode('', $recent));
                echo "</pre>";
            }
        } else {
            echo "<p>⚠️ <strong>EMAIL_TEST_MODE is OFF</strong> - System will try to send real emails</p>";
            echo "<p>But XAMPP might not be configured for real email sending.</p>";
        }
    }
}

echo "<h3>📝 Recommendation</h3>";
echo "<div style='background:#e7f3ff;padding:15px;border-left:4px solid #0066cc;'>";
echo "<p><strong>For Local Development (XAMPP):</strong></p>";
echo "<ul>";
echo "<li>Keep <code>EMAIL_TEST_MODE = true</code></li>";
echo "<li>Check email content in <code>email_log.txt</code></li>";
echo "<li>OTP will be visible in the log file</li>";
echo "<li>This simulates real email sending perfectly</li>";
echo "</ul>";
echo "</div>";

echo "<br><a href='signup_with_verification.php'>Test Signup Now →</a>";
?>
