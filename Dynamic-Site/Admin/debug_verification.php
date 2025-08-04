<?php
// Debug User Verification Data
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 User Verification Debug</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";

try {
    include '../lib/Database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check tbl_user_verification table structure
    echo "<h3>1. 📋 tbl_user_verification Table Structure</h3>";
    $showColumns = "SHOW COLUMNS FROM tbl_user_verification";
    $columns = $db->select($showColumns);
    
    if ($columns && $columns->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #ddd;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($col = $columns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ tbl_user_verification table not found</p>";
    }
    
    // Check actual data in tbl_user_verification
    echo "<h3>2. 📊 tbl_user_verification Data</h3>";
    $verificationData = "SELECT * FROM tbl_user_verification ORDER BY submitted_at DESC LIMIT 10";
    $verifications = $db->select($verificationData);
    
    if ($verifications && $verifications->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>User ID</th><th>User Type</th><th>Status</th><th>Submitted At</th><th>Citizenship ID</th></tr>";
        while ($ver = $verifications->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $ver['id'] . "</td>";
            echo "<td>" . $ver['user_id'] . "</td>";
            echo "<td>" . $ver['user_type'] . "</td>";
            echo "<td>" . $ver['verification_status'] . "</td>";
            echo "<td>" . $ver['submitted_at'] . "</td>";
            echo "<td>" . ($ver['citizenship_id'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No data in tbl_user_verification</p>";
    }
    
    // Check recent users that require verification
    echo "<h3>3. 👥 Users Requiring Verification</h3>";
    $usersQuery = "SELECT userId, firstName, lastName, userEmail, userLevel, verification_status, requires_verification, created_at 
                   FROM tbl_user 
                   WHERE requires_verification = 1 
                   ORDER BY userId DESC 
                   LIMIT 10";
    $users = $db->select($usersQuery);
    
    if ($users && $users->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>User ID</th><th>Name</th><th>Email</th><th>Level</th><th>Verification Status</th><th>Created</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['userId'] . "</td>";
            echo "<td>" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($user['userEmail']) . "</td>";
            echo "<td>" . $user['userLevel'] . "</td>";
            echo "<td>" . $user['verification_status'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No users requiring verification found</p>";
    }
    
    // Check the actual JOIN query being used
    echo "<h3>4. 🔍 Current JOIN Query Result</h3>";
    $joinQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.created_at as user_created
                  FROM tbl_user_verification uv 
                  JOIN tbl_user u ON uv.user_id = u.userId 
                  WHERE uv.verification_status = 'pending' 
                  ORDER BY uv.submitted_at ASC";
    $joinResult = $db->select($joinQuery);
    
    if ($joinResult && $joinResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>Ver ID</th><th>User ID</th><th>Name from tbl_user</th><th>Email from tbl_user</th><th>User Type</th><th>Status</th></tr>";
        while ($result = $joinResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $result['id'] . "</td>";
            echo "<td>" . $result['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($result['firstName'] . ' ' . $result['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($result['userEmail']) . "</td>";
            echo "<td>" . $result['user_type'] . "</td>";
            echo "<td>" . $result['verification_status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No pending verifications found in JOIN query</p>";
    }
    
    // Check if there's a mismatch
    echo "<h3>5. 🚨 Data Consistency Check</h3>";
    
    // Users who should have verification records but don't
    $missingVerQuery = "SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.verification_status 
                       FROM tbl_user u 
                       LEFT JOIN tbl_user_verification uv ON u.userId = uv.user_id 
                       WHERE u.requires_verification = 1 AND uv.id IS NULL";
    $missingVer = $db->select($missingVerQuery);
    
    if ($missingVer && $missingVer->num_rows > 0) {
        echo "<p style='color: red;'>❌ Found users requiring verification but missing verification records:</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #ddd;'><th>User ID</th><th>Name</th><th>Email</th><th>Level</th><th>Status</th></tr>";
        while ($missing = $missingVer->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $missing['userId'] . "</td>";
            echo "<td>" . htmlspecialchars($missing['firstName'] . ' ' . $missing['lastName']) . "</td>";
            echo "<td>" . htmlspecialchars($missing['userEmail']) . "</td>";
            echo "<td>" . $missing['userLevel'] . "</td>";
            echo "<td>" . $missing['verification_status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: green;'>✅ All users requiring verification have verification records</p>";
    }
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 5px;'>";
    echo "<h4>🔧 Quick Fix Options</h4>";
    echo "<a href='fix_verification_data.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🛠️ Fix Data</a>";
    echo "<a href='verify_users.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 View Verify Users Page</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
