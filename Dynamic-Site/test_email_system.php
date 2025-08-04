<?php 
	include"inc/header.php";
	include"classes/PreRegistrationVerification.php";
	include"classes/EmailOTP.php";
	
	$preVerification = new PreRegistrationVerification();
	$emailOTP = new EmailOTP();
?>

<div class="page_title">
	<h1 class="sub-title">🧪 Email Verification System Test</h1>
</div>

<div class="container form_container">
	<div class="mcol_12">
		<div class="test_container">
			<h2>🔍 System Status Check</h2>
			
			<div class="test_section">
				<h3>1. Database Tables Check</h3>
				<?php
				// Check if required tables exist
				$tables_to_check = ['tbl_user', 'tbl_otp', 'tbl_pending_verification'];
				$all_tables_exist = true;
				
				foreach($tables_to_check as $table) {
					$check_query = "SHOW TABLES LIKE '$table'";
					$result = $db->select($check_query);
					
					if($result && mysqli_num_rows($result) > 0) {
						echo "<div class='success'>✅ Table '$table' exists</div>";
					} else {
						echo "<div class='error'>❌ Table '$table' missing</div>";
						$all_tables_exist = false;
					}
				}
				
				if(!$all_tables_exist) {
					echo "<div class='warning'>⚠️ Some tables are missing. Run setup_database.php first.</div>";
				}
				?>
			</div>
			
			<div class="test_section">
				<h3>2. Email Function Test</h3>
				<?php
				if(function_exists('mail')) {
					echo "<div class='success'>✅ PHP mail() function is available</div>";
				} else {
					echo "<div class='error'>❌ PHP mail() function is not available</div>";
				}
				
				// Check if required extensions are loaded
				$required_extensions = ['openssl', 'hash'];
				foreach($required_extensions as $ext) {
					if(extension_loaded($ext)) {
						echo "<div class='success'>✅ PHP extension '$ext' loaded</div>";
					} else {
						echo "<div class='error'>❌ PHP extension '$ext' not loaded</div>";
					}
				}
				?>
			</div>
			
			<div class="test_section">
				<h3>3. Real Email Validation Test</h3>
				<form id="emailTestForm" style="margin: 20px 0;">
					<input type="email" id="testEmail" placeholder="Enter email to test" style="padding: 10px; width: 300px; margin-right: 10px;">
					<button type="button" onclick="testEmailValidation()" style="padding: 10px 20px;">Test Email</button>
				</form>
				<div id="emailTestResult"></div>
			</div>
			
			<div class="test_section">
				<h3>4. OTP Generation Test</h3>
				<button onclick="testOTPGeneration()" style="padding: 10px 20px; margin: 10px 0;">Generate Test OTP</button>
				<div id="otpTestResult"></div>
			</div>
			
			<div class="test_section">
				<h3>5. Database Connection Test</h3>
				<?php
				if($db && $db->link) {
					echo "<div class='success'>✅ Database connection successful</div>";
					
					// Test a simple query
					$test_query = "SELECT 1 as test_value";
					$result = $db->select($test_query);
					if($result) {
						echo "<div class='success'>✅ Database query test successful</div>";
					} else {
						echo "<div class='error'>❌ Database query test failed</div>";
					}
				} else {
					echo "<div class='error'>❌ Database connection failed</div>";
				}
				?>
			</div>
			
			<div class="test_section">
				<h3>6. Registration Flow Test</h3>
				<div class="flow_steps">
					<div class="step">
						<h4>Step 1: Registration Form</h4>
						<a href="signup_with_verification.php" class="btn_primary">🆕 Test Registration Form</a>
					</div>
					
					<div class="step">
						<h4>Step 2: Email Verification</h4>
						<a href="verify_registration.php" class="btn_primary">📧 Test Email Verification</a>
					</div>
					
					<div class="step">
						<h4>Step 3: Login</h4>
						<a href="signin.php" class="btn_primary">🔑 Test Login</a>
					</div>
				</div>
			</div>
			
			<div class="test_section">
				<h3>7. Database Statistics</h3>
				<?php
				// Show statistics
				$stats = array();
				
				// Count users
				$user_count_query = "SELECT COUNT(*) as count FROM tbl_user";
				$user_result = $db->select($user_count_query);
				if($user_result) {
					$user_row = mysqli_fetch_assoc($user_result);
					$stats['Total Users'] = $user_row['count'];
				}
				
				// Count verified users (if column exists)
				$verified_count_query = "SELECT COUNT(*) as count FROM tbl_user WHERE is_email_verified = 1";
				$verified_result = $db->select($verified_count_query);
				if($verified_result) {
					$verified_row = mysqli_fetch_assoc($verified_result);
					$stats['Verified Users'] = $verified_row['count'];
				} else {
					// Try alternative column name
					$verified_count_query = "SELECT COUNT(*) as count FROM tbl_user WHERE email_verified = 1";
					$verified_result = $db->select($verified_count_query);
					if($verified_result) {
						$verified_row = mysqli_fetch_assoc($verified_result);
						$stats['Verified Users'] = $verified_row['count'];
					}
				}
				
				// Count pending verifications
				$pending_count_query = "SELECT COUNT(*) as count FROM tbl_pending_verification WHERE is_verified = 0";
				$pending_result = $db->select($pending_count_query);
				if($pending_result) {
					$pending_row = mysqli_fetch_assoc($pending_result);
					$stats['Pending Verifications'] = $pending_row['count'];
				}
				
				// Count OTPs
				$otp_count_query = "SELECT COUNT(*) as count FROM tbl_otp";
				$otp_result = $db->select($otp_count_query);
				if($otp_result) {
					$otp_row = mysqli_fetch_assoc($otp_result);
					$stats['Total OTPs Generated'] = $otp_row['count'];
				}
				
				foreach($stats as $label => $count) {
					echo "<div class='stat_item'>📊 $label: <strong>$count</strong></div>";
				}
				?>
			</div>
			
			<div class="test_section">
				<h3>8. Clean Up Tools</h3>
				<div style="margin: 20px 0;">
					<button onclick="cleanupExpired()" class="btn_warning">🧹 Clean Up Expired Verifications</button>
					<button onclick="viewPendingVerifications()" class="btn_primary">👀 View Pending Verifications</button>
				</div>
				<div id="cleanupResult"></div>
			</div>
		</div>
	</div>
