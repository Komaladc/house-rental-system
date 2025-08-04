<?php
// Final verification debug - shows the exact flow
session_start();
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/EmailOTP.php";
include_once "classes/PreRegistrationVerification.php";

echo "<h1>🔧 Enhanced Signup Flow Verification</h1>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

$db = new Database();
$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

// Get session data
$pendingEmail = $_SESSION['pending_email'] ?? $_SESSION['verification_email'] ?? '';
$verificationToken = $_SESSION['verification_token'] ?? '';

echo "<h2>📋 Session Information</h2>";
echo "<p><strong>Pending Email:</strong> " . ($pendingEmail ?: 'Not set') . "</p>";
echo "<p><strong>Verification Token:</strong> " . ($verificationToken ? 'Present (' . substr($verificationToken, 0, 15) . '...)' : 'Not set') . "</p>";

if ($pendingEmail) {
    echo "<h2>🔍 Database Records for: $pendingEmail</h2>";
    
    // Check OTP records
    echo "<h3>OTP Records</h3>";
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$pendingEmail' ORDER BY created_at DESC LIMIT 3";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th><th>Status</th></tr>";
        while ($row = $otpResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) <= strtotime(NepalTime::now());
            $status = $row['is_used'] ? 'Used' : ($isExpired ? 'Expired' : 'Valid');
            $statusColor = $row['is_used'] ? '#ff9999' : ($isExpired ? '#ffcc99' : '#99ff99');
            
            echo "<tr style='background: $statusColor;'>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['purpose'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_used'] ? 'YES' : 'NO') . "</td>";
            echo "<td><strong>$status</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No OTP records found</p>";
    }
    
    // Check pending verification records
    echo "<h3>Pending Verification Records</h3>";
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$pendingEmail' ORDER BY created_at DESC LIMIT 3";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult && $pendingResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Token</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th><th>Status</th></tr>";
        while ($row = $pendingResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) <= strtotime(NepalTime::now());
            $status = $row['is_verified'] ? 'Verified' : ($isExpired ? 'Expired' : 'Valid');
            $statusColor = $row['is_verified'] ? '#99ff99' : ($isExpired ? '#ffcc99' : '#99ff99');
            
            echo "<tr style='background: $statusColor;'>";
            echo "<td>" . substr($row['verification_token'], 0, 20) . "...</td>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_verified'] ? 'YES' : 'NO') . "</td>";
            echo "<td><strong>$status</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No pending verification records found</p>";
    }
}

// Test form
if ($_POST) {
    $testEmail = $_POST['test_email'];
    $testOTP = $_POST['test_otp'];
    
    echo "<h2>🧪 Testing OTP: $testOTP for Email: $testEmail</h2>";
    
    // Test the enhanced verification
    $result = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
    
    if ($result['success']) {
        echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ SUCCESS!</h3>";
        echo "<p>Account creation successful!</p>";
        if (isset($result['requires_verification'])) {
            echo "<p><strong>Requires Admin Verification:</strong> " . ($result['requires_verification'] ? 'YES' : 'NO') . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ FAILED</h3>";
        echo "<div>" . $result['message'] . "</div>";
        echo "</div>";
    }
}
?>

<form method="POST" style="margin: 20px 0; padding: 20px; border: 2px solid #007cba; background: #f8f9fa; border-radius: 5px;">
    <h3>🧪 Test Enhanced OTP Verification</h3>
    <div style="margin: 10px 0;">
        <label><strong>Email:</strong></label><br>
        <input type="email" name="test_email" value="<?php echo $pendingEmail; ?>" required style="width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
    </div>
    <div style="margin: 10px 0;">
        <label><strong>OTP Code:</strong></label><br>
        <input type="text" name="test_otp" placeholder="Enter the 6-digit OTP" required style="width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
    </div>
    <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; border-radius: 3px;">🔍 Test Enhanced Verification</button>
</form>

<div style="margin: 20px 0; padding: 15px; border: 1px solid #bee5eb; background: #d1ecf1; border-radius: 5px;">
    <h4>📝 Instructions:</h4>
    <ol>
        <li>Go to <a href="signup_enhanced.php" target="_blank">Enhanced Signup Page</a></li>
        <li>Fill out the registration form and submit</li>
        <li>You'll receive an OTP code (check console/email)</li>
        <li>Come back here and test the OTP verification</li>
        <li>This page will show exactly what happens during verification</li>
    </ol>
    <p><strong>🎯 This should now work with the enhanced verification logic!</strong></p>
</div>
