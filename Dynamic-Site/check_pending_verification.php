<?php
include "config/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Check Pending Verification Records</h1>";

echo "<h2>tbl_pending_verification records:</h2>";
$result = $mysqli->query("SELECT * FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Token</th><th>OTP</th><th>Registration Data</th><th>Is Verified</th><th>Created</th><th>Expires</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $registrationData = json_decode($row['registration_data'], true);
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>" . substr($row['verification_token'], 0, 10) . "...</td>";
        echo "<td>{$row['otp']}</td>";
        echo "<td>";
        if ($registrationData) {
            echo "Level: " . ($registrationData['level'] ?? 'N/A') . "<br>";
            echo "Name: " . ($registrationData['fname'] ?? '') . " " . ($registrationData['lname'] ?? '') . "<br>";
            echo "Requires Verification: " . ($registrationData['requires_verification'] ? 'Yes' : 'No');
        } else {
            echo "Invalid JSON";
        }
        echo "</td>";
        echo "<td>" . ($row['is_verified'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending verification records found</p>";
}

echo "<h2>tbl_otp records:</h2>";
$result = $mysqli->query("SELECT * FROM tbl_otp ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>OTP Code</th><th>Purpose</th><th>Expires</th><th>Used</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['otp_code']}</td>";
        echo "<td>{$row['purpose']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found</p>";
}

echo "<h2>Test Registration Flow</h2>";
echo "<form method='POST'>";
echo "<h3>Quick Test - Register 'Dipesh Agent'</h3>";
echo "<input type='hidden' name='test_quick_register' value='1'>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none;'>Quick Register</button>";
echo "</form>";

if (isset($_POST['test_quick_register'])) {
    // Create a quick registration
    include "lib/Database.php";
    include "classes/PreRegistrationVerification.php";
    
    $db = new Database();
    $preReg = new PreRegistrationVerification();
    
    $testData = [
        'fname' => 'Dipesh',
        'lname' => 'Tamang', 
        'username' => 'dipesh_agent_' . time(),
        'email' => 'dipesh.agent.' . time() . '@example.com',
        'cellno' => '9841234567',
        'address' => 'Kathmandu, Nepal',
        'password' => 'password123',
        'level' => 3,
        'requires_verification' => true,
        'uploaded_files' => [],
        'citizenship_id' => 'CT' . time()
    ];
    
    echo "<h3>Registration Data:</h3>";
    echo "<pre>" . print_r($testData, true) . "</pre>";
    
    // Step 1: Initiate email verification
    $result = $preReg->initiateEmailVerification($testData);
    echo "<h3>Step 1 - Email Verification Result:</h3>";
    echo "<p>" . ($result['success'] ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
    echo "<p>{$result['message']}</p>";
    
    if ($result['success']) {
        // Step 2: Get the OTP
        $otpQuery = "SELECT * FROM tbl_otp WHERE email = '{$testData['email']}' ORDER BY created_at DESC LIMIT 1";
        $otpResult = $db->select($otpQuery);
        
        if ($otpResult && $otpResult->num_rows > 0) {
            $otpRow = $otpResult->fetch_assoc();
            echo "<h3>Step 2 - OTP Found:</h3>";
            echo "<p>OTP Code: <strong>{$otpRow['otp_code']}</strong></p>";
            
            // Step 3: Verify OTP and create account
            $verifyResult = $preReg->verifyOTPAndCreateAccount($testData['email'], $otpRow['otp_code']);
            echo "<h3>Step 3 - OTP Verification Result:</h3>";
            echo "<p>" . ($verifyResult['success'] ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
            echo "<p>{$verifyResult['message']}</p>";
            
            if ($verifyResult['success']) {
                echo "<p><a href='Admin/verify_users.php' target='_blank'>Check Admin Panel</a></p>";
            }
        }
    }
}

$mysqli->close();
?>
