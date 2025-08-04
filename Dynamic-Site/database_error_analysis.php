<?php
// Test Database Operations with Better Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kathmandu');

echo "<h2>🔧 Database Class Error Analysis</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (Nepal Time)</p>";

// Test the actual Database class
include "lib/Database.php";
$db = new Database();

function logResult($message, $success = true) {
    $color = $success ? "#d4edda" : "#f8d7da";
    $icon = $success ? "✅" : "❌";
    echo "<div style='background: $color; padding: 10px; margin: 5px 0; border-radius: 5px;'>$icon $message</div>";
}

if ($_POST && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testOTP = sprintf("%06d", mt_rand(100000, 999999));
    
    echo "<h3>🧪 Testing Database Class Insert Method</h3>";
    logResult("Testing with Email: $testEmail");
    logResult("Generated OTP: $testOTP");
    
    // Step 1: Test table existence
    echo "<h4>📋 Step 1: Check if tbl_otp exists</h4>";
    try {
        $checkTableQuery = "SHOW TABLES LIKE 'tbl_otp'";
        $tableResult = $db->select($checkTableQuery);
        if ($tableResult) {
            logResult("tbl_otp table exists");
        } else {
            logResult("tbl_otp table does NOT exist!", false);
        }
    } catch (Exception $e) {
        logResult("Error checking table: " . $e->getMessage(), false);
    }
    
    // Step 2: Check table structure
    echo "<h4>📋 Step 2: Check tbl_otp structure</h4>";
    try {
        $structureQuery = "DESCRIBE tbl_otp";
        $structureResult = $db->select($structureQuery);
        if ($structureResult) {
            logResult("Table structure retrieved");
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
            logResult("Failed to get table structure", false);
        }
    } catch (Exception $e) {
        logResult("Error getting structure: " . $e->getMessage(), false);
    }
    
    // Step 3: Test manual connection without Database class
    echo "<h4>🔧 Step 3: Test Direct MySQL Connection</h4>";
    include "config/config.php";
    
    $directConn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($directConn->connect_error) {
        logResult("Direct connection failed: " . $directConn->connect_error, false);
    } else {
        logResult("Direct connection successful");
        
        // Test direct insert
        $currentTime = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+20 minutes'));
        
        $directInsertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                             VALUES (?, ?, 'email_verification', ?, ?, 0)";
        
        $stmt = $directConn->prepare($directInsertQuery);
        if ($stmt) {
            $stmt->bind_param("ssss", $testEmail, $testOTP, $currentTime, $expiresAt);
            if ($stmt->execute()) {
                logResult("✅ Direct prepared statement insert: SUCCESS");
                logResult("Insert ID: " . $directConn->insert_id);
                
                // Verify the insert
                $verifyQuery = "SELECT * FROM tbl_otp WHERE email = ? AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
                $verifyStmt = $directConn->prepare($verifyQuery);
                $verifyStmt->bind_param("s", $testEmail);
                $verifyStmt->execute();
                $verifyResult = $verifyStmt->get_result();
                
                if ($verifyResult->num_rows > 0) {
                    $otpData = $verifyResult->fetch_assoc();
                    logResult("✅ OTP verified in database: " . $otpData['otp']);
                } else {
                    logResult("❌ OTP not found after insert", false);
                }
                
            } else {
                logResult("❌ Direct prepared statement insert: FAILED", false);
                logResult("Error: " . $stmt->error, false);
            }
        } else {
            logResult("❌ Failed to prepare statement", false);
            logResult("Error: " . $directConn->error, false);
        }
        
        $directConn->close();
    }
    
    // Step 4: Test Database class insert with error capture
    echo "<h4>🧪 Step 4: Test Database Class Insert</h4>";
    
    // Delete old entries first
    $deleteQuery = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail . "_dbclass") . "' AND purpose = 'email_verification'";
    try {
        $deleteResult = $db->delete($deleteQuery);
        logResult("Delete query executed");
    } catch (Exception $e) {
        logResult("Delete failed: " . $e->getMessage(), false);
    }
    
    // Try insert with Database class
    $currentTime = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+20 minutes'));
    
    $dbClassInsertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                          VALUES ('" . mysqli_real_escape_string($db->link, $testEmail . "_dbclass") . "', 
                                 '$testOTP', 
                                 'email_verification', 
                                 '$currentTime', 
                                 '$expiresAt',
                                 0)";
    
    echo "<code style='background: #f8f9fa; padding: 5px; display: block; margin: 5px 0; font-size: 12px;'>$dbClassInsertQuery</code>";
    
    try {
        $dbInsertResult = $db->insert($dbClassInsertQuery);
        if ($dbInsertResult) {
            logResult("✅ Database class insert: SUCCESS");
        } else {
            logResult("❌ Database class insert: FAILED", false);
        }
    } catch (Exception $e) {
        logResult("Database class insert exception: " . $e->getMessage(), false);
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
    <h4>🔧 Test Database Operations</h4>
    <label>Email Address:</label><br>
    <input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
    <button type="submit">🔍 Run Database Tests</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
    <h4>🎯 Analysis Purpose:</h4>
    <p>This will test:</p>
    <ol>
        <li>Whether tbl_otp table exists</li>
        <li>Table structure and columns</li>
        <li>Direct MySQL operations (bypassing Database class)</li>
        <li>Database class insert method behavior</li>
        <li>Error handling and reporting</li>
    </ol>
    <p><strong>This will identify if the issue is with the Database class or the table itself.</strong></p>
</div>
