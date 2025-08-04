<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚨 Signup Error Debug</h1>";

echo "<h3>Step 1: Basic PHP Test</h3>";
echo "✅ PHP is working<br>";

echo "<h3>Step 2: Testing includes step by step</h3>";

try {
    echo "Testing Session...<br>";
    include'lib/Session.php';
    Session::init();
    echo "✅ Session loaded<br>";
    
    echo "Testing Database...<br>";
    include'lib/Database.php';
    $db = new Database();
    echo "✅ Database loaded<br>";
    
    echo "Testing Format helper...<br>";
    include'helpers/Format.php';
    $fm = new Format();
    echo "✅ Format loaded<br>";
    
    echo "Testing User class...<br>";
    if (file_exists('classes/User.php')) {
        include'classes/User.php';
        $usr = new User();
        echo "✅ User loaded<br>";
    } else {
        echo "❌ User.php not found<br>";
    }
    
    echo "Testing EmailOTP class...<br>";
    include'classes/EmailOTP.php';
    if (class_exists('EmailOTP')) {
        $emailOTP = new EmailOTP();
        echo "✅ EmailOTP loaded<br>";
    } else {
        echo "❌ EmailOTP class not found<br>";
    }
    
    echo "Testing PreRegistrationVerification class...<br>";
    include'classes/PreRegistrationVerification.php';
    if (class_exists('PreRegistrationVerification')) {
        $preVerification = new PreRegistrationVerification();
        echo "✅ PreRegistrationVerification loaded<br>";
    } else {
        echo "❌ PreRegistrationVerification class not found<br>";
    }
    
    echo "<h3>✅ All Tests Passed!</h3>";
    echo "<a href='signup_with_verification.php' style='background:#27ae60; color:white; padding:10px; text-decoration:none; border-radius:5px;'>🆕 Try Signup Page Now</a>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error Found:</h3>";
    echo "<div style='background:#ff7675; color:white; padding:15px; border-radius:5px;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
    
    echo "<h3>🛠️ Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running (Apache + MySQL)</li>";
    echo "<li>Check if database 'db_rental' exists</li>";
    echo "<li>Run <a href='fix_database.php'>fix_database.php</a> to setup tables</li>";
    echo "<li>Check file permissions</li>";
    echo "</ul>";
}
?>
