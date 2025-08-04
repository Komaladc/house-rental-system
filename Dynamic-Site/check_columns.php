<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Table Column Names</h1>";

echo "<h2>tbl_user columns:</h2>";
$result = $mysqli->query("SHOW COLUMNS FROM tbl_user");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>{$row['Field']}</strong> - {$row['Type']}</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . $mysqli->error;
}

echo "<h2>tbl_user_verification columns:</h2>";
$result = $mysqli->query("SHOW COLUMNS FROM tbl_user_verification");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>{$row['Field']}</strong> - {$row['Type']}</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . $mysqli->error;
}

echo "<h2>Sample data from tbl_user:</h2>";
$result = $mysqli->query("SELECT * FROM tbl_user LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r(array_keys($row));
    echo "</pre>";
}

$mysqli->close();
?>
