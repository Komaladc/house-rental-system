<?php
// Test registration simulation

// Start session
session_start();

// Include necessary files
include 'lib/Database.php';
include 'lib/Session.php';
include 'helpers/Format.php';
include 'config/config.php';

$db = new Database();

echo "=== Testing Admin Approval Process ===\n\n";

// 1. Check if any users with level 2 have userStatus = 0 (pending approval)
echo "1. Checking for users pending admin approval (level 2, status 0):\n";
$pendingUsers = $db->select("SELECT userId, firstName, lastName, userEmail, userLevel, userStatus FROM tbl_user WHERE userLevel = 2 AND userStatus = 0 ORDER BY userId DESC LIMIT 5");

if ($pendingUsers && $pendingUsers->num_rows > 0) {
    echo "   Found " . $pendingUsers->num_rows . " users pending approval:\n";
    while ($user = $pendingUsers->fetch_assoc()) {
        echo "   - ID: {$user['userId']}, Name: {$user['firstName']} {$user['lastName']}, Email: {$user['userEmail']}, Level: {$user['userLevel']}, Status: {$user['userStatus']}\n";
    }
} else {
    echo "   No users pending approval found.\n";
}

echo "\n2. Checking user verification records:\n";
$verifications = $db->select("SELECT user_id, email, user_type, verification_status, citizenship_id, submitted_at FROM tbl_user_verification ORDER BY submitted_at DESC LIMIT 5");

if ($verifications && $verifications->num_rows > 0) {
    echo "   Found " . $verifications->num_rows . " verification records:\n";
    while ($v = $verifications->fetch_assoc()) {
        echo "   - User ID: {$v['user_id']}, Email: {$v['email']}, Type: {$v['user_type']}, Status: {$v['verification_status']}, Citizenship: {$v['citizenship_id']}\n";
    }
} else {
    echo "   No verification records found.\n";
}

echo "\n3. Checking all users with level 2 (should all be status 0 unless approved):\n";
$level2Users = $db->select("SELECT userId, firstName, lastName, userEmail, userLevel, userStatus FROM tbl_user WHERE userLevel = 2 ORDER BY userId DESC LIMIT 10");

if ($level2Users && $level2Users->num_rows > 0) {
    echo "   Found " . $level2Users->num_rows . " level 2 users:\n";
    while ($user = $level2Users->fetch_assoc()) {
        $statusText = ($user['userStatus'] == 0) ? 'PENDING APPROVAL' : 'ACTIVE (Should be pending!)';
        echo "   - ID: {$user['userId']}, Name: {$user['firstName']} {$user['lastName']}, Status: {$user['userStatus']} ($statusText)\n";
    }
} else {
    echo "   No level 2 users found.\n";
}

echo "\n=== Test Complete ===\n";
?>
