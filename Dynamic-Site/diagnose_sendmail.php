<?php
echo "<h2>🔍 XAMPP Sendmail Diagnosis</h2>";

$sendmail_ini_path = "C:\\xampp\\sendmail\\sendmail.ini";
$sendmail_exe_path = "C:\\xampp\\sendmail\\sendmail.exe";
$php_ini_path = "C:\\xampp\\php\\php.ini";

echo "<h3>File Existence Check:</h3>";
echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr><th>File</th><th>Status</th><th>Size</th></tr>";

$files_to_check = [
    'sendmail.ini' => $sendmail_ini_path,
    'sendmail.exe' => $sendmail_exe_path,
    'php.ini' => $php_ini_path
];

foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $status = $exists ? "✅ Found" : "❌ Missing";
    echo "<tr><td>{$name}</td><td>{$status}</td><td>" . ($size > 0 ? number_format($size) . " bytes" : "N/A") . "</td></tr>";
}
echo "</table>";

echo "<h3>Current sendmail.ini Content:</h3>";
if (file_exists($sendmail_ini_path)) {
    $content = file_get_contents($sendmail_ini_path);
    echo "<pre style='background:#f0f0f0;padding:10px;max-height:300px;overflow-y:scroll;'>" . htmlspecialchars($content) . "</pre>";
    
    // Check if it's configured for Gmail
    if (strpos($content, 'smtp.gmail.com') !== false) {
        echo "<p style='color:green;'>✅ Gmail SMTP server configured</p>";
    } else {
        echo "<p style='color:red;'>❌ Gmail SMTP server not configured</p>";
    }
    
    if (strpos($content, 'smtp_ssl=tls') !== false) {
        echo "<p style='color:green;'>✅ TLS encryption enabled</p>";
    } else {
        echo "<p style='color:red;'>❌ TLS encryption not enabled</p>";
    }
    
    if (strpos($content, 'bistak297@gmail.com') !== false) {
        echo "<p style='color:green;'>✅ Gmail authentication configured</p>";
    } else {
        echo "<p style='color:red;'>❌ Gmail authentication not configured</p>";
    }
} else {
    echo "<p style='color:red;'>❌ sendmail.ini file not found</p>";
}

echo "<h3>Current PHP Mail Settings:</h3>";
echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr><th>Setting</th><th>Current Value</th></tr>";
echo "<tr><td>SMTP</td><td>" . ini_get('SMTP') . "</td></tr>";
echo "<tr><td>smtp_port</td><td>" . ini_get('smtp_port') . "</td></tr>";
echo "<tr><td>sendmail_from</td><td>" . ini_get('sendmail_from') . "</td></tr>";
echo "<tr><td>sendmail_path</td><td>" . ini_get('sendmail_path') . "</td></tr>";
echo "</table>";

echo "<h3>Sendmail Error Logs:</h3>";
$error_log_path = "C:\\xampp\\sendmail\\error.log";
if (file_exists($error_log_path)) {
    $error_content = file_get_contents($error_log_path);
    if (!empty(trim($error_content))) {
        echo "<pre style='background:#f8d7da;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars($error_content) . "</pre>";
    } else {
        echo "<p>No errors logged yet.</p>";
    }
} else {
    echo "<p>No error log file found.</p>";
}

echo "<h3>🔧 Recommended Actions:</h3>";
echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;'>";

if (!file_exists($sendmail_ini_path)) {
    echo "<p>❌ <strong>sendmail.ini missing</strong> - Use the configuration tool to create it</p>";
} else {
    $content = file_get_contents($sendmail_ini_path);
    if (strpos($content, 'smtp.gmail.com') === false) {
        echo "<p>⚠️ <strong>sendmail.ini not configured for Gmail</strong> - Use the configuration tool</p>";
    } else {
        echo "<p>✅ <strong>sendmail.ini looks good</strong> - Try testing email sending</p>";
    }
}

$sendmail_path = ini_get('sendmail_path');
if (empty($sendmail_path) || strpos($sendmail_path, 'sendmail.exe') === false) {
    echo "<p>⚠️ <strong>php.ini sendmail_path not set</strong> - Configure php.ini</p>";
} else {
    echo "<p>✅ <strong>php.ini sendmail_path configured</strong></p>";
}

echo "</div>";

echo "<hr>";
echo "<p><a href='configure_sendmail.php' style='background:#007bff;color:white;padding:10px 15px;text-decoration:none;'>🔧 Auto-Configure Sendmail</a></p>";
echo "<p><a href='test_real_email_final.php' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;'>🧪 Test Email System</a></p>";
?>
