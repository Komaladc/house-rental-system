<?php
// Test URL verification to ensure it's working after the column fix
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/PreRegistrationVerification.php";

echo "<h2>🔗 URL Verification Test</h2>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

$db = new Database();
$preReg = new PreRegistrationVerification();

// Check if we have URL parameters
if (isset($_GET['test']) && $_GET['test'] == 'create') {
    echo "<h3>Creating test verification link...</h3>";
    
    $testEmail = "urltest@example.com";
    
    // Clean up existing data
    $cleanupUser = "DELETE FROM tbl_user WHERE userEmail = '$testEmail'";
    $cleanupOTP = "DELETE FROM tbl_otp WHERE email = '$testEmail'";
    $cleanupPending = "DELETE FROM tbl_pending_verification WHERE email = '$testEmail'";
    $cleanupVerification = "DELETE FROM tbl_user_verification WHERE email = '$testEmail'";
    
    $db->delete($cleanupUser);
    $db->delete($cleanupOTP);
    $db->delete($cleanupPending);
    $db->delete($cleanupVerification);
    
    // Create test registration
    $testData = [
        'fname' => 'URL',
        'lname' => 'Test',
        'email' => $testEmail,
        'cellno' => '9800000002',
        'address' => 'URL Test Address',
        'password' => 'password123',
        'level' => '1', // Regular user
        'requires_verification' => false,
        'uploaded_files' => [],
        'citizenship_id' => ''
    ];
    
    $result = $preReg->initiateEmailVerification($testData);
    
    if ($result['success']) {
        echo "<p>✅ Test registration created</p>";
        
        // Get the verification token
        $tokenQuery = "SELECT verification_token FROM tbl_pending_verification WHERE email = '$testEmail' ORDER BY created_at DESC LIMIT 1";
        $tokenResult = $db->select($tokenQuery);
        
        if ($tokenResult && $tokenResult->num_rows > 0) {
            $tokenData = $tokenResult->fetch_assoc();
            $token = $tokenData['verification_token'];
            
            echo "<p>✅ Verification token: " . substr($token, 0, 20) . "...</p>";
            
            // Create verification URL
            $verificationUrl = "?email=" . urlencode($testEmail) . "&token=" . urlencode($token);
            echo "<p><a href='$verificationUrl' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test Verification URL</a></p>";
        }
    } else {
        echo "<p>❌ Failed to create test registration</p>";
    }
    
} else if (isset($_GET['email']) && isset($_GET['token'])) {
    // Test the URL verification
    $email = $_GET['email'];
    $token = $_GET['token'];
    
    echo "<h3>Testing URL verification for: $email</h3>";
    
    try {
        $result = $preReg->verifyAndCreateAccount($email, $token);
        
        if ($result['success']) {
            echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>🎉 URL VERIFICATION SUCCESS!</h3>";
            echo "<p>Account created successfully via verification URL!</p>";
            echo "<div>" . $result['message'] . "</div>";
            echo "</div>";
            
            // Show created user
            $userCheck = "SELECT * FROM tbl_user WHERE userEmail = '$email'";
            $userResult = $db->select($userCheck);
            
            if ($userResult && $userResult->num_rows > 0) {
                $userData = $userResult->fetch_assoc();
                echo "<h4>Created User Details:</h4>";
                echo "<p><strong>User ID:</strong> " . $userData['userId'] . "</p>";
                echo "<p><strong>Name:</strong> " . $userData['firstName'] . " " . $userData['lastName'] . "</p>";
                echo "<p><strong>Username:</strong> " . $userData['userName'] . "</p>";
                echo "<p><strong>Email:</strong> " . $userData['userEmail'] . "</p>";
                echo "<p><strong>Status:</strong> " . $userData['userStatus'] . "</p>";
            }
            
        } else {
            echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>❌ URL VERIFICATION FAILED</h3>";
            echo "<div>" . $result['message'] . "</div>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ EXCEPTION</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
} else {
    echo "<h3>📋 Test Instructions</h3>";
    echo "<ol>";
    echo "<li><a href='?test=create' style='color: #007cba;'>Create a test verification link</a></li>";
    echo "<li>Click the generated verification URL to test the verification process</li>";
    echo "<li>This tests the same flow used by verify_registration.php</li>";
    echo "</ol>";
    
    echo "<h3>🔍 Current Test Status</h3>";
    echo "<p>✅ Database columns fixed (userName, userEmail, etc.)</p>";
    echo "<p>✅ Username generation added for missing usernames</p>";
    echo "<p>✅ Password hashing implemented</p>";
    echo "<p>✅ Document storage fixed for user verification</p>";
}
?>
