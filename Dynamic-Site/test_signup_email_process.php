<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Signup Form Email Test</h2>";

// Load required classes
require_once 'config/config.php';
require_once 'classes/PreRegistrationVerification.php';
require_once 'classes/EmailOTP.php';

// Load Gmail config
include_once 'config/gmail_config.php';

echo "<h3>📋 Configuration Status:</h3>";
echo "<p><strong>Test Mode:</strong> " . (defined('EMAIL_TEST_MODE') ? (EMAIL_TEST_MODE ? 'ON (test)' : 'OFF (real)') : 'Unknown') . "</p>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_signup_email'])) {
    echo "<h3>🧪 Testing Signup Email Process:</h3>";
    
    // Simulate registration data like the real form
    $registrationData = [
        'fname' => $_POST['fname'] ?? 'Test',
        'lname' => $_POST['lname'] ?? 'User',
        'username' => $_POST['username'] ?? 'testuser',
        'email' => $_POST['email'] ?? 'bistak297@gmail.com',
        'cellno' => $_POST['cellno'] ?? '+977-9876543210',
        'address' => $_POST['address'] ?? 'Kathmandu, Nepal',
        'password' => 'testpassword123',
        'level' => '3'
    ];
    
    echo "<p><strong>Testing with email:</strong> " . htmlspecialchars($registrationData['email']) . "</p>";
    
    try {
        // Use the same process as the real signup form
        $preVerification = new PreRegistrationVerification();
        $result = $preVerification->initiateEmailVerification($registrationData);
        
        echo "<h4>📧 Email Sending Result:</h4>";
        if ($result['success']) {
            echo "<p style='color:green;font-size:18px;'>🎉 <strong>SUCCESS!</strong></p>";
            echo "<p>✅ " . htmlspecialchars($result['message']) . "</p>";
            
            if (defined('EMAIL_TEST_MODE') && EMAIL_TEST_MODE) {
                echo "<p>📝 <strong>TEST MODE:</strong> Check email logs for OTP</p>";
            } else {
                echo "<p>📧 <strong>REAL MODE:</strong> Check your email inbox!</p>";
            }
        } else {
            echo "<p style='color:red;font-size:18px;'>❌ <strong>FAILED!</strong></p>";
            echo "<p>❌ " . htmlspecialchars($result['message']) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ <strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre style='background:#f8d7da;padding:10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
    // Show recent logs
    echo "<h4>📝 Recent Email Logs:</h4>";
    
    if (file_exists('real_email_log.txt')) {
        $log = file_get_contents('real_email_log.txt');
        $lines = explode("\n", $log);
        $recent = array_slice($lines, -15);
        if (!empty(trim(implode('', $recent)))) {
            echo "<h5>Real Email Log:</h5>";
            echo "<pre style='background:#f0f0f0;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars(implode("\n", $recent)) . "</pre>";
        }
    }
    
    if (file_exists('email_log.txt')) {
        $log = file_get_contents('email_log.txt');
        $lines = explode("\n", $log);
        $recent = array_slice($lines, -15);
        if (!empty(trim(implode('', $recent)))) {
            echo "<h5>General Email Log:</h5>";
            echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow-y:scroll;'>" . htmlspecialchars(implode("\n", $recent)) . "</pre>";
        }
    }
    
    echo "<hr>";
}

?>

<div style="max-width:500px;margin:20px auto;padding:20px;border:1px solid #ddd;">
    <h3>🧪 Test Signup Email Process</h3>
    <p>This tests the exact same email sending process used by the signup form.</p>
    
    <form method="POST">
        <table style="width:100%;">
            <tr>
                <td><label>First Name:</label></td>
                <td><input type="text" name="fname" value="Test" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td><label>Last Name:</label></td>
                <td><input type="text" name="lname" value="User" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td><label>Username:</label></td>
                <td><input type="text" name="username" value="testuser" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td><label>Email:</label></td>
                <td><input type="email" name="email" value="bistak297@gmail.com" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td><label>Phone:</label></td>
                <td><input type="tel" name="cellno" value="+977-9876543210" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td><label>Address:</label></td>
                <td><input type="text" name="address" value="Kathmandu, Nepal" required style="width:100%;padding:5px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;padding-top:15px;">
                    <button type="submit" name="test_signup_email" style="background:#007bff;color:white;padding:10px 20px;border:none;font-size:16px;">
                        🧪 Test Signup Email Sending
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>

<hr>
<p><a href="signup_with_verification.php">← Real Signup Form</a></p>
<p><a href="diagnose_sendmail.php">← Sendmail Diagnosis</a></p>
<p><a href="test_real_email_final.php">← Email System Test</a></p>

<?php
// Show current email service status
echo "<h3>🔍 Email Service Debug Info:</h3>";

try {
    $emailOTP = new EmailOTP();
    
    // Check which email service is being used
    echo "<p><strong>Email Service Status:</strong></p>";
    echo "<p>Test Mode: " . (defined('EMAIL_TEST_MODE') ? (EMAIL_TEST_MODE ? 'ON' : 'OFF') : 'Unknown') . "</p>";
    
    // Check if real email service is configured
    if (class_exists('RealEmailService')) {
        $realEmailService = new RealEmailService();
        $configStatus = $realEmailService->getConfigurationStatus();
        echo "<p>Gmail Configuration: " . ($configStatus['configured'] ? 'Configured' : 'Not Configured') . "</p>";
        echo "<p>Gmail Password Set: " . ($configStatus['gmail_pass_set'] ? 'Yes' : 'No') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error checking email service: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
