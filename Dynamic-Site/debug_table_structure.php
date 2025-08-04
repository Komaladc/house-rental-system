<?php
	include "inc/header.php";
	
	echo "<h2>🗄️ Database Table Structure Check</h2>";
	
	// Check tbl_otp structure
	echo "<h3>📋 tbl_otp Table Structure</h3>";
	$otpStructureQuery = "DESCRIBE tbl_otp";
	$otpStructure = $db->select($otpStructureQuery);
	
	if ($otpStructure && mysqli_num_rows($otpStructure) > 0) {
		echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white; margin: 10px 0;'>";
		echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
		
		while ($column = mysqli_fetch_assoc($otpStructure)) {
			echo "<tr>";
			echo "<td><strong>" . $column['Field'] . "</strong></td>";
			echo "<td>" . $column['Type'] . "</td>";
			echo "<td>" . $column['Null'] . "</td>";
			echo "<td>" . $column['Key'] . "</td>";
			echo "<td>" . $column['Default'] . "</td>";
			echo "<td>" . $column['Extra'] . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>❌ tbl_otp table not found or empty</div>";
	}
	
	// Test manual OTP insertion
	echo "<h3>🧪 Test Manual OTP Insertion</h3>";
	
	if ($_POST && isset($_POST['test_email'])) {
		$testEmail = $_POST['test_email'];
		$testOTP = sprintf("%06d", mt_rand(100000, 999999));
		
		echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
		echo "<h4>Testing Manual OTP Creation</h4>";
		echo "<strong>Email:</strong> $testEmail<br>";
		echo "<strong>OTP:</strong> $testOTP<br>";
		
		// Delete old OTPs
		$deleteQuery = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification'";
		$deleteResult = $db->delete($deleteQuery);
		echo "<strong>Old OTPs deleted:</strong> " . ($deleteResult ? "✅ Success" : "❌ Failed") . "<br>";
		
		// Insert new OTP
		$currentTime = date('Y-m-d H:i:s');
		$expiresAt = date('Y-m-d H:i:s', strtotime('+20 minutes'));
		
		$insertQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
		               VALUES ('" . mysqli_real_escape_string($db->link, $testEmail) . "', 
		                      '$testOTP', 
		                      'email_verification', 
		                      '$currentTime', 
		                      '$expiresAt',
		                      0)";
		
		echo "<strong>Insert Query:</strong><br><code style='background: #f8f9fa; padding: 5px; font-size: 12px;'>$insertQuery</code><br><br>";
		
		$insertResult = $db->insert($insertQuery);
		echo "<strong>Insert Result:</strong> " . ($insertResult ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
		
		if (!$insertResult) {
			echo "<strong>MySQL Error:</strong> " . mysqli_error($db->link) . "<br>";
		}
		
		// Verify insertion
		$verifyQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' AND purpose = 'email_verification' ORDER BY created_at DESC LIMIT 1";
		$verifyResult = $db->select($verifyQuery);
		
		if ($verifyResult && mysqli_num_rows($verifyResult) > 0) {
			$otpData = mysqli_fetch_assoc($verifyResult);
			echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
			echo "<strong>✅ OTP Found in Database:</strong><br>";
			echo "OTP: " . $otpData['otp'] . "<br>";
			echo "Created: " . $otpData['created_at'] . "<br>";
			echo "Expires: " . $otpData['expires_at'] . "<br>";
			echo "Is Used: " . ($otpData['is_used'] ? 'Yes' : 'No') . "<br>";
			echo "</div>";
			
			// Test verification
			include "classes/EmailOTP.php";
			$emailOTP = new EmailOTP();
			$verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'email_verification');
			echo "<strong>Verification Test:</strong> " . ($verifyResult ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
		} else {
			echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>❌ OTP not found in database after insertion</div>";
		}
		
		echo "</div>";
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	table { margin: 15px 0; font-size: 14px; }
	th, td { padding: 8px 12px; text-align: left; }
	form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
	input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
	button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
	code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 12px; }
</style>

<form method="POST">
	<h4>🧪 Test Manual OTP Creation</h4>
	<label>Email Address:</label><br>
	<input type="email" name="test_email" value="bistakaran298@gmail.com" required><br><br>
	<button type="submit">🔧 Test OTP Creation</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fffbf0; border-left: 4px solid #ffa500;">
	<h4>📋 Purpose:</h4>
	<p>This tool will:</p>
	<ol>
		<li>Show the exact structure of tbl_otp table</li>
		<li>Test manual OTP creation with the same process</li>
		<li>Identify any database issues or missing columns</li>
		<li>Test the verification process step by step</li>
	</ol>
</div>

<?php include "inc/footer.php"; ?>
