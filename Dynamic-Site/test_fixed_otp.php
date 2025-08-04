<?php
// Test Fixed OTP System
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kathmandu');

echo "<h2>🎯 Fixed OTP System Test</h2>";
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

if ($_POST && isset($_POST['test_registration'])) {
    $testData = [
        'fname' => 'Test',
        'lname' => 'User',
        'username' => 'testuser_' . time(),
        'email' => $_POST['test_email'],
        'cellno' => '9876543210',
        'address' => 'Test Address',
        'password' => 'testpass123',
        'level' => 2
    ];
    
    echo "<h3>🧪 Testing FIXED Registration Flow</h3>";
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
    
    // Step 1: Initiate registration
    logStep(1, "Initiating email verification with FIXED purpose ('registration')...");
    $result = $preReg->initiateEmailVerification($testData);
    
    if ($result['success']) {
        logStep(1, "Registration initiated successfully");
        
        // Step 2: Check pending verification
        logStep(2, "Checking pending verification data...");
        $email = $testData['email'];
        $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC LIMIT 1";
        $pendingResult = $db->select($pendingQuery);
        
        if ($pendingResult && mysqli_num_rows($pendingResult) > 0) {
            $pendingData = mysqli_fetch_assoc($pendingResult);
            logStep(2, "Pending verification found - OTP: " . $pendingData['otp'] . ", Expires: " . $pendingData['expires_at']);
            
            // Step 3: Check OTP in tbl_otp with CORRECT purpose
            logStep(3, "Checking OTP in tbl_otp table with purpose='registration'...");
            $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND purpose = 'registration' ORDER BY created_at DESC LIMIT 1";
            $otpResult = $db->select($otpQuery);
            
            if ($otpResult && mysqli_num_rows($otpResult) > 0) {
                $otpData = mysqli_fetch_assoc($otpResult);
                logStep(3, "✅ OTP found in tbl_otp - OTP: " . $otpData['otp'] . ", Purpose: " . $otpData['purpose'] . ", Expires: " . $otpData['expires_at']);
                
                // Step 4: Test verification with actual OTP
                logStep(4, "Testing OTP verification with correct purpose...");
                $verifyResult = $emailOTP->verifyOTP($email, $otpData['otp'], 'registration');
                
                if ($verifyResult) {
                    logStep(4, "🎉 OTP verification SUCCESS! The fix worked! ✅", true);
                    
                    // Step 5: Test complete account creation
                    logStep(5, "Testing account creation...");
                    $createResult = $preReg->verifyAndCreateAccount($email, $pendingData['verification_token'], $otpData['otp']);
                    
                    if ($createResult['success']) {
                        logStep(5, "🎉 Account creation SUCCESS! ✅", true);
                        echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0; border: 3px solid #17a2b8; text-align: center;'>";
                        echo "<h2>🎉 COMPLETE SUCCESS! 🎉</h2>";
                        echo "<h3>✅ The OTP system is now FULLY WORKING! ✅</h3>";
                        echo "<p><strong>The issue was:</strong> Using 'email_verification' instead of 'registration' purpose.</p>";
                        echo "<p><strong>The fix:</strong> Updated all OTP operations to use 'registration' purpose.</p>";
                        echo "<hr>";
                        echo "<h4>🚀 Registration flow now works perfectly:</h4>";
                        echo "<ol style='text-align: left;'>";
                        echo "<li>✅ Email verification initiated</li>";
                        echo "<li>✅ Pending data stored correctly</li>";
                        echo "<li>✅ OTP stored in tbl_otp with correct purpose</li>";
                        echo "<li>✅ OTP verification works perfectly</li>";
                        echo "<li>✅ Account created successfully</li>";
                        echo "</ol>";
                        echo "<p style='font-size: 18px; color: #28a745;'><strong>🎯 Your users can now register successfully! 🎯</strong></p>";
                        echo "</div>";
                    } else {
                        logStep(5, "Account creation failed: " . strip_tags($createResult['message']), false);
                    }
                } else {
                    logStep(4, "OTP verification still failed - there may be another issue", false);
                }
                
            } else {
                logStep(3, "❌ OTP still not found in tbl_otp with purpose='registration'", false);
                
                // Check what's actually in the table
                $allOtpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC";
                $allOtpResult = $db->select($allOtpQuery);
                
                if ($allOtpResult && mysqli_num_rows($allOtpResult) > 0) {
                    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h4>🔍 Found these OTPs for this email:</h4>";
                    while ($row = mysqli_fetch_assoc($allOtpResult)) {
                        echo "OTP: {$row['otp']}, Purpose: '{$row['purpose']}', Created: {$row['created_at']}<br>";
                    }
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h4>❌ No OTPs found at all for this email</h4>";
                    echo "</div>";
                }
            }
            
        } else {
            logStep(2, "No pending verification found", false);
        }
        
    } else {
        logStep(1, "Registration initiation FAILED: " . strip_tags($result['message']), false);
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
    input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
    button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: bold; }
    button:hover { background: #218838; }
</style>

<form method="POST">
    <h4>🎯 Test FIXED Registration Flow</h4>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit" name="test_registration">🚀 Test Fixed System</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #d1ecf1; border-left: 4px solid #17a2b8;">
    <h4>🎯 Fix Applied:</h4>
    <p><strong>Root Cause:</strong> The 'purpose' column in tbl_otp is an ENUM that only accepts 'registration', 'password_reset', 'email_change'.</p>
    <p><strong>Problem:</strong> Code was using 'email_verification' which is not in the enum list.</p>
    <p><strong>Solution:</strong> Updated all OTP operations to use 'registration' purpose.</p>
    <h4>✅ Files Updated:</h4>
    <ul>
        <li>PreRegistrationVerification.php - OTP insertion and verification</li>
        <li>EmailOTP.php - Already had correct default purpose</li>
    </ul>
</div>
