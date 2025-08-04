<?php
// Simple diagnostic page to check if PHP is working
echo "<h2>🔍 Server Diagnostic Check</h2>";

echo "<h3>1. PHP Version</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h3>2. Current Time</h3>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";

echo "<h3>3. Include Path Test</h3>";
$files_to_check = [
    "config/timezone.php",
    "lib/Database.php", 
    "classes/PreRegistrationVerification.php",
    "classes/EmailOTP.php"
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - NOT FOUND</p>";
    }
}

echo "<h3>4. Database Connection Test</h3>";
try {
    include "lib/Database.php";
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h3>5. Class Loading Test</h3>";
try {
    if (class_exists('Database')) {
        echo "<p style='color: green;'>✅ Database class loaded</p>";
    } else {
        echo "<p style='color: red;'>❌ Database class not loaded</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Class loading error: " . $e->getMessage() . "</p>";
}

echo "<h3>6. Directory Permissions</h3>";
$uploadDir = "uploads/documents/";
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✅ Upload directory exists: $uploadDir</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✅ Upload directory is writable</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Upload directory is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Upload directory does not exist: $uploadDir</p>";
}

echo "<h3>7. Available Signup Pages</h3>";
$signup_files = glob("signup*.php");
foreach ($signup_files as $file) {
    echo "<p><a href='$file'>$file</a></p>";
}

echo "<p><strong>Recommended:</strong> <a href='signup.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Original Signup Page</a></p>";
?>
