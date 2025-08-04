<?php
require_once 'lib/Database.php';

$db = new Database();

echo "<h2>Checking database table structures...</h2>";

// Check tbl_user structure
echo "<h3>=== tbl_user structure ===</h3>";
$result = $db->select("SHOW COLUMNS FROM tbl_user");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "Table tbl_user does not exist<br>";
}

echo "<h3>=== tbl_user_verification structure ===</h3>";
$result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "<strong style='color:red;'>Table tbl_user_verification does not exist</strong><br>";
}

echo "<h3>=== tbl_pending_verification structure ===</h3>";
$result = $db->select("SHOW COLUMNS FROM tbl_pending_verification");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "Table tbl_pending_verification does not exist<br>";
}

echo "<h3>=== All tables in database ===</h3>";
$result = $db->select("SHOW TABLES");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
}

// Fix the tbl_user_verification table
echo "<hr><h2>🔧 FIXING TABLE STRUCTURE</h2>";

// Check if tbl_user_verification exists
$checkTable = $db->select("SHOW TABLES LIKE 'tbl_user_verification'");
if (!$checkTable || $checkTable->num_rows == 0) {
    echo "<p><strong>Creating tbl_user_verification table...</strong></p>";
    
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
    )";
    
    if ($db->select("SET foreign_key_checks = 0") && $db->select($createTable)) {
        echo "<p style='color:green;'>✅ Table created successfully!</p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to create table</p>";
    }
} else {
    echo "<p><strong>Table exists. Checking if userName column exists...</strong></p>";
    
    $checkColumn = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'userName'");
    if (!$checkColumn || $checkColumn->num_rows == 0) {
        echo "<p><strong>Adding userName column...</strong></p>";
        
        // Check if there's a 'username' column to rename
        $checkUsername = $db->select("SHOW COLUMNS FROM tbl_user_verification LIKE 'username'");
        if ($checkUsername && $checkUsername->num_rows > 0) {
            $alterQuery = "ALTER TABLE tbl_user_verification CHANGE username userName VARCHAR(255) NOT NULL";
            echo "<p>Renaming 'username' to 'userName'...</p>";
        } else {
            $alterQuery = "ALTER TABLE tbl_user_verification ADD COLUMN userName VARCHAR(255) NOT NULL AFTER email";
            echo "<p>Adding new 'userName' column...</p>";
        }
        
        if ($db->select($alterQuery)) {
            echo "<p style='color:green;'>✅ userName column added/fixed successfully!</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add userName column</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ userName column already exists!</p>";
    }
}

echo "<hr>";
echo "<h3>Final tbl_user_verification structure:</h3>";
$result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
}

echo "<p><a href='verify_registration.php?email=thekomalad%40gmail.com' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🔄 Try Verification Again</a></p>";
?>
