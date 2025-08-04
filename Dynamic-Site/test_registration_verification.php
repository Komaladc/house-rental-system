<?php
// Test User Registration and Verification Record Creation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test User Registration and Verification</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    include 'lib/Database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check latest user registrations
    echo "<h3>1. 📋 Latest User Registrations</h3>";
    $latestUsers = "SELECT userId, firstName, lastName, userEmail, userLevel, verification_status, requires_verification, status, created_at 
                   FROM tbl_user 
                   ORDER BY userId DESC 
                   LIMIT 10";
    $users = $db->select($latestUsers);
    
    if ($users && $users->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>Name</th><th>Email</th><th>Level</th><th>Ver Status</th><th>Requires Ver</th><th>Active</th><th>Created</th></tr>";
        while ($user = $users->fetch_assoc()) {
            $levelText = '';
            switch($user['userLevel']) {
                case 1: $levelText = 'Seeker'; break;
                case 2: $levelText = 'Owner'; break;
                case 3: $levelText = 'Agent'; break;
            }
            
            echo "<tr>";
            echo "<td>" . $user['userId'] . "</td>";
            echo "<td>" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($user['userEmail']) . "</td>";
            echo "<td>" . $levelText . "</td>";
            echo "<td>" . $user['verification_status'] . "</td>";
            echo "<td>" . ($user['requires_verification'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($user['status'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check verification records
    echo "<h3>2. 📋 Latest Verification Records</h3>";
    $latestVerifications = "SELECT uv.*, u.firstName, u.lastName, u.userEmail 
                           FROM tbl_user_verification uv 
                           JOIN tbl_user u ON uv.user_id = u.userId 
                           ORDER BY uv.id DESC 
                           LIMIT 10";
    $verifications = $db->select($latestVerifications);
    
    if ($verifications && $verifications->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>Ver ID</th><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Citizenship ID</th><th>Status</th><th>Submitted</th></tr>";
        while ($ver = $verifications->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $ver['id'] . "</td>";
            echo "<td>" . $ver['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($ver['firstName'] . ' ' . $ver['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($ver['userEmail']) . "</td>";
            echo "<td>" . ucfirst($ver['user_type']) . "</td>";
            echo "<td>" . htmlspecialchars($ver['citizenship_id'] ?? 'N/A') . "</td>";
            echo "<td>" . ucfirst($ver['verification_status']) . "</td>";
            echo "<td>" . $ver['submitted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No verification records found</p>";
    }
    
    // Check for mismatches
    echo "<h3>3. 🔍 Data Verification Check</h3>";
    
    // Users requiring verification but without verification records
    $missingVerQuery = "SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel 
                       FROM tbl_user u 
                       LEFT JOIN tbl_user_verification uv ON u.userId = uv.user_id 
                       WHERE u.requires_verification = 1 
                       AND u.userLevel IN (2, 3) 
                       AND uv.id IS NULL";
    $missingVer = $db->select($missingVerQuery);
    
    if ($missingVer && $missingVer->num_rows > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ Users Missing Verification Records</h4>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #ddd;'><th>User ID</th><th>Name</th><th>Email</th><th>Level</th></tr>";
        while ($missing = $missingVer->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $missing['userId'] . "</td>";
            echo "<td>" . htmlspecialchars($missing['firstName'] . ' ' . $missing['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($missing['userEmail']) . "</td>";
            echo "<td>" . $missing['userLevel'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<a href='admin/fix_verification_data.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Missing Records</a>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ All users requiring verification have verification records</p>";
    }
    
    // Verification records without corresponding users
    $orphanedQuery = "SELECT uv.id, uv.user_id, uv.email, uv.user_type 
                     FROM tbl_user_verification uv 
                     LEFT JOIN tbl_user u ON uv.user_id = u.userId 
                     WHERE u.userId IS NULL";
    $orphaned = $db->select($orphanedQuery);
    
    if ($orphaned && $orphaned->num_rows > 0) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🗑️ Orphaned Verification Records</h4>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #ddd;'><th>Ver ID</th><th>User ID</th><th>Email</th><th>Type</th></tr>";
        while ($orph = $orphaned->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $orph['id'] . "</td>";
            echo "<td>" . $orph['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($orph['email']) . "</td>";
            echo "<td>" . $orph['user_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<a href='admin/fix_verification_data.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🗑️ Clean Orphaned Records</a>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ No orphaned verification records found</p>";
    }
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px;'>";
    echo "<h4>🚀 Quick Actions</h4>";
    echo "<a href='signup_enhanced.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Test New Registration</a>";
    echo "<a href='admin/verify_users.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Verify Users</a>";
    echo "<a href='admin/fix_verification_data.php' style='background: #fd7e14; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Data</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
