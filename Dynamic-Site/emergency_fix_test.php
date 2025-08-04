<?php
// Quick OTP test - Recreate the exact issue
session_start();
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/EmailOTP.php";
include_once "classes/PreRegistrationVerification.php";

echo "<h2>🚨 Emergency OTP Fix Test</h2>";

$db = new Database();
$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

// Test with a specific email
$testEmail = "test@example.com";

// Step 1: Create a fresh OTP and pending verification
echo "<h3>Step 1: Creating fresh OTP and pending verification</h3>";

// Clean up any existing data
$cleanupOTP = "DELETE FROM tbl_otp WHERE email = '$testEmail'";
$cleanupPending = "DELETE FROM tbl_pending_verification WHERE email = '$testEmail'";
$db->delete($cleanupOTP);
$db->delete($cleanupPending);

// Create a fake registration data
$testData = [
    'fname' => 'Test',
    'lname' => 'User',
    'email' => $testEmail,
    'cellno' => '9800000000',
    'address' => 'Test Address',
    'password' => 'password123',
    'level' => '1',
    'requires_verification' => false,
    'uploaded_files' => [],
    'citizenship_id' => ''
];

// Try the initiate email verification
$result = $preReg->initiateEmailVerification($testData);

if ($result['success']) {
    echo "<p>✅ Registration initiated successfully</p>";
    echo "<p>Token provided: " . (isset($result['token']) ? 'YES' : 'NO') . "</p>";
    
    if (isset($result['token'])) {
        echo "<p>Token: " . substr($result['token'], 0, 20) . "...</p>";
        $_SESSION['verification_token'] = $result['token'];
    }
    
    // Get the OTP from database
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' ORDER BY created_at DESC LIMIT 1";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        $otpData = $otpResult->fetch_assoc();
        $testOTP = $otpData['otp'];
        echo "<p>✅ OTP found: $testOTP</p>";
        echo "<p>Expires: " . $otpData['expires_at'] . "</p>";
        echo "<p>Current time: " . NepalTime::now() . "</p>";
        
        // Step 2: Try to verify this OTP
        echo "<h3>Step 2: Testing OTP Verification</h3>";
        
        // Test EmailOTP verification first
        $otpVerifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
        echo "<p>EmailOTP verification: " . ($otpVerifyResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        if ($otpVerifyResult) {
            // Test full account creation
            echo "<h3>Step 3: Testing Account Creation</h3>";
            $accountResult = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
            echo "<p>Account creation: " . ($accountResult['success'] ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            
            if (!$accountResult['success']) {
                echo "<p>Error: " . strip_tags($accountResult['message']) . "</p>";
            }
        }
        
    } else {
        echo "<p>❌ No OTP found in database</p>";
    }
    
} else {
    echo "<p>❌ Registration failed: " . strip_tags($result['message']) . "</p>";
}

// Show current OTP records
echo "<h3>Current OTP Records</h3>";
$allOTPQuery = "SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 5";
$allOTPResult = $db->select($allOTPQuery);

if ($allOTPResult && $allOTPResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $allOTPResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['otp'] . "</td>";
        echo "<td>" . $row['purpose'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_used'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// Show current pending verification records
echo "<h3>Current Pending Verification Records</h3>";
$allPendingQuery = "SELECT * FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5";
$allPendingResult = $db->select($allPendingQuery);

if ($allPendingResult && $allPendingResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>Token</th><th>OTP</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $allPendingResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . substr($row['verification_token'], 0, 20) . "...</td>";
        echo "<td>" . $row['otp'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_used'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending verification records found</p>";
}
?>
