<?php
// Security check - only allow admin access
if(!defined('ADMIN_DEBUG_ACCESS') && !isset($_SESSION['admin_debug'])) {
    die('Access denied. Debug files are restricted.');
}

// Debug OTP Insertion with Full Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kathmandu');

echo "<h2>🔧 OTP Insertion Debug</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (Nepal Time)</p>";

try {
    include "lib/Database.php";
    $db = new Database();
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>✅ Database connected successfully</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

function logResult($message, $success = true) {
    $color = $success ? "#d4edda" : "#f8d7da";
    $icon = $success ? "✅" : "❌";
    echo "<div style='background: $color; padding: 10px; margin: 5px 0; border-radius: 5px;'>$icon $message</div>";
}

if ($_POST && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testOTP = sprintf("%06d", mt_rand(100000, 999999));
    
    echo "<h3>🧪 Testing OTP Insertion Process</h3>";
    logResult("Email: $testEmail");
    logResult("Generated OTP: $testOTP");
    
    // Step 1: Check tbl_otp table structure
    echo "<h4>📋 Step 1: Check tbl_otp Table Structure</h4>";
    $structureQuery = "DESCRIBE tbl_otp";
    $structureResult = $db->select($structureQuery);
    
    if ($structureResult) {
        logResult("Table structure retrieved successfully");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; background: white;'>";
        echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($column = mysqli_fetch_assoc($structureResult)) {
            echo "<tr>";
            echo "<td><strong>" . $column['Field'] . "</strong></td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        logResult("Failed to get table structure: " . mysqli_error($db->link), false);
    }
    
    // Step 2: Delete old OTPs
    echo "<h4>🗑️ Step 2: Delete Old OTPs</h4>";
    $deleteQuery = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification'";
    echo "<code style='background: #f8f9fa; padding: 5px; display: block; margin: 5px 0;'>$deleteQuery</code>";
    
    $deleteResult = $db->delete($deleteQuery);
    if ($deleteResult !== false) {
        logResult("Old OTPs deleted successfully (affected rows: " . mysqli_affected_rows($db->link) . ")");
    } else {
        logResult("Delete failed: " . mysqli_error($db->link), false);
    }
    
    // Step 3: Insert new OTP
    echo "<h4>📝 Step 3: Insert New OTP</h4>";
    $currentTime = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+20 minutes'));
    
    $insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                   VALUES ('" . mysqli_real_escape_string($db->link, $testEmail) . "', 
                          '$testOTP', 
                          'email_verification', 
                          '$currentTime', 
                          '$expiresAt',
                          0)";
    
    echo "<code style='background: #f8f9fa; padding: 5px; display: block; margin: 5px 0; font-size: 12px;'>$insertQuery</code>";
    
    $insertResult = $db->insert($insertQuery);
    if ($insertResult) {
        logResult("OTP inserted successfully! Insert ID: $insertResult");
    } else {
        logResult("INSERT FAILED! MySQL Error: " . mysqli_error($db->link), false);
        logResult("MySQL Error Number: " . mysqli_errno($db->link), false);
        
        // Try to identify the specific issue
        if (mysqli_errno($db->link) == 1146) {
            logResult("Error 1146: Table 'tbl_otp' doesn't exist!", false);
        } else if (mysqli_errno($db->link) == 1054) {
            logResult("Error 1054: Unknown column in field list!", false);
        } else if (mysqli_errno($db->link) == 1062) {
            logResult("Error 1062: Duplicate entry!", false);
        }
    }
    
    // Step 4: Verify insertion
    echo "<h4>🔍 Step 4: Verify Insertion</h4>";
    $verifyQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
    echo "<code style='background: #f8f9fa; padding: 5px; display: block; margin: 5px 0; font-size: 12px;'>$verifyQuery</code>";
    
    $verifyResult = $db->select($verifyQuery);
    if ($verifyResult && mysqli_num_rows($verifyResult) > 0) {
        $otpData = mysqli_fetch_assoc($verifyResult);
        logResult("✅ OTP found in database!");
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; background: white;'>";
        echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Value</th></tr>";
        foreach ($otpData as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        // Test verification logic
        $now = date('Y-m-d H:i:s');
        if ($otpData['otp'] === $testOTP && $otpData['expires_at'] > $now && $otpData['is_used'] == 0) {
            logResult("🎯 Verification Logic Test: ✅ SUCCESS");
        } else {
            logResult("🎯 Verification Logic Test: ❌ FAILED", false);
            logResult("OTP Match: " . ($otpData['otp'] === $testOTP ? 'YES' : 'NO'), false);
            logResult("Not Expired: " . ($otpData['expires_at'] > $now ? 'YES' : 'NO'), false);
            logResult("Not Used: " . ($otpData['is_used'] == 0 ? 'YES' : 'NO'), false);
        }
        
    } else {
        logResult("❌ OTP NOT FOUND after insertion!", false);
        logResult("Select Error: " . mysqli_error($db->link), false);
    }
    
    // Step 5: Test using Database class methods
    echo "<h4>🧪 Step 5: Test Database Class Methods</h4>";
    echo "<p>Testing if the issue is with the Database class insert method...</p>";
    
    // Try direct mysqli insert
    $directQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                   VALUES ('" . mysqli_real_escape_string($db->link, $testEmail . "_direct") . "', 
                          '$testOTP', 
                          'email_verification', 
                          '$currentTime', 
                          '$expiresAt',
                          0)";
    
    $directResult = mysqli_query($db->link, $directQuery);
    if ($directResult) {
        logResult("Direct mysqli_query: ✅ SUCCESS");
    } else {
        logResult("Direct mysqli_query: ❌ FAILED - " . mysqli_error($db->link), false);
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { margin: 15px 0; font-size: 14px; }
    th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
    form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
    input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
    button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
    code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 12px; }
</style>

<form method="POST">
    <h4>🔧 Debug OTP Insertion</h4>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit">🔍 Debug Insertion Process</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
    <h4>🎯 Debug Purpose:</h4>
    <p>This tool will:</p>
    <ol>
        <li>Check the exact tbl_otp table structure</li>
        <li>Test the delete operation</li>
        <li>Test the insert operation with full error reporting</li>
        <li>Verify the data was actually inserted</li>
        <li>Compare Database class vs direct mysqli calls</li>
    </ol>
    <p><strong>This will identify the exact SQL error causing the insertion failure.</strong></p>
</div>
