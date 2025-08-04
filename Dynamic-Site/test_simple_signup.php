<?php
// Simple Signup Test - Minimal version to test if basic functionality works
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Simple Signup Test</title>";
echo "</head>";
echo "<body>";

echo "<h2>🧪 Simple Signup Test</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    // Test basic includes one by one
    echo "<p>1. Starting session...</p>";
    session_start();
    echo "<p style='color: green;'>✅ Session started</p>";
    
    echo "<p>2. Testing Database connection...</p>";
    include "lib/Database.php";
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    echo "<p>3. Testing timezone config...</p>";
    include "config/timezone.php";
    echo "<p style='color: green;'>✅ Timezone config loaded</p>";
    
    echo "<p>4. Testing PreRegistrationVerification class...</p>";
    include "classes/PreRegistrationVerification.php";
    $preReg = new PreRegistrationVerification();
    echo "<p style='color: green;'>✅ PreRegistrationVerification class loaded</p>";
    
    echo "<p>5. Testing EmailOTP class...</p>";
    include "classes/EmailOTP.php";
    echo "<p style='color: green;'>✅ EmailOTP class loaded</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ All Core Components Working!</h3>";
    echo "<p>The issue might be in the HTML/CSS part of signup_enhanced.php</p>";
    echo "</div>";
    
    // Simple form test
    echo "<h3>📝 Simple Registration Form</h3>";
    echo "<form method='POST' style='background: white; padding: 20px; border-radius: 8px;'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>First Name:</label><br>";
    echo "<input type='text' name='fname' required style='width: 100%; padding: 8px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Last Name:</label><br>";
    echo "<input type='text' name='lname' required style='width: 100%; padding: 8px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Email:</label><br>";
    echo "<input type='email' name='email' required style='width: 100%; padding: 8px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Account Type:</label><br>";
    echo "<select name='level' required style='width: 100%; padding: 8px;'>";
    echo "<option value='1'>Property Seeker</option>";
    echo "<option value='2'>Property Owner</option>";
    echo "<option value='3'>Real Estate Agent</option>";
    echo "</select>";
    echo "</div>";
    echo "<button type='submit' name='test_signup' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Test Registration</button>";
    echo "</form>";
    
    // Handle test form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_signup'])) {
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📊 Form Data Received:</h4>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error Found:</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre><strong>Stack Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<div style='margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 5px;'>";
echo "<h4>🔧 Debug Actions</h4>";
echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Try Original Signup</a>";
echo "<a href='debug_signup_error.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug Components</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
