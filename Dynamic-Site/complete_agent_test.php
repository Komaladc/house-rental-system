<?php
// Complete Agent Signup Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/config.php";
include "lib/Database.php";

echo "<h1>Complete Agent Signup Test</h1>";

$db = new Database();

echo "<h2>Current Database State</h2>";

// Check users
$userCount = $db->select("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc();
echo "<p>Total users: {$userCount['count']}</p>";

// Check verification records
$verificationCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification")->fetch_assoc();
echo "<p>Total verification records: {$verificationCount['count']}</p>";

// Check pending records
$pendingCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'pending'")->fetch_assoc();
echo "<p>Pending verifications: {$pendingCount['count']}</p>";

if (isset($_POST['signup'])) {
    echo "<h2>Processing Signup...</h2>";
    
    // Simulate the signup form submission
    $_POST['fname'] = 'Dipesh';
    $_POST['lname'] = 'Tamang';
    $_POST['username'] = 'dipesh_real_agent_' . time();
    $_POST['email'] = 'dipesh.real.agent.' . time() . '@example.com';
    $_POST['cellno'] = '9841234567';
    $_POST['address'] = 'Kathmandu, Nepal';
    $_POST['password'] = 'password123';
    $_POST['confpass'] = 'password123';
    $_POST['level'] = '3'; // Agent
    $_POST['citizenship_id'] = 'CT' . time();
    
    echo "<h3>Form Data:</h3>";
    echo "<pre>";
    foreach ($_POST as $key => $value) {
        if ($key !== 'signup') {
            echo "$key: $value\n";
        }
    }
    echo "</pre>";
    
    // Include the signup processing logic
    include "classes/PreRegistrationVerification.php";
    
    $preReg = new PreRegistrationVerification();
    
    // Handle file uploads (none for this test)
    $uploadedFiles = [];
    
    // Prepare registration data
    $registrationData = [
        'fname' => $_POST['fname'],
        'lname' => $_POST['lname'],
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'cellno' => $_POST['cellno'],
        'address' => $_POST['address'],
        'password' => $_POST['password'],
        'level' => $_POST['level'],
        'requires_verification' => true,
        'uploaded_files' => $uploadedFiles,
        'citizenship_id' => $_POST['citizenship_id']
    ];
    
    echo "<h3>Registration Data:</h3>";
    echo "<pre>" . print_r($registrationData, true) . "</pre>";
    
    // Attempt to register
    $result = $preReg->initiateEmailVerification($registrationData);
    
    echo "<h3>Registration Result:</h3>";
    echo "<p>" . ($result['success'] ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
    echo "<div>{$result['message']}</div>";
    
    if ($result['success']) {
        // Get OTP and verify
        $otpQuery = "SELECT * FROM tbl_otp WHERE email = '{$registrationData['email']}' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($otpQuery);
        
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpRow = $otpResult->fetch_assoc();
            echo "<h3>OTP Found: {$otpRow['otp_code']}</h3>";
            
            // Verify OTP and create account
            $verifyResult = $preReg->verifyOTPAndCreateAccount($registrationData['email'], $otpRow['otp_code']);
            
            echo "<h3>OTP Verification Result:</h3>";
            echo "<p>" . ($verifyResult['success'] ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
            echo "<div>{$verifyResult['message']}</div>";
            
            if ($verifyResult['success']) {
                echo "<h3>Final Database Check:</h3>";
                
                // Check if user was created
                $newUserQuery = "SELECT * FROM tbl_user WHERE userEmail = '{$registrationData['email']}'";
                $newUser = $db->select($newUserQuery);
                if ($newUser && $newUser->num_rows > 0) {
                    $user = $newUser->fetch_assoc();
                    echo "<p>✅ User created: {$user['firstName']} {$user['lastName']} (ID: {$user['userId']})</p>";
                    
                    // Check if verification record was created
                    $newVerificationQuery = "SELECT * FROM tbl_user_verification WHERE user_id = {$user['userId']}";
                    $newVerification = $db->select($newVerificationQuery);
                    if ($newVerification && $newVerification->num_rows > 0) {
                        $verification = $newVerification->fetch_assoc();
                        echo "<p>✅ Verification record created: ID {$verification['verification_id']}, Status: {$verification['verification_status']}</p>";
                        
                        echo "<p><a href='Admin/verify_users.php' target='_blank' style='background: #28a745; color: white; padding: 10px; text-decoration: none;'>Check Admin Panel Now</a></p>";
                    } else {
                        echo "<p>❌ No verification record found for user {$user['userId']}</p>";
                    }
                } else {
                    echo "<p>❌ User not found in database</p>";
                }
            }
        } else {
            echo "<p>❌ No OTP found</p>";
        }
    }
} else {
    echo "<h2>Test Agent Signup</h2>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='signup' value='1'>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;'>Complete Agent Signup Test</button>";
    echo "</form>";
}

echo "<br><br>";
echo "<p><a href='test_admin_query.php'>Check Admin Query Test</a></p>";
echo "<p><a href='Admin/verify_users.php'>Check Admin Panel</a></p>";
?>
