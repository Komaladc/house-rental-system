<?php 
	include "inc/header.php";
	include "classes/PreRegistrationVerification.php";
	include "classes/EmailOTP.php";
	
	$preVerification = new PreRegistrationVerification();
	$emailOTP = new EmailOTP();
	$registrationMsg = "";
	$showForm = true;
	$showOtpForm = false;
	
	// Handle OTP verification
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_account'])){
		$email = $_POST['email'];
		$otp = $_POST['otp_code'];
		
		// Debug output (remove this in production)
		error_log("OTP Verification Attempt - Email: $email, OTP: $otp");
		
		// Get pending verification data to get the token
		$query = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
		$result = $db->select($query);
		
		if($result && mysqli_num_rows($result) > 0) {
			$pendingData = mysqli_fetch_assoc($result);
			$token = $pendingData['verification_token'];
			
			// Debug output
			error_log("Found pending verification for: $email, Token: " . substr($token, 0, 10) . "...");
			
			// Verify OTP and create account in one step
			$accountResult = $preVerification->verifyAndCreateAccount($email, $token, $otp);
			$registrationMsg = $accountResult['message'];
			
			// Debug output
			error_log("Verification result: " . ($accountResult['success'] ? 'SUCCESS' : 'FAILED') . " - " . strip_tags($accountResult['message']));
			
			if($accountResult['success']) {
				// Auto-login and redirect
				if(isset($accountResult['user_data'])) {
					$userData = $accountResult['user_data'];
					Session::set("userlogin", true);
					Session::set("userEmail", $userData['email']);
					Session::set("userFName", $userData['fname']);
					Session::set("userLName", $userData['lname']);
					
					echo "<script>
						setTimeout(function() {
							window.location.href = 'index.php';
						}, 3000);
					</script>";
				}
				$showForm = false;
				$showOtpForm = false;
			} else {
				$showOtpForm = true;
			}
		} else {
			$registrationMsg = '<div class="error">❌ No pending verification found. Please register again.</div>';
			error_log("No pending verification found for: $email");
			$showForm = true;
			$showOtpForm = false;
		}
	}
	
	// Handle initial registration
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])){
		// Collect all form data
		$registrationData = [
			'fname' => $_POST['fname'],
			'lname' => $_POST['lname'],
			'username' => $_POST['username'],
			'email' => $_POST['email'],
			'cellno' => $_POST['cellno'],
			'address' => $_POST['address'],
			'password' => $_POST['password'],
			'level' => $_POST['level']
		];
		
		// Initiate email verification
		$result = $preVerification->initiateEmailVerification($registrationData);
		$registrationMsg = $result['message'];
		
		if ($result['success']) {
			$showForm = false; // Hide form after successful email send
			$showOtpForm = true; // Show OTP verification form
		}
	}
?>

<div class="page_title">
	<h1 class="sub-title">📧 Email Verification Required</h1>
</div>

