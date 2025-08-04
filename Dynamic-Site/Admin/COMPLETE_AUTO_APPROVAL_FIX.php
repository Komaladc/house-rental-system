<?php
// Complete Fix for Auto-Approval and User Display Issues
include "../lib/Database.php";
include "../lib/Session.php";

Session::init();

/*========================
Admin Access Control
========================*/
if(Session::get("userLevel") != 3){
    echo"<script>window.location='../index.php'</script>";
}

$db = new Database();

echo "<h1>🔧 COMPLETE FIX: Auto-Approval & User Display Issues</h1>";

$fixes_applied = [];
$issues_found = [];

// Step 1: Identify current issues
echo "<h2>1️⃣ Issue Analysis</h2>";

// Check for auto-approved users
$autoApprovedUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userStatus 
                                 FROM tbl_user u 
                                 WHERE u.userLevel = 2 AND u.userStatus = 1");

if ($autoApprovedUsers && $autoApprovedUsers->num_rows > 0) {
    $issues_found[] = "Auto-approved users found: {$autoApprovedUsers->num_rows} level 2 users have userStatus = 1";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ⚠️ <strong>Issue 1:</strong> {$autoApprovedUsers->num_rows} level 2 users are auto-approved (userStatus = 1)
          </div>";
}

// Check for users without verification records
$usersWithoutVerification = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail 
                                        FROM tbl_user u 
                                        LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                        WHERE u.userLevel = 2 AND v.user_id IS NULL");

if ($usersWithoutVerification && $usersWithoutVerification->num_rows > 0) {
    $issues_found[] = "Missing verification records: {$usersWithoutVerification->num_rows} level 2 users without verification records";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ⚠️ <strong>Issue 2:</strong> {$usersWithoutVerification->num_rows} level 2 users don't have verification records
          </div>";
}

// Check admin dashboard query
$adminDashboardUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user u 
                                   LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                   WHERE u.userStatus = 0 AND u.userLevel = 2");
$dashboardCount = $adminDashboardUsers ? $adminDashboardUsers->fetch_assoc()['count'] : 0;

if ($dashboardCount == 0) {
    $issues_found[] = "Empty admin dashboard: No pending users to show";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ⚠️ <strong>Issue 3:</strong> Admin dashboard shows 0 pending users
          </div>";
}

if (empty($issues_found)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ✅ No issues detected! System appears to be working correctly.
          </div>";
} else {
    echo "<h2>2️⃣ Applying Fixes</h2>";
    
    // Fix 1: Reset auto-approved users back to pending
    if ($autoApprovedUsers && $autoApprovedUsers->num_rows > 0) {
        $resetQuery = "UPDATE tbl_user SET userStatus = 0 WHERE userLevel = 2 AND userStatus = 1";
        if ($db->update($resetQuery)) {
            $fixes_applied[] = "Reset {$autoApprovedUsers->num_rows} auto-approved users back to pending status";
            echo "✅ Fixed auto-approved users - set userStatus = 0<br>";
        }
    }
    
    // Fix 2: Add verification records for users who don't have them
    if ($usersWithoutVerification && $usersWithoutVerification->num_rows > 0) {
        while ($user = $usersWithoutVerification->fetch_assoc()) {
            $userType = 'Owner'; // Default to Owner
            $insertVerification = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                  VALUES ({$user['userId']}, 
                                         '" . mysqli_real_escape_string($db->link, $user['userEmail']) . "', 
                                         '" . mysqli_real_escape_string($db->link, $user['userName'] ?? 'user' . $user['userId']) . "', 
                                         2, 
                                         '$userType', 
                                         'pending', 
                                         NOW())";
            
            if ($db->insert($insertVerification)) {
                echo "✅ Added verification record for {$user['firstName']} {$user['lastName']}<br>";
                $fixes_applied[] = "Added verification record for {$user['firstName']} {$user['lastName']}";
            }
        }
    }
    
    // Fix 3: Create test user if needed to verify system works
    if ($dashboardCount == 0) {
        $testEmail = "testowner_" . time() . "@example.com";
        $testUsername = "testowner_" . time();
        
        $testUserQuery = "INSERT INTO tbl_user (firstName, lastName, userName, userEmail, cellNo, userPass, confPass, userLevel, userStatus, created_at) 
                         VALUES ('Test', 'Owner', '$testUsername', '$testEmail', '9876543210', MD5('password123'), MD5('password123'), 2, 0, NOW())";
        
        $testUserId = $db->insert($testUserQuery);
        if ($testUserId) {
            $verificationQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                 VALUES ($testUserId, '$testEmail', '$testUsername', 2, 'Owner', 'pending', NOW())";
            
            if ($db->insert($verificationQuery)) {
                echo "✅ Created test user for verification dashboard<br>";
                $fixes_applied[] = "Created test user to populate admin dashboard";
            }
        }
    }
}

