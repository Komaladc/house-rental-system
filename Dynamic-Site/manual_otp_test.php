<?php
include_once 'config/config.php';
include_once 'config/timezone.php';
include_once 'lib/Database.php';
include_once 'classes/EmailOTP.php';

echo "<h2>🔧 Manual OTP Test</h2>";

if ($_POST) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    
    echo "<h3>Testing OTP for: $email</h3>";
    
    $emailOTP = new EmailOTP();
    $result = $emailOTP->verifyOTP($email, $otp, 'registration');
    
    if ($result) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 5px;'>";
        echo "✅ <strong>OTP VERIFICATION SUCCESSFUL!</strong><br>";
        echo "The error has been fixed. OTP verification is now working correctly.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px;'>";
        echo "❌ <strong>OTP VERIFICATION FAILED</strong><br>";
        echo "The OTP is either invalid, expired, or already used.";
        echo "</div>";
        
        // Show debug info
        $db = new Database();
        $debugQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC LIMIT 3";
        $debugResult = $db->select($debugQuery);
        
        if ($debugResult && $debugResult->num_rows > 0) {
            echo "<h4>Available OTPs for this email:</h4>";
            echo "<table border='1'><tr><th>OTP</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
            while ($row = $debugResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['otp']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "<td>{$row['expires_at']}</td>";
                echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}
?>

<form method="POST">
    <h3>Manual OTP Verification Test</h3>
    <p>Use this to test any email/OTP combination:</p>
    
    <label>Email:</label><br>
    <input type="email" name="email" required style="width: 300px; padding: 5px;"><br><br>
    
    <label>OTP Code:</label><br>
    <input type="text" name="otp" required style="width: 300px; padding: 5px;"><br><br>
    
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
        Test OTP Verification
    </button>
</form>

<hr>

<h3>Current OTP Records</h3>
<?php
// Use Database class instead of global $db
$db = new Database();
$otpQuery = "SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 10";
$otpResult = $db->select($otpQuery);

if ($otpResult && $otpResult->num_rows > 0) {
    echo "<table border='1' style='width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $otpResult->fetch_assoc()) {
        $isExpired = strtotime($row['expires_at']) < time();
        $rowStyle = '';
        if ($row['is_used']) {
            $rowStyle = 'background-color: #f8f9fa;';
        } elseif ($isExpired) {
            $rowStyle = 'background-color: #fff3cd;';
        } else {
            $rowStyle = 'background-color: #d1ecf1;';
        }
        
        echo "<tr style='$rowStyle'>";
        echo "<td>{$row['email']}</td>";
        echo "<td><strong>{$row['otp']}</strong></td>";
        echo "<td>{$row['purpose']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><small>";
    echo "🔵 Blue = Active OTP | ";
    echo "🟡 Yellow = Expired | ";
    echo "⚪ Gray = Used";
    echo "</small></p>";
} else {
    echo "<p>No OTP records found.</p>";
}
?>
