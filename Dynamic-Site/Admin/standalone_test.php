<?php
// Standalone Property Insert Test - No Authentication Required
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Standalone Property Insert Test</h2>";
echo "<p><strong>Note:</strong> This bypasses authentication for testing purposes only.</p>";

try {
    // Include required files directly
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
    
    // Test database connection
    echo "<h3>🗄️ Database Connection Test</h3>";
    $testQuery = "SHOW TABLES";
    $testResult = $db->select($testQuery);
    if($testResult) {
        echo "✅ Database connected, tables found: " . $testResult->num_rows . "<br>";
        echo "Tables: ";
        while($row = $testResult->fetch_array()) {
            echo $row[0] . " ";
        }
        echo "<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    // Test if tbl_ad table exists and check its structure
    echo "<h3>📋 Table Structure Check</h3>";
    $structureQuery = "DESCRIBE tbl_ad";
    $structureResult = $db->select($structureQuery);
    if($structureResult) {
        echo "✅ tbl_ad table exists<br>";
        echo "Columns: ";
        while($row = $structureResult->fetch_assoc()) {
            echo $row['Field'] . " (" . $row['Type'] . ") ";
        }
        echo "<br>";
    } else {
        echo "❌ tbl_ad table does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Setup error: " . $e->getMessage() . "<br>";
    echo "🔍 Error details: " . $e->getFile() . " line " . $e->getLine() . "<br>";
    exit;
}

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
    echo "<hr><h3>📥 Form Submission Test</h3>";
    
    // Mock user session for testing
    Session::init();
    Session::set("userId", 1); // Test user ID
    
    // Show received data
    echo "<h4>📝 Posted Data:</h4>";
    foreach($_POST as $key => $value) {
        if($key !== 'submit_ad') {
            echo "- $key: " . (empty($value) ? '<span style="color:red;">EMPTY</span>' : htmlspecialchars($value)) . "<br>";
        }
    }
    
    echo "<h4>📁 File Data:</h4>";
    if(isset($_FILES['adimg']) && !empty($_FILES['adimg']['name'])) {
        echo "- File name: " . $_FILES['adimg']['name'] . "<br>";
        echo "- File size: " . $_FILES['adimg']['size'] . "<br>";
        echo "- File error: " . $_FILES['adimg']['error'] . "<br>";
        echo "- File type: " . $_FILES['adimg']['type'] . "<br>";
        echo "- Temp name: " . $_FILES['adimg']['tmp_name'] . "<br>";
    } else {
        echo "- No file uploaded (this is OK, should use default)<br>";
    }
    
    // Try the property insert with extensive error handling
    try {
        echo "<h4>🚀 Attempting Property Insert</h4>";
        
        // Log before calling the method
        echo "📞 Calling propertyInsert method...<br>";
        
        $startTime = microtime(true);
        $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
        $endTime = microtime(true);
        
        echo "<div style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>";
        echo "✅ <strong>SUCCESS!</strong><br>";
        echo "📋 Result: " . $getAdMsg . "<br>";
        echo "⏱️ Execution time: " . round(($endTime - $startTime) * 1000, 2) . "ms<br>";
        echo "</div>";
        
        // Check if data was actually inserted
        echo "<h4>🔍 Verification</h4>";
        $verifyQuery = "SELECT * FROM tbl_ad ORDER BY adId DESC LIMIT 1";
        $verifyResult = $db->select($verifyQuery);
        if($verifyResult) {
            $lastProperty = $verifyResult->fetch_assoc();
            echo "✅ Last inserted property ID: " . $lastProperty['adId'] . "<br>";
            echo "✅ Property title: " . $lastProperty['adTitle'] . "<br>";
        }
        
    } catch (Error $e) {
        echo "<div style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
        echo "❌ <strong>FATAL ERROR:</strong><br>";
        echo "📝 Message: " . $e->getMessage() . "<br>";
        echo "📍 File: " . $e->getFile() . "<br>";
        echo "📍 Line: " . $e->getLine() . "<br>";
        echo "🔍 Stack trace:<br><pre style='font-size:12px;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>";
        echo "❌ <strong>EXCEPTION:</strong><br>";
        echo "📝 Message: " . $e->getMessage() . "<br>";
        echo "📍 File: " . $e->getFile() . "<br>";
        echo "📍 Line: " . $e->getLine() . "<br>";
        echo "🔍 Stack trace:<br><pre style='font-size:12px;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin: 10px 0; }
    label { display: block; font-weight: bold; margin: 5px 0; }
    input, select, textarea { width: 100%; max-width: 300px; padding: 6px; margin: 3px 0; border: 1px solid #ddd; border-radius: 3px; }
    button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    button:hover { background: #005a87; }
    .row { display: flex; flex-wrap: wrap; gap: 20px; }
    .col { flex: 1; min-width: 250px; }
</style>

<div class="container">
    <h3>🧪 Minimal Test Form</h3>
    <p>This form has all required fields pre-filled with valid test data.</p>
    
    <form enctype="multipart/form-data" action="" method="POST">
        <div class="row">
            <div class="col">
                <h4>📝 Basic Info</h4>
                <div class="form-group">
                    <label>Property Title *</label>
                    <input type="text" name="adtitle" value="Test Property <?php echo date('H:i:s'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Property Type *</label>
                    <select name="catid" required>
                        <option value="1">Apartment</option>
                        <option value="2">House</option>
                        <option value="3">Room</option>
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
                    <textarea name="addetails" rows="3">Test property description</textarea>
                </div>
            </div>
            
            <div class="col">
                <h4>📍 Location</h4>
                <div class="form-group">
                    <label>Area *</label>
                    <input type="text" name="adarea" value="Test Area" required>
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="adaddress" rows="2" required>123 Test Street, Test City</textarea>
                </div>
                
                <h4>📐 Size</h4>
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
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <h4>🏠 Rooms</h4>
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
            </div>
            
            <div class="col">
                <h4>🏢 Floor Details</h4>
                <div class="form-group">
                    <label>Floor No *</label>
                    <input type="number" name="floorno" value="3" required>
                </div>
                <div class="form-group">
                    <label>Floor Type *</label>
                    <select name="floortype" required>
                        <option value="Tiles">Tiles</option>
                        <option value="Marble">Marble</option>
                        <option value="Normal">Normal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Preferred Renter *</label>
                    <textarea name="prefferedrenter" rows="2" required>Family preferred</textarea>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <h4>🏗️ Facilities</h4>
                <div class="form-group">
                    <label>Lift/Elevator *</label>
                    <select name="liftelevetor" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Generator *</label>
                    <select name="adgenerator" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Wi-Fi *</label>
                    <select name="adwifi" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Car Parking *</label>
                    <select name="carparking" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label>Open Space *</label>
                    <select name="openspace" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Play Ground *</label>
                    <select name="playground" required>
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>CCTV *</label>
                    <select name="cctv" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Security Guard *</label>
                    <select name="sguard" required>
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <h4>💰 Price Details</h4>
                <div class="form-group">
                    <label>Rent Type *</label>
                    <select name="renttype" required>
                        <option value="mo">Per month</option>
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
            </div>
            
            <div class="col">
                <div class="form-group">
                    <label>Electric Bill Type *</label>
                    <select name="ebilltype" required>
                        <option value="exc">Excluding</option>
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
            </div>
        </div>
        
        <div class="form-group">
            <label>Property Photo (Optional)</label>
            <input type="file" name="adimg" accept="image/*">
            <small>Optional - will use default image if none selected</small>
        </div>
        
        <button type="submit" name="submit_ad">🧪 Test Property Insert</button>
    </form>
</div>

<hr>
<p><a href="debug_form_test.php">🔧 Back to Main Debug Form</a> | <a href="../index.php">🏠 Main Site</a></p>
