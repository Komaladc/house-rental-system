<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection test
include "../config/config.php";
include "../lib/Database.php";

echo "<h1>🔧 Add Property Debug Tool</h1>";

// Test database connection
try {
    $db = new Database();
    echo "<p style='color:green;'>✅ Database connection successful!</p>";
    
    // Check tbl_ad table structure
    $tableCheck = $db->select("DESCRIBE tbl_ad");
    if($tableCheck) {
        echo "<h3>📋 Table Structure (tbl_ad):</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while($column = $tableCheck->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check categories
    $catCheck = $db->select("SELECT * FROM tbl_category");
    if($catCheck && $catCheck->num_rows > 0) {
        echo "<h3>🏠 Available Categories:</h3>";
        echo "<ul>";
        while($cat = $catCheck->fetch_assoc()) {
            echo "<li>ID: " . $cat['catId'] . " - " . $cat['catName'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>❌ No categories found! You need to add categories first.</p>";
    }
    
    // Check uploads directory
    $uploadDir = "../uploads/ad_image/";
    if(is_dir($uploadDir)) {
        if(is_writable($uploadDir)) {
            echo "<p style='color:green;'>✅ Upload directory exists and is writable</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ Upload directory exists but is not writable</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Upload directory does not exist</p>";
        // Try to create it
        if(mkdir($uploadDir, 0777, true)) {
            echo "<p style='color:green;'>✅ Upload directory created successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to create upload directory</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>🔗 Navigation:</h3>";
echo "<p><a href='add_property.php' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🏠 Add Property</a></p>";
echo "<p><a href='category_list.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>📋 Manage Categories</a></p>";
echo "<p><a href='property_list_admin.php' style='background:#17a2b8; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>📝 Property List</a></p>";
?>
