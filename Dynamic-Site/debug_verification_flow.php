<?php
// Debug verification flow
session_start();
include_once "inc/header.php";
include_once "lib/Database.php";
include_once "classes/PreRegistrationVerification.php";
include_once "classes/EmailOTP.php";

// Initialize classes
$preReg = new PreRegistrationVerification();
$emailOTP = new EmailOTP();
$db = new Database();

// Check if we have email in session
$email = isset($_SESSION['verification_email']) ? $_SESSION['verification_email'] : '';

echo "<h2>Verification Flow Debug</h2>";
echo "<p><strong>Session Email:</strong> " . $email . "</p>";

if ($email) {
    // Check pending verification
    $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' ORDER BY created_at DESC LIMIT 1";
    $pendingResult = $db->select($pendingQuery);
    
    echo "<h3>Pending Verification Records:</h3>";
    if ($pendingResult && $pendingResult->num_rows > 0) {
        while ($row = $pendingResult->fetch_assoc()) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
            echo "<p><strong>Verification Token:</strong> " . $row['verification_token'] . "</p>";
            echo "<p><strong>Created:</strong> " . $row['created_at'] . "</p>";
            echo "<p><strong>Is Used:</strong> " . $row['is_used'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No pending verification records found.</p>";
    }
    
    // Check OTP records
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' ORDER BY created_at DESC LIMIT 3";
    $otpResult = $db->select($otpQuery);
    
    echo "<h3>OTP Records:</h3>";
    if ($otpResult && $otpResult->num_rows > 0) {
        while ($row = $otpResult->fetch_assoc()) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
            echo "<p><strong>OTP:</strong> " . $row['otp'] . "</p>";
            echo "<p><strong>Purpose:</strong> " . $row['purpose'] . "</p>";
            echo "<p><strong>Created:</strong> " . $row['created_at'] . "</p>";
            echo "<p><strong>Expires:</strong> " . $row['expires_at'] . "</p>";
            echo "<p><strong>Is Used:</strong> " . $row['is_used'] . "</p>";
            echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            echo "<p><strong>Nepal Time:</strong> " . NepalTime::now() . "</p>";
            $expired = strtotime($row['expires_at']) < strtotime(NepalTime::now()) ? 'YES' : 'NO';
            echo "<p><strong>Expired?:</strong> " . $expired . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No OTP records found.</p>";
    }
}

// Test form for manual verification
if ($_POST) {
    echo "<h3>Manual Verification Test:</h3>";
    $test_email = $_POST['test_email'] ?? '';
    $test_otp = $_POST['test_otp'] ?? '';
    $test_token = $_POST['test_token'] ?? '';
    
    if ($test_email && $test_otp) {
        echo "<p>Testing OTP verification...</p>";
        $result = $emailOTP->verifyOTP($test_email, $test_otp, 'registration');
        echo "<p><strong>OTP Verification Result:</strong> " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    }
    
    if ($test_email && $test_token) {
        echo "<p>Testing token verification...</p>";
        $result = $preReg->verifyAndCreateAccount($test_email, $test_token);
        echo "<p><strong>Token Verification Result:</strong> " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    }
}
?>

<form method="POST" style="margin: 20px 0; padding: 20px; border: 1px solid #ddd;">
    <h3>Manual Verification Test</h3>
    <div>
        <label>Email:</label><br>
        <input type="email" name="test_email" value="<?php echo $email; ?>" required>
    </div>
    <div style="margin: 10px 0;">
        <label>OTP:</label><br>
        <input type="text" name="test_otp" placeholder="Enter OTP">
    </div>
    <div style="margin: 10px 0;">
        <label>Verification Token:</label><br>
        <input type="text" name="test_token" placeholder="Enter verification token">
    </div>
    <button type="submit">Test Verification</button>
</form>

<?php include_once "inc/footer.php"; ?>
