<?php
// Disable output buffering to see immediate results
ob_start();

include '../lib/Database.php';

echo "<h2>Direct Database Insert Test</h2>";

$db = new Database();

// Test 1: Check if table exists
echo "<h3>1. Checking if table exists:</h3>";
$tableCheck = $db->select("SHOW TABLES LIKE 'tbl_notification'");
if($tableCheck) {
    echo "<p style='color:green;'>✓ tbl_notification table exists</p>";
} else {
    echo "<p style='color:red;'>✗ tbl_notification table does not exist</p>";
    // Show available tables
    $tables = $db->select("SHOW TABLES");
    if($tables) {
        echo "<h4>Available tables:</h4><ul>";
        while($row = $tables->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    die();
}

// Test 2: Check table structure
echo "<h3>2. Table structure:</h3>";
$structure = $db->select("DESCRIBE tbl_notification");
if($structure) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while($row = $structure->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Key'] . "</td></tr>";
    }
    echo "</table>";
}

// Test 3: Try direct insert
echo "<h3>3. Testing direct insert:</h3>";
$testName = "Direct Test " . time();
$testEmail = "directtest" . time() . "@example.com";

$insertQuery = "INSERT INTO tbl_notification (notfName, notfEmail, notfPhone, notfAddress, notfMsg, renterId, ownerId, adId) 
                VALUES ('$testName', '$testEmail', '1234567890', 'Test Address', 'Direct insert test message', 1, 1, 1)";

echo "<p><strong>Query:</strong> " . $insertQuery . "</p>";

$result = $db->insert($insertQuery);
if($result) {
    $insertId = mysqli_insert_id($db->link);
    echo "<p style='color:green;'>✓ Insert successful! ID: " . $insertId . "</p>";
    
    // Verify the insert
    $verifyQuery = "SELECT * FROM tbl_notification WHERE notfId = $insertId";
    $verify = $db->select($verifyQuery);
    if($verify) {
        $row = $verify->fetch_assoc();
        echo "<h4>Inserted record:</h4>";
        echo "<ul>";
        foreach($row as $key => $value) {
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color:red;'>✗ Insert failed</p>";
    echo "<p><strong>MySQL Error:</strong> " . mysqli_error($db->link) . "</p>";
}

// Test 4: Count total records
echo "<h3>4. Total notifications in database:</h3>";
$countResult = $db->select("SELECT COUNT(*) as total FROM tbl_notification");
if($countResult) {
    $count = $countResult->fetch_assoc();
    echo "<p>Total records: " . $count['total'] . "</p>";
}

// Test 5: Show recent records
echo "<h3>5. Recent 5 records:</h3>";
$recent = $db->select("SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 5");
if($recent && $recent->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
    while($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['notfId'] . "</td>";
        echo "<td>" . $row['notfName'] . "</td>";
        echo "<td>" . $row['notfEmail'] . "</td>";
        echo "<td>" . substr($row['notfMsg'], 0, 30) . "...</td>";
        echo "<td>" . $row['notfDate'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found</p>";
}

ob_end_flush();
?>
