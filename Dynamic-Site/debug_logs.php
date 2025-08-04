<?php
	echo "<h2>🗂️ PHP Error Log Viewer</h2>";
	echo "<p>This will show recent PHP error log entries to help debug the OTP issue.</p>";
	
	// Try different log locations
	$logLocations = [
		'C:\xampp\apache\logs\error.log',
		'C:\xampp\php\logs\php_error_log',
		ini_get('error_log'),
		'php_errors.log'
	];
	
	$logFound = false;
	
	foreach ($logLocations as $logFile) {
		if ($logFile && file_exists($logFile)) {
			echo "<h3>📄 Log file: $logFile</h3>";
			$logFound = true;
			
			// Read last 50 lines
			$lines = file($logFile);
			$recentLines = array_slice($lines, -50);
			
			echo "<div style='background: #f8f9fa; border: 1px solid #ddd; padding: 15px; max-height: 400px; overflow-y: scroll; font-family: monospace; font-size: 12px;'>";
			foreach ($recentLines as $line) {
				// Highlight OTP-related entries
				if (stripos($line, 'otp') !== false || stripos($line, 'verification') !== false) {
					echo "<div style='background: #fff3cd; padding: 2px;'>" . htmlspecialchars($line) . "</div>";
				} else {
					echo htmlspecialchars($line) . "<br>";
				}
			}
			echo "</div>";
			break;
		}
	}
	
	if (!$logFound) {
		echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
		echo "<strong>❌ No PHP error log found.</strong><br>";
		echo "Checked locations:<br>";
		foreach ($logLocations as $loc) {
			echo "• $loc<br>";
		}
		echo "</div>";
		
		echo "<div style='background: #d1ecf1; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
		echo "<strong>💡 To enable PHP error logging:</strong><br>";
		echo "1. Edit C:\\xampp\\php\\php.ini<br>";
		echo "2. Set: log_errors = On<br>";
		echo "3. Set: error_log = C:\\xampp\\php\\logs\\php_error_log<br>";
		echo "4. Restart Apache<br>";
		echo "</div>";
	}
	
	// Manual trigger button
	echo "<hr>";
	echo "<h3>🧪 Manual Test</h3>";
	echo "<p>Click this button to trigger a test log entry:</p>";
	echo "<button onclick=\"fetch('debug_otp_detailed.php?test=1')\" style='padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px;'>Generate Test Log Entry</button>";
	
	if (isset($_GET['test'])) {
		error_log("OTP Debug Test - This is a test log entry from debug_logs.php");
		echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>✅ Test log entry generated!</div>";
	}
	
	echo "<div style='margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007cba;'>";
	echo "<h4>📋 Instructions:</h4>";
	echo "<ol>";
	echo "<li>Try to verify an OTP on the signup form</li>";
	echo "<li>Refresh this page to see new log entries</li>";
	echo "<li>Look for entries highlighted in yellow (OTP-related)</li>";
	echo "<li>The logs will show exactly why OTP verification is failing</li>";
	echo "</ol>";
	echo "</div>";
?>

<style>
	body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
</style>
