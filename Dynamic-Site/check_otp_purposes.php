<?php
// Quick database check for OTP purposes
include "lib/Database.php";
$db = new Database();

echo "<h1>🔍 OTP Purpose Analysis</h1>";

// Check what purposes are actually in the database
echo "<h2>📊 OTP Purpose Distribution</h2>";
$purposeQuery = "SELECT purpose, COUNT(*) as count FROM tbl_otp GROUP BY purpose ORDER BY count DESC";
$purposeResult = $db->select($purposeQuery);

if ($purposeResult && $purposeResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Purpose</th><th>Count</th></tr>";
    while ($row = $purposeResult->fetch_assoc()) {
        echo "<tr><td><strong>" . $row['purpose'] . "</strong></td><td>" . $row['count'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// Check recent OTPs with their purposes
echo "<h2>📋 Recent OTPs (Last 20)</h2>";
$recentQuery = "SELECT email, otp, purpose, created_at, expires_at, is_used FROM tbl_otp ORDER BY created_at DESC LIMIT 20";
$recentResult = $db->select($recentQuery);

if ($recentResult && $recentResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $recentResult->fetch_assoc()) {
        $isExpired = strtotime($row['expires_at']) < time();
        $rowColor = $row['is_used'] ? '#f8d7da' : ($isExpired ? '#fff3cd' : '#d4edda');
        echo "<tr style='background: $rowColor;'>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td><strong>" . $row['purpose'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_used'] ? '🔒 Used' : ($isExpired ? '⏰ Expired' : '✅ Valid')) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No recent OTP records found</p>";
}

// Check pending verifications
echo "<h2>📋 Pending Verifications (Last 10)</h2>";
$pendingQuery = "SELECT email, otp, created_at, expires_at, is_verified FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 10";
$pendingResult = $db->select($pendingQuery);

if ($pendingResult && $pendingResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP (from pending)</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
    while ($row = $pendingResult->fetch_assoc()) {
        $isExpired = strtotime($row['expires_at']) < time();
        $rowColor = $row['is_verified'] ? '#f8d7da' : ($isExpired ? '#fff3cd' : '#d4edda');
        echo "<tr style='background: $rowColor;'>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_verified'] ? '✅ Verified' : ($isExpired ? '⏰ Expired' : '○ Pending')) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending verification records found</p>";
}

// Test specific email
if (isset($_GET['email'])) {
    $testEmail = mysqli_real_escape_string($db->link, $_GET['email']);
    echo "<h2>🔍 Analysis for: " . htmlspecialchars($testEmail) . "</h2>";
    
    // Check OTPs for this email
    $emailOtpQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' ORDER BY created_at DESC";
    $emailOtpResult = $db->select($emailOtpQuery);
    
    if ($emailOtpResult && $emailOtpResult->num_rows > 0) {
        echo "<h3>📧 OTPs for this email:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th><th>Status</th></tr>";
        while ($row = $emailOtpResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < time();
            $isValid = !$row['is_used'] && !$isExpired;
            echo "<tr>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td><strong>" . $row['purpose'] . "</strong></td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_used'] ? '🔒 Yes' : '○ No') . "</td>";
            echo "<td>" . ($isValid ? '✅ Valid' : ($isExpired ? '⏰ Expired' : '🔒 Used')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check pending verifications for this email
    $emailPendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$testEmail' ORDER BY created_at DESC";
    $emailPendingResult = $db->select($emailPendingQuery);
    
    if ($emailPendingResult && $emailPendingResult->num_rows > 0) {
        echo "<h3>📋 Pending verifications for this email:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th><th>Status</th></tr>";
        while ($row = $emailPendingResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < time();
            $isValid = !$row['is_verified'] && !$isExpired;
            echo "<tr>";
            echo "<td><strong>" . $row['otp'] . "</strong></td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "<td>" . ($row['is_verified'] ? '✅ Yes' : '○ No') . "</td>";
            echo "<td>" . ($isValid ? '✅ Valid' : ($isExpired ? '⏰ Expired' : '✅ Verified')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<form method="GET" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <label for="email"><strong>Check specific email:</strong></label><br>
    <input type="email" name="email" id="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    <button type="submit" style="background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 3px;">Analyze Email</button>
</form>
