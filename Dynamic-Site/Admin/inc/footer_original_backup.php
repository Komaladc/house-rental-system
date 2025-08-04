<!--Footer Section Start------------->		
	<footer class="footersection overflow">
		<div class="footer-top overflow">
			<div class="footer_top_upper overflow">
			<!--Footer content 1------------->
				<div class="footer_content">
					<h2>about us</h2>
					<div class="footer_text overflow">
						<div class="text_icon">
							<i class="fa-solid fa-location-dot"></i>
						</div>
						<div class="text_body">
							<h3>address</h3>
							<p>Dhanmondi, Dhaka</p>
						</div>
					</div>
					
					<div class="footer_text overflow">
						<div class="text_icon">
							<i class="fa-solid fa-envelopes-bulk"></i>
						</div>
						<div class="text_body">
							<h3>for any question</h3>
							<p>houserental@gmail.com</p>
						</div>
					</div>
					
					<div class="footer_text overflow">
						<div class="text_icon">
							<i class="fa-solid fa-mobile-screen"></i>
						</div>
						<div class="text_body">
							<h3>help & support</h3>
							<p>01232-231324</p>
						</div>
					</div>
				</div>
			
			<!--Footer content 2------------->	
				<div class="footer_content">
					<h2>recent property</h2>
				<?php
					$getrp = $pro->recentProperty();
					if($getrp){
						while($recentad = $getrp->fetch_assoc()){ 
				?>
					<div class="footer_text">
						<a href="../property_details.php?adid=<?php echo $recentad['adId']; ?>">
							<div class="footer_property overflow">
								<div class="text_icon text_img">
									<img src="../<?php echo $recentad['adImg'];?>" alt="recent ad"/>
								</div>
								<div class="text_body">
									<p><?php echo $recentad['adTitle'];?></p>
									<p><?php echo $fm->formatDate($recentad['adDate']);?></p>
								</div>
							</div>
						</a>
					</div>
				<?php } } ?>
				</div>
			
			<!--Footer content 3------------->
				<div class="footer_content">
					<h2>quick links</h2>
					<div class="footer_text">
						<nav class="footer_nav">
							<ul>
								<li><a href="../property_list.php">list of property &raquo;</a></li>
								<li><a href="add_property.php">post property &raquo;</a></li>
								<li><a href="../help_support.php">help & support &raquo;</a></li>
								<li><a href="../signup.php">signup &raquo;</a></li>
							</ul>
						</nav>
					</div>
				</div>	
			</div>
		
		<!--Footer social links------------->	
			<div class="footer_top_lower">
				<nav class="footer_social">
					<ul>
						<li><a href=""><p class="fb"><i class="fa-brands fa-facebook-f"></i></p></a></li>
						<li><a href=""><p class="tw"><i class="fa-brands fa-twitter"></i></p></a></li>
						<li><a href=""><p class="gp"><i class="fa-brands fa-google-plus-g"></i></p></a></li>
					</ul>
				</nav>
			</div>
		</div>
		
		<div class="footer-bottom">
			<p>&copy; 2022. All Rights Reserved</p>
		</div>
	</footer>
<!--Footer Section End------------->

<!--Javascript Files for TinyMce Editor Start------------->	
	<!-- TinyMce Javascript local file ---->
	<script type="text/javascript" src="../css/tinymce/js/tinymce/tinymce.min.js?v=<?php echo time(); ?>"></script>
<!--Javascript Files for TinyMce Editor End------------->

<!--Script for TinyMce Editor Start------------->
<script>
	tinymce.init({
		selector: 'textarea.tinymce',
		plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste wordcount',
		toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
		height: 400,
		branding: false,
		promotion: false,
		// Disable all cloud services and premium features
		cloud_server: false,
		forced_root_block: 'p',
		// Prevent any API calls
		referrer_policy: 'no-referrer',
		setup: function (editor) {
			editor.on('init', function () {
				console.log('TinyMCE initialized successfully without cloud services');
			});
		}
	});
</script>
<!--Script for TinyMce Editor End------------->

<!--Javascript Fiels for Data Table Start------------->
	<script type="text/javascript" src="../js/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
	
	<!-- Data Table Javascript cdn ---->
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!--Javascript Fiels for Data Table End------------->

<!-- Initializing Data Table Start---->
	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		} );
	</script>
<!-- Initializing Data Table End---->

<!--Script for responsive navbar Start------------>
<script>
	var navList = document.getElementById("navList");
	function togglebtn(){
		navList.classList.toggle("hidemenu");
	}
	
	var sitedetailsNav = document.getElementById("sitedetailsNav");
	function togglebtn_sitedetails(){
		sitedetailsNav.classList.toggle("hide_dashboard_menu");
	}
	
	var categoryNav = document.getElementById("categoryNav");
	function togglebtn_category(){
		categoryNav.classList.toggle("hide_dashboard_menu");
	}
	
	var personalizeNav = document.getElementById("personalizeNav");
	function togglebtn_personalize(){
		personalizeNav.classList.toggle("hide_dashboard_menu");
	}
	
	var propertyNav = document.getElementById("propertyNav");
	function togglebtn_property(){
		propertyNav.classList.toggle("hide_dashboard_menu");
	}
</script>
<!--Script for responsive navbar End------------>
</body>
</html>