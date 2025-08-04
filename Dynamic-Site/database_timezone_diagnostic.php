<?php
// Database and timezone diagnostic
include "lib/Database.php";
include "config/timezone.php";

$db = new Database();

echo "<h1>🔍 Database & Timezone Diagnostic</h1>";

// Check database connection
echo "<h2>💾 Database Connection</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<strong>Connection Status:</strong> " . ($db->link ? '✅ Connected' : '❌ Failed') . "<br>";
if ($db->link) {
    echo "<strong>MySQL Version:</strong> " . mysqli_get_server_info($db->link) . "<br>";
    echo "<strong>Character Set:</strong> " . mysqli_character_set_name($db->link) . "<br>";
}
echo "</div>";

// Check timezone settings
echo "<h2>⏰ Timezone Settings</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<strong>PHP Default Timezone:</strong> " . date_default_timezone_get() . "<br>";
echo "<strong>PHP Current Time:</strong> " . date('Y-m-d H:i:s') . "<br>";

if (class_exists('NepalTime')) {
    echo "<strong>NepalTime Class:</strong> ✅ Available<br>";
    echo "<strong>NepalTime::now():</strong> " . NepalTime::now() . "<br>";
} else {
    echo "<strong>NepalTime Class:</strong> ❌ Missing<br>";
}

// Check MySQL timezone
$mysqlTimeResult = $db->select("SELECT NOW() as mysql_time, UTC_TIMESTAMP() as utc_time");
if ($mysqlTimeResult && $mysqlTimeResult->num_rows > 0) {
    $timeData = $mysqlTimeResult->fetch_assoc();
    echo "<strong>MySQL NOW():</strong> " . $timeData['mysql_time'] . "<br>";
    echo "<strong>MySQL UTC:</strong> " . $timeData['utc_time'] . "<br>";
}
echo "</div>";

// Check OTP table structure and data
echo "<h2>📋 OTP Table Analysis</h2>";

// Table structure
$structure = $db->select("DESCRIBE tbl_otp");
if ($structure && $structure->num_rows > 0) {
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Could not get tbl_otp structure</div>";
}

// Recent OTP data with time analysis
echo "<h3>Recent OTP Data with Time Analysis:</h3>";
$recentOtps = $db->select("
    SELECT *, 
           NOW() as current_mysql_time,
           CASE 
               WHEN expires_at > NOW() AND is_used = 0 THEN 'Valid'
               WHEN expires_at <= NOW() THEN 'Expired'
               WHEN is_used = 1 THEN 'Used'
               ELSE 'Unknown'
           END as status
    FROM tbl_otp 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($recentOtps && $recentOtps->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Current</th><th>Used</th><th>Status</th></tr>";
    while ($row = $recentOtps->fetch_assoc()) {
        $statusColor = ($row['status'] == 'Valid') ? '#d4edda' : (($row['status'] == 'Expired') ? '#fff3cd' : '#f8d7da');
        echo "<tr style='background: $statusColor;'>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['purpose'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "<td>" . $row['current_mysql_time'] . "</td>";
        echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
        echo "<td><strong>" . $row['status'] . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// Test OTP operations
echo "<h2>🧪 OTP Operations Test</h2>";

if (isset($_POST['test_otp_ops'])) {
    $testEmail = "diagnostic@test.com";
    $testOTP = "123456";
    
    echo "<h3>Testing OTP Operations for: $testEmail</h3>";
    
    // Clean existing
    $deleteResult = $db->delete("DELETE FROM tbl_otp WHERE email = '$testEmail'");
    echo "<p>1. Clean existing: " . ($deleteResult ? '✅ Success' : '❌ Failed') . "</p>";
    
    // Insert test OTP using direct SQL
    $currentTime = date('Y-m-d H:i:s');
    $expiryTime = date('Y-m-d H:i:s', strtotime('+20 minutes'));
    
    $insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                    VALUES ('$testEmail', '$testOTP', 'registration', '$currentTime', '$expiryTime', 0)";
    $insertResult = $db->insert($insertQuery);
    echo "<p>2. Insert OTP: " . ($insertResult ? '✅ Success' : '❌ Failed') . "</p>";
    
    if ($insertResult) {
        // Test select
        $selectQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' AND otp = '$testOTP'";
        $selectResult = $db->select($selectQuery);
        
        if ($selectResult && $selectResult->num_rows > 0) {
            echo "<p>3. Select OTP: ✅ Success</p>";
            $otpData = $selectResult->fetch_assoc();
            
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($otpData as $key => $value) {
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
            echo "</table>";
            
            // Test verification query
            $verifyQuery = "SELECT * FROM tbl_otp 
                           WHERE email = '$testEmail' 
                           AND otp = '$testOTP' 
                           AND purpose = 'registration' 
                           AND expires_at > NOW() 
                           AND is_used = 0";
            $verifyResult = $db->select($verifyQuery);
            
            echo "<p>4. Verification Query: " . ($verifyResult && $verifyResult->num_rows > 0 ? '✅ Match Found' : '❌ No Match') . "</p>";
            
            if ($verifyResult && $verifyResult->num_rows > 0) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ OTP operations working correctly!</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Verification query failed - possible timezone or data issue</div>";
            }
            
        } else {
            echo "<p>3. Select OTP: ❌ Failed</p>";
        }
        
        // Clean up
        $db->delete("DELETE FROM tbl_otp WHERE email = '$testEmail'");
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
    <h3>🧪 Test OTP Database Operations</h3>
    <button type="submit" name="test_otp_ops" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Run OTP Operations Test</button>
</form>
