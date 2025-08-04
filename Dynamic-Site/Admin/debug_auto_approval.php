<?php
// Debug the auto-approval issue and user display problem
include "../lib/Database.php";
include "../lib/Session.php";

Session::init();
$db = new Database();

echo "<h1>🐛 Auto-Approval & User Display Debug</h1>";

// Check current user data
echo "<h2>1️⃣ Current User Data Analysis</h2>";

// Get all level 2 users
$allLevel2Users = $db->select("SELECT userId, firstName, lastName, userName, userEmail, userLevel, userStatus, created_at FROM tbl_user WHERE userLevel = 2 ORDER BY userId DESC");

if ($allLevel2Users && $allLevel2Users->num_rows > 0) {
    echo "<h3>All Level 2 Users (Owners/Agents):</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f1f1f1;'><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Status</th><th>Created</th></tr>";
    
    while ($user = $allLevel2Users->fetch_assoc()) {
        $statusText = $user['userStatus'] == 1 ? 
            '<span style="background: #28a745; color: white; padding: 2px 6px; border-radius: 3px;">ACTIVE (Auto-approved?)</span>' : 
            '<span style="background: #ffc107; color: black; padding: 2px 6px; border-radius: 3px;">PENDING</span>';
        
        echo "<tr>
              <td>{$user['userId']}</td>
              <td>{$user['firstName']} {$user['lastName']}</td>
              <td>{$user['userEmail']}</td>
              <td>{$user['userName']}</td>
              <td>$statusText</td>
              <td>" . ($user['created_at'] ?? 'N/A') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No level 2 users found.</p>";
}

// Check verification records
echo "<h2>2️⃣ Verification Records Analysis</h2>";

$verificationRecords = $db->select("SELECT v.*, u.firstName, u.lastName FROM tbl_user_verification v LEFT JOIN tbl_user u ON v.user_id = u.userId ORDER BY v.submitted_at DESC");

if ($verificationRecords && $verificationRecords->num_rows > 0) {
    echo "<h3>All Verification Records:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f1f1f1;'><th>ID</th><th>User ID</th><th>Name</th><th>Email</th><th>Status</th><th>Submitted</th><th>Reviewed</th></tr>";
    
    while ($record = $verificationRecords->fetch_assoc()) {
        $statusBadge = '';
        switch ($record['verification_status']) {
            case 'pending':
                $statusBadge = '<span style="background: #ffc107; color: black; padding: 2px 6px; border-radius: 3px;">PENDING</span>';
                break;
            case 'approved':
                $statusBadge = '<span style="background: #28a745; color: white; padding: 2px 6px; border-radius: 3px;">APPROVED</span>';
                break;
            case 'rejected':
                $statusBadge = '<span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px;">REJECTED</span>';
                break;
        }
        
        echo "<tr>
              <td>{$record['verification_id']}</td>
              <td>{$record['user_id']}</td>
              <td>" . ($record['firstName'] ?? 'N/A') . " " . ($record['lastName'] ?? '') . "</td>
              <td>{$record['email']}</td>
              <td>$statusBadge</td>
              <td>{$record['submitted_at']}</td>
              <td>" . ($record['reviewed_at'] ?? 'Not reviewed') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No verification records found.</p>";
}

// Check what the admin dashboard query returns
echo "<h2>3️⃣ Admin Dashboard Query Test</h2>";

$adminQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
              FROM tbl_user u 
              LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
              WHERE u.userStatus = 0 AND u.userLevel = 2 
              ORDER BY u.userId DESC";

echo "<h3>Query Used by Admin Dashboard:</h3>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>$adminQuery</pre>";

$adminResult = $db->select($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
          ✅ Query returns {$adminResult->num_rows} users for admin dashboard
          </div>";
    
    echo "<h3>Users That Should Appear in Admin Dashboard:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f1f1f1;'><th>ID</th><th>Name</th><th>Email</th><th>User Status</th><th>Verification Status</th></tr>";
    
    while ($user = $adminResult->fetch_assoc()) {
        echo "<tr>
              <td>{$user['userId']}</td>
              <td>{$user['firstName']} {$user['lastName']}</td>
              <td>{$user['userEmail']}</td>
              <td>" . ($user['userStatus'] == 0 ? 'Pending' : 'Active') . "</td>
              <td>" . ($user['verification_status'] ?? 'No record') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>
          ❌ Admin dashboard query returns 0 users! This is why only old users like 'Muhaim Khan' appear.
          </div>";
}

// Check for Muhaim Khan specifically
echo "<h2>4️⃣ Muhaim Khan Investigation</h2>";

$muhaimQuery = $db->select("SELECT * FROM tbl_user WHERE firstName LIKE '%Muhaim%' OR lastName LIKE '%Khan%' OR userName LIKE '%muhaim%'");

if ($muhaimQuery && $muhaimQuery->num_rows > 0) {
    echo "<h3>Muhaim Khan User Record:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f1f1f1;'><th>Field</th><th>Value</th></tr>";
    
    $muhaim = $muhaimQuery->fetch_assoc();
    foreach ($muhaim as $field => $value) {
        echo "<tr><td>$field</td><td>$value</td></tr>";
    }
    echo "</table>";
    
    // Check his verification record
    $muhaimVerification = $db->select("SELECT * FROM tbl_user_verification WHERE user_id = {$muhaim['userId']}");
    if ($muhaimVerification && $muhaimVerification->num_rows > 0) {
        echo "<h3>Muhaim Khan Verification Record:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f1f1f1;'><th>Field</th><th>Value</th></tr>";
        
        $verification = $muhaimVerification->fetch_assoc();
        foreach ($verification as $field => $value) {
            echo "<tr><td>$field</td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No verification record found for Muhaim Khan.</p>";
    }
} else {
    echo "<p>Muhaim Khan user not found.</p>";
}

// Identify the issues
echo "<h2>5️⃣ Issues Identified</h2>";

$issues = [];

// Check for auto-approval issue
$autoApprovedUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 1");
$autoApprovedCount = $autoApprovedUsers ? $autoApprovedUsers->fetch_assoc()['count'] : 0;

if ($autoApprovedCount > 0) {
    $issues[] = "🚨 <strong>Auto-approval Issue:</strong> $autoApprovedCount level 2 users have userStatus = 1 (active) - they should be 0 (pending) until admin approval";
}

// Check for missing verification records
$usersWithoutVerification = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail FROM tbl_user u LEFT JOIN tbl_user_verification v ON u.userId = v.user_id WHERE u.userLevel = 2 AND v.user_id IS NULL");

if ($usersWithoutVerification && $usersWithoutVerification->num_rows > 0) {
    $issues[] = "🚨 <strong>Missing Verification Records:</strong> {$usersWithoutVerification->num_rows} level 2 users don't have verification records";
}

// Check for pending users not appearing in dashboard
$pendingUsersCount = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 0");
$pendingCount = $pendingUsersCount ? $pendingUsersCount->fetch_assoc()['count'] : 0;

if ($pendingCount == 0) {
    $issues[] = "🚨 <strong>No Pending Users:</strong> There are no users with userStatus = 0, so admin dashboard appears empty";
}

if (empty($issues)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
          ✅ No obvious issues detected. The system might be working correctly.
          </div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Issues Found:</h3>";
    foreach ($issues as $issue) {
        echo "<p>$issue</p>";
    }
    echo "</div>";
}

echo "<h2>6️⃣ Recommended Actions</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px;'>
      <strong>To fix these issues:</strong><br><br>
      
      1. <strong>Fix Auto-Approval:</strong> Update the registration process to set userStatus = 0 for level 2 users<br>
      2. <strong>Add Missing Verification Records:</strong> Create verification records for existing level 2 users<br>
      3. <strong>Test Registration:</strong> Try registering a new owner/agent to verify the fix works<br>
      4. <strong>Fix Existing Users:</strong> Reset status of auto-approved users if needed
      </div>";

echo "<br><hr><br>";
echo "<p><strong>🔗 Quick Actions:</strong></p>";
echo "<p><a href='?fix_auto_approval=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Auto-Approval Issue</a></p>";
echo "<p><a href='user_verification.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Go to Admin Dashboard</a></p>";

// Quick fix for auto-approval
if (isset($_GET['fix_auto_approval'])) {
    echo "<h2>🔧 Applying Auto-Approval Fix</h2>";
    
    // Set all level 2 users with status 1 back to status 0 (except those already processed)
    $resetUsersQuery = "UPDATE tbl_user u 
                       LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                       SET u.userStatus = 0 
                       WHERE u.userLevel = 2 
                       AND u.userStatus = 1 
                       AND (v.verification_status IS NULL OR v.verification_status = 'pending')";
    
    if ($db->update($resetUsersQuery)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>
              ✅ Reset auto-approved users back to pending status
              </div>";
    }
    
    // Add verification records for users who don't have them
    $usersNeedingVerification = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userName, u.userEmail, u.userLevel 
                                            FROM tbl_user u 
                                            LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                            WHERE u.userLevel = 2 AND v.user_id IS NULL");
    
    if ($usersNeedingVerification && $usersNeedingVerification->num_rows > 0) {
        while ($user = $usersNeedingVerification->fetch_assoc()) {
            $userType = 'Owner'; // Default to Owner
            $insertVerification = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, submitted_at) 
                                  VALUES ({$user['userId']}, 
                                         '" . mysqli_real_escape_string($db->link, $user['userEmail']) . "', 
                                         '" . mysqli_real_escape_string($db->link, $user['userName']) . "', 
                                         {$user['userLevel']}, 
                                         '$userType', 
                                         'pending', 
                                         NOW())";
            
            if ($db->insert($insertVerification)) {
                echo "✅ Added verification record for {$user['firstName']} {$user['lastName']}<br>";
            }
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>
          🎉 <strong>Fix Complete!</strong> Check the admin dashboard now.
          </div>";
}

echo "<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";
?>
