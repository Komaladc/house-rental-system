<?php
include '../lib/Database.php';

echo "<h2>🚨 Force Create Dipesh Tamang Verification Record</h2>";

$db = new Database();

// First, let's find Dipesh Tamang in tbl_user
echo "<h3>Step 1: Find Dipesh Tamang User</h3>";
$findUserQuery = "SELECT * FROM tbl_user WHERE firstName = 'Dipesh' AND lastName = 'Tamang'";
$userResult = $db->select($findUserQuery);

if ($userResult && $userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $dipeshUserId = $userData['userId'];
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #155724; margin-top: 0;'>✅ Found Dipesh Tamang</h4>";
    echo "<p>User ID: <strong>$dipeshUserId</strong></p>";
    echo "<p>Email: <strong>{$userData['userEmail']}</strong></p>";
    echo "<p>User Level: <strong>{$userData['userLevel']}</strong></p>";
    echo "</div>";
    
    // Step 2: Delete any existing verification records for this user
    echo "<h3>Step 2: Clean Existing Verification Records</h3>";
    $deleteQuery = "DELETE FROM tbl_user_verification WHERE user_id = $dipeshUserId";
    $deleteResult = $db->delete($deleteQuery);
    echo "<p>✅ Cleaned any existing verification records for User ID $dipeshUserId</p>";
    
    // Step 3: Create fresh verification record
    echo "<h3>Step 3: Create Fresh Verification Record</h3>";
    $insertQuery = "INSERT INTO tbl_user_verification 
        (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
        VALUES 
        ($dipeshUserId, '{$userData['userEmail']}', 'dipesh_tamang', {$userData['userLevel']}, 'agent', 'pending', NOW())";
    
    if ($db->insert($insertQuery)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ SUCCESS!</h4>";
        echo "<p style='color: #155724; margin: 0;'>Fresh verification record created for Dipesh Tamang!</p>";
        echo "</div>";
        
        // Step 4: Verify the creation
        echo "<h3>Step 4: Verify Creation</h3>";
        $verifyQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.userLevel
                       FROM tbl_user_verification uv 
                       JOIN tbl_user u ON uv.user_id = u.userId 
                       WHERE uv.user_id = $dipeshUserId";
        
        $verifyResult = $db->select($verifyQuery);
        
        if ($verifyResult && $verifyResult->num_rows > 0) {
            $verData = $verifyResult->fetch_assoc();
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Verification ID</td><td>{$verData['verification_id']}</td></tr>";
            echo "<tr><td>User ID</td><td>{$verData['user_id']}</td></tr>";
            echo "<tr><td>Name</td><td>{$verData['firstName']} {$verData['lastName']}</td></tr>";
            echo "<tr><td>Email</td><td>{$verData['userEmail']}</td></tr>";
            echo "<tr><td>Username</td><td>{$verData['username']}</td></tr>";
            echo "<tr><td>User Level</td><td>{$verData['userLevel']}</td></tr>";
            echo "<tr><td>User Type</td><td>{$verData['user_type']}</td></tr>";
            echo "<tr><td>Verification Status</td><td>{$verData['verification_status']}</td></tr>";
            echo "<tr><td>Submitted At</td><td>{$verData['submitted_at']}</td></tr>";
            echo "</table>";
            
            echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4 style='color: #0c5460; margin-top: 0;'>🎯 READY FOR ADMIN APPROVAL!</h4>";
            echo "<p style='color: #0c5460; margin: 0;'>Dipesh Tamang should now appear in the admin verification panel.</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to verify creation</p>";
        }
        
        // Step 5: Test the admin query
        echo "<h3>Step 5: Test Admin Panel Query</h3>";
        $adminTestQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                          FROM tbl_user_verification uv 
                          JOIN tbl_user u ON uv.user_id = u.userId 
                          WHERE uv.verification_status = 'pending' AND u.firstName = 'Dipesh' AND u.lastName = 'Tamang'";
        
        $adminTestResult = $db->select($adminTestQuery);
        
        if ($adminTestResult && $adminTestResult->num_rows > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ Dipesh Tamang found in admin panel query!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Dipesh Tamang still not found in admin panel query</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create verification record</p>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<p style='color: #721c24; margin: 0;'>❌ Dipesh Tamang user not found in tbl_user. Need to create user first.</p>";
    echo "</div>";
    
    echo "<p><a href='../create_dipesh_agent.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Create Dipesh Tamang User</a></p>";
}

echo "<h3>🔗 Next Steps</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>👨‍💼 CHECK ADMIN PANEL</a>";
echo "<a href='complete_debug.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Run Debug Again</a>";
echo "</p>";
?>
