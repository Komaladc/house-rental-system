<?php
// Test the complete user registration flow - New version
session_start();
include "lib/Database.php";

$db = new Database();

echo "<h1>🧪 Complete User Registration Flow Test</h1>";

// Simulate the registration process
if (isset($_POST['test_register'])) {
    echo "<h2>📝 Processing Test Registration...</h2>";
    
    $testData = [
        'fname' => 'Test',
        'lname' => 'Owner',
        'username' => 'testowner' . time(),
        'email' => 'testowner' . time() . '@gmail.com',
        'cellno' => '9876543210',
        'password' => 'password123',
        'level' => '2', // Owner/Agent level
        'address' => 'Test Address'
    ];
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Test Data:</strong><br>";
    foreach ($testData as $key => $value) {
        echo "$key: $value<br>";
    }
    echo "</div>";
    
    // Step 1: Create user with proper status
    $hashedPassword = md5($testData['password']);
    $accountStatus = 0; // 0 = inactive (pending admin approval) for level 2 users
    
    $userQuery = "INSERT INTO tbl_user(firstName, lastName, userName, userImg, userEmail, cellNo, phoneNo, userAddress, userPass, confPass, userLevel, userStatus, created_at) 
                 VALUES('" . mysqli_real_escape_string($db->link, $testData['fname']) . "',
                        '" . mysqli_real_escape_string($db->link, $testData['lname']) . "',
                        '" . mysqli_real_escape_string($db->link, $testData['username']) . "',
                        '',
                        '" . mysqli_real_escape_string($db->link, $testData['email']) . "',
                        '" . mysqli_real_escape_string($db->link, $testData['cellno']) . "',
                        '',
                        '" . mysqli_real_escape_string($db->link, $testData['address']) . "',
                        '$hashedPassword',
                        '$hashedPassword',
                        '" . mysqli_real_escape_string($db->link, $testData['level']) . "',
                        $accountStatus,
                        NOW())";
    
    echo "<h3>Step 1: Creating User Account</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>$userQuery</pre>";
    
    $userId = $db->insert($userQuery);
    if ($userId) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
              ✅ User created successfully! User ID: $userId
              </div>";
        
        // Step 2: Add verification record
        echo "<h3>Step 2: Creating Verification Record</h3>";
        $verificationQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                             VALUES ($userId, '" . mysqli_real_escape_string($db->link, $testData['email']) . "', '" . mysqli_real_escape_string($db->link, $testData['username']) . "', " . $testData['level'] . ", 'Owner', 'pending', NOW())";
        
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>$verificationQuery</pre>";
        
        $verificationId = $db->insert($verificationQuery);
        if ($verificationId) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
                  ✅ Verification record created! Verification ID: $verificationId
                  </div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>
                  ❌ Failed to create verification record: " . $db->link->error . "
                  </div>";
        }
        
        // Step 3: Test the admin dashboard query
        echo "<h3>Step 3: Testing Admin Dashboard Query</h3>";
        $adminQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
                      FROM tbl_user u 
                      LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                      WHERE u.userStatus = 0 AND u.userLevel = 2 
                      ORDER BY u.userId DESC
                      LIMIT 5";
        
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>$adminQuery</pre>";
        
        $adminResult = $db->select($adminQuery);
        if ($adminResult && $adminResult->num_rows > 0) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
                  ✅ Admin query found {$adminResult->num_rows} pending users (including our test user)
                  </div>";
            
            echo "<h4>Users that should appear in admin dashboard:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f1f1f1;'><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Verification</th><th>Created</th></tr>";
            
            while ($user = $adminResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$user['userId']}</td>";
                echo "<td>{$user['firstName']} {$user['lastName']}</td>";
                echo "<td>{$user['userEmail']}</td>";
                echo "<td>" . ($user['userStatus'] == 0 ? 'Pending' : 'Active') . "</td>";
                echo "<td>" . ($user['verification_status'] ?? 'No record') . "</td>";
                echo "<td>" . ($user['created_at'] ?? 'No timestamp') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>
                  ❌ Admin query returned no results! This means users won't appear in the dashboard.
                  </div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>
              ❌ Failed to create user: " . $db->link->error . "
              </div>";
    }
}

// Show current pending users
echo "<h2>📋 Current Pending Users</h2>";
$currentPending = $db->select("SELECT u.*, v.verification_status, v.submitted_at 
                              FROM tbl_user u 
                              LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                              WHERE u.userStatus = 0 AND u.userLevel = 2 
                              ORDER BY u.userId DESC");

if ($currentPending && $currentPending->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
          ✅ Found {$currentPending->num_rows} pending users
          </div>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f1f1f1;'><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Status</th><th>Verification</th></tr>";
    
    while ($user = $currentPending->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['userId']}</td>";
        echo "<td>{$user['firstName']} {$user['lastName']}</td>";
        echo "<td>{$user['userEmail']}</td>";
        echo "<td>{$user['userName']}</td>";
        echo "<td>" . ($user['userStatus'] == 0 ? '⏳ Pending' : '✅ Active') . "</td>";
        echo "<td>" . ($user['verification_status'] ?? 'No record') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>
          ⚠️ No pending users found. Try creating a test user below.
          </div>";
}

// Test form
if (!isset($_POST['test_register'])) {
    echo "<h2>🧪 Create Test User</h2>";
    echo "<form method='POST'>
          <button type='submit' name='test_register' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
          🧪 Create Test Owner User
          </button>
          </form>";
}

echo "<br><hr><br>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='Admin/user_verification.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Admin Verification Dashboard</a></p>";
echo "<p><a href='signup_enhanced.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📝 Test Real Signup Process</a></p>";

echo "<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";
?>
