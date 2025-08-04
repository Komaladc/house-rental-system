<?php
echo "<h1>Signup Debug Test</h1>";

// Test 1: Basic PHP
echo "<h2>1. Basic PHP Test</h2>";
echo "<p>✓ PHP is working</p>";

// Test 2: Session
echo "<h2>2. Session Test</h2>";
session_start();
echo "<p>✓ Session started</p>";

// Test 3: File paths
echo "<h2>3. File Path Test</h2>";
$config_path = __DIR__ . '/config/config.php';
$preregistration_path = __DIR__ . '/classes/PreRegistrationVerification.php';

echo "<p>Config path: " . $config_path . "</p>";
echo "<p>Config exists: " . (file_exists($config_path) ? "✓ Yes" : "✗ No") . "</p>";

echo "<p>PreRegistration path: " . $preregistration_path . "</p>";
echo "<p>PreRegistration exists: " . (file_exists($preregistration_path) ? "✓ Yes" : "✗ No") . "</p>";

// Test 4: Include config
echo "<h2>4. Config Include Test</h2>";
try {
    include_once $config_path;
    echo "<p>✓ Config included successfully</p>";
    echo "<p>DB Host: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Config error: " . $e->getMessage() . "</p>";
}

// Test 5: Include PreRegistrationVerification
echo "<h2>5. PreRegistrationVerification Include Test</h2>";
try {
    include_once $preregistration_path;
    echo "<p>✓ PreRegistrationVerification included successfully</p>";
    
    // Test class instantiation
    try {
        $preReg = new PreRegistrationVerification();
        echo "<p>✓ PreRegistrationVerification class instantiated successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Class instantiation error: " . $e->getMessage() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ PreRegistrationVerification include error: " . $e->getMessage() . "</p>";
}

// Test 6: Memory and error reporting
echo "<h2>6. System Info</h2>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max execution time: " . ini_get('max_execution_time') . "</p>";
echo "<p>Error reporting: " . error_reporting() . "</p>";
echo "<p>Display errors: " . ini_get('display_errors') . "</p>";

// Test 7: POST data simulation
echo "<h2>7. POST Simulation Test</h2>";
$_POST['account_type'] = 'agent';
echo "<p>Simulated POST account_type: " . $_POST['account_type'] . "</p>";

echo "<h2>Test Complete</h2>";
echo "<p>If you see this message, PHP is working properly.</p>";
?>
