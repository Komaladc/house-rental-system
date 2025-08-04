<?php
require_once 'lib/Database.php';
require_once 'classes/PreRegistrationVerification.php';

$db = new Database();
$preVerification = new PreRegistrationVerification();

echo "=== OTP Verification Test for thekomalad@gmail.com ===\n\n";

$email = "thekomalad@gmail.com";

// 1. Check current pending verification
echo "1. Checking pending verification records:\n";
$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
$result = $db->select($pendingQuery);

if ($result && $result->num_rows > 0) {
    $pending = $result->fetch_assoc();
    echo "   ✓ Found pending verification:\n";
    echo "     Email: {$pending['email']}\n";
    echo "     OTP: {$pending['otp']}\n";
    echo "     Token: {$pending['verification_token']}\n";
    echo "     Expires: {$pending['expires_at']}\n";
    echo "     Created: {$pending['created_at']}\n\n";
    
    $storedOtp = $pending['otp'];
    $storedToken = $pending['verification_token'];
} else {
    echo "   ✗ No pending verification found!\n\n";
    exit("Please complete the signup process first.\n");
}

// 2. Check OTP table
echo "2. Checking OTP table records:\n";
$otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' AND purpose = 'registration' ORDER BY created_at DESC LIMIT 1";
$result = $db->select($otpQuery);

if ($result && $result->num_rows > 0) {
    $otpRecord = $result->fetch_assoc();
    echo "   ✓ Found OTP record:\n";
    echo "     Email: {$otpRecord['email']}\n";
    echo "     OTP: {$otpRecord['otp']}\n";
    echo "     Purpose: {$otpRecord['purpose']}\n";
    echo "     Expires: {$otpRecord['expires_at']}\n";
    echo "     Is Used: {$otpRecord['is_used']}\n";
    echo "     Created: {$otpRecord['created_at']}\n\n";
    
    $otpTableOtp = $otpRecord['otp'];
} else {
    echo "   ✗ No OTP record found!\n\n";
    $otpTableOtp = null;
}

// 3. Compare OTPs
echo "3. Comparing OTPs:\n";
if (isset($storedOtp) && isset($otpTableOtp)) {
    if ($storedOtp === $otpTableOtp) {
        echo "   ✓ OTPs match: $storedOtp\n\n";
        $testOtp = $storedOtp;
    } else {
        echo "   ✗ OTPs don't match!\n";
        echo "     Pending table: $storedOtp\n";
        echo "     OTP table: $otpTableOtp\n\n";
        echo "   Using OTP from pending table: $storedOtp\n\n";
        $testOtp = $storedOtp;
    }
} else {
    echo "   ⚠ Missing OTP data\n\n";
    $testOtp = null;
}

// 4. Test the verification process
if ($testOtp) {
    echo "4. Testing OTP verification:\n";
    echo "   Using OTP: $testOtp\n";
    echo "   Using Email: $email\n\n";
    
    try {
        // Test the verifyOTPAndCreateAccount method directly
        $result = $preVerification->verifyOTPAndCreateAccount($email, $testOtp);
        
        echo "   Verification Result:\n";
        echo "     Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
        echo "     Message: " . strip_tags($result['message']) . "\n";
        
        if ($result['success']) {
            echo "   ✓ VERIFICATION SUCCESSFUL!\n";
            if (isset($result['user_data'])) {
                echo "     User Data Available: YES\n";
                echo "     First Name: " . $result['user_data']['fname'] . "\n";
                echo "     Last Name: " . $result['user_data']['lname'] . "\n";
                echo "     Email: " . $result['user_data']['email'] . "\n";
            } else {
                echo "     User Data Available: NO\n";
            }
        } else {
            echo "   ✗ VERIFICATION FAILED!\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Exception occurred: " . $e->getMessage() . "\n";
        echo "     File: " . $e->getFile() . "\n";
        echo "     Line: " . $e->getLine() . "\n";
    }
} else {
    echo "4. Cannot test - no OTP available\n";
}

echo "\n=== INSTRUCTIONS ===\n";
echo "1. The OTP from your email should be: " . (isset($testOtp) ? $testOtp : 'NOT FOUND') . "\n";
echo "2. Go to: http://localhost/house-rental-system/Dynamic-Site/verify_registration.php?email=" . urlencode($email) . "\n";
echo "3. Enter the OTP: " . (isset($testOtp) ? $testOtp : 'NOT FOUND') . "\n";
echo "4. Click 'Verify & Create Account'\n";
echo "\nIf this still doesn't work, there might be a session or database issue.\n";
?>
