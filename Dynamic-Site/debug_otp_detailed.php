<?php
	include "inc/header.php";
	include "classes/EmailOTP.php";
	include "classes/PreRegistrationVerification.php";
	
	$emailOTP = new EmailOTP();
	$preVerification = new PreRegistrationVerification();
	
	echo "<h2>🔍 Detailed OTP Verification Debug</h2>";
	
	if ($_POST) {
		$testEmail = $_POST['test_email'];
		$testOTP = $_POST['test_otp'];
		
		echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
		echo "<h3>📧 Testing Email: " . htmlspecialchars($testEmail) . "</h3>";
		echo "<h3>🔢 Testing OTP: " . htmlspecialchars($testOTP) . "</h3>";
		
		// Step 1: Check pending verification
		echo "<h4>Step 1: Check Pending Verification</h4>";
		$query = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
		$result = $db->select($query);
		
		if ($result && mysqli_num_rows($result) > 0) {
			$pendingData = mysqli_fetch_assoc($result);
			echo "<pre style='background: #d4edda; padding: 10px;'>";
			echo "✅ Found pending verification:\n";
			echo "Email: " . $pendingData['email'] . "\n";
			echo "Token: " . substr($pendingData['verification_token'], 0, 20) . "...\n";
			echo "Stored OTP: " . $pendingData['otp'] . "\n";
			echo "Created: " . $pendingData['created_at'] . "\n";
			echo "Expires: " . $pendingData['expires_at'] . "\n";
			echo "Is Verified: " . ($pendingData['is_verified'] ? 'Yes' : 'No') . "\n";
			echo "</pre>";
			
			// Step 2: Check OTP table
			echo "<h4>Step 2: Check OTP Table</h4>";
			$otpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 3";
			$otpResult = $db->select($otpQuery);
			
			if ($otpResult && mysqli_num_rows($otpResult) > 0) {
				echo "<pre style='background: #d4edda; padding: 10px;'>";
				echo "✅ Found OTP records:\n";
				while ($otpData = mysqli_fetch_assoc($otpResult)) {
					echo "OTP: " . $otpData['otp'] . " | Is Used: " . ($otpData['is_used'] ? 'Yes' : 'No') . " | Expires: " . $otpData['expires_at'] . " | Created: " . $otpData['created_at'] . "\n";
				}
				echo "</pre>";
			} else {
				echo "<pre style='background: #f8d7da; padding: 10px;'>❌ No OTP records found</pre>";
			}
			
			// Step 3: Test OTP verification directly with detailed analysis
			echo "<h4>Step 3: Detailed OTP Analysis</h4>";
			
			// Get the latest OTP from database
			$latestOtpQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' AND is_used = 0 ORDER BY created_at DESC LIMIT 1";
			$latestOtpResult = $db->select($latestOtpQuery);
			
			if ($latestOtpResult && mysqli_num_rows($latestOtpResult) > 0) {
				$latestOtp = mysqli_fetch_assoc($latestOtpResult);
				$dbOtp = $latestOtp['otp'];
				
				echo "<pre style='background: #fff3cd; padding: 10px;'>";
				echo "🔍 Detailed OTP Comparison:\n";
				echo "Input OTP: '" . $testOTP . "' (length: " . strlen($testOTP) . ")\n";
				echo "DB OTP:    '" . $dbOtp . "' (length: " . strlen($dbOtp) . ")\n";
				echo "Input OTP bytes: " . bin2hex($testOTP) . "\n";
				echo "DB OTP bytes:    " . bin2hex($dbOtp) . "\n";
				echo "Are they equal? " . ($testOTP === $dbOtp ? 'YES' : 'NO') . "\n";
				echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
				echo "OTP Expires:  " . $latestOtp['expires_at'] . "\n";
				echo "Is Expired? " . (date('Y-m-d H:i:s') > $latestOtp['expires_at'] ? 'YES' : 'NO') . "\n";
				echo "Is Used? " . ($latestOtp['is_used'] ? 'YES' : 'NO') . "\n";
				echo "</pre>";
			}
			
			$otpVerified = $emailOTP->verifyOTP($testEmail, $testOTP, 'email_verification');
			echo "<pre style='background: " . ($otpVerified ? '#d4edda' : '#f8d7da') . "; padding: 10px;'>";
			echo ($otpVerified ? "✅ EmailOTP->verifyOTP() returned TRUE" : "❌ EmailOTP->verifyOTP() returned FALSE");
			echo "</pre>";
			
			// Step 4: Check OTP table after verification attempt
			echo "<h4>Step 4: Check OTP Table After Verification</h4>";
			$otpAfterQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
			$otpAfterResult = $db->select($otpAfterQuery);
			
			if ($otpAfterResult && mysqli_num_rows($otpAfterResult) > 0) {
				$otpAfterData = mysqli_fetch_assoc($otpAfterResult);
				echo "<pre style='background: #fff3cd; padding: 10px;'>";
				echo "OTP Status After Verification:\n";
				echo "OTP: " . $otpAfterData['otp'] . "\n";
				echo "Is Used: " . ($otpAfterData['is_used'] ? 'Yes' : 'No') . "\n";
				echo "Expires: " . $otpAfterData['expires_at'] . "\n";
				echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
				echo "</pre>";
			}
			
			// Step 5: Test full verification process
			echo "<h4>Step 5: Test Full Verification Process</h4>";
			// Reset OTP for testing
			$resetQuery = "UPDATE tbl_otp SET is_used = 0 WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "' AND purpose = 'email_verification'";
			$db->update($resetQuery);
			
			$accountResult = $preVerification->verifyAndCreateAccount($testEmail, $pendingData['verification_token'], $testOTP);
			echo "<pre style='background: " . ($accountResult['success'] ? '#d4edda' : '#f8d7da') . "; padding: 10px;'>";
			echo ($accountResult['success'] ? "✅ Full verification SUCCESS" : "❌ Full verification FAILED") . "\n";
			echo "Message: " . strip_tags($accountResult['message']) . "\n";
			echo "</pre>";
			
		} else {
			echo "<pre style='background: #f8d7da; padding: 10px;'>❌ No pending verification found</pre>";
		}
		
		echo "</div>";
		
		// Additional debugging - check current time vs expiry
		echo "<div style='background: #e7f3ff; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba;'>";
		echo "<h4>🕒 Time Debugging</h4>";
		echo "<strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
		echo "<strong>Current Timezone:</strong> " . date_default_timezone_get() . "<br>";
		echo "</div>";
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	pre { border-radius: 5px; white-space: pre-wrap; }
	form { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background: white; }
	input[type="email"], input[type="text"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
	button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
	button:hover { background: #005a87; }
	h4 { color: #2c3e50; margin-top: 20px; }
</style>

<form method="POST">
	<h3>🧪 Test OTP Verification</h3>
	<label>Email Address:</label><br>
	<input type="email" name="test_email" value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : 'bistakaran298@gmail.com'; ?>" required><br><br>
	
	<label>OTP Code:</label><br>
	<input type="text" name="test_otp" placeholder="Enter 6-digit OTP" pattern="[0-9]{6}" maxlength="6" required><br><br>
	
	<button type="submit">🔍 Debug OTP Verification</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
	<h4>📋 How to Use This Debug Tool:</h4>
	<ol>
		<li>Enter the email address you used for registration</li>
		<li>Enter the exact OTP code you received</li>
		<li>Click "Debug OTP Verification" to see detailed analysis</li>
		<li>This will show you exactly where the verification is failing</li>
	</ol>
	<p><strong>⚠️ Note:</strong> This tool will reset the OTP for testing purposes.</p>
</div>

<?php include "inc/footer.php"; ?>
