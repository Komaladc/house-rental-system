<?php
echo "<h1>Agent Signup Debug</h1>";

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Step 1: Testing Agent Parameter</h2>";
$_GET['account_type'] = 'agent';
echo "<p>Account type set to: " . $_GET['account_type'] . "</p>";

echo "<h2>Step 2: Including Config</h2>";
try {
    include_once __DIR__ . '/config/config.php';
    echo "<p>✓ Config included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Config error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 3: Including PreRegistrationVerification</h2>";
try {
    include_once __DIR__ . '/classes/PreRegistrationVerification.php';
    echo "<p>✓ PreRegistrationVerification included</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>PreRegistrationVerification error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 4: Creating PreRegistration Instance</h2>";
try {
    $preReg = new PreRegistrationVerification();
    echo "<p>✓ PreRegistrationVerification instance created</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Instance creation error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 5: Testing Session</h2>";
session_start();
echo "<p>✓ Session started</p>";

echo "<h2>Step 6: Testing Basic HTML Output</h2>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agent Signup Test</title>
</head>
<body>
    <h3>Basic HTML Test for Agent Signup</h3>
    <p>Account Type: <?php echo isset($_GET['account_type']) ? $_GET['account_type'] : 'Not set'; ?></p>
    
    <form method="POST" action="">
        <label>Full Name:</label>
        <input type="text" name="full_name" required>
        <br><br>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>
        
        <label>Phone:</label>
        <input type="tel" name="phone" required>
        <br><br>
        
        <!-- Agent-specific fields -->
        <label>Citizenship Document:</label>
        <input type="file" name="citizenship_doc" accept=".pdf,.jpg,.jpeg,.png">
        <br><br>
        
        <input type="submit" value="Test Submit">
    </form>
</body>
</html>
<?php
echo "<h2>Step 7: Complete</h2>";
echo "<p>If you see this, the agent signup components are working.</p>";
?>
