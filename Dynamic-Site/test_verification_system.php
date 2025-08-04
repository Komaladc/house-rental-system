<?php
echo "<h1>🔍 Complete Verification System Test</h1>";

include "lib/Session.php";
Session::chkSession();
include "lib/Database.php";
include "helpers/Format.php";

$db = new Database();
$fm = new Format();

echo "<h2>📊 Current System Status</h2>";

// Check pending users
$pendingQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.submitted_at
                FROM tbl_user u 
                LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                WHERE u.userStatus = 0 AND u.userLevel = 2 
                ORDER BY u.userId DESC";

$pendingResult = $db->select($pendingQuery);

echo "<h3>⏳ Pending Users (userStatus=0, userLevel=2):</h3>";
if ($pendingResult && $pendingResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Level</th><th>Verification Status</th><th>Submitted</th>";
    echo "</tr>";
    
    while($user = $pendingResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['userId'] . "</td>";
        echo "<td>" . $user['firstName'] . " " . $user['lastName'] . "</td>";
        echo "<td>" . $user['userEmail'] . "</td>";
        echo "<td>" . ($user['userStatus'] == 0 ? '🔴 Inactive' : '🟢 Active') . "</td>";
        echo "<td>" . $user['userLevel'] . "</td>";
        echo "<td>" . ($user['verification_status'] ?? 'pending') . "</td>";
        echo "<td>" . ($user['submitted_at'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #28a745;'>✅ No pending users found. All users are either approved or no new registrations.</p>";
}

// Check approved users
$approvedQuery = "SELECT u.*, v.verification_status, v.reviewed_at
                 FROM tbl_user u 
                 JOIN tbl_user_verification v ON u.userId = v.user_id 
                 WHERE v.verification_status = 'approved' 
                 ORDER BY v.reviewed_at DESC 
                 LIMIT 5";

$approvedResult = $db->select($approvedQuery);

echo "<h3>✅ Recently Approved Users:</h3>";
if ($approvedResult && $approvedResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #d4edda;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Approved At</th>";
    echo "</tr>";
    
    while($user = $approvedResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['userId'] . "</td>";
        echo "<td>" . $user['firstName'] . " " . $user['lastName'] . "</td>";
        echo "<td>" . $user['userEmail'] . "</td>";
        echo "<td>" . ($user['userStatus'] == 1 ? '🟢 Active' : '🔴 Inactive') . "</td>";
        echo "<td>" . $user['reviewed_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No approved users found.</p>";
}

// Check rejected users
$rejectedQuery = "SELECT u.*, v.verification_status, v.reviewed_at, v.admin_comments
                 FROM tbl_user u 
                 JOIN tbl_user_verification v ON u.userId = v.user_id 
                 WHERE v.verification_status = 'rejected' 
                 ORDER BY v.reviewed_at DESC 
                 LIMIT 5";

$rejectedResult = $db->select($rejectedQuery);

echo "<h3>❌ Recently Rejected Users:</h3>";
if ($rejectedResult && $rejectedResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8d7da;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Rejected At</th><th>Reason</th>";
    echo "</tr>";
    
    while($user = $rejectedResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['userId'] . "</td>";
        echo "<td>" . $user['firstName'] . " " . $user['lastName'] . "</td>";
        echo "<td>" . $user['userEmail'] . "</td>";
        echo "<td>" . $user['reviewed_at'] . "</td>";
        echo "<td>" . ($user['admin_comments'] ?? 'No reason provided') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No rejected users found.</p>";
}

// Check statistics
echo "<h2>📈 System Statistics</h2>";

$stats = array();

$totalUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2");
$stats['total'] = $totalUsers ? $totalUsers->fetch_assoc()['count'] : 0;

$pendingUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0 AND userLevel = 2");
$stats['pending'] = $pendingUsers ? $pendingUsers->fetch_assoc()['count'] : 0;

$activeUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 1 AND userLevel = 2");
$stats['active'] = $activeUsers ? $activeUsers->fetch_assoc()['count'] : 0;

$approvedCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'approved'");
$stats['approved'] = $approvedCount ? $approvedCount->fetch_assoc()['count'] : 0;

$rejectedCount = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'rejected'");
$stats['rejected'] = $rejectedCount ? $rejectedCount->fetch_assoc()['count'] : 0;

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<h3>👥 Total Agents/Owners</h3>";
echo "<h2 style='color: #007bff;'>" . $stats['total'] . "</h2>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h3>⏳ Pending Approval</h3>";
echo "<h2 style='color: #ffc107;'>" . $stats['pending'] . "</h2>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<h3>✅ Active Users</h3>";
echo "<h2 style='color: #28a745;'>" . $stats['active'] . "</h2>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; border-left: 4px solid #17a2b8;'>";
echo "<h3>✅ Approved</h3>";
echo "<h2 style='color: #17a2b8;'>" . $stats['approved'] . "</h2>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;'>";
echo "<h3>❌ Rejected</h3>";
echo "<h2 style='color: #dc3545;'>" . $stats['rejected'] . "</h2>";
echo "</div>";

echo "</div>";

// Check for any inconsistencies
echo "<h2>🔍 System Health Check</h2>";

$healthChecks = array();

// Check for users without verification records
$noVerificationQuery = "SELECT COUNT(*) as count FROM tbl_user u 
                       LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                       WHERE u.userLevel = 2 AND v.user_id IS NULL";
$noVerification = $db->select($noVerificationQuery);
$noVerificationCount = $noVerification ? $noVerification->fetch_assoc()['count'] : 0;

// Check for active users without approved verification
$activeNotApprovedQuery = "SELECT COUNT(*) as count FROM tbl_user u 
                          LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                          WHERE u.userStatus = 1 AND u.userLevel = 2 AND (v.verification_status != 'approved' OR v.verification_status IS NULL)";
$activeNotApproved = $db->select($activeNotApprovedQuery);
$activeNotApprovedCount = $activeNotApproved ? $activeNotApproved->fetch_assoc()['count'] : 0;

echo "<div style='background: " . ($noVerificationCount > 0 ? "#f8d7da" : "#d4edda") . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>📋 Verification Records:</strong> ";
if ($noVerificationCount > 0) {
    echo "⚠️ Found $noVerificationCount users without verification records!";
} else {
    echo "✅ All users have verification records.";
}
echo "</div>";

echo "<div style='background: " . ($activeNotApprovedCount > 0 ? "#f8d7da" : "#d4edda") . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>🔐 Status Consistency:</strong> ";
if ($activeNotApprovedCount > 0) {
    echo "⚠️ Found $activeNotApprovedCount active users who are not approved!";
} else {
    echo "✅ All active users are properly approved.";
}
echo "</div>";

echo "<h2>🎯 Test Summary</h2>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<h3>✅ Verification System Status: " . (($noVerificationCount == 0 && $activeNotApprovedCount == 0) ? "HEALTHY" : "NEEDS ATTENTION") . "</h3>";
echo "<ul>";
echo "<li>📊 <strong>Total System:</strong> " . $stats['total'] . " agents/owners registered</li>";
echo "<li>⏳ <strong>Pending Approval:</strong> " . $stats['pending'] . " users waiting for admin verification</li>";
echo "<li>✅ <strong>Active Users:</strong> " . $stats['active'] . " users can sign in and add properties</li>";
echo "<li>🔒 <strong>Security:</strong> " . ($stats['pending'] > 0 ? "Manual admin approval required" : "No pending approvals") . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔗 Quick Actions</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;'>👥 Go to Verification Dashboard</a>";
echo "<a href='user_verification.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Alternative Verification View</a>";
echo "<a href='../registration_enhanced.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;'>➕ Test Registration Form</a>";
echo "</p>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #666; font-style: italic;'>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>
