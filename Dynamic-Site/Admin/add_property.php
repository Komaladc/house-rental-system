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
    try {
        // Add detailed debugging
        echo "<div style='background:#fff3cd;padding:10px;margin:10px 0;border-radius:5px;border:1px solid #ffeaa7;'>";
        echo "<h4>🔍 Processing Form Data:</h4>";
        echo "<p>User ID: " . Session::get("userId") . "</p>";
        echo "<p>User Level: " . Session::get("userLevel") . "</p>";
        echo "<p>Form submitted with " . count($_POST) . " fields</p>";
        
        // Check if required classes are loaded
        if(!class_exists('Property')) {
            echo "<p style='color:red;'>❌ Property class not found!</p>";
        } else {
            echo "<p style='color:green;'>✅ Property class loaded</p>";
        }
        
        if(!isset($pro) || !is_object($pro)) {
            echo "<p style='color:red;'>❌ Property object not initialized!</p>";
        } else {
            echo "<p style='color:green;'>✅ Property object ready</p>";
        }
        echo "</div>";
        
        $getAdMsg = $pro->propertyInsert($_POST, $_FILES);
        
        // Add debugging to check if property was actually inserted
        echo "<div style='background:#e7f3ff;padding:10px;margin:10px 0;border-radius:5px;'>";
        echo "<h4>🔍 Checking if property was saved:</h4>";
        
        // Get the last inserted property
        $checkQuery = "SELECT * FROM tbl_ad WHERE userId = '" . Session::get("userId") . "' ORDER BY adId DESC LIMIT 1";
        $testDb = new Database();
        $result = $testDb->select($checkQuery);
        
        if($result && $result->num_rows > 0) {
            $lastProperty = $result->fetch_assoc();
            echo "✅ Last property found: " . $lastProperty['adTitle'] . " (ID: " . $lastProperty['adId'] . ")<br>";
            echo "📅 Date added: " . $lastProperty['adDate'] . "<br>";
        } else {
            echo "❌ No properties found for this user<br>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        $getAdMsg = "<div class='alert alert_danger'>Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div style='background:#f8d7da;padding:10px;margin:10px 0;border-radius:5px;'>";
        echo "<h4>❌ Exception Details:</h4>";
        echo "<p>Message: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "</div>";
        
    } catch (Error $e) {
        $getAdMsg = "<div class='alert alert_danger'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div style='background:#f8d7da;padding:10px;margin:10px 0;border-radius:5px;'>";
        echo "<h4>💥 Fatal Error Details:</h4>";
        echo "<p>Message: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "</div>";
    }
}
?>

<style>
    .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
    .alert_success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert_danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
</style>

