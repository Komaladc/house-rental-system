<?php
	include"inc/header.php";
	Session::chkLogin();
	
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['signin'])){
		$loginmsg = $usr->UserLogin($_POST);
	}
?>
<!--Header Section End------------->

<!--Sign In Form Start------------->
<div class="container form_container">
	<div class="mcol_8 register">
	<div class="mcol_3">
		<img src="images/signin_bg.png" alt="sign in background"/>
	</div>
	
	<div class="mcol_9">
		<form action="" method="POST">
		<table class="tbl_form">
			<caption><h1>sign in</h1></caption>
			<?php if(isset($loginmsg)){ ?>
			<tr>
				<td colspan="2">
					<?php echo $loginmsg; ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="email" class="required">Email Address</label>
				</td>
				<td colspan="2">
					<input type="email" placeholder="Enter email address (e.g., user@gmail.com)" name="email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address">
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="password" class="required">Password</label>
				</td>
				<td colspan="2">
					<input type="password" placeholder="Enter password" name="password" required title="Please enter your password">
				</td>
			</tr>
			
			<tr>
				<td colspan="2">
					<button class="btn_success" type="submit" name="signin">Sign In</button>
				</td>
			</tr>
			
			<tr>
				<td colspan="2">
				<p><a href="forgot_password_otp.php">🔐 Forgot Password?</a></p>
				</td>
			</tr>
			
			<tr>
				<td colspan="2">
				<p>Not joined yet?
				<span><a href="signup_enhanced.php">🆕 Create New Account</a></span>
				</p>
				<p style="font-size: 12px; color: #666; margin-top: 5px;">
				📧 Email verification required for all new accounts
				</p>
				</td>
			</tr>
		</table>
		</form>
	</div>
	</div>
</div>
<!--Sign In Form End------------->

<script src="js/form-validation.js"></script>

	
<!--Footer Section Start------------->
<?php include"inc/footer.php"; ?>
<!--Footer Section End------------->