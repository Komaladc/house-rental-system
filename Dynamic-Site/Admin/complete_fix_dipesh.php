<?php
include '../lib/Database.php';

echo "<h2>🔧 COMPLETE FIX for Dipesh Tamang Verification</h2>";

$db = new Database();

// Step 1: Find ALL users with name Dipesh Tamang
echo "<h3>Step 1: Find Dipesh Tamang in tbl_user</h3>";
$findDipeshQuery = "SELECT * FROM tbl_user WHERE (firstName LIKE '%Dipesh%' OR lastName LIKE '%Tamang%') OR userEmail LIKE '%dipesh%'";
$dipeshUsers = $db->select($findDipeshQuery);

if ($dipeshUsers && $dipeshUsers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>User Level</th><th>Status</th></tr>";
    $dipeshUserId = null;
    while ($user = $dipeshUsers->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['userId']}</td>";
        echo "<td>{$user['firstName']}</td>";
        echo "<td>{$user['lastName']}</td>";
        echo "<td>{$user['userEmail']}</td>";
        echo "<td>{$user['userLevel']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
        
        // Find the correct Dipesh Tamang
        if ($user['firstName'] === 'Dipesh' && $user['lastName'] === 'Tamang') {
            $dipeshUserId = $user['userId'];
            $dipeshEmail = $user['userEmail'];
            $dipeshLevel = $user['userLevel'];
        }
    }
    echo "</table>";
    
    if ($dipeshUserId) {
        echo "<p style='color: green;'>✅ Found correct Dipesh Tamang: User ID <strong>$dipeshUserId</strong>, Email: <strong>$dipeshEmail</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ No exact match for 'Dipesh Tamang' found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No users found matching Dipesh/Tamang</p>";
    $dipeshUserId = null;
}

