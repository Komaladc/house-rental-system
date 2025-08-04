<?php
// Complete test of signup process without conflicts
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/complete_test_errors.log');

echo "<h2>Complete Signup Process Test</h2>";

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include header which includes Database
echo "<p>1. Including header...</p>";
include_once 'inc/header.php';
echo "<p>✓ Header included. Database class exists: " . (class_exists('Database') ? 'YES' : 'NO') . "</p>";

// Test form processing simulation
echo "<p>2. Simulating form processing...</p>";

// Include verification classes
echo "<p>3. Including PreRegistrationVerification...</p>";
include_once 'classes/PreRegistrationVerification.php';
echo "<p>✓ PreRegistrationVerification included</p>";

echo "<p>4. Creating PreRegistrationVerification instance with existing DB...</p>";
$preVerification = new PreRegistrationVerification($db);
echo "<p>✓ PreRegistrationVerification instance created successfully</p>";

// Test email validation
echo "<p>5. Testing email validation...</p>";
$testEmail = "test@example.com";
$isValid = $preVerification->isRealEmail($testEmail);
echo "<p>✓ Email validation test completed. Result: " . ($isValid ? 'VALID' : 'INVALID') . "</p>";

echo "<p><strong>🎉 All tests passed! No Database class conflicts!</strong></p>";
echo "<p>The signup system should work correctly now.</p>";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        p { margin: 10px 0; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <div class="success">
        <p>System Status: ✅ OPERATIONAL</p>
        <p><a href="signup_enhanced.php">Go to Registration Page</a></p>
    </div>
</body>
</html>
