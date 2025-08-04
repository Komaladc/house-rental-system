<?php
include "inc/header.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";
include "config/timezone.php";

$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

echo "<h1>Final OTP System Test</h1>";

$testEmail = "munaim1010@gmail.com";

// Step 1: Clean slate
echo "<h3>Step 1: Cleaning existing data</h3>";
$cleanQuery = "DELETE FROM tbl_otp WHERE email = '$testEmail'";
$db->delete($cleanQuery);
$cleanQuery2 = "DELETE FROM tbl_pending_verification WHERE email = '$testEmail'";
$db->delete($cleanQuery2);
echo "✅ Cleaned existing data<br>";

// Step 2: Test full registration flow
echo "<h3>Step 2: Starting Registration Process</h3>";
$registrationData = [
    'fname' => 'Test',
    'lname' => 'User',
    'username' => 'testuser_' . time(),
    'email' => $testEmail,
    'cellno' => '9876543210',
    'address' => 'Test Address',
    'password' => 'testpass123',
    'level' => '1'
];

echo "Initiating email verification for: $testEmail<br>";
$result = $preReg->initiateEmailVerification($registrationData);

if ($result['success']) {
    echo "✅ Email verification initiated successfully<br>";
    echo "Result: " . strip_tags($result['message']) . "<br>";
    
    // Step 3: Check what was stored
    echo "<h3>Step 3: Checking stored data</h3>";
    
    // Check pending verification
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$testEmail' ORDER BY created_at DESC LIMIT 1";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult && mysqli_num_rows($pendingResult) > 0) {
        $pendingData = mysqli_fetch_assoc($pendingResult);
        echo "✅ Pending verification found:<br>";
        echo "- Token: " . substr($pendingData['verification_token'], 0, 20) . "...<br>";
        echo "- Expires: " . $pendingData['expires_at'] . "<br>";
        echo "- Created: " . $pendingData['created_at'] . "<br>";
        
        // Check OTP
        $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($otpQuery);
        
        if ($otpResult && mysqli_num_rows($otpResult) > 0) {
            $otpData = mysqli_fetch_assoc($otpResult);
            echo "✅ OTP found:<br>";
            echo "- OTP: " . $otpData['otp'] . "<br>";
            echo "- Purpose: " . $otpData['purpose'] . "<br>";
            echo "- Expires: " . $otpData['expires_at'] . "<br>";
            echo "- Used: " . ($otpData['is_used'] ? 'Yes' : 'No') . "<br>";
            
            // Step 4: Test verification
            echo "<h3>Step 4: Testing OTP Verification</h3>";
            
            $testOTP = $otpData['otp'];
            $testToken = $pendingData['verification_token'];
            
            echo "Testing verification with:<br>";
            echo "- Email: $testEmail<br>";
            echo "- OTP: $testOTP<br>";
            echo "- Token: " . substr($testToken, 0, 20) . "...<br>";
            
            $verifyResult = $preReg->verifyAndCreateAccount($testEmail, $testToken, $testOTP);
            
            if ($verifyResult['success']) {
                echo "✅ <strong style='color: green'>VERIFICATION SUCCESSFUL!</strong><br>";
                echo "Message: " . strip_tags($verifyResult['message']) . "<br>";
                
                // Check if user was created
                $userQuery = "SELECT * FROM tbl_user WHERE email = '$testEmail' ORDER BY id DESC LIMIT 1";
                $userResult = $db->select($userQuery);
                
                if ($userResult && mysqli_num_rows($userResult) > 0) {
                    $userData = mysqli_fetch_assoc($userResult);
                    echo "✅ User account created successfully:<br>";
                    echo "- ID: " . $userData['id'] . "<br>";
                    echo "- Name: " . $userData['fname'] . " " . $userData['lname'] . "<br>";
                    echo "- Email: " . $userData['email'] . "<br>";
                    echo "- Level: " . $userData['level'] . "<br>";
                } else {
                    echo "❌ User account was not created<br>";
                }
            } else {
                echo "❌ <strong style='color: red'>VERIFICATION FAILED!</strong><br>";
                echo "Error: " . strip_tags($verifyResult['message']) . "<br>";
                
                // Debug: Check OTP verification directly
                echo "<h4>Debug: Direct OTP Verification</h4>";
                $directOTPCheck = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
                echo "Direct EmailOTP->verifyOTP() result: " . ($directOTPCheck ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            }
        } else {
            echo "❌ No OTP found in database<br>";
        }
    } else {
        echo "❌ No pending verification found<br>";
    }
} else {
    echo "❌ Email verification initiation failed<br>";
    echo "Error: " . strip_tags($result['message']) . "<br>";
}

echo "<h3>Current Nepal Time: " . NepalTime::now() . "</h3>";
?>
