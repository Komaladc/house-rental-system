<?php
	include "inc/header.php";
	
	echo "<h2>✅ Timezone Fix Applied</h2>";
	
	echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #28a745;'>";
	echo "<h3>🕐 Current Time Settings (After Fix)</h3>";
	echo "<strong>Application Timezone:</strong> " . date_default_timezone_get() . "<br>";
	echo "<strong>Current Nepal Time:</strong> " . date('Y-m-d H:i:s T') . "<br>";
	echo "<strong>Timezone Offset:</strong> " . date('P') . "<br>";
	echo "</div>";
	
	echo "<div style='background: #d1ecf1; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
	echo "<h4>🔧 What Was Fixed:</h4>";
	echo "<ol>";
	echo "<li><strong>Timezone Setting:</strong> Set to 'Asia/Kathmandu' in header.php</li>";
	echo "<li><strong>OTP Expiry Time:</strong> Extended from 15 to 20 minutes</li>";
	echo "<li><strong>Pending Verification:</strong> Extended from 1 to 2 hours</li>";
	echo "<li><strong>Consistent Timing:</strong> All date() functions now use Nepal time</li>";
	echo "<li><strong>Better Logging:</strong> Added timezone-aware debug logs</li>";
	echo "</ol>";
	echo "</div>";
	
	// Test OTP creation timing
	if ($_POST && isset($_POST['test_timing'])) {
		include "classes/EmailOTP.php";
		$emailOTP = new EmailOTP();
		
		$testEmail = "test@example.com";
		$testOTP = "123456";
		
		echo "<h3>🧪 Test OTP Timing</h3>";
		
		// Store test OTP
		$result = $emailOTP->storeOTP($testEmail, $testOTP, 'test');
		
		if ($result) {
			echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
			echo "✅ Test OTP created successfully!<br>";
			echo "<strong>Current Time:</strong> " . date('Y-m-d H:i:s T') . "<br>";
			echo "<strong>Expires At:</strong> " . date('Y-m-d H:i:s T', strtotime('+20 minutes')) . "<br>";
			echo "</div>";
			
			// Check if it can be verified immediately
			$canVerify = $emailOTP->verifyOTP($testEmail, $testOTP, 'test');
			echo "<div style='background: " . ($canVerify ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
			echo ($canVerify ? "✅ OTP verification works immediately!" : "❌ OTP verification failed");
			echo "</div>";
			
			// Clean up test data
			$db->delete("DELETE FROM tbl_otp WHERE email = '$testEmail' AND purpose = 'test'");
		} else {
			echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Failed to create test OTP</div>";
		}
	}
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
	form { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0; }
	button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
	button:hover { background: #218838; }
</style>

<form method="POST">
	<h4>🧪 Test Timezone Fix</h4>
	<p>Click this button to test if OTP timing now works correctly with Nepal time:</p>
	<button type="submit" name="test_timing">Test OTP Timing</button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
	<h4>📋 Next Steps:</h4>
	<ol>
		<li><strong>Test Registration:</strong> Try the registration process again</li>
		<li><strong>Check Timing:</strong> Use the timezone debug tool to verify timing</li>
		<li><strong>Verify OTP:</strong> The 20-minute window should now work properly</li>
		<li><strong>Check Logs:</strong> Use the log viewer to see detailed timing information</li>
	</ol>
	<p><strong>The OTP verification should now work correctly with Nepal time! 🇳🇵</strong></p>
</div>

<?php include "inc/footer.php"; ?>
