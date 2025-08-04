<?php 
	include"inc/header.php";
	include"classes/PreRegistrationVerification.php";
	
	$preVerification = new PreRegistrationVerification();
	$verificationMsg = "";
	$showOtpForm = false;
	$showSuccess = false;
	$demoMode = isset($_GET['demo']) && $_GET['demo'] == '1';
	
	// Handle demo mode
	if($demoMode && isset($_GET['email'])) {
		$email = htmlspecialchars($_GET['email']);
		$showOtpForm = true;
		
		// Get demo data from session
		$demoName = $_SESSION['demo_name'] ?? 'Demo User';
		$demoType = $_SESSION['demo_type'] ?? 'Property Owner';
		
		$verificationMsg = "<div class='alert alert_info'>
			🎯 <strong>Demo Mode Active</strong><br>
			<strong>Name:</strong> $demoName<br>
			<strong>Account Type:</strong> $demoType<br>
			<strong>Email:</strong> $email<br><br>
			
			This is a demonstration of the email verification process.<br>
			In the full system, a 6-digit code would be sent to <strong>$email</strong><br><br>
			
			<strong>For this demo, use any 6-digit code (example: <strong>123456</strong>)</strong>
		</div>";
	}
	
	// Handle direct link verification
	if(isset($_GET['email']) && isset($_GET['token'])) {
		$email = mysqli_real_escape_string($db->link, $_GET['email']);
		$token = mysqli_real_escape_string($db->link, $_GET['token']);
		
		$result = $preVerification->verifyAndCreateAccount($email, $token);
		$verificationMsg = $result['message'];
		
		if($result['success']) {
			$showSuccess = true;
			// Don't auto-login - redirect to sign-in page instead
			error_log("Email verification successful - account created, redirecting to sign-in");
		}
	}
	
	// Handle OTP verification
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_otp'])) {
		$email = mysqli_real_escape_string($db->link, $_POST['email']);
		$otp = mysqli_real_escape_string($db->link, $_POST['otp']);
		$token = mysqli_real_escape_string($db->link, $_POST['token']);
		$isDemo = isset($_POST['demo']) && $_POST['demo'] == '1';
		
		// Handle demo mode
		if($isDemo) {
			if(strlen($otp) == 6 && ctype_digit($otp)) {
				$showSuccess = true;
				
				// Get demo data from session
				$demoName = $_SESSION['demo_name'] ?? 'Demo User';
				$demoType = $_SESSION['demo_type'] ?? 'Property Owner';
				$demoUsername = $_SESSION['demo_username'] ?? 'demo_user';
				$demoPhone = $_SESSION['demo_phone'] ?? '';
				$demoAddress = $_SESSION['demo_address'] ?? '';
				$demoCitizenship = $_SESSION['demo_citizenship'] ?? '';
				
				$verificationMsg = "<div class='alert alert_success'>
					🎉 <strong>Demo Verification Successful!</strong><br><br>
					
					<strong>📋 Account Summary:</strong><br>
					• <strong>Name:</strong> $demoName<br>
					• <strong>Username:</strong> $demoUsername<br>
					• <strong>Email:</strong> $email<br>
					• <strong>Phone:</strong> $demoPhone<br>
					• <strong>Account Type:</strong> $demoType<br>
					• <strong>Address:</strong> $demoAddress<br>
					" . ($demoCitizenship ? "• <strong>Citizenship ID:</strong> $demoCitizenship<br>" : "") . "<br>
					
					<strong>✅ In the full system, this account would be:</strong><br>
					• Stored in the database with encrypted password<br>
					• Email verified and activated<br>
					• Ready for admin approval (Property Owners/Agents)<br>
					• Able to sign in and access the platform<br>
					• Eligible to list properties after approval<br><br>
					
					<strong>🚀 Next Steps in Production:</strong><br>
					• Set up database connection<br>
					• Configure email SMTP settings<br>
					• Enable real OTP verification<br>
					• Activate admin approval workflow
				</div>";
				
				// Clear demo session data
				unset($_SESSION['demo_email'], $_SESSION['demo_name'], $_SESSION['demo_type'], 
					  $_SESSION['demo_username'], $_SESSION['demo_phone'], $_SESSION['demo_address'], 
					  $_SESSION['demo_citizenship']);
			} else {
				$verificationMsg = "<div class='alert alert_danger'>❌ Please enter a valid 6-digit verification code.</div>";
				$showOtpForm = true;
			}
		} else {
			// Regular verification logic
			// Debug logging
			error_log("=== OTP VERIFICATION ATTEMPT ===");
			error_log("Email: $email");
			error_log("OTP: $otp");
			error_log("Token: " . (empty($token) ? 'EMPTY' : 'PROVIDED'));
			error_log("POST data: " . print_r($_POST, true));
			
			// Validate input
			if (empty($email) || empty($otp)) {
				$verificationMsg = "<div class='alert alert_danger'>❌ Email and OTP are required.</div>";
				error_log("Validation failed - missing email or OTP");
			} elseif (strlen($otp) !== 6 || !ctype_digit($otp)) {
				$verificationMsg = "<div class='alert alert_danger'>❌ OTP must be exactly 6 digits.</div>";
				error_log("Validation failed - invalid OTP format: $otp");
			} else {
				// If we have a token, use the combined verification method
				// If no token, use the dedicated OTP verification method
				if(!empty($token)) {
					error_log("Using verifyAndCreateAccount method (with token)");
					$result = $preVerification->verifyAndCreateAccount($email, $token, $otp);
				} else {
					error_log("Using verifyOTPAndCreateAccount method (no token)");
					$result = $preVerification->verifyOTPAndCreateAccount($email, $otp);
				}
				
				error_log("Verification Result - Success: " . ($result['success'] ? 'YES' : 'NO'));
				error_log("Verification Message: " . strip_tags($result['message']));
			
			$verificationMsg = $result['message'];
			
			if($result['success']) {
				$showSuccess = true;
				// Don't auto-login - redirect to sign-in page instead
				error_log("OTP verification successful - account created, redirecting to sign-in");
			} else {
				error_log("OTP verification failed");
			}
		}
	}
	
	// Handle resend verification
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['resend_verification'])) {
		$email = mysqli_real_escape_string($db->link, $_POST['email']);
		$result = $preVerification->resendVerification($email);
		$verificationMsg = $result['message'];
	}
	
	// Show OTP form if email is provided in URL but no token
	if(isset($_GET['email']) && !isset($_GET['token'])) {
		$showOtpForm = true;
	}
