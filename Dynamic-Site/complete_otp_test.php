<?php
// Complete OTP verification test
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set Nepal timezone
include "config/timezone.php";

session_start();
include "lib/Database.php";
include "classes/EmailOTP.php";
include "classes/PreRegistrationVerification.php";

$db = new Database();
$emailOTP = new EmailOTP();
$preReg = new PreRegistrationVerification();

echo "<h1>🔍 Complete OTP Verification Test</h1>";

// Step 1: Create a fresh test registration
if (isset($_POST['create_test_registration'])) {
    $testEmail = $_POST['test_email'];
    
    echo "<h2>Step 1: Creating Test Registration</h2>";
    
    // Clean existing data
    $db->delete("DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    $db->delete("DELETE FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    $db->delete("DELETE FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    
    echo "<p>✅ Cleaned existing data for $testEmail</p>";
    
    // Create test registration
    $registrationData = [
        'fname' => 'Test',
        'lname' => 'User',
        'username' => 'testuser_' . time(),
        'email' => $testEmail,
        'cellno' => '9876543210',
        'address' => 'Test Address',
        'password' => 'testpass123',
        'level' => '2', // Owner - requires verification
        'requires_verification' => true,
        'uploaded_files' => [],
        'citizenship_id' => 'TEST123456'
    ];
    
    $result = $preReg->initiateEmailVerification($registrationData);
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Registration initiated successfully!</div>";
        echo "<p>" . strip_tags($result['message']) . "</p>";
        
        // Get the OTP that was created
        $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($otpQuery);
        
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpData = $otpResult->fetch_assoc();
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>📧 Generated OTP:</strong> <span style='font-size: 20px; color: #e74c3c;'><strong>" . $otpData['otp'] . "</strong></span><br>";
            echo "<strong>Purpose:</strong> " . $otpData['purpose'] . "<br>";
            echo "<strong>Expires:</strong> " . $otpData['expires_at'] . "<br>";
            echo "</div>";
            
            // Set session for next step
            $_SESSION['test_email'] = $testEmail;
            $_SESSION['test_otp'] = $otpData['otp'];
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Registration failed!</div>";
        echo "<p>" . strip_tags($result['message']) . "</p>";
    }
}

// Step 2: Test OTP verification
if (isset($_POST['test_otp_verification'])) {
    $testEmail = $_POST['test_email'];
    $testOTP = $_POST['test_otp'];
    
    echo "<h2>Step 2: Testing OTP Verification</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Email:</strong> $testEmail<br>";
    echo "<strong>OTP:</strong> $testOTP<br>";
    echo "<strong>Current Nepal Time:</strong> " . NepalTime::now() . "<br>";
    echo "</div>";
    
    // Check OTP exists
    echo "<h3>🔍 Checking OTP in Database</h3>";
    $checkQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "'";
    $checkResult = $db->select($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $otpData = $checkResult->fetch_assoc();
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ OTP found in database</div>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($otpData as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        // Check if expired
        $currentTime = NepalTime::now();
        $isExpired = strtotime($otpData['expires_at']) < strtotime($currentTime);
        $isUsed = $otpData['is_used'] == 1;
        
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Status Check:</strong><br>";
        echo "Expired: " . ($isExpired ? '❌ Yes' : '✅ No') . "<br>";
        echo "Used: " . ($isUsed ? '❌ Yes' : '✅ No') . "<br>";
        echo "Valid: " . (!$isExpired && !$isUsed ? '✅ Yes' : '❌ No') . "<br>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ OTP not found in database</div>";
    }
    
    // Test EmailOTP verification
    echo "<h3>🧪 Testing EmailOTP->verifyOTP()</h3>";
    $emailOtpResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
    
    if ($emailOtpResult) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ EmailOTP verification SUCCESS!</div>";
        
        // Reset OTP for further testing
        $resetQuery = "UPDATE tbl_otp SET is_used = 0 WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "'";
        $db->update($resetQuery);
        echo "<p><em>OTP reset for complete flow testing...</em></p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ EmailOTP verification FAILED!</div>";
    }
    
    // Test complete flow
    echo "<h3>🎯 Testing Complete Verification Flow</h3>";
    $completeResult = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
    
    if ($completeResult['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>🎉 COMPLETE FLOW SUCCESS!</div>";
        echo "<div style='background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
        echo $completeResult['message'];
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ COMPLETE FLOW FAILED!</div>";
        echo "<div style='background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
        echo $completeResult['message'];
        echo "</div>";
    }
}

// Show current data
echo "<h2>📊 Current Database State</h2>";

echo "<h3>Recent OTPs</h3>";
$recentOtps = $db->select("SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 5");
if ($recentOtps && $recentOtps->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $recentOtps->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['purpose'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_used'] ? '🔒 Used' : '✅ Available') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

echo "<h3>Recent Pending Verifications</h3>";
$recentPending = $db->select("SELECT email, otp, created_at, expires_at, is_verified FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5");
if ($recentPending && $recentPending->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
    while ($row = $recentPending->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_verified'] ? '✅ Yes' : '○ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending verification records found</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🚀 Step 1: Create Test Registration</h3>
    <label for="test_email"><strong>Test Email:</strong></label><br>
    <input type="email" name="test_email" id="test_email" value="<?php echo $_SESSION['test_email'] ?? 'test' . time() . '@example.com'; ?>" style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    
    <button type="submit" name="create_test_registration" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🎯 Create Test Registration</button>
</form>

<?php if (isset($_SESSION['test_email']) && isset($_SESSION['test_otp'])): ?>
<form method="POST" style="background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🔍 Step 2: Test OTP Verification</h3>
    <label for="test_email2"><strong>Email:</strong></label><br>
    <input type="email" name="test_email" id="test_email2" value="<?php echo $_SESSION['test_email']; ?>" readonly style="width: 300px; padding: 5px; margin: 5px 0; background: #f0f0f0;"><br>
    
    <label for="test_otp"><strong>OTP Code:</strong></label><br>
    <input type="text" name="test_otp" id="test_otp" value="<?php echo $_SESSION['test_otp']; ?>" style="width: 100px; padding: 5px; margin: 5px 0; font-size: 18px; font-weight: bold;"><br>
    
    <button type="submit" name="test_otp_verification" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🧪 Test OTP Verification</button>
</form>
<?php endif; ?>
