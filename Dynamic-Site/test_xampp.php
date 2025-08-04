<?php
echo "<h1>XAMPP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test MySQL connection
try {
    $mysqli = new mysqli("localhost", "root", "", "db_rental");
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>MySQL Connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>MySQL Connected successfully</p>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>MySQL Error: " . $e->getMessage() . "</p>";
}
?>
