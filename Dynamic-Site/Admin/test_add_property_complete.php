<?php
include '../lib/Session.php';
Session::init();
include '../lib/Database.php';
include '../helpers/Format.php';

spl_autoload_register(function($class){
    include_once '../classes/'.$class.'.php';
});

$pro = new Property();
$cat = new Category();

// Simulate user login
Session::set("userlogin", true);
Session::set("userId", 1);

echo "<h2>Add Property with Photo Upload Test</h2>";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addproperty'])){
    echo "<h3>Form Submitted!</h3>";
    
    echo "<h4>POST Data:</h4>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h4>FILES Data:</h4>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    // Test the property insert
    $result = $pro->propertyInsert($_POST, $_FILES);
    echo "<div style='padding:10px; margin:10px 0; border:1px solid #ccc;'>" . $result . "</div>";
    
    // Check if property was saved
    $db = new Database();
    $checkQuery = "SELECT * FROM tbl_ad WHERE adTitle = '" . $_POST['adtitle'] . "' ORDER BY adId DESC LIMIT 1";
    $propertyResult = $db->select($checkQuery);
    if($propertyResult) {
        echo "<p style='color:green;'>✓ Property found in database!</p>";
        $property = $propertyResult->fetch_assoc();
        echo "<p>Property ID: " . $property['adId'] . " | Image: " . $property['adImg'] . "</p>";
        
        // Check if image file exists
        if($property['adImg'] != 'images/1.jpg') {
            $imagePath = "../" . $property['adImg'];
            if(file_exists($imagePath)) {
                echo "<p style='color:green;'>✓ Uploaded image file exists on server</p>";
                echo "<p>Image path: " . $imagePath . "</p>";
                echo "<img src='../" . $property['adImg'] . "' style='width:150px; height:100px; object-fit:cover; border:1px solid #ccc;' alt='Property Image'>";
            } else {
                echo "<p style='color:red;'>✗ Uploaded image file not found on server</p>";
                echo "<p>Expected path: " . $imagePath . "</p>";
            }
        } else {
            echo "<p style='color:blue;'>ℹ Using default image</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Property not found in database!</p>";
    }
}

// Get categories for dropdown
$categories = $cat->getAllCategory();
?>

<h3>Test Property Form:</h3>
<form method="POST" action="" enctype="multipart/form-data">
    <table border="1" style="border-collapse:collapse; width:100%;">
        <tr>
            <td><label><b>Property Title:</b></label></td>
            <td><input type="text" name="adtitle" value="Test Property <?php echo time(); ?>" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Category:</b></label></td>
            <td>
                <select name="catid" required style="width:310px; padding:5px;">
                    <option value="">Select Category</option>
                    <?php 
                    if($categories) {
                        while($category = $categories->fetch_assoc()) {
                            echo "<option value='" . $category['catId'] . "'>" . $category['catName'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Date:</b></label></td>
            <td><input type="date" name="addate" value="<?php echo date('Y-m-d'); ?>" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Built Year:</b></label></td>
            <td><input type="number" name="builtyear" value="2020" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Address:</b></label></td>
            <td><input type="text" name="adaddress" value="123 Test Street" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Area:</b></label></td>
            <td><input type="text" name="adarea" value="Test Area" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Size (sqft):</b></label></td>
            <td><input type="number" name="adsize" value="1200" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Floor:</b></label></td>
            <td><input type="number" name="totalfloor" value="5" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Unit:</b></label></td>
            <td><input type="number" name="totalunit" value="1" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Room:</b></label></td>
            <td><input type="number" name="totalroom" value="3" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Bed:</b></label></td>
            <td><input type="number" name="totalbed" value="2" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Bath:</b></label></td>
            <td><input type="number" name="totalbath" value="2" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Attach Bath:</b></label></td>
            <td><input type="number" name="attachbath" value="1" style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Common Bath:</b></label></td>
            <td><input type="number" name="commonbath" value="1" style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Total Balcony:</b></label></td>
            <td><input type="number" name="totalbalcony" value="1" style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Floor No:</b></label></td>
            <td><input type="number" name="floorno" value="3" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Floor Type:</b></label></td>
            <td>
                <select name="floortype" required style="width:310px; padding:5px;">
                    <option value="Concrete">Concrete</option>
                    <option value="Tiles">Tiles</option>
                    <option value="Wood">Wood</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Preferred Renter:</b></label></td>
            <td>
                <select name="prefferedrenter" required style="width:310px; padding:5px;">
                    <option value="Family">Family</option>
                    <option value="Bachelor">Bachelor</option>
                    <option value="Any">Any</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Lift/Elevator:</b></label></td>
            <td>
                <select name="liftelevetor" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Generator:</b></label></td>
            <td>
                <select name="adgenerator" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>WiFi:</b></label></td>
            <td>
                <select name="adwifi" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Car Parking:</b></label></td>
            <td>
                <select name="carparking" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Open Space:</b></label></td>
            <td>
                <select name="openspace" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Playground:</b></label></td>
            <td>
                <select name="playground" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>CCTV:</b></label></td>
            <td>
                <select name="cctv" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Security Guard:</b></label></td>
            <td>
                <select name="sguard" required style="width:310px; padding:5px;">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Rent Type:</b></label></td>
            <td>
                <select name="renttype" required style="width:310px; padding:5px;">
                    <option value="Monthly">Monthly</option>
                    <option value="Yearly">Yearly</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Rent Amount:</b></label></td>
            <td><input type="number" name="adrent" value="25000" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Gas Bill:</b></label></td>
            <td><input type="number" name="gasbill" value="1500" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Electric Bill Type:</b></label></td>
            <td>
                <select name="ebilltype" required style="width:310px; padding:5px;">
                    <option value="Fixed">Fixed</option>
                    <option value="Unit">Unit</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><b>Electric Bill:</b></label></td>
            <td><input type="number" name="electricbill" value="2000" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Service Charge:</b></label></td>
            <td><input type="number" name="scharge" value="3000" required style="width:300px; padding:5px;"></td>
        </tr>
        <tr>
            <td><label><b>Negotiable:</b></label></td>
            <td><input type="checkbox" name="adnegotiable" value="Yes"> Yes, rent is negotiable</td>
        </tr>
        <tr>
            <td><label><b>Description:</b></label></td>
            <td><textarea name="addetails" style="width:300px; height:60px; padding:5px;">This is a test property description.</textarea></td>
        </tr>
        <tr>
            <td><label><b>Property Photo:</b></label></td>
            <td>
                <input type="file" name="adimg" accept="image/*" style="padding:5px;">
                <br><small style="color:#666;">Optional - JPG, JPEG, PNG formats supported</small>
            </td>
        </tr>
    </table>
    
    <p>
        <button type="submit" name="addproperty" style="padding:15px 30px; background:#007cba; color:white; border:none; cursor:pointer; font-size:16px;">Add Property</button>
    </p>
</form>

<h3>PHP Settings:</h3>
<ul>
    <li><strong>upload_max_filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
    <li><strong>post_max_size:</strong> <?php echo ini_get('post_max_size'); ?></li>
    <li><strong>file_uploads:</strong> <?php echo ini_get('file_uploads') ? 'On' : 'Off'; ?></li>
</ul>
