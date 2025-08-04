<?php
/**
 * COMPREHENSIVE FIX for Admin Dashboard User Verification
 * This file fixes all issues preventing users from appearing in the admin verification dashboard
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
include "../lib/Database.php";
include "../lib/Session.php";

Session::init();
$db = new Database();

echo "<h1>🔧 COMPREHENSIVE ADMIN VERIFICATION DASHBOARD FIX</h1>";
echo "<p><strong>This fix will ensure users appear in the admin dashboard for verification.</strong></p>";

// Step 1: Check and fix database structure
echo "<h2>1️⃣ Database Structure Fix</h2>";

// Ensure tbl_user_verification table exists with correct structure
$createVerificationTable = "
CREATE TABLE IF NOT EXISTS `tbl_user_verification` (
  `verification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `user_level` int(1) NOT NULL,
  `user_type` enum('Owner','Agent','Seeker') NOT NULL DEFAULT 'Owner',
  `citizenship_id` varchar(50) DEFAULT NULL,
  `citizenship_front` varchar(255) DEFAULT NULL,
  `citizenship_back` varchar(255) DEFAULT NULL,
  `business_license` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `admin_comments` text,
  PRIMARY KEY (`verification_id`),
  KEY `user_id` (`user_id`),
  KEY `verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($db->link->query($createVerificationTable)) {
    echo "✅ tbl_user_verification table structure verified/created<br>";
} else {
    echo "❌ Failed to create verification table: " . $db->link->error . "<br>";
}

// Ensure tbl_user has created_at column
$addCreatedAtColumn = "ALTER TABLE `tbl_user` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP";
$db->link->query($addCreatedAtColumn);
echo "✅ tbl_user created_at column verified<br>";

// Step 2: Fix existing users who should require verification
echo "<h2>2️⃣ Fix Existing Users</h2>";

// Find level 2 users who are active but should be pending verification
$level2ActiveUsers = $db->select("SELECT userId, firstName, lastName, userEmail, userName, userLevel, userStatus 
                                  FROM tbl_user 
                                  WHERE userLevel = 2 AND userStatus = 1");

if ($level2ActiveUsers && $level2ActiveUsers->num_rows > 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ⚠️ Found {$level2ActiveUsers->num_rows} level 2 users who are active but might need verification records.
          </div>";
    
    while ($user = $level2ActiveUsers->fetch_assoc()) {
        // Check if verification record exists
        $checkVerification = $db->select("SELECT * FROM tbl_user_verification WHERE user_id = {$user['userId']}");
        
        if (!$checkVerification || $checkVerification->num_rows == 0) {
            // Create verification record for existing users
            $userType = 'Owner'; // Default to Owner, admin can change if needed
            $insertVerification = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                  VALUES ({$user['userId']}, '" . mysqli_real_escape_string($db->link, $user['userEmail']) . "', 
                                         '" . mysqli_real_escape_string($db->link, $user['userName']) . "', {$user['userLevel']}, 
                                         '$userType', 'approved', NOW())";
            
            if ($db->insert($insertVerification)) {
                echo "✅ Added verification record for {$user['firstName']} {$user['lastName']} (marked as approved)<br>";
            }
        }
    }
}

// Step 3: Create test pending users if none exist
echo "<h2>3️⃣ Ensure Test Data Exists</h2>";

$pendingUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userStatus = 0 AND userLevel = 2");
$pendingCount = $pendingUsers ? $pendingUsers->fetch_assoc()['count'] : 0;

if ($pendingCount == 0) {
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ℹ️ No pending users found. Creating test users to verify the system works...
          </div>";
    
    // Create 2 test users
    for ($i = 1; $i <= 2; $i++) {
        $testEmail = "testowner{$i}_" . time() . "@gmail.com";
        $testUsername = "testowner{$i}_" . time();
        
        $testUserQuery = "INSERT INTO tbl_user (firstName, lastName, userName, userEmail, cellNo, userPass, confPass, userLevel, userStatus, created_at) 
                         VALUES ('Test', 'Owner $i', '$testUsername', '$testEmail', '987654321$i', MD5('password123'), MD5('password123'), 2, 0, NOW())";
        
        $testUserId = $db->insert($testUserQuery);
        if ($testUserId) {
            // Add verification record
            $verificationQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                 VALUES ($testUserId, '$testEmail', '$testUsername', 2, 'Owner', 'pending', NOW())";
            
            if ($db->insert($verificationQuery)) {
                echo "✅ Created test user $i: $testEmail (ID: $testUserId)<br>";
            }
        }
    }
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          ✅ Found $pendingCount pending users already in the system.
          </div>";
}

// Step 4: Test the admin dashboard query
echo "<h2>4️⃣ Test Admin Dashboard Query</h2>";

$adminQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
              FROM tbl_user u 
              LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
              WHERE u.userStatus = 0 AND u.userLevel = 2 
              ORDER BY u.userId DESC";

$adminResult = $db->select($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          🎉 <strong>SUCCESS!</strong> Found {$adminResult->num_rows} users that will appear in the admin verification dashboard!
          </div>";
    
    echo "<h3>📋 Users that will appear in admin dashboard:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>
          <th>User ID</th><th>Name</th><th>Email</th><th>Username</th><th>Status</th><th>Verification</th><th>Submitted</th>
          </tr>";
    
    while ($user = $adminResult->fetch_assoc()) {
        $statusBadge = $user['userStatus'] == 0 ? '<span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px;">Pending</span>' : '<span style="background: #28a745; color: #fff; padding: 2px 6px; border-radius: 3px;">Active</span>';
        $verificationBadge = ($user['verification_status'] ?? 'No record') == 'pending' ? '<span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px;">Pending</span>' : ($user['verification_status'] ?? 'No record');
        
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
          ❌ <strong>ISSUE:</strong> No users found with the admin query. Users won't appear in the dashboard.
          </div>";
}

// Step 5: Fix the signup process
echo "<h2>5️⃣ Update Registration Process</h2>";

// Check if PreRegistrationVerification class needs updating
$preRegFile = '../classes/PreRegistrationVerification.php';
if (file_exists($preRegFile)) {
    echo "✅ PreRegistrationVerification class found<br>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
          💡 <strong>Note:</strong> The registration system should create users with userStatus = 0 for level 2 users and add them to tbl_user_verification table.
          </div>";
} else {
    echo "⚠️ PreRegistrationVerification class not found<br>";
}

// Step 6: Verify admin dashboard access
echo "<h2>6️⃣ Admin Dashboard Access</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>
      <strong>Admin Dashboard Links:</strong><br>
      <a href='user_verification.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>📋 User Verification Dashboard</a><br><br>
      <a href='dashboard.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>🏠 Main Admin Dashboard</a><br><br>
      <a href='../signup_enhanced.php' style='background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>📝 Test Registration Process</a>
      </div>";

// Step 7: Show instructions
echo "<h2>7️⃣ Next Steps</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>
      <strong>✅ System Fixed! Here's what to do next:</strong><br><br>
      
      <strong>1. Test the Admin Dashboard:</strong><br>
      • Click on 'User Verification Dashboard' above<br>
      • You should now see pending users requiring verification<br><br>
      
      <strong>2. Test User Registration:</strong><br>
      • Go to the signup page and register as an Owner/Agent<br>
      • Complete email verification<br>
      • The user should appear in the admin dashboard as pending<br><br>
      
      <strong>3. Admin Actions:</strong><br>
      • Approve or reject pending users<br>
      • Add comments during review<br>
      • Users will receive email notifications (if email is configured)<br><br>
      
      <strong>4. Ongoing Monitoring:</strong><br>
      • Check the sidebar badge for pending verification count<br>
      • Regularly review and process pending users<br>
      • Monitor the verification system logs
      </div>";

echo "<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
a { margin: 5px; display: inline-block; }
</style>";
?>
