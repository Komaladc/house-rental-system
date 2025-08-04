<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Create/Fix tbl_user_verification Table</h1>";

// First, check if table exists
$result = $mysqli->query("SHOW TABLES LIKE 'tbl_user_verification'");
if ($result->num_rows > 0) {
    echo "<p>Table exists. Checking structure...</p>";
    
    // Show current structure
    $result = $mysqli->query("DESCRIBE tbl_user_verification");
    echo "<h2>Current Structure:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if verification_id column exists
    $checkColumn = $mysqli->query("SHOW COLUMNS FROM tbl_user_verification LIKE 'verification_id'");
    if ($checkColumn->num_rows == 0) {
        echo "<h2>Adding missing verification_id column...</h2>";
        
        // First, let's see what the current primary key is
        $showKeys = $mysqli->query("SHOW KEYS FROM tbl_user_verification WHERE Key_name = 'PRIMARY'");
        if ($showKeys && $showKeys->num_rows > 0) {
            $key = $showKeys->fetch_assoc();
            echo "<p>Current primary key: {$key['Column_name']}</p>";
            
            // If there's already a primary key, we need to decide how to handle this
            if ($key['Column_name'] != 'verification_id') {
                echo "<p style='color: orange;'>⚠️ Table has different primary key structure</p>";
                echo "<p>Recreating table with correct structure...</p>";
                
                // Drop and recreate table
                if (isset($_POST['recreate_table'])) {
                    $mysqli->query("DROP TABLE IF EXISTS tbl_user_verification");
                    echo "<p>Table dropped.</p>";
                } else {
                    echo "<form method='POST'>";
                    echo "<input type='hidden' name='recreate_table' value='1'>";
                    echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px;'>Recreate Table</button>";
                    echo "</form>";
                    echo "<p style='color: red;'>⚠️ This will delete all existing verification data!</p>";
                    $mysqli->close();
                    exit;
                }
            }
        } else {
            // Add verification_id as primary key
            $addColumn = "ALTER TABLE tbl_user_verification ADD COLUMN verification_id INT AUTO_INCREMENT PRIMARY KEY FIRST";
            if ($mysqli->query($addColumn)) {
                echo "<p style='color: green;'>✅ Added verification_id column</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column: " . $mysqli->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ verification_id column exists</p>";
    }
} else {
    echo "<p>Table doesn't exist. Creating...</p>";
}

// Create/recreate the table with correct structure
if (!$result->num_rows || isset($_POST['recreate_table'])) {
    $createTable = "CREATE TABLE IF NOT EXISTS tbl_user_verification (
        verification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        username VARCHAR(100) NULL,
        user_level INT NOT NULL,
        user_type ENUM('owner', 'agent') NOT NULL,
        citizenship_id VARCHAR(50) NULL,
        citizenship_front VARCHAR(255) NULL,
        citizenship_back VARCHAR(255) NULL,
        business_license VARCHAR(255) NULL,
        verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        verified_by INT NULL,
        verified_at TIMESTAMP NULL,
        rejection_reason TEXT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (verification_status),
        INDEX idx_email (email),
        FOREIGN KEY (user_id) REFERENCES tbl_user(userId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($createTable)) {
        echo "<p style='color: green;'>✅ Table created successfully</p>";
        
        // Show the new structure
        $result = $mysqli->query("DESCRIBE tbl_user_verification");
        echo "<h2>New Table Structure:</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create table: " . $mysqli->error . "</p>";
    }
}

echo "<h2>Test the Fixed Table</h2>";
echo "<p><a href='check_all_records.php'>Check All Records</a></p>";
echo "<p><a href='complete_agent_test.php'>Test Agent Signup</a></p>";

$mysqli->close();
?>
