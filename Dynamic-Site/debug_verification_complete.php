<?php
// Security check - only allow admin access
if(!defined('ADMIN_DEBUG_ACCESS') && !isset($_SESSION['admin_debug'])) {
    die('Access denied. Debug files are restricted.');
}

require_once 'lib/Database.php';
require_once 'classes/PreRegistrationVerification.php';

$db = new Database();
$email = "thekomalad@gmail.com";

echo "=== COMPREHENSIVE VERIFICATION DEBUG ===<br><br>";

// 1. Check if database connection works
try {
    $db = new Database();
    echo "✅ Database connection: OK<br><br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br><br>";
    exit;
}

// 2. Check required tables exist
$tables = ['tbl_pending_verification', 'tbl_otp', 'tbl_user', 'tbl_user_verification'];
echo "<strong>2. Checking required tables:</strong><br>";
foreach ($tables as $table) {
    $result = $db->select("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $table: EXISTS<br>";
    } else {
        echo "❌ $table: MISSING<br>";
    }
}
echo "<br>";

// 3. Check pending verification data
echo "<strong>3. Checking pending verification for $email:</strong><br>";
$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
$result = $db->select($pendingQuery);

if ($result && $result->num_rows > 0) {
    $pending = $result->fetch_assoc();
    echo "✅ Found pending verification:<br>";
    echo "&nbsp;&nbsp;Email: {$pending['email']}<br>";
    echo "&nbsp;&nbsp;OTP: <strong style='color:blue;'>{$pending['otp']}</strong><br>";
    echo "&nbsp;&nbsp;Token: {$pending['verification_token']}<br>";
    echo "&nbsp;&nbsp;Expires: {$pending['expires_at']}<br>";
    echo "&nbsp;&nbsp;Created: {$pending['created_at']}<br>";
    echo "&nbsp;&nbsp;Is Verified: " . ($pending['is_verified'] ? 'YES' : 'NO') . "<br><br>";
    
    $pendingOtp = $pending['otp'];
    $pendingToken = $pending['verification_token'];
} else {
    echo "❌ No pending verification found!<br><br>";
    $pendingOtp = null;
    $pendingToken = null;
}

// 4. Check OTP table
echo "<strong>4. Checking OTP table for $email:</strong><br>";
$otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' AND purpose = 'registration' ORDER BY created_at DESC LIMIT 1";
$result = $db->select($otpQuery);

if ($result && $result->num_rows > 0) {
    $otp = $result->fetch_assoc();
    echo "✅ Found OTP record:<br>";
    echo "&nbsp;&nbsp;Email: {$otp['email']}<br>";
    echo "&nbsp;&nbsp;OTP: <strong style='color:blue;'>{$otp['otp']}</strong><br>";
    echo "&nbsp;&nbsp;Purpose: {$otp['purpose']}<br>";
    echo "&nbsp;&nbsp;Expires: {$otp['expires_at']}<br>";
    echo "&nbsp;&nbsp;Is Used: " . ($otp['is_used'] ? 'YES' : 'NO') . "<br>";
    echo "&nbsp;&nbsp;Created: {$otp['created_at']}<br><br>";
    
    $otpTableOtp = $otp['otp'];
} else {
    echo "❌ No OTP record found!<br><br>";
    $otpTableOtp = null;
}

// 5. Check for time issues
echo "<strong>5. Checking time issues:</strong><br>";
$currentTime = date('Y-m-d H:i:s');
echo "Current Server Time: $currentTime<br>";

if ($pending) {
    if ($pending['expires_at'] > $currentTime) {
        echo "✅ Pending verification not expired<br>";
    } else {
        echo "❌ Pending verification EXPIRED<br>";
    }
}

if ($otp) {
    if ($otp['expires_at'] > $currentTime) {
        echo "✅ OTP not expired<br>";
    } else {
        echo "❌ OTP EXPIRED<br>";
    }
}
echo "<br>";

// 6. Test verification methods
if ($pendingOtp) {
    echo "<strong>6. Testing verification methods:</strong><br>";
    
    try {
        $preVerification = new PreRegistrationVerification();
        
        echo "Testing verifyOTPAndCreateAccount method...<br>";
        
        // First, reset any used flags
        if ($otpTableOtp) {
            $db->update("UPDATE tbl_otp SET is_used = 0 WHERE email = '$email' AND otp = '$pendingOtp'");
        }
        
        $result = $preVerification->verifyOTPAndCreateAccount($email, $pendingOtp);
        
        echo "Result: " . ($result['success'] ? '✅ SUCCESS' : '❌ FAILED') . "<br>";
        echo "Message: " . strip_tags($result['message']) . "<br><br>";
        
        if (!$result['success']) {
            echo "<strong>Debugging failed verification:</strong><br>";
            
            // Check what's in the database now
            $debugOtp = $db->select("SELECT * FROM tbl_otp WHERE email = '$email' AND otp = '$pendingOtp'");
            if ($debugOtp && $debugOtp->num_rows > 0) {
                $debugData = $debugOtp->fetch_assoc();
                echo "OTP record exists - Used: " . ($debugData['is_used'] ? 'YES' : 'NO') . "<br>";
                echo "OTP expires: {$debugData['expires_at']}<br>";
                echo "Current time: $currentTime<br>";
            } else {
                echo "❌ OTP record not found during verification<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Exception during verification: " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
    }
}

echo "<hr>";
echo "<h3>🔧 AUTOMATIC FIXES</h3>";

// Fix 1: Ensure OTP record exists
if ($pendingOtp && !$otpTableOtp) {
    echo "Creating missing OTP record...<br>";
    $insertOtp = "INSERT INTO tbl_otp (email, otp, purpose, expires_at, created_at, is_used) 
                 VALUES ('$email', '$pendingOtp', 'registration', '{$pending['expires_at']}', NOW(), 0)";
    
    if ($db->insert($insertOtp)) {
        echo "✅ OTP record created<br>";
    } else {
        echo "❌ Failed to create OTP record<br>";
    }
}

// Fix 2: Reset used flag
if ($otpTableOtp) {
    echo "Resetting OTP used flag...<br>";
    $db->update("UPDATE tbl_otp SET is_used = 0 WHERE email = '$email' AND otp = '$pendingOtp'");
    echo "✅ OTP reset<br>";
}

// Fix 3: Extend expiration if needed
if ($pending && $pending['expires_at'] <= $currentTime) {
    echo "Extending OTP expiration...<br>";
    $newExpiry = date('Y-m-d H:i:s', strtotime('+2 hours'));
    $db->update("UPDATE tbl_pending_verification SET expires_at = '$newExpiry' WHERE email = '$email'");
    $db->update("UPDATE tbl_otp SET expires_at = '$newExpiry' WHERE email = '$email' AND otp = '$pendingOtp'");
    echo "✅ Expiration extended by 2 hours<br>";
}

echo "<br>";
echo "<h3>🎯 DIRECT TEST</h3>";
echo "<p>Use this exact OTP in the verification form:</p>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:5px; font-size:20px; text-align:center;'>";
echo "<strong>OTP: " . ($pendingOtp ? $pendingOtp : 'NOT FOUND') . "</strong>";
echo "</div><br>";

echo "<p><a href='verify_registration.php?email=" . urlencode($email) . "' style='background:#3498db; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🔗 Go to Verification Page</a></p>";
?>
