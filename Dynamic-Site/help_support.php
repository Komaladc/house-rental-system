<?php 
	include"inc/header.php";
	
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['sendmessage'])){
		$sendmsg = $ibx->sendMessage($_POST);
	}
?>
<!--Header Section End------------->

<div class="page_title">
	<h1 class="sub-title">help & support</h1>
</div>

<!--Help Section Start------------->
<div class="container">
	<div class="mcol_8">
	<!--Contact Section Start------------->
		<div class="property_contact">
			<div class="property_contact_title">
				<h1>contact us</h1>
			</div>
			
			<form action="" method="POST">
			<?php
				if(isset($sendmsg)){ 
					echo $sendmsg; 
				}
			?>
				<div class="contact_body overflow">
					<div class="contact_part">
					  <label for="name"><b>Name:</b></label>
					  <input type="text" placeholder="Enter name" name="name" 
					         pattern="[a-zA-Z\s]{2,50}" 
					         title="Name should contain only letters and be 2-50 characters long" required><br><br><br>
					  
					  <label for="phone"><b>Mobile No:</b></label>
					  <input type="tel" placeholder="Enter Mobile No (98xxxxxxxx)" name="phone" 
					         pattern="(98|97)[0-9]{8}" 
					         title="Phone number must be 10 digits starting with 98 or 97" required><br><br><br>
					  
					  <label for="email"><b>Email:</b></label>
					  <input type="email" placeholder="Enter Email" name="email" 
					         pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
					         title="Please enter a valid email address" required><br><br><br>
					</div>
				
					<div class="contact_part">
					  <label for="message"><b>Message:</b></label>
					  <textarea placeholder="Type your message" name="message" required></textarea>
					</div>
				</div>
				
				<div class="contact_button overflow">
					<button class="btn_success" type="submit" name="sendmessage">Send</button>
				</div>
			</form>
			
			<div class="property_contact_body">
				<div class="contact_part">
					<h3>address</h3>
					<p>Property Finder Nepal Ltd. Thamel Marg, Kathmandu-44600, Nepal</p>
				</div>
				<div class="contact_part">
					<div class="virtual_contact overflow">
						<div><p>telephone</p></div>
						<div><p>+977-1-4567890</p></div>
					</div>
					<div class="virtual_contact overflow">
						<div><p>mobile</p></div>
						<div><p>+977-9841234567</p></div>
					</div>
					<div class="virtual_contact overflow">
						<div><p>fax</p></div>
						<div><p>+977-1-4567891</p></div>
					</div>
					<div class="virtual_contact overflow">
						<div><p>e-mail</p></div>
						<div><p>info@propertyfindernepal.com</p></div>
					</div>
					<div class="virtual_contact overflow">
						<div><p>website</p></div>
						<div><p>www.propertyfindernepal.com</p></div>
					</div>
				</div>
			</div>
		</div>
	<!--Contact Section End------------->	
		
		
	<!--FAQ Section Start------------->	
		<div class="property_faq">
			<div class="faq_title">
				<h1>freequently asked questions</h1>
			</div>
			<div class="faq_body">
				<div class="faq_content">
					<h3>Why choose Property Finder Nepal?</h3>
					<p>We offer the most comprehensive and trusted property rental platform in Nepal. Our verified listings, transparent pricing, and dedicated customer support make us the preferred choice for thousands of renters and property owners across the country. Every property is personally verified by our team to ensure quality and authenticity.</p>
				</div>
				
				<div class="faq_content">
					<h3>How to book a property through our platform?</h3>
					<p>Booking a property is simple: Browse our verified listings, view detailed photos and information, contact the property owner directly through our secure messaging system, schedule a viewing, and finalize your rental agreement. We provide guidance throughout the entire process to ensure a smooth experience for both tenants and landlords.</p>
				</div>
				
				<div class="faq_content">
					<h3>What areas do you cover in Nepal?</h3>
					<p>We cover all major cities and towns across Nepal including Kathmandu, Pokhara, Chitwan, Butwal, Dharan, Biratnagar, and many more. Our network continues to expand to serve property seekers in every corner of beautiful Nepal.</p>
				</div>
				
				<div class="faq_content">
					<h3>Is the platform safe and secure?</h3>
					<p>Absolutely. We implement robust security measures to protect your personal information and transactions. All communications are encrypted, property listings are verified, and we have a dedicated support team to assist with any concerns. Your safety and security are our top priorities.</p>
				</div>
			</div>
		</div>
	<!--FAQ Section End------------->
	</div>
</div>
<!--Help Section End------------->

<script src="js/form-validation.js"></script>
	
<!--Footer Section Start------------->
<?php include"inc/footer.php"; ?>
<!--Footer Section End------------->