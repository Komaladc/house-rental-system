<?php
	include"inc/header.php";
	
	if(isset($_GET['wlistid'])){
		if($_GET['wlistid'] == NULL){
			echo"<script>window.location='index.php'</script>";
		} else{
			if(Session::get("userlogin") != true){
				echo"<script>window.location='signin.php'</script>";
			} else{
				$wlistId = $_GET['wlistid'];
				$loginId  = Session::get("userId");
				$addWlist = $pro->addToWishlist($wlistId, $loginId);
				
				if(isset($addWlist)){ 
					echo $addWlist; 
				}
			}
		}
	}
	
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['sendmessage'])){
		$sendmsg = $ibx->sendMessage($_POST);
		
		if(isset($sendmsg)){ 
			echo $sendmsg; 
		}
	}
?>
<!--Header Section End------------->


<!--Banner Section Start------------->	
<?php include"inc/banner.php"; ?>
<!--Banner Section End------------->


<!--Property List Section Start------------->	
	<div class= "list overflow">
		<h1 class="sub-title">property list</h1>
		<?php
			$getAllAd = $pro->getAllPropertyByRange();
			if($getAllAd){ 
			$totalAd = mysqli_num_rows($getAllAd);
		?>
		<div class="list_content <?php if($totalAd > 2){ ?>slider<?php } ?>">
		<!--List Item 1------------->
			<?php
				while($getad = $getAllAd->fetch_assoc()){ ?>
			<div class="list_item overflow">
				<div class="item_box overflow">
				<a href="property_details.php?adid=<?php echo $getad['adId'];?>">
					<div class="item_box_upper overflow">
						<div class="item_upper item_category">
							<p><?php echo $getad['catName'];?></p>
						</div>
						<div class="item_upper item_pricebox overflow">
							<div class="item_upper_left">
								<p><span>NPR <?php echo $getad['adRent'];?> / <?php if($getad['rentType'] == "mo"){echo"Month";} else{ echo"Week"; };?></span></p>
							</div>
							<a href="?wlistid=<?php echo $getad['adId'];?>">
							<div class="item_upper_left item_wlist_icon">
								<p><i class="fa-solid fa-heart"></i></p>
							</div>
							</a>
						</div>
					</div>
				</a>
				<a href="property_details.php?adid=<?php echo $getad['adId'];?>">
					<div class="list_img overflow">
						<img src="<?php echo $getad['adImg'];?>" alt="ad image">
					</div>
				</a>
				<a href="property_details.php?adid=<?php echo $getad['adId'];?>">
					<div class="item_box_lower overflow"> 
						<p><?php echo $getad['adTitle'];?></p>
						<h3><i class="fa-brands fa-accusoft"></i><span><?php echo $getad['catName'];?></span></h3>
						<p><i class="fa-solid fa-file-pen"></i>Posted on: <?php echo $fm->formatDate($getad['adDate']);?></p>
						<p><i class="fa-solid fa-rupee-sign"></i>NPR <?php echo $getad['adRent'];?>/<?php if($getad['rentType'] == "mo"){echo"Month";} else{ echo"week"; };?> <?php if($getad['adNegotiable'] == "negotiable"){ ?><span>(Negotiable)</span><?php } ?></p>
						<p><i class="fa-solid fa-location-dot"></i><?php echo $getad['adAddress'];?></p>
					</div>
				</a>
				</div>
			</div>
		<?php } ?>
		</div>
		<?php } ?>
		
		<div class="browse_list_button">
			<a href="property_list.php"><button class="btn_success btn_browse">browse list</button></a>
		</div>
	</div>
	
<!--Property List Section End------------->	


<!--Popular Category Section Start------------->	

<div class= "list overflow">
		<h1 class="sub-title">popular category</h1>
		<div class="list_content">
		
		<!--Category 1------------->
		<?php
			$getcat = $cat->getAllCat();
			if($getcat){
				while($category = $getcat->fetch_assoc()){
				$catId = $category['catId'];
				$totalAd = $cat->getCatAdNum($catId);
		?>
			<div class="list_item popular_category overflow">
				<div class="item_box overflow">
					<a href="property_by_cat.php?catid=<?php echo $category['catId'];?>">
						<div class="popular_cat_heading">
							<p>category</p>
						</div>
						<div class="popular_cat_img">
							<img src="<?php echo $category['catImg']?>"/>
							<div class="popular_cat_text">
								<p><?php echo $category['catName']?></p>
								<p><?php if(!empty($totalAd)){ echo $totalAd;} else{echo"0";} ?> Property ads</p>
							</div>
						</div>
					</a>
				</div>
			</div>
		<?php } } ?>
		</div>
	</div>
	
<!--Popular Category Section End------------->	


