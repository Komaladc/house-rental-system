<?php
echo "<h1>🏠 House Rental System - Quick Status Check</h1>";

include "lib/Session.php";
include "lib/Database.php";
include "helpers/Format.php";

$db = new Database();
$fm = new Format();

echo "<style>
.status-card { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #007bff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { border-left-color: #28a745; }
.warning { border-left-color: #ffc107; }
.danger { border-left-color: #dc3545; }
.info { border-left-color: #17a2b8; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #f8f9fa; }
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
</style>";

// Check database connection
echo "<div class='status-card success'>";
echo "<h2>🔌 Database Connection</h2>";
try {
    $testQuery = $db->select("SELECT 1");
    echo "<p>✅ <strong>Status:</strong> Connected successfully</p>";
    echo "<p>📊 <strong>Server:</strong> " . mysqli_get_server_info($db->link) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
echo "</div>";

// Check tables exist
$tables = ['tbl_user', 'tbl_user_verification', 'tbl_property', 'tbl_category'];
echo "<div class='status-card info'>";
echo "<h2>🗂️ Database Tables</h2>";
foreach($tables as $table) {
    $checkTable = $db->select("SHOW TABLES LIKE '$table'");
    if($checkTable && $checkTable->num_rows > 0) {
        $count = $db->select("SELECT COUNT(*) as count FROM $table");
        $recordCount = $count ? $count->fetch_assoc()['count'] : 0;
        echo "<p>✅ <strong>$table:</strong> Exists ($recordCount records)</p>";
    } else {
        echo "<p>❌ <strong>$table:</strong> Missing</p>";
    }
}
echo "</div>";

// User Statistics
echo "<div class='status-card'>";
echo "<h2>👥 User Statistics</h2>";

$userStats = array();

// Total users by level
$level1 = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 1");
$userStats['customers'] = $level1 ? $level1->fetch_assoc()['count'] : 0;

$level2 = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2");
$userStats['agents'] = $level2 ? $level2->fetch_assoc()['count'] : 0;

$level3 = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 3");
$userStats['admins'] = $level3 ? $level3->fetch_assoc()['count'] : 0;

// Active vs Inactive
$active = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 1");
$userStats['active'] = $active ? $active->fetch_assoc()['count'] : 0;

$inactive = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0");
$userStats['inactive'] = $inactive ? $inactive->fetch_assoc()['count'] : 0;

echo "<div class='grid'>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px;'>";
echo "<h4>🛒 Customers (Level 1)</h4><h3>" . $userStats['customers'] . "</h3>";
echo "</div>";
echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px;'>";
echo "<h4>🏠 Agents/Owners (Level 2)</h4><h3>" . $userStats['agents'] . "</h3>";
echo "</div>";
echo "<div style='background: #fce4ec; padding: 15px; border-radius: 5px;'>";
echo "<h4>👤 Admins (Level 3)</h4><h3>" . $userStats['admins'] . "</h3>";
echo "</div>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<h4>✅ Active Users</h4><h3>" . $userStats['active'] . "</h3>";
echo "</div>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px;'>";
echo "<h4>⏳ Inactive Users</h4><h3>" . $userStats['inactive'] . "</h3>";
echo "</div>";
echo "</div>";
echo "</div>";

// Verification System Status
echo "<div class='status-card warning'>";
echo "<h2>🔒 Verification System Status</h2>";

// Check verification table structure
$verificationStructure = $db->select("DESCRIBE tbl_user_verification");
if($verificationStructure) {
    echo "<p>✅ <strong>Verification Table:</strong> Exists with proper structure</p>";
    
    // Count verification statuses
    $pending = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'pending' OR verification_status IS NULL");
    $pendingCount = $pending ? $pending->fetch_assoc()['count'] : 0;
    
    $approved = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'approved'");
    $approvedCount = $approved ? $approved->fetch_assoc()['count'] : 0;
    
    $rejected = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'rejected'");
    $rejectedCount = $rejected ? $rejected->fetch_assoc()['count'] : 0;
    
    echo "<div class='grid'>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<h4>⏳ Pending Verification</h4><h3>$pendingCount</h3>";
    echo "</div>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h4>✅ Approved</h4><h3>$approvedCount</h3>";
    echo "</div>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Rejected</h4><h3>$rejectedCount</h3>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<p>❌ <strong>Verification Table:</strong> Missing or inaccessible</p>";
}
echo "</div>";

// Recent Activity
echo "<div class='status-card info'>";
echo "<h2>📈 Recent Activity</h2>";

// Latest registrations
$latestUsers = $db->select("SELECT firstName, lastName, userEmail, userLevel, userStatus, userId FROM tbl_user ORDER BY userId DESC LIMIT 5");
if($latestUsers && $latestUsers->num_rows > 0) {
    echo "<h4>🆕 Latest Registrations:</h4>";
    echo "<table>";
    echo "<tr><th>Name</th><th>Email</th><th>Type</th><th>Status</th></tr>";
    while($user = $latestUsers->fetch_assoc()) {
        $userType = ($user['userLevel'] == 1) ? 'Customer' : (($user['userLevel'] == 2) ? 'Agent/Owner' : 'Admin');
        $status = ($user['userStatus'] == 1) ? '🟢 Active' : '🔴 Inactive';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</td>";
        echo "<td>" . htmlspecialchars($user['userEmail']) . "</td>";
        echo "<td>$userType</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in the system.</p>";
}
echo "</div>";

// System Health Summary
$pendingAgents = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 0");
$pendingAgentsCount = $pendingAgents ? $pendingAgents->fetch_assoc()['count'] : 0;

echo "<div class='status-card " . ($pendingAgentsCount > 0 ? 'warning' : 'success') . "'>";
echo "<h2>🎯 System Health Summary</h2>";

if($pendingAgentsCount > 0) {
    echo "<p>⚠️ <strong>Action Required:</strong> $pendingAgentsCount agents/owners are waiting for admin verification</p>";
    echo "<p>👉 <a href='Admin/verify_users.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Go to Verification Dashboard</a></p>";
} else {
    echo "<p>✅ <strong>All Good:</strong> No pending verifications at this time</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<h4>🔗 Quick Navigation:</h4>";
echo "<a href='Admin/verify_users.php' style='background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 5px;'>👥 User Verification</a>";
echo "<a href='Admin/user_verification.php' style='background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 5px;'>📋 Alt Verification</a>";
echo "<a href='registration_enhanced.php' style='background: #ffc107; color: black; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 5px;'>➕ Test Registration</a>";
echo "<a href='Admin/' style='background: #6c757d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 5px;'>🏠 Admin Panel</a>";
echo "</div>";

echo "</div>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #666; font-style: italic;'>System check completed at " . date('Y-m-d H:i:s') . "</p>";
?>
