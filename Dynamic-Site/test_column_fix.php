<?php
// Test the fixed database columns issue
session_start();
include_once "config/timezone.php";
include_once "config/config.php";
include_once "lib/Database.php";
include_once "classes/PreRegistrationVerification.php";

echo "<h2>🔧 Database Column Fix Test</h2>";
echo "<p><strong>Current Nepal Time:</strong> " . NepalTime::now() . "</p>";

$db = new Database();
$preReg = new PreRegistrationVerification();

// Test email and OTP
$testEmail = "columnfix@test.com";
$testOTP = "123456";

echo "<h3>Step 1: Clean up existing test data</h3>";
$cleanupUser = "DELETE FROM tbl_user WHERE userEmail = '$testEmail'";
$cleanupOTP = "DELETE FROM tbl_otp WHERE email = '$testEmail'";
$cleanupPending = "DELETE FROM tbl_pending_verification WHERE email = '$testEmail'";
$cleanupVerification = "DELETE FROM tbl_user_verification WHERE email = '$testEmail'";

$db->delete($cleanupUser);
$db->delete($cleanupOTP);
$db->delete($cleanupPending);
$db->delete($cleanupVerification);

echo "<p>✅ Cleaned up existing test data</p>";

echo "<h3>Step 2: Create test registration data</h3>";
$testData = [
    'fname' => 'Column',
    'lname' => 'Fix',
    'email' => $testEmail,
    'cellno' => '9800000001',
    'address' => 'Test Address for Column Fix',
    'password' => 'password123',
    'level' => '3', // Agent level to test document storage
    'requires_verification' => true,
    'uploaded_files' => [
        'citizenship_front' => 'test_front.jpg',
        'citizenship_back' => 'test_back.jpg'
    ],
    'citizenship_id' => 'TEST123456'
];

echo "<h3>Step 3: Initiate email verification</h3>";
$result = $preReg->initiateEmailVerification($testData);

if ($result['success']) {
    echo "<p>✅ Email verification initiated successfully</p>";
    
    // Override the OTP in database for testing
    $updateOTP = "UPDATE tbl_otp SET otp = '$testOTP' WHERE email = '$testEmail' AND purpose = 'registration'";
    $db->update($updateOTP);
    echo "<p>✅ Set test OTP: $testOTP</p>";
    
    echo "<h3>Step 4: Test account creation with fixed columns</h3>";
    try {
        $accountResult = $preReg->verifyOTPAndCreateAccount($testEmail, $testOTP);
        
        if ($accountResult['success']) {
            echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>🎉 SUCCESS!</h3>";
            echo "<p>Account created successfully with fixed database columns!</p>";
            echo "<p>The 'username' column issue has been resolved.</p>";
            echo "</div>";
            
            // Check what was actually created
            echo "<h3>Step 5: Verify created records</h3>";
            
            $userCheck = "SELECT * FROM tbl_user WHERE userEmail = '$testEmail'";
            $userResult = $db->select($userCheck);
            
            if ($userResult && $userResult->num_rows > 0) {
                $userData = $userResult->fetch_assoc();
                echo "<p>✅ User created with ID: " . $userData['userId'] . "</p>";
                echo "<p>✅ Username: " . $userData['userName'] . "</p>";
                echo "<p>✅ Email: " . $userData['userEmail'] . "</p>";
                echo "<p>✅ Status: " . $userData['userStatus'] . "</p>";
            }
            
            $verificationCheck = "SELECT * FROM tbl_user_verification WHERE email = '$testEmail'";
            $verificationResult = $db->select($verificationCheck);
            
            if ($verificationResult && $verificationResult->num_rows > 0) {
                $verificationData = $verificationResult->fetch_assoc();
                echo "<p>✅ Verification record created</p>";
                echo "<p>✅ Verification Username: " . $verificationData['username'] . "</p>";
                echo "<p>✅ User Type: " . $verificationData['user_type'] . "</p>";
                echo "<p>✅ Status: " . $verificationData['verification_status'] . "</p>";
            }
            
        } else {
            echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>❌ FAILED</h3>";
            echo "<div>" . $accountResult['message'] . "</div>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ EXCEPTION</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
} else {
    echo "<p>❌ Email verification initiation failed: " . strip_tags($result['message']) . "</p>";
}

echo "<h3>Current Database Status</h3>";
echo "<h4>User Table</h4>";
$userQuery = "SELECT userId, firstName, lastName, userName, userEmail, userLevel, userStatus FROM tbl_user ORDER BY userId DESC LIMIT 5";
$userResult = $db->select($userQuery);

if ($userResult && $userResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Username</th><th>Email</th><th>Level</th><th>Status</th></tr>";
    while ($row = $userResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['userId'] . "</td>";
        echo "<td>" . $row['firstName'] . "</td>";
        echo "<td>" . $row['lastName'] . "</td>";
        echo "<td>" . $row['userName'] . "</td>";
        echo "<td>" . $row['userEmail'] . "</td>";
        echo "<td>" . $row['userLevel'] . "</td>";
        echo "<td>" . $row['userStatus'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found</p>";
}
?>
