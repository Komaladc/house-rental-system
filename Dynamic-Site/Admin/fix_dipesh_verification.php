<?php
include '../lib/Database.php';

echo "<h2>🔧 Fix Dipesh Tamang Verification Record</h2>";

$db = new Database();

// Get Dipesh's user data
$getUserQuery = "SELECT * FROM tbl_user WHERE firstName = 'Dipesh' AND lastName = 'Tamang'";
$userResult = $db->select($getUserQuery);

if ($userResult && $userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userId = $userData['userId'];
    
    echo "<h3>✅ Found Dipesh Tamang User</h3>";
    echo "<p>User ID: <strong>$userId</strong></p>";
    echo "<p>Email: <strong>{$userData['userEmail']}</strong></p>";
    echo "<p>User Type: <strong>{$userData['userType']}</strong></p>";
    echo "<p>User Level: <strong>{$userData['userLevel']}</strong></p>";
    
    // Check if verification record exists
    $checkVerification = "SELECT * FROM tbl_user_verification WHERE user_id = $userId";
    $verificationResult = $db->select($checkVerification);
    
    if ($verificationResult && $verificationResult->num_rows > 0) {
        echo "<h3>⚠️ Verification Record Already Exists</h3>";
        $verData = $verificationResult->fetch_assoc();
        echo "<p>Status: <strong>{$verData['verification_status']}</strong></p>";
        echo "<p>Submitted: <strong>{$verData['submitted_at']}</strong></p>";
        
        // If status is not pending, update it
        if ($verData['verification_status'] !== 'pending') {
            $updateQuery = "UPDATE tbl_user_verification SET verification_status = 'pending' WHERE user_id = $userId";
            if ($db->update($updateQuery)) {
                echo "<p style='color: green;'>✅ Updated verification status to 'pending'</p>";
            }
        }
    } else {
        echo "<h3>🔧 Creating Verification Record</h3>";
        
        // Create verification record
        $insertQuery = "INSERT INTO tbl_user_verification 
            (user_id, email, username, user_level, user_type, verification_status, submitted_at) 
            VALUES 
            ($userId, '{$userData['userEmail']}', 'dipesh_tamang', {$userData['userLevel']}, '{$userData['userType']}', 'pending', NOW())";
        
        if ($db->insert($insertQuery)) {
            echo "<p style='color: green;'>✅ Verification record created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create verification record</p>";
        }
    }
    
    // Now test the admin query
    echo "<h3>🧪 Testing Admin Query</h3>";
    $testQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                  FROM tbl_user_verification uv 
                  JOIN tbl_user u ON uv.user_id = u.userId 
                  WHERE uv.verification_status = 'pending' AND u.userId = $userId";
    
    $testResult = $db->select($testQuery);
    
    if ($testResult && $testResult->num_rows > 0) {
        $testData = $testResult->fetch_assoc();
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ SUCCESS!</h4>";
        echo "<p style='color: #155724;'>Dipesh Tamang will now appear in admin verification panel:</p>";
        echo "<ul style='color: #155724;'>";
        echo "<li>Name: {$testData['firstName']} {$testData['lastName']}</li>";
        echo "<li>Email: {$testData['userEmail']}</li>";
        echo "<li>User Type: Agent (Level {$testData['userLevel']})</li>";
        echo "<li>Status: {$testData['verification_status']}</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Still not showing in admin query</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Dipesh Tamang user not found</p>";
}

echo "<h3>🔗 Verify Fix</h3>";
echo "<p>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Check Admin Panel</a>";
echo "<a href='debug_dipesh_verification.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug Again</a>";
echo "</p>";
?>
