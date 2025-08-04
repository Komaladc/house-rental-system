<?php
require_once 'config/config.php';

echo "<h2>🗃️ Database Schema Check</h2>";

// Check tbl_otp structure
echo "<h3>tbl_otp Structure:</h3>";
$otpStructure = $db->select("DESCRIBE tbl_otp");
if ($otpStructure) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $otpStructure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Could not retrieve tbl_otp structure</p>";
}

// Check tbl_pending_verification structure
echo "<h3>tbl_pending_verification Structure:</h3>";
$pendingStructure = $db->select("DESCRIBE tbl_pending_verification");
if ($pendingStructure) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $pendingStructure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Could not retrieve tbl_pending_verification structure</p>";
}

// Check tbl_user structure
echo "<h3>tbl_user Structure:</h3>";
$userStructure = $db->select("DESCRIBE tbl_user");
if ($userStructure) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $userStructure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Could not retrieve tbl_user structure</p>";
}

echo "<h3>📊 Data Sample Check:</h3>";

// Check recent data in all tables
echo "<h4>Recent tbl_otp entries:</h4>";
$recentOTP = $db->select("SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 5");
if ($recentOTP && $recentOTP->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;font-size:12px;'>";
    echo "<tr><th>ID</th><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $recentOTP->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['otp']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
        echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP entries found</p>";
}

echo "<h4>Recent tbl_pending_verification entries:</h4>";
$recentPending = $db->select("SELECT * FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5");
if ($recentPending && $recentPending->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;font-size:12px;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Token</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
    while ($row = $recentPending->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['verification_token'], 0, 20)) . "...</td>";
        echo "<td><strong>" . htmlspecialchars($row['otp']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
        echo "<td>" . ($row['is_verified'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending verification entries found</p>";
}

echo "<h4>Recent tbl_user entries:</h4>";
$recentUsers = $db->select("SELECT userId, firstName, lastName, userEmail, email_verified, userLevel FROM tbl_user ORDER BY userId DESC LIMIT 5");
if ($recentUsers && $recentUsers->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Verified</th><th>Level</th></tr>";
    while ($row = $recentUsers->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['userId']) . "</td>";
        echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userEmail']) . "</td>";
        echo "<td>" . ($row['email_verified'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . htmlspecialchars($row['userLevel']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No user entries found</p>";
}

echo "<hr>";
echo "<p><a href='debug_otp_verification.php'>← OTP Debug Tool</a></p>";
echo "<p><a href='signup_with_verification.php'>← Signup Form</a></p>";
?>
