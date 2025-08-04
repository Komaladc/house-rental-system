<?php
// Test agent signup end-to-end
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/config.php";
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";

echo "<h1>Agent Signup Test - Dipesh Tamang</h1>";

$db = new Database();
$preReg = new PreRegistrationVerification();

// Test data for Dipesh Tamang
$testData = [
    'fname' => 'Dipesh',
    'lname' => 'Tamang',
    'username' => 'dipesh_tamang',
    'email' => 'dipesh.tamang@example.com',
    'cellno' => '9841234567',
    'address' => 'Kathmandu, Nepal',
    'password' => 'password123',
    'level' => 3, // Agent
    'requires_verification' => true,
    'uploaded_files' => [],
    'citizenship_id' => 'CT12345678'
];

echo "<h2>1. Test Registration Data</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

// Check if user already exists
echo "<h2>2. Check if user exists</h2>";
$checkUser = "SELECT * FROM tbl_user WHERE email = '{$testData['email']}' OR username = '{$testData['username']}'";
$existingUser = $db->select($checkUser);

if ($existingUser && $existingUser->num_rows > 0) {
    $user = $existingUser->fetch_assoc();
    echo "<p style='color: orange;'>⚠️ User already exists:</p>";
    echo "<p>User ID: {$user['user_id']}, Name: {$user['fname']} {$user['lname']}, Level: {$user['level']}</p>";
    
    // Check verification record
    $checkVerification = "SELECT * FROM tbl_user_verification WHERE user_id = {$user['user_id']}";
    $verificationRecord = $db->select($checkVerification);
    
    if ($verificationRecord && $verificationRecord->num_rows > 0) {
        $verification = $verificationRecord->fetch_assoc();
        echo "<p style='color: blue;'>ℹ️ Verification record exists:</p>";
        echo "<p>Verification ID: {$verification['verification_id']}, Status: {$verification['verification_status']}</p>";
    } else {
        echo "<p style='color: red;'>❌ No verification record found!</p>";
    }
} else {
    echo "<p style='color: green;'>✅ User does not exist - ready for new registration</p>";
}

echo "<h2>3. Test Email OTP (Mock)</h2>";
echo "<p>✅ Email OTP would be sent to: {$testData['email']}</p>";

echo "<h2>4. Check Database Tables</h2>";
$userCount = $db->select("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc();
$verificationCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification")->fetch_assoc();

echo "<p>Total users: {$userCount['count']}</p>";
echo "<p>Total verification records: {$verificationCount['count']}</p>";

echo "<h2>5. Manual Registration Test</h2>";
echo "<form method='POST' style='border: 1px solid #ccc; padding: 20px;'>";
echo "<h3>Quick Register Dipesh Tamang as Agent</h3>";
echo "<input type='hidden' name='test_register' value='1'>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Register Now</button>";
echo "</form>";

if (isset($_POST['test_register'])) {
    echo "<h3>Processing Registration...</h3>";
    
    // Simulate the registration process
    try {
        $result = $preReg->initiateEmailVerification($testData);
        
        if ($result['success']) {
            echo "<p style='color: green;'>✅ Registration successful: {$result['message']}</p>";
            
            // Check if verification record was created
            $checkNewUser = "SELECT * FROM tbl_user WHERE email = '{$testData['email']}'";
            $newUser = $db->select($checkNewUser);
            
            if ($newUser && $newUser->num_rows > 0) {
                $user = $newUser->fetch_assoc();
                echo "<p>✅ User created with ID: {$user['user_id']}</p>";
                
                $checkNewVerification = "SELECT * FROM tbl_user_verification WHERE user_id = {$user['user_id']}";
                $newVerification = $db->select($checkNewVerification);
                
                if ($newVerification && $newVerification->num_rows > 0) {
                    $verification = $newVerification->fetch_assoc();
                    echo "<p>✅ Verification record created with ID: {$verification['verification_id']}</p>";
                    echo "<p>Status: {$verification['verification_status']}</p>";
                } else {
                    echo "<p style='color: red;'>❌ Verification record not created!</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Registration failed: {$result['message']}</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='Admin/verify_users.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Check Admin Verify Users</a></p>";
?>
