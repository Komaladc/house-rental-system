<?php
include_once 'config/config.php';
include_once 'config/timezone.php';
include_once 'lib/Database.php';
include_once 'classes/EmailOTP.php';
include_once 'classes/PreRegistrationVerification.php';

echo "<h2>🔧 OTP Verification Fix Test</h2>";

// Test email
$testEmail = "test@example.com";
$testOTP = "123456";

echo "<h3>1. Testing OTP Storage and Retrieval</h3>";

try {
    // Initialize Database and EmailOTP
    $db = new Database();
    $emailOTP = new EmailOTP();
    
    // Clear any existing OTPs for test email
    $clearQuery = "DELETE FROM tbl_otp WHERE email = '$testEmail'";
    $db->delete($clearQuery);
    echo "✓ Cleared existing OTPs for test email<br>";
    
    // Store a test OTP
    $storeResult = $emailOTP->storeOTP($testEmail, $testOTP, 'registration');
    echo "Store OTP Result: " . ($storeResult ? "✓ SUCCESS" : "✗ FAILED") . "<br>";
    
    // Check if OTP was stored correctly
    $checkQuery = "SELECT * FROM tbl_otp WHERE email = '$testEmail' AND otp = '$testOTP'";
    $result = $db->select($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✓ OTP stored correctly in database<br>";
        echo "  - Email: {$row['email']}<br>";
        echo "  - OTP Code: {$row['otp']}<br>";
        echo "  - Purpose: {$row['purpose']}<br>";
        echo "  - Created: {$row['created_at']}<br>";
        echo "  - Expires: {$row['expires_at']}<br>";
        echo "  - Is Used: " . ($row['is_used'] ? 'Yes' : 'No') . "<br>";
        
        // Now test verification
        echo "<br><h3>2. Testing OTP Verification</h3>";
        $verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
        echo "Verify OTP Result: " . ($verifyResult ? "✓ SUCCESS" : "✗ FAILED") . "<br>";
        
        if ($verifyResult) {
            echo "🎉 <strong>OTP VERIFICATION WORKING CORRECTLY!</strong><br>";
            
            // Check if OTP was marked as used
            $checkUsedQuery = "SELECT is_used FROM tbl_otp WHERE email = '$testEmail' AND otp = '$testOTP'";
            $usedResult = $db->select($checkUsedQuery);
            if ($usedResult && $usedResult->num_rows > 0) {
                $usedRow = $usedResult->fetch_assoc();
                echo "✓ OTP marked as used: " . ($usedRow['is_used'] ? 'Yes' : 'No') . "<br>";
            }
        } else {
            echo "❌ <strong>OTP VERIFICATION STILL FAILING!</strong><br>";
        }
        
    } else {
        echo "✗ OTP was not stored in database<br>";
    }
    
    echo "<br><h3>3. Testing Full Registration Flow</h3>";
    
    // Test the full PreRegistrationVerification flow
    $preReg = new PreRegistrationVerification();
    
    // Create a sample registration data
    $registrationData = [
        'fname' => 'Test',
        'lname' => 'User',
        'username' => 'testuser',
        'email' => $testEmail,
        'cellno' => '9801234567',
        'address' => 'Test Address',
        'password' => 'testpass123',
        'level' => 'Owner'
    ];
    
    echo "Testing with registration data for: $testEmail<br>";
    
    // Clear existing data
    $clearPendingQuery = "DELETE FROM tbl_pending_verification WHERE email = '$testEmail'";
    $db->delete($clearPendingQuery);
    
    // Initiate registration
    $initResult = $preReg->initiateEmailVerification($registrationData);
    echo "Registration Initiation: " . ($initResult['success'] ? "✓ SUCCESS" : "✗ FAILED") . "<br>";
    
    if ($initResult['success']) {
        // Get the OTP that was generated
        $getOTPQuery = "SELECT otp FROM tbl_otp WHERE email = '$testEmail' AND purpose = 'registration' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($getOTPQuery);
        
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpRow = $otpResult->fetch_assoc();
            $actualOTP = $otpRow['otp'];
            echo "Generated OTP: $actualOTP<br>";
            
            // Test OTP verification and account creation
            $verificationResult = $preReg->verifyOTPAndCreateAccount($testEmail, $actualOTP);
            echo "Account Creation: " . ($verificationResult['success'] ? "✓ SUCCESS" : "✗ FAILED") . "<br>";
            
            if (!$verificationResult['success']) {
                echo "Error Message: " . strip_tags($verificationResult['message']) . "<br>";
            } else {
                echo "🎉 <strong>FULL REGISTRATION FLOW WORKING!</strong><br>";
            }
        } else {
            echo "✗ Could not retrieve generated OTP<br>";
        }
    } else {
        echo "Error Message: " . strip_tags($initResult['message']) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "<br>";
}

echo "<br><h3>4. Current Database State</h3>";

// Show current OTP records
echo "<h4>OTP Records:</h4>";
$otpQuery = "SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 5";
$otpResult = $db->select($otpQuery);

if ($otpResult && $otpResult->num_rows > 0) {
    echo "<table border='1'><tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
    while ($row = $otpResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['otp']}</td>";
        echo "<td>{$row['purpose']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No OTP records found.<br>";
}

// Show pending verification records
echo "<h4>Pending Verification Records:</h4>";
$pendingQuery = "SELECT email, verification_status, created_at, expires_at FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 5";
$pendingResult = $db->select($pendingQuery);

if ($pendingResult && $pendingResult->num_rows > 0) {
    echo "<table border='1'><tr><th>Email</th><th>Status</th><th>Created</th><th>Expires</th></tr>";
    while ($row = $pendingResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['verification_status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No pending verification records found.<br>";
}

echo "<br><h3>✅ Test Complete</h3>";
echo "<p>If you see '🎉 OTP VERIFICATION WORKING CORRECTLY!' above, then the issue has been fixed!</p>";
?>
