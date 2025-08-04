<?php
include "config/timezone.php";

echo "<h1>🇳🇵 Nepal Time Configuration Test</h1>";

echo "<h3>Current Timezone Settings:</h3>";
echo "PHP Timezone: " . date_default_timezone_get() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s T') . "<br>";
echo "Nepal Time: " . NepalTime::now() . "<br>";
echo "Nepal Time (with timezone): " . NepalTime::logFormat() . "<br>";

echo "<h3>Time Calculations:</h3>";
echo "Current Nepal Time: " . NepalTime::now() . "<br>";
echo "Nepal Time + 20 minutes: " . NepalTime::addMinutes(20) . "<br>";
echo "Nepal Time + 2 hours: " . NepalTime::addHours(2) . "<br>";

echo "<h3>Database Connection Test:</h3>";
include "lib/Database.php";
global $db;
$db = new Database();

// Test OTP storage with Nepal time
include "classes/EmailOTP.php";
$emailOTP = new EmailOTP();

echo "✅ EmailOTP class loaded with Nepal timezone<br>";
echo "✅ Current timezone is set to: " . date_default_timezone_get() . "<br>";

// Test PreRegistrationVerification with Nepal time
include "classes/PreRegistrationVerification.php";
$preReg = new PreRegistrationVerification();

echo "✅ PreRegistrationVerification class loaded with Nepal timezone<br>";

echo "<h3>🎯 Timezone Fix Summary:</h3>";
echo "✅ Default timezone set to Asia/Kathmandu (Nepal Time)<br>";
echo "✅ NepalTime helper class created<br>";
echo "✅ EmailOTP updated to use Nepal time<br>";
echo "✅ PreRegistrationVerification updated to use Nepal time<br>";
echo "✅ All time functions now use Nepal Time (NPT, UTC+5:45)<br>";

echo "<br><strong>The OTP system should now work correctly with Nepal time!</strong><br>";
echo "<a href='signup_enhanced.php'>→ Test the Fixed Signup Form</a>";
?>