<div class="container form_container background" style="background-image:url(images/signup_bg.jpg);background-blend-mode:overlay;">
	<div class="mcol_6">
		<?php if($registrationMsg) { echo $registrationMsg; } ?>
		
		<?php if($showForm) { ?>
		<!-- Registration Form -->
		<div class="pre_registration_notice">
			<div class="notice_box">
				<h3>🔐 Secure Registration Process</h3>
				<p><strong>Step 1:</strong> Fill out the registration form below</p>
				<p><strong>Step 2:</strong> We'll send a verification email to your address</p>
				<p><strong>Step 3:</strong> Enter the OTP code to create your account</p>
				<div class="security_note">
					<strong>⚠️ Important:</strong> Your account will NOT be created until you verify your email address. 
					Please use a real email address that you can access.
				</div>
			</div>
		</div>
		
		<form action="" method="POST" id="registrationForm">
			<table class="tbl_form">
				<caption><h1>🇳🇵 Create Your Account - Property Finder Nepal</h1></caption>
				
				<tr>
					<td>
						<label for="fname"><b>👤 First Name:</b></label>
					</td>
					<td>
						<input type="text" 
							   placeholder="Enter First Name" 
							   name="fname" 
							   pattern="[a-zA-Z\s]{2,30}" 
							   title="First name should be 2-30 characters and contain only letters"
							   value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>"
							   required>
						<span class="error-message" id="fname-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="lname"><b>👤 Last Name:</b></label>
					</td>
					<td>
						<input type="text" 
							   placeholder="Enter Last Name" 
							   name="lname" 
							   pattern="[a-zA-Z\s]{2,30}" 
							   title="Last name should be 2-30 characters and contain only letters"
							   value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>"
							   required>
						<span class="error-message" id="lname-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="username"><b>🆔 Username:</b></label>
					</td>
					<td>
						<input type="text" 
							   placeholder="Enter Username" 
							   name="username" 
							   pattern="[a-zA-Z0-9_]{3,20}" 
							   title="Username should be 3-20 characters with letters, numbers, and underscores only"
							   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
							   autocomplete="username"
							   required>
						<span class="error-message" id="username-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="email"><b>📧 Email Address:</b></label>
					</td>
					<td>
						<input type="email" 
							   placeholder="Enter your real email address" 
							   name="email" 
							   id="email"
							   pattern="[a-zA-Z0-9._%\-+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" 
							   title="Please enter a valid email address"
							   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
							   autocomplete="email"
							   required>
						<span class="error-message" id="email-error"></span>
						<span class="success-message" id="email-success"></span>
						<div class="email-validation-status" id="email-validation-status"></div>
						<div class="email_notice">
							<small>⚠️ Please use a real email address. Temporary or fake emails are not allowed.</small>
						</div>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="cellno"><b>📱 Mobile Number:</b></label>
					</td>
					<td>
						<input type="tel" 
							   placeholder="98xxxxxxxx (Nepal format)" 
							   name="cellno" 
							   pattern="(98|97)[0-9]{8}" 
							   title="Phone number must be 10 digits starting with 98 or 97"
							   value="<?php echo isset($_POST['cellno']) ? htmlspecialchars($_POST['cellno']) : ''; ?>"
							   autocomplete="tel"
							   required>
						<span class="error-message" id="cellno-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="address"><b>Address:</b></label>
					</td>
					<td>
						<input type="text" 
							   placeholder="Enter your address" 
							   name="address" 
							   title="Please enter your full address"
							   value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
							   required>
						<span class="error-message" id="address-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="password"><b>🔐 Password:</b></label>
					</td>
					<td>
						<input type="password" 
							   placeholder="Enter password (min 8 characters)" 
							   name="password" 
							   pattern=".{8,}" 
							   title="Password must be at least 8 characters long"
							   autocomplete="new-password"
							   required>
						<span class="error-message" id="password-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="cpassword"><b>🔐 Confirm Password:</b></label>
					</td>
					<td>
						<input type="password" 
							   placeholder="Confirm password" 
							   name="cpassword" 
							   autocomplete="new-password"
							   required>
						<span class="error-message" id="cpassword-error"></span>
					</td>
				</tr>
				
				<tr>
					<td>
						<label for="level"><b>👨‍💼 Account Type:</b></label>
					</td>
					<td>
						<select name="level" required>
							<option value="" disabled selected>Choose account type</option>
							<option value="1" <?php echo (isset($_POST['level']) && $_POST['level'] == '1') ? 'selected' : ''; ?>>🏠 Property Seeker</option>
							<option value="2" <?php echo (isset($_POST['level']) && $_POST['level'] == '2') ? 'selected' : ''; ?>>🏘️ Property Owner</option>
							<option value="3" <?php echo (isset($_POST['level']) && $_POST['level'] == '3') ? 'selected' : ''; ?>>🏢 Real Estate Agent</option>
						</select>
						<span class="error-message" id="level-error"></span>
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<button type="submit" name="register" class="btn_success" id="registerBtn">
							📧 Send Verification Email
						</button>
					</td>
				</tr>
				
				<tr>
					<td colspan="2" class="signup_links">
						<p>Already have an account? <a href="signin.php">Sign In Here</a></p>
					</td>
				</tr>
			</table>
		</form>
		
		<?php } elseif($showOtpForm) { ?>
		<!-- OTP Verification Form -->
		<div class="otp_verification_section">
			<div class="verification_header">
				<h3>📱 Enter Verification Code</h3>
				<p>We've sent a 6-digit verification code to your email address.</p>
				<div class="email_display">
					<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>
				</div>
			</div>
			
			<form id="otpVerificationForm" action="" method="POST">
				<input type="hidden" name="verify_account" value="1">
				<input type="hidden" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
				
				<table class="tbl_form">
					<tr>
						<td colspan="2">
							<label for="otp_code"><b>🔢 Verification Code:</b></label>
							<input type="text" 
								   name="otp_code" 
								   id="otp_code" 
								   placeholder="000000" 
								   maxlength="6" 
								   pattern="[0-9]{6}" 
								   title="Please enter a 6-digit number"
								   required 
								   autocomplete="one-time-code"
								   style="font-size: 24px; text-align: center; letter-spacing: 8px;">
							<span class="error-message" id="otp-error"></span>
							<div class="otp-info">
								<small>💡 Enter the 6-digit code, then click "Verify & Create Account"</small>
							</div>
						</td>
					</tr>
					
					<tr>
						<td colspan="2">
							<button type="submit" class="btn_success" id="verifyOtpBtn">
								✅ Verify & Create Account
							</button>
						</td>
					</tr>
				</table>
			</form>
		</div>
		
		<div class="help_section">
			<h4>💡 Having trouble?</h4>
			<ul>
				<li>Check your spam/junk folder</li>
				<li>Wait a few minutes for the email to arrive</li>
				<li>The verification code expires in 20 minutes</li>
				<li>You can also click the verification link in the email</li>
				<li>All times are in Nepal Standard Time (NPT)</li>
			</ul>
		</div>
		<?php } ?>
		
		<div class="back_links">
			<a href="signin.php">← Back to Sign In</a> |
			<a href="index.php">← Home</a>
		</div>
	</div>
