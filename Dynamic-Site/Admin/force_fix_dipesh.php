<?php
include '../lib/Database.php';

echo "<h2>🚨 Force Fix Dipesh Tamang Verification</h2>";

$db = new Database();

// Get Dipesh's user ID
$getUserQuery = "SELECT userId FROM tbl_user WHERE firstName = 'Dipesh' AND lastName = 'Tamang'";
$userResult = $db->select($getUserQuery);

if ($userResult && $userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userId = $userData['userId'];
    
    echo "<p>Found Dipesh Tamang with User ID: <strong>$userId</strong></p>";
    
    // Step 1: Delete any existing verification record
    echo "<h3>Step 1: Cleaning existing verification records</h3>";
    $deleteQuery = "DELETE FROM tbl_user_verification WHERE user_id = $userId";
    $deleteResult = $db->delete($deleteQuery);
    echo "<p>✅ Cleaned existing records</p>";
    
    // Step 2: Create fresh verification record
    echo "<h3>Step 2: Creating fresh verification record</h3>";
    $insertQuery = "INSERT INTO tbl_user_verification 
        (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
        VALUES 
        ($userId, 'dipesh.tamang@example.com', 'dipesh_tamang', 3, 'agent', 'pending', NOW())";
    
    if ($db->insert($insertQuery)) {
        echo "<p style='color: green;'>✅ Fresh verification record created!</p>";
        
        // Step 3: Verify it was created correctly
        echo "<h3>Step 3: Verification check</h3>";
        $checkQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.userLevel
                      FROM tbl_user_verification uv 
                      JOIN tbl_user u ON uv.user_id = u.userId 
                      WHERE uv.user_id = $userId";
        
        $checkResult = $db->select($checkQuery);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $data = $checkResult->fetch_assoc();
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Verification ID</td><td>{$data['verification_id']}</td></tr>";
            echo "<tr><td>User ID</td><td>{$data['user_id']}</td></tr>";
            echo "<tr><td>Name</td><td>{$data['firstName']} {$data['lastName']}</td></tr>";
            echo "<tr><td>Email</td><td>{$data['userEmail']}</td></tr>";
            echo "<tr><td>User Level</td><td>{$data['userLevel']}</td></tr>";
            echo "<tr><td>Status</td><td>{$data['verification_status']}</td></tr>";
            echo "<tr><td>Submitted</td><td>{$data['submitted_at']}</td></tr>";
            echo "</table>";
            
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4 style='color: #155724; margin-top: 0;'>🎉 SUCCESS!</h4>";
            echo "<p style='color: #155724; margin: 0;'>Dipesh Tamang verification record has been recreated and should now appear in the admin panel!</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to verify creation</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to create verification record</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Dipesh Tamang user not found</p>";
}

echo "<h3>🔗 Check Results</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>👨‍💼 Check Admin Panel Now</a>";
echo "<a href='complete_debug.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Run Debug Again</a>";
echo "</p>";
?>
