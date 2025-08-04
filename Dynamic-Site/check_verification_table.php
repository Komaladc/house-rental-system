<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Check tbl_user_verification Structure</h1>";

// Check if the table exists
$result = $mysqli->query("SHOW TABLES LIKE 'tbl_user_verification'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Table tbl_user_verification exists</p>";
    
    // Show structure
    echo "<h2>Table Structure:</h2>";
    $result = $mysqli->query("DESCRIBE tbl_user_verification");
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
    
    // Check sample data
    echo "<h2>Sample Data (last 5 records):</h2>";
    $result = $mysqli->query("SELECT * FROM tbl_user_verification ORDER BY verification_id DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No data in tbl_user_verification table</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Table tbl_user_verification does not exist</p>";
    
    // Check if we need to create it
    echo "<h2>Creating tbl_user_verification table...</h2>";
    
    $createTable = "CREATE TABLE IF NOT EXISTS tbl_user_verification (
        verification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        user_type ENUM('owner', 'agent') NOT NULL,
        citizenship_id VARCHAR(50),
        citizenship_front VARCHAR(255),
        citizenship_back VARCHAR(255),
        business_license VARCHAR(255),
        verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        verified_by INT NULL,
        verified_at TIMESTAMP NULL,
        rejection_reason TEXT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (verification_status),
        INDEX idx_email (email)
    )";
    
    if ($mysqli->query($createTable)) {
        echo "<p style='color: green;'>✅ Table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $mysqli->error . "</p>";
    }
}

$mysqli->close();
?>
