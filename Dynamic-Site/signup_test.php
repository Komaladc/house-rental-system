<?php
// Simplified Enhanced Signup - Minimal Version
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head><title>Enhanced Signup Test</title></head><body>";
echo "<h1>🧪 Enhanced Signup Test Page</h1>";

// Basic checks
echo "<h2>System Status:</h2>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
echo "<p>✅ Server Time: " . date('Y-m-d H:i:s') . "</p>";

// Check if files exist
$required_files = [
    'config/timezone.php',
    'lib/Database.php',
    'classes/PreRegistrationVerification.php',
    'classes/EmailOTP.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

echo "<h2>Available Signup Options:</h2>";
echo "<ul>";
echo "<li><a href='signup.php'>Original Signup Page</a></li>";
echo "<li><a href='signup_fixed.php'>Fixed Signup Page</a></li>";
echo "<li><a href='signup_dynamic.php'>Dynamic Signup Page</a></li>";
echo "<li><a href='signup_with_verification.php'>Signup with Verification</a></li>";
echo "</ul>";

echo "<h2>For Dipesh Tamang Issue:</h2>";
echo "<p>Since you're having trouble with Dipesh not showing in verify users:</p>";
echo "<ul>";
echo "<li><a href='Admin/verify_users.php'>Go to Verify Users Page</a></li>";
echo "<li><a href='Admin/owner_list.php'>Go to Manage Users Page</a></li>";
echo "</ul>";

echo "</body></html>";
?>
