<?php
include '../lib/Database.php';
$db = new Database();

echo "<h2>🔍 Debug: Checking Agent/Owner Registration Status</h2>";

// Check all users with userLevel 2 or 3
echo "<h3>All Users with userLevel 2 (Owners) or 3 (Agents):</h3>";
$allUsersQuery = "SELECT userId, firstName, lastName, userName, userEmail, userLevel, userStatus, 
                 CASE 
                     WHEN userLevel = 2 THEN '🏠 Property Owner' 
                     WHEN userLevel = 3 THEN '🏢 Real Estate Agent'
                     ELSE '👤 Regular User'
                 END as userType
                 FROM tbl_user 
                 WHERE userLevel IN (2, 3) 
                 ORDER BY userId DESC";

$result = $db->select($allUsersQuery);
if($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Can Login?</th></tr>";
    while($user = $result->fetch_assoc()) {
        $statusColor = $user['userStatus'] == 0 ? 'background: #fff3cd;' : 'background: #d4edda;';
        $statusText = $user['userStatus'] == 0 ? '⏳ PENDING ADMIN APPROVAL' : '✅ ACTIVE';
        $canLogin = $user['userStatus'] == 1 ? '✅ YES' : '❌ NO (Needs Admin Approval)';
        
        echo "<tr style='$statusColor'>";
        echo "<td>{$user['userId']}</td>";
        echo "<td>{$user['firstName']} {$user['lastName']}</td>";
        echo "<td>{$user['userEmail']}</td>";
        echo "<td>{$user['userType']}</td>";
        echo "<td>$statusText</td>";
        echo "<td>$canLogin</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No agents or owners found in the system.</p>";
}

// Check verification records
echo "<h3>Verification Records:</h3>";
$verificationQuery = "SELECT v.*, u.firstName, u.lastName, u.userEmail 
                     FROM tbl_user_verification v 
                     JOIN tbl_user u ON v.user_id = u.userId 
                     ORDER BY v.submitted_at DESC";

$verificationResult = $db->select($verificationQuery);
if($verificationResult && $verificationResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>User</th><th>Email</th><th>Type</th><th>Status</th><th>Submitted</th></tr>";
    while($verification = $verificationResult->fetch_assoc()) {
        $statusColor = '';
        if($verification['verification_status'] == 'pending') $statusColor = 'background: #fff3cd;';
        elseif($verification['verification_status'] == 'approved') $statusColor = 'background: #d4edda;';
        elseif($verification['verification_status'] == 'rejected') $statusColor = 'background: #f8d7da;';
        
        echo "<tr style='$statusColor'>";
        echo "<td>{$verification['user_id']}</td>";
        echo "<td>{$verification['firstName']} {$verification['lastName']}</td>";
        echo "<td>{$verification['userEmail']}</td>";
        echo "<td>{$verification['user_type']}</td>";
        echo "<td>" . ucfirst($verification['verification_status']) . "</td>";
        echo "<td>{$verification['submitted_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No verification records found.</p>";
}

// Count pending users for admin verification
echo "<h3>Summary for Admin Dashboard:</h3>";
$pendingCount = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0 AND (userLevel = 2 OR userLevel = 3)");
$pending = $pendingCount ? $pendingCount->fetch_assoc()['count'] : 0;

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📊 Admin Verification Queue:</h4>";
echo "<p><strong>Pending Approvals:</strong> $pending users waiting for admin verification</p>";
echo "<p><strong>Note:</strong> These users completed OTP verification but need admin approval to access the system.</p>";
echo "</div>";

echo "<h3>🔗 Quick Actions:</h3>";
echo "<p><a href='verify_users.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Go to Admin Verification Page</a></p>";
echo "<p><a href='verify_users_admin_direct.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🚀 Go to Direct Access Page</a></p>";
?>
