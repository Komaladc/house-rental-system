<?php
// Focused OTP verification debug for signup process
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

echo "<h1>🔍 Signup OTP Verification Debug</h1>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

// Simulate the exact signup process
if (isset($_POST['simulate_signup'])) {
    $testEmail = $_POST['test_email'];
    
    echo "<h2>🚀 Simulating Signup Process</h2>";
    
    // Step 1: Clean existing data
    echo "<h3>Step 1: Cleaning Existing Data</h3>";
    $db->delete("DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    $db->delete("DELETE FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    $db->delete("DELETE FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testEmail) . "'");
    echo "<p>✅ Cleaned existing data</p>";
    
    // Step 2: Initiate email verification (like signup form does)
    echo "<h3>Step 2: Initiating Email Verification</h3>";
    $registrationData = [
        'fname' => 'Test',
        'lname' => 'Owner',
        'username' => 'testowner_' . time(),
        'email' => $testEmail,
        'cellno' => '9876543210',
        'address' => 'Test Address, Kathmandu',
        'password' => 'testpass123',
        'level' => '2', // Owner
        'requires_verification' => true,
        'uploaded_files' => [],
        'citizenship_id' => 'TEST123456'
    ];
    
    $result = $preReg->initiateEmailVerification($registrationData);
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Email verification initiated successfully!</div>";
        
        // Store in session like the real signup form does
        $_SESSION['pending_email'] = $testEmail;
        if (isset($result['token'])) {
            $_SESSION['verification_token'] = $result['token'];
        }
        
        // Get the generated OTP
        $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($otpQuery);
        
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpData = $otpResult->fetch_assoc();
            $_SESSION['generated_otp'] = $otpData['otp'];
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>📧 Generated OTP:</strong> <span style='font-size: 24px; color: #e74c3c;'><strong>" . $otpData['otp'] . "</strong></span><br>";
            echo "<strong>Purpose:</strong> " . $otpData['purpose'] . "<br>";
            echo "<strong>Created:</strong> " . $otpData['created_at'] . "<br>";
            echo "<strong>Expires:</strong> " . $otpData['expires_at'] . "<br>";
            echo "<strong>Used:</strong> " . ($otpData['is_used'] ? 'Yes' : 'No') . "<br>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Email verification initiation failed!</div>";
        echo "<p>" . strip_tags($result['message']) . "</p>";
    }
}

// Test OTP verification (like the signup form does)
if (isset($_POST['test_verification'])) {
    $email = $_SESSION['pending_email'] ?? $_POST['email'];
    $token = $_SESSION['verification_token'] ?? '';
    $otpCode = $_POST['otp_code'];
    
    echo "<h2>🔍 Testing OTP Verification</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Email:</strong> $email<br>";
    echo "<strong>Token:</strong> " . ($token ? substr($token, 0, 10) . '...' : 'Empty') . "<br>";
    echo "<strong>OTP Code:</strong> $otpCode<br>";
    echo "</div>";
    
    // Use the exact same logic as the signup form
    if (empty($token)) {
        echo "<h3>🔄 Using OTP-only verification (no token)</h3>";
        $verifyResult = $preReg->verifyOTPAndCreateAccount($email, $otpCode);
    } else {
        echo "<h3>🔄 Using token + OTP verification</h3>";
        $verifyResult = $preReg->verifyAndCreateAccount($email, $token, $otpCode);
    }
    
    if ($verifyResult['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>🎉 VERIFICATION SUCCESS!</div>";
        echo "<div style='background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
        echo $verifyResult['message'];
        echo "</div>";
        
        // Clear session like the real form does
        unset($_SESSION['pending_email']);
        unset($_SESSION['verification_token']);
        unset($_SESSION['generated_otp']);
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ VERIFICATION FAILED!</div>";
        echo "<div style='background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
        echo $verifyResult['message'];
        echo "</div>";
    }
}

// Show debug information
echo "<h2>📊 Debug Information</h2>";

if (isset($_SESSION['pending_email'])) {
    $email = $_SESSION['pending_email'];
    
    // Show OTP data
    echo "<h3>OTP Data for " . htmlspecialchars($email) . "</h3>";
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th><th>Status</th></tr>";
        
        $currentTime = NepalTime::now();
        while ($row = $otpResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
            $isUsed = $row['is_used'] == 1;
            $isValid = !$isExpired && !$isUsed;
            
            $statusColor = $isValid ? '#d4edda' : ($isExpired ? '#fff3cd' : '#f8d7da');
            
            echo "<tr style='background: $statusColor;'>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['purpose'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($isUsed ? '🔒 Yes' : '○ No') . "</td>";
            echo "<td>" . ($isValid ? '✅ Valid' : ($isExpired ? '⏰ Expired' : '🔒 Used')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show pending verification data
    echo "<h3>Pending Verification Data</h3>";
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC";
    $pendingResult = $db->select($pendingQuery);
    
    if ($pendingResult && $pendingResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Token (First 10)</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
        while ($row = $pendingResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . substr($row['verification_token'], 0, 10) . "...</td>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_verified'] ? '✅ Yes' : '○ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Show session data
echo "<h3>Session Data</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<strong>pending_email:</strong> " . (isset($_SESSION['pending_email']) ? $_SESSION['pending_email'] : 'Not set') . "<br>";
echo "<strong>verification_token:</strong> " . (isset($_SESSION['verification_token']) ? substr($_SESSION['verification_token'], 0, 10) . '...' : 'Not set') . "<br>";
echo "<strong>generated_otp:</strong> " . (isset($_SESSION['generated_otp']) ? $_SESSION['generated_otp'] : 'Not set') . "<br>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🚀 Step 1: Simulate Signup Process</h3>
    <label for="test_email"><strong>Test Email:</strong></label><br>
    <input type="email" name="test_email" id="test_email" value="<?php echo $_SESSION['pending_email'] ?? 'testowner' . time() . '@gmail.com'; ?>" style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    
    <button type="submit" name="simulate_signup" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🎯 Simulate Signup</button>
</form>

<?php if (isset($_SESSION['pending_email'])): ?>
<form method="POST" style="background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🔍 Step 2: Test OTP Verification</h3>
    <input type="hidden" name="email" value="<?php echo $_SESSION['pending_email']; ?>">
    
    <label for="otp_code"><strong>OTP Code:</strong></label><br>
    <input type="text" name="otp_code" id="otp_code" value="<?php echo $_SESSION['generated_otp'] ?? ''; ?>" style="width: 100px; padding: 5px; margin: 5px 0; font-size: 18px; font-weight: bold;"><br>
    <small style="color: #666;">Use the OTP generated above or enter your own</small><br>
    
    <button type="submit" name="test_verification" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🧪 Test Verification</button>
</form>
<?php endif; ?>

<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <h4>💡 Debug Tips:</h4>
    <ul>
        <li>First, simulate the signup process to generate an OTP</li>
        <li>Then use the generated OTP to test verification</li>
        <li>Check the debug information to see database states</li>
        <li>Look for any timing or purpose mismatches</li>
    </ul>
</div>
