<?php
require_once 'lib/Database.php';
require_once 'classes/PreRegistrationVerification.php';

echo "Testing Email Verification Flow...\n\n";

// Create test registration data
$testEmail = "test.verification@example.com";
$testData = [
    'fname' => 'Test',
    'lname' => 'User',
    'email' => $testEmail,
    'cellno' => '9800000000',
    'address' => 'Test Address',
    'password' => 'test123',
    'level' => '2', // owner
    'requires_verification' => true
];

echo "1. Creating test registration data...\n";

$preReg = new PreRegistrationVerification();
$db = new Database();

// Clean up any existing test data
$db->delete("DELETE FROM tbl_pending_verification WHERE email = '$testEmail'");
$db->delete("DELETE FROM tbl_user WHERE userEmail = '$testEmail'");
$db->delete("DELETE FROM tbl_user_verification WHERE email = '$testEmail'");

echo "2. Initiating email verification...\n";

// Store pending verification
$verificationToken = bin2hex(random_bytes(32));
$otp = rand(100000, 999999);
$hashedPassword = md5($testData['password']);

$insertData = [
    'fname' => $testData['fname'],
    'lname' => $testData['lname'],
    'email' => $testData['email'],
    'cellno' => $testData['cellno'],
    'address' => $testData['address'],
    'password' => $hashedPassword,
    'level' => $testData['level'],
    'requires_verification' => true
];

$insertQuery = "INSERT INTO tbl_pending_verification 
                (email, registration_data, otp, verification_token, expires_at, created_at) 
                VALUES 
                ('$testEmail', 
                 '" . mysqli_real_escape_string($db->link, json_encode($insertData)) . "',
                 '$otp',
                 '$verificationToken',
                 DATE_ADD(NOW(), INTERVAL 1 HOUR),
                 NOW())";

if ($db->insert($insertQuery)) {
    echo "✓ Pending verification record created\n";
} else {
    echo "✗ Failed to create pending verification record\n";
    exit;
}

echo "3. Testing email verification process...\n";

// Simulate clicking the email verification link
try {
    $result = $preReg->verifyAndCreateAccount($testEmail, $verificationToken);
    
    if ($result['success']) {
        echo "✓ Email verification successful!\n";
        echo "Message: " . strip_tags($result['message']) . "\n";
        
        // Check if user was created
        $userCheck = $db->select("SELECT * FROM tbl_user WHERE userEmail = '$testEmail'");
        if ($userCheck && $userCheck->num_rows > 0) {
            $user = $userCheck->fetch_assoc();
            echo "✓ User account created with ID: " . $user['userId'] . "\n";
            echo "✓ Username: " . $user['userName'] . "\n";
            
            // Check if verification record was created
            $verificationCheck = $db->select("SELECT * FROM tbl_user_verification WHERE email = '$testEmail'");
            if ($verificationCheck && $verificationCheck->num_rows > 0) {
                echo "✓ User verification record created\n";
            } else {
                echo "✗ User verification record NOT created\n";
            }
        } else {
            echo "✗ User account NOT created\n";
        }
    } else {
        echo "✗ Email verification failed!\n";
        echo "Error: " . strip_tags($result['message']) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception during email verification: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n4. Cleaning up test data...\n";
$db->delete("DELETE FROM tbl_pending_verification WHERE email = '$testEmail'");
$db->delete("DELETE FROM tbl_user WHERE userEmail = '$testEmail'");
$db->delete("DELETE FROM tbl_user_verification WHERE email = '$testEmail'");
echo "✓ Test data cleaned up\n";

echo "\nTest completed.\n";
?>
