<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert_success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert_danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .debug-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 OTP Verification Test Tool</h1>
        
        <?php
        require_once 'lib/Database.php';
        require_once 'classes/PreRegistrationVerification.php';
        require_once 'helpers/NepalTime.php';
        
        $db = new Database();
        $message = "";
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_otp'])) {
            $email = $_POST['email'];
            $otp = $_POST['otp'];
            
            echo "<div class='debug-info'>";
            echo "<strong>Test Started:</strong><br>";
            echo "Email: $email<br>";
            echo "OTP: $otp<br>";
            echo "Time: " . date('Y-m-d H:i:s') . "<br>";
            echo "</div>";
            
            try {
                $preVerification = new PreRegistrationVerification();
                $result = $preVerification->verifyOTPAndCreateAccount($email, $otp);
                
                if ($result['success']) {
                    $message = "<div class='alert alert_success'>✅ SUCCESS: " . strip_tags($result['message']) . "</div>";
                } else {
                    $message = "<div class='alert alert_danger'>❌ FAILED: " . strip_tags($result['message']) . "</div>";
                }
                
                echo "<div class='debug-info'>";
                echo "<strong>Result:</strong><br>";
                echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "<br>";
                echo "Message: " . strip_tags($result['message']) . "<br>";
                echo "</div>";
                
            } catch (Exception $e) {
                $message = "<div class='alert alert_danger'>❌ EXCEPTION: " . $e->getMessage() . "</div>";
                echo "<div class='debug-info'>";
                echo "<strong>Exception:</strong><br>";
                echo "Message: " . $e->getMessage() . "<br>";
                echo "File: " . $e->getFile() . "<br>";
                echo "Line: " . $e->getLine() . "<br>";
                echo "</div>";
            }
        }
        
        // Get current data for the email
        $email = "thekomalad@gmail.com";
        $currentOtp = "";
        
        $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
        $result = $db->select($pendingQuery);
        
        if ($result && $result->num_rows > 0) {
            $pending = $result->fetch_assoc();
            $currentOtp = $pending['otp'];
            
            echo "<div class='alert alert_success'>";
            echo "✅ Found your verification data:<br>";
            echo "Email: {$pending['email']}<br>";
            echo "OTP: <strong>{$pending['otp']}</strong><br>";
            echo "Expires: {$pending['expires_at']}<br>";
            echo "</div>";
        } else {
            echo "<div class='alert alert_danger'>❌ No pending verification found for $email</div>";
        }
        ?>
        
        <?php if ($message) echo $message; ?>
        
        <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
            <h3>Test OTP Verification</h3>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $email; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="otp">OTP Code:</label>
                <input type="text" name="otp" id="otp" value="<?php echo $currentOtp; ?>" maxlength="6" pattern="[0-9]{6}" required>
                <small>Enter the 6-digit code from your email</small>
            </div>
            
            <button type="submit" name="test_otp">🔬 Test Verification</button>
        </form>
        
        <div style="margin-top: 30px;">
            <h3>🔗 Quick Links</h3>
            <p><a href="debug_verification_complete.php">🔍 Run Complete Diagnosis</a></p>
            <p><a href="verify_registration.php?email=<?php echo urlencode($email); ?>">🔐 Go to Official Verification Page</a></p>
        </div>
    </div>
</body>
</html>