</div>

<style>
/* Email Validation Styles */
.email-validation-status {
	margin: 8px 0;
	padding: 8px 12px;
	border-radius: 5px;
	font-size: 14px;
	display: none;
}

.email-validation-status.validating {
	background: #e8f4fd;
	color: #2980b9;
	border: 1px solid #3498db;
	display: block;
}

.email-validation-status.valid {
	background: #d5f4e6;
	color: #27ae60;
	border: 1px solid #27ae60;
	display: block;
}

.email-validation-status.invalid {
	background: #fdeaea;
	color: #e74c3c;
	border: 1px solid #e74c3c;
	display: block;
}

.success-message {
	color: #27ae60;
	font-size: 14px;
	margin-top: 5px;
	display: none;
}

.success-message.show {
	display: block;
}

/* OTP Section Styles */
.otp_verification_section {
	background: white;
	padding: 30px;
	border-radius: 15px;
	box-shadow: 0 10px 30px rgba(0,0,0,0.1);
	margin: 20px 0;
}

.verification_header {
	text-align: center;
	margin-bottom: 25px;
}

.verification_header h3 {
	color: #2c3e50;
	margin-bottom: 10px;
}

.email_display {
	background: #f8f9fa;
	padding: 12px 15px;
	border: 2px solid #e9ecef;
	border-radius: 8px;
	font-family: 'Courier New', monospace;
	font-weight: bold;
	color: #2c3e50;
	margin: 10px 0;
	word-break: break-all;
}

#otp_code {
	width: 100% !important;
	padding: 15px !important;
	border: 2px solid #ddd !important;
	border-radius: 8px !important;
	font-size: 24px !important;
	text-align: center !important;
	letter-spacing: 8px !important;
	background: #f8f9fa !important;
	font-family: 'Courier New', monospace !important;
}

#otp_code:focus {
	border-color: #3498db !important;
	outline: none !important;
	background: white !important;
}

.help_section {
	background: #fff3cd;
	padding: 20px;
	border-radius: 8px;
	border-left: 4px solid #ffc107;
	margin: 20px 0;
}

.help_section h4 {
	color: #856404;
	margin-top: 0;
}

.help_section ul {
	margin: 10px 0;
	padding-left: 20px;
}

.help_section li {
	margin: 8px 0;
	color: #856404;
}

.pre_registration_notice {
	margin: 20px 0;
}

.notice_box {
	background: #e8f5e8;
	padding: 20px;
	border-radius: 10px;
	border-left: 4px solid #27ae60;
}

.notice_box h3 {
	color: #27ae60;
	margin-top: 0;
}

.security_note {
	background: #fff3cd;
	color: #856404;
	padding: 15px;
	border-radius: 5px;
	border-left: 4px solid #ffc107;
	margin: 15px 0;
}

.back_links {
	text-align: center;
	margin: 30px 0;
	padding: 20px;
	border-top: 1px solid #eee;
}

.back_links a {
	color: #3498db;
	text-decoration: none;
	font-weight: bold;
	margin: 0 10px;
}

.back_links a:hover {
	text-decoration: underline;
}

