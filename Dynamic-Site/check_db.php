<?php
include 'lib/Database.php';
include 'config/config.php';

$db = new Database();

echo "=== Recent User Registrations ===\n";
$users = $db->select("SELECT userId, firstName, lastName, userEmail, userLevel, userStatus, userName FROM tbl_user ORDER BY userId DESC LIMIT 5");
if ($users && $users->num_rows > 0) {
    while ($user = $users->fetch_assoc()) {
        echo "ID: {$user['userId']}, Name: {$user['firstName']} {$user['lastName']}, Email: {$user['userEmail']}, Level: {$user['userLevel']}, Status: {$user['userStatus']}\n";
    }
} else {
    echo "No users found\n";
}

echo "\n=== Pending Verifications ===\n";
$pending = $db->select("SELECT email, verification_token, is_verified, created_at FROM tbl_pending_verification ORDER BY created_at DESC LIMIT 3");
if ($pending && $pending->num_rows > 0) {
    while ($p = $pending->fetch_assoc()) {
        echo "Email: {$p['email']}, Verified: {$p['is_verified']}, Created: {$p['created_at']}\n";
    }
} else {
    echo "No pending verifications\n";
}

echo "\n=== User Verification Records ===\n";
$verifications = $db->select("SELECT user_id, email, user_type, verification_status, citizenship_id, submitted_at FROM tbl_user_verification ORDER BY submitted_at DESC LIMIT 3");
if ($verifications && $verifications->num_rows > 0) {
    while ($v = $verifications->fetch_assoc()) {
        echo "User ID: {$v['user_id']}, Email: {$v['email']}, Type: {$v['user_type']}, Status: {$v['verification_status']}, Citizenship: {$v['citizenship_id']}, Submitted: {$v['submitted_at']}\n";
    }
} else {
    echo "No verification records\n";
}
?>
