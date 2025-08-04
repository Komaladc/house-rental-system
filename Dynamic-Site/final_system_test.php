<?php
include_once 'config/config.php';
include_once 'config/timezone.php';
include_once 'lib/Database.php';
include_once 'classes/EmailOTP.php';
include_once 'classes/PreRegistrationVerification.php';

echo "<h2>🎯 Final System Verification Test</h2>";

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📋 Testing All Components</h3>";

$allTestsPassed = true;
$testResults = [];

// Test 1: Database Connection
try {
    $db = new Database();
    if ($db && $db->link) {
        $testResults[] = "✅ Database Connection: PASS";
    } else {
        $testResults[] = "❌ Database Connection: FAIL";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = "❌ Database Connection: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 2: EmailOTP Class
try {
    $emailOTP = new EmailOTP();
    $testResults[] = "✅ EmailOTP Class: PASS";
} catch (Exception $e) {
    $testResults[] = "❌ EmailOTP Class: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 3: PreRegistrationVerification Class
try {
    $preReg = new PreRegistrationVerification();
    $testResults[] = "✅ PreRegistrationVerification Class: PASS";
} catch (Exception $e) {
    $testResults[] = "❌ PreRegistrationVerification Class: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 4: OTP Generation
try {
    $testOTP = $emailOTP->generateOTP();
    if ($testOTP && strlen($testOTP) == 6 && is_numeric($testOTP)) {
        $testResults[] = "✅ OTP Generation: PASS (Generated: $testOTP)";
    } else {
        $testResults[] = "❌ OTP Generation: FAIL";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = "❌ OTP Generation: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 5: Database Table Structure
try {
    $tableCheck = $db->select("DESCRIBE tbl_otp");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $testResults[] = "✅ Database Table Structure: PASS";
    } else {
        $testResults[] = "❌ Database Table Structure: FAIL";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = "❌ Database Table Structure: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 6: OTP Storage and Retrieval
try {
    $testEmail = "finaltest@example.com";
    $testOTP = "999888";
    
    // Clean up any existing test data
    $db->delete("DELETE FROM tbl_otp WHERE email = '$testEmail'");
    
    // Store OTP
    $storeResult = $emailOTP->storeOTP($testEmail, $testOTP, 'registration');
    if ($storeResult) {
        // Verify OTP
        $verifyResult = $emailOTP->verifyOTP($testEmail, $testOTP, 'registration');
        if ($verifyResult) {
            $testResults[] = "✅ OTP Storage & Verification: PASS";
        } else {
            $testResults[] = "❌ OTP Verification: FAIL";
            $allTestsPassed = false;
        }
    } else {
        $testResults[] = "❌ OTP Storage: FAIL";
        $allTestsPassed = false;
    }
    
    // Clean up test data
    $db->delete("DELETE FROM tbl_otp WHERE email = '$testEmail'");
    
} catch (Exception $e) {
    $testResults[] = "❌ OTP Storage & Verification: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Test 7: Nepal Timezone
try {
    $nepalTime = NepalTime::now();
    if ($nepalTime && strtotime($nepalTime)) {
        $testResults[] = "✅ Nepal Timezone: PASS (Current: $nepalTime)";
    } else {
        $testResults[] = "❌ Nepal Timezone: FAIL";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    $testResults[] = "❌ Nepal Timezone: FAIL - " . $e->getMessage();
    $allTestsPassed = false;
}

// Display all test results
foreach ($testResults as $result) {
    echo "<p>$result</p>";
}

echo "</div>";

// Final verdict
if ($allTestsPassed) {
    echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
    echo "<h2>🎉 ALL TESTS PASSED!</h2>";
    echo "<h3>✅ SYSTEM FULLY OPERATIONAL</h3>";
    echo "<p><strong>The OTP verification system is working perfectly!</strong></p>";
    echo "<p>Ready for production use with real email addresses.</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🚀 What You Can Do Now:</h3>";
    echo "<ul>";
    echo "<li>✅ Test Owner signup with real email addresses</li>";
    echo "<li>✅ Test Agent signup with document uploads</li>";
    echo "<li>✅ Verify OTP codes received via email</li>";
    echo "<li>✅ Check admin dashboard for pending verifications</li>";
    echo "</ul>";
    echo "<p><strong>Links:</strong></p>";
    echo "<p>";
    echo "<a href='signup_enhanced.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>🔗 Test Signup</a>";
    echo "<a href='admin/dashboard.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>👥 Admin Dashboard</a>";
    echo "<a href='manual_otp_test.php' style='background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;'>🧪 Manual OTP Test</a>";
    echo "</p>";
    echo "</div>";
    
} else {
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
    echo "<h2>❌ SOME TESTS FAILED</h2>";
    echo "<p>Please check the failed tests above and fix the issues.</p>";
    echo "</div>";
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Database Status Summary</h3>";

try {
    // Show current OTP records count
    $otpCount = $db->select("SELECT COUNT(*) as count FROM tbl_otp");
    if ($otpCount) {
        $otpRow = $otpCount->fetch_assoc();
        echo "<p>📧 <strong>Total OTP Records:</strong> " . $otpRow['count'] . "</p>";
    }
    
    // Show pending verification count
    $pendingCount = $db->select("SELECT COUNT(*) as count FROM tbl_pending_verification");
    if ($pendingCount) {
        $pendingRow = $pendingCount->fetch_assoc();
        echo "<p>⏳ <strong>Pending Verifications:</strong> " . $pendingRow['count'] . "</p>";
    }
    
    // Show user count
    $userCount = $db->select("SELECT COUNT(*) as count FROM tbl_user");
    if ($userCount) {
        $userRow = $userCount->fetch_assoc();
        echo "<p>👥 <strong>Total Users:</strong> " . $userRow['count'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>⚠️ Could not retrieve database statistics: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
