<?php
// Simple database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Database Connection Test</h2>";

// Test basic PHP
echo "✅ PHP is working<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test database connection
try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "db_rental";
    
    $connection = new mysqli($host, $username, $password, $database);
    
    if ($connection->connect_error) {
        echo "❌ Connection failed: " . $connection->connect_error . "<br>";
    } else {
        echo "✅ Database connected successfully<br>";
        echo "Database: " . $database . "<br>";
        
        // Test if tables exist
        $result = $connection->query("SHOW TABLES");
        if ($result) {
            echo "✅ Tables found: " . $result->num_rows . "<br>";
            while($row = $result->fetch_array()) {
                echo "📋 Table: " . $row[0] . "<br>";
            }
        }
    }
    $connection->close();
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test file includes
echo "<hr><h3>🔧 Testing File Includes</h3>";

try {
    include_once('../config/config.php');
    echo "✅ Config loaded<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

try {
    include_once('../lib/Database.php');
    echo "✅ Database class loaded<br>";
} catch (Exception $e) {
    echo "❌ Database class error: " . $e->getMessage() . "<br>";
}

try {
    include_once('../lib/Session.php');
    echo "✅ Session class loaded<br>";
} catch (Exception $e) {
    echo "❌ Session class error: " . $e->getMessage() . "<br>";
}

try {
    include_once('../classes/Property.php');
    echo "✅ Property class loaded<br>";
} catch (Exception $e) {
    echo "❌ Property class error: " . $e->getMessage() . "<br>";
}

echo "<hr><p><a href='debug_add_property.php'>← Back to Add Property Form</a></p>";
?>
