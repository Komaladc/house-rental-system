<?php
// Fix missing columns in tbl_property table
include "lib/Database.php";

$db = new Database();

echo "<h2>🔧 Fixing tbl_property Table Schema</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check current table structure
echo "<h3>📋 Current tbl_property structure:</h3>";
$describeQuery = "DESCRIBE tbl_property";
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

// Add missing columns for property table
$property_columns_to_add = [
    "status" => "INT(1) DEFAULT 1 COMMENT 'Property active status (1=active, 0=inactive)'",
    "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Property creation date'",
    "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date'"
];

echo "<h3>➕ Adding missing columns to tbl_property:</h3>";

foreach ($property_columns_to_add as $column => $definition) {
    // Check if column exists
    $checkQuery = "SHOW COLUMNS FROM tbl_property LIKE '$column'";
    $exists = $db->select($checkQuery);
    
    if (!$exists || $exists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_property ADD COLUMN $column $definition";
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

// Update existing properties to active status
echo "<h3>🔄 Updating existing property data:</h3>";
$updateQuery = "UPDATE tbl_property SET status = 1 WHERE status IS NULL OR status = 0";
if ($db->update($updateQuery)) {
    echo "<p style='color: green;'>✅ Updated existing properties to 'active' status</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating property status</p>";
}

echo "<h3>📋 Updated tbl_property structure:</h3>";
$describeQuery2 = "DESCRIBE tbl_property";
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

echo "<h3>✅ Property table schema fix completed!</h3>";
echo "<p><a href='admin/login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Go to Admin Login</a></p>";
echo "</div>";
?>
