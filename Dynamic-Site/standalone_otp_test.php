<?php
// Security check - only allow admin access
if(!defined('ADMIN_DEBUG_ACCESS') && !isset($_SESSION['admin_debug'])) {
    die('Access denied. Debug files are restricted.');
}

// Standalone OTP test - minimal dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kathmandu');

// Basic database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_rental';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>🔧 Standalone OTP Test</h2>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . " (Nepal Time)</p>";

// Function to log and display
function logAndDisplay($message, $isError = false) {
    $style = $isError ? "background: #f8d7da; color: #721c24;" : "background: #d4edda; color: #155724;";
    echo "<div style='padding: 10px; margin: 5px 0; border-radius: 5px; $style'>$message</div>";
    error_log("OTP_TEST: $message");
}

if ($_POST && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testOTP = sprintf("%06d", mt_rand(100000, 999999));
    
    logAndDisplay("🧪 Testing OTP creation for: $testEmail");
    logAndDisplay("📱 Generated OTP: $testOTP");
    
    // Step 1: Delete old OTPs
    $deleteQuery = "DELETE FROM tbl_otp WHERE email = ? AND purpose = 'email_verification'";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $testEmail);
    $deleteResult = $deleteStmt->execute();
    
    logAndDisplay("🗑️ Old OTPs deleted: " . ($deleteResult ? "SUCCESS" : "FAILED"));
    if (!$deleteResult) {
        logAndDisplay("Delete Error: " . $conn->error, true);
    }
    
    // Step 2: Insert new OTP
    $currentTime = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+20 minutes'));
    
    $insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) VALUES (?, ?, 'email_verification', ?, ?, 0)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ssss", $testEmail, $testOTP, $currentTime, $expiresAt);
    $insertResult = $insertStmt->execute();
    
    logAndDisplay("📝 OTP inserted: " . ($insertResult ? "✅ SUCCESS" : "❌ FAILED"));
    if (!$insertResult) {
        logAndDisplay("Insert Error: " . $conn->error, true);
    } else {
        logAndDisplay("📋 Insert ID: " . $conn->insert_id);
    }
    
    // Step 3: Verify insertion
    $verifyQuery = "SELECT * FROM tbl_otp WHERE email = ? AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("s", $testEmail);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows > 0) {
        $otpData = $verifyResult->fetch_assoc();
        logAndDisplay("✅ OTP found in database:");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; background: white;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($otpData as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        // Step 4: Test verification logic
        $now = date('Y-m-d H:i:s');
        if ($otpData['otp'] === $testOTP && $otpData['expires_at'] > $now && $otpData['is_used'] == 0) {
            logAndDisplay("🎯 Verification test: ✅ SUCCESS - OTP would be accepted");
        } else {
            logAndDisplay("🎯 Verification test: ❌ FAILED", true);
            logAndDisplay("OTP match: " . ($otpData['otp'] === $testOTP ? 'YES' : 'NO'), true);
            logAndDisplay("Not expired: " . ($otpData['expires_at'] > $now ? 'YES' : 'NO'), true);
            logAndDisplay("Not used: " . ($otpData['is_used'] == 0 ? 'YES' : 'NO'), true);
        }
        
    } else {
        logAndDisplay("❌ OTP NOT found in database after insertion!", true);
    }
}

// Show table structure
echo "<h3>📋 tbl_otp Table Structure</h3>";
$structureResult = $conn->query("DESCRIBE tbl_otp");
if ($structureResult) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white;'>";
    echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($column = $structureResult->fetch_assoc()) {
        echo "<tr>";
        foreach ($column as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px;'>Error getting table structure: " . $conn->error . "</div>";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { margin: 15px 0; font-size: 14px; }
    th, td { padding: 8px 12px; text-align: left; }
    form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
    input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
    button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>

<form method="POST">
    <h4>🧪 Test OTP Creation</h4>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit">🔧 Create and Test OTP</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
    <h4>🎯 Purpose:</h4>
    <p>This standalone test will:</p>
    <ol>
        <li>Show the exact tbl_otp table structure</li>
        <li>Test OTP creation with prepared statements</li>
        <li>Verify the OTP was actually inserted</li>
        <li>Test the verification logic step by step</li>
    </ol>
</div>
