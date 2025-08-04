<?php
// Debug version of add_property.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Debug Add Property Form</h2>";

try {
    include"inc/header.php";
    echo "✅ Header loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Header error: " . $e->getMessage() . "<br>";
    // Try alternative includes
    try {
        include_once('../lib/Session.php');
        include_once('../classes/Property.php');
        Session::init();
        $pro = new Property();
        echo "✅ Alternative includes loaded<br>";
    } catch (Exception $e2) {
        echo "❌ Alternative include error: " . $e2->getMessage() . "<br>";
        die("Cannot load required files");
    }
}

/*========================
User Access Control
========================*/
echo "🔐 Checking user session...<br>";
if(Session::get("userLevel") != 2){
    echo "❌ Access denied - User level: " . Session::get("userLevel") . "<br>";
    echo "👤 User ID: " . Session::get("userId") . "<br>";
    echo "📧 Email: " . Session::get("userEmail") . "<br>";
    echo "<strong>Solution:</strong> <a href='../signin.php'>Login as Owner</a><br>";
    exit();
} else {
    echo "✅ Access granted - User level: " . Session::get("userLevel") . "<br>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
    echo "📝 Form submitted! Processing...<br>";
    try {
        echo "🔍 Form data received:<br>";
        echo "Title: " . (isset($_POST['adtitle']) ? $_POST['adtitle'] : 'NOT SET') . "<br>";
        echo "Category: " . (isset($_POST['catid']) ? $_POST['catid'] : 'NOT SET') . "<br>";
        echo "Date: " . (isset($_POST['addate']) ? $_POST['addate'] : 'NOT SET') . "<br>";
        echo "Area: " . (isset($_POST['adarea']) ? $_POST['adarea'] : 'NOT SET') . "<br>";
        
        $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
        echo "📋 Result: " . $getAdMsg . "<br>";
    } catch (Exception $e) {
        echo "❌ Property insert error: " . $e->getMessage() . "<br>";
        echo "🔍 Error details: " . $e->getTraceAsString() . "<br>";
    } catch (Error $e) {
        echo "❌ Fatal error: " . $e->getMessage() . "<br>";
        echo "🔍 Error details: " . $e->getTraceAsString() . "<br>";
    }
}

?>

<h3>✅ TinyMCE-Free Add Property Form</h3>
<p>All textareas are simple HTML - No API key required!</p>

<form enctype="multipart/form-data" action="" method="POST">
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Basic Information</h4>
        
        <label>Property Title *</label><br>
        <input type="text" name="adtitle" placeholder="Property Title" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Property Type *</label><br>
        <select name="catid" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="">Choose Property Type</option>
            <option value="1">Apartment</option>
            <option value="2">House</option>
            <option value="3">Room</option>
        </select><br>
        
        <label>Available From *</label><br>
        <input type="date" name="addate" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Built Year *</label><br>
        <input type="text" name="builtyear" placeholder="Built Year" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Description (Optional)</label><br>
        <textarea name="addetails" placeholder="Enter property description... (Optional)" style="width: 100%; min-height: 100px; margin: 5px 0; padding: 10px; border: 2px solid #ddd; border-radius: 5px;"></textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Location</h4>
        
        <label>Area *</label><br>
        <input type="text" name="adarea" placeholder="Area Name" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Address *</label><br>
        <textarea name="adaddress" placeholder="Enter full address..." style="width: 100%; min-height: 80px; margin: 5px 0; padding: 10px; border: 2px solid #ddd; border-radius: 5px;"></textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Quick Specification</h4>
        
        <label>Property Size (Sq Ft) *</label><br>
        <input type="number" name="adsize" placeholder="Sq Ft" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Total Floor *</label><br>
        <input type="number" name="totalfloor" placeholder="Total Floor" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Total Unit *</label><br>
        <input type="number" name="totalunit" placeholder="Total Unit" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Total Room *</label><br>
        <input type="number" name="totalroom" placeholder="Total Room" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Total Bedroom *</label><br>
        <input type="number" name="totalbed" placeholder="Total Bedroom" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Total Bathroom *</label><br>
        <input type="number" name="totalbath" placeholder="Total Bathroom" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Attach Bath</label><br>
        <input type="number" name="attachbath" placeholder="Attach Bath" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Common Bath</label><br>
        <input type="number" name="commonbath" placeholder="Common Bath" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Balconies</label><br>
        <input type="number" name="totalbalcony" placeholder="Total Balcony" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Floor No *</label><br>
        <input type="number" name="floorno" placeholder="Floor No" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Floor Type *</label><br>
        <select name="floortype" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="">Choose Floor Type</option>
            <option value="Tiles">Tiles</option>
            <option value="Mosice">Mosice</option>
            <option value="Marble">Marble</option>
            <option value="Normal">Normal</option>
        </select><br>
        
        <label>Preferred Renter *</label><br>
        <textarea name="prefferedrenter" placeholder="Describe preferred renter type..." style="width: 100%; min-height: 80px; margin: 5px 0; padding: 10px; border: 2px solid #ddd; border-radius: 5px;"></textarea><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Facilities</h4>
        
        <label>Lift/Elevator *</label><br>
        <select name="liftelevetor" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Generator *</label><br>
        <select name="adgenerator" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Wi-Fi *</label><br>
        <select name="adwifi" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Car Parking *</label><br>
        <select name="carparking" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Open Space *</label><br>
        <select name="openspace" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Play Ground *</label><br>
        <select name="playground" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>CCTV *</label><br>
        <select name="cctv" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
        
        <label>Security Guard *</label><br>
        <select name="sguard" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Price Details</h4>
        
        <label>Rent Type *</label><br>
        <select name="renttype" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="mo">Per month</option>
            <option value="we">Per week</option>
        </select><br>
        
        <label>Rent (BDT) *</label><br>
        <input type="number" name="adrent" placeholder="Rent (BDT)" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Gas Bill *</label><br>
        <input type="text" name="gasbill" placeholder="Gas Bill" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Electric Bill Type *</label><br>
        <select name="ebilltype" style="width: 100%; margin: 5px 0; padding: 10px;">
            <option value="exc">Excluding</option>
            <option value="inc">Including</option>
        </select><br>
        
        <label>Electric Bill *</label><br>
        <input type="text" name="electricbill" placeholder="Electric Bill" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>Service Charge *</label><br>
        <input type="number" name="scharge" placeholder="Service Charge" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
        
        <label>
            <input type="checkbox" name="adnegotiable" value="negotiable"/>
            Negotiable
        </label><br>
    </div>
    
    <div style="border: 2px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h4>Property Photo</h4>
        
        <label>Upload Photo *</label><br>
        <input type="file" name="adimg" style="width: 100%; margin: 5px 0; padding: 10px;"/><br>
    </div>
    
    <button type="submit" name="submit_ad" style="background: #007cba; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
        Submit Property
    </button>
</form>

<hr>
<p><strong>🎉 TinyMCE Status:</strong> Completely removed! All textareas are simple HTML.</p>
<p><strong>📝 Description:</strong> Optional field - form will submit successfully without it.</p>
<p><a href="../index.php">← Back to Main Site</a></p>
