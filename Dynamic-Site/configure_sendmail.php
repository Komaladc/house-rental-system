<?php
/**
 * XAMPP Sendmail Auto-Configuration for Gmail
 * This script will configure XAMPP's sendmail to work with Gmail SMTP + TLS
 */

echo "<h2>🔧 XAMPP Sendmail Configuration for Gmail</h2>";

$sendmail_ini_path = "C:\\xampp\\sendmail\\sendmail.ini";
$php_ini_path = "C:\\xampp\\php\\php.ini";

echo "<h3>Step 1: Check Sendmail Files</h3>";

// Check if sendmail.ini exists
if (file_exists($sendmail_ini_path)) {
    echo "<p>✓ Found sendmail.ini at: <code>{$sendmail_ini_path}</code></p>";
} else {
    echo "<p>❌ sendmail.ini not found. XAMPP might not be properly installed.</p>";
    exit;
}

// Check if php.ini exists
if (file_exists($php_ini_path)) {
    echo "<p>✓ Found php.ini at: <code>{$php_ini_path}</code></p>";
} else {
    echo "<p>❌ php.ini not found at expected location.</p>";
}

echo "<h3>Step 2: Auto-Configure Sendmail</h3>";

if (isset($_POST['configure_sendmail'])) {
    echo "<p>Configuring sendmail.ini for Gmail...</p>";
    
    // Create the sendmail.ini content
    $sendmail_config = "[sendmail]

; Configuration for using Gmail SMTP
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
error_logfile=error.log
debug_logfile=debug.log

; Gmail authentication
auth_username=bistak297@gmail.com
auth_password=gibyhyvtgifhtctc

; Email settings
force_sender=bistak297@gmail.com
force_recipient=
hostname=localhost

; Additional settings
default_domain=gmail.com
";

    // Try to write the configuration
    $write_result = @file_put_contents($sendmail_ini_path, $sendmail_config);
    
    if ($write_result !== false) {
        echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> sendmail.ini configured successfully</p>";
        
        // Show the configuration
        echo "<h4>Configuration Applied:</h4>";
        echo "<pre style='background:#f0f0f0;padding:10px;'>" . htmlspecialchars($sendmail_config) . "</pre>";
        
    } else {
        echo "<p style='color:red;'>❌ <strong>FAILED!</strong> Could not write to sendmail.ini</p>";
        echo "<p>Please manually create the file with these contents:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px;'>" . htmlspecialchars($sendmail_config) . "</pre>";
    }
}

