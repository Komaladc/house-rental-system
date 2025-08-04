<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Add Property Form Debug Test</h2>";

// Test basic PHP
echo "✅ PHP is working<br>";

// Test includes
try {
    include"inc/header.php";
    echo "✅ Header included successfully<br>";
} catch (Exception $e) {
    echo "❌ Header include failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test if we're logged in with proper user level
echo "📝 User Level: " . Session::get("userLevel") . "<br>";
echo "📝 User ID: " . Session::get("userId") . "<br>";

if(Session::get("userLevel") != 2){
    echo "❌ Access denied - user level is not 2<br>";
    exit;
}

// Test form processing
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
    echo "<hr><h3>📥 Form Data Received</h3>";
    
    // Show all posted data
    foreach($_POST as $key => $value) {
        if($key !== 'submit_ad') {
            echo "- $key: " . (empty($value) ? '<span style="color:red;">EMPTY</span>' : $value) . "<br>";
        }
    }
    
    echo "<h3>📁 File Data</h3>";
    if(isset($_FILES['adimg'])) {
        echo "- File name: " . $_FILES['adimg']['name'] . "<br>";
        echo "- File size: " . $_FILES['adimg']['size'] . "<br>";
        echo "- File error: " . $_FILES['adimg']['error'] . "<br>";
        echo "- File type: " . $_FILES['adimg']['type'] . "<br>";
    } else {
        echo "- No file uploaded<br>";
    }
    
    // Validate required fields before attempting insert
    echo "<h3>🔍 Validation Check</h3>";
    $required_fields = [
        'adtitle', 'catid', 'addate', 'builtyear', 'adarea', 'adaddress', 
        'adsize', 'totalfloor', 'totalunit', 'totalroom', 'totalbed', 
        'totalbath', 'floorno', 'floortype', 'prefferedrenter', 
        'liftelevetor', 'adgenerator', 'adwifi', 'carparking', 'openspace', 
        'playground', 'cctv', 'sguard', 'renttype', 'adrent', 'gasbill', 
        'ebilltype', 'electricbill', 'scharge'
    ];
    
    $missing_fields = [];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if(!empty($missing_fields)) {
        echo "<span style='color:red;'>❌ Missing required fields: " . implode(', ', $missing_fields) . "</span><br>";
    } else {
        echo "<span style='color:green;'>✅ All required fields present</span><br>";
    }
    
    // Test database connection before insert
    echo "<h3>🗄️ Database Connection Test</h3>";
    try {
        // Create a new Database instance to test connection
        $testDb = new Database();
        $testQuery = "SELECT COUNT(*) as count FROM tbl_ad";
        $testResult = $testDb->select($testQuery);
        if($testResult) {
            echo "✅ Database connection working<br>";
        } else {
            echo "❌ Database query failed<br>";
        }
    } catch (Exception $e) {
        echo "❌ Database connection error: " . $e->getMessage() . "<br>";
    }
    
    // Try property insert with better error handling
    if(empty($missing_fields)) {
        try {
            echo "<h3>🚀 Attempting Property Insert</h3>";
            
            // Log the exact data being sent
            echo "📝 Sending data to propertyInsert...<br>";
            
            $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
            echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>";
            echo "✅ Success: " . $getAdMsg;
            echo "</div>";
            
        } catch (Error $e) {
            echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
            echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
            echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
            echo "🔍 Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
            echo "❌ Exception: " . $e->getMessage() . "<br>";
            echo "� File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
            echo "�🔍 Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeeba;border-radius:5px;margin:10px 0;'>";
        echo "⚠️ Cannot proceed with property insert due to missing required fields.";
        echo "</div>";
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .form-group { margin: 15px 0; }
    label { display: block; font-weight: bold; margin: 5px 0; }
    input, select, textarea { width: 100%; max-width: 400px; padding: 8px; margin: 5px 0; }
    button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #005a87; }
</style>

<h3>🧪 Test Form (All Required Fields)</h3>
<form enctype="multipart/form-data" action="" method="POST">
    <div class="form-group">
        <label>Property Title *</label>
        <input type="text" name="adtitle" value="Test Property" required>
    </div>
    
    <div class="form-group">
        <label>Property Type *</label>
        <select name="catid" required>
            <option value="">Choose Property Type</option>
            <?php
            $getCat = $cat->getAllCat();
            if($getCat){
                while($getCatId = $getCat->fetch_assoc()){ ?>
                    <option value="<?php echo $getCatId['catId'];?>"><?php echo $getCatId['catName'];?></option>
            <?php } } ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Available From *</label>
        <input type="date" name="addate" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    
    <div class="form-group">
        <label>Built Year *</label>
        <input type="text" name="builtyear" value="2020" required>
    </div>
    
    <div class="form-group">
        <label>Description (Optional)</label>
        <textarea name="addetails">Test description</textarea>
    </div>
    
    <div class="form-group">
        <label>Area *</label>
        <input type="text" name="adarea" value="Test Area" required>
    </div>
    
    <div class="form-group">
        <label>Address *</label>
        <textarea name="adaddress" required>Test Address, Test City</textarea>
    </div>
    
    <div class="form-group">
        <label>Property Size (Sq Ft) *</label>
        <input type="number" name="adsize" value="1200" required>
    </div>
    
    <div class="form-group">
        <label>Total Floor *</label>
        <input type="number" name="totalfloor" value="5" required>
    </div>
    
    <div class="form-group">
        <label>Total Unit *</label>
        <input type="number" name="totalunit" value="1" required>
    </div>
    
    <div class="form-group">
        <label>Total Room *</label>
        <input type="number" name="totalroom" value="3" required>
    </div>
    
    <div class="form-group">
        <label>Total Bedroom *</label>
        <input type="number" name="totalbed" value="2" required>
    </div>
    
    <div class="form-group">
        <label>Total Bathroom *</label>
        <input type="number" name="totalbath" value="2" required>
    </div>
    
    <div class="form-group">
        <label>Attach Bath</label>
        <input type="number" name="attachbath" value="1">
    </div>
    
    <div class="form-group">
        <label>Common Bath</label>
        <input type="number" name="commonbath" value="1">
    </div>
    
    <div class="form-group">
        <label>Total Balcony</label>
        <input type="number" name="totalbalcony" value="1">
    </div>
    
    <div class="form-group">
        <label>Floor No *</label>
        <input type="number" name="floorno" value="3" required>
    </div>
    
    <div class="form-group">
        <label>Floor Type *</label>
        <select name="floortype" required>
            <option value="">Choose Floor Type</option>
            <option value="Tiles" selected>Tiles</option>
            <option value="Mosice">Mosice</option>
            <option value="Marble">Marble</option>
            <option value="Normal">Normal</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Preferred Renter *</label>
        <textarea name="prefferedrenter" required>Family preferred</textarea>
    </div>
    
    <!-- Facilities -->
    <div class="form-group">
        <label>Lift/Elevator</label>
        <select name="liftelevetor">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Generator</label>
        <select name="adgenerator">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Wi-Fi</label>
        <select name="adwifi">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Car Parking</label>
        <select name="carparking">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Open Space</label>
        <select name="openspace">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Play Ground</label>
        <select name="playground">
            <option value="No" selected>No</option>
            <option value="Yes">Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>CCTV</label>
        <select name="cctv">
            <option value="No">No</option>
            <option value="Yes" selected>Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Security Guard</label>
        <select name="sguard">
            <option value="No" selected>No</option>
            <option value="Yes">Yes</option>
        </select>
    </div>
    
    <!-- Price Details -->
    <div class="form-group">
        <label>Rent Type</label>
        <select name="renttype">
            <option value="mo" selected>Per month</option>
            <option value="we">Per week</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Rent (BDT) *</label>
        <input type="number" name="adrent" value="25000" required>
    </div>
    
    <div class="form-group">
        <label>Gas Bill *</label>
        <input type="text" name="gasbill" value="500" required>
    </div>
    
    <div class="form-group">
        <label>Electric Bill Type *</label>
        <select name="ebilltype" required>
            <option value="exc" selected>Excluding</option>
            <option value="inc">Including</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Electric Bill Amount *</label>
        <input type="text" name="electricbill" value="1000" required>
    </div>
    
    <div class="form-group">
        <label>Service Charge *</label>
        <input type="number" name="scharge" value="2000" required>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="adnegotiable" value="negotiable"> Negotiable
        </label>
    </div>
    
    <div class="form-group">
        <label>Property Photo</label>
        <input type="file" name="adimg" accept="image/*">
        <small>Optional - will use default image if none selected</small>
    </div>
    
    <button type="submit" name="submit_ad">🚀 Test Submit Property</button>
</form>

<?php include"inc/footer.php";?>
