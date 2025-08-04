<?php
// Fix OTP purpose consistency
include "lib/Database.php";
$db = new Database();

echo "<h1>🔧 OTP Purpose Consistency Fix</h1>";

// Step 1: Check current purposes in database
echo "<h2>Step 1: Current OTP Purposes in Database</h2>";
$purposeCheck = $db->select("SELECT purpose, COUNT(*) as count FROM tbl_otp GROUP BY purpose");
if ($purposeCheck && $purposeCheck->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Purpose</th><th>Count</th></tr>";
    while ($row = $purposeCheck->fetch_assoc()) {
        echo "<tr><td><strong>" . $row['purpose'] . "</strong></td><td>" . $row['count'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// Step 2: Fix any 'email_verification' purposes to 'registration'
echo "<h2>Step 2: Fixing Purpose Inconsistencies</h2>";
$updateQuery = "UPDATE tbl_otp SET purpose = 'registration' WHERE purpose = 'email_verification'";
$updateResult = $db->update($updateQuery);

if ($updateResult) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Updated email_verification purposes to registration</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>⚠️ No records needed updating or update failed</div>";
}

// Step 3: Check purposes again
echo "<h2>Step 3: Purposes After Fix</h2>";
$purposeCheckAfter = $db->select("SELECT purpose, COUNT(*) as count FROM tbl_otp GROUP BY purpose");
if ($purposeCheckAfter && $purposeCheckAfter->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Purpose</th><th>Count</th></tr>";
    while ($row = $purposeCheckAfter->fetch_assoc()) {
        echo "<tr><td><strong>" . $row['purpose'] . "</strong></td><td>" . $row['count'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// Step 4: Test OTP verification with a real example
if (isset($_POST['test_verification'])) {
    include "classes/EmailOTP.php";
    include "classes/PreRegistrationVerification.php";
    
    $emailOTP = new EmailOTP();
    $preReg = new PreRegistrationVerification();
    
    $testEmail = $_POST['test_email'];
    $testOTP = $_POST['test_otp'];
    
    echo "<h2>Step 4: Testing OTP Verification</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Testing Email:</strong> $testEmail<br>";
    echo "<strong>Testing OTP:</strong> $testOTP<br>";
    echo "</div>";
    
    // Test with 'registration' purpose (correct)
    echo "<h3>Testing with purpose='registration'</h3>";
    $verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
    
    if ($verifyResult) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ OTP verification SUCCESS with purpose='registration'!</div>";
        
        // Reset the OTP for further testing
        $resetQuery = "UPDATE tbl_otp SET is_used = 0 WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "'";
        $db->update($resetQuery);
        echo "<p><em>OTP reset for further testing...</em></p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ OTP verification FAILED with purpose='registration'</div>";
    }
    
    // Show what OTPs exist for this email
    echo "<h3>OTPs for this email:</h3>";
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' ORDER BY created_at DESC";
    $otpResult = $db->select($otpQuery);
    
    if ($otpResult && $otpResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Is Used</th></tr>";
        while ($row = $otpResult->fetch_assoc()) {
            $highlight = ($row['otp'] == $testOTP) ? "background: #fff3cd;" : "";
            echo "<tr style='$highlight'>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['purpose'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_used'] ? '🔒 Used' : '✅ Available') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No OTPs found for this email</p>";
    }
    
    // Test complete verification flow
    echo "<h3>Testing Complete Verification Flow</h3>";
    $completeResult = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
    
    if ($completeResult['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>🎉 COMPLETE VERIFICATION SUCCESS!</div>";
        echo "<p>" . strip_tags($completeResult['message']) . "</p>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ COMPLETE VERIFICATION FAILED</div>";
        echo "<p>" . strip_tags($completeResult['message']) . "</p>";
    }
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>🧪 Test OTP Verification After Fix</h3>
    <label for="test_email"><strong>Email:</strong></label><br>
    <input type="email" name="test_email" id="test_email" value="bistakaran89@gmail.com" style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    
    <label for="test_otp"><strong>OTP Code:</strong></label><br>
    <input type="text" name="test_otp" id="test_otp" value="" style="width: 100px; padding: 5px; margin: 5px 0;" placeholder="Enter OTP"><br>
    
    <button type="submit" name="test_verification" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">🔍 Test Verification</button>
</form>
