<?php
/**
 * XAMPP SMTP Configuration for Gmail
 * This script will help configure XAMPP to send real emails
 */

echo "<h2>📧 XAMPP Gmail SMTP Configuration</h2>";

// Check current PHP mail settings
echo "<h3>Current PHP Mail Settings:</h3>";
echo "<table border='1' style='border-collapse:collapse;'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Required for Gmail</th></tr>";
echo "<tr><td>SMTP</td><td>" . ini_get('SMTP') . "</td><td>smtp.gmail.com</td></tr>";
echo "<tr><td>smtp_port</td><td>" . ini_get('smtp_port') . "</td><td>587</td></tr>";
echo "<tr><td>sendmail_from</td><td>" . ini_get('sendmail_from') . "</td><td>bistak297@gmail.com</td></tr>";
echo "<tr><td>sendmail_path</td><td>" . ini_get('sendmail_path') . "</td><td>(not needed for Windows)</td></tr>";
echo "</table>";

echo "<h3>🔧 Configuration Steps:</h3>";

echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin:10px 0;'>";
echo "<h4>Option 1: Automatic Configuration (Try First)</h4>";

if (isset($_POST['auto_configure'])) {
    echo "<p>Attempting automatic configuration...</p>";
    
    // Try to set SMTP settings programmatically
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    ini_set('sendmail_from', 'bistak297@gmail.com');
    
    echo "<p>✓ SMTP settings updated for this session</p>";
    
    // Test email sending
    $test_email = "bistak297@gmail.com";
    $test_subject = "XAMPP Gmail SMTP Test";
    $test_message = "<html><body><h2>Test Email</h2><p>This email was sent from XAMPP using Gmail SMTP configuration.</p><p>Time: " . date('Y-m-d H:i:s') . "</p></body></html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Property Finder Nepal <bistak297@gmail.com>" . "\r\n";
    
    $result = @mail($test_email, $test_subject, $test_message, $headers);
    
    if ($result) {
        echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> Test email sent successfully!</p>";
        echo "<p>Check the email inbox for: {$test_email}</p>";
    } else {
        $error = error_get_last();
        echo "<p style='color:red;'>❌ <strong>FAILED!</strong> Email sending failed.</p>";
        echo "<p>Error: " . ($error ? $error['message'] : 'Unknown error') . "</p>";
        echo "<p>Try Option 2 below...</p>";
    }
}

if (!isset($_POST['auto_configure'])) {
    echo "<form method='post'>";
    echo "<button type='submit' name='auto_configure' style='background:#28a745;color:white;padding:10px 20px;border:none;'>Auto-Configure & Test</button>";
    echo "</form>";
}

echo "</div>";

echo "<div style='background:#e7f3ff;padding:15px;border-left:4px solid #0066cc;margin:10px 0;'>";
echo "<h4>Option 2: Manual php.ini Configuration</h4>";
echo "<p>If automatic configuration doesn't work, manually edit your php.ini file:</p>";
echo "<ol>";
echo "<li>Open <code>C:\\xampp\\php\\php.ini</code> in a text editor</li>";
echo "<li>Find the <code>[mail function]</code> section</li>";
echo "<li>Update these lines:</li>";
echo "</ol>";

echo "<pre style='background:#f8f9fa;padding:10px;'>";
echo "[mail function]\n";
echo "SMTP = smtp.gmail.com\n";
echo "smtp_port = 587\n";
echo "sendmail_from = bistak297@gmail.com\n";
echo "; For Win32 only.\n";
echo "sendmail_path = \"C:\\xampp\\sendmail\\sendmail.exe -t\"\n";
echo "</pre>";

echo "<p>4. Save the file and restart Apache from XAMPP Control Panel</p>";
echo "</div>";

echo "<div style='background:#f8d7da;padding:15px;border-left:4px solid #dc3545;margin:10px 0;'>";
echo "<h4>Option 3: Configure XAMPP Sendmail (Recommended)</h4>";
echo "<p>Configure XAMPP's sendmail to use Gmail SMTP:</p>";
echo "<ol>";
echo "<li>Open <code>C:\\xampp\\sendmail\\sendmail.ini</code></li>";
echo "<li>Update these settings:</li>";
echo "</ol>";

echo "<pre style='background:#f8f9fa;padding:10px;'>";
echo "[sendmail]\n";
echo "smtp_server=smtp.gmail.com\n";
echo "smtp_port=587\n";
echo "smtp_ssl=tls\n";
echo "auth_username=bistak297@gmail.com\n";
echo "auth_password=gibyhyvtgifhtctc\n";
echo "force_sender=bistak297@gmail.com\n";
echo "</pre>";

echo "<p>3. In php.ini, make sure:</p>";
echo "<pre style='background:#f8f9fa;padding:10px;'>";
echo "sendmail_path = \"C:\\xampp\\sendmail\\sendmail.exe -t\"\n";
echo "</pre>";

echo "<p>4. Restart Apache</p>";
echo "</div>";

echo "<h3>🧪 Test Real Email System:</h3>";
echo "<p><a href='test_signup_form.php' style='background:#0066cc;color:white;padding:10px 15px;text-decoration:none;'>Test Signup Form</a></p>";
echo "<p><a href='signup_with_verification.php' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;'>Go to Real Signup</a></p>";

echo "<h3>📝 Debug Information:</h3>";
if (file_exists('real_email_log.txt')) {
    echo "<h4>Real Email Log:</h4>";
    $log = file_get_contents('real_email_log.txt');
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars($log) . "</pre>";
}

if (file_exists('email_log.txt')) {
    echo "<h4>General Email Log:</h4>";
    $log = file_get_contents('email_log.txt');
    $lines = explode("\n", $log);
    $recent = array_slice($lines, -10);
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars(implode("\n", $recent)) . "</pre>";
}
?>
