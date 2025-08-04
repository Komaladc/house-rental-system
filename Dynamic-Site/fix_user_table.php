<?php
// Fix missing columns in tbl_user table
include "lib/Database.php";

$db = new Database();

echo "<h2>🔧 Fixing Database Schema - Missing Columns</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check current table structure
echo "<h3>📋 Current tbl_user structure:</h3>";
$describeQuery = "DESCRIBE tbl_user";
$result = $db->select($describeQuery);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Add missing columns
$columns_to_add = [
    "status" => "INT(1) DEFAULT 1 COMMENT 'User active status (1=active, 0=inactive)'",
    "verification_status" => "ENUM('pending', 'verified', 'rejected') DEFAULT 'verified' COMMENT 'User verification status'",
    "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation date'",
    "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date'"
];

echo "<h3>➕ Adding missing columns:</h3>";

foreach ($columns_to_add as $column => $definition) {
    // Check if column exists
    $checkQuery = "SHOW COLUMNS FROM tbl_user LIKE '$column'";
    $exists = $db->select($checkQuery);
    
    if (!$exists || $exists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_user ADD COLUMN $column $definition";
        echo "<p>🔄 Adding column '$column'...</p>";
        
        if ($db->link->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added column '$column'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column '$column': " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$column' already exists</p>";
    }
}

// Check if we need to update existing users
echo "<h3>🔄 Updating existing user data:</h3>";

// Set verification_status to 'verified' for existing users (since they were already in the system)
$updateQuery = "UPDATE tbl_user SET verification_status = 'verified' WHERE verification_status IS NULL OR verification_status = ''";
if ($db->update($updateQuery)) {
    echo "<p style='color: green;'>✅ Updated existing users to 'verified' status</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating user verification status</p>";
}

// Set status to 1 (active) for existing users
$updateStatusQuery = "UPDATE tbl_user SET status = 1 WHERE status IS NULL OR status = 0";
if ($db->update($updateStatusQuery)) {
    echo "<p style='color: green;'>✅ Updated existing users to 'active' status</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating user status</p>";
}

echo "<h3>📋 Updated tbl_user structure:</h3>";
$describeQuery2 = "DESCRIBE tbl_user";
$result2 = $db->select($describeQuery2);

if ($result2) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>✅ Database schema fix completed!</h3>";
echo "<p><a href='admin/login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Go to Admin Login</a></p>";
echo "</div>";
?>
