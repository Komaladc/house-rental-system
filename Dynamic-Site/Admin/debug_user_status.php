<?php
// Debug user status and verification issue
include "../lib/Database.php";
include "../lib/Session.php";

Session::init();

$db = new Database();

echo "<h1>🔍 User Status Debug</h1>";

// Check all users and their status
echo "<h2>📊 All Users in System</h2>";
$allUsers = $db->select("SELECT userId, firstName, lastName, userName, userEmail, userLevel, userStatus, created_at FROM tbl_user ORDER BY userId DESC LIMIT 20");

if ($allUsers && $allUsers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>
          <tr style='background: #f1f1f1;'>
            <th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Level</th><th>Status</th><th>Created</th>
          </tr>";
    
    while($user = $allUsers->fetch_assoc()) {
        $levelText = '';
        switch($user['userLevel']) {
            case 1: $levelText = '🏠 Seeker'; break;
            case 2: $levelText = '🏘️ Owner/Agent'; break;
            case 3: $levelText = '👑 Admin'; break;
            default: $levelText = 'Unknown';
        }
        
        $statusText = $user['userStatus'] == 1 ? '✅ Active' : '⏳ Pending';
        $rowColor = $user['userStatus'] == 0 && $user['userLevel'] == 2 ? 'background: #fff3cd;' : '';
        
        echo "<tr style='$rowColor'>
                <td>{$user['userId']}</td>
                <td>{$user['firstName']} {$user['lastName']}</td>
                <td>{$user['userName']}</td>
                <td>{$user['userEmail']}</td>
                <td>$levelText</td>
                <td>$statusText</td>
                <td>" . ($user['created_at'] ?? 'No timestamp') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No users found in database.</p>";
}

// Check pending verifications (what the admin dashboard should show)
echo "<h2>⏳ Users Pending Admin Verification (Should appear in admin dashboard)</h2>";
$pendingUsers = $db->select("SELECT u.*, v.verification_status, v.submitted_at 
                            FROM tbl_user u 
                            LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                            WHERE u.userStatus = 0 AND u.userLevel = 2 
                            ORDER BY u.userId DESC");

if ($pendingUsers && $pendingUsers->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ✅ <strong>Found " . $pendingUsers->num_rows . " users that should appear in admin dashboard!</strong>
          </div>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>
          <tr style='background: #fff3cd;'>
            <th>User ID</th><th>Name</th><th>Email</th><th>User Status</th><th>Verification Status</th><th>Submitted</th>
          </tr>";
    
    while($user = $pendingUsers->fetch_assoc()) {
        echo "<tr>
                <td>{$user['userId']}</td>
                <td>{$user['firstName']} {$user['lastName']}</td>
                <td>{$user['userEmail']}</td>
                <td>" . ($user['userStatus'] == 0 ? '❌ Inactive' : '✅ Active') . "</td>
                <td>" . ($user['verification_status'] ?? 'No record') . "</td>
                <td>" . ($user['submitted_at'] ?? 'N/A') . "</td>
              </tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
          <strong>💡 Solution:</strong> These users should appear in the admin verification dashboard at 
          <a href='user_verification.php'>Admin/user_verification.php</a>
          </div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ❌ <strong>No pending users found!</strong><br>
          This means either:<br>
          • No users have signed up as owners/agents recently<br>
          • All users are already approved<br>
          • There's an issue with the registration process
          </div>";
}

// Check verification table
echo "<h2>📋 User Verification Table Records</h2>";
$verificationRecords = $db->select("SELECT * FROM tbl_user_verification ORDER BY submitted_at DESC LIMIT 10");

if ($verificationRecords && $verificationRecords->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>
          <tr style='background: #f1f1f1;'>
            <th>ID</th><th>User ID</th><th>Email</th><th>User Type</th><th>Status</th><th>Submitted</th><th>Reviewed</th>
          </tr>";
    
    while($record = $verificationRecords->fetch_assoc()) {
        echo "<tr>
                <td>{$record['verification_id']}</td>
                <td>{$record['user_id']}</td>
                <td>{$record['email']}</td>
                <td>{$record['user_type']}</td>
                <td>{$record['verification_status']}</td>
                <td>{$record['submitted_at']}</td>
                <td>" . ($record['reviewed_at'] ?? 'Not reviewed') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No records found in tbl_user_verification table.</p>";
}

// Quick test to create a sample pending user
echo "<h2>🧪 Quick Test - Create Sample User</h2>";
echo "<p><a href='?create_test=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Test Owner User</a></p>";

if (isset($_GET['create_test'])) {
    // Create a test user that should appear in admin dashboard
    $testEmail = "test_owner_" . time() . "@example.com";
    $testQuery = "INSERT INTO tbl_user (firstName, lastName, userName, userEmail, cellNo, userPass, confPass, userLevel, userStatus) 
                  VALUES ('Test', 'Owner', 'testowner', '$testEmail', '1234567890', MD5('password'), MD5('password'), 2, 0)";
    
    $testUserId = $db->insert($testQuery);
    if ($testUserId) {
        // Also add to verification table
        $verifyQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                       VALUES ($testUserId, '$testEmail', 'testowner', 2, 'Owner', 'pending', NOW())";
        $db->insert($verifyQuery);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
              ✅ Test user created! UserID: $testUserId<br>
              Email: $testEmail<br>
              This user should now appear in the admin verification dashboard.
              </div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>
              ❌ Failed to create test user.
              </div>";
    }
}

echo "<br><hr><br>";
echo "<p><strong>🔗 Navigation:</strong></p>";
echo "<p><a href='user_verification.php'>📋 Go to Admin Verification Dashboard</a></p>";
echo "<p><a href='dashboard.php'>🏠 Back to Admin Dashboard</a></p>";
?>
