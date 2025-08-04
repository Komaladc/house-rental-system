<?php
// Minimal Signup Test - Test the exact includes and logic from signup_enhanced.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Signup Debug</title></head><body>";
echo "<h2>🔧 Signup Debug Test</h2>";

try {
    echo "<p>1. Testing includes in order...</p>";
    
    // Test each include exactly as in signup_enhanced.php
    echo "<p>1a. Setting timezone...</p>";
    include "config/timezone.php";
    echo "<p style='color: green;'>✅ Timezone included</p>";
    
    echo "<p>1b. Starting session...</p>";
    session_start();
    echo "<p style='color: green;'>✅ Session started</p>";
    
    echo "<p>1c. Including Database...</p>";
    include "lib/Database.php";
    echo "<p style='color: green;'>✅ Database included</p>";
    
    echo "<p>1d. Including PreRegistrationVerification...</p>";
    include "classes/PreRegistrationVerification.php";
    echo "<p style='color: green;'>✅ PreRegistrationVerification included</p>";
    
    echo "<p>1e. Including EmailOTP...</p>";
    include "classes/EmailOTP.php";
    echo "<p style='color: green;'>✅ EmailOTP included</p>";
    
    echo "<p>2. Creating global database connection...</p>";
    global $db;
    $db = new Database();
    echo "<p style='color: green;'>✅ Global DB created</p>";
    
    echo "<p>3. Creating PreRegistrationVerification object...</p>";
    $preReg = new PreRegistrationVerification();
    echo "<p style='color: green;'>✅ PreReg object created</p>";
    
    echo "<p>4. Initializing variables...</p>";
    $registrationMsg = "";
    $showForm = true;
    $showOtpForm = false;
    echo "<p style='color: green;'>✅ Variables initialized</p>";
    
    echo "<p>5. Testing POST check...</p>";
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        echo "<p style='color: blue;'>📝 POST request detected</p>";
        if (isset($_POST['signup'])) {
            echo "<p style='color: blue;'>📝 Signup form submitted</p>";
        }
        if (isset($_POST['verify_otp'])) {
            echo "<p style='color: blue;'>📝 OTP verification submitted</p>";
        }
    } else {
        echo "<p style='color: gray;'>📄 GET request (normal page load)</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ All Core Logic Working!</h3>";
    echo "<p>The signup_enhanced.php should work. The issue might be:</p>";
    echo "<ul>";
    echo "<li>XAMPP services not running</li>";
    echo "<li>File permissions</li>";
    echo "<li>Browser cache</li>";
    echo "<li>A runtime error in form processing</li>";
    echo "</ul>";
    echo "</div>";
    
    // Simple test form
    echo "<h3>📝 Test Form</h3>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='test' value='1'>";
    echo "<button type='submit'>Test POST</button>";
    echo "</form>";
    
    if (isset($_POST['test'])) {
        echo "<p style='color: green;'>✅ Form submission works!</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Error Found:</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Fatal Error Found:</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='signup_enhanced.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Try Signup Page Again</a>";
echo "</div>";

echo "</body></html>";
?>
