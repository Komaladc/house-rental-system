<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Quick OTP Test</h2>";

// Test with the exact same setup as the signup form
include "inc/header.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

echo "<h3>✅ Classes Loaded Successfully</h3>";

try {
    $emailOTP = new EmailOTP();
    echo "<p>✅ EmailOTP object created</p>";
    
    $preVerification = new PreRegistrationVerification();
    echo "<p>✅ PreRegistrationVerification object created</p>";
    
    // Test OTP verification with your specific data
    $email = "bistakaran89@gmail.com";
    $otp = "897470";
    
    echo "<h3>🔍 Testing OTP: {$otp} for email: {$email}</h3>";
    
    // Check if OTP exists in database
    $currentTime = date('Y-m-d H:i:s');
    $query = "SELECT * FROM tbl_otp 
             WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' 
             AND otp = '" . mysqli_real_escape_string($db->link, $otp) . "' 
             ORDER BY created_at DESC";
    
    $result = $db->select($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green;'>✅ OTP found in database!</p>";
        
        $row = $result->fetch_assoc();
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>OTP</td><td><strong>" . htmlspecialchars($row['otp']) . "</strong></td></tr>";
        echo "<tr><td>Purpose</td><td>" . htmlspecialchars($row['purpose']) . "</td></tr>";
        echo "<tr><td>Created</td><td>" . htmlspecialchars($row['created_at']) . "</td></tr>";
        echo "<tr><td>Expires</td><td>" . htmlspecialchars($row['expires_at']) . "</td></tr>";
        echo "<tr><td>Used</td><td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td></tr>";
        echo "</table>";
        
        // Check if expired
        $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
        if ($isExpired) {
            echo "<p style='color:red;'>❌ OTP is expired</p>";
        } else {
            echo "<p style='color:green;'>✅ OTP is still valid</p>";
        }
        
        // Test verification
        echo "<h3>🧪 Testing Verification Process</h3>";
        $otpVerified = $emailOTP->verifyOTP($email, $otp, 'email_verification');
        
        if ($otpVerified) {
            echo "<p style='color:green;font-size:18px;'>🎉 <strong>OTP VERIFICATION: SUCCESS!</strong></p>";
        } else {
            echo "<p style='color:red;font-size:18px;'>❌ <strong>OTP VERIFICATION: FAILED!</strong></p>";
        }
        
    } else {
        echo "<p style='color:red;'>❌ OTP not found in database</p>";
        
        // Show all OTPs for this email
        echo "<h4>All OTPs for this email:</h4>";
        $allQuery = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC";
        $allResult = $db->select($allQuery);
        
        if ($allResult && $allResult->num_rows > 0) {
            echo "<table border='1' style='border-collapse:collapse;'>";
            echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
            while ($row = $allResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($row['otp']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
                echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No OTPs found for this email</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='signup_with_verification.php'>← Back to Signup Form</a></p>";
?>
