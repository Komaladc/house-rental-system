<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// For testing, let's simulate a logged-in owner
if(!isset($_SESSION['userId'])) {
    $_SESSION['userId'] = 1;
    $_SESSION['userLevel'] = 2; // Owner level
    $_SESSION['userName'] = 'Test Owner';
}

include "../classes/Session.php";
include "../config/config.php";
include "../lib/Database.php";
include "../classes/Property.php";
include "../classes/Category.php";

echo "<h1>🔧 Add Property Diagnostic & Test</h1>";

// Test session
echo "<div style='background:#e8f5e9;padding:10px;margin:10px 0;border-radius:5px;'>";
echo "<h3>👤 Session Information:</h3>";
echo "User ID: " . Session::get("userId") . "<br>";
echo "User Level: " . Session::get("userLevel") . "<br>";
echo "User Name: " . Session::get("userName") . "<br>";
echo "</div>";

// Test database and classes
try {
    $db = new Database();
    echo "<p style='color:green;'>✅ Database connection successful!</p>";
    
    $pro = new Property();
    echo "<p style='color:green;'>✅ Property class loaded!</p>";
    
    $cat = new Category();
    echo "<p style='color:green;'>✅ Category class loaded!</p>";
    
    // Check categories
    $categories = $cat->getAllCat();
    if($categories && $categories->num_rows > 0) {
        echo "<h3>🏠 Available Categories:</h3>";
        echo "<ul>";
        while($category = $categories->fetch_assoc()) {
            echo "<li>ID: " . $category['catId'] . " - " . $category['catName'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>❌ No categories found!</p>";
        echo "<p><a href='add_category.php'>Add Categories First</a></p>";
    }
    
    // Check upload directory
    $uploadDir = "../uploads/ad_image/";
    if(!is_dir($uploadDir)) {
        if(mkdir($uploadDir, 0777, true)) {
            echo "<p style='color:green;'>✅ Upload directory created successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to create upload directory</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ Upload directory exists</p>";
    }
    
    // Test form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_submit'])) {
        echo "<div style='background:#fff3cd;padding:10px;margin:10px 0;border-radius:5px;'>";
        echo "<h3>🧪 Testing Property Insertion:</h3>";
        
        // Prepare test data
        $testData = [
            'adtitle' => 'Test Property ' . date('Y-m-d H:i:s'),
            'catid' => '1',
            'addate' => date('Y-m-d'),
            'builtyear' => '2020',
            'addetails' => 'This is a test property description.',
            'adaddress' => '123 Test Street',
            'adarea' => 'Test Area',
            'adsize' => '1200',
            'totalfloor' => '3',
            'totalunit' => '1',
            'totalroom' => '5',
            'totalbed' => '3',
            'totalbath' => '2',
            'attachbath' => '1',
            'commonbath' => '1',
            'totalbalcony' => '2',
            'floorno' => '2',
            'floortype' => 'Tiles',
            'prefferedrenter' => 'Family preferred',
            'liftelevetor' => 'Yes',
            'adgenerator' => 'Yes',
            'adwifi' => 'Yes',
            'carparking' => 'Yes',
            'openspace' => 'Yes',
            'playground' => 'No',
            'cctv' => 'Yes',
            'sguard' => 'Yes',
            'renttype' => 'mo',
            'adrent' => '25000',
            'gasbill' => 'Including',
            'ebilltype' => 'exc',
            'electricbill' => 'According to meter',
            'scharge' => '2000'
        ];
        
        $testFiles = [
            'adimg' => [
                'name' => '',
                'size' => 0,
                'tmp_name' => '',
                'error' => 4 // UPLOAD_ERR_NO_FILE
            ]
        ];
        
        try {
            $result = $pro->propertyInsert($testData, $testFiles);
            echo $result;
            
            // Check if it was actually inserted
            $checkQuery = "SELECT * FROM tbl_ad WHERE userId = '" . Session::get("userId") . "' ORDER BY adId DESC LIMIT 1";
            $lastProperty = $db->select($checkQuery);
            
            if($lastProperty && $lastProperty->num_rows > 0) {
                $property = $lastProperty->fetch_assoc();
                echo "<p style='color:green;'>✅ Property found in database: " . $property['adTitle'] . "</p>";
            } else {
                echo "<p style='color:red;'>❌ Property not found in database</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Exception: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    .btn:hover { background: #0056b3; }
</style>

<div class="test-form">
    <h3>🧪 Quick Test</h3>
    <form method="POST">
        <button type="submit" name="test_submit" class="btn">Test Property Insertion</button>
    </form>
</div>

<div style="margin-top: 30px;">
    <h3>🔗 Navigation:</h3>
    <a href="add_property.php" style="background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin-right:10px;">🏠 Add Property Form</a>
    <a href="property_list_admin.php" style="background:#17a2b8; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin-right:10px;">📋 Property List</a>
    <a href="add_category.php" style="background:#ffc107; color:black; padding:10px 20px; text-decoration:none; border-radius:5px;">📂 Add Category</a>
</div>
