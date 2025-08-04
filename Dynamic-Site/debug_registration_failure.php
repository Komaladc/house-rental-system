<?php
// Debug the registration initiation failure
session_start();
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/EmailOTP.php";
include_once "classes/PreRegistrationVerification.php";

echo "<h2>🔍 Debug Registration Initiation Issue</h2>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

$db = new Database();
$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

$quickTestEmail = "debug_reg@example.com";

echo "<h3>Step 1: Clean up existing data</h3>";
$cleanupUser = "DELETE FROM tbl_user WHERE userEmail = '$quickTestEmail'";
$cleanupOTP = "DELETE FROM tbl_otp WHERE email = '$quickTestEmail'";
$cleanupPending = "DELETE FROM tbl_pending_verification WHERE email = '$quickTestEmail'";
$cleanupVerification = "DELETE FROM tbl_user_verification WHERE email = '$quickTestEmail'";

$db->delete($cleanupUser);
$db->delete($cleanupOTP);
$db->delete($cleanupPending);
$db->delete($cleanupVerification);

echo "<p>✅ Cleanup completed</p>";

echo "<h3>Step 2: Prepare test data</h3>";
$testData = [
    'fname' => 'Debug',
    'lname' => 'Registration',
    'email' => $quickTestEmail,
    'cellno' => '9800000099',
    'address' => 'Debug Test Address',
    'password' => 'debug123',
    'level' => '1',
    'requires_verification' => false,
    'uploaded_files' => [],
    'citizenship_id' => ''
];

echo "<p>✅ Test data prepared for email: $quickTestEmail</p>";

echo "<h3>Step 3: Attempt registration initiation</h3>";
try {
    $registrationResult = $preReg->initiateEmailVerification($testData);
    
    if ($registrationResult['success']) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ SUCCESS!</h4>";
        echo "<p>Registration initiation successful!</p>";
        echo "<p><strong>Message:</strong> " . strip_tags($registrationResult['message']) . "</p>";
        if (isset($registrationResult['token'])) {
            echo "<p><strong>Token:</strong> " . substr($registrationResult['token'], 0, 20) . "...</p>";
        }
        echo "</div>";
        
        // Check what was created
        echo "<h3>Step 4: Verify created records</h3>";
        
        $otpCheck = "SELECT * FROM tbl_otp WHERE email = '$quickTestEmail'";
        $otpResult = $db->select($otpCheck);
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpData = $otpResult->fetch_assoc();
            echo "<p>✅ OTP created: " . $otpData['otp'] . " (expires: " . $otpData['expires_at'] . ")</p>";
        } else {
            echo "<p>❌ No OTP record found</p>";
        }
        
        $pendingCheck = "SELECT * FROM tbl_pending_verification WHERE email = '$quickTestEmail'";
        $pendingResult = $db->select($pendingCheck);
        if ($pendingResult && $pendingResult->num_rows > 0) {
            $pendingData = $pendingResult->fetch_assoc();
            echo "<p>✅ Pending verification created (token: " . substr($pendingData['verification_token'], 0, 20) . "...)</p>";
        } else {
            echo "<p>❌ No pending verification record found</p>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ FAILURE!</h4>";
        echo "<p>Registration initiation failed!</p>";
        echo "<p><strong>Message:</strong> " . strip_tags($registrationResult['message']) . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ EXCEPTION!</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<h3>Step 5: Check PHP Error Log</h3>";
$errorLogFile = "C:\\xampp\\php\\logs\\php_error_log";
if (file_exists($errorLogFile)) {
    $lastErrors = file_get_contents($errorLogFile);
    $recentErrors = array_slice(explode("\n", $lastErrors), -10);
    
    echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
    echo "<h4>Recent PHP Errors:</h4>";
    foreach ($recentErrors as $error) {
        if (!empty($error) && (strpos($error, 'initiate') !== false || strpos($error, 'OTP') !== false || strpos($error, 'verification') !== false)) {
            echo "<p style='font-family: monospace; font-size: 12px; color: #dc3545;'>" . htmlspecialchars($error) . "</p>";
        }
    }
    echo "</div>";
} else {
    echo "<p>PHP error log not found at: $errorLogFile</p>";
}

echo "<h3>Step 6: Test Database Operations Manually</h3>";
try {
    // Test OTP insertion manually
    $testOTPInsert = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) VALUES ('$quickTestEmail', '123456', 'registration', '" . NepalTime::now() . "', '" . NepalTime::addMinutes(20) . "', 0)";
    $otpInsertResult = $db->insert($testOTPInsert);
    
    if ($otpInsertResult) {
        echo "<p>✅ Manual OTP insertion: SUCCESS</p>";
        
        // Clean it up
        $db->delete("DELETE FROM tbl_otp WHERE email = '$quickTestEmail'");
    } else {
        echo "<p>❌ Manual OTP insertion: FAILED</p>";
        echo "<p>MySQL Error: " . mysqli_error($db->link) . "</p>";
    }
    
    // Test pending verification insertion manually
    $testToken = md5(uniqid() . time());
    $testPendingInsert = "INSERT INTO tbl_pending_verification (email, verification_token, otp, registration_data, expires_at, created_at) VALUES ('$quickTestEmail', '$testToken', '123456', '" . mysqli_real_escape_string($db->link, json_encode($testData)) . "', '" . NepalTime::addHours(2) . "', '" . NepalTime::now() . "')";
    $pendingInsertResult = $db->insert($testPendingInsert);
    
    if ($pendingInsertResult) {
        echo "<p>✅ Manual pending verification insertion: SUCCESS</p>";
        
        // Clean it up
        $db->delete("DELETE FROM tbl_pending_verification WHERE email = '$quickTestEmail'");
    } else {
        echo "<p>❌ Manual pending verification insertion: FAILED</p>";
        echo "<p>MySQL Error: " . mysqli_error($db->link) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Manual database test failed: " . $e->getMessage() . "</p>";
}
?>