.otp-info {
	margin-top: 8px;
	text-align: center;
}

.otp-info small {
	color: #6c757d;
	font-style: italic;
}

#otp_code.complete {
	border-color: #28a745 !important;
	background: #f8fff9 !important;
}

#verifyOtpBtn:disabled {
	background: #6c757d !important;
	cursor: not-allowed !important;
}
</style>

<script>
// Real-time email validation (simplified)
document.addEventListener('DOMContentLoaded', function() {
	const emailInput = document.getElementById('email');
	const emailError = document.getElementById('email-error');
	const emailSuccess = document.getElementById('email-success');
	const emailStatus = document.getElementById('email-validation-status');
	
	if (emailInput) {
		emailInput.addEventListener('input', function() {
			const email = this.value.trim();
			
			if (email.length < 5) {
				hideEmailStatus();
				return;
			}
			
			// Simple email validation without API call
			if (validateEmailFormat(email)) {
				if (emailStatus) {
					emailStatus.className = 'email-validation-status valid';
					emailStatus.textContent = '✅ Email format is valid';
				}
				if (emailSuccess) {
					emailSuccess.textContent = 'Email format looks good!';
					emailSuccess.classList.add('show');
				}
				if (emailError) emailError.textContent = '';
			} else {
				if (emailStatus) {
					emailStatus.className = 'email-validation-status invalid';
					emailStatus.textContent = '❌ Please enter a valid email address';
				}
				if (emailError) emailError.textContent = 'Please enter a valid email address';
				if (emailSuccess) {
					emailSuccess.textContent = '';
					emailSuccess.classList.remove('show');
				}
			}
		});
	}
	
	function validateEmailFormat(email) {
		const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
		return emailRegex.test(email);
	}
	
	function hideEmailStatus() {
		if (emailStatus) {
			emailStatus.style.display = 'none';
		}
		if (emailError) emailError.textContent = '';
		if (emailSuccess) {
			emailSuccess.textContent = '';
			emailSuccess.classList.remove('show');
		}
	}
	
	// OTP input handling
	const otpInput = document.getElementById('otp_code');
	const verifyBtn = document.getElementById('verifyOtpBtn');
	
	if (otpInput) {
		otpInput.focus();
		
		otpInput.addEventListener('input', function() {
			// Only allow numbers
			this.value = this.value.replace(/[^0-9]/g, '');
			
			// Visual feedback when 6 digits are entered
			if (this.value.length === 6) {
				this.classList.add('complete');
				if (verifyBtn) {
					verifyBtn.style.background = '#28a745';
					verifyBtn.innerHTML = '✅ Ready to Verify & Create Account';
				}
			} else {
				this.classList.remove('complete');
				if (verifyBtn) {
					verifyBtn.style.background = '#007cba';
					verifyBtn.innerHTML = '✅ Verify & Create Account';
				}
			}
		});
		
		otpInput.addEventListener('paste', function(e) {
			e.preventDefault();
			const pastedText = (e.clipboardData || window.clipboardData).getData('text');
			const numericOnly = pastedText.replace(/[^0-9]/g, '').substring(0, 6);
			this.value = numericOnly;
			
			// Trigger input event for visual feedback
			const inputEvent = new Event('input', { bubbles: true });
			this.dispatchEvent(inputEvent);
		});
	}
});

// Form validation before submit
const registrationForm = document.getElementById('registrationForm');
if (registrationForm) {
	registrationForm.addEventListener('submit', function(e) {
		// Check password confirmation
		const password = document.querySelector('input[name="password"]').value;
		const cpassword = document.querySelector('input[name="cpassword"]').value;
		
		if (password !== cpassword) {
			e.preventDefault();
			alert('❌ Passwords do not match!');
			return false;
		}
		
		// Basic validation
		const email = document.getElementById('email').value;
		const phone = document.getElementById('cellno').value;
		
		if (!validateEmailFormat(email)) {
			e.preventDefault();
			alert('❌ Please enter a valid email address');
			return false;
		}
		
		if (!phone.match(/^(98|97)[0-9]{8}$/)) {
			e.preventDefault();
			alert('❌ Phone number must be 10 digits starting with 98 or 97');
			return false;
		}
		
		// Show loading state
		const submitBtn = document.getElementById('registerBtn');
		submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Email...';
		submitBtn.disabled = true;
		
		// Allow form submission
		return true;
	});
}
</script>

<?php include"inc/footer.php"; ?>