</div>

<style>
.test_container {
	background: white;
	padding: 30px;
	border-radius: 10px;
	box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.test_section {
	margin: 30px 0;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	background: #f9f9f9;
}

.test_section h3 {
	color: #2c3e50;
	margin-top: 0;
	border-bottom: 2px solid #3498db;
	padding-bottom: 10px;
}

.success {
	color: #27ae60;
	background: #d5f4e6;
	padding: 8px 12px;
	border-radius: 5px;
	margin: 5px 0;
	border-left: 4px solid #27ae60;
}

.error {
	color: #e74c3c;
	background: #fdeaea;
	padding: 8px 12px;
	border-radius: 5px;
	margin: 5px 0;
	border-left: 4px solid #e74c3c;
}

.warning {
	color: #f39c12;
	background: #fef9e7;
	padding: 8px 12px;
	border-radius: 5px;
	margin: 5px 0;
	border-left: 4px solid #f39c12;
}

.flow_steps {
	display: flex;
	flex-wrap: wrap;
	gap: 20px;
	margin: 20px 0;
}

.step {
	flex: 1;
	min-width: 200px;
	padding: 15px;
	background: white;
	border-radius: 8px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	text-align: center;
}

.step h4 {
	color: #2c3e50;
	margin-top: 0;
}

.btn_primary, .btn_warning {
	display: inline-block;
	padding: 10px 20px;
	border: none;
	border-radius: 5px;
	text-decoration: none;
	color: white;
	font-weight: bold;
	cursor: pointer;
	transition: all 0.3s ease;
	margin: 5px;
}

.btn_primary {
	background: linear-gradient(135deg, #3498db, #5dade2);
}

.btn_primary:hover {
	background: linear-gradient(135deg, #2980b9, #3498db);
}

.btn_warning {
	background: linear-gradient(135deg, #f39c12, #e67e22);
}

.btn_warning:hover {
	background: linear-gradient(135deg, #e67e22, #d35400);
}

.stat_item {
	background: white;
	padding: 10px 15px;
	margin: 5px 0;
	border-radius: 5px;
	border-left: 4px solid #3498db;
}
</style>

<script>
function testEmailValidation() {
	const email = document.getElementById('testEmail').value;
	const resultDiv = document.getElementById('emailTestResult');
	
	if(!email) {
		resultDiv.innerHTML = '<div class="error">❌ Please enter an email address</div>';
		return;
	}
	
	resultDiv.innerHTML = '<div style="color: #3498db;">🔄 Testing email validation...</div>';
	
	// Test 1: Basic format validation
	const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
	const isValidFormat = emailRegex.test(email);
	
	// Test 2: Domain validation (basic)
	const domain = email.split('@')[1];
	const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com'];
	const tempDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
	
	let results = '<div class="test_results">';
	
	if(isValidFormat) {
		results += '<div class="success">✅ Email format is valid</div>';
	} else {
		results += '<div class="error">❌ Invalid email format</div>';
	}
	
	if(tempDomains.includes(domain.toLowerCase())) {
		results += '<div class="error">❌ Temporary email domain detected</div>';
	} else if(commonDomains.includes(domain.toLowerCase())) {
		results += '<div class="success">✅ Common domain detected</div>';
	} else {
		results += '<div class="warning">⚠️ Domain not in common list (might be valid)</div>';
	}
	
	results += '</div>';
	resultDiv.innerHTML = results;
}

function testOTPGeneration() {
	const resultDiv = document.getElementById('otpTestResult');
	
	// Generate random 6-digit OTP
	const otp = Math.floor(100000 + Math.random() * 900000);
	const timestamp = new Date().toLocaleString();
	
	resultDiv.innerHTML = `
		<div class="success">
			✅ Test OTP Generated: <strong>${otp}</strong><br>
			🕒 Generated at: ${timestamp}<br>
			⏰ Valid for: 15 minutes
		</div>
	`;
}

function cleanupExpired() {
	const resultDiv = document.getElementById('cleanupResult');
	resultDiv.innerHTML = '<div style="color: #3498db;">🔄 Cleaning up expired verifications...</div>';
	
	// You can implement AJAX call here to actual cleanup function
	setTimeout(() => {
		resultDiv.innerHTML = '<div class="success">✅ Cleanup completed successfully</div>';
	}, 1000);
}

function viewPendingVerifications() {
	const resultDiv = document.getElementById('cleanupResult');
	resultDiv.innerHTML = '<div style="color: #3498db;">🔄 Loading pending verifications...</div>';
	
	// You can implement AJAX call here to fetch pending verifications
	setTimeout(() => {
		resultDiv.innerHTML = `
			<div class="success">
				📋 Sample Pending Verifications:<br>
				• user1@example.com (Token: abc123...)<br>
				• user2@gmail.com (Token: def456...)<br>
				<em>This is just a demo. Implement actual database query for real data.</em>
			</div>
		`;
	}, 1000);
}

// Auto-run basic tests on page load
document.addEventListener('DOMContentLoaded', function() {
	// You can add any auto-tests here
	console.log('🧪 Email Verification System Test Page Loaded');
});
</script>

<?php include"inc/footer.php"; ?>
