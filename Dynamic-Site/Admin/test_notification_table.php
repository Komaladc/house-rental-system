<?php
include '../lib/Database.php';

// Simple database connection test
echo "<h2>Database Connection Test</h2>";

$db = new Database();
if($db->link) {
    echo "<p style='color:green;'>Database connection successful</p>";
} else {
    echo "<p style='color:red;'>Database connection failed</p>";
    die();
}

// Check if notification table exists
echo "<h3>Checking notification table:</h3>";
$query = "SHOW TABLES LIKE 'tbl_notification'";
$result = $db->select($query);
if($result) {
    echo "<p style='color:green;'>tbl_notification table exists</p>";
} else {
    echo "<p style='color:red;'>tbl_notification table does not exist</p>";
    
    // Show all tables
    $query = "SHOW TABLES";
    $tables = $db->select($query);
    if($tables) {
        echo "<h4>Available tables:</h4><ul>";
        while($row = $tables->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    die();
}

// Show table structure
$query = "DESCRIBE tbl_notification";
$structure = $db->select($query);
if($structure) {
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test direct insert
echo "<h3>Testing Direct Insert:</h3>";
$testQuery = "INSERT INTO tbl_notification(notfName, notfEmail, notfPhone, notfAddress, notfMsg, renterId, ownerId, adId) 
VALUES('Test User', 'test@example.com', '1234567890', 'Test Address', 'Test message', 1, 1, 1)";

$insertResult = $db->insert($testQuery);
if($insertResult) {
    echo "<p style='color:green;'>Direct insert successful! Insert ID: " . mysqli_insert_id($db->link) . "</p>";
} else {
    echo "<p style='color:red;'>Direct insert failed</p>";
    echo "<p>MySQL Error: " . mysqli_error($db->link) . "</p>";
    echo "<p>Query: " . $testQuery . "</p>";
}

// Show recent records
$query = "SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 3";
$recent = $db->select($query);
if($recent) {
    echo "<h3>Recent Records:</h3>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th></tr>";
    while($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['notfId'] . "</td>";
        echo "<td>" . $row['notfName'] . "</td>";
        echo "<td>" . $row['notfEmail'] . "</td>";
        echo "<td>" . $row['notfPhone'] . "</td>";
        echo "<td>" . substr($row['notfMsg'], 0, 30) . "...</td>";
        echo "<td>" . $row['notfDate'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found</p>";
}
?>
