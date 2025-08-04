<?php
// Debug OTP verification step by step
session_start();
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/EmailOTP.php";
include_once "classes/PreRegistrationVerification.php";

// Get email from session
$email = isset($_SESSION['verification_email']) ? $_SESSION['verification_email'] : '';

echo "<h2>🔍 OTP Verification Debug</h2>";
echo "<p><strong>Session Email:</strong> " . $email . "</p>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

// Manual form for testing
if ($_POST) {
    $test_email = $_POST['email'];
    $test_otp = $_POST['otp'];
    
    echo "<h3>Testing OTP: $test_otp for Email: $test_email</h3>";
    
    // Initialize classes
    $emailOTP = new EmailOTP();
    $preReg = new PreRegistrationVerification();
    $db = new Database();
    
    // Step 1: Check if OTP exists in database
    $query = "SELECT * FROM tbl_otp WHERE email = '$test_email' AND otp = '$test_otp' ORDER BY created_at DESC LIMIT 1";
    $result = $db->select($query);
    
    echo "<h4>Step 1: OTP Database Check</h4>";
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p>✅ OTP found in database</p>";
        echo "<div style='border: 1px solid #green; padding: 10px; background: #f0f8f0;'>";
        echo "<p><strong>OTP:</strong> " . $row['otp'] . "</p>";
        echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
        echo "<p><strong>Purpose:</strong> " . $row['purpose'] . "</p>";
        echo "<p><strong>Created:</strong> " . $row['created_at'] . "</p>";
        echo "<p><strong>Expires:</strong> " . $row['expires_at'] . "</p>";
        echo "<p><strong>Is Used:</strong> " . ($row['is_used'] ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Current Time:</strong> " . NepalTime::now() . "</p>";
        
        // Check if expired
        $expired = strtotime($row['expires_at']) < strtotime(NepalTime::now());
        echo "<p><strong>Expired?:</strong> " . ($expired ? 'YES' : 'NO') . "</p>";
        echo "</div>";
        
        // Step 2: Test EmailOTP verification
        echo "<h4>Step 2: EmailOTP Class Verification</h4>";
        $otpResult = $emailOTP->verifyOTP($test_email, $test_otp, 'registration');
        echo "<p><strong>EmailOTP Result:</strong> " . ($otpResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        // Step 3: Test PreRegistration verification
        echo "<h4>Step 3: PreRegistration Verification</h4>";
        $preRegResult = $preReg->verifyOTPAndCreateAccount($test_email, $test_otp);
        echo "<p><strong>PreRegistration Result:</strong> " . ($preRegResult['success'] ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        if (!$preRegResult['success']) {
            echo "<p><strong>Error:</strong> " . $preRegResult['message'] . "</p>";
        }
        
    } else {
        echo "<p>❌ OTP not found in database</p>";
        
        // Show all OTPs for this email
        $allOtpQuery = "SELECT * FROM tbl_otp WHERE email = '$test_email' ORDER BY created_at DESC LIMIT 5";
        $allOtpResult = $db->select($allOtpQuery);
        
        echo "<h4>All OTPs for this email:</h4>";
        if ($allOtpResult && $allOtpResult->num_rows > 0) {
            while ($row = $allOtpResult->fetch_assoc()) {
                echo "<div style='border: 1px solid #red; padding: 10px; margin: 5px 0; background: #f8f0f0;'>";
                echo "<p><strong>OTP:</strong> " . $row['otp'] . "</p>";
                echo "<p><strong>Purpose:</strong> " . $row['purpose'] . "</p>";
                echo "<p><strong>Created:</strong> " . $row['created_at'] . "</p>";
                echo "<p><strong>Expires:</strong> " . $row['expires_at'] . "</p>";
                echo "<p><strong>Is Used:</strong> " . ($row['is_used'] ? 'YES' : 'NO') . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No OTPs found for this email.</p>";
        }
    }
}
?>

<form method="POST" style="margin: 20px 0; padding: 20px; border: 2px solid #007cba; background: #f0f8ff;">
    <h3>🧪 Test OTP Verification</h3>
    <div style="margin: 10px 0;">
        <label><strong>Email:</strong></label><br>
        <input type="email" name="email" value="<?php echo $email; ?>" required style="width: 300px; padding: 5px;">
    </div>
    <div style="margin: 10px 0;">
        <label><strong>OTP Code:</strong></label><br>
        <input type="text" name="otp" placeholder="Enter the 6-digit OTP" required style="width: 300px; padding: 5px;">
    </div>
    <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;">🔍 Test Verification</button>
</form>

<div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">
    <h4>Instructions:</h4>
    <ol>
        <li>Go to the signup enhanced page and register with an email</li>
        <li>Copy the OTP code that was generated</li>
        <li>Come back here and test the verification</li>
        <li>This will show exactly where the verification is failing</li>
    </ol>
</div>
