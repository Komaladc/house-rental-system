<?php
require_once 'lib/Database.php';

$db = new Database();

echo "Checking and fixing tbl_user_verification table...\n\n";

// Check if table exists
$result = $db->select("SHOW TABLES LIKE 'tbl_user_verification'");
if ($result && $result->num_rows > 0) {
    echo "Table tbl_user_verification exists. Checking structure...\n";
    
    // Check current structure
    $result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
    $columns = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    // Check if userName column exists
    if (!in_array('userName', $columns)) {
        if (in_array('username', $columns)) {
            echo "Found 'username' column, renaming to 'userName'...\n";
            $db->update("ALTER TABLE tbl_user_verification CHANGE username userName VARCHAR(255) NOT NULL");
            echo "Column renamed successfully.\n";
        } else {
            echo "Adding missing userName column...\n";
            $db->update("ALTER TABLE tbl_user_verification ADD COLUMN userName VARCHAR(255) NOT NULL AFTER email");
            echo "Column added successfully.\n";
        }
    } else {
        echo "userName column exists - table structure is correct.\n";
    }
} else {
    echo "Table tbl_user_verification does not exist. Creating it...\n";
    
    $createQuery = "CREATE TABLE tbl_user_verification (
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
    
    if ($db->insert($createQuery)) {
        echo "Table created successfully.\n";
    } else {
        echo "Failed to create table.\n";
    }
}

echo "\nFinal table structure:\n";
$result = $db->select("SHOW COLUMNS FROM tbl_user_verification");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}
?>
