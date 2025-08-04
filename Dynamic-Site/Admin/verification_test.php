<?php
session_start();
include '../lib/Database.php';

// Ensure admin session
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';

echo "<h2>🎯 Admin Panel Verification Test</h2>";
echo "<p>This shows the EXACT same data that should appear in verify_users.php</p>";

$db = new Database();

// Use the EXACT same query from verify_users.php
$pendingQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";

$pendingUsers = $db->select($pendingQuery);

echo "<div style='border: 2px solid #007cba; border-radius: 10px; padding: 20px; margin: 20px 0;'>";
echo "<h3>⏳ Pending Verifications (Admin Panel Data)</h3>";

if ($pendingUsers && $pendingUsers->num_rows > 0) {
    echo "<p style='color: green;'>✅ Found <strong>{$pendingUsers->num_rows}</strong> pending verification(s)</p>";
    
    while ($user = $pendingUsers->fetch_assoc()) {
        $fullName = $user['firstName'] . ' ' . $user['lastName'];
        
        // Check if this is Dipesh Tamang
        $isDipesh = (stripos($fullName, 'Dipesh Tamang') !== false);
        
        echo "<div style='border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin: 10px 0; " . ($isDipesh ? "background: #d4edda; border-color: #c3e6cb;" : "background: #f8f9fa;") . "'>";
        
        if ($isDipesh) {
            echo "<h4 style='color: #155724; margin-top: 0;'>🎯 DIPESH TAMANG - FOUND!</h4>";
        } else {
            echo "<h4 style='margin-top: 0;'>👤 {$fullName}</h4>";
        }
        
        echo "<div style='display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;'>";
        echo "<div><strong>📧 Email:</strong> {$user['userEmail']}</div>";
        echo "<div><strong>📱 Phone:</strong> {$user['cellNo']}</div>";
        echo "<div><strong>🏠 Address:</strong> " . ($user['userAddress'] ?? 'Not provided') . "</div>";
        echo "<div><strong>👨‍💼 User Type:</strong> ";
        
        if ($user['userLevel'] == 1) echo '🏠 Property Seeker';
        else if ($user['userLevel'] == 2) echo '🏘️ Property Owner';
        else if ($user['userLevel'] == 3) echo '🏢 Real Estate Agent';
        else echo 'Unknown';
        
        echo "</div>";
        echo "<div><strong>📅 Submitted:</strong> {$user['submitted_at']}</div>";
        echo "<div><strong>🔍 Debug:</strong> User ID: {$user['user_id']} | Verification ID: {$user['verification_id']} | Level: {$user['userLevel']}</div>";
        echo "</div>";
        
        if ($isDipesh) {
            echo "<div style='background: #155724; color: white; padding: 10px; border-radius: 5px; margin-top: 10px; text-align: center;'>";
            echo "<strong>✅ DIPESH TAMANG IS READY FOR APPROVAL!</strong>";
            echo "</div>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ No pending verifications found</p>";
}

echo "</div>";

echo "<h3>🔗 Actions</h3>";
echo "<p>";
echo "<a href='verify_users.php?refresh=" . time() . "' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;'>🔄 OPEN ADMIN PANEL (Force Refresh)</a>";
echo "</p>";

echo "<h4>⚠️ If Dipesh still not showing in admin panel:</h4>";
echo "<ul>";
echo "<li>There might be a browser caching issue</li>";
echo "<li>Try clearing browser cache or using incognito mode</li>";
echo "<li>The admin panel query might have additional filters</li>";
echo "</ul>";
?>
