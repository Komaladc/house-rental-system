<?php
	include"inc/header.php";
	Session::chkLogin();
	
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['signup'])){
		$regimsg = $usr->UserRegistration($_POST);
	}
?>
<!--Header Section End------------->

<!--Form Start------------->
<div class="container form_container">
	<div class="mcol_8 register overflow">
	<div class="mcol_3 background signup_bg" style="background-image:url(images/signup_bg.jpg);"></div>
	
	<div class="mcol_9">
		<form action="" method="POST">
		<table class="tbl_form">
			<caption><h1>create account</h1></caption>
			<?php if(isset($regimsg)){ ?>
			<tr>
				<td colspan="4">
					<?php echo $regimsg; ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="fname" class="required">First Name</label>
				</td>
				<td colspan="3">
					<input type="text" placeholder="Enter first name" name="fname" required minlength="2" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="lname" class="required">Last Name</label>
				</td>
				<td colspan="3">
					<input type="text" placeholder="Enter last name" name="lname" required minlength="2" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="username" class="required">Username</label>
				</td>
				<td colspan="3">
					<input type="text" placeholder="3-20 characters, letters, numbers, underscore only" name="username" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+" title="Username must be 3-20 characters, letters, numbers and underscore only">
				</td>
			</tr>
			  
			<tr>
				<td>
					<label for="email" class="required">Email Address</label>
				</td>
				<td colspan="3">
					<input type="email" placeholder="Enter email address (e.g., user@gmail.com)" name="email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address with proper domain">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="cellno" class="required">Cell Number</label>
				</td>
				<td colspan="3">
					<input type="tel" placeholder="98xxxxxxxx or 97xxxxxxxx (Nepal only)" name="cellno" required pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter Nepal phone number starting with 98 or 97 (10 digits total)">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="address" class="required">Address</label>
				</td>
				<td colspan="3">
					<textarea style="resize:none;" name="address" placeholder="Enter your detailed address" required minlength="10" title="Please provide a detailed address"></textarea>
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="password" class="required">Password</label>
				</td>
				<td colspan="3">
					<input type="password" placeholder="Strong password with 8+ characters" name="password" required minlength="6" title="Password must be at least 6 characters long">
				</td>
			</tr>

			<tr>
				<td>
					<label for="cnf_password" class="required">Confirm Password</label>
				</td>
				<td colspan="3">
					<input type="password" placeholder="Confirm password" name="cnf_password" required title="Please confirm your password">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="level" class="required">Join As</label>
				</td>
				<td>
					<input type="radio" name="level" value="1" required><span>Regular User</span><br>
					<small style="color: #666;">Browse and rent properties</small>
				</td>
				<td>
					<input type="radio" name="level" value="2" required><span>Property Owner</span><br>
					<small style="color: #666;">List and manage properties</small>
				</td>
				<td>
					<input type="radio" name="level" value="3" required><span>Real Estate Agent</span><br>
					<small style="color: #666;">Professional property management</small>
				</td>
			</tr>
			
			<tr>
				<td colspan="4" style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
					<strong>📋 Note for Owners & Agents:</strong><br>
					<small style="color: #666;">
						• Your account will require admin verification after email confirmation<br>
						• Please ensure you have valid documents ready for verification<br>
						• Verification usually takes 1-2 business days
					</small>
				</td>
			</tr>
			
			<tr>
				<td colspan="4">
					<button class="btn_success" type="submit" name="signup">Sign Up</button>
				</td>
			</tr>

			<tr>
				<td colspan="4">
				<p>Already have an account?
				<span><a href="signin.php">Sign in</a></span>
				</p>
				</td>
			</tr>
		</table>
		</form>
	</div>
	</div>
</div>
<!--Form End------------->

<script src="js/form-validation.js"></script>
	
<!--Footer Section Start------------->
<?php include"inc/footer.php"; ?>
<!--Footer Section End------------->