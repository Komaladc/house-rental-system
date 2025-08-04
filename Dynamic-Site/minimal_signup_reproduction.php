<?php
// Minimal signup reproduction test
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/minimal_test_errors.log');

echo "<h2>Minimal Signup Reproduction Test</h2>";

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include header (which includes Database class)
echo "<p>Including header...</p>";
include 'inc/header.php';
echo "<p>✓ Header included successfully</p>";

// Test POST processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>Processing POST Request</h3>";
    
    // Include verification class
    echo "<p>Including PreRegistrationVerification...</p>";
    include_once 'classes/PreRegistrationVerification.php';
    echo "<p>✓ PreRegistrationVerification included</p>";
    
    echo "<p>Creating instance...</p>";
    $preVerification = new PreRegistrationVerification();
    echo "<p>✓ Instance created successfully</p>";
    
    echo "<p><strong>POST processing completed without errors!</strong></p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Minimal Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>

<?php if ($_SERVER['REQUEST_METHOD'] != 'POST'): ?>
<form method="POST">
    <h3>Test Form Submission</h3>
    <input type="text" name="test_field" value="test" />
    <button type="submit">Submit Test</button>
</form>
<?php endif; ?>

</body>
</html>