// Step 2: Check existing verification records for this user
if ($dipeshUserId) {
    echo "<h3>Step 2: Check existing verification records for User ID $dipeshUserId</h3>";
    $checkVerificationQuery = "SELECT * FROM tbl_user_verification WHERE user_id = $dipeshUserId";
    $existingVerification = $db->select($checkVerificationQuery);
    
    if ($existingVerification && $existingVerification->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>Ver ID</th><th>User ID</th><th>Email</th><th>Username</th><th>User Type</th><th>Status</th><th>Submitted</th></tr>";
        while ($ver = $existingVerification->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$ver['verification_id']}</td>";
            echo "<td>{$ver['user_id']}</td>";
            echo "<td>{$ver['email']}</td>";
            echo "<td>{$ver['username']}</td>";
            echo "<td>{$ver['user_type']}</td>";
            echo "<td>{$ver['verification_status']}</td>";
            echo "<td>{$ver['submitted_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: orange;'>⚠️ Existing verification record found. Will clean and recreate.</p>";
        
        // Delete existing records
        $deleteQuery = "DELETE FROM tbl_user_verification WHERE user_id = $dipeshUserId";
        $db->delete($deleteQuery);
        echo "<p>✅ Cleaned existing verification records</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No existing verification records found for User ID $dipeshUserId</p>";
    }
    
    // Step 3: Create fresh verification record
    echo "<h3>Step 3: Create Fresh Verification Record</h3>";
    $insertVerificationQuery = "INSERT INTO tbl_user_verification 
        (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
        VALUES 
        ($dipeshUserId, '$dipeshEmail', 'dipesh_tamang', $dipeshLevel, 'agent', 'pending', NOW())";
    
    echo "<p><strong>Insert Query:</strong></p>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
    echo htmlspecialchars($insertVerificationQuery);
    echo "</div>";
    
    if ($db->insert($insertVerificationQuery)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ SUCCESS!</h4>";
        echo "<p style='color: #155724; margin: 0;'>Fresh verification record created for Dipesh Tamang!</p>";
        echo "</div>";
        
        // Step 4: Verify the creation with exact admin query
        echo "<h3>Step 4: Test Exact Admin Panel Query</h3>";
        $adminQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                      FROM tbl_user_verification uv 
                      JOIN tbl_user u ON uv.user_id = u.userId 
                      WHERE uv.verification_status = 'pending' 
                      ORDER BY uv.submitted_at ASC";
        
        $adminResult = $db->select($adminQuery);
        
        if ($adminResult && $adminResult->num_rows > 0) {
            echo "<p style='color: green;'>✅ Found {$adminResult->num_rows} pending verification(s)</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'><th>Ver ID</th><th>User ID</th><th>Name</th><th>Email</th><th>User Level</th><th>Status</th></tr>";
            
            $dipeshFoundInAdmin = false;
            while ($row = $adminResult->fetch_assoc()) {
                $fullName = $row['firstName'] . ' ' . $row['lastName'];
                $isDipesh = (stripos($fullName, 'Dipesh') !== false && stripos($fullName, 'Tamang') !== false);
                
                if ($isDipesh) {
                    echo "<tr style='background: #d4edda;'>";
                    $dipeshFoundInAdmin = true;
                } else {
                    echo "<tr>";
                }
                
                echo "<td>" . ($row['verification_id'] ?? 'N/A') . "</td>";
                echo "<td>{$row['user_id']}</td>";
                echo "<td>$fullName</td>";
                echo "<td>{$row['userEmail']}</td>";
                echo "<td>{$row['userLevel']}</td>";
                echo "<td>{$row['verification_status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            if ($dipeshFoundInAdmin) {
                echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h4 style='color: #0c5460; margin-top: 0;'>🎯 SUCCESS!</h4>";
                echo "<p style='color: #0c5460; margin: 0;'><strong>Dipesh Tamang found in admin query!</strong> He will now appear in the admin verification panel.</p>";
                echo "</div>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Dipesh Tamang still not found in admin query results</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No pending verifications found in admin query</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create verification record</p>";
        echo "<p>Database error: " . mysqli_error($db->link) . "</p>";
    }
    
} else {
    // If no Dipesh found, create the user first
    echo "<h3>Step 2: Create Dipesh Tamang User (Not Found)</h3>";
    echo "<p style='color: orange;'>Dipesh Tamang user not found. Creating new user...</p>";
    
    $createUserQuery = "INSERT INTO tbl_user 
        (firstName, lastName, userEmail, cellNo, userAddress, password, userLevel, status, verification_status, created_at) 
        VALUES 
        ('Dipesh', 'Tamang', 'dipesh.tamang@example.com', '9876543210', 'Kathmandu, Nepal', MD5('Agent123!'), 3, 0, 'pending', NOW())";
    
    if ($db->insert($createUserQuery)) {
        echo "<p style='color: green;'>✅ Dipesh Tamang user created successfully!</p>";
        
        // Get the new user ID
        $getNewUserQuery = "SELECT userId FROM tbl_user WHERE firstName = 'Dipesh' AND lastName = 'Tamang'";
        $newUserResult = $db->select($getNewUserQuery);
        $newUserData = $newUserResult->fetch_assoc();
        $newUserId = $newUserData['userId'];
        
        echo "<p>New User ID: <strong>$newUserId</strong></p>";
        
        // Create verification record
        $createVerificationQuery = "INSERT INTO tbl_user_verification 
            (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
            VALUES 
            ($newUserId, 'dipesh.tamang@example.com', 'dipesh_tamang', 3, 'agent', 'pending', NOW())";
        
        if ($db->insert($createVerificationQuery)) {
            echo "<p style='color: green;'>✅ Verification record created for new user!</p>";
        }
    }
}

echo "<h3>🔗 Final Check</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>👨‍💼 CHECK ADMIN PANEL NOW</a>";
echo "<a href='complete_debug.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Run Debug Again</a>";
echo "</p>";
?>