<!--About Us Section Start------------->
	<div class="about"> 
		<div class="about-container">
			<div class="about-content">
				<h1 class="sub-title">About PropertyFinder Nepal</h1>
				<div class="about_text">
					<div class="about-intro">
						<p>PropertyFinder Nepal is a leading digital platform revolutionizing the real estate rental market in Nepal. Since our inception, we have been committed to simplifying property transactions and creating seamless connections between property owners and potential tenants across the nation.</p>
						
						<p>Our mission is to transform how Nepalis search, discover, and secure rental properties through innovative technology solutions and exceptional customer service.</p>
					</div>
					
					<div class="company-stats">
						<div class="stats-grid">
							<div class="stat-item">
								<div class="stat-number">1000+</div>
								<div class="stat-label">Properties Listed</div>
							</div>
							<div class="stat-item">
								<div class="stat-number">500+</div>
								<div class="stat-label">Happy Customers</div>
							</div>
							<div class="stat-item">
								<div class="stat-number">7+</div>
								<div class="stat-label">Major Cities</div>
							</div>
							<div class="stat-item">
								<div class="stat-number">24/7</div>
								<div class="stat-label">Customer Support</div>
							</div>
						</div>
					</div>
					
					<div class="about-features">
						<div class="feature-grid">
							<div class="feature-item">
								<i class="fa-solid fa-shield-halved"></i>
								<h3>Trusted Platform</h3>
								<p>We ensure verified property listings and secure transactions, building trust between property owners and tenants through our rigorous verification process.</p>
							</div>
							<div class="feature-item">
								<i class="fa-solid fa-search"></i>
								<h3>Smart Search Technology</h3>
								<p>Our advanced search algorithms help you find the perfect property based on your specific requirements, budget, and preferred location.</p>
							</div>
							<div class="feature-item">
								<i class="fa-solid fa-handshake"></i>
								<h3>Seamless Connections</h3>
								<p>We facilitate direct communication between property owners and potential tenants, ensuring transparent and efficient rental processes.</p>
							</div>
							<div class="feature-item">
								<i class="fa-solid fa-chart-line"></i>
								<h3>Market Insights</h3>
								<p>Access real-time market data, pricing trends, and neighborhood insights to make informed rental decisions across Nepal.</p>
							</div>
						</div>
					</div>

					<div class="company-values">
						<h3>Our Core Values</h3>
						<div class="values-container">
							<div class="value-item">
								<h4><i class="fa-solid fa-heart"></i> Customer First</h4>
								<p>We prioritize our customers' needs and strive to exceed expectations in every interaction.</p>
							</div>
							<div class="value-item">
								<h4><i class="fa-solid fa-lightbulb"></i> Innovation</h4>
								<p>We continuously innovate our platform to provide cutting-edge solutions for the real estate market.</p>
							</div>
							<div class="value-item">
								<h4><i class="fa-solid fa-users-line"></i> Community</h4>
								<p>We believe in building strong communities by connecting people with their ideal living spaces.</p>
							</div>
						</div>
					</div>

					<div class="about-mission">
						<h3>Why Choose PropertyFinder Nepal?</h3>
						<p>With years of experience in the Nepali real estate market, we understand the unique challenges and opportunities in property rentals. Our platform combines local market expertise with modern technology to deliver unparalleled service quality.</p>
						
						<div class="project-features">
							<div class="feature-list">
								<h4>What Sets Us Apart:</h4>
								<ul>
									<li>✅ Comprehensive property verification and quality assurance</li>
									<li>✅ Advanced filtering and search capabilities</li>
									<li>✅ Secure booking and payment processing</li>
									<li>✅ 24/7 customer support in Nepali and English</li>
									<li>✅ Mobile-first responsive design for on-the-go access</li>
									<li>✅ Real-time notifications and instant messaging</li>
									<li>✅ Detailed property analytics and market reports</li>
									<li>✅ Multi-language support for diverse communities</li>
								</ul>
							</div>
						</div>
						
						<div class="company-commitment">
							<h4>Our Commitment to Excellence</h4>
							<p>At PropertyFinder Nepal, we are dedicated to maintaining the highest standards of service quality, transparency, and customer satisfaction. Our team of real estate professionals works tirelessly to ensure that every property listing meets our strict quality guidelines and that every customer receives personalized attention.</p>
							
							<p>Join thousands of satisfied customers who have found their perfect rental properties through our platform. Experience the future of property rental in Nepal with PropertyFinder Nepal.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<!--About Us Section End------------->


<!--Contact Section Start------------->
	<div class="contact">
		<form action="" method="POST">
			<h1 class="sub-title">Get In Touch</h1>
			<div class="contact_body overflow">
				<div class="contact_part">
				  <label for="name" class="required"><b>Name:</b></label>
				  <input type="text" placeholder="Enter your full name" name="name" required minlength="2" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed"><br><br><br>
				  
				  <label for="phone" class="required"><b>Mobile No:</b></label>
				  <input type="tel" placeholder="98xxxxxxxx or 97xxxxxxxx (Nepal only)" name="phone" required pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter Nepal phone number (10 digits starting with 98 or 97)"><br><br><br>
				  
				  <label for="email" class="required"><b>Email:</b></label>
				  <input type="email" placeholder="Enter your email address (e.g., user@gmail.com)" name="email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address"><br><br><br>
				</div>
			
				<div class="contact_part">
				  <label for="message" class="required"><b>Message:</b></label>
				  <textarea placeholder="Type your message here (minimum 10 characters)" name="message" required minlength="10" title="Please enter your message"></textarea>
				</div>
			</div>
			<div class="contact_button">
				<button class="btn_success" type="submit" name="sendmessage">Send</button>
			</div>
		</form>
	</div>

<!--Contact Section End------------->
	</div>
	</div>
	
<script src="js/form-validation.js"></script>

<!--Footer Section Start------------->
<?php include"inc/footer.php"; ?>
<!--Footer Section End------------->