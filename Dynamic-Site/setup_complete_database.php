<?php
// Complete database setup - Create all missing tables
include "lib/Database.php";

$db = new Database();

echo "<h1>🔧 Complete Database Setup for Property Nepal</h1>";
echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";

// Check current database and tables
echo "<h2>📋 Current Database Status</h2>";
$showTables = "SHOW TABLES";
$tables = $db->select($showTables);

echo "<h3>Existing Tables:</h3>";
if ($tables && $tables->num_rows > 0) {
    echo "<ul>";
    while ($table = $tables->fetch_assoc()) {
        $tableName = array_values($table)[0];
        echo "<li>✅ $tableName</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ No tables found in database</p>";
}

// Create all required tables
echo "<h2>🚀 Creating Required Tables</h2>";

// 1. Category table
$categoryTable = "
CREATE TABLE IF NOT EXISTS tbl_category (
    categoryId INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL,
    categoryDescription TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>1. Creating tbl_category...</h3>";
if ($db->link->query($categoryTable)) {
    echo "<p style='color: green;'>✅ tbl_category created successfully</p>";
    
    // Insert default categories
    $categories = [
        "INSERT IGNORE INTO tbl_category (categoryName, categoryDescription) VALUES ('Apartment', 'Modern apartments and flats')",
        "INSERT IGNORE INTO tbl_category (categoryName, categoryDescription) VALUES ('House', 'Single family houses')",
        "INSERT IGNORE INTO tbl_category (categoryName, categoryDescription) VALUES ('Room', 'Single rooms for rent')",
        "INSERT IGNORE INTO tbl_category (categoryName, categoryDescription) VALUES ('Commercial', 'Commercial properties and office spaces')",
        "INSERT IGNORE INTO tbl_category (categoryName, categoryDescription) VALUES ('Land', 'Land and plots for rent')"
    ];
    
    foreach ($categories as $categoryInsert) {
        $db->insert($categoryInsert);
    }
    echo "<p style='color: blue;'>ℹ️ Default categories added</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_category: " . $db->link->error . "</p>";
}

// 2. User table (enhanced)
$userTable = "
CREATE TABLE IF NOT EXISTS tbl_user (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    userName VARCHAR(50) UNIQUE NOT NULL,
    userEmail VARCHAR(100) UNIQUE NOT NULL,
    userPass VARCHAR(255) NOT NULL,
    cellNo VARCHAR(20),
    userLevel INT DEFAULT 1 COMMENT '1=Seeker, 2=Owner, 3=Agent',
    status INT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'verified',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>2. Creating tbl_user...</h3>";
if ($db->link->query($userTable)) {
    echo "<p style='color: green;'>✅ tbl_user created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_user: " . $db->link->error . "</p>";
}

// 3. Property table
$propertyTable = "
CREATE TABLE IF NOT EXISTS tbl_property (
    propertyId INT AUTO_INCREMENT PRIMARY KEY,
    categoryId INT,
    ownerId INT,
    propertyTitle VARCHAR(200) NOT NULL,
    propertyDetails TEXT,
    propertyPrice DECIMAL(10,2) NOT NULL,
    propertyLocation VARCHAR(200) NOT NULL,
    propertyImage VARCHAR(255),
    propertyFeatures TEXT,
    status INT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoryId) REFERENCES tbl_category(categoryId) ON DELETE SET NULL,
    FOREIGN KEY (ownerId) REFERENCES tbl_user(userId) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>3. Creating tbl_property...</h3>";
if ($db->link->query($propertyTable)) {
    echo "<p style='color: green;'>✅ tbl_property created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_property: " . $db->link->error . "</p>";
}

// 4. OTP table
$otpTable = "
CREATE TABLE IF NOT EXISTS tbl_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_otp_code (otp_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>4. Creating tbl_otp...</h3>";
if ($db->link->query($otpTable)) {
    echo "<p style='color: green;'>✅ tbl_otp created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_otp: " . $db->link->error . "</p>";
}

// 5. Admin Users table
$adminUsersTable = "
CREATE TABLE IF NOT EXISTS tbl_admin_users (
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

echo "<h3>5. Creating tbl_admin_users...</h3>";
if ($db->link->query($adminUsersTable)) {
    echo "<p style='color: green;'>✅ tbl_admin_users created successfully</p>";
    
    // Create default admin user
    $checkAdmin = "SELECT * FROM tbl_admin_users WHERE email = 'admin@propertynepal.com'";
    $adminExists = $db->select($checkAdmin);
    
    if (!$adminExists || $adminExists->num_rows == 0) {
        $createAdmin = "
            INSERT INTO tbl_admin_users (username, email, password, full_name, role, status) 
            VALUES ('admin', 'admin@propertynepal.com', '" . md5('admin123') . "', 'System Administrator', 'super_admin', 'active')
        ";
        if ($db->insert($createAdmin)) {
            echo "<p style='color: green;'>✅ Default admin user created (admin@propertynepal.com / admin123)</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_admin_users: " . $db->link->error . "</p>";
}

// 6. Admin Logs table
$adminLogsTable = "
CREATE TABLE IF NOT EXISTS tbl_admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES tbl_admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>6. Creating tbl_admin_logs...</h3>";
if ($db->link->query($adminLogsTable)) {
    echo "<p style='color: green;'>✅ tbl_admin_logs created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_admin_logs: " . $db->link->error . "</p>";
}

// 7. User Verification table
$userVerificationTable = "
CREATE TABLE IF NOT EXISTS tbl_user_verification (
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
";

echo "<h3>7. Creating tbl_user_verification...</h3>";
if ($db->link->query($userVerificationTable)) {
    echo "<p style='color: green;'>✅ tbl_user_verification created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_user_verification: " . $db->link->error . "</p>";
}

// 8. Website Stats table
$websiteStatsTable = "
CREATE TABLE IF NOT EXISTS tbl_website_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "<h3>8. Creating tbl_website_stats...</h3>";
if ($db->link->query($websiteStatsTable)) {
    echo "<p style='color: green;'>✅ tbl_website_stats created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating tbl_website_stats: " . $db->link->error . "</p>";
}

// Create uploads directories
echo "<h2>📁 Creating Upload Directories</h2>";
$directories = ['uploads', 'uploads/documents', 'uploads/properties'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✅ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Directory already exists: $dir</p>";
    }
}

// Final verification
echo "<h2>✅ Final Database Status</h2>";
$finalTables = "SHOW TABLES";
$finalResult = $db->select($finalTables);

echo "<h3>All Tables:</h3>";
if ($finalResult && $finalResult->num_rows > 0) {
    echo "<ul>";
    while ($table = $finalResult->fetch_assoc()) {
        $tableName = array_values($table)[0];
        echo "<li>✅ $tableName</li>";
    }
    echo "</ul>";
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 Database setup completed successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Something went wrong with database setup</p>";
}

echo "<h3>🚀 Ready to Use!</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/login.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Admin Login</a>";
echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Enhanced Signup</a>";
echo "<a href='index.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🏠 Main Site</a>";
echo "</div>";

echo "</div>";
?>
