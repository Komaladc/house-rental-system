<?php
	include "inc/header.php";
	include "classes/PreRegistrationVerification.php";
	include "classes/EmailOTP.php";
	
	$preVerification = new PreRegistrationVerification();
	$emailOTP = new EmailOTP();
	
	echo "<h2>🔍 Complete OTP Verification Diagnostic</h2>";
	
	if ($_POST) {
		$testEmail = $_POST['test_email'];
		$testOTP = $_POST['test_otp'];
		
		echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
		echo "<h3>🕐 Current Time Info</h3>";
		echo "<strong>Server Timezone:</strong> " . date_default_timezone_get() . "<br>";
		echo "<strong>Current Time:</strong> " . date('Y-m-d H:i:s T') . "<br>";
		echo "<hr>";
		
		echo "<h3>📋 Step-by-Step Diagnosis</h3>";
		
		// Step 1: Check pending verification
		echo "<h4>Step 1: Pending Verification Check</h4>";
		$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
		$pendingResult = $db->select($pendingQuery);
		
		if ($pendingResult && mysqli_num_rows($pendingResult) > 0) {
			$pendingData = mysqli_fetch_assoc($pendingResult);
			echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
			echo "✅ Found pending verification<br>";
			echo "<strong>Email:</strong> " . $pendingData['email'] . "<br>";
			echo "<strong>Token:</strong> " . substr($pendingData['verification_token'], 0, 15) . "...<br>";
			echo "<strong>Created:</strong> " . $pendingData['created_at'] . "<br>";
			echo "<strong>Expires:</strong> " . $pendingData['expires_at'] . "<br>";
			echo "<strong>Is Verified:</strong> " . ($pendingData['is_verified'] ? 'Yes' : 'No') . "<br>";
			echo "</div>";
			
			// Step 2: Check OTP records
			echo "<h4>Step 2: OTP Records Analysis</h4>";
			$otpQuery = "SELECT *, 
			            (expires_at > NOW()) as not_expired,
			            TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_remaining
			            FROM tbl_otp 
			            WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' 
			            AND purpose = 'email_verification' 
			            ORDER BY created_at DESC";
			$otpResult = $db->select($otpQuery);
			
			if ($otpResult && mysqli_num_rows($otpResult) > 0) {
				echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white; margin: 10px 0;'>";
				echo "<tr style='background: #f8f9fa;'><th>OTP</th><th>Created</th><th>Expires</th><th>Used</th><th>Not Expired</th><th>Minutes Left</th><th>Status</th></tr>";
				
				while ($otpRow = mysqli_fetch_assoc($otpResult)) {
					$status = "";
					$bgColor = "#f8d7da"; // red
					
					if ($otpRow['not_expired'] && !$otpRow['is_used']) {
						$status = "✅ VALID";
						$bgColor = "#d4edda"; // green
					} elseif ($otpRow['is_used']) {
						$status = "⚠️ USED";
						$bgColor = "#fff3cd"; // yellow
					} else {
						$status = "❌ EXPIRED";
						$bgColor = "#f8d7da"; // red
					}
					
					echo "<tr style='background: $bgColor;'>";
					echo "<td><strong>" . $otpRow['otp'] . "</strong></td>";
					echo "<td>" . $otpRow['created_at'] . "</td>";
					echo "<td>" . $otpRow['expires_at'] . "</td>";
					echo "<td>" . ($otpRow['is_used'] ? 'Yes' : 'No') . "</td>";
					echo "<td>" . ($otpRow['not_expired'] ? 'Yes' : 'No') . "</td>";
					echo "<td>" . $otpRow['minutes_remaining'] . "</td>";
					echo "<td>" . $status . "</td>";
					echo "</tr>";
				}
				echo "</table>";
			} else {
				echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>❌ No OTP records found</div>";
			}
			
			// Step 3: Test direct OTP verification
			echo "<h4>Step 3: Direct OTP Verification Test</h4>";
			if ($testOTP) {
				// First, let's see what the verifyOTP method finds
				$directQuery = "SELECT *, 
				               (expires_at > NOW()) as not_expired,
				               (is_used = 0) as not_used,
				               TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_remaining
				               FROM tbl_otp 
				               WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' 
				               AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "' 
				               AND purpose = 'email_verification'";
				
				$directResult = $db->select($directQuery);
				
				if ($directResult && mysqli_num_rows($directResult) > 0) {
					$directOTP = mysqli_fetch_assoc($directResult);
					echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
					echo "<strong>🔍 Direct OTP Lookup Results:</strong><br>";
					echo "OTP: " . $directOTP['otp'] . "<br>";
					echo "Email: " . $directOTP['email'] . "<br>";
					echo "Purpose: " . $directOTP['purpose'] . "<br>";
					echo "Created: " . $directOTP['created_at'] . "<br>";
					echo "Expires: " . $directOTP['expires_at'] . "<br>";
					echo "Is Used: " . ($directOTP['is_used'] ? 'Yes' : 'No') . "<br>";
					echo "Not Expired: " . ($directOTP['not_expired'] ? 'Yes' : 'No') . "<br>";
					echo "Not Used: " . ($directOTP['not_used'] ? 'Yes' : 'No') . "<br>";
					echo "Minutes Remaining: " . $directOTP['minutes_remaining'] . "<br>";
					
					$canVerify = $directOTP['not_expired'] && $directOTP['not_used'];
					echo "<strong>Should Work: " . ($canVerify ? '✅ YES' : '❌ NO') . "</strong><br>";
					echo "</div>";
				} else {
					echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
					echo "❌ No matching OTP found for: Email='" . $testEmail . "', OTP='" . $testOTP . "', Purpose='email_verification'";
					echo "</div>";
				}
				
				// Test the actual verifyOTP method
				echo "<h4>Step 4: EmailOTP->verifyOTP() Method Test</h4>";
				$verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'email_verification');
				echo "<div style='background: " . ($verifyResult ? '#d4edda' : '#f8d7da') . "; padding: 10px; border-radius: 5px;'>";
				echo ($verifyResult ? "✅ EmailOTP->verifyOTP() returned TRUE" : "❌ EmailOTP->verifyOTP() returned FALSE");
				echo "</div>";
				
				// Test the full verification process
				echo "<h4>Step 5: Full Verification Process Test</h4>";
				// Reset the OTP if it was marked as used
				$resetQuery = "UPDATE tbl_otp SET is_used = 0 WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND otp = '" . mysqli_real_escape_string($db->link, $testOTP) . "' AND purpose = 'email_verification'";
				$db->update($resetQuery);
				
				$fullResult = $preVerification->verifyAndCreateAccount($testEmail, $pendingData['verification_token'], $testOTP);
				echo "<div style='background: " . ($fullResult['success'] ? '#d4edda' : '#f8d7da') . "; padding: 10px; border-radius: 5px;'>";
				echo "<strong>Full Verification Result:</strong><br>";
				echo "Success: " . ($fullResult['success'] ? 'YES' : 'NO') . "<br>";
				echo "Message: " . strip_tags($fullResult['message']) . "<br>";
				echo "</div>";
			}
		} else {
			echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>❌ No pending verification found for this email</div>";
		}
		
		echo "</div>";
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	table { margin: 15px 0; font-size: 12px; }
	th, td { padding: 6px 8px; text-align: left; }
	form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
	input[type="email"], input[type="text"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
	button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
	h4 { color: #2c3e50; margin-top: 20px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
</style>

<form method="POST">
	<h3>🧪 Complete OTP Diagnostic</h3>
	<label>Email Address:</label><br>
	<input type="email" name="test_email" value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : 'bistakaran298@gmail.com'; ?>" required><br><br>
	
	<label>OTP Code (the exact code you received):</label><br>
	<input type="text" name="test_otp" placeholder="Enter 6-digit OTP" pattern="[0-9]{6}" maxlength="6" value="<?php echo isset($_POST['test_otp']) ? htmlspecialchars($_POST['test_otp']) : ''; ?>"><br><br>
	
	<button type="submit">🔍 Run Complete Diagnosis</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
	<h4>📋 Instructions:</h4>
	<ol>
		<li>Enter the email you used for registration</li>
		<li>Enter the EXACT OTP code from your email (copy/paste recommended)</li>
		<li>Click "Run Complete Diagnosis"</li>
		<li>This will show exactly where the verification is failing</li>
	</ol>
	<p><strong>This tool will reveal the exact cause of the "Invalid verification code" error.</strong></p>
</div>

<?php include "inc/footer.php"; ?>
