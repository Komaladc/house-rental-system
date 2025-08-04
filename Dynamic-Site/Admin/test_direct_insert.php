<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include"inc/header.php";

if(Session::get("userLevel") != 2){
    echo"<script>window.location='../index.php'</script>";
}

echo "<h2>🔧 Direct Database Insert Test</h2>";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_insert'])){
    echo "<div style='background:#f8f9fa;padding:20px;margin:10px 0;border:1px solid #dee2e6;border-radius:5px;'>";
    
    try {
        $db = new Database();
        $userId = Session::get("userId");
        
        // Simple test insert
        $testQuery = "INSERT INTO tbl_ad(adTitle, adImg, catId, adDate, builtYear, adDetails, adArea, adAddress, adSize, totalFloor, totalUnit, totalRoom, totalBed, totalBath, attachBath, commonBath, totalBelcony, floorNo, floorType, prefferedRenter, liftElevetor, adGenerator, adWifi, carParking, openSpace, playGround, ccTV, sGuard, rentType, adRent, gasBill, electricBill, eBillType, sCharge, adNegotiable, userId) VALUES(
            'Direct Test Property',
            'images/1.jpg',
            '1',
            '2025-07-31',
            '2020',
            'Direct test description',
            'Test Area',
            'Test Address',
            '1200',
            '5',
            '1',
            '3',
            '2',
            '2',
            '1',
            '1',
            '1',
            '3',
            'Tiles',
            'Family preferred',
            'Yes',
            'Yes',
            'Yes',
            'Yes',
            'Yes',
            'No',
            'Yes',
            'No',
            'mo',
            '25000',
            '500',
            '1000',
            'exc',
            '2000',
            'negotiable',
            '$userId'
        )";
        
        echo "<h4>Testing direct database insert...</h4>";
        echo "<pre>Query: " . htmlspecialchars($testQuery) . "</pre>";
        
        $result = $db->insert($testQuery);
        
        if($result) {
            echo "<div style='background:#d4edda;padding:10px;border-radius:5px;color:#155724;'>";
            echo "✅ Direct insert successful!<br>";
            echo "Insert ID: " . $result . "<br>";
            echo "</div>";
            
            // Verify the insert
            $verifyQuery = "SELECT * FROM tbl_ad WHERE adTitle = 'Direct Test Property' ORDER BY adId DESC LIMIT 1";
            $verifyResult = $db->select($verifyQuery);
            
            if($verifyResult && $verifyResult->num_rows > 0) {
                $property = $verifyResult->fetch_assoc();
                echo "<h4>✅ Property verified in database:</h4>";
                echo "<pre>" . print_r($property, true) . "</pre>";
            } else {
                echo "<h4>❌ Property not found after insert</h4>";
            }
        } else {
            echo "<div style='background:#f8d7da;padding:10px;border-radius:5px;color:#721c24;'>";
            echo "❌ Direct insert failed!<br>";
            echo "Database error: " . mysqli_error($db->link) . "<br>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background:#f8d7da;padding:10px;border-radius:5px;color:#721c24;'>";
        echo "❌ Exception: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Show current properties count
try {
    $db = new Database();
    $countQuery = "SELECT COUNT(*) as total FROM tbl_ad";
    $countResult = $db->select($countQuery);
    if($countResult) {
        $count = $countResult->fetch_assoc();
        echo "<p>📊 Current total properties in database: <strong>" . $count['total'] . "</strong></p>";
    }
    
    // Show recent properties
    $recentQuery = "SELECT adId, adTitle, adDate, userId FROM tbl_ad ORDER BY adId DESC LIMIT 5";
    $recentResult = $db->select($recentQuery);
    if($recentResult && $recentResult->num_rows > 0) {
        echo "<h3>📋 Recent Properties:</h3>";
        echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Date</th><th>User ID</th></tr>";
        while($row = $recentResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['adId'] . "</td>";
            echo "<td>" . $row['adTitle'] . "</td>";
            echo "<td>" . $row['adDate'] . "</td>";
            echo "<td>" . $row['userId'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>Error getting property count: " . $e->getMessage() . "</p>";
}
?>

<form method="POST" action="">
    <div style="background: #f8f9fa; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h3>🚀 Test Direct Database Insert</h3>
        <p>This will attempt to insert a test property directly into the database.</p>
        <button type="submit" name="test_insert" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Test Direct Insert</button>
    </div>
</form>

<p><a href="add_property.php">← Back to Add Property Form</a></p>

<?php include"inc/footer.php";?>
