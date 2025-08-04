<?php
// Admin Setup and Database Fix Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Admin Dashboard Setup and Database Fix</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    include 'lib/Database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if tbl_admin_users table exists
    $checkTable = "SHOW TABLES LIKE 'tbl_admin_users'";
    $tableExists = $db->select($checkTable);
    
    if (!$tableExists || $tableExists->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Creating tbl_admin_users table...</p>";
        
        $createTable = "CREATE TABLE IF NOT EXISTS `tbl_admin_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `full_name` varchar(100) NOT NULL,
            `role` enum('admin','super_admin') DEFAULT 'admin',
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->insert($createTable)) {
            echo "<p style='color: green;'>✅ tbl_admin_users table created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create tbl_admin_users table</p>";
            echo "<p>Error: " . mysqli_error($db->link) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ tbl_admin_users table exists</p>";
    }
    
    // Check if default admin user exists
    $adminQuery = "SELECT * FROM tbl_admin_users WHERE email = 'admin@propertynepal.com'";
    $adminResult = $db->select($adminQuery);
    
    if (!$adminResult || $adminResult->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Creating default admin user...</p>";
        
        $adminUsername = 'admin';
        $adminEmail = 'admin@propertynepal.com';
        $adminPassword = 'admin123';
        $hashedPassword = md5($adminPassword);
        $adminFullName = 'System Administrator';
        
        $createAdmin = "INSERT INTO tbl_admin_users (username, email, password, full_name, role, status) 
                       VALUES ('$adminUsername', '$adminEmail', '$hashedPassword', '$adminFullName', 'super_admin', 'active')";
        
        if ($db->insert($createAdmin)) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>✅ Default Admin User Created!</h3>";
            echo "<strong>Login URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a><br>";
            echo "<strong>Username:</strong> $adminUsername<br>";
            echo "<strong>Email:</strong> $adminEmail<br>";
            echo "<strong>Password:</strong> $adminPassword<br>";
            echo "<br><strong>⚠️ Important:</strong> Please change the password after first login!";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin user</p>";
            echo "<p>Error: " . mysqli_error($db->link) . "</p>";
        }
    } else {
        $admin = $adminResult->fetch_assoc();
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Admin User Already Exists</h3>";
        echo "<strong>Login URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a><br>";
        echo "<strong>Username:</strong> " . $admin['username'] . "<br>";
        echo "<strong>Email:</strong> " . $admin['email'] . "<br>";
        echo "<strong>Status:</strong> " . $admin['status'] . "<br>";
        echo "<strong>Role:</strong> " . $admin['role'] . "<br>";
        echo "</div>";
    }
    
    // Check if tbl_admin_logs table exists
    $checkLogsTable = "SHOW TABLES LIKE 'tbl_admin_logs'";
    $logsTableExists = $db->select($checkLogsTable);
    
    if (!$logsTableExists || $logsTableExists->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Creating tbl_admin_logs table...</p>";
        
        $createLogsTable = "CREATE TABLE IF NOT EXISTS `tbl_admin_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `admin_id` int(11) NOT NULL,
            `action` varchar(100) NOT NULL,
            `description` text NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `admin_id` (`admin_id`),
            KEY `action` (`action`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->insert($createLogsTable)) {
            echo "<p style='color: green;'>✅ tbl_admin_logs table created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create tbl_admin_logs table</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ tbl_admin_logs table exists</p>";
    }
    
    // Check verification columns in tbl_user
    $checkUserColumns = "SHOW COLUMNS FROM tbl_user";
    $userColumns = $db->select($checkUserColumns);
    $hasVerificationColumns = false;
    $hasStatusColumn = false;
    
    if ($userColumns) {
        $columnNames = [];
        while ($col = $userColumns->fetch_assoc()) {
            $columnNames[] = $col['Field'];
        }
        
        $hasVerificationColumns = in_array('verification_status', $columnNames) && 
                                 in_array('requires_verification', $columnNames) &&
                                 in_array('email_verified', $columnNames) &&
                                 in_array('document_verified', $columnNames);
        $hasStatusColumn = in_array('status', $columnNames);
    }
    
    if (!$hasVerificationColumns || !$hasStatusColumn) {
        echo "<p style='color: orange;'>⚠️ Adding missing verification columns to tbl_user...</p>";
        
        $alterQueries = [];
        
        if (!in_array('status', $columnNames)) {
            $alterQueries[] = "ALTER TABLE tbl_user ADD COLUMN status TINYINT(1) DEFAULT 1";
        }
        if (!in_array('verification_status', $columnNames)) {
            $alterQueries[] = "ALTER TABLE tbl_user ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'verified'";
        }
        if (!in_array('requires_verification', $columnNames)) {
            $alterQueries[] = "ALTER TABLE tbl_user ADD COLUMN requires_verification TINYINT(1) DEFAULT 0";
        }
        if (!in_array('email_verified', $columnNames)) {
            $alterQueries[] = "ALTER TABLE tbl_user ADD COLUMN email_verified TINYINT(1) DEFAULT 0";
        }
        if (!in_array('document_verified', $columnNames)) {
            $alterQueries[] = "ALTER TABLE tbl_user ADD COLUMN document_verified TINYINT(1) DEFAULT 0";
        }
        
        foreach ($alterQueries as $query) {
            if ($db->update($query)) {
                echo "<p style='color: green;'>✅ Added column: " . substr($query, strpos($query, 'ADD COLUMN') + 11) . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column: " . substr($query, strpos($query, 'ADD COLUMN') + 11) . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ All verification columns exist in tbl_user</p>";
    }
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px;'>";
    echo "<h3>🚀 Quick Actions</h3>";
    echo "<a href='admin/login.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;'>🔐 Admin Login</a>";
    echo "<a href='admin/dashboard.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Dashboard</a>";
    echo "<a href='signup_enhanced.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>📝 Test Signup</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p style='color: orange;'>🔧 <strong>Solution:</strong> Please start XAMPP (Apache and MySQL services)</p>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 5px;'>";
    echo "<h3>🛠️ XAMPP Setup Instructions</h3>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel (usually at C:\\xampp\\xampp-control.exe)</li>";
    echo "<li>Start <strong>Apache</strong> service</li>";
    echo "<li>Start <strong>MySQL</strong> service</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</div>";
?>
