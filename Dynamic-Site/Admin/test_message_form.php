<!DOCTYPE html>
<html>
<head>
    <title>Message Form Test</title>
</head>
<body>
    <h2>Test Message Form</h2>
    
    <?php
    include '../lib/Session.php';
    Session::init();
    include '../lib/Database.php';
    include '../helpers/Format.php';

    spl_autoload_register(function($class){
        include_once '../classes/'.$class.'.php';
    });

    $ntf = new Notification();
    
    // Simulate user being logged in
    Session::set("userlogin", true);
    Session::set("userId", 2);
    
    // Simulate property data (like URL parameter adid=1)
    $adId = 1;
    $ownerId = 1;
    Session::set("adId", $adId);
    Session::set("ownerId", $ownerId);
    $renterId = Session::get("userId");
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendmsg'])){
        echo "<h3>Form Submitted!</h3>";
        echo "<p>Processing message...</p>";
        
        $sendNotif = $ntf->notificationInsert($adId, $ownerId, $renterId, $_POST);
        echo "<div style='padding:10px; margin:10px 0; border:1px solid #ccc;'>" . $sendNotif . "</div>";
        
        // Check if it was saved
        $db = new Database();
        $checkQuery = "SELECT * FROM tbl_notification WHERE notfEmail = '" . $_POST['email'] . "' ORDER BY notfId DESC LIMIT 1";
        $result = $db->select($checkQuery);
        if($result) {
            echo "<p style='color:green;'>✓ Message found in database!</p>";
            $row = $result->fetch_assoc();
            echo "<p>Message ID: " . $row['notfId'] . " | Date: " . $row['notfDate'] . "</p>";
        } else {
            echo "<p style='color:red;'>✗ Message not found in database!</p>";
        }
    }
    ?>
    
    <h3>User Status:</h3>
    <p>Logged in: <?php echo Session::get("userlogin") ? "Yes" : "No"; ?></p>
    <p>User ID: <?php echo Session::get("userId"); ?></p>
    <p>Property ID: <?php echo Session::get("adId"); ?></p>
    <p>Owner ID: <?php echo Session::get("ownerId"); ?></p>
    
    <h3>Send Message:</h3>
    <form method="POST" action="">
        <p>
            <label><b>Name:</b></label><br>
            <input type="text" name="name" value="Test User" required style="width:300px; padding:5px;">
        </p>
        
        <p>
            <label><b>Email:</b></label><br>
            <input type="email" name="email" value="test@example.com" required style="width:300px; padding:5px;">
        </p>
        
        <p>
            <label><b>Mobile No:</b></label><br>
            <input type="text" name="phone" value="1234567890" required style="width:300px; padding:5px;">
        </p>
        
        <p>
            <label><b>Address:</b></label><br>
            <textarea name="address" required style="width:300px; height:60px; padding:5px;">123 Test Street, Test City</textarea>
        </p>
        
        <p>
            <label><b>Message:</b></label><br>
            <textarea name="message" required style="width:300px; height:100px; padding:5px;">I am interested in this property. Please contact me with more details.</textarea>
        </p>
        
        <p>
            <button type="submit" name="sendmsg" style="padding:10px 20px; background:#007cba; color:white; border:none; cursor:pointer;">Send Message</button>
        </p>
    </form>
    
    <h3>Recent Messages:</h3>
    <?php
    $db = new Database();
    $query = "SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 3";
    $recent = $db->select($query);
    if($recent) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
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
        echo "<p>No recent messages found.</p>";
    }
    ?>
    
</body>
</html>
