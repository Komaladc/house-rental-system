<?php
// Test PHP Error Detection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 PHP Error Detection Test</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    echo "<p style='color: green;'>✅ Basic PHP execution working</p>";
    
    // Test including the signup file components
    echo "<h3>1. Testing Include Files</h3>";
    
    // Test timezone config
    if (file_exists("config/timezone.php")) {
        echo "<p style='color: green;'>✅ config/timezone.php exists</p>";
        try {
            include_once "config/timezone.php";
            echo "<p style='color: green;'>✅ timezone.php included successfully</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error including timezone.php: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ config/timezone.php not found</p>";
    }
    
    // Test database
    if (file_exists("lib/Database.php")) {
        echo "<p style='color: green;'>✅ lib/Database.php exists</p>";
        try {
            include_once "lib/Database.php";
            $db = new Database();
            echo "<p style='color: green;'>✅ Database connection successful</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ lib/Database.php not found</p>";
    }
    
    // Test PreRegistrationVerification class
    if (file_exists("classes/PreRegistrationVerification.php")) {
        echo "<p style='color: green;'>✅ classes/PreRegistrationVerification.php exists</p>";
        try {
            include_once "classes/PreRegistrationVerification.php";
            echo "<p style='color: green;'>✅ PreRegistrationVerification class included</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error including PreRegistrationVerification: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ classes/PreRegistrationVerification.php not found</p>";
    }
    
    // Test EmailOTP class
    if (file_exists("classes/EmailOTP.php")) {
        echo "<p style='color: green;'>✅ classes/EmailOTP.php exists</p>";
        try {
            include_once "classes/EmailOTP.php";
            echo "<p style='color: green;'>✅ EmailOTP class included</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error including EmailOTP: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ classes/EmailOTP.php not found</p>";
    }
    
    // Test session start
    echo "<h3>2. Testing Session</h3>";
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        echo "<p style='color: green;'>✅ Session started successfully</p>";
    } else {
        echo "<p style='color: green;'>✅ Session already active</p>";
    }
    
    // Test if we can create the registration object
    echo "<h3>3. Testing Registration Objects</h3>";
    try {
        $preReg = new PreRegistrationVerification();
        echo "<p style='color: green;'>✅ PreRegistrationVerification object created</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating PreRegistrationVerification: " . $e->getMessage() . "</p>";
    }
    
    // Check PHP version and extensions
    echo "<h3>4. PHP Environment</h3>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Extensions:</strong></p>";
    echo "<ul>";
    echo "<li>mysqli: " . (extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded') . "</li>";
    echo "<li>curl: " . (extension_loaded('curl') ? '✅ Loaded' : '❌ Not loaded') . "</li>";
    echo "<li>json: " . (extension_loaded('json') ? '✅ Loaded' : '❌ Not loaded') . "</li>";
    echo "<li>mbstring: " . (extension_loaded('mbstring') ? '✅ Loaded' : '❌ Not loaded') . "</li>";
    echo "</ul>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px;'>";
    echo "<h4>🚀 Next Steps</h4>";
    echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Try Signup Page</a>";
    echo "<a href='test_simple_signup.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📝 Simple Signup Test</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Fatal error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";
?>
