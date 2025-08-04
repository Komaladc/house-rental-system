<?php
// Check database tables and fix any issues
include "lib/Database.php";

$db = new Database();

echo "<h2>🔍 Database Table Verification</h2>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check what tables actually exist
echo "<h3>📋 Checking Existing Tables</h3>";
$showTables = "SHOW TABLES";
$result = $db->select($showTables);

$existingTables = [];
if ($result && $result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        $tableName = array_values($row)[0];
        $existingTables[] = $tableName;
        echo "<li style='color: green;'>✅ $tableName</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ No tables found!</p>";
}

// Required tables
$requiredTables = [
    'tbl_user',
    'tbl_property', 
    'tbl_category',
    'tbl_admin_users',
    'tbl_admin_logs',
    'tbl_user_verification',
    'tbl_website_stats',
    'tbl_otp'
];

echo "<h3>📊 Table Status Check</h3>";
$missingTables = [];
foreach ($requiredTables as $table) {
    if (in_array($table, $existingTables)) {
        echo "<p style='color: green;'>✅ $table - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>❌ $table - MISSING</p>";
        $missingTables[] = $table;
    }
}

// If tables are missing, create them
if (!empty($missingTables)) {
    echo "<h3>🔧 Creating Missing Tables</h3>";
    
    // Create tbl_property if missing
    if (in_array('tbl_property', $missingTables)) {
        echo "<p>🔄 Creating tbl_property...</p>";
        $createProperty = "
        CREATE TABLE tbl_property (
            propertyId INT AUTO_INCREMENT PRIMARY KEY,
            categoryId INT,
            ownerId INT,
            propertyTitle VARCHAR(200) NOT NULL,
            propertyDetails TEXT,
            propertyPrice DECIMAL(10,2) NOT NULL,
            propertyLocation VARCHAR(200) NOT NULL,
            propertyImage VARCHAR(255),
            propertyFeatures TEXT,
            status INT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        if ($db->link->query($createProperty)) {
            echo "<p style='color: green;'>✅ tbl_property created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating tbl_property: " . $db->link->error . "</p>";
        }
    }
    
    // Create other missing tables...
    if (in_array('tbl_category', $missingTables)) {
        echo "<p>🔄 Creating tbl_category...</p>";
        $createCategory = "
        CREATE TABLE tbl_category (
            categoryId INT AUTO_INCREMENT PRIMARY KEY,
            categoryName VARCHAR(100) NOT NULL,
            categoryDescription TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        if ($db->link->query($createCategory)) {
            echo "<p style='color: green;'>✅ tbl_category created successfully</p>";
            
            // Add default categories
            $defaultCategories = [
                "INSERT INTO tbl_category (categoryName, categoryDescription) VALUES ('Apartment', 'Modern apartments and flats')",
                "INSERT INTO tbl_category (categoryName, categoryDescription) VALUES ('House', 'Single family houses')",
                "INSERT INTO tbl_category (categoryName, categoryDescription) VALUES ('Room', 'Single rooms for rent')"
            ];
            
            foreach ($defaultCategories as $catSql) {
                $db->insert($catSql);
            }
            echo "<p style='color: blue;'>ℹ️ Default categories added</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating tbl_category: " . $db->link->error . "</p>";
        }
    }
}

// Test the queries that dashboard uses
echo "<h3>🧪 Testing Dashboard Queries</h3>";

$testQueries = [
    'Total Users' => "SELECT COUNT(*) as count FROM tbl_user",
    'Total Properties' => "SELECT COUNT(*) as count FROM tbl_property", 
    'Total Categories' => "SELECT COUNT(*) as count FROM tbl_category",
    'Pending Verifications' => "SELECT COUNT(*) as count FROM tbl_user WHERE verification_status = 'pending'"
];

foreach ($testQueries as $label => $query) {
    echo "<p><strong>Testing: $label</strong></p>";
    echo "<code>$query</code><br>";
    
    $testResult = $db->select($query);
    if ($testResult) {
        $row = $testResult->fetch_assoc();
        echo "<p style='color: green;'>✅ Result: " . $row['count'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $db->link->error . "</p>";
    }
    echo "<br>";
}

// Check final status
echo "<h3>✅ Final Status</h3>";
$finalCheck = "SHOW TABLES";
$finalResult = $db->select($finalCheck);

if ($finalResult && $finalResult->num_rows >= count($requiredTables)) {
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 All tables are ready!</p>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚀 Try Dashboard Again</a>";
    echo "<a href='admin/login.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🔐 Admin Login</a>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Some tables are still missing. Please run the complete database setup.</p>";
    echo "<a href='setup_complete_database.php' style='background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🔧 Run Complete Setup</a>";
}

echo "</div>";
?>
