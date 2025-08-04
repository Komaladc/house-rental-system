<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Enhanced Add Property Form Debug Test</h2>";

// Test basic PHP
echo "✅ PHP is working<br>";

// Test includes with better error handling
try {
    include"inc/header.php";
    echo "✅ Header included successfully<br>";
} catch (Exception $e) {
    echo "❌ Header include failed: " . $e->getMessage() . "<br>";
    // Try to include files manually for debugging
    try {
        include_once('../lib/Session.php');
        include_once('../classes/Property.php');
        include_once('../classes/Category.php');
        Session::init();
        $pro = new Property();
        $cat = new Category();
        echo "✅ Manual includes successful<br>";
    } catch (Exception $e2) {
        echo "❌ Manual include failed: " . $e2->getMessage() . "<br>";
        exit;
    }
}

// Test if we're logged in with proper user level
echo "📝 User Level: " . Session::get("userLevel") . "<br>";
echo "📝 User ID: " . Session::get("userId") . "<br>";

if(Session::get("userLevel") != 2){
    echo "❌ Access denied - user level is not 2<br>";
    echo "<p><strong>Solution:</strong> <a href='../signin.php'>Please login as an Owner (userLevel = 2)</a></p>";
    echo "<hr>";
}

// Test form processing with enhanced error handling
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
    echo "<hr><h3>📥 Form Data Received</h3>";
    
    // Show all posted data
    foreach($_POST as $key => $value) {
        if($key !== 'submit_ad') {
            echo "- $key: " . (empty($value) ? '<span style="color:red;">EMPTY</span>' : htmlspecialchars($value)) . "<br>";
        }
    }
    
    echo "<h3>📁 File Data</h3>";
    if(isset($_FILES['adimg']) && !empty($_FILES['adimg']['name'])) {
        echo "- File name: " . $_FILES['adimg']['name'] . "<br>";
        echo "- File size: " . $_FILES['adimg']['size'] . " bytes<br>";
        echo "- File error: " . $_FILES['adimg']['error'] . "<br>";
        echo "- File type: " . $_FILES['adimg']['type'] . "<br>";
        echo "- Temp name: " . $_FILES['adimg']['tmp_name'] . "<br>";
    } else {
        echo "- No file uploaded (using default image)<br>";
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
    
    // Check user authentication
    if(Session::get("userLevel") != 2) {
        echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeeba;border-radius:5px;margin:10px 0;'>";
        echo "⚠️ Cannot proceed: User not logged in as Owner (userLevel = 2)";
        echo "</div>";
    } else {
        // Test database connection before insert
        echo "<h3>🗄️ Database Connection Test</h3>";
        try {
            // Create a separate database instance since $pro->db is private
            $testDb = new Database();
            $testQuery = "SELECT COUNT(*) as count FROM tbl_ad";
            $testResult = $testDb->select($testQuery);
            if($testResult) {
                $row = $testResult->fetch_assoc();
                echo "✅ Database connection working, current properties: " . $row['count'] . "<br>";
            } else {
                echo "❌ Database query failed<br>";
            }
        } catch (Exception $e) {
            echo "❌ Database connection error: " . $e->getMessage() . "<br>";
        }
        
        // Try property insert with comprehensive error handling
        if(empty($missing_fields)) {
            try {
                echo "<h3>🚀 Attempting Property Insert</h3>";
                
                // Log the exact data being sent
                echo "📝 Calling propertyInsert with " . count($_POST) . " POST fields and " . count($_FILES) . " file fields<br>";
                
                $startTime = microtime(true);
                $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
                $endTime = microtime(true);
                
                echo "<div style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>";
                echo "✅ <strong>SUCCESS!</strong><br>";
                echo "📋 Result: " . $getAdMsg . "<br>";
                echo "⏱️ Execution time: " . round(($endTime - $startTime) * 1000, 2) . "ms<br>";
                echo "</div>";
                
                // Verify insertion
                try {
                    $verifyDb = new Database();
                    $verifyQuery = "SELECT * FROM tbl_ad ORDER BY adId DESC LIMIT 1";
                    $verifyResult = $verifyDb->select($verifyQuery);
                    if($verifyResult) {
                        $lastProperty = $verifyResult->fetch_assoc();
                        echo "<h4>🔍 Verification</h4>";
                        echo "✅ Last inserted property ID: " . $lastProperty['adId'] . "<br>";
                        echo "✅ Property title: " . $lastProperty['adTitle'] . "<br>";
                        echo "✅ Property image: " . $lastProperty['adImg'] . "<br>";
                    }
                } catch (Exception $e) {
                    echo "⚠️ Could not verify insertion: " . $e->getMessage() . "<br>";
                }
                
            } catch (Error $e) {
                echo "<div style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
                echo "❌ <strong>FATAL ERROR:</strong><br>";
                echo "📝 Message: " . $e->getMessage() . "<br>";
                echo "📍 File: " . $e->getFile() . "<br>";
                echo "📍 Line: " . $e->getLine() . "<br>";
                echo "🔍 Stack trace:<br><pre style='font-size:12px;max-height:200px;overflow:auto;'>" . $e->getTraceAsString() . "</pre>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
                echo "❌ <strong>EXCEPTION:</strong><br>";
                echo "📝 Message: " . $e->getMessage() . "<br>";
                echo "📍 File: " . $e->getFile() . "<br>";
                echo "📍 Line: " . $e->getLine() . "<br>";
                echo "🔍 Stack trace:<br><pre style='font-size:12px;max-height:200px;overflow:auto;'>" . $e->getTraceAsString() . "</pre>";
                echo "</div>";
            }
        } else {
            echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeeba;border-radius:5px;margin:10px 0;'>";
            echo "⚠️ Cannot proceed with property insert due to missing required fields.";
            echo "</div>";
        }
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .form-group { margin: 15px 0; }
    label { display: block; font-weight: bold; margin: 5px 0; color: #333; }
    input, select, textarea { 
        width: 100%; 
        max-width: 400px; 
        padding: 8px; 
        margin: 5px 0; 
        border: 1px solid #ddd; 
        border-radius: 4px;
    }
    button { 
        background: #007cba; 
        color: white; 
        padding: 12px 24px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 16px;
    }
    button:hover { background: #005a87; }
    .required { color: #d63384; }
    .optional { color: #6c757d; font-size: 12px; }
</style>

<h3>🧪 Enhanced Test Form (All Required Fields)</h3>
<p><strong>Status:</strong> This form includes all fields required by the Property class with proper validation.</p>

<form enctype="multipart/form-data" action="" method="POST">
    <div class="form-group">
        <label>Property Title <span class="required">*</span></label>
        <input type="text" name="adtitle" value="Test Property <?php echo date('H:i:s'); ?>" required>
    </div>
    
    <div class="form-group">
        <label>Property Type <span class="required">*</span></label>
        <select name="catid" required>
            <option value="">Choose Property Type</option>
            <?php
            if(isset($cat)) {
                $getCat = $cat->getAllCat();
                if($getCat){
                    while($getCatId = $getCat->fetch_assoc()){ ?>
                        <option value="<?php echo $getCatId['catId'];?>"><?php echo $getCatId['catName'];?></option>
                <?php } 
                } else {
                    // Fallback options if database is not accessible
                    echo '<option value="1">Apartment</option>';
                    echo '<option value="2">House</option>';
                    echo '<option value="3">Room</option>';
                }
            } else {
                echo '<option value="1">Apartment</option>';
                echo '<option value="2">House</option>';
                echo '<option value="3">Room</option>';
            }
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Available From <span class="required">*</span></label>
        <input type="date" name="addate" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    
    <div class="form-group">
        <label>Built Year <span class="required">*</span></label>
        <input type="text" name="builtyear" value="2020" required>
    </div>
    
    <div class="form-group">
        <label>Description <span class="optional">(Optional)</span></label>
        <textarea name="addetails" rows="3">Test property description - this field is optional</textarea>
    </div>
    
    <div class="form-group">
        <label>Area <span class="required">*</span></label>
        <input type="text" name="adarea" value="Test Area" required>
    </div>
    
    <div class="form-group">
        <label>Address <span class="required">*</span></label>
        <textarea name="adaddress" rows="2" required>123 Test Street, Test City</textarea>
    </div>
    
    <div class="form-group">
        <label>Property Size (Sq Ft) <span class="required">*</span></label>
        <input type="number" name="adsize" value="1200" required>
    </div>
    
    <div class="form-group">
        <label>Total Floor <span class="required">*</span></label>
        <input type="number" name="totalfloor" value="5" required>
    </div>
    
    <div class="form-group">
        <label>Total Unit <span class="required">*</span></label>
        <input type="number" name="totalunit" value="1" required>
    </div>
    
    <div class="form-group">
        <label>Total Room <span class="required">*</span></label>
        <input type="number" name="totalroom" value="3" required>
    </div>
    
    <div class="form-group">
        <label>Total Bedroom <span class="required">*</span></label>
        <input type="number" name="totalbed" value="2" required>
    </div>
    
    <div class="form-group">
        <label>Total Bathroom <span class="required">*</span></label>
        <input type="number" name="totalbath" value="2" required>
    </div>
    
    <div class="form-group">
        <label>Attach Bath <span class="optional">(Optional)</span></label>
        <input type="number" name="attachbath" value="1" placeholder="0">
    </div>
    
    <div class="form-group">
        <label>Common Bath <span class="optional">(Optional)</span></label>
        <input type="number" name="commonbath" value="1" placeholder="0">
    </div>
    
    <div class="form-group">
        <label>Total Balcony <span class="optional">(Optional)</span></label>
        <input type="number" name="totalbalcony" value="1" placeholder="0">
    </div>
    
    <div class="form-group">
        <label>Floor No <span class="required">*</span></label>
        <input type="number" name="floorno" value="3" required>
    </div>
    
    <div class="form-group">
        <label>Floor Type <span class="required">*</span></label>
        <select name="floortype" required>
            <option value="">Choose Floor Type</option>
            <option value="Tiles" selected>Tiles</option>
            <option value="Mosice">Mosice</option>
            <option value="Marble">Marble</option>
            <option value="Normal">Normal</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Preferred Renter <span class="required">*</span></label>
        <textarea name="prefferedrenter" rows="2" required>Family preferred</textarea>
    </div>
    
    <!-- Facilities Section -->
    <h4>🏗️ Facilities</h4>
    
    <div class="form-group">
        <label>Lift/Elevator <span class="required">*</span></label>
        <select name="liftelevetor" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Generator <span class="required">*</span></label>
        <select name="adgenerator" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Wi-Fi <span class="required">*</span></label>
        <select name="adwifi" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Car Parking <span class="required">*</span></label>
        <select name="carparking" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Open Space <span class="required">*</span></label>
        <select name="openspace" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Play Ground <span class="required">*</span></label>
        <select name="playground" required>
            <option value="No" selected>No</option>
            <option value="Yes">Yes</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>CCTV <span class="required">*</span></label>
        <select name="cctv" required>
            <option value="Yes" selected>Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Security Guard <span class="required">*</span></label>
        <select name="sguard" required>
            <option value="No" selected>No</option>
            <option value="Yes">Yes</option>
        </select>
    </div>
    
    <!-- Price Section -->
    <h4>💰 Price Details</h4>
    
    <div class="form-group">
        <label>Rent Type <span class="required">*</span></label>
        <select name="renttype" required>
            <option value="mo" selected>Per month</option>
            <option value="we">Per week</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Rent (BDT) <span class="required">*</span></label>
        <input type="number" name="adrent" value="25000" required>
    </div>
    
    <div class="form-group">
        <label>Gas Bill <span class="required">*</span></label>
        <input type="text" name="gasbill" value="500" required>
    </div>
    
    <div class="form-group">
        <label>Electric Bill Type <span class="required">*</span></label>
        <select name="ebilltype" required>
            <option value="exc" selected>Excluding</option>
            <option value="inc">Including</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Electric Bill Amount <span class="required">*</span></label>
        <input type="text" name="electricbill" value="1000" required>
    </div>
    
    <div class="form-group">
        <label>Service Charge <span class="required">*</span></label>
        <input type="number" name="scharge" value="2000" required>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="adnegotiable" value="negotiable"> 
            Negotiable <span class="optional">(Optional)</span>
        </label>
    </div>
    
    <div class="form-group">
        <label>Property Photo <span class="optional">(Optional)</span></label>
        <input type="file" name="adimg" accept="image/*">
        <small class="optional">Optional - Leave empty to use default image</small>
    </div>
    
    <button type="submit" name="submit_ad">🚀 Test Submit Property</button>
</form>

<hr>
<p>
    <a href="add_property.php">📝 Original Form</a> | 
    <a href="standalone_test.php">🧪 Standalone Test</a> | 
    <a href="check_database.php">🗄️ Database Check</a>
</p>

<?php if(isset($cat) && isset($pro)) { include"inc/footer.php"; } ?>
