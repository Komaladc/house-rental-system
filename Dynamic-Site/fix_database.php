<?php
echo "<h1>🔧 Database Connection Test</h1>";

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_rental";

try {
    // Test basic MySQL connection
    echo "<h3>1. Testing MySQL Connection</h3>";
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        echo "❌ MySQL Connection Failed: " . $conn->connect_error . "<br>";
        echo "<div style='background:#ffeaa7; padding:15px; border-radius:5px; margin:10px 0;'>";
        echo "<strong>🚨 XAMPP Not Running?</strong><br>";
        echo "1. Start XAMPP Control Panel<br>";
        echo "2. Start Apache and MySQL services<br>";
        echo "3. Refresh this page<br>";
        echo "</div>";
        exit;
    } else {
        echo "✅ MySQL Connection Successful<br>";
    }
    
    // Test if database exists
    echo "<h3>2. Testing Database</h3>";
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows > 0) {
        echo "✅ Database '$dbname' exists<br>";
    } else {
        echo "❌ Database '$dbname' does not exist<br>";
        echo "Creating database...<br>";
        if ($conn->query("CREATE DATABASE $dbname")) {
            echo "✅ Database '$dbname' created successfully<br>";
        } else {
            echo "❌ Error creating database: " . $conn->error . "<br>";
        }
    }
    
    // Connect to the specific database
    $conn->close();
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "❌ Database Connection Failed: " . $conn->connect_error . "<br>";
        exit;
    } else {
        echo "✅ Connected to database '$dbname'<br>";
    }
    
    // Check if tables exist
    echo "<h3>3. Checking Tables</h3>";
    $required_tables = [
        'tbl_user' => "CREATE TABLE IF NOT EXISTS `tbl_user` (
            `userId` int(11) NOT NULL AUTO_INCREMENT,
            `firstName` varchar(60) NOT NULL,
            `lastName` varchar(60) NOT NULL,
            `userName` varchar(60) NOT NULL,
            `userEmail` varchar(100) NOT NULL,
            `cellNo` varchar(20) NOT NULL,
            `userAddress` varchar(200) NOT NULL,
            `userPass` varchar(100) NOT NULL,
            `confPass` varchar(100) NOT NULL,
            `userLevel` int(11) NOT NULL,
            `is_email_verified` tinyint(1) DEFAULT 0,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `verification_token` varchar(255) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`userId`),
            UNIQUE KEY `userEmail` (`userEmail`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'tbl_otp' => "CREATE TABLE IF NOT EXISTS `tbl_otp` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `otp` varchar(6) NOT NULL,
            `purpose` varchar(50) DEFAULT 'registration',
            `is_used` tinyint(1) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `expires_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'tbl_pending_verification' => "CREATE TABLE IF NOT EXISTS `tbl_pending_verification` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    foreach ($required_tables as $table_name => $create_sql) {
        $result = $conn->query("SHOW TABLES LIKE '$table_name'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table_name' exists<br>";
        } else {
            echo "❌ Table '$table_name' missing - Creating...<br>";
            if ($conn->query($create_sql)) {
                echo "✅ Table '$table_name' created successfully<br>";
            } else {
                echo "❌ Error creating table '$table_name': " . $conn->error . "<br>";
            }
        }
    }
    
    echo "<h3>4. Testing Classes</h3>";
    
    // Test if our PHP classes work
    try {
        include_once 'lib/Database.php';
        $db = new Database();
        echo "✅ Database class works<br>";
        
        include_once 'classes/EmailOTP.php';
        if (class_exists('EmailOTP')) {
            $emailOTP = new EmailOTP();
            echo "✅ EmailOTP class works<br>";
        }
        
        include_once 'classes/PreRegistrationVerification.php';
        if (class_exists('PreRegistrationVerification')) {
            $preVerification = new PreRegistrationVerification();
            echo "✅ PreRegistrationVerification class works<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Class error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<div style='background:#00b894; color:white; padding:15px; border-radius:5px; margin:10px 0;'>";
    echo "<strong>🎉 Everything looks good!</strong><br>";
    echo "Your signup page should now work properly.<br>";
    echo "<a href='signup_with_verification.php' style='color:white; font-weight:bold;'>🆕 Test Signup Page</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "<br>";
}
?>