if (isset($_POST['configure_php_ini'])) {
    echo "<p>Configuring php.ini for sendmail...</p>";
    
    // Read current php.ini
    $php_ini_content = file_get_contents($php_ini_path);
    
    if ($php_ini_content !== false) {
        // Update the mail function section
        $new_mail_config = "
[mail function]
; For Win32 only.
; http://php.net/smtp
SMTP = localhost
; http://php.net/smtp-port
smtp_port = 25

; For Win32 only.
; http://php.net/sendmail-from
sendmail_from = bistak297@gmail.com

; For Unix only.  You may supply arguments as well (default: \"sendmail -t -i\").
; http://php.net/sendmail-path
sendmail_path = \"C:\\xampp\\sendmail\\sendmail.exe -t\"
";
        
        // Replace the existing mail function section
        $pattern = '/\[mail function\].*?(?=\[|\Z)/s';
        $updated_content = preg_replace($pattern, $new_mail_config, $php_ini_content);
        
        $write_result = @file_put_contents($php_ini_path, $updated_content);
        
        if ($write_result !== false) {
            echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> php.ini configured successfully</p>";
            echo "<p style='color:orange;'>⚠️ <strong>IMPORTANT:</strong> You must restart Apache in XAMPP Control Panel!</p>";
        } else {
            echo "<p style='color:red;'>❌ <strong>FAILED!</strong> Could not write to php.ini</p>";
        }
    }
}

if (!isset($_POST['configure_sendmail']) && !isset($_POST['configure_php_ini'])) {
    echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;'>";
    echo "<h4>Auto-Configuration Options:</h4>";
    echo "<form method='post'>";
    echo "<button type='submit' name='configure_sendmail' style='background:#007bff;color:white;padding:10px 20px;border:none;margin:5px;'>Configure sendmail.ini</button><br>";
    echo "<button type='submit' name='configure_php_ini' style='background:#28a745;color:white;padding:10px 20px;border:none;margin:5px;'>Configure php.ini</button>";
    echo "</form>";
    echo "</div>";
}

echo "<h3>Step 3: Manual Configuration (If Auto Fails)</h3>";

echo "<div style='background:#e7f3ff;padding:15px;border-left:4px solid #0066cc;'>";
echo "<h4>Manual sendmail.ini Configuration:</h4>";
echo "<p>Edit <code>C:\\xampp\\sendmail\\sendmail.ini</code> and replace all content with:</p>";
echo "<textarea readonly style='width:100%;height:200px;font-family:monospace;'>[sendmail]

; Configuration for using Gmail SMTP
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
error_logfile=error.log
debug_logfile=debug.log

; Gmail authentication
auth_username=bistak297@gmail.com
auth_password=gibyhyvtgifhtctc

; Email settings
force_sender=bistak297@gmail.com
force_recipient=
hostname=localhost

; Additional settings
default_domain=gmail.com</textarea>";
echo "</div>";

echo "<div style='background:#d1ecf1;padding:15px;border-left:4px solid #bee5eb;'>";
echo "<h4>Manual php.ini Configuration:</h4>";
echo "<p>In <code>C:\\xampp\\php\\php.ini</code>, find the [mail function] section and update:</p>";
echo "<textarea readonly style='width:100%;height:120px;font-family:monospace;'>[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = bistak297@gmail.com
sendmail_path = \"C:\\xampp\\sendmail\\sendmail.exe -t\"</textarea>";
echo "</div>";

echo "<h3>Step 4: Test Email Sending</h3>";

if (isset($_POST['test_email'])) {
    echo "<p>Testing email with new configuration...</p>";
    
    $test_email = "bistak297@gmail.com";
    $subject = "XAMPP Sendmail Test - " . date('Y-m-d H:i:s');
    $message = "<html><body>";
    $message .= "<h2>✅ XAMPP Sendmail Working!</h2>";
    $message .= "<p>This email was sent successfully using XAMPP sendmail with Gmail SMTP.</p>";
    $message .= "<p><strong>Configuration:</strong> TLS encryption on port 587</p>";
    $message .= "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    $message .= "</body></html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Property Finder Nepal <bistak297@gmail.com>" . "\r\n";
    
    $result = @mail($test_email, $subject, $message, $headers);
    
    if ($result) {
        echo "<p style='color:green;font-size:18px;'>🎉 <strong>SUCCESS!</strong></p>";
        echo "<p>✅ Test email sent successfully to: {$test_email}</p>";
        echo "<p>📧 Check your Gmail inbox!</p>";
    } else {
        $error = error_get_last();
        echo "<p style='color:red;'>❌ <strong>FAILED!</strong> " . ($error ? $error['message'] : 'Unknown error') . "</p>";
        
        // Check sendmail logs
        $error_log = "C:\\xampp\\sendmail\\error.log";
        if (file_exists($error_log)) {
            echo "<h4>Sendmail Error Log:</h4>";
            $log_content = file_get_contents($error_log);
            echo "<pre style='background:#f8d7da;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars($log_content) . "</pre>";
        }
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='test_email' style='background:#dc3545;color:white;padding:15px 25px;border:none;font-size:16px;'>🧪 Test Email Sending</button>";
echo "</form>";

echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li>Configure sendmail.ini (using button above or manually)</li>";
echo "<li>Configure php.ini (using button above or manually)</li>";
echo "<li><strong>Restart Apache</strong> in XAMPP Control Panel</li>";
echo "<li>Test email sending (using button above)</li>";
echo "<li>If successful, test the signup form</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='test_real_email_final.php'>← Test Real Email System</a></p>";
echo "<p><a href='signup_with_verification.php'>← Try Signup Form</a></p>";
?>
