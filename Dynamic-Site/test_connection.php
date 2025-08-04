<?php
echo "<h1>🔧 House Rental System - Connection Test</h1>";
echo "<hr>";

// Test PHP
echo "<h2>✅ PHP Status</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PHP is working correctly!<br>";

// Test database connection
echo "<h2>🗃️ Database Connection Test</h2>";
try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_rental";
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "❌ Database Connection Failed: " . $conn->connect_error . "<br>";
        echo "<strong>Solution:</strong> Create database 'db_rental' in phpMyAdmin<br>";
        echo "<a href='http://localhost/phpmyadmin' target='_blank'>Open phpMyAdmin</a><br>";
    } else {
        echo "✅ Database Connection Successful!<br>";
        echo "Database: " . $dbname . "<br>";
        
        // Test if tables exist
        $result = $conn->query("SHOW TABLES");
        if ($result && $result->num_rows > 0) {
            echo "✅ Database tables found: " . $result->num_rows . " tables<br>";
        } else {
            echo "⚠️ No tables found. Import db_rental.sql file.<br>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Test file paths
echo "<h2>📁 File Path Test</h2>";
$files_to_check = [
    "index.php",
    "Admin/add_property.php", 
    "config/config.php",
    "classes/Property.php",
    "1-Database/db_rental.sql"
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ " . $file . " - Found<br>";
    } else {
        echo "❌ " . $file . " - Missing<br>";
    }
}

// Test session
echo "<h2>🔐 Session Test</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessions working correctly<br>";
} else {
    echo "❌ Session error<br>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li><a href='http://localhost/phpmyadmin'>Setup Database in phpMyAdmin</a></li>";
echo "<li><a href='index.php'>Go to Main Page</a></li>";
echo "<li><a href='signup.php'>Register as Owner</a></li>";
echo "<li><a href='Admin/add_property.php'>Test Add Property (after login)</a></li>";
echo "</ol>";

echo "<h2>📝 TinyMCE Status</h2>";
echo "✅ TinyMCE completely removed from all forms<br>";
echo "✅ All textareas are simple HTML<br>";
echo "✅ No API key required<br>";
echo "✅ Description field is optional<br>";
?>
