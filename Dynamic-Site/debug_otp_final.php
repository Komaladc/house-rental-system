<?php
// Set Nepal timezone first
include "config/timezone.php";
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

// Create database connection
$db = new Database();

echo "<h1>🔍 OTP Verification Debug</h1>";

// Check recent OTP entries
echo "<h3>Recent OTP Entries:</h3>";
$otpQuery = "SELECT email, otp, purpose, created_at, expires_at, is_used FROM tbl_otp WHERE purpose = 'registration' ORDER BY created_at DESC LIMIT 5";
$otpResult = $db->select($otpQuery);

if ($otpResult && $otpResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $otpResult->fetch_assoc()) {
        $isExpired = (strtotime($row['expires_at']) < time()) ? 'EXPIRED' : 'VALID';
        $isUsed = $row['is_used'] ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . " ($isExpired)</td>";
        echo "<td>$isUsed</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No OTP entries found.<br>";
}

echo "<h3>Recent Pending Verifications:</h3>";
$pendingQuery = "SELECT email, verification_token, created_at, expires_at, is_verified FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5";
$pendingResult = $db->select($pendingQuery);

if ($pendingResult && $pendingResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>Token (first 10 chars)</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
    while ($row = $pendingResult->fetch_assoc()) {
        $isExpired = (strtotime($row['expires_at']) < time()) ? 'EXPIRED' : 'VALID';
        $isVerified = $row['is_verified'] ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . substr($row['verification_token'], 0, 10) . "...</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . " ($isExpired)</td>";
        echo "<td>$isVerified</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No pending verifications found.<br>";
}

echo "<h3>Current Nepal Time:</h3>";
echo "Nepal Time: " . NepalTime::now() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";

// Test OTP verification
if (isset($_GET['test_email']) && isset($_GET['test_otp'])) {
    echo "<h3>Testing OTP Verification:</h3>";
    $testEmail = $_GET['test_email'];
    $testOTP = $_GET['test_otp'];
    
    $preReg = new PreRegistrationVerification();
    $result = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
    
    echo "<strong>Test Result:</strong><br>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}

echo "<br><h3>📝 Test Instructions:</h3>";
echo "1. Register with an email and note the OTP<br>";
echo "2. Check the tables above to see if OTP was stored correctly<br>";
echo "3. Use this URL to test: ?test_email=YOUR_EMAIL&test_otp=YOUR_OTP<br>";
echo "<br><a href='signup_enhanced.php'>→ Back to Signup Form</a>";
?>