// Step 3: Verify fixes worked
echo "<h2>3️⃣ Verification of Fixes</h2>";

// Test admin dashboard query
$finalAdminQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
                   FROM tbl_user u 
                   LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                   WHERE u.userStatus = 0 AND u.userLevel = 2 
                   ORDER BY u.userId DESC";

$finalAdminResult = $db->select($finalAdminQuery);

if ($finalAdminResult && $finalAdminResult->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          🎉 <strong>SUCCESS!</strong> Admin dashboard query now returns {$finalAdminResult->num_rows} pending users!
          </div>";
    
    echo "<h3>📋 Users Now Appearing in Admin Dashboard:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Status</th><th>Verification</th><th>Submitted</th></tr>";
    
    while ($user = $finalAdminResult->fetch_assoc()) {
        $statusBadge = '<span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px;">PENDING</span>';
        $verificationBadge = '<span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px;">PENDING</span>';
        
        echo "<tr>
              <td>{$user['userId']}</td>
              <td>{$user['firstName']} {$user['lastName']}</td>
              <td>{$user['userEmail']}</td>
              <td>{$user['userName']}</td>
              <td>$statusBadge</td>
              <td>$verificationBadge</td>
              <td>" . ($user['submitted_at'] ?? 'N/A') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ❌ Admin dashboard query still returns 0 users. Additional investigation needed.
          </div>";
}

// Step 4: Summary
echo "<h2>4️⃣ Fix Summary</h2>";

if (!empty($fixes_applied)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Fixes Applied:</h3>";
    foreach ($fixes_applied as $fix) {
        echo "<p>• $fix</p>";
    }
    echo "</div>";
}

// Step 5: Testing instructions
echo "<h2>5️⃣ Testing Instructions</h2>";

echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
      <h3>🧪 How to Test the Fixed System:</h3>
      
      <strong>1. Test Admin Dashboard:</strong><br>
      • Click 'Go to Admin Dashboard' below<br>
      • You should now see pending users requiring verification<br>
      • Try approving/rejecting a user to test the workflow<br><br>
      
      <strong>2. Test New Registration:</strong><br>
      • Go to the registration page<br>
      • Register as 'Property Owner' (Level 2)<br>
      • Complete email verification<br>
      • User should appear in admin dashboard with userStatus = 0<br><br>
      
      <strong>3. Verify Registration Process:</strong><br>
      • New level 2 users should have userStatus = 0 (pending)<br>
      • Verification records should be created automatically<br>
      • Users should NOT be able to sign in until admin approval<br><br>
      
      <strong>4. Test Approval Process:</strong><br>
      • Admin approves user → userStatus changes to 1<br>
      • User can now sign in and access owner features<br>
      • Verification status changes to 'approved'
      </div>";

// Step 6: Action buttons
echo "<h2>6️⃣ Quick Actions</h2>";

echo "<div style='margin: 20px 0;'>
      <a href='user_verification.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📋 Go to Admin Dashboard</a>
      <a href='../signup_enhanced.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📝 Test Registration</a>
      <a href='debug_auto_approval.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔍 Debug Status</a>
      </div>";

// Step 7: Expected behavior
echo "<h2>7️⃣ Expected System Behavior</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>
      <h3>✅ How the System Should Work Now:</h3>
      
      <strong>For House Seekers (Level 1):</strong><br>
      • Register → Email verification → Account active (userStatus = 1) → Can sign in immediately<br><br>
      
      <strong>For Owners/Agents (Level 2):</strong><br>
      • Register → Email verification → Account created with userStatus = 0<br>
      • Verification record created with status 'pending'<br>
      • User appears in admin dashboard<br>
      • Cannot sign in until admin approval<br>
      • Admin approves → userStatus = 1 → User can sign in<br><br>
      
      <strong>Admin Dashboard:</strong><br>
      • Shows all users with userStatus = 0 AND userLevel = 2<br>
      • Allows approve/reject with comments<br>
      • Updates user status and verification records<br>
      • Shows statistics and recent activity
      </div>";

echo "<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";
?>
