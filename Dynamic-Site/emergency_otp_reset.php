<?php
	include "inc/header.php";
	
	echo "<h2>🛠️ Emergency OTP Reset Tool</h2>";
	
	if ($_POST && isset($_POST['reset_email'])) {
		$email = $_POST['reset_email'];
		$newOTP = sprintf("%06d", mt_rand(100000, 999999));
		
		echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
		echo "<h3>🔄 Resetting OTP for: " . htmlspecialchars($email) . "</h3>";
		
		// Delete old OTPs
		$deleteQuery = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND purpose = 'email_verification'";
		$deleteResult = $db->delete($deleteQuery);
		echo "Old OTPs deleted: " . ($deleteResult ? "✅ Success" : "❌ Failed") . "<br>";
		
		// Create new OTP with current Nepal time
		$currentTime = date('Y-m-d H:i:s');
		$expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes')); // 30 minutes for testing
		
		$insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
		               VALUES ('" . mysqli_real_escape_string($db->link, $email) . "', 
		                      '$newOTP', 
		                      'email_verification', 
		                      '$currentTime', 
		                      '$expiresAt', 
		                      0)";
		
		$insertResult = $db->insert($insertQuery);
		
		if ($insertResult) {
			echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
			echo "<h4>✅ New OTP Created Successfully!</h4>";
			echo "<strong>New OTP Code:</strong> <span style='font-size: 24px; color: #dc3545; font-weight: bold;'>$newOTP</span><br>";
			echo "<strong>Created:</strong> $currentTime<br>";
			echo "<strong>Expires:</strong> $expiresAt<br>";
			echo "<strong>Valid for:</strong> 30 minutes<br>";
			echo "</div>";
			
			echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
			echo "<h4>📋 Next Steps:</h4>";
			echo "<ol>";
			echo "<li>Copy the OTP code above: <strong>$newOTP</strong></li>";
			echo "<li>Go to the signup form OTP verification page</li>";
			echo "<li>Enter this new OTP code</li>";
			echo "<li>Click 'Verify & Create Account'</li>";
			echo "</ol>";
			echo "</div>";
		} else {
			echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Failed to create new OTP</div>";
		}
		echo "</div>";
	}
	
	if ($_POST && isset($_POST['check_email'])) {
		$email = $_POST['check_email'];
		
		echo "<h3>📊 Current Status for: " . htmlspecialchars($email) . "</h3>";
		
		// Check pending verification
		$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC LIMIT 1";
		$pendingResult = $db->select($pendingQuery);
		
		if ($pendingResult && mysqli_num_rows($pendingResult) > 0) {
			$pending = mysqli_fetch_assoc($pendingResult);
			echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
			echo "<strong>✅ Pending Verification Found</strong><br>";
			echo "Created: " . $pending['created_at'] . "<br>";
			echo "Expires: " . $pending['expires_at'] . "<br>";
			echo "Is Verified: " . ($pending['is_verified'] ? 'Yes' : 'No') . "<br>";
			echo "</div>";
		} else {
			echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>❌ No pending verification found</div>";
		}
		
		// Check OTP records
		$otpQuery = "SELECT *, 
		            (expires_at > NOW()) as is_valid,
		            TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_left
		            FROM tbl_otp 
		            WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' 
		            AND purpose = 'email_verification' 
		            ORDER BY created_at DESC LIMIT 3";
		$otpResult = $db->select($otpQuery);
		
		if ($otpResult && mysqli_num_rows($otpResult) > 0) {
			echo "<h4>📱 OTP Records:</h4>";
			echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white;'>";
			echo "<tr style='background: #f8f9fa;'><th>OTP</th><th>Created</th><th>Expires</th><th>Used</th><th>Valid</th><th>Minutes Left</th></tr>";
			
			while ($otp = mysqli_fetch_assoc($otpResult)) {
				$bgColor = $otp['is_valid'] && !$otp['is_used'] ? '#d4edda' : '#f8d7da';
				echo "<tr style='background: $bgColor;'>";
				echo "<td><strong>" . $otp['otp'] . "</strong></td>";
				echo "<td>" . $otp['created_at'] . "</td>";
				echo "<td>" . $otp['expires_at'] . "</td>";
				echo "<td>" . ($otp['is_used'] ? 'Yes' : 'No') . "</td>";
				echo "<td>" . ($otp['is_valid'] ? 'Yes' : 'No') . "</td>";
				echo "<td>" . $otp['minutes_left'] . "</td>";
				echo "</tr>";
			}
			echo "</table>";
		} else {
			echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>❌ No OTP records found</div>";
		}
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
	input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
	button { padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
	button.check { background: #007cba; }
	button:hover { opacity: 0.9; }
	table { margin: 15px 0; font-size: 14px; }
	th, td { padding: 8px 12px; text-align: left; }
</style>

<div style="background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
	<h3>⚠️ Emergency OTP Reset Tool</h3>
	<p>Use this tool if OTP verification keeps failing. This will create a fresh OTP with extended validity.</p>
</div>

<form method="POST">
	<h4>🔄 Reset OTP</h4>
	<label>Email Address:</label><br>
	<input type="email" name="reset_email" value="bistakaran298@gmail.com" required><br><br>
	<button type="submit">🔄 Create New OTP</button>
</form>

<form method="POST">
	<h4>📊 Check Current Status</h4>
	<label>Email Address:</label><br>
	<input type="email" name="check_email" value="bistakaran298@gmail.com" required><br><br>
	<button type="submit" class="check">📊 Check Status</button>
</form>

<div style="background: #e7f3ff; padding: 15px; margin: 15px 0; border-radius: 5px;">
	<h4>📋 How to Use:</h4>
	<ol>
		<li><strong>Check Status First:</strong> See current OTP records</li>
		<li><strong>Reset OTP:</strong> Create a fresh 30-minute OTP</li>
		<li><strong>Use New OTP:</strong> Go to signup form and use the new code</li>
		<li><strong>Success:</strong> Account should be created successfully</li>
	</ol>
</div>

<?php include "inc/footer.php"; ?>
