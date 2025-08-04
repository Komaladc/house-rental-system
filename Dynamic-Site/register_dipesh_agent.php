<?php
// Register Dipesh Tamang as Agent - Step by Step
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/config.php";
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";

echo "<h1>Register Dipesh Tamang as Agent</h1>";

$db = new Database();
$preReg = new PreRegistrationVerification();

// Test data for Dipesh Tamang
$testData = [
    'fname' => 'Dipesh',
    'lname' => 'Tamang',
    'username' => 'dipesh_agent',
    'email' => 'dipesh.tamang@example.com',
    'cellno' => '9841234567',
    'address' => 'Kathmandu, Nepal',
    'password' => 'password123',
    'level' => 3, // Agent
    'requires_verification' => true,
    'uploaded_files' => [], // No files for now
    'citizenship_id' => 'CT12345678'
];

echo "<h2>Step 1: Check if user already exists</h2>";
$checkUser = "SELECT * FROM tbl_user WHERE userEmail = '{$testData['email']}' OR userName = '{$testData['username']}'";
$existingUser = $db->select($checkUser);

if ($existingUser && $existingUser->num_rows > 0) {
    $user = $existingUser->fetch_assoc();
    echo "<p style='color: orange;'>⚠️ User already exists: {$user['firstName']} {$user['lastName']} (ID: {$user['userId']})</p>";
    
    // Delete existing user for fresh test
    if (isset($_POST['delete_user'])) {
        $deleteVerification = "DELETE FROM tbl_user_verification WHERE user_id = {$user['userId']}";
        $deleteUser = "DELETE FROM tbl_user WHERE userId = {$user['userId']}";
        
        $db->delete($deleteVerification);
        $db->delete($deleteUser);
        
        echo "<p style='color: green;'>✅ User deleted. Refresh to register again.</p>";
        echo "<a href='{$_SERVER['PHP_SELF']}'>Refresh Page</a>";
        exit;
    }
    
    echo "<form method='POST'>";
    echo "<input type='hidden' name='delete_user' value='1'>";
    echo "<button type='submit'>Delete Existing User</button>";
    echo "</form>";
    
} else {
    echo "<p style='color: green;'>✅ User does not exist - ready for registration</p>";
    
    echo "<h2>Step 2: Start Registration Process</h2>";
    
    if (isset($_POST['start_registration'])) {
        try {
            // First, initiate email verification
            $result = $preReg->initiateEmailVerification($testData);
            
            if ($result['success']) {
                echo "<p style='color: green;'>✅ Email verification initiated: {$result['message']}</p>";
                
                // Now simulate OTP verification
                echo "<h3>Step 3: Simulate OTP Verification</h3>";
                
                // Get the OTP from database
                $otpQuery = "SELECT * FROM tbl_otp WHERE email = '{$testData['email']}' ORDER BY created_at DESC LIMIT 1";
                $otpResult = $db->select($otpQuery);
                
                if ($otpResult && $otpResult->num_rows > 0) {
                    $otpRow = $otpResult->fetch_assoc();
                    echo "<p>OTP generated: {$otpRow['otp_code']}</p>";
                    
                    // Verify OTP
                    $verifyResult = $preReg->verifyOTPAndCreateAccount($testData['email'], $otpRow['otp_code']);
                    
                    if ($verifyResult['success']) {
                        echo "<p style='color: green;'>✅ OTP verified and user created!</p>";
                        echo "<p>{$verifyResult['message']}</p>";
                    } else {
                        echo "<p style='color: red;'>❌ OTP verification failed: {$verifyResult['message']}</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ No OTP found in database</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Email verification failed: {$result['message']}</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<form method='POST'>";
        echo "<input type='hidden' name='start_registration' value='1'>";
        echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Start Registration</button>";
        echo "</form>";
    }
}

echo "<h2>Current Database Status</h2>";
$userCount = $db->select("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc();
$verificationCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification")->fetch_assoc();
$pendingCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'pending'")->fetch_assoc();

echo "<p>Total users: {$userCount['count']}</p>";
echo "<p>Total verification records: {$verificationCount['count']}</p>";
echo "<p>Pending verifications: {$pendingCount['count']}</p>";

echo "<p><a href='Admin/verify_users.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Check Admin Verify Users</a></p>";
?>
