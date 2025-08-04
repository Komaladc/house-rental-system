<?php
include '../lib/Session.php';
Session::init();
include '../lib/Database.php';
include '../helpers/Format.php';

// Include classes like the main files do
spl_autoload_register(function($class){
    include_once '../classes/'.$class.'.php';
});

$ntf = new Notification();

echo "<h2>Message Send Test</h2>";

// Simulate user login
Session::set("userlogin", true);
Session::set("userId", 2); // Set a test user ID

// Simulate property data (like from property_details.php)
$adId = 1; // Test property ID
$ownerId = 1; // Test owner ID
$renterId = Session::get("userId");

// Set session data like property_details.php does
Session::set("adId", $adId);
Session::set("ownerId", $ownerId);

// Test form data (like what would come from the form)
$_POST = array(
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'address' => '123 Test Street',
    'message' => 'I am interested in this property. Please contact me.',
    'sendmsg' => 'Send'
);

echo "<h3>Test Parameters:</h3>";
echo "<p>adId (Property ID): " . $adId . "</p>";
echo "<p>ownerId (Property Owner): " . $ownerId . "</p>";  
echo "<p>renterId (Message Sender): " . $renterId . "</p>";
echo "<p>User Login Status: " . (Session::get("userlogin") ? "Logged In" : "Not Logged In") . "</p>";

echo "<h3>Form Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Test the notification insert exactly like property_details.php does
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendmsg'])){
    echo "<h3>Testing Message Send:</h3>";
    echo "<p>Form submitted with sendmsg button...</p>";
    
    $sendNotif = $ntf->notificationInsert($adId, $ownerId, $renterId, $_POST);
    
    echo "<p><strong>Result:</strong> " . $sendNotif . "</p>";
    
    // Check if message was actually saved
    $db = new Database();
    $query = "SELECT * FROM tbl_notification WHERE notfEmail = 'john@example.com' ORDER BY notfId DESC LIMIT 1";
    $result = $db->select($query);
    
    if($result && $result->num_rows > 0) {
        echo "<p style='color:green;'><strong>SUCCESS:</strong> Message found in database!</p>";
        $row = $result->fetch_assoc();
        echo "<h4>Saved Message Details:</h4>";
        echo "<ul>";
        echo "<li>ID: " . $row['notfId'] . "</li>";
        echo "<li>Name: " . $row['notfName'] . "</li>";
        echo "<li>Email: " . $row['notfEmail'] . "</li>";
        echo "<li>Phone: " . $row['notfPhone'] . "</li>";
        echo "<li>Address: " . $row['notfAddress'] . "</li>";
        echo "<li>Message: " . $row['notfMsg'] . "</li>";
        echo "<li>Property ID: " . $row['adId'] . "</li>";
        echo "<li>Owner ID: " . $row['ownerId'] . "</li>";
        echo "<li>Sender ID: " . $row['renterId'] . "</li>";
        echo "<li>Date: " . $row['notfDate'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red;'><strong>FAILED:</strong> Message not found in database!</p>";
    }
} else {
    echo "<p style='color:red;'>Form not submitted correctly</p>";
}

// Show recent messages for context
echo "<h3>Recent Messages in Database:</h3>";
$db = new Database();
$query = "SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 5";
$recent = $db->select($query);

if($recent && $recent->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message (First 50 chars)</th><th>Property ID</th><th>Date</th></tr>";
    while($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['notfId'] . "</td>";
        echo "<td>" . $row['notfName'] . "</td>";
        echo "<td>" . $row['notfEmail'] . "</td>";
        echo "<td>" . substr($row['notfMsg'], 0, 50) . "...</td>";
        echo "<td>" . $row['adId'] . "</td>";
        echo "<td>" . $row['notfDate'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No messages found in database</p>";
}
?>
