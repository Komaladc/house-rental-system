<?php
// Complete Fix for Admin Dashboard User Verification Issue
include'../lib/Session.php';
Session::chkSession();
include'../lib/Database.php';

/*========================
Admin Access Control
========================*/
if(Session::get("userLevel") != 3){
    echo"<script>window.location='../index.php'</script>";
}

$db = new Database();

echo "<h1>🔧 Admin Dashboard User Verification Fix</h1>";

// Step 1: Check current database structure
echo "<h2>1️⃣ Database Structure Check</h2>";

// Check if required tables exist
$tables = ['tbl_user', 'tbl_user_verification'];
foreach($tables as $table) {
    $checkTable = $db->select("SHOW TABLES LIKE '$table'");
    if($checkTable && $checkTable->num_rows > 0) {
        echo "✅ Table $table exists<br>";
    } else {
        echo "❌ Table $table missing<br>";
    }
}

// Check tbl_user structure
echo "<h3>tbl_user Table Structure:</h3>";
$userStructure = $db->select("DESCRIBE tbl_user");
if($userStructure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    while($col = $userStructure->fetch_assoc()) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
}

// Step 2: Check existing pending users
echo "<h2>2️⃣ Current Pending Users</h2>";
$pendingQuery = "SELECT u.*, v.verification_status, v.submitted_at 
                FROM tbl_user u 
                LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                WHERE u.userStatus = 0 AND u.userLevel = 2 
                ORDER BY u.userId DESC";

$pendingResult = $db->select($pendingQuery);

if ($pendingResult && $pendingResult->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
          ✅ Found {$pendingResult->num_rows} pending users that should appear in admin dashboard
          </div>";
    
    while($user = $pendingResult->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>
              <strong>{$user['firstName']} {$user['lastName']}</strong> ({$user['userEmail']})<br>
              User Status: " . ($user['userStatus'] == 0 ? 'Inactive' : 'Active') . "<br>
              Verification Status: " . ($user['verification_status'] ?? 'No record') . "
              </div>";
    }
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>
          ⚠️ No pending users found. Let's create a test user to verify the system works.
          </div>";
}

// Step 3: Fix potential issues
echo "<h2>3️⃣ System Fixes</h2>";

if(isset($_GET['fix_system'])) {
    echo "<h3>Applying Fixes...</h3>";
    
    // Ensure tbl_user_verification table exists with correct structure
    $createVerificationTable = "
    CREATE TABLE IF NOT EXISTS `tbl_user_verification` (
      `verification_id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `email` varchar(255) NOT NULL,
      `userName` varchar(100) DEFAULT NULL,
      `user_level` int(1) NOT NULL,
      `user_type` enum('Owner','Agent') NOT NULL,
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
      FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`userId`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if($db->link->query($createVerificationTable)) {
        echo "✅ User verification table created/updated<br>";
    } else {
        echo "❌ Failed to create verification table: " . $db->link->error . "<br>";
    }
    
    // Create a test pending user if none exist
    if (!$pendingResult || $pendingResult->num_rows == 0) {
        $testEmail = "test_owner_" . time() . "@gmail.com";
        $testUserQuery = "INSERT INTO tbl_user (firstName, lastName, userName, userEmail, cellNo, userPass, confPass, userLevel, userStatus, created_at) 
                         VALUES ('John', 'Doe', 'johndoe', '$testEmail', '9876543210', MD5('password123'), MD5('password123'), 2, 0, NOW())";
        
        $testUserId = $db->insert($testUserQuery);
        if($testUserId) {
            // Add verification record
            $verificationQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                 VALUES ($testUserId, '$testEmail', 'johndoe', 2, 'Owner', 'pending', NOW())";
            
            if($db->insert($verificationQuery)) {
                echo "✅ Test user created successfully (ID: $testUserId)<br>";
            } else {
                echo "❌ Failed to create verification record<br>";
            }
        } else {
            echo "❌ Failed to create test user<br>";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>
          ✅ <strong>System fixes applied!</strong><br>
          Now check the admin verification dashboard to see if users appear.
          </div>";
}

// Step 4: Provide action buttons
echo "<h2>4️⃣ Actions</h2>";

if(!isset($_GET['fix_system'])) {
    echo "<p><a href='?fix_system=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Apply System Fixes</a></p>";
}

echo "<p><a href='user_verification.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Go to User Verification Dashboard</a></p>";

// Step 5: Show the verification query that the dashboard uses
echo "<h2>5️⃣ Dashboard Query Debug</h2>";
echo "<p>The admin dashboard uses this query to find pending users:</p>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>
SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
FROM tbl_user u 
LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
WHERE u.userStatus = 0 AND u.userLevel = 2 
ORDER BY u.userId DESC
</pre>";

$queryResult = $db->select("SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
                           FROM tbl_user u 
                           LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                           WHERE u.userStatus = 0 AND u.userLevel = 2 
                           ORDER BY u.userId DESC");

if($queryResult && $queryResult->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
          ✅ Query returns {$queryResult->num_rows} users - these should appear in the dashboard!
          </div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>
          ❌ Query returns 0 users - this is why the dashboard appears empty!
          </div>";
}
?>

<style>
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
