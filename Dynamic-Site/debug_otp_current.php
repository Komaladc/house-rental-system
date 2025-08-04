<?php
// Security check - only allow admin access
if(!defined('ADMIN_DEBUG_ACCESS') && !isset($_SESSION['admin_debug'])) {
    die('Access denied. Debug files are restricted.');
}

// Set Nepal timezone first
include "config/timezone.php";

session_start();
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

// Create database connection
$db = new Database();
$preReg = new PreRegistrationVerification();

echo "<h1>🔍 OTP Debug Test</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    
    echo "<h2>Testing OTP: $otp for Email: $email</h2>";
    
    // Step 1: Check what's in the database
    echo "<h3>Step 1: Database Check</h3>";
    
    $currentTime = NepalTime::now();
    echo "Current Nepal Time: $currentTime<br>";
    
    // Check tbl_otp
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC LIMIT 3";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        echo "<strong>OTPs found in tbl_otp:</strong><br>";
        while ($row = $otpResult->fetch_assoc()) {
            $isExpired = $row['expires_at'] < $currentTime ? 'EXPIRED' : 'VALID';
            $isUsed = $row['is_used'] ? 'USED' : 'UNUSED';
            echo "- OTP: {$row['otp']}, Status: $isUsed, Expiry: $isExpired ({$row['expires_at']}), Purpose: {$row['purpose']}<br>";
        }
    } else {
        echo "❌ No OTPs found in tbl_otp for $email<br>";
    }
    
    // Check tbl_pending_verification
    echo "<br>";
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC LIMIT 1";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult && $pendingResult->num_rows > 0) {
        $pendingData = $pendingResult->fetch_assoc();
        $isExpired = $pendingData['expires_at'] < $currentTime ? 'EXPIRED' : 'VALID';
        echo "<strong>Pending verification found:</strong><br>";
        echo "- OTP: {$pendingData['otp']}, Status: $isExpired ({$pendingData['expires_at']})<br>";
        echo "- Token: " . substr($pendingData['verification_token'], 0, 20) . "...<br>";
        echo "- Is Verified: " . ($pendingData['is_verified'] ? 'YES' : 'NO') . "<br>";
    } else {
        echo "❌ No pending verification found for $email<br>";
    }
    
    // Step 2: Test OTP verification
    echo "<h3>Step 2: OTP Verification Test</h3>";
    $emailOTP = new EmailOTP();
    $otpVerified = $emailOTP->verifyOTP($email, $otp, 'registration');
    echo "OTP Verification Result: " . ($otpVerified ? '✅ SUCCESS' : '❌ FAILED') . "<br>";
    
    // Step 3: Test account creation if OTP is valid
    if ($otpVerified) {
        echo "<h3>Step 3: Account Creation Test</h3>";
        $accountResult = $preReg->verifyOTPAndCreateAccount($email, $otp);
        
        if ($accountResult['success']) {
            echo "✅ <strong>Account Creation: SUCCESS</strong><br>";
            echo strip_tags($accountResult['message']) . "<br>";
        } else {
            echo "❌ <strong>Account Creation: FAILED</strong><br>";
            echo strip_tags($accountResult['message']) . "<br>";
        }
    }
    
} else {
    echo "<p>Enter the email and OTP to debug the verification process:</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2, h3 { color: #333; }
        form { background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input { padding: 10px; margin: 5px; width: 300px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; }
    </style>
</head>
<body>
    <form method="POST">
        <div>
            <label>Email:</label><br>
            <input type="email" name="email" required placeholder="Enter the email used for registration">
        </div>
        
        <div>
            <label>OTP Code:</label><br>
            <input type="text" name="otp" maxlength="6" required placeholder="Enter the 6-digit OTP">
        </div>
        
        <button type="submit">🔍 Debug OTP Verification</button>
    </form>
    
    <p><a href="signup_enhanced.php">← Back to Signup Form</a></p>
</body>
</html>
