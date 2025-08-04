<?php
include '../lib/Session.php';
Session::init();
include '../lib/Database.php';
include '../helpers/Format.php';

spl_autoload_register(function($class){
    include_once '../classes/'.$class.'.php';
});

echo "<h2>Live Notification Insert Test</h2>";

// Set up test session data like a real user
Session::set("userlogin", true);
Session::set("userId", 2);

$ntf = new Notification();
$db = new Database();

// Test parameters that would come from a real form submission
$adId = 1;
$ownerId = 1; 
$renterId = Session::get("userId");

$testData = array(
    'name' => 'Live Test User',
    'email' => 'livetest@example.com',
    'phone' => '9876543210',
    'address' => 'Live Test Address, Test City',
    'message' => 'This is a live test message sent at ' . date('Y-m-d H:i:s')
);

echo "<h3>Test Parameters:</h3>";
echo "<ul>";
echo "<li>adId (Property): $adId</li>";
echo "<li>ownerId (Owner): $ownerId</li>";
echo "<li>renterId (Sender): $renterId</li>";
echo "<li>User logged in: " . (Session::get("userlogin") ? "Yes" : "No") . "</li>";
echo "</ul>";

echo "<h3>Test Data:</h3>";
echo "<ul>";
foreach($testData as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";

// Record count before insert
$beforeCount = $db->select("SELECT COUNT(*) as count FROM tbl_notification");
$countBefore = $beforeCount ? $beforeCount->fetch_assoc()['count'] : 0;
echo "<p><strong>Records before insert:</strong> $countBefore</p>";

echo "<h3>Calling notificationInsert method...</h3>";

// Call the method exactly like property_details.php does
$result = $ntf->notificationInsert($adId, $ownerId, $renterId, $testData);

echo "<p><strong>Method returned:</strong> " . htmlspecialchars($result) . "</p>";

// Check if record was actually inserted
$afterCount = $db->select("SELECT COUNT(*) as count FROM tbl_notification");
$countAfter = $afterCount ? $afterCount->fetch_assoc()['count'] : 0;
echo "<p><strong>Records after insert:</strong> $countAfter</p>";

if($countAfter > $countBefore) {
    echo "<p style='color:green; font-weight:bold;'>✓ SUCCESS: Record was inserted!</p>";
    
    // Find the new record
    $newRecord = $db->select("SELECT * FROM tbl_notification WHERE notfEmail = 'livetest@example.com' ORDER BY notfId DESC LIMIT 1");
    if($newRecord) {
        $row = $newRecord->fetch_assoc();
        echo "<h4>New Record Details:</h4>";
        echo "<table border='1' style='border-collapse:collapse;'>";
        foreach($row as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color:red; font-weight:bold;'>✗ FAILED: No record was inserted!</p>";
    
    echo "<h4>Debugging Information:</h4>";
    
    // Check if parameters are valid
    echo "<p>Parameter validation:</p>";
    echo "<ul>";
    echo "<li>adId empty: " . (empty($adId) ? "YES" : "NO") . "</li>";
    echo "<li>ownerId empty: " . (empty($ownerId) ? "YES" : "NO") . "</li>";
    echo "<li>renterId empty: " . (empty($renterId) ? "YES" : "NO") . "</li>";
    echo "<li>Email valid: " . (filter_var($testData['email'], FILTER_VALIDATE_EMAIL) ? "YES" : "NO") . "</li>";
    echo "</ul>";
    
    // Test direct database insert with same data
    echo "<h4>Testing Direct Database Insert:</h4>";
    $directQuery = "INSERT INTO tbl_notification(notfName, notfEmail, notfPhone, notfAddress, notfMsg, renterId, ownerId, adId) 
                   VALUES('" . mysqli_real_escape_string($db->link, $testData['name']) . "', 
                          '" . mysqli_real_escape_string($db->link, $testData['email']) . "', 
                          '" . mysqli_real_escape_string($db->link, $testData['phone']) . "', 
                          '" . mysqli_real_escape_string($db->link, $testData['address']) . "', 
                          '" . mysqli_real_escape_string($db->link, $testData['message']) . "', 
                          '$renterId', '$ownerId', '$adId')";
    
    echo "<p><strong>Direct Query:</strong></p>";
    echo "<code style='background:#f0f0f0; padding:10px; display:block;'>" . htmlspecialchars($directQuery) . "</code>";
    
    $directResult = $db->insert($directQuery);
    if($directResult) {
        echo "<p style='color:green;'>✓ Direct insert worked! Insert ID: " . mysqli_insert_id($db->link) . "</p>";
        echo "<p style='color:orange;'>This means the issue is in the Notification class method.</p>";
    } else {
        echo "<p style='color:red;'>✗ Direct insert also failed!</p>";
        echo "<p><strong>MySQL Error:</strong> " . mysqli_error($db->link) . "</p>";
    }
}

// Show recent 3 records for context
echo "<h3>Recent Records in Database:</h3>";
$recent = $db->select("SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 3");
if($recent && $recent->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
    while($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['notfId'] . "</td>";
        echo "<td>" . $row['notfName'] . "</td>";
        echo "<td>" . $row['notfEmail'] . "</td>";
        echo "<td>" . substr($row['notfMsg'], 0, 50) . "...</td>";
        echo "<td>" . $row['notfDate'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found in database</p>";
}
?>
