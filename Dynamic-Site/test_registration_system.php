<?php
include"inc/header.php";

echo "<h1>🧪 Registration & Verification Test</h1>";

echo "<h2>Current System Configuration:</h2>";
echo "<ul>";
echo "<li><strong>Level 1:</strong> Regular Users - Direct activation after email verification</li>";
echo "<li><strong>Level 2:</strong> Owners/Agents - Admin verification required after email verification</li>";
echo "<li><strong>Level 3:</strong> Admin - Not available for registration</li>";
echo "</ul>";

echo "<h2>🔗 Quick Links for Testing:</h2>";
echo "<p><a href='signup.php' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;'>📝 Test Signup</a></p>";
echo "<p><a href='signin.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;'>🔑 Test Sign In</a></p>";

if(Session::get("userLevel") == 3) {
    echo "<p><a href='Admin/user_verification.php' style='background:#ffc107; color:black; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;'>👥 Admin: Manage User Verifications</a></p>";
}

echo "<h2>📊 Database Status:</h2>";

// Check tables exist
$requiredTables = ['tbl_user', 'tbl_pending_verification', 'tbl_otp', 'tbl_user_verification'];
foreach($requiredTables as $table) {
    $checkTable = $db->select("SHOW TABLES LIKE '$table'");
    $exists = ($checkTable && $checkTable->num_rows > 0);
    echo "<p>$table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check pending users
$pendingQuery = "SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0 AND userLevel = 2";
$pendingResult = $db->select($pendingQuery);
$pendingCount = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;

echo "<h3>Pending Verifications: <span style='color: " . ($pendingCount > 0 ? "orange" : "green") . ";'>$pendingCount</span></h3>";

echo "<h2>🎯 How to Test:</h2>";
echo "<ol>";
echo "<li><strong>Regular User Test:</strong>";
echo "<ul>";
echo "<li>Go to signup and select 'Regular User'</li>";
echo "<li>Complete registration and verify email</li>";
echo "<li>Should be able to sign in immediately</li>";
echo "</ul></li>";
echo "<li><strong>Owner/Agent Test:</strong>";
echo "<ul>";
echo "<li>Go to signup and select 'Property Owner' or 'Real Estate Agent'</li>";
echo "<li>Complete registration and verify email</li>";
echo "<li>Account will be created but inactive (status=0)</li>";
echo "<li>Admin must approve from Admin > User Verification</li>";
echo "<li>After approval, user can sign in normally</li>";
echo "</ul></li>";
echo "</ol>";

echo "<p style='background:#f8f9fa; padding:15px; border-radius:5px; margin:20px 0;'>";
echo "<strong>📋 Note:</strong> This system ensures that property owners and agents are properly verified by admin before they can add properties to the platform.";
echo "</p>";

include"inc/footer.php";
?>
