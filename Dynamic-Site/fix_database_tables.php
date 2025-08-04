<?php
require_once 'lib/Database.php';

$db = new Database();

echo "<h1>🔧 Database Table Fix Tool</h1>";

echo "<h2>Step 1: Checking Current Database State</h2>";

// Check if tbl_user_verification exists
$checkTable = $db->select("SHOW TABLES LIKE 'tbl_user_verification'");
if (!$checkTable || $checkTable->num_rows == 0) {
    echo "<p style='color:red;'>❌ tbl_user_verification table does not exist!</p>";
    echo "<p><strong>Creating the table now...</strong></p>";
    
    $createTable = "CREATE TABLE tbl_user_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        userName VARCHAR(255) NOT NULL,
        user_level INT NOT NULL,
        user_type VARCHAR(50) NOT NULL,
        citizenship_id VARCHAR(100) DEFAULT NULL,
        citizenship_front VARCHAR(255) DEFAULT NULL,
        citizenship_back VARCHAR(255) DEFAULT NULL,
        business_license VARCHAR(255) DEFAULT NULL,
        verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        admin_comments TEXT DEFAULT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL DEFAULT NULL,
        reviewed_by INT DEFAULT NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_email (email),
        INDEX idx_status (verification_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    // Disable foreign key checks temporarily
    $db->select("SET foreign_key_checks = 0");
    
    if ($db->select($createTable)) {
        echo "<p style='color:green;'>✅ Table created successfully!</p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to create table. Error: " . mysqli_error($db->link) . "</p>";
    }
    
    // Re-enable foreign key checks
    $db->select("SET foreign_key_checks = 1");
    
} else {
    echo "<p style='color:green;'>✅ tbl_user_verification table exists</p>";
    
    // Check table structure
    echo "<h3>Current table structure:</h3>";
    $result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
    $columns = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    }
    
    // Check if userName column exists
    if (!in_array('userName', $columns)) {
        echo "<p style='color:orange;'>⚠️ userName column is missing!</p>";
        
        if (in_array('username', $columns)) {
            echo "<p><strong>Renaming 'username' to 'userName'...</strong></p>";
            $alterQuery = "ALTER TABLE tbl_user_verification CHANGE username userName VARCHAR(255) NOT NULL";
        } else {
            echo "<p><strong>Adding userName column...</strong></p>";
            $alterQuery = "ALTER TABLE tbl_user_verification ADD COLUMN userName VARCHAR(255) NOT NULL AFTER email";
        }
        
        if ($db->select($alterQuery)) {
            echo "<p style='color:green;'>✅ userName column fixed successfully!</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to fix userName column. Error: " . mysqli_error($db->link) . "</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ userName column exists!</p>";
    }
}

echo "<h2>Step 2: Verifying Other Required Tables</h2>";

// Check other required tables
$requiredTables = [
    'tbl_pending_verification' => "CREATE TABLE tbl_pending_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        registration_data TEXT NOT NULL,
        otp VARCHAR(6) NOT NULL,
        verification_token VARCHAR(64) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_verified BOOLEAN DEFAULT FALSE,
        INDEX idx_email (email),
        INDEX idx_token (verification_token),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'tbl_otp' => "CREATE TABLE tbl_otp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp VARCHAR(6) NOT NULL,
        purpose VARCHAR(50) NOT NULL DEFAULT 'registration',
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_used BOOLEAN DEFAULT FALSE,
        INDEX idx_email_otp (email, otp),
        INDEX idx_purpose (purpose),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($requiredTables as $tableName => $createSQL) {
    $checkTable = $db->select("SHOW TABLES LIKE '$tableName'");
    if (!$checkTable || $checkTable->num_rows == 0) {
        echo "<p style='color:orange;'>⚠️ $tableName does not exist. Creating...</p>";
        
        if ($db->select($createSQL)) {
            echo "<p style='color:green;'>✅ $tableName created successfully!</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to create $tableName. Error: " . mysqli_error($db->link) . "</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ $tableName exists</p>";
    }
}

echo "<h2>Step 3: Final Verification</h2>";

echo "<h3>Final tbl_user_verification structure:</h3>";
$result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $highlight = ($row['Field'] === 'userName') ? 'style="background:yellow; font-weight:bold;"' : '';
        echo "<div $highlight>- " . $row['Field'] . " (" . $row['Type'] . ")</div>";
    }
} else {
    echo "<p style='color:red;'>❌ Could not retrieve table structure</p>";
}

echo "<hr>";
echo "<h2>🎯 Ready to Test!</h2>";
echo "<p>The database tables have been fixed. You can now try the verification process again.</p>";
echo "<p><a href='verify_registration.php?email=thekomalad%40gmail.com' style='background:#28a745; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-size:18px;'>🔄 Try Verification Again</a></p>";
echo "<p><a href='test_otp_verification.php' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:10px;'>🧪 Test OTP Verification</a></p>";
echo "<p><a href='debug_verification_complete.php' style='background:#6c757d; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:10px;'>🔍 Run Complete Diagnosis</a></p>";
?>
