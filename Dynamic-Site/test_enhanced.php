<?php
// Test version of signup_enhanced.php with error checking
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🧪 Testing Enhanced Signup Components</h2>";

try {
    echo "<h3>1. Loading Timezone Config</h3>";
    include "config/timezone.php";
    echo "<p style='color: green;'>✅ Timezone loaded successfully</p>";
    echo "<p>Current time: " . date('Y-m-d H:i:s T') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Timezone error: " . $e->getMessage() . "</p>";
}

try {
    echo "<h3>2. Starting Session</h3>";
    session_start();
    echo "<p style='color: green;'>✅ Session started successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Session error: " . $e->getMessage() . "</p>";
}

try {
    echo "<h3>3. Loading Database Class</h3>";
    include "lib/Database.php";
    $db = new Database();
    echo "<p style='color: green;'>✅ Database class loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

try {
    echo "<h3>4. Loading PreRegistrationVerification Class</h3>";
    include "classes/PreRegistrationVerification.php";
    echo "<p style='color: green;'>✅ PreRegistrationVerification class loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ PreRegistrationVerification error: " . $e->getMessage() . "</p>";
}

try {
    echo "<h3>5. Loading EmailOTP Class</h3>";
    include "classes/EmailOTP.php";
    echo "<p style='color: green;'>✅ EmailOTP class loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EmailOTP error: " . $e->getMessage() . "</p>";
}

try {
    echo "<h3>6. Instantiating Classes</h3>";
    $preReg = new PreRegistrationVerification();
    echo "<p style='color: green;'>✅ PreRegistrationVerification instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Instantiation error: " . $e->getMessage() . "</p>";
}

echo "<h3>7. Next Steps</h3>";
echo "<p>If all tests passed, the enhanced signup should work.</p>";
echo "<p><a href='signup_enhanced.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Enhanced Signup</a></p>";
echo "<p><a href='signup.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Use Original Signup</a></p>";
?>
