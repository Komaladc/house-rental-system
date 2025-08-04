<?php
require_once 'lib/Database.php';

$db = new Database();
$email = "thekomalad@gmail.com"; // Your email

echo "=== OTP Verification Debug Analysis ===\n\n";

echo "1. Checking pending verification records:\n";
$pendingQuery = "SELECT * FROM tbl_pending_verification WHERE email = '$email' ORDER BY created_at DESC LIMIT 3";
$result = $db->select($pendingQuery);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "  - ID: {$row['id']}\n";
        echo "    Email: {$row['email']}\n";
        echo "    OTP: {$row['otp']}\n";
        echo "    Token: {$row['verification_token']}\n";
        echo "    Expires: {$row['expires_at']}\n";
        echo "    Is Verified: {$row['is_verified']}\n";
        echo "    Created: {$row['created_at']}\n\n";
    }
} else {
    echo "  No pending verification records found.\n\n";
}

echo "2. Checking OTP table records:\n";
$otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' ORDER BY created_at DESC LIMIT 3";
$result = $db->select($otpQuery);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "  - ID: {$row['id']}\n";
        echo "    Email: {$row['email']}\n";
        echo "    OTP: {$row['otp']}\n";
        echo "    Purpose: {$row['purpose']}\n";
        echo "    Expires: {$row['expires_at']}\n";
        echo "    Is Used: {$row['is_used']}\n";
        echo "    Created: {$row['created_at']}\n\n";
    }
} else {
    echo "  No OTP records found.\n\n";
}

echo "3. Current server time:\n";
echo "  MySQL NOW(): ";
$timeResult = $db->select("SELECT NOW() as current_time");
if ($timeResult && $timeResult->num_rows > 0) {
    $timeRow = $timeResult->fetch_assoc();
    echo $timeRow['current_time'] . "\n\n";
}

echo "4. Checking if tables exist:\n";
$tables = ['tbl_pending_verification', 'tbl_otp', 'tbl_user', 'tbl_user_verification'];
foreach ($tables as $table) {
    $checkQuery = "SHOW TABLES LIKE '$table'";
    $result = $db->select($checkQuery);
    echo "  $table: " . ($result && $result->num_rows > 0 ? "EXISTS" : "NOT FOUND") . "\n";
}

echo "\n=== POTENTIAL ISSUES ===\n";

// Check if the verification process is looking in the wrong place
$latestPending = $db->select("SELECT * FROM tbl_pending_verification WHERE email = '$email' ORDER BY created_at DESC LIMIT 1");
$latestOtp = $db->select("SELECT * FROM tbl_otp WHERE email = '$email' ORDER BY created_at DESC LIMIT 1");

if ($latestPending && $latestPending->num_rows > 0) {
    $pendingData = $latestPending->fetch_assoc();
    echo "1. Latest pending verification OTP: {$pendingData['otp']}\n";
}

if ($latestOtp && $latestOtp->num_rows > 0) {
    $otpData = $latestOtp->fetch_assoc();
    echo "2. Latest OTP table OTP: {$otpData['otp']}\n";
}

echo "\n=== RECOMMENDED FIX ===\n";
echo "The verification process should check BOTH:\n";
echo "- The OTP in tbl_pending_verification\n";
echo "- The OTP in tbl_otp table\n";
echo "\nOne of these might be missing or incorrect.\n";
?>
