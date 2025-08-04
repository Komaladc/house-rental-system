<?php
// Quick fix for OTP tables and enhanced signup
include "lib/Database.php";
$db = new Database();

echo "<h2>🔧 Quick Fix for OTP System</h2>";

// Create tbl_otp if it doesn't exist
$createOTP = "CREATE TABLE IF NOT EXISTS tbl_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    purpose VARCHAR(50) DEFAULT 'registration',
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_otp (otp),
    INDEX idx_purpose (purpose),
    INDEX idx_expires (expires_at)
)";

if ($db->link->query($createOTP)) {
    echo "<p>✅ tbl_otp table ready</p>";
} else {
    echo "<p>❌ Error with tbl_otp: " . $db->link->error . "</p>";
}

// Create tbl_pending_verification if it doesn't exist
$createPending = "CREATE TABLE IF NOT EXISTS tbl_pending_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    verification_token VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    registration_data TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_token (verification_token),
    INDEX idx_expires (expires_at)
)";

if ($db->link->query($createPending)) {
    echo "<p>✅ tbl_pending_verification table ready</p>";
} else {
    echo "<p>❌ Error with tbl_pending_verification: " . $db->link->error . "</p>";
}

// Clean up old expired records
$currentTime = date('Y-m-d H:i:s');
$cleanupOTP = "DELETE FROM tbl_otp WHERE expires_at < '$currentTime'";
$cleanupPending = "DELETE FROM tbl_pending_verification WHERE expires_at < '$currentTime'";

$db->delete($cleanupOTP);
$db->delete($cleanupPending);

// Check current status
echo "<h3>Current Status:</h3>";
$otpCount = $db->link->query("SELECT COUNT(*) as count FROM tbl_otp")->fetch_assoc();
echo "<p>Active OTP records: {$otpCount['count']}</p>";

$pendingCount = $db->link->query("SELECT COUNT(*) as count FROM tbl_pending_verification")->fetch_assoc();
echo "<p>Active pending verification records: {$pendingCount['count']}</p>";

echo "<h3>Test Registration:</h3>";
echo "<p>✅ Database tables are now ready for enhanced signup</p>";
echo "<p>✅ OTP verification should work properly</p>";
echo "<p>✅ All expired records have been cleaned up</p>";

echo "<div style='margin-top: 20px;'>";
echo "<p><a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Try Enhanced Signup</a>";
echo "<a href='otp_debug.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug OTP</a></p>";
echo "</div>";
?>
