<?php
// Database structure verification
include "lib/Database.php";
$db = new Database();

echo "<h1>🔍 Database Structure Verification</h1>";

// Check tbl_otp structure
echo "<h2>📋 tbl_otp Table Structure</h2>";
$otpStructure = $db->select("DESCRIBE tbl_otp");
if ($otpStructure && $otpStructure->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $otpStructure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ tbl_otp table not found!</div>";
}

// Check tbl_pending_verification structure
echo "<h2>📋 tbl_pending_verification Table Structure</h2>";
$pendingStructure = $db->select("DESCRIBE tbl_pending_verification");
if ($pendingStructure && $pendingStructure->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $pendingStructure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ tbl_pending_verification table not found!</div>";
}

// Check tbl_user structure (key columns)
echo "<h2>📋 tbl_user Table Key Columns</h2>";
$userStructure = $db->select("DESCRIBE tbl_user");
if ($userStructure && $userStructure->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $userStructure->fetch_assoc()) {
        // Only show key columns for verification
        if (in_array($row['Field'], ['userId', 'userEmail', 'email_verified', 'verification_token', 'verification_status', 'requires_verification', 'document_verified'])) {
            echo "<tr>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ tbl_user table not found!</div>";
}

// Check recent OTP data
echo "<h2>📊 Recent OTP Data (Last 10 records)</h2>";
$recentOtp = $db->select("SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 10");
if ($recentOtp && $recentOtp->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Is Used</th></tr>";
    while ($row = $recentOtp->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['purpose'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_used'] ? '✅ Yes' : '○ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>⚠️ No OTP records found</div>";
}

// Check recent pending verifications
echo "<h2>📊 Recent Pending Verifications (Last 10 records)</h2>";
$recentPending = $db->select("SELECT email, verification_token, otp, created_at, expires_at, is_verified FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 10");
if ($recentPending && $recentPending->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>Token (First 10)</th><th>OTP</th><th>Created</th><th>Expires</th><th>Verified</th></tr>";
    while ($row = $recentPending->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . substr($row['verification_token'], 0, 10) . "...</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . ($row['is_verified'] ? '✅ Yes' : '○ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>⚠️ No pending verification records found</div>";
}

// Test database connection and operations
echo "<h2>🔧 Database Connection Test</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<strong>Connection Status:</strong> " . ($db->link ? '✅ Connected' : '❌ Failed') . "<br>";

if ($db->link) {
    echo "<strong>MySQL Version:</strong> " . mysqli_get_server_info($db->link) . "<br>";
    
    // Test insert/select/delete
    $testEmail = "test@example.com";
    $testOTP = "123456";
    
    // Insert test OTP
    $testInsert = "INSERT INTO tbl_otp (email, otp, purpose, expires_at, created_at, is_used) 
                   VALUES ('$testEmail', '$testOTP', 'test', DATE_ADD(NOW(), INTERVAL 20 MINUTE), NOW(), 0)";
    $insertResult = $db->insert($testInsert);
    echo "<strong>Test Insert:</strong> " . ($insertResult ? '✅ Success' : '❌ Failed') . "<br>";
    
    if ($insertResult) {
        // Test select
        $testSelect = "SELECT * FROM tbl_otp WHERE email = '$testEmail' AND otp = '$testOTP'";
        $selectResult = $db->select($testSelect);
        echo "<strong>Test Select:</strong> " . ($selectResult && $selectResult->num_rows > 0 ? '✅ Success' : '❌ Failed') . "<br>";
        
        // Clean up test data
        $testDelete = "DELETE FROM tbl_otp WHERE email = '$testEmail' AND otp = '$testOTP'";
        $deleteResult = $db->delete($testDelete);
        echo "<strong>Test Delete:</strong> " . ($deleteResult ? '✅ Success' : '❌ Failed') . "<br>";
    }
}
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
