<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 OTP Verification Debug</h2>";

// Include files the same way as signup form
include "inc/header.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    echo "<h3>🧪 Testing OTP Verification for:</h3>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>OTP:</strong> " . htmlspecialchars($otp) . "</p>";
    
    try {
        $emailOTP = new EmailOTP();
        $preVerification = new PreRegistrationVerification();
        
        // Step 1: Check if OTP exists in database
        echo "<h4>Step 1: Database OTP Check</h4>";
        $currentTime = date('Y-m-d H:i:s');
        
        // Make sure $db is available
        if (!isset($db) || !$db) {
            echo "<p style='color:red;'>❌ Database connection not available</p>";
            return;
        }
        
        $query = "SELECT * FROM tbl_otp 
                 WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' 
                 ORDER BY created_at DESC";
        
        $result = $db->select($query);
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
            echo "<tr><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th><th>Status</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $isExpired = strtotime($row['expires_at']) < strtotime($currentTime);
                $isUsed = $row['is_used'] == 1;
                $isMatch = $row['otp'] === $otp;
                
                $status = '';
                if ($isMatch && !$isExpired && !$isUsed) {
                    $status = '✅ Valid';
                } elseif ($isMatch && $isExpired) {
                    $status = '⏰ Expired';
                } elseif ($isMatch && $isUsed) {
                    $status = '🔒 Used';
                } elseif ($isMatch) {
                    $status = '❓ Unknown Issue';
                } else {
                    $status = '❌ No Match';
                }
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['otp']) . ($isMatch ? ' ✓' : '') . "</td>";
                echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
                echo "<td>" . ($row['is_used'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red;'>❌ No OTP records found for this email</p>";
        }
        
        // Step 2: Test OTP verification
        echo "<h4>Step 2: OTP Verification Test</h4>";
        $otpVerified = $emailOTP->verifyOTP($email, $otp, 'email_verification');
        
        if ($otpVerified) {
            echo "<p style='color:green;'>✅ <strong>OTP Verification: SUCCESS</strong></p>";
        } else {
            echo "<p style='color:red;'>❌ <strong>OTP Verification: FAILED</strong></p>";
        }
        
        // Step 3: Check pending verification
        echo "<h4>Step 3: Pending Verification Check</h4>";
        
        if (!$db) {
            echo "<p style='color:red;'>❌ Database connection not available</p>";
        } else {
            $pendingQuery = "SELECT * FROM tbl_pending_verification 
                            WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' 
                            AND is_verified = 0 
                            ORDER BY created_at DESC LIMIT 1";
        
        $pendingResult = $db->select($pendingQuery);
        
        if ($pendingResult && $pendingResult->num_rows > 0) {
            $pendingData = $pendingResult->fetch_assoc();
            echo "<p>✅ <strong>Pending verification found</strong></p>";
            echo "<table border='1' style='border-collapse:collapse;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Email</td><td>" . htmlspecialchars($pendingData['email']) . "</td></tr>";
            echo "<tr><td>Token</td><td>" . htmlspecialchars($pendingData['verification_token']) . "</td></tr>";
            echo "<tr><td>OTP</td><td>" . htmlspecialchars($pendingData['otp']) . "</td></tr>";
            echo "<tr><td>Expires</td><td>" . htmlspecialchars($pendingData['expires_at']) . "</td></tr>";
            echo "<tr><td>Verified</td><td>" . ($pendingData['is_verified'] ? 'Yes' : 'No') . "</td></tr>";
            echo "</table>";
            
            // Check if OTP matches
            if ($pendingData['otp'] === $otp) {
                echo "<p style='color:green;'>✅ OTP matches pending verification</p>";
            } else {
                echo "<p style='color:red;'>❌ OTP does not match pending verification</p>";
                echo "<p>Expected: " . htmlspecialchars($pendingData['otp']) . "</p>";
                echo "<p>Provided: " . htmlspecialchars($otp) . "</p>";
            }
            
        } else {
            echo "<p style='color:red;'>❌ No pending verification found for this email</p>";
        }
        }
        
        // Step 4: Test complete verification process
        echo "<h4>Step 4: Complete Verification Test</h4>";
        if ($otpVerified && isset($pendingData)) {
            $accountResult = $preVerification->verifyAndCreateAccount($email, $pendingData['verification_token'], $otp);
            
            if ($accountResult['success']) {
                echo "<p style='color:green;'>🎉 <strong>ACCOUNT CREATION: SUCCESS</strong></p>";
                echo "<div style='background:#d4edda;padding:10px;border-left:4px solid #28a745;'>";
                echo $accountResult['message'];
                echo "</div>";
            } else {
                echo "<p style='color:red;'>❌ <strong>ACCOUNT CREATION: FAILED</strong></p>";
                echo "<div style='background:#f8d7da;padding:10px;border-left:4px solid #dc3545;'>";
                echo $accountResult['message'];
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}
?>

<div style="max-width:400px;margin:20px auto;padding:20px;border:1px solid #ddd;">
    <h3>🔍 Debug OTP Verification</h3>
    <p>Enter the email and OTP code to debug the verification process:</p>
    
    <form method="POST">
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" required style="width:100%;padding:8px;" placeholder="Enter email address">
        </p>
        <p>
            <label>OTP Code:</label><br>
            <input type="text" name="otp" required style="width:100%;padding:8px;" placeholder="Enter 6-digit OTP">
        </p>
        <p style="text-align:center;">
            <button type="submit" style="background:#007bff;color:white;padding:10px 20px;border:none;">
                🔍 Debug Verification
            </button>
        </p>
    </form>
</div>

<hr>
<p><a href="signup_with_verification.php">← Back to Signup Form</a></p>

<?php
// Show recent OTPs for debugging
echo "<h3>📋 Recent OTPs (for debugging):</h3>";

try {
    $recentQuery = "SELECT email, otp, purpose, created_at, expires_at, is_used 
                   FROM tbl_otp 
                   ORDER BY created_at DESC 
                   LIMIT 10";
    
    $recentResult = $db->select($recentQuery);
    
    if ($recentResult && $recentResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
        echo "<tr><th>Email</th><th>OTP</th><th>Purpose</th><th>Created</th><th>Expires</th><th>Used</th></tr>";
        
        while ($row = $recentResult->fetch_assoc()) {
            $isExpired = strtotime($row['expires_at']) < time();
            $rowStyle = $isExpired ? 'background:#ffe6e6;' : ($row['is_used'] ? 'background:#f0f0f0;' : '');
            
            echo "<tr style='{$rowStyle}'>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['otp']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['expires_at']) . "</td>";
            echo "<td>" . ($row['is_used'] ? '✓ Used' : '○ Available') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No OTP records found.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error loading OTP records: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