<!--Dashboard Section Start------------->
<div class="container">
    <div class="mcol_12 admin_page_title">
        <div class="page_title overflow">
            <h1 class="sub-title">add property</h1>
            <h4><a href="?action=logout"><i class="fa-solid fa-right-from-bracket"></i><span>sign out</span></a></h4>
        </div>
    </div>
    
    <div class="responsive_mcol_small mcol_12">
        <?php include"inc/sidebar.php";?>
        
        <div class="responsive_mcol responsive_mcol_small mcol_8">
        <!--Admin Content Start------------->
            <div class="admin_content overflow">
            
            <?php
                if(!empty($getAdMsg)){
                    echo $getAdMsg;
                }
            ?>
            
            <!--Add Property Block Start: Basic------------->
            <form enctype="multipart/form-data" action="" method="POST">
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>basic information</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>property title *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="text" name="adtitle" value="<?php echo isset($_POST['adtitle']) ? htmlspecialchars($_POST['adtitle']) : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>property type *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="catid" required>
                                <option value="">Choose Property Type</option>
                                <?php
                                $getCat = $cat->getAllCat();
                                if($getCat){
                                    while($getCatId = $getCat->fetch_assoc()){ ?>
                                        <option value="<?php echo $getCatId['catId'];?>" 
                                            <?php echo (isset($_POST['catid']) && $_POST['catid'] == $getCatId['catId']) ? 'selected' : ''; ?>>
                                            <?php echo $getCatId['catName'];?>
                                        </option>
                                <?php } } ?>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>available from *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="date" name="addate" value="<?php echo isset($_POST['addate']) ? $_POST['addate'] : date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>built year *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="text" name="builtyear" value="<?php echo isset($_POST['builtyear']) ? htmlspecialchars($_POST['builtyear']) : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>description (optional)</p>
                            <small style="color: #666;">Enter property description in plain text</small>
                        </div>
                        
                        <div class="add_property_field">
                            <textarea name="addetails" style="width: 100%; min-height: 200px; padding: 15px; border: 2px solid #ddd; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; resize: vertical;" placeholder="Enter property description here... (Optional)"><?php echo isset($_POST['addetails']) ? htmlspecialchars($_POST['addetails']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!--Location Section-->
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>property location</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>area *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="text" name="adarea" value="<?php echo isset($_POST['adarea']) ? htmlspecialchars($_POST['adarea']) : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>address *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <textarea name="adaddress" required><?php echo isset($_POST['adaddress']) ? htmlspecialchars($_POST['adaddress']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!--Property Details Section-->
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>property details</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>property size (sq ft) *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="adsize" value="<?php echo isset($_POST['adsize']) ? $_POST['adsize'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>total floor *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalfloor" value="<?php echo isset($_POST['totalfloor']) ? $_POST['totalfloor'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>total unit *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalunit" value="<?php echo isset($_POST['totalunit']) ? $_POST['totalunit'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>total room *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalroom" value="<?php echo isset($_POST['totalroom']) ? $_POST['totalroom'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>total bedroom *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalbed" value="<?php echo isset($_POST['totalbed']) ? $_POST['totalbed'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>total bathroom *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalbath" value="<?php echo isset($_POST['totalbath']) ? $_POST['totalbath'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>attach bath</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="attachbath" value="<?php echo isset($_POST['attachbath']) ? $_POST['attachbath'] : '0'; ?>">
                        </div>
                        
                        <div class="add_property_title">
                            <p>common bath</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="commonbath" value="<?php echo isset($_POST['commonbath']) ? $_POST['commonbath'] : '0'; ?>">
                        </div>
                        
                        <div class="add_property_title">
                            <p>total balcony</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="totalbalcony" value="<?php echo isset($_POST['totalbalcony']) ? $_POST['totalbalcony'] : '0'; ?>">
                        </div>
                        
                        <div class="add_property_title">
                            <p>floor no *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="floorno" value="<?php echo isset($_POST['floorno']) ? $_POST['floorno'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>floor type *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="floortype" required>
                                <option value="">Choose Floor Type</option>
                                <option value="Tiles" <?php echo (isset($_POST['floortype']) && $_POST['floortype'] == 'Tiles') ? 'selected' : ''; ?>>Tiles</option>
                                <option value="Mosice" <?php echo (isset($_POST['floortype']) && $_POST['floortype'] == 'Mosice') ? 'selected' : ''; ?>>Mosice</option>
                                <option value="Marble" <?php echo (isset($_POST['floortype']) && $_POST['floortype'] == 'Marble') ? 'selected' : ''; ?>>Marble</option>
                                <option value="Normal" <?php echo (isset($_POST['floortype']) && $_POST['floortype'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>preferred renter *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <textarea name="prefferedrenter" required><?php echo isset($_POST['prefferedrenter']) ? htmlspecialchars($_POST['prefferedrenter']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!--Facilities Section-->
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>facilities</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>lift/elevator *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="liftelevetor" required>
                                <option value="No" <?php echo (isset($_POST['liftelevetor']) && $_POST['liftelevetor'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['liftelevetor']) && $_POST['liftelevetor'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>generator *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="adgenerator" required>
                                <option value="No" <?php echo (isset($_POST['adgenerator']) && $_POST['adgenerator'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['adgenerator']) && $_POST['adgenerator'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>wi-fi *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="adwifi" required>
                                <option value="No" <?php echo (isset($_POST['adwifi']) && $_POST['adwifi'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['adwifi']) && $_POST['adwifi'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>car parking *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="carparking" required>
                                <option value="No" <?php echo (isset($_POST['carparking']) && $_POST['carparking'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['carparking']) && $_POST['carparking'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>open space *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="openspace" required>
                                <option value="No" <?php echo (isset($_POST['openspace']) && $_POST['openspace'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['openspace']) && $_POST['openspace'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>play ground *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="playground" required>
                                <option value="No" <?php echo (isset($_POST['playground']) && $_POST['playground'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['playground']) && $_POST['playground'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>cctv *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="cctv" required>
                                <option value="No" <?php echo (isset($_POST['cctv']) && $_POST['cctv'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['cctv']) && $_POST['cctv'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>security guard *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="sguard" required>
                                <option value="No" <?php echo (isset($_POST['sguard']) && $_POST['sguard'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Yes" <?php echo (isset($_POST['sguard']) && $_POST['sguard'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!--Price Details Section-->
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>price details</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>rent type *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="renttype" required>
                                <option value="mo" <?php echo (isset($_POST['renttype']) && $_POST['renttype'] == 'mo') ? 'selected' : ''; ?>>Per month</option>
                                <option value="we" <?php echo (isset($_POST['renttype']) && $_POST['renttype'] == 'we') ? 'selected' : ''; ?>>Per week</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>rent (BDT) *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="adrent" value="<?php echo isset($_POST['adrent']) ? $_POST['adrent'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>gas bill *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="text" name="gasbill" value="<?php echo isset($_POST['gasbill']) ? htmlspecialchars($_POST['gasbill']) : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>electric bill type *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <select name="ebilltype" required>
                                <option value="exc" <?php echo (isset($_POST['ebilltype']) && $_POST['ebilltype'] == 'exc') ? 'selected' : ''; ?>>Excluding</option>
                                <option value="inc" <?php echo (isset($_POST['ebilltype']) && $_POST['ebilltype'] == 'inc') ? 'selected' : ''; ?>>Including</option>
                            </select>
                        </div>
                        
                        <div class="add_property_title">
                            <p>electric bill amount *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="text" name="electricbill" value="<?php echo isset($_POST['electricbill']) ? htmlspecialchars($_POST['electricbill']) : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>service charge *</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="number" name="scharge" value="<?php echo isset($_POST['scharge']) ? $_POST['scharge'] : ''; ?>" required>
                        </div>
                        
                        <div class="add_property_title">
                            <p>negotiable</p>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="checkbox" name="adnegotiable" value="negotiable" <?php echo (isset($_POST['adnegotiable'])) ? 'checked' : ''; ?>> Negotiable
                        </div>
                    </div>
                </div>
                
                <!--Image Upload Section-->
                <div class="add_property_block overflow">
                    <div class="property_block_title">
                        <h2>property photo</h2>
                    </div>
                    
                    <div class="property_block_body overflow">
                        <div class="add_property_title">
                            <p>property photo (optional)</p>
                            <small style="color: #666;">Supported formats: JPG, JPEG, PNG. If no image is selected, a default image will be used.</small>
                        </div>
                        
                        <div class="add_property_field">
                            <input type="file" name="adimg" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="action_button overflow">
                        <button type="submit" name="submit_ad">submit property</button>
                    </div>
                </div>
            </form>
            
            </div>
        </div>
    </div>
</div>

<?php include"inc/footer.php";?>
