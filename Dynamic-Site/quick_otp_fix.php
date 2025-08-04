<?php
// Quick OTP Fix for thekomalad@gmail.com
require_once 'lib/Database.php';

$db = new Database();
$email = "thekomalad@gmail.com";

echo "<h2>🔧 Quick OTP Fix Tool</h2>";

// Get the latest pending verification
$query = "SELECT * FROM tbl_pending_verification WHERE email = '$email' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
$result = $db->select($query);

if ($result && $result->num_rows > 0) {
    $pending = $result->fetch_assoc();
    $correctOtp = $pending['otp'];
    $token = $pending['verification_token'];
    
    echo "<p><strong>✅ Found your verification data:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> {$pending['email']}</li>";
    echo "<li><strong>Correct OTP:</strong> <span style='font-size:24px; color:blue; font-weight:bold;'>$correctOtp</span></li>";
    echo "<li><strong>Created:</strong> {$pending['created_at']}</li>";
    echo "<li><strong>Expires:</strong> {$pending['expires_at']}</li>";
    echo "</ul>";
    
    // Check if OTP is still valid
    $currentTime = date('Y-m-d H:i:s');
    if ($pending['expires_at'] > $currentTime) {
        echo "<p style='color:green;'><strong>✅ OTP is still valid!</strong></p>";
        
        // Check if there's an OTP record in tbl_otp
        $otpCheck = $db->select("SELECT * FROM tbl_otp WHERE email = '$email' AND otp = '$correctOtp' AND purpose = 'registration'");
        if (!$otpCheck || $otpCheck->num_rows == 0) {
            echo "<p style='color:orange;'>⚠️ OTP not found in tbl_otp table. Creating it now...</p>";
            
            // Create the missing OTP record
            $insertOtp = "INSERT INTO tbl_otp (email, otp, purpose, expires_at, created_at, is_used) 
                         VALUES ('$email', '$correctOtp', 'registration', '{$pending['expires_at']}', NOW(), 0)";
            
            if ($db->insert($insertOtp)) {
                echo "<p style='color:green;'>✅ OTP record created successfully!</p>";
            } else {
                echo "<p style='color:red;'>❌ Failed to create OTP record.</p>";
            }
        } else {
            $otpData = $otpCheck->fetch_assoc();
            if ($otpData['is_used'] == 1) {
                echo "<p style='color:red;'>❌ OTP has already been used. Resetting it...</p>";
                $db->update("UPDATE tbl_otp SET is_used = 0 WHERE email = '$email' AND otp = '$correctOtp'");
                echo "<p style='color:green;'>✅ OTP reset successfully!</p>";
            } else {
                echo "<p style='color:green;'>✅ OTP record exists and is ready to use!</p>";
            }
        }
        
        echo "<hr>";
        echo "<h3>🎯 Now use this OTP:</h3>";
        echo "<ol>";
        echo "<li>Go to: <a href='verify_registration.php?email=" . urlencode($email) . "' target='_blank'>Verification Page</a></li>";
        echo "<li>Enter this OTP: <strong style='font-size:20px; color:blue;'>$correctOtp</strong></li>";
        echo "<li>Click 'Verify & Create Account'</li>";
        echo "</ol>";
        
    } else {
        echo "<p style='color:red;'><strong>❌ OTP has expired!</strong></p>";
        echo "<p>Extending expiration time...</p>";
        
        $newExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $updateQuery = "UPDATE tbl_pending_verification SET expires_at = '$newExpiry' WHERE email = '$email' AND verification_token = '$token'";
        
        if ($db->update($updateQuery)) {
            echo "<p style='color:green;'>✅ OTP expiration extended by 1 hour!</p>";
            echo "<p>Now use the OTP: <strong style='font-size:20px; color:blue;'>$correctOtp</strong></p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to extend expiration.</p>";
        }
    }
    
} else {
    echo "<p style='color:red;'><strong>❌ No pending verification found for $email</strong></p>";
    echo "<p>Please complete the signup process first.</p>";
}

echo "<hr>";
echo "<p><a href='verify_registration.php?email=" . urlencode($email) . "'>🔗 Go to Verification Page</a></p>";
?>
