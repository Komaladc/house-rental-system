<?php
// Standalone test for propertyInsert function
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Property Insert Test (No Auth Required)</h2>";

try {
    // Include required files
    include_once('../config/config.php');
    include_once('../lib/Database.php');
    include_once('../helpers/Format.php');
    include_once('../lib/Session.php');
    include_once('../classes/Property.php');
    
    echo "✅ All classes loaded successfully<br>";
    
    // Initialize classes
    $db = new Database();
    $fm = new Format();
    $pro = new Property();
    
    echo "✅ Classes instantiated successfully<br>";
    
    // Test form processing if submitted
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
        echo "<hr><h3>📝 Processing Form Submission</h3>";
        
        // Mock user session for testing
        Session::init();
        Session::set("userId", 1); // Test user ID
        
        try {
            echo "🔍 Form data received:<br>";
            foreach($_POST as $key => $value){
                if($key !== 'submit_ad'){
                    echo "- " . $key . ": " . $value . "<br>";
                }
            }
            
            $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
            echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "📋 Result: " . $getAdMsg;
            echo "</div>";
        } catch (Exception $e) {
            echo "<div style='background: #ffe8e8; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "❌ Property insert error: " . $e->getMessage() . "<br>";
            echo "🔍 Error details: " . $e->getTraceAsString();
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Setup error: " . $e->getMessage() . "<br>";
    echo "🔍 Error details: " . $e->getTraceAsString() . "<br>";
}

?>

<h3>🧪 Test Property Form</h3>
<p><strong>Note:</strong> This bypasses authentication for testing purposes only.</p>

<form enctype="multipart/form-data" action="" method="POST" style="max-width: 600px;">
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Basic Information</h4>
        
        <label>Property Title *</label><br>
        <input type="text" name="adtitle" value="Test Property" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Property Type *</label><br>
        <select name="catid" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="1">Apartment</option>
            <option value="2">House</option>
            <option value="3">Room</option>
        </select><br>
        
        <label>Available From *</label><br>
        <input type="date" name="addate" value="2025-08-01" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Built Year *</label><br>
        <input type="text" name="builtyear" value="2020" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Description (Optional)</label><br>
        <textarea name="addetails" style="width: 100%; min-height: 60px; margin: 5px 0; padding: 8px;">Test property description - this is optional</textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Location</h4>
        
        <label>Area *</label><br>
        <input type="text" name="adarea" value="Test Area" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Address *</label><br>
        <textarea name="adaddress" style="width: 100%; min-height: 60px; margin: 5px 0; padding: 8px;">123 Test Street, Test City</textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Specifications</h4>
        
        <label>Property Size (Sq Ft) *</label><br>
        <input type="number" name="adsize" value="1200" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Total Floor *</label><br>
        <input type="number" name="totalfloor" value="5" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Total Unit *</label><br>
        <input type="number" name="totalunit" value="1" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Total Room *</label><br>
        <input type="number" name="totalroom" value="3" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Total Bedroom *</label><br>
        <input type="number" name="totalbed" value="2" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Total Bathroom *</label><br>
        <input type="number" name="totalbath" value="2" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Attach Bath</label><br>
        <input type="number" name="attachbath" value="1" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Common Bath</label><br>
        <input type="number" name="commonbath" value="1" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Balconies</label><br>
        <input type="number" name="totalbalcony" value="1" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Floor No *</label><br>
        <input type="number" name="floorno" value="3" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Floor Type *</label><br>
        <select name="floortype" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Tiles">Tiles</option>
        </select><br>
        
        <label>Preferred Renter *</label><br>
        <textarea name="prefferedrenter" style="width: 100%; min-height: 60px; margin: 5px 0; padding: 8px;">Family preferred</textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Facilities</h4>
        
        <label>Lift/Elevator *</label><br>
        <select name="liftelevetor" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Generator *</label><br>
        <select name="adgenerator" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Wi-Fi *</label><br>
        <select name="adwifi" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Car Parking *</label><br>
        <select name="carparking" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Open Space *</label><br>
        <select name="openspace" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Play Ground *</label><br>
        <select name="playground" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>CCTV *</label><br>
        <select name="cctv" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        
        <label>Security Guard *</label><br>
        <select name="sguard" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Price Details</h4>
        
        <label>Rent Type *</label><br>
        <select name="renttype" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="mo">Per month</option>
            <option value="we">Per week</option>
        </select><br>
        
        <label>Rent (BDT) *</label><br>
        <input type="number" name="adrent" value="25000" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Gas Bill *</label><br>
        <input type="text" name="gasbill" value="Included" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Electric Bill Type *</label><br>
        <select name="ebilltype" style="width: 100%; margin: 5px 0; padding: 8px;">
            <option value="exc">Excluding</option>
            <option value="inc">Including</option>
        </select><br>
        
        <label>Electric Bill *</label><br>
        <input type="text" name="electricbill" value="Separate" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>Service Charge *</label><br>
        <input type="number" name="scharge" value="2000" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        
        <label>
            <input type="checkbox" name="adnegotiable" value="negotiable"/>
            Negotiable
        </label><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
        <h4>Property Photo (Optional)</h4>
        
        <label>Upload Photo</label><br>
        <input type="file" name="adimg" style="width: 100%; margin: 5px 0; padding: 8px;"/><br>
        <small>Note: Image is optional - form will work without it</small>
    </div>
    
    <button type="submit" name="submit_ad" style="background: #007cba; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
        🧪 Test Submit Property
    </button>
</form>

<hr>
<p><a href="test_connection_simple.php">🔧 Database Connection Test</a></p>
<p><a href="../index.php">← Back to Main Site</a></p>
