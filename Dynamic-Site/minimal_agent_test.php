<?php
// Minimal agent signup test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Minimal Agent Signup Test</h1>";

// Simulate the exact parameters that would come from index.php
$_GET['account_type'] = 'agent';

echo "<p>Account Type: " . $_GET['account_type'] . "</p>";

// Test direct access to signup_enhanced.php logic
echo "<h2>Testing signup_enhanced.php includes:</h2>";

// Test the exact includes from signup_enhanced.php
try {
    include "config/timezone.php";
    echo "<p>✓ Timezone included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Timezone error: " . $e->getMessage() . "</p>";
}

try {
    include "lib/Database.php";
    echo "<p>✓ Database included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

try {
    include "classes/PreRegistrationVerification.php";
    echo "<p>✓ PreRegistrationVerification included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ PreRegistrationVerification error: " . $e->getMessage() . "</p>";
}

try {
    include "classes/EmailOTP.php";
    echo "<p>✓ EmailOTP included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ EmailOTP error: " . $e->getMessage() . "</p>";
}

// Test database and class instantiation
echo "<h2>Testing class instantiation:</h2>";
try {
    global $db;
    $db = new Database();
    echo "<p>✓ Database instantiated</p>";
    
    $preReg = new PreRegistrationVerification();
    echo "<p>✓ PreRegistrationVerification instantiated</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Instantiation error: " . $e->getMessage() . "</p>";
}

echo "<h2>Test complete - no fatal errors found</h2>";
?>

<p><a href="signup_enhanced.php?account_type=agent">Now try the real signup_enhanced.php with agent parameter</a></p>
