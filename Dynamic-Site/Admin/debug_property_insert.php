<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include"inc/header.php";

/*========================
User Access Control
========================*/
if(Session::get("userLevel") != 2){
    echo"<script>window.location='../index.php'</script>";
}

$getAdMsg = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ad'])){
    echo "<div style='background:#f8f9fa;padding:20px;margin:10px 0;border:1px solid #dee2e6;border-radius:5px;'>";
    echo "<h3>🔧 Debug Property Insert</h3>";
    
    echo "<h4>POST Data:</h4>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h4>FILES Data:</h4>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    try {
        echo "<h4>Calling propertyInsert...</h4>";
        $result = $pro->propertyInsert($_POST, $_FILES);
        echo "<h4>Result from propertyInsert:</h4>";
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo $result;
        echo "</div>";
        
        // Check if property was actually inserted
        echo "<h4>Checking last inserted property:</h4>";
        $checkQuery = "SELECT * FROM tbl_ad ORDER BY adId DESC LIMIT 1";
        $checkResult = $pro->getAllProperty();
        if($checkResult) {
            $lastProperty = $checkResult->fetch_assoc();
            echo "<pre>Last property in database: " . print_r($lastProperty, true) . "</pre>";
        } else {
            echo "No properties found in database";
        }
        
    } catch (Exception $e) {
        echo "<h4>Exception caught:</h4>";
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
        
    } catch (Error $e) {
        echo "<h4>Fatal Error caught:</h4>";
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
    
    echo "</div>";
}
?>

<h2>🔧 Debug Property Insert Test</h2>
<form enctype="multipart/form-data" action="" method="POST">
    <div style="background: #f8f9fa; padding: 20px; margin: 10px 0; border-radius: 5px;">
        <h3>Quick Test Form</h3>
        
        <label>Property Title *:</label><br>
        <input type="text" name="adtitle" value="Debug Test Property" required><br><br>
        
        <label>Property Type *:</label><br>
        <select name="catid" required>
            <option value="">Choose Property Type</option>
            <?php
            $getCat = $cat->getAllCat();
            if($getCat){
                while($getCatId = $getCat->fetch_assoc()){ ?>
                    <option value="<?php echo $getCatId['catId'];?>"><?php echo $getCatId['catName'];?></option>
            <?php } } ?>
        </select><br><br>
        
        <label>Available From *:</label><br>
        <input type="date" name="addate" value="<?php echo date('Y-m-d'); ?>" required><br><br>
        
        <label>Built Year *:</label><br>
        <input type="text" name="builtyear" value="2020" required><br><br>
        
        <label>Description:</label><br>
        <textarea name="addetails">Debug test description</textarea><br><br>
        
        <label>Area *:</label><br>
        <input type="text" name="adarea" value="Debug Area" required><br><br>
        
        <label>Address *:</label><br>
        <textarea name="adaddress" required>Debug Address, Debug City</textarea><br><br>
        
        <label>Property Size *:</label><br>
        <input type="number" name="adsize" value="1200" required><br><br>
        
        <label>Total Floor *:</label><br>
        <input type="number" name="totalfloor" value="5" required><br><br>
        
        <label>Total Unit *:</label><br>
        <input type="number" name="totalunit" value="1" required><br><br>
        
        <label>Total Room *:</label><br>
        <input type="number" name="totalroom" value="3" required><br><br>
        
        <label>Total Bedroom *:</label><br>
        <input type="number" name="totalbed" value="2" required><br><br>
        
        <label>Total Bathroom *:</label><br>
        <input type="number" name="totalbath" value="2" required><br><br>
        
        <label>Attach Bath:</label><br>
        <input type="number" name="attachbath" value="1"><br><br>
        
        <label>Common Bath:</label><br>
        <input type="number" name="commonbath" value="1"><br><br>
        
        <label>Total Balcony:</label><br>
        <input type="number" name="totalbalcony" value="1"><br><br>
        
        <label>Floor No *:</label><br>
        <input type="number" name="floorno" value="3" required><br><br>
        
        <label>Floor Type *:</label><br>
        <select name="floortype" required>
            <option value="Tiles">Tiles</option>
        </select><br><br>
        
        <label>Preferred Renter *:</label><br>
        <textarea name="prefferedrenter" required>Family preferred</textarea><br><br>
        
        <label>Lift/Elevator *:</label><br>
        <select name="liftelevetor" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Generator *:</label><br>
        <select name="adgenerator" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Wi-Fi *:</label><br>
        <select name="adwifi" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Car Parking *:</label><br>
        <select name="carparking" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Open Space *:</label><br>
        <select name="openspace" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Play Ground *:</label><br>
        <select name="playground" required>
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br><br>
        
        <label>CCTV *:</label><br>
        <select name="cctv" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>
        
        <label>Security Guard *:</label><br>
        <select name="sguard" required>
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select><br><br>
        
        <label>Rent Type *:</label><br>
        <select name="renttype" required>
            <option value="mo">Per month</option>
            <option value="we">Per week</option>
        </select><br><br>
        
        <label>Rent (BDT) *:</label><br>
        <input type="number" name="adrent" value="25000" required><br><br>
        
        <label>Gas Bill *:</label><br>
        <input type="text" name="gasbill" value="500" required><br><br>
        
        <label>Electric Bill Type *:</label><br>
        <select name="ebilltype" required>
            <option value="exc">Excluding</option>
            <option value="inc">Including</option>
        </select><br><br>
        
        <label>Electric Bill Amount *:</label><br>
        <input type="text" name="electricbill" value="1000" required><br><br>
        
        <label>Service Charge *:</label><br>
        <input type="number" name="scharge" value="2000" required><br><br>
        
        <label>Negotiable:</label><br>
        <input type="checkbox" name="adnegotiable" value="negotiable"> Negotiable<br><br>
        
        <label>Property Photo:</label><br>
        <input type="file" name="adimg" accept="image/*"><br><br>
        
        <button type="submit" name="submit_ad">🔧 Debug Submit Property</button>
    </div>
</form>

<?php include"inc/footer.php";?>
