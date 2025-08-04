<?php
include 'lib/Database.php';

echo "<h2>🧪 Create Dipesh Tamang Agent Signup</h2>"            echo "<tr><td>Name</td><td>" . $verData['firstName'] . " " . $verData['lastName'] . "</td></tr>";
            echo "<tr><td>Email</td><td>" . $verData['userEmail'] . "</td></tr>";
            echo "<tr><td>User Type (from verification)</td><td>" . $verData['user_type'] . "</td></tr>";
            echo "<tr><td>User Level</td><td>" . $verData['userLevel'] . "</td></tr>";
            echo "<tr><td>Status</td><td>" . $verData['verification_status'] . "</td></tr>";
            echo "<tr><td>Submitted</td><td>" . $verData['submitted_at'] . "</td></tr>";db = new Database();

// Test data for Dipesh Tamang agent
$testData = [
    'firstName' => 'Dipesh',
    'lastName' => 'Tamang',
    'userEmail' => 'dipesh.tamang@example.com',
    'cellNo' => '9876543210',
    'userAddress' => 'Kathmandu, Nepal',
    'password' => 'Agent123!',
    'userType' => 'agent',
    'userLevel' => 3
];

echo "<h3>📝 Creating Agent Registration...</h3>";

try {
    // Check if user already exists
    $checkUser = "SELECT * FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testData['userEmail']) . "'";
    $existingUser = $db->select($checkUser);
    
    if ($existingUser && $existingUser->num_rows > 0) {
        $userData = $existingUser->fetch_assoc();
        $userId = $userData['userId'];
        echo "<p style='color: orange;'>⚠️ User already exists with ID: $userId</p>";
    } else {
        // Step 1: Create the user account
        $hashedPassword = md5($testData['password']); // Using MD5 to match existing system
        
        $insertUserQuery = "INSERT INTO tbl_user 
            (firstName, lastName, userEmail, cellNo, userAddress, password, userLevel, status, verification_status, created_at) 
            VALUES 
            ('{$testData['firstName']}', '{$testData['lastName']}', '{$testData['userEmail']}', '{$testData['cellNo']}', '{$testData['userAddress']}', '$hashedPassword', {$testData['userLevel']}, 0, 'pending', NOW())";
        
        if ($db->insert($insertUserQuery)) {
            echo "<p style='color: green;'>✅ User account created successfully!</p>";
            
            // Get the user ID
            $getUserQuery = "SELECT userId FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $testData['userEmail']) . "'";
            $userResult = $db->select($getUserQuery);
            $userData = $userResult->fetch_assoc();
            $userId = $userData['userId'];
            
            echo "<p>✅ User created with ID: <strong>$userId</strong></p>";
        } else {
            throw new Exception("Failed to create user account");
        }
    }
    
    // Step 2: Check if verification record exists
    $checkVerification = "SELECT * FROM tbl_user_verification WHERE user_id = $userId";
    $existingVerification = $db->select($checkVerification);
    
    if ($existingVerification && $existingVerification->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Verification record already exists</p>";
    } else {
        // Create verification record
        echo "<h3>📋 Creating Verification Record...</h3>";
        
        $insertVerificationQuery = "INSERT INTO tbl_user_verification 
            (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
            VALUES 
            ($userId, '{$testData['userEmail']}', 'dipesh_tamang', {$testData['userLevel']}, '{$testData['userType']}', 'pending', NOW())";
        
        if ($db->insert($insertVerificationQuery)) {
            echo "<p style='color: green;'>✅ Verification record created successfully!</p>";
        } else {
            throw new Exception("Failed to create verification record");
        }
    }
    
    // Verify the creation
    echo "<h3>🔍 Final Verification Check</h3>";
    $checkQuery = "SELECT v.*, u.firstName, u.lastName, u.userEmail, u.userLevel
                  FROM tbl_user_verification v 
                  JOIN tbl_user u ON v.user_id = u.userId 
                  WHERE u.userId = $userId";
    $checkResult = $db->select($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $verData = $checkResult->fetch_assoc();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>Verification ID</td><td>" . $verData['verification_id'] . "</td></tr>";
        echo "<tr><td>User ID</td><td>" . $verData['user_id'] . "</td></tr>";
        echo "<tr><td>Name</td><td>" . $verData['firstName'] . " " . $verData['lastName'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $verData['userEmail'] . "</td></tr>";
        echo "<tr><td>User Type (from verification)</td><td>" . $verData['user_type'] . "</td></tr>";
        echo "<tr><td>User Level</td><td>" . $verData['userLevel'] . "</td></tr>";
        echo "<tr><td>Status</td><td>" . $verData['verification_status'] . "</td></tr>";
        echo "<tr><td>Submitted</td><td>" . $verData['submitted_at'] . "</td></tr>";
        echo "</table>";
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>🎉 SUCCESS!</h4>";
        echo "<p style='color: #155724; margin: 0;'><strong>Dipesh Tamang</strong> agent is now ready for admin verification!</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Failed to verify creation</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>🔗 Next Steps</h3>";
echo "<p>";
echo "<a href='Admin/verify_users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>👨‍💼 Check Admin Panel</a>";
echo "<a href='Admin/search_dipesh.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Search Results</a>";
echo "</p>";
?>
