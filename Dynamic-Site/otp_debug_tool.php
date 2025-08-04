<?php
require_once 'lib/Database.php';

$db = new Database();
$email = "thekomalad@gmail.com";

echo "=== OTP Debug Tool for $email ===<br><br>";

// Check if we have any pending verification
$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
$result = $db->select($pendingQuery);

if ($result && $result->num_rows > 0) {
    $pending = $result->fetch_assoc();
    echo "<strong>✅ Pending Verification Found:</strong><br>";
    echo "Email: {$pending['email']}<br>";
    echo "OTP: <span style='font-size:20px; color:blue; font-weight:bold;'>{$pending['otp']}</span><br>";
    echo "Token: {$pending['verification_token']}<br>";
    echo "Expires: {$pending['expires_at']}<br>";
    echo "Created: {$pending['created_at']}<br><br>";
    
    // Check if OTP exists in tbl_otp
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' AND otp = '{$pending['otp']}' AND purpose = 'registration'";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        $otpData = $otpResult->fetch_assoc();
        echo "<strong>✅ OTP Record Found:</strong><br>";
        echo "OTP: {$otpData['otp']}<br>";
        echo "Is Used: " . ($otpData['is_used'] ? 'YES' : 'NO') . "<br>";
        echo "Expires: {$otpData['expires_at']}<br><br>";
        
        if ($otpData['is_used'] == 1) {
            echo "<strong>⚠️ OTP has been used. Resetting...</strong><br>";
            $db->update("UPDATE tbl_otp SET is_used = 0 WHERE email = '$email' AND otp = '{$pending['otp']}'");
            echo "✅ OTP reset successfully!<br><br>";
        }
    } else {
        echo "<strong>❌ OTP not found in tbl_otp. Creating...</strong><br>";
        $insertOtp = "INSERT INTO tbl_otp (email, otp, purpose, expires_at, created_at, is_used) 
                     VALUES ('$email', '{$pending['otp']}', 'registration', '{$pending['expires_at']}', NOW(), 0)";
        
        if ($db->insert($insertOtp)) {
            echo "✅ OTP record created!<br><br>";
        } else {
            echo "❌ Failed to create OTP record.<br><br>";
        }
    }
    
    // Check if expired
    $currentTime = date('Y-m-d H:i:s');
    if ($pending['expires_at'] < $currentTime) {
        echo "<strong>⚠️ OTP has expired. Extending...</strong><br>";
        $newExpiry = date('Y-m-d H:i:s', strtotime('+2 hours'));
        $updateQuery = "UPDATE tbl_pending_verification SET expires_at = '$newExpiry' WHERE email = '$email'";
        $db->update($updateQuery);
        
        $updateOtpQuery = "UPDATE tbl_otp SET expires_at = '$newExpiry' WHERE email = '$email' AND otp = '{$pending['otp']}'";
        $db->update($updateOtpQuery);
        
        echo "✅ OTP extended by 2 hours!<br><br>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 Test Form</h3>";
    echo "<p>Use this form to test the verification:</p>";
    
    echo "<form method='POST' action='verify_registration.php' style='background:#f8f9fa; padding:20px; border-radius:5px;'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='token' value=''>";
    echo "<label><strong>Email:</strong></label><br>";
    echo "<input type='text' value='$email' disabled style='width:300px; padding:10px; margin:10px 0;'><br>";
    echo "<label><strong>OTP Code:</strong></label><br>";
    echo "<input type='text' name='otp' value='{$pending['otp']}' style='width:200px; padding:10px; margin:10px 0; font-size:18px; text-align:center;' maxlength='6'><br>";
    echo "<button type='submit' name='verify_otp' style='background:#27ae60; color:white; padding:12px 24px; border:none; border-radius:5px; font-size:16px; cursor:pointer;'>✅ Test Verification</button>";
    echo "</form>";
    
    echo "<br><p><strong>Or use the regular verification page:</strong></p>";
    echo "<a href='verify_registration.php?email=" . urlencode($email) . "' style='background:#3498db; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🔗 Go to Verification Page</a>";
    
} else {
    echo "<strong>❌ No pending verification found for $email</strong><br>";
    echo "Please complete the signup process first.<br><br>";
    echo "<a href='signup_enhanced.php'>🔗 Go to Signup</a>";
}
?>