?>

<div class="page_title">
	<h1 class="sub-title">🔐 Email Verification</h1>
</div>

<div class="container form_container background" style="background-image:url(images/signup_bg.jpg);background-blend-mode:overlay;">
	<div class="mcol_6">
		<?php if($verificationMsg) { echo $verificationMsg; } ?>
		
		<?php if($showSuccess) { ?>
		<!-- Success State -->
		<div class="success_container">
			<div class="success_icon">🎉</div>
			<h2>Welcome to Property Finder Nepal!</h2>
			<p>Your email has been verified and your account has been created successfully.</p>
			
			<div class="success_actions">
				<a href="signin.php" class="btn_primary">🔑 Sign In to Your Account</a>
				<p style="margin-top: 15px; color: #666;">You will be automatically redirected to the sign-in page in 5 seconds...</p>
			</div>
			
			<div class="welcome_info">
				<h3>🎯 What's Next?</h3>
				<ul>
					<li>✅ Your account is now active and verified</li>
					<li>🔑 Sign in with your email and password</li>
					<li>🔍 Start searching for rental properties</li>
					<li>💝 Save properties to your wishlist</li>
					<li>📞 Contact property owners directly</li>
					<li>🏘️ List your own properties (for owners)</li>
				</ul>
			</div>
		</div>
		
		<?php } elseif($showOtpForm || (isset($_POST['email']) && !$showSuccess)) { ?>
		<!-- OTP Verification Form -->
		<div class="otp_verification_container">
			<div class="verification_header">
				<h2>📱 Enter Verification Code</h2>
				<p>Please enter the 6-digit verification code sent to your email address.</p>
			</div>
			
			<form action="" method="POST" id="otpVerificationForm">
				<input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>">
				<input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
				<?php if($demoMode) { ?>
				<input type="hidden" name="demo" value="1">
				<?php } ?>>
				
				<div class="form_group">
					<label for="email_display"><b>📧 Email Address:</b></label>
					<div class="email_display">
						<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>
					</div>
				</div>
				
				<div class="form_group">
					<label for="otp"><b>🔢 Verification Code:</b></label>
					<input type="text" 
						   name="otp" 
						   id="otp" 
						   placeholder="000000" 
						   maxlength="6" 
						   pattern="[0-9]{6}" 
						   title="Please enter a 6-digit number"
						   required 
						   autocomplete="off"
						   style="font-size: 24px; text-align: center; letter-spacing: 8px;">
					<span class="error-message" id="otp-error"></span>
				</div>
				
				<div class="form_group">
					<button type="submit" name="verify_otp" class="btn_success" id="verifyBtn">
						✅ Verify & Create Account
					</button>
				</div>
			</form>
			
			<div class="resend_section">
				<p>Didn't receive the verification code?</p>
				<form action="" method="POST" style="display: inline;">
					<input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>">
					<button type="submit" name="resend_verification" class="btn_warning">
						📧 Resend Verification Email
					</button>
				</form>
			</div>
			
			<div class="verification_help">
				<h4>💡 Troubleshooting Tips:</h4>
				<ul>
					<li>Check your spam/junk folder for the verification email</li>
					<li>Make sure you entered the correct email address</li>
					<li>The verification code expires in 1 hour</li>
					<li>Try clicking the verification link in the email instead</li>
				</ul>
			</div>
		</div>
		
		<?php } else { ?>
		<!-- Manual Email Entry Form -->
		<div class="manual_verification_container">
			<div class="verification_header">
				<h2>📧 Email Verification</h2>
				<p>Enter your email address to verify your account.</p>
			</div>
			
			<form action="" method="POST" id="emailVerificationForm">
				<div class="form_group">
					<label for="email"><b>📧 Email Address:</b></label>
					<input type="email" 
						   name="email" 
						   id="email" 
						   placeholder="Enter your email address" 
						   pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
						   title="Please enter a valid email address"
						   required>
					<span class="error-message" id="email-error"></span>
				</div>
				
				<div class="form_group">
					<button type="submit" name="find_verification" class="btn_primary">
						🔍 Find My Verification
					</button>
				</div>
			</form>
			
			<div class="help_section">
				<h4>📋 How Email Verification Works:</h4>
				<ol>
					<li><strong>Registration:</strong> You filled out the registration form</li>
					<li><strong>Email Sent:</strong> We sent a verification email to your address</li>
					<li><strong>Verification:</strong> Click the link in the email or enter the OTP code</li>
					<li><strong>Account Created:</strong> Your account is created after successful verification</li>
				</ol>
				
				<div class="important_note">
					<strong>⚠️ Important:</strong> Your account is NOT created until you verify your email address. 
					This ensures security and prevents spam accounts.
				</div>
			</div>
		</div>
		<?php } ?>
		
		<div class="back_links">
			<a href="signup_with_verification.php">← Start New Registration</a> |
			<a href="signin.php">Already have an account? Sign In</a>
		</div>
	</div>
