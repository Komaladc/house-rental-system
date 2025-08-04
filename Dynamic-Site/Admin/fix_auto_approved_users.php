<?php
include '../lib/Database.php';
$db = new Database();

echo "<h2>🔧 Fix Auto-Approved Users</h2>";

// Find users who are auto-approved but shouldn't be
$autoApprovedQuery = "SELECT u.*, v.verification_status 
                     FROM tbl_user u 
                     LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                     WHERE u.userStatus = 1 AND (u.userLevel = 2 OR u.userLevel = 3)
                     AND (v.verification_status != 'approved' OR v.verification_status IS NULL)";

$autoApproved = $db->select($autoApprovedQuery);

if ($autoApproved && $autoApproved->num_rows > 0) {
    echo "<h3>⚠️ Found {$autoApproved->num_rows} auto-approved users that need to be reset:</h3>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Current Status</th><th>Verification Status</th><th>Action</th></tr>";
    
    while($user = $autoApproved->fetch_assoc()) {
        $userType = ($user['userLevel'] == 2) ? '🏠 Owner' : '🏢 Agent';
        $verificationStatus = $user['verification_status'] ?? 'No record';
        
        echo "<tr style='background: #fff3cd;'>";
        echo "<td>{$user['userId']}</td>";
        echo "<td>{$user['firstName']} {$user['lastName']}</td>";
        echo "<td>{$user['userEmail']}</td>";
        echo "<td>$userType</td>";
        echo "<td>✅ ACTIVE (Should be PENDING)</td>";
        echo "<td>$verificationStatus</td>";
        echo "<td>Reset to Pending</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🔧 Fixing Auto-Approved Users:</h3>";
    
    // Reset auto-approved users to pending status
    $resetQuery = "UPDATE tbl_user SET userStatus = 0 WHERE userStatus = 1 AND (userLevel = 2 OR userLevel = 3)";
    $resetResult = $db->update($resetQuery);
    
    if ($resetResult) {
        echo "<p style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Successfully reset auto-approved users to pending status.</p>";
        
        // Update verification records to pending status
        $updateVerificationQuery = "UPDATE tbl_user_verification 
                                   SET verification_status = 'pending' 
                                   WHERE user_id IN (SELECT userId FROM tbl_user WHERE userStatus = 0 AND (userLevel = 2 OR userLevel = 3))
                                   AND verification_status != 'approved'";
        $updateVerificationResult = $db->update($updateVerificationQuery);
        
        if ($updateVerificationResult) {
            echo "<p style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ Updated verification records to pending status.</p>";
        }
        
    } else {
        echo "<p style='background: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Failed to reset users. Error: " . mysqli_error($db->link) . "</p>";
    }
    
} else {
    echo "<p style='background: #d4edda; padding: 15px; border-radius: 5px;'>✅ No auto-approved users found. All users are properly managed.</p>";
}

// Show current status after fix
echo "<h3>📊 Current Status After Fix:</h3>";

$pendingUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0 AND (userLevel = 2 OR userLevel = 3)");
$pendingCount = $pendingUsers ? $pendingUsers->fetch_assoc()['count'] : 0;

$approvedUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user u JOIN tbl_user_verification v ON u.userId = v.user_id WHERE u.userStatus = 1 AND (u.userLevel = 2 OR u.userLevel = 3) AND v.verification_status = 'approved'");
$approvedCount = $approvedUsers ? $approvedUsers->fetch_assoc()['count'] : 0;

echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;'>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; text-align: center;'>";
echo "<h4>⏳ Pending Admin Approval</h4>";
echo "<h2>$pendingCount</h2>";
echo "<p>Users waiting for verification</p>";
echo "</div>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; text-align: center;'>";
echo "<h4>✅ Properly Approved</h4>";
echo "<h2>$approvedCount</h2>";
echo "<p>Users approved by admin</p>";
echo "</div>";
echo "</div>";

echo "<h3>🔗 Next Steps:</h3>";
echo "<p><a href='verify_users.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Go to Admin Verification Page</a></p>";
echo "<p><a href='debug_verification_process.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Test Verification Process</a></p>";
?>
