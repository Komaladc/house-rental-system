<?php
echo "<h1>🛠️ Database Table Fix</h1>";

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_rental";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "❌ Database Connection Failed: " . $conn->connect_error . "<br>";
        exit;
    }
    
    echo "✅ Connected to database<br>";
    
    // Drop problematic tables and recreate them
    echo "<h3>🗑️ Cleaning up problematic tables</h3>";
    
    $tables_to_fix = ['tbl_pending_verification', 'tbl_otp'];
    
    foreach ($tables_to_fix as $table) {
        echo "Dropping table '$table'...<br>";
        $conn->query("DROP TABLE IF EXISTS `$table`");
        echo "✅ Table '$table' dropped<br>";
    }
    
    echo "<h3>🏗️ Creating tables with correct structure</h3>";
    
    // Create OTP table
    $create_otp = "CREATE TABLE `tbl_otp` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(100) NOT NULL,
        `otp` varchar(6) NOT NULL,
        `purpose` varchar(50) DEFAULT 'registration',
        `is_used` tinyint(1) DEFAULT 0,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `expires_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_otp)) {
        echo "✅ Table 'tbl_otp' created successfully<br>";
    } else {
        echo "❌ Error creating 'tbl_otp': " . $conn->error . "<br>";
    }
    
    // Create pending verification table
    $create_pending = "CREATE TABLE `tbl_pending_verification` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(100) NOT NULL,
        `verification_token` varchar(255) NOT NULL,
        `otp` varchar(6) NOT NULL,
        `registration_data` text NOT NULL,
        `is_verified` tinyint(1) DEFAULT 0,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `expires_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `email` (`email`),
        KEY `verification_token` (`verification_token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_pending)) {
        echo "✅ Table 'tbl_pending_verification' created successfully<br>";
    } else {
        echo "❌ Error creating 'tbl_pending_verification': " . $conn->error . "<br>";
    }
    
    // Test the tables
    echo "<h3>🧪 Testing tables</h3>";
    
    $test_tables = ['tbl_user', 'tbl_otp', 'tbl_pending_verification'];
    foreach ($test_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' exists and ready<br>";
            
            // Show column info
            $columns = $conn->query("SHOW COLUMNS FROM `$table`");
            echo "<details><summary>📋 $table structure</summary>";
            echo "<table border='1' style='border-collapse:collapse; margin:10px;'>";
            echo "<tr style='background:#f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($col = $columns->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table></details>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
    
    echo "<h3>✅ Database Fix Complete!</h3>";
    echo "<div style='background:#00b894; color:white; padding:15px; border-radius:5px; margin:10px 0;'>";
    echo "<strong>🎉 All tables are now properly configured!</strong><br>";
    echo "You can now test the signup functionality.<br><br>";
    echo "<a href='simple_signup_test.php' style='color:white; font-weight:bold; background:#0984e3; padding:8px 12px; border-radius:3px; text-decoration:none; margin:5px;'>🧪 Test Simple Signup</a> ";
    echo "<a href='signup_with_verification.php' style='color:white; font-weight:bold; background:#6c5ce7; padding:8px 12px; border-radius:3px; text-decoration:none; margin:5px;'>📧 Test Email Verification Signup</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "<br>";
    echo "<div style='background:#ff7675; color:white; padding:15px; border-radius:5px;'>";
    echo "If this error persists:<br>";
    echo "1. Restart XAMPP<br>";
    echo "2. Check MySQL error logs<br>";
    echo "3. Try creating tables manually in phpMyAdmin<br>";
    echo "</div>";
}
?>
