<?php
// Comprehensive Registration Flow Test
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kathmandu');

echo "<h2>🔍 Complete Registration Flow Diagnosis</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (Nepal Time)</p>";

// Include necessary files
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
    
    echo "<h3>🧪 Testing Complete Registration Flow</h3>";
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
    logStep(1, "Initiating email verification...");
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
            
            // Step 3: Check OTP in tbl_otp
            logStep(3, "Checking OTP in tbl_otp table...");
            $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
            $otpResult = $db->select($otpQuery);
            
            if ($otpResult && mysqli_num_rows($otpResult) > 0) {
                $otpData = mysqli_fetch_assoc($otpResult);
                logStep(3, "OTP found in tbl_otp - OTP: " . $otpData['otp'] . ", Expires: " . $otpData['expires_at']);
                
                // Step 4: Test verification with actual OTP
                logStep(4, "Testing OTP verification...");
                $verifyResult = $emailOTP->verifyOTP($email, $otpData['otp'], 'email_verification');
                
                if ($verifyResult) {
                    logStep(4, "OTP verification SUCCESS! ✅", true);
                    
                    // Step 5: Test complete account creation
                    logStep(5, "Testing account creation...");
                    $createResult = $preReg->verifyAndCreateAccount($email, $pendingData['verification_token'], $otpData['otp']);
                    
                    if ($createResult['success']) {
                        logStep(5, "Account creation SUCCESS! ✅", true);
                        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border: 2px solid #17a2b8;'>";
                        echo "<h4>🎉 COMPLETE SUCCESS!</h4>";
                        echo "<p>The entire registration flow worked perfectly:</p>";
                        echo "<ol>";
                        echo "<li>✅ Email verification initiated</li>";
                        echo "<li>✅ Pending data stored</li>";
                        echo "<li>✅ OTP stored in tbl_otp</li>";
                        echo "<li>✅ OTP verification worked</li>";
                        echo "<li>✅ Account created successfully</li>";
                        echo "</ol>";
                        echo "</div>";
                    } else {
                        logStep(5, "Account creation FAILED: " . strip_tags($createResult['message']), false);
                    }
                } else {
                    logStep(4, "OTP verification FAILED! This is the problem.", false);
                    
                    // Additional diagnosis
                    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h4>🔍 Detailed OTP Analysis:</h4>";
                    echo "<strong>OTP from pending:</strong> " . $pendingData['otp'] . "<br>";
                    echo "<strong>OTP from tbl_otp:</strong> " . $otpData['otp'] . "<br>";
                    echo "<strong>OTPs match:</strong> " . ($pendingData['otp'] === $otpData['otp'] ? 'YES' : 'NO') . "<br>";
                    echo "<strong>Current time:</strong> " . date('Y-m-d H:i:s') . "<br>";
                    echo "<strong>OTP expires:</strong> " . $otpData['expires_at'] . "<br>";
                    echo "<strong>OTP expired:</strong> " . ($otpData['expires_at'] < date('Y-m-d H:i:s') ? 'YES' : 'NO') . "<br>";
                    echo "<strong>OTP used:</strong> " . ($otpData['is_used'] ? 'YES' : 'NO') . "<br>";
                    echo "</div>";
                }
                
            } else {
                logStep(3, "❌ CRITICAL: OTP NOT found in tbl_otp! This is the root cause.", false);
                
                // Check if OTP insert failed
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>🔍 Root Cause Analysis:</h4>";
                echo "<p>The OTP was stored in tbl_pending_verification but NOT in tbl_otp.</p>";
                echo "<p>This means the OTP insertion in PreRegistrationVerification.php failed silently.</p>";
                echo "<p><strong>Solution:</strong> Check the OTP insertion code and MySQL errors.</p>";
                echo "</div>";
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
    button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>

<form method="POST">
    <h4>🧪 Test Complete Registration Flow</h4>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit" name="test_registration">🔧 Run Complete Test</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
    <h4>🎯 Complete Flow Test:</h4>
    <p>This will test every step of the registration process:</p>
    <ol>
        <li>Initiate email verification (call PreRegistrationVerification)</li>
        <li>Check if data is stored in tbl_pending_verification</li>
        <li>Check if OTP is stored in tbl_otp</li>
        <li>Test OTP verification logic</li>
        <li>Test complete account creation</li>
    </ol>
    <p><strong>This will identify exactly where the process breaks!</strong></p>
</div>
