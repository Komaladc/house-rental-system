<?php
// Complete database schema fix for Property Nepal Admin System
include "lib/Database.php";

$db = new Database();

echo "<h1>🔧 Complete Database Schema Fix</h1>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check if admin tables exist and create them if not
echo "<h2>📋 Checking Admin Tables</h2>";

// Create tbl_admin_users if it doesn't exist
$checkAdminTable = "SHOW TABLES LIKE 'tbl_admin_users'";
$adminTableExists = $db->select($checkAdminTable);

if (!$adminTableExists || $adminTableExists->num_rows == 0) {
    echo "<p>🔄 Creating tbl_admin_users table...</p>";
    $createAdminTable = "
        CREATE TABLE tbl_admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('super_admin', 'admin') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            created_by INT DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($db->link->query($createAdminTable)) {
        echo "<p style='color: green;'>✅ tbl_admin_users table created successfully</p>";
        
        // Insert default admin user
        $defaultAdmin = "
            INSERT INTO tbl_admin_users (username, email, password, full_name, role, status) 
            VALUES ('admin', 'admin@propertynepal.com', '" . md5('admin123') . "', 'System Administrator', 'super_admin', 'active')
        ";
        if ($db->insert($defaultAdmin)) {
            echo "<p style='color: green;'>✅ Default admin user created (admin@propertynepal.com / admin123)</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error creating tbl_admin_users table: " . $db->link->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ tbl_admin_users table already exists</p>";
}

// Check and create other admin tables
$adminTables = [
    'tbl_admin_logs' => "
        CREATE TABLE tbl_admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES tbl_admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'tbl_user_verification' => "
        CREATE TABLE tbl_user_verification (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            document_front VARCHAR(255),
            document_back VARCHAR(255),
            verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
            rejection_reason TEXT,
            verified_by INT,
            verified_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES tbl_user(userId) ON DELETE CASCADE,
            FOREIGN KEY (verified_by) REFERENCES tbl_admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'tbl_website_stats' => "
        CREATE TABLE tbl_website_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_name VARCHAR(100) UNIQUE NOT NULL,
            stat_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

foreach ($adminTables as $tableName => $createSQL) {
    $checkTable = "SHOW TABLES LIKE '$tableName'";
    $tableExists = $db->select($checkTable);
    
    if (!$tableExists || $tableExists->num_rows == 0) {
        echo "<p>🔄 Creating $tableName table...</p>";
        if ($db->link->query($createSQL)) {
            echo "<p style='color: green;'>✅ $tableName table created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating $tableName table: " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ $tableName table already exists</p>";
    }
}

// Fix tbl_user table columns
echo "<h2>🔧 Fixing tbl_user Table</h2>";
$userColumns = [
    "status" => "INT(1) DEFAULT 1 COMMENT 'User active status (1=active, 0=inactive)'",
    "verification_status" => "ENUM('pending', 'verified', 'rejected') DEFAULT 'verified' COMMENT 'User verification status'",
    "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation date'",
    "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date'"
];

foreach ($userColumns as $column => $definition) {
    $checkColumn = "SHOW COLUMNS FROM tbl_user LIKE '$column'";
    $columnExists = $db->select($checkColumn);
    
    if (!$columnExists || $columnExists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_user ADD COLUMN $column $definition";
        echo "<p>🔄 Adding column '$column' to tbl_user...</p>";
        
        if ($db->link->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added column '$column'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column '$column': " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$column' already exists in tbl_user</p>";
    }
}

// Fix tbl_property table columns
echo "<h2>🔧 Fixing tbl_property Table</h2>";
$propertyColumns = [
    "status" => "INT(1) DEFAULT 1 COMMENT 'Property active status (1=active, 0=inactive)'",
    "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Property creation date'",
    "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update date'"
];

foreach ($propertyColumns as $column => $definition) {
    $checkColumn = "SHOW COLUMNS FROM tbl_property LIKE '$column'";
    $columnExists = $db->select($checkColumn);
    
    if (!$columnExists || $columnExists->num_rows == 0) {
        $alterQuery = "ALTER TABLE tbl_property ADD COLUMN $column $definition";
        echo "<p>🔄 Adding column '$column' to tbl_property...</p>";
        
        if ($db->link->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added column '$column'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding column '$column': " . $db->link->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$column' already exists in tbl_property</p>";
    }
}

// Update existing data
echo "<h2>📊 Updating Existing Data</h2>";

// Set existing users to active and verified
$updateUsers = "UPDATE tbl_user SET status = 1, verification_status = 'verified' WHERE status IS NULL OR verification_status IS NULL";
if ($db->update($updateUsers)) {
    echo "<p style='color: green;'>✅ Updated existing users to active and verified status</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Users table update: " . $db->link->error . "</p>";
}

// Set existing properties to active
$updateProperties = "UPDATE tbl_property SET status = 1 WHERE status IS NULL";
if ($db->update($updateProperties)) {
    echo "<p style='color: green;'>✅ Updated existing properties to active status</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Properties table update: " . $db->link->error . "</p>";
}

// Create uploads directory if it doesn't exist
$uploadsDir = "uploads";
$documentsDir = "uploads/documents";

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    echo "<p style='color: green;'>✅ Created uploads directory</p>";
}

if (!is_dir($documentsDir)) {
    mkdir($documentsDir, 0755, true);
    echo "<p style='color: green;'>✅ Created documents directory</p>";
}

// Show final table structures
echo "<h2>📋 Final Database Structure</h2>";

echo "<h3>tbl_user columns:</h3>";
$userStructure = $db->select("DESCRIBE tbl_user");
if ($userStructure) {
    echo "<ul>";
    while ($row = $userStructure->fetch_assoc()) {
        echo "<li><strong>" . $row['Field'] . "</strong> (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
}

echo "<h3>tbl_property columns:</h3>";
$propertyStructure = $db->select("DESCRIBE tbl_property");
if ($propertyStructure) {
    echo "<ul>";
    while ($row = $propertyStructure->fetch_assoc()) {
        echo "<li><strong>" . $row['Field'] . "</strong> (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
}

echo "<h3>Admin tables:</h3>";
$adminTablesList = ['tbl_admin_users', 'tbl_admin_logs', 'tbl_user_verification', 'tbl_website_stats'];
foreach ($adminTablesList as $table) {
    $checkTable = "SHOW TABLES LIKE '$table'";
    $exists = $db->select($checkTable);
    $status = ($exists && $exists->num_rows > 0) ? "✅ Exists" : "❌ Missing";
    echo "<p>$table: $status</p>";
}

echo "<h2>🎉 Database Setup Complete!</h2>";
echo "<p style='color: green; font-weight: bold;'>All database tables and columns are now properly configured.</p>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/login.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Admin Login</a>";
echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>📝 Test Enhanced Signup</a>";
echo "</div>";

echo "</div>";
?>
