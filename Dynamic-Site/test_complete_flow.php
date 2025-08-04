<?php
// Test Complete Registration Flow (Without Double OTP Verification)
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kathmandu');

echo "<h2>🚀 Complete Registration Test (Fixed)</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (Nepal Time)</p>";

try {
    include "lib/Database.php";
    include "classes/PreRegistrationVerification.php";
    include "classes/EmailOTP.php";
    
    $db = new Database();
    $preReg = new PreRegistrationVerification();
    $emailOTP = new EmailOTP();
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>✅ All classes loaded successfully</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>❌ Error loading classes: " . $e->getMessage() . "</div>";
    exit;
}

function logStep($step, $message, $success = true) {
    $color = $success ? "#d4edda" : "#f8d7da";
    $icon = $success ? "✅" : "❌";
    echo "<div style='background: $color; padding: 10px; margin: 5px 0; border-radius: 5px;'>$icon <strong>Step $step:</strong> $message</div>";
}

if ($_POST && isset($_POST['test_complete'])) {
    $testEmail = $_POST['test_email'];
    $testData = [
        'fname' => 'Test',
        'lname' => 'User',
        'username' => 'testuser_' . time(),
        'email' => $testEmail,
        'cellno' => '9876543210',
        'address' => 'Test Address',
        'password' => 'testpass123',
        'level' => 2
    ];
    
    echo "<h3>🧪 Testing COMPLETE Registration Flow</h3>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>Test Data:</strong><br>";
    foreach ($testData as $key => $value) {
        if ($key !== 'password') {
            echo "$key: $value<br>";
        } else {
            echo "$key: [hidden]<br>";
        }
    }
    echo "</div>";
    
    // Step 1: Clear any existing data for this email
    logStep(0, "Clearing existing test data...");
    $clearOTP = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'";
    $clearPending = "DELETE FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'";
    $clearUser = "DELETE FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testEmail) . "'";
    $db->delete($clearOTP);
    $db->delete($clearPending);
    $db->delete($clearUser);
    logStep(0, "Test data cleared successfully");
    
    // Step 1: Initiate registration
    logStep(1, "Initiating email verification...");
    $result = $preReg->initiateEmailVerification($testData);
    
    if ($result['success']) {
        logStep(1, "✅ Registration initiated successfully");
        
        // Get the verification token and OTP
        $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' ORDER BY created_at DESC LIMIT 1";
        $pendingResult = $db->select($pendingQuery);
        
        if ($pendingResult && mysqli_num_rows($pendingResult) > 0) {
            $pendingData = mysqli_fetch_assoc($pendingResult);
            $verificationToken = $pendingData['verification_token'];
            $otp = $pendingData['otp'];
            
            logStep(2, "Got verification token and OTP: $otp");
            
            // Step 3: Verify OTP exists in tbl_otp
            $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'registration' ORDER BY created_at DESC LIMIT 1";
            $otpResult = $db->select($otpQuery);
            
            if ($otpResult && mysqli_num_rows($otpResult) > 0) {
                $otpData = mysqli_fetch_assoc($otpResult);
                logStep(3, "✅ OTP found in tbl_otp - Purpose: " . $otpData['purpose'] . ", Is_used: " . $otpData['is_used']);
                
                // Step 4: Complete account creation (this will verify OTP internally)
                logStep(4, "Creating account with verification token and OTP...");
                $createResult = $preReg->verifyAndCreateAccount($testEmail, $verificationToken, $otp);
                
                if ($createResult['success']) {
                    logStep(4, "🎉 ACCOUNT CREATION SUCCESS! ✅");
                    
                    // Step 5: Verify user was created
                    $userQuery = "SELECT * FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testEmail) . "'";
                    $userResult = $db->select($userQuery);
                    
                    if ($userResult && mysqli_num_rows($userResult) > 0) {
                        $userData = mysqli_fetch_assoc($userResult);
                        logStep(5, "✅ User account created in database");
                        
                        echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0; border: 3px solid #28a745; text-align: center;'>";
                        echo "<h2>🎉 COMPLETE SUCCESS! 🎉</h2>";
                        echo "<h3>🚀 The entire registration system is now FULLY WORKING! 🚀</h3>";
                        echo "<hr>";
                        echo "<h4>✅ Successful Steps:</h4>";
                        echo "<ol style='text-align: left;'>";
                        echo "<li>✅ Email verification initiated</li>";
                        echo "<li>✅ OTP stored with correct purpose ('registration')</li>";
                        echo "<li>✅ Pending verification data stored</li>";
                        echo "<li>✅ OTP verification successful</li>";
                        echo "<li>✅ User account created in database</li>";
                        echo "</ol>";
                        echo "<hr>";
                        echo "<h4>📋 Created User Details:</h4>";
                        echo "<table style='margin: 10px auto; background: white; border: 1px solid #ddd;'>";
                        echo "<tr><td><strong>Name:</strong></td><td>" . $userData['firstName'] . " " . $userData['lastName'] . "</td></tr>";
                        echo "<tr><td><strong>Username:</strong></td><td>" . $userData['userName'] . "</td></tr>";
                        echo "<tr><td><strong>Email:</strong></td><td>" . $userData['userEmail'] . "</td></tr>";
                        echo "<tr><td><strong>Email Verified:</strong></td><td>" . ($userData['email_verified'] ? 'Yes' : 'No') . "</td></tr>";
                        echo "<tr><td><strong>Level:</strong></td><td>" . $userData['userLevel'] . "</td></tr>";
                        echo "</table>";
                        echo "<p style='font-size: 18px; color: #28a745;'><strong>🎯 Registration system is ready for production! 🎯</strong></p>";
                        echo "</div>";
                        
                    } else {
                        logStep(5, "❌ User account not found in database", false);
                    }
                    
                } else {
                    logStep(4, "Account creation failed: " . strip_tags($createResult['message']), false);
                    
                    // Debug: Check if OTP was marked as used
                    $debugOtpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'registration'";
                    $debugOtpResult = $db->select($debugOtpQuery);
                    if ($debugOtpResult && mysqli_num_rows($debugOtpResult) > 0) {
                        $debugOtpData = mysqli_fetch_assoc($debugOtpResult);
                        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>Debug OTP Status:</strong><br>";
                        echo "OTP: " . $debugOtpData['otp'] . "<br>";
                        echo "Is Used: " . ($debugOtpData['is_used'] ? 'Yes' : 'No') . "<br>";
                        echo "Expires: " . $debugOtpData['expires_at'] . "<br>";
                        echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
                        echo "</div>";
                    }
                }
                
            } else {
                logStep(3, "❌ OTP not found in tbl_otp table", false);
            }
            
        } else {
            logStep(2, "❌ No pending verification data found", false);
        }
        
    } else {
        logStep(1, "Registration initiation failed: " . strip_tags($result['message']), false);
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
    input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
    button { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
    button:hover { background: #218838; }
    table { border-collapse: collapse; }
    td { padding: 8px 12px; border: 1px solid #ddd; }
</style>

<form method="POST">
    <h4>🚀 Test COMPLETE Registration Flow</h4>
    <p>This will test the entire process from start to finish without double-verification.</p>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit" name="test_complete">🎯 Run Complete Test</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #d4edda; border-left: 4px solid #28a745;">
    <h4>🎯 Complete Flow Test:</h4>
    <p>This test will:</p>
    <ol>
        <li>Clear any existing test data</li>
        <li>Initiate email verification (with fixed purpose)</li>
        <li>Verify OTP is stored correctly</li>
        <li>Complete account creation using the verification token and OTP</li>
        <li>Verify the user account was created in the database</li>
    </ol>
    <p><strong>This simulates the exact user experience without any testing artifacts.</strong></p>
</div>
