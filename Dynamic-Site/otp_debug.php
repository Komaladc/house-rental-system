<?php
// Debug OTP issue for enhanced signup
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "lib/Database.php";

$db = new Database();

echo "<h2>🔍 OTP Debug Analysis</h2>";

// 1. Check if tbl_otp table exists
echo "<h3>1. Check tbl_otp Table Structure</h3>";
$tableCheck = $db->link->query("DESCRIBE tbl_otp");
if ($tableCheck) {
    echo "<p style='color: green;'>✅ tbl_otp table exists</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $tableCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ tbl_otp table does not exist!</p>";
    echo "<p>Creating tbl_otp table...</p>";
    
    $createTable = "CREATE TABLE tbl_otp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        purpose VARCHAR(50) DEFAULT 'registration',
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_otp (otp),
        INDEX idx_purpose (purpose),
        INDEX idx_expires (expires_at)
    )";
    
    if ($db->link->query($createTable)) {
        echo "<p style='color: green;'>✅ Created tbl_otp table successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create tbl_otp table: " . $db->link->error . "</p>";
    }
}

// 2. Check if tbl_pending_verification table exists
echo "<h3>2. Check tbl_pending_verification Table</h3>";
$pendingTableCheck = $db->link->query("DESCRIBE tbl_pending_verification");
if ($pendingTableCheck) {
    echo "<p style='color: green;'>✅ tbl_pending_verification table exists</p>";
} else {
    echo "<p style='color: red;'>❌ tbl_pending_verification table does not exist!</p>";
    echo "<p>Creating tbl_pending_verification table...</p>";
    
    $createPendingTable = "CREATE TABLE tbl_pending_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        verification_token VARCHAR(255) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        registration_data TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        is_verified TINYINT(1) DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_token (verification_token),
        INDEX idx_expires (expires_at)
    )";
    
    if ($db->link->query($createPendingTable)) {
        echo "<p style='color: green;'>✅ Created tbl_pending_verification table successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create tbl_pending_verification table: " . $db->link->error . "</p>";
    }
}

// 3. Test OTP generation and storage
echo "<h3>3. Test OTP Generation</h3>";
include "classes/EmailOTP.php";
$emailOTP = new EmailOTP();

$testEmail = "test@example.com";
$testOTP = $emailOTP->generateOTP();
echo "<p>Generated OTP: <strong>$testOTP</strong></p>";

// 4. Check current OTPs in table
echo "<h3>4. Current OTP Records</h3>";
$otpQuery = "SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 10";
$otpResult = $db->select($otpQuery);

if ($otpResult && $otpResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $otpResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td><strong>{$row['otp']}</strong></td>";
        echo "<td>{$row['purpose']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>" . ($row['is_used'] ? '✅ Used' : '⏳ Available') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

// 5. Check timezone settings
echo "<h3>5. Timezone Information</h3>";
echo "<p>PHP Timezone: " . date_default_timezone_get() . "</p>";
echo "<p>Current PHP Time: " . date('Y-m-d H:i:s') . "</p>";

// Check Nepal time class
include "config/timezone.php";
if (class_exists('NepalTime')) {
    echo "<p>Nepal Time Now: " . NepalTime::now() . "</p>";
    echo "<p>Nepal Time +20 min: " . NepalTime::addMinutes(20) . "</p>";
} else {
    echo "<p style='color: red;'>❌ NepalTime class not available</p>";
}

echo "<h3>6. Manual OTP Test</h3>";
echo "<form method='POST'>";
echo "<p>Test Email: <input type='email' name='test_email' value='dipesh@example.com' required></p>";
echo "<p><button type='submit' name='create_test_otp'>Create Test OTP</button></p>";
echo "</form>";

if (isset($_POST['create_test_otp'])) {
    $testEmail = $_POST['test_email'];
    
    // Delete existing OTPs
    $deleteOld = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "'";
    $db->delete($deleteOld);
    
    // Create new OTP
    $newOTP = $emailOTP->generateOTP();
    $currentTime = date('Y-m-d H:i:s');
    $expiryTime = date('Y-m-d H:i:s', strtotime('+20 minutes'));
    
    $insertOTP = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                  VALUES ('" . mysqli_real_escape_string($db->link, $testEmail) . "', 
                          '$newOTP', 
                          'registration', 
                          '$currentTime', 
                          '$expiryTime',
                          0)";
    
    if ($db->insert($insertOTP)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p style='color: #155724;'><strong>✅ Test OTP Created Successfully!</strong></p>";
        echo "<p>Email: <strong>$testEmail</strong></p>";
        echo "<p>OTP: <strong>$newOTP</strong></p>";
        echo "<p>Expires: <strong>$expiryTime</strong></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create test OTP: " . $db->link->error . "</p>";
    }
}

echo "<p><a href='signup_enhanced.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Back to Signup</a></p>";
?>
