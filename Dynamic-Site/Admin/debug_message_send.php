<?php
include '../lib/Session.php';
Session::init();
include '../lib/Database.php';
include '../classes/Notification.php';

// Debug message sending functionality
echo "<h2>Debug Message Send</h2>";

// Check if we're logged in
if(!Session::get("userlogin")) {
    echo "<p style='color:red;'>ERROR: User not logged in!</p>";
    echo "<p>userlogin session: " . (Session::get("userlogin") ? "true" : "false") . "</p>";
    echo "<p>userId session: " . Session::get("userId") . "</p>";
} else {
    echo "<p style='color:green;'>User is logged in</p>";
    echo "<p>userId: " . Session::get("userId") . "</p>";
}

// Test data
$testData = array(
    'name' => 'Test User',
    'email' => 'test@example.com', 
    'phone' => '1234567890',
    'address' => 'Test Address',
    'message' => 'This is a test message'
);

// Test with sample IDs
$adId = 1;
$ownerId = 1; 
$renterId = Session::get("userId") ? Session::get("userId") : 1;

echo "<h3>Test Parameters:</h3>";
echo "<p>adId: " . $adId . "</p>";
echo "<p>ownerId: " . $ownerId . "</p>";
echo "<p>renterId: " . $renterId . "</p>";
echo "<p>Test data: " . print_r($testData, true) . "</p>";

// Initialize notification class
$ntf = new Notification();

// Test notification insert
echo "<h3>Testing notificationInsert:</h3>";
$result = $ntf->notificationInsert($adId, $ownerId, $renterId, $testData);
echo "<p>Result: " . $result . "</p>";

// Check if table exists
$db = new Database();
$query = "SHOW TABLES LIKE 'tbl_notification'";
$tableCheck = $db->select($query);
if($tableCheck) {
    echo "<p style='color:green;'>tbl_notification table exists</p>";
    
    // Check table structure
    $query = "DESCRIBE tbl_notification";
    $structure = $db->select($query);
    if($structure) {
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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
    
    // Check recent notifications
    $query = "SELECT * FROM tbl_notification ORDER BY notfDate DESC LIMIT 5";
    $recent = $db->select($query);
    if($recent) {
        echo "<h3>Recent Notifications:</h3>";
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
        while($row = $recent->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . (isset($row['notfId']) ? $row['notfId'] : 'N/A') . "</td>";
            echo "<td>" . (isset($row['notfName']) ? $row['notfName'] : 'N/A') . "</td>";
            echo "<td>" . (isset($row['notfEmail']) ? $row['notfEmail'] : 'N/A') . "</td>";
            echo "<td>" . (isset($row['notfMsg']) ? substr($row['notfMsg'], 0, 50) . '...' : 'N/A') . "</td>";
            echo "<td>" . (isset($row['notfDate']) ? $row['notfDate'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No notifications found or query failed</p>";
    }
    
} else {
    echo "<p style='color:red;'>tbl_notification table does not exist!</p>";
}

// Test database insert directly
echo "<h3>Testing Direct Database Insert:</h3>";
$query = "INSERT INTO tbl_notification(notfName, notfEmail, notfPhone, notfAddress, notfMsg, renterId, ownerId, adId) VALUES('Direct Test', 'direct@test.com', '999999999', 'Direct Address', 'Direct message test', '$renterId', '$ownerId', '$adId')";
$directInsert = $db->insert($query);
if($directInsert) {
    echo "<p style='color:green;'>Direct insert successful</p>";
} else {
    echo "<p style='color:red;'>Direct insert failed</p>";
    echo "<p>MySQL Error: " . mysqli_error($db->link) . "</p>";
}
?>
