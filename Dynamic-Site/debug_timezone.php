<?php
	include "inc/header.php";
	
	echo "<h2>🕐 Timezone and Time Debug</h2>";
	
	echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
	
	echo "<h3>Current Time Settings</h3>";
	echo "<strong>Server Default Timezone:</strong> " . date_default_timezone_get() . "<br>";
	echo "<strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
	echo "<strong>Current Server Time (with timezone):</strong> " . date('Y-m-d H:i:s T') . "<br>";
	
	// Set Nepal timezone and compare
	date_default_timezone_set('Asia/Kathmandu');
	echo "<strong>Nepal Time (Asia/Kathmandu):</strong> " . date('Y-m-d H:i:s T') . "<br>";
	echo "<strong>Nepal Timezone Offset:</strong> " . date('P') . "<br>";
	
	echo "</div>";
	
	// Check recent OTP records
	if (isset($_GET['email'])) {
		$email = $_GET['email'];
		echo "<h3>🔍 OTP Records for: " . htmlspecialchars($email) . "</h3>";
		
		$query = "SELECT *, 
		         TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_since_created,
		         TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_until_expires
		         FROM tbl_otp 
		         WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' 
		         AND purpose = 'email_verification' 
		         ORDER BY created_at DESC LIMIT 3";
		
		$result = $db->select($query);
		
		if ($result && mysqli_num_rows($result) > 0) {
			echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white;'>";
			echo "<tr style='background: #f8f9fa;'>";
			echo "<th>OTP</th><th>Created At</th><th>Expires At</th><th>Is Used</th><th>Minutes Since Created</th><th>Minutes Until Expires</th><th>Status</th>";
			echo "</tr>";
			
			while ($row = mysqli_fetch_assoc($result)) {
				$isExpired = $row['minutes_until_expires'] < 0;
				$statusColor = $isExpired ? '#f8d7da' : ($row['is_used'] ? '#fff3cd' : '#d4edda');
				
				echo "<tr style='background: $statusColor;'>";
				echo "<td>" . $row['otp'] . "</td>";
				echo "<td>" . $row['created_at'] . "</td>";
				echo "<td>" . $row['expires_at'] . "</td>";
				echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
				echo "<td>" . $row['minutes_since_created'] . "</td>";
				echo "<td>" . $row['minutes_until_expires'] . "</td>";
				echo "<td>";
				if ($isExpired) {
					echo "❌ EXPIRED";
				} elseif ($row['is_used']) {
					echo "⚠️ USED";
				} else {
					echo "✅ VALID";
				}
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
		} else {
			echo "<p>No OTP records found for this email.</p>";
		}
	}
	
	echo "<hr>";
	echo "<h3>🧪 Test Current Time vs OTP Expiry</h3>";
	
	if ($_POST && isset($_POST['test_email'])) {
		$testEmail = $_POST['test_email'];
		
		// Set Nepal timezone for the test
		date_default_timezone_set('Asia/Kathmandu');
		
		echo "<h4>Testing with Nepal Timezone</h4>";
		echo "<strong>Current Nepal Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
		
		// Check if there are any valid OTPs
		$currentTime = date('Y-m-d H:i:s');
		$checkQuery = "SELECT *, 
		              ('$currentTime' < expires_at) as is_not_expired,
		              is_used,
		              TIMESTAMPDIFF(MINUTE, '$currentTime', expires_at) as minutes_remaining
		              FROM tbl_otp 
		              WHERE email = '" . mysqli_real_escape_string($db->link, $testEmail) . "' 
		              AND purpose = 'email_verification' 
		              ORDER BY created_at DESC LIMIT 1";
		
		$checkResult = $db->select($checkQuery);
		
		if ($checkResult && mysqli_num_rows($checkResult) > 0) {
			$otpData = mysqli_fetch_assoc($checkResult);
			
			echo "<div style='background: white; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
			echo "<strong>Latest OTP Analysis:</strong><br>";
			echo "OTP: " . $otpData['otp'] . "<br>";
			echo "Created: " . $otpData['created_at'] . "<br>";
			echo "Expires: " . $otpData['expires_at'] . "<br>";
			echo "Current Time: " . $currentTime . "<br>";
			echo "Is Not Expired: " . ($otpData['is_not_expired'] ? 'YES' : 'NO') . "<br>";
			echo "Is Used: " . ($otpData['is_used'] ? 'YES' : 'NO') . "<br>";
			echo "Minutes Remaining: " . $otpData['minutes_remaining'] . "<br>";
			
			$canUse = $otpData['is_not_expired'] && !$otpData['is_used'];
			echo "<strong>Can Use OTP: " . ($canUse ? '✅ YES' : '❌ NO') . "</strong><br>";
			echo "</div>";
		}
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	table { margin: 15px 0; }
	th, td { padding: 8px 12px; text-align: left; }
	form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
	input[type="email"] { width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
	button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>

<form method="POST">
	<h4>Test Email for OTP Timing</h4>
	<label>Email Address:</label><br>
	<input type="email" name="test_email" value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : 'bistakaran298@gmail.com'; ?>" required><br><br>
	<button type="submit">🕐 Check OTP Timing</button>
</form>

<form method="GET">
	<h4>View OTP Records</h4>
	<label>Email Address:</label><br>
	<input type="email" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'bistakaran298@gmail.com'; ?>" required><br><br>
	<button type="submit">📋 View OTP Records</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
	<h4>🔧 Solution:</h4>
	<p>If the times don't match Nepal time, we need to:</p>
	<ol>
		<li>Set the default timezone to Asia/Kathmandu in the PHP code</li>
		<li>Update all time-related functions to use Nepal time</li>
		<li>Ensure database timestamps are consistent</li>
	</ol>
</div>

<?php include "inc/footer.php"; ?>