</div>

<style>
.success_container {
	text-align: center;
	background: white;
	padding: 40px;
	border-radius: 15px;
	box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.success_icon {
	font-size: 80px;
	margin-bottom: 20px;
}

.success_container h2 {
	color: #27ae60;
	margin-bottom: 20px;
}

.success_actions {
	margin: 30px 0;
}

.success_actions a {
	display: inline-block;
	padding: 15px 30px;
	margin: 10px;
	text-decoration: none;
	border-radius: 8px;
	font-weight: bold;
	transition: all 0.3s ease;
}

.btn_success {
	background: linear-gradient(135deg, #27ae60, #2ecc71);
	color: white;
}

.btn_success:hover {
	background: linear-gradient(135deg, #229954, #27ae60);
	transform: translateY(-2px);
}

.btn_primary {
	background: linear-gradient(135deg, #3498db, #5dade2);
	color: white;
}

.btn_primary:hover {
	background: linear-gradient(135deg, #2980b9, #3498db);
	transform: translateY(-2px);
}

.welcome_info {
	background: #f8f9fa;
	padding: 20px;
	border-radius: 8px;
	margin: 20px 0;
	text-align: left;
}

.welcome_info h3 {
	color: #2c3e50;
	margin-top: 0;
}

.welcome_info ul {
	margin: 15px 0;
	padding-left: 20px;
}

.welcome_info li {
	margin: 8px 0;
	color: #555;
}

.otp_verification_container, .manual_verification_container {
	background: white;
	padding: 40px;
	border-radius: 15px;
	box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.verification_header {
	text-align: center;
	margin-bottom: 30px;
}

.verification_header h2 {
	color: #2c3e50;
	margin-bottom: 10px;
}

.form_group {
	margin-bottom: 25px;
}

.form_group label {
	display: block;
	margin-bottom: 8px;
	color: #2c3e50;
	font-weight: bold;
}

.email_display {
	background: #f8f9fa;
	padding: 12px;
	border: 2px solid #e9ecef;
	border-radius: 8px;
	font-family: 'Courier New', monospace;
	font-weight: bold;
	color: #2c3e50;
}

#otp {
	width: 100%;
	padding: 15px;
	border: 2px solid #ddd;
	border-radius: 8px;
	font-size: 24px !important;
	text-align: center;
	letter-spacing: 8px;
	background: #f8f9fa;
	font-family: 'Courier New', monospace;
}

#otp:focus {
	border-color: #3498db;
	outline: none;
	background: white;
}

#email {
	width: 100%;
	padding: 12px;
	border: 2px solid #ddd;
	border-radius: 8px;
	font-size: 16px;
}

#email:focus {
	border-color: #3498db;
	outline: none;
}

.btn_success, .btn_primary, .btn_warning {
	width: 100%;
	padding: 15px;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: bold;
	cursor: pointer;
	transition: all 0.3s ease;
	margin: 10px 0;
}

.btn_warning {
	background: linear-gradient(135deg, #f39c12, #e67e22);
	color: white;
}

.btn_warning:hover {
	background: linear-gradient(135deg, #e67e22, #d35400);
	transform: translateY(-2px);
}

.resend_section {
	text-align: center;
	margin: 30px 0;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
}

.verification_help, .help_section {
	background: #e8f5e8;
	padding: 20px;
	border-radius: 8px;
	margin: 20px 0;
}

.verification_help h4, .help_section h4 {
	color: #27ae60;
	margin-top: 0;
}

.verification_help ul, .help_section ol {
	margin: 15px 0;
	padding-left: 20px;
}

.verification_help li, .help_section li {
	margin: 8px 0;
	color: #555;
}

.important_note {
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

.error-message {
	color: #e74c3c;
	font-size: 14px;
	margin-top: 5px;
	display: block;
}

/* OTP Input Animation */
#otp.error {
	border-color: #e74c3c;
	animation: shake 0.5s;
}

@keyframes shake {
	0%, 100% { transform: translateX(0); }
	25% { transform: translateX(-5px); }
	75% { transform: translateX(5px); }
}

/* Button States */
.btn_disabled {
	background-color: #bdc3c7 !important;
	color: #7f8c8d !important;
	cursor: not-allowed !important;
	opacity: 0.6;
}

.btn_loading {
	background-color: #f39c12 !important;
	cursor: wait !important;
}

.btn_success {
	background-color: #27ae60 !important;
	cursor: pointer !important;
}

.btn_success:hover:not(.btn_disabled) {
	background-color: #2ecc71 !important;
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* OTP Input Focus State */
#otp:focus {
	border-color: #3498db;
	box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
	outline: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const otpInput = document.getElementById('otp');
	const verifyBtn = document.getElementById('verifyBtn');
	const otpForm = document.getElementById('otpVerificationForm');
	
	if (otpInput) {
		// Auto-focus on OTP input
		otpInput.focus();
		
		// Only allow numbers and limit to 6 digits
		otpInput.addEventListener('input', function(e) {
			// Remove any non-numeric characters
			let value = this.value.replace(/[^0-9]/g, '');
			
			// Limit to 6 digits
			if (value.length > 6) {
				value = value.substring(0, 6);
			}
			
			this.value = value;
			
			// Clear any previous error messages
			document.getElementById('otp-error').textContent = '';
			this.classList.remove('error');
			
			// Enable/disable verify button based on input length
			if (verifyBtn) {
				verifyBtn.disabled = value.length !== 6;
				
				if (value.length === 6) {
					verifyBtn.textContent = '✅ Verify & Create Account';
					verifyBtn.classList.remove('btn_disabled');
					verifyBtn.classList.add('btn_success');
				} else {
					verifyBtn.textContent = '✅ Enter 6 Digits';
					verifyBtn.classList.add('btn_disabled');
					verifyBtn.classList.remove('btn_success');
				}
			}
		});
		
		// Handle keypress events
		otpInput.addEventListener('keypress', function(e) {
			// Only allow numbers
			if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
				e.preventDefault();
			}
			
			// Submit form on Enter key if OTP is complete
			if (e.key === 'Enter' && this.value.length === 6) {
				e.preventDefault();
				if (otpForm && verifyBtn && !verifyBtn.disabled) {
					otpForm.submit();
				}
			}
		});
		
		// Prevent paste of non-numeric content
		otpInput.addEventListener('paste', function(e) {
			e.preventDefault();
			const pastedText = (e.clipboardData || window.clipboardData).getData('text');
			const numericOnly = pastedText.replace(/[^0-9]/g, '').substring(0, 6);
			this.value = numericOnly;
			
			// Trigger input event to update button state
			this.dispatchEvent(new Event('input'));
		});
		
		// Initialize button state
		if (verifyBtn) {
			verifyBtn.disabled = true;
			verifyBtn.textContent = '✅ Enter 6 Digits';
			verifyBtn.classList.add('btn_disabled');
		}
	}
	
	// Form validation and submission
	if (otpForm) {
		otpForm.addEventListener('submit', function(e) {
			if (otpInput) {
				const otp = otpInput.value.trim();
				
				// Validate OTP length
				if (otp.length !== 6) {
					e.preventDefault();
					otpInput.classList.add('error');
					document.getElementById('otp-error').textContent = 'Please enter a 6-digit verification code';
					
					setTimeout(function() {
						otpInput.classList.remove('error');
					}, 3000);
					
					return false;
				}
				
				// Validate OTP format (only numbers)
				if (!/^[0-9]{6}$/.test(otp)) {
					e.preventDefault();
					otpInput.classList.add('error');
					document.getElementById('otp-error').textContent = 'Verification code must contain only numbers';
					
					setTimeout(function() {
						otpInput.classList.remove('error');
					}, 3000);
					
					return false;
				}
				
				// Show loading state
				if (verifyBtn) {
					verifyBtn.textContent = '🔄 Verifying...';
					verifyBtn.disabled = true;
					verifyBtn.classList.add('btn_loading');
				}
				
				// Disable the input to prevent changes during submission
				otpInput.disabled = true;
			}
		});
	}
	
	// Auto-redirect to sign-in page after successful verification
	<?php if($showSuccess) { ?>
	setTimeout(function() {
		window.location.href = 'signin.php';
	}, 5000);
	<?php } ?>
});
</script>

<?php include"inc/footer.php"; ?>
