<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files exactly like signup form does
include "inc/header.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

echo "<div class='page_title'>";
echo "<h1 class='sub-title'>🔍 OTP Verification Debug Tool</h1>";
echo "</div>";

echo "<div class='container form_container'>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    echo "<div class='mcol_12'>";
    echo "<h3>🧪 Testing OTP Verification</h3>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>OTP:</strong> " . htmlspecialchars($otp) . "</p>";
    
    try {
        $emailOTP = new EmailOTP();
        $preVerification = new PreRegistrationVerification();
        
        echo "<h4>✅ Objects Created Successfully</h4>";
        
        // Test 1: Direct database query
        echo "<h4>📊 Database Check</h4>";
        $currentTime = date('Y-m-d H:i:s');
        
        // Query all OTPs for this email
        $query = "SELECT * FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' ORDER BY created_at DESC";
        $result = $db->select($query);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color:green;'>✅ Found " . $result->num_rows . " OTP record(s)</p>";
            echo "<table border='1' class='tbl_form'>";
            echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th><th>Match</th><th>Valid</th></tr>";
            
            $foundMatch = false;
            while ($row = $result->fetch_assoc()) {
                $isMatch = $row['otp'] === $otp;
                $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
                $isUsed = $row['is_used'] == 1;
                $isValid = $isMatch && !$isExpired && !$isUsed;
                
                if ($isMatch) $foundMatch = true;
                
                echo "<tr style='" . ($isMatch ? 'background:#e7f3ff;' : '') . "'>";
                echo "<td><strong>" . htmlspecialchars($row['otp']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
                echo "<td>" . ($row['is_used'] ? '🔒 Yes' : '○ No') . "</td>";
                echo "<td>" . ($isMatch ? '✅ Yes' : '○ No') . "</td>";
                echo "<td>" . ($isValid ? '✅ Valid' : ($isMatch ? ($isExpired ? '⏰ Expired' : '🔒 Used') : '○ No')) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            if (!$foundMatch) {
                echo "<p style='color:red;'>❌ <strong>OTP {$otp} not found in database</strong></p>";
            }
        } else {
            echo "<p style='color:red;'>❌ No OTP records found for this email</p>";
        }
        
        // Test 2: OTP Verification
        echo "<h4>🔐 OTP Verification Test</h4>";
        $otpVerified = $emailOTP->verifyOTP($email, $otp, 'email_verification');
        
        if ($otpVerified) {
            echo "<p style='color:green;font-size:18px;'>🎉 <strong>OTP VERIFICATION: SUCCESS!</strong></p>";
        } else {
            echo "<p style='color:red;font-size:18px;'>❌ <strong>OTP VERIFICATION: FAILED!</strong></p>";
        }
        
        // Test 3: Pending Verification
        echo "<h4>📋 Pending Verification Check</h4>";
        $pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
        $pendingResult = $db->select($pendingQuery);
        
        if ($pendingResult && $pendingResult->num_rows > 0) {
            $pendingData = $pendingResult->fetch_assoc();
            echo "<p style='color:green;'>✅ Pending verification found</p>";
            
            echo "<table border='1' class='tbl_form'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>OTP</td><td><strong>" . htmlspecialchars($pendingData['otp']) . "</strong></td></tr>";
            echo "<tr><td>Token</td><td>" . htmlspecialchars(substr($pendingData['verification_token'], 0, 20)) . "...</td></tr>";
            echo "<tr><td>Expires</td><td>" . htmlspecialchars($pendingData['expires_at']) . "</td></tr>";
            echo "<tr><td>Verified</td><td>" . ($pendingData['is_verified'] ? 'Yes' : 'No') . "</td></tr>";
            echo "</table>";
            
            if ($pendingData['otp'] === $otp) {
                echo "<p style='color:green;'>✅ OTP matches pending verification</p>";
                
                // Test 4: Complete verification process
                if ($otpVerified) {
                    echo "<h4>🚀 Complete Account Creation Test</h4>";
                    $accountResult = $preVerification->verifyAndCreateAccount($email, $pendingData['verification_token'], $otp);
                    
                    if ($accountResult['success']) {
                        echo "<p style='color:green;font-size:18px;'>🎉 <strong>ACCOUNT CREATION: SUCCESS!</strong></p>";
                        echo "<div class='alert alert_success'>" . $accountResult['message'] . "</div>";
                    } else {
                        echo "<p style='color:red;font-size:18px;'>❌ <strong>ACCOUNT CREATION: FAILED!</strong></p>";
                        echo "<div class='alert alert_danger'>" . $accountResult['message'] . "</div>";
                    }
                }
            } else {
                echo "<p style='color:red;'>❌ OTP does not match pending verification</p>";
                echo "<p>Expected: <strong>" . htmlspecialchars($pendingData['otp']) . "</strong></p>";
                echo "<p>Provided: <strong>" . htmlspecialchars($otp) . "</strong></p>";
            }
        } else {
            echo "<p style='color:red;'>❌ No pending verification found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre style='background:#f8d7da;padding:10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
    echo "</div>";
    echo "<hr>";
}

// Form for testing
echo "<div class='mcol_6'>";
echo "<form method='POST' class='debug_form'>";
echo "<table class='tbl_form'>";
echo "<caption><h2>🔍 Debug OTP Verification</h2></caption>";
echo "<tr>";
echo "<td><label>Email Address:</label></td>";
echo "<td><input type='email' name='email' value='bistakaran89@gmail.com' required style='width:100%;padding:8px;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><label>OTP Code:</label></td>";
echo "<td><input type='text' name='otp' value='897470' required style='width:100%;padding:8px;' placeholder='Enter 6-digit OTP'></td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='2' style='text-align:center;padding-top:15px;'>";
echo "<input type='submit' value='🔍 Debug OTP Verification' class='btn_signup'>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "</div>";

include "inc/footer.php";
?>
