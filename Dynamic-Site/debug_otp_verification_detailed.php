<?php
// Security check - only allow admin access
if(!defined('ADMIN_DEBUG_ACCESS') && !isset($_SESSION['admin_debug'])) {
    die('Access denied. Debug files are restricted.');
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Nepal timezone first
include "config/timezone.php";

session_start();
include "lib/Database.php";
include "classes/EmailOTP.php";
include "classes/PreRegistrationVerification.php";

$db = new Database();
$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

echo "<h1>🔍 Detailed OTP Verification Debug</h1>";
echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>Current Nepal Time:</strong> " . NepalTime::now() . "<br>";
echo "<strong>Current PHP Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "<strong>Timezone:</strong> " . date_default_timezone_get() . "<br>";
echo "</div>";

if ($_POST && isset($_POST['test_email'])) {
    $testEmail = mysqli_real_escape_string($db->link, $_POST['test_email']);
    $testOTP = mysqli_real_escape_string($db->link, $_POST['test_otp']);
    
    echo "<h2>📧 Testing: $testEmail with OTP: $testOTP</h2>";
    
    // Step 1: Check all OTP records for this email
    echo "<h3>Step 1: All OTP Records for This Email</h3>";
    $allOtpQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' ORDER BY created_at DESC";
    $allOtpResult = $db->select($allOtpQuery);
    
    if ($allOtpResult && $allOtpResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Is Used</th><th>Status</th></tr>";
        
        $currentTime = NepalTime::now();
        while ($row = $allOtpResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
            $isUsed = $row['is_used'] == 1;
            $isValid = !$isExpired && !$isUsed;
            
            echo "<tr>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['purpose'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($isUsed ? '🔒 Yes' : '○ No') . "</td>";
            echo "<td>" . ($isValid ? '✅ Valid' : ($isExpired ? '⏰ Expired' : '🔒 Used')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ No OTP records found for this email</div>";
    }
    
    // Step 2: Check pending verification
    echo "<h3>Step 2: Pending Verification Records</h3>";
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$testEmail' ORDER BY created_at DESC";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult && $pendingResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Token (First 10)</th><th>OTP</th><th>Created</th><th>Expires</th><th>Is Verified</th><th>Status</th></tr>";
        
        $currentTime = NepalTime::now();
        while ($row = $pendingResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
            $isVerified = $row['is_verified'] == 1;
            $isValid = !$isExpired && !$isVerified;
            
            echo "<tr>";
            echo "<td>" . substr($row['verification_token'], 0, 10) . "...</td>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($isVerified ? '✅ Yes' : '○ No') . "</td>";
            echo "<td>" . ($isValid ? '✅ Valid' : ($isExpired ? '⏰ Expired' : '🔒 Verified')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ No pending verification records found for this email</div>";
    }
    
    // Step 3: Test OTP verification with EmailOTP class
    echo "<h3>Step 3: Direct OTP Verification Test</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Testing with EmailOTP->verifyOTP()...</strong><br>";
    
    $verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
    
    if ($verifyResult) {
        echo "<p style='color: green; font-size: 18px;'>🎉 <strong>SUCCESS!</strong> OTP verification passed!</p>";
    } else {
        echo "<p style='color: red; font-size: 18px;'>❌ <strong>FAILED!</strong> OTP verification failed!</p>";
    }
    echo "</div>";
    
    // Step 4: Test manual OTP verification query
    echo "<h3>Step 4: Manual OTP Verification Query</h3>";
    $currentTime = NepalTime::now();
    $manualQuery = "SELECT * FROM tbl_otp 
                   WHERE email = '$testEmail' 
                   AND otp = '$testOTP' 
                   AND purpose = 'registration' 
                   AND expires_at > '$currentTime' 
                   AND is_used = 0";
    
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Query:</strong><br>";
    echo "<code>$manualQuery</code>";
    echo "</div>";
    
    $manualResult = $db->select($manualQuery);
    
    if ($manualResult && $manualResult->num_rows > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Manual query found matching OTP record!</div>";
        $row = $manualResult->fetch_assoc();
        echo "<pre>" . print_r($row, true) . "</pre>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Manual query found no matching OTP record!</div>";
    }
    
    // Step 5: Test complete verification flow
    echo "<h3>Step 5: Complete Verification Flow Test</h3>";
    
    // Get the most recent pending verification
    $recentPendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$testEmail' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
    $recentPendingResult = $db->select($recentPendingQuery);
    
    if ($recentPendingResult && $recentPendingResult->num_rows > 0) {
        $pendingData = $recentPendingResult->fetch_assoc();
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Testing with PreRegistrationVerification->verifyOTPAndCreateAccount()...</strong><br>";
        
        // Test the complete flow (but don't actually create account)
        $completeResult = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
        
        if ($completeResult['success']) {
            echo "<p style='color: green; font-size: 18px;'>🎉 <strong>COMPLETE FLOW SUCCESS!</strong></p>";
            echo "<p>" . strip_tags($completeResult['message']) . "</p>";
        } else {
            echo "<p style='color: red; font-size: 18px;'>❌ <strong>COMPLETE FLOW FAILED!</strong></p>";
            echo "<p>" . strip_tags($completeResult['message']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ No pending verification found for complete flow test</div>";
    }
    
    // Step 6: Database connection and timezone checks
    echo "<h3>Step 6: System Status</h3>";
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Database Connection:</strong> " . ($db->link ? '✅ Connected' : '❌ Failed') . "<br>";
    echo "<strong>MySQL Time:</strong> ";
    $mysqlTimeResult = $db->select("SELECT NOW() as mysql_time");
    if ($mysqlTimeResult && $mysqlTimeResult->num_rows > 0) {
        $mysqlTime = $mysqlTimeResult->fetch_assoc();
        echo $mysqlTime['mysql_time'];
    } else {
        echo "❌ Could not get MySQL time";
    }
    echo "<br>";
    echo "<strong>PHP Timezone:</strong> " . date_default_timezone_get() . "<br>";
    echo "<strong>NepalTime Class:</strong> " . (class_exists('NepalTime') ? '✅ Available' : '❌ Missing') . "<br>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>

<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🧪 Test OTP Verification</h3>
    <label for="test_email"><strong>Email:</strong></label><br>
    <input type="email" name="test_email" id="test_email" value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : 'bistakaran89@gmail.com'; ?>" style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    
    <label for="test_otp"><strong>OTP Code:</strong></label><br>
    <input type="text" name="test_otp" id="test_otp" value="<?php echo isset($_POST['test_otp']) ? htmlspecialchars($_POST['test_otp']) : ''; ?>" style="width: 100px; padding: 5px; margin: 5px 0;"><br>
    
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🔍 Debug OTP</button>
</form>

</body>
</html>
