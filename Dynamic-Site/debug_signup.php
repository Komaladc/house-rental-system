<?php
// Quick diagnostic for signup issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Signup Page Diagnostic</h1>";

try {
    // Test 1: Basic includes
    echo "<h3>1. Testing Includes</h3>";
    
    if (file_exists('config/config.php')) {
        include_once 'config/config.php';
        echo "✅ config.php loaded<br>";
    } else {
        echo "❌ config.php not found<br>";
    }
    
    if (file_exists('lib/Database.php')) {
        include_once 'lib/Database.php';
        echo "✅ Database.php loaded<br>";
    } else {
        echo "❌ Database.php not found<br>";
    }
    
    if (file_exists('lib/Session.php')) {
        include_once 'lib/Session.php';
        echo "✅ Session.php loaded<br>";
    } else {
        echo "❌ Session.php not found<br>";
    }
    
    // Test 2: Database connection
    echo "<h3>2. Testing Database Connection</h3>";
    $db = new Database();
    if ($db && $db->link) {
        echo "✅ Database connected successfully<br>";
        
        // Test required tables
        $tables = ['tbl_user', 'tbl_otp', 'tbl_pending_verification'];
        foreach ($tables as $table) {
            $result = mysqli_query($db->link, "SHOW TABLES LIKE '$table'");
            if ($result && mysqli_num_rows($result) > 0) {
                echo "✅ Table $table exists<br>";
                
                // Show table structure
                $structure = mysqli_query($db->link, "DESCRIBE $table");
                if ($structure) {
                    echo "<details><summary>📋 $table structure</summary>";
                    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
                    while ($row = mysqli_fetch_assoc($structure)) {
                        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
                    }
                    echo "</table></details>";
                }
            } else {
                echo "❌ Table $table missing<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    // Test 3: Classes
    echo "<h3>3. Testing Classes</h3>";
    
    if (file_exists('classes/EmailOTP.php')) {
        include_once 'classes/EmailOTP.php';
        if (class_exists('EmailOTP')) {
            echo "✅ EmailOTP class loaded<br>";
            try {
                $emailOTP = new EmailOTP();
                echo "✅ EmailOTP instantiated<br>";
            } catch (Exception $e) {
                echo "❌ EmailOTP error: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ EmailOTP class not found<br>";
        }
    } else {
        echo "❌ EmailOTP.php file not found<br>";
    }
    
    if (file_exists('classes/PreRegistrationVerification.php')) {
        include_once 'classes/PreRegistrationVerification.php';
        if (class_exists('PreRegistrationVerification')) {
            echo "✅ PreRegistrationVerification class loaded<br>";
            try {
                $preVerification = new PreRegistrationVerification();
                echo "✅ PreRegistrationVerification instantiated<br>";
            } catch (Exception $e) {
                echo "❌ PreRegistrationVerification error: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ PreRegistrationVerification class not found<br>";
        }
    } else {
        echo "❌ PreRegistrationVerification.php file not found<br>";
    }
    
    // Test 4: PHP Mail Function
    echo "<h3>4. Testing PHP Mail</h3>";
    if (function_exists('mail')) {
        echo "✅ mail() function available<br>";
    } else {
        echo "❌ mail() function not available<br>";
    }
    
    // Test 5: Check if any users exist
    echo "<h3>5. Database Content Check</h3>";
    $userCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_user"))['count'];
    $otpCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_otp"))['count'];
    $pendingCount = mysqli_fetch_assoc(mysqli_query($db->link, "SELECT COUNT(*) as count FROM tbl_pending_verification"))['count'];
    
    echo "📊 Users: $userCount<br>";
    echo "📊 OTPs: $otpCount<br>";
    echo "📊 Pending: $pendingCount<br>";
    
} catch (Exception $e) {
    echo "<h3>❌ Critical Error</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><h3>🧪 Test Links</h3>";
echo '<a href="signup_with_verification.php" style="padding:10px; background:#3498db; color:white; text-decoration:none; border-radius:5px;">🆕 Test Signup</a><br><br>';
echo '<a href="signin.php" style="padding:10px; background:#27ae60; color:white; text-decoration:none; border-radius:5px;">🔑 Test Signin</a><br><br>';
echo '<a href="index.php" style="padding:10px; background:#f39c12; color:white; text-decoration:none; border-radius:5px;">🏠 Home</a>';
?>
