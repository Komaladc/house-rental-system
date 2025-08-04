<?php
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
			// Validate required fields before processing
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
				$getAdMsg = "<div class='alert alert_danger'>Error: Missing required fields - " . implode(', ', $missing_fields) . "</div>";
			} else {
				$getAdMsg = $pro->propertyInsert($_POST, $_FILES);
			}
			
		} catch (Exception $e) {
			$getAdMsg = "<div class='alert alert_danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
		} catch (Error $e) {
			$getAdMsg = "<div class='alert alert_danger'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</div>";
		}
	}
?>


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
			
			<!--Add Property Block Start: Basic------------->
			<form enctype="multipart/form-data" action="" method="POST">
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>basic information</h2>
					</div>
				<?php
					if(isset($getAdMsg)){
						echo $getAdMsg;
					}
				?>	
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*ad title</p>
						</div>
						
						<div class="add_property_field">
							<input type="text" name="adtitle" placeholder="Property Title"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*select property type</p>
						</div>
						
						<div class="add_property_field">
							<select name="catid">
								<option value="">Choose Property Type</option>
							<?php
								$getCat = $cat->getAllCat();
								if($getCat){
									while($getCatId = $getCat->fetch_assoc()){ ?>
								<option value="<?php echo $getCatId['catId'];?>"><?php echo $getCatId['catName'];?></option>
							<?php } } ?>	
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*available from</p>
						</div>
						
						<div class="add_property_field">
							<input type="date" name="addate"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*built year</p>
						</div>
						
						<div class="add_property_field">
							<input type="text" name="builtyear" placeholder="Built Year"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>description (optional)</p>
							<small style="color: #666;">Enter property description in plain text</small>
						</div>
						
						<div class="add_property_field">
							<!-- Simple textarea - NO TINYMCE -->
							<textarea name="addetails" id="addetails" style="width: 100%; min-height: 200px; padding: 15px; border: 2px solid #ddd; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; resize: vertical;" placeholder="Enter property description here... (Optional)"></textarea>
						</div>
					</div>
				</div>
			<!--Add Property Block End------------->
			
			
			<!--Add Property Block Start: Location ------------->
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>property location</h2>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*area</p>
						</div>
						
						<div class="add_property_field">
							<input type="text" name="adarea" placeholder="Area Name"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*address</p>
						</div>
						
						<div class="add_property_field">
							<textarea name="adaddress" style="width: 100%; min-height: 120px; padding: 15px; border: 2px solid #ddd; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; resize: vertical;" placeholder="Enter full address..."></textarea>
						</div>
					</div>
				</div>
			<!--Add Property Block End------------->
			
			
			<!--Add Property Block Start: Specification ------------->
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>property specification</h2>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*property size</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="adsize" placeholder="Sq Ft"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*floor</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalfloor" placeholder="Total Floor"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*unit</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalunit" placeholder="Total Unit"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*room</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalroom" placeholder="Total Room"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*bedroom</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalbed" placeholder="Total Bedroom"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*bathroom</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalbath" placeholder="Total Bathroom"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>attach bath</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="attachbath" placeholder="Attach Bath"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>common bath</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="commonbath" placeholder="Common Bath"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>balconies</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="totalbalcony" placeholder="Total Balcony"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*floor no</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="floorno" placeholder="Floor No"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*floor type</p>
						</div>
						
						<div class="add_property_field">
							<select name="floortype">
								<option value="">Choose Floor Type</option>
								<option value="Tiles">Tiles</option>
								<option value="Mosice">Mosice</option>
								<option value="Marble">Marble</option>
								<option value="Normal">Normal</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*prefferd renter</p>
						</div>
						
						<div class="add_property_field">
							<textarea name="prefferedrenter" style="width: 100%; min-height: 120px; padding: 15px; border: 2px solid #ddd; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; resize: vertical;" placeholder="Describe preferred renter type..."></textarea>
						</div>
					</div>
				</div>
			<!--Add Property Block End------------->	
			

			<!--Add Property Block Start: Facilities ------------->
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>facilities</h2>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>lift/elevator</p>
						</div>
						
						<div class="add_property_field">
							<select name="liftelevetor">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>generator</p>
						</div>
						
						<div class="add_property_field">
							<select name="adgenerator">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>Wi-Fi connectivity</p>
						</div>
						
						<div class="add_property_field">
							<select name="adwifi">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>car parking</p>
						</div>
						
						<div class="add_property_field">
							<select name="carparking">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>open space</p>
						</div>
						
						<div class="add_property_field">
							<select name="openspace">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>play ground</p>
						</div>
						
						<div class="add_property_field">
							<select name="playground">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p style="text-transform:uppercase">cctv</p>
						</div>
						
						<div class="add_property_field">
							<select name="cctv">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>security guard</p>
						</div>
						
						<div class="add_property_field">
							<select name="sguard">
								<option value="No">No</option>
								<option value="Yes">Yes</option>
							</select>
						</div>
					</div>					
				</div>
			<!--Add Property Block End------------->
			
			
			<!--Add Property Block Start: Price ------------->
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>price details</h2>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>rent type</p>
						</div>
						
						<div class="add_property_field">
							<select name="renttype">
								<option value="mo">Per month</option>
								<option value="we">Per week</option>
							</select>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*rent (BDT)</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="adrent" placeholder="Rent (BDT)"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*gas bill</p>
						</div>
						
						<div class="add_property_field">
							<input type="text" name="gasbill" placeholder="Gas Bill"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*electric bill</p>
						</div>
						
						<div class="add_property_field">
							<select name="ebilltype">
								<option value="exc">Excluding</option>
								<option value="inc">Including</option>
							</select>
						</div>
						<div class="add_property_field">
							<input type="text" name="electricbill"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>*service charge</p>
						</div>
						
						<div class="add_property_field">
							<input type="number" name="scharge" placeholder="Service Charge"/>
						</div>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_field property_negotiable_check">
							<input type="checkbox" name="adnegotiable" value="negotiable"/>
							<span>negotiable</span>
						</div>
					</div>
				</div>
			<!--Add Property Block End------------->
			
			
			<!--Add Property Block Start: Photo ------------->
				<div class="add_property_block overflow">
					<div class="property_block_title">
						<h2>property photo</h2>
					</div>
					
					<div class="property_block_body overflow">
						<div class="add_property_title">
							<p>upload photo (optional)</p>
						</div>
						
						<div class="add_property_field">
							<input type="file" name="adimg" accept="image/*"/>
							<small style="color: #666; font-size: 12px;">Optional - Leave empty to use default image</small>
						</div>
					</div>
					
					<div class="action_button overflow">
						<button type="submit" name="submit_ad">submit property</button>
					</div>
				</div>
			<!--Add Property Block End------------->
			</form>
			
			</div>
		<!--Admin Content End------------->
		
		</div>
	</div>
</div>
<!--Add Property Section Start------------->


<!--Footer Section Start------------->		
<?php include"inc/footer.php";?>
<!--Footer Section End------------->