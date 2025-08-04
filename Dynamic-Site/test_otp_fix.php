<?php
	include "inc/header.php";
	include "classes/EmailOTP.php";
	include "classes/PreRegistrationVerification.php";
	
	$emailOTP = new EmailOTP();
	$preVerification = new PreRegistrationVerification();
	
	echo "<h2>OTP Verification Test</h2>";
	
	// Test data - replace with actual email from your tests
	$testEmail = "your_test_email@gmail.com"; // Replace with your email
	
	if ($_POST && isset($_POST['test_email'])) {
		$testEmail = $_POST['test_email'];
		
		echo "<h3>📧 Testing OTP for: " . htmlspecialchars($testEmail) . "</h3>";
		
		// Get the latest pending verification
		$query = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
		$result = $db->select($query);
		
		if ($result && mysqli_num_rows($result) > 0) {
			$pendingData = mysqli_fetch_assoc($result);
			echo "<p><strong>✅ Found pending verification:</strong></p>";
			echo "<pre>";
			echo "Email: " . $pendingData['email'] . "\n";
			echo "Token: " . substr($pendingData['verification_token'], 0, 10) . "...\n";
			echo "Stored OTP: " . $pendingData['otp'] . "\n";
			echo "Created: " . $pendingData['created_at'] . "\n";
			echo "Expires: " . $pendingData['expires_at'] . "\n";
			echo "</pre>";
			
			// Check OTP in tbl_otp
			$otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
			$otpResult = $db->select($otpQuery);
			
			if ($otpResult && mysqli_num_rows($otpResult) > 0) {
				$otpData = mysqli_fetch_assoc($otpResult);
				echo "<p><strong>✅ Found OTP record:</strong></p>";
				echo "<pre>";
				echo "OTP Code: " . $otpData['otp'] . "\n";
				echo "Is Used: " . ($otpData['is_used'] ? 'Yes' : 'No') . "\n";
				echo "Expires: " . $otpData['expires_at'] . "\n";
				echo "</pre>";
				
				if ($_POST && isset($_POST['test_otp'])) {
					$testOTP = $_POST['test_otp'];
					echo "<h4>🔍 Testing OTP: " . htmlspecialchars($testOTP) . "</h4>";
					
					// Test the verification
					$verificationResult = $preVerification->verifyAndCreateAccount($testEmail, $pendingData['verification_token'], $testOTP);
					
					echo "<div style='padding: 10px; margin: 10px 0; border: 1px solid #ccc; background: " . ($verificationResult['success'] ? '#d4edda' : '#f8d7da') . ";'>";
					echo $verificationResult['message'];
					echo "</div>";
				}
			} else {
				echo "<p><strong>❌ No OTP record found in tbl_otp</strong></p>";
			}
		} else {
			echo "<p><strong>❌ No pending verification found</strong></p>";
		}
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; }
	pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
	form { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
	input[type="email"], input[type="text"] { width: 300px; padding: 8px; margin: 5px 0; }
	button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
	button:hover { background: #005a87; }
</style>

<form method="POST">
	<h3>Test OTP Verification</h3>
	<label>Email:</label><br>
	<input type="email" name="test_email" value="<?php echo htmlspecialchars($testEmail); ?>" required><br><br>
	
	<label>OTP Code (enter the code you received):</label><br>
	<input type="text" name="test_otp" placeholder="Enter 6-digit OTP" pattern="[0-9]{6}" maxlength="6"><br><br>
	
	<button type="submit">Test Verification</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007cba;">
	<h4>📋 Instructions:</h4>
	<ol>
		<li>Enter the email address you used for registration</li>
		<li>Check the database records and OTP status</li>
		<li>Enter the 6-digit OTP code you received in your email</li>
		<li>Click "Test Verification" to see if the process works</li>
	</ol>
	<p><strong>Note:</strong> This will actually create the user account if verification succeeds!</p>
</div>

<?php include "inc/footer.php"; ?>
