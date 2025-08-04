<?php
session_start();
include "lib/Database.php";
include "lib/Session.php";
include "classes/User.php";

$db = new Database();
$usr = new User();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Sign-In Without Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .danger { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .form-test { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>🔐 Test Sign-In Without Email Verification</h1>
    
    <?php
    echo "<div class='info'><strong>Test Run:</strong> " . date('Y-m-d H:i:s') . "</div>";
    
    // Test the sign-in logic
    if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['test_signin'])) {
        echo "<h2>📋 Sign-In Test Result</h2>";
        
        $testData = array(
            'email' => $_POST['email'],
            'password' => $_POST['password']
        );
        
        $loginResult = $usr->UserLogin($testData);
        
        if(isset($loginResult)) {
            echo "<div class='warning'><strong>Login Response:</strong><br>$loginResult</div>";
        } else {
            echo "<div class='success'>✅ <strong>Login Successful!</strong> User should be redirected to their dashboard.</div>";
        }
    }
    
    // Show approved users for testing
    echo "<h2>📋 Available Approved Users for Testing</h2>";
    
    $approvedUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.userStatus, 
                                 CASE WHEN EXISTS(SELECT 1 FROM information_schema.columns WHERE table_name='tbl_user' AND column_name='email_verified') 
                                      THEN (SELECT email_verified FROM tbl_user u2 WHERE u2.userId = u.userId)
                                      ELSE 'N/A' END as email_verified
                                 FROM tbl_user u 
                                 WHERE u.userStatus = 1 
                                 ORDER BY u.userLevel, u.userId DESC 
                                 LIMIT 8");

    if($approvedUsers && $approvedUsers->num_rows > 0) {
        echo "<div class='success'>✅ <strong>Found {$approvedUsers->num_rows} approved users:</strong></div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Email Verified</th><th>Expected Behavior</th></tr>";
        while($user = $approvedUsers->fetch_assoc()) {
            $userType = '';
            $expected = '';
            
            switch($user['userLevel']) {
                case 1:
                    $userType = '👤 House Seeker';
                    $expected = 'Sign in directly → index.php';
                    break;
                case 2:
                    $userType = '🏠 Property Owner';
                    $expected = 'Sign in directly → index.php (admin approved)';
                    break;
                case 3:
                    if(strtolower($user['firstName']) == 'admin' || $user['userId'] == 1) {
                        $userType = '👑 True Admin';
                        $expected = 'Sign in directly → Admin dashboard';
                    } else {
                        $userType = '🏢 Real Estate Agent';
                        $expected = 'Sign in directly → index.php (admin approved)';
                    }
                    break;
            }
            
            $statusText = ($user['userStatus'] == 1) ? '✅ Active' : '⏳ Pending';
            $emailVerifiedText = ($user['email_verified'] === 'N/A') ? 'No Column' : (($user['email_verified'] == 1) ? '✅ Yes' : '❌ No');
            
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>$userType</td>";
            echo "<td>$statusText</td>";
            echo "<td>$emailVerifiedText</td>";
            echo "<td><strong>$expected</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test form with first user
        $approvedUsers->data_seek(0);
        $testUser = $approvedUsers->fetch_assoc();
        
        echo "<div class='form-test'>";
        echo "<h3>🧪 Quick Test with: {$testUser['firstName']} {$testUser['lastName']}</h3>";
        echo "<form method='post'>";
        echo "<table style='width: auto;'>";
        echo "<tr><td><strong>Email:</strong></td><td><input type='email' name='email' value='{$testUser['userEmail']}' readonly style='background: #f8f9fa; padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px;'></td></tr>";
        echo "<tr><td><strong>Password:</strong></td><td><input type='password' name='password' placeholder='Enter password' required style='padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px;'></td></tr>";
        echo "<tr><td colspan='2'><button type='submit' name='test_signin' class='btn btn-success' style='padding: 10px 20px; margin-top: 10px;'>🚀 Test Sign-In</button></td></tr>";
        echo "</table>";
        echo "</form>";
        echo "</div>";
        
    } else {
        echo "<div class='warning'>⚠️ <strong>No approved users found.</strong> Please approve some users first.</div>";
    }

    // Show what was fixed
    echo "<h2>🔧 Email Verification Fix Summary</h2>";
    echo "<div class='success'>";
    echo "<strong>✅ Email Verification Logic Updated:</strong><br><br>";
    echo "• <strong>Admin-approved users (userStatus = 1):</strong> Skip email verification completely<br>";
    echo "• <strong>Regular users (userLevel = 1):</strong> Skip email verification<br>";
    echo "• <strong>Pending users (userStatus = 0):</strong> May still need email verification<br><br>";
    echo "<strong>This means:</strong><br>";
    echo "• Approved owners can sign in directly<br>";
    echo "• Approved agents can sign in directly<br>";
    echo "• Regular users can sign in directly<br>";
    echo "• No additional email verification required!";
    echo "</div>";

    // Show pending users
    echo "<h2>📋 Pending Users (May Still Need Email Verification)</h2>";
    
    $pendingUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.userStatus
                                FROM tbl_user u 
                                WHERE u.userStatus = 0 AND (u.userLevel = 2 OR u.userLevel = 3)
                                ORDER BY u.userId DESC 
                                LIMIT 5");

    if($pendingUsers && $pendingUsers->num_rows > 0) {
        echo "<div class='info'>📋 <strong>Found {$pendingUsers->num_rows} pending users:</strong></div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Note</th></tr>";
        while($user = $pendingUsers->fetch_assoc()) {
            $userType = ($user['userLevel'] == 2) ? '🏠 Property Owner' : '🏢 Real Estate Agent';
            
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>$userType</td>";
            echo "<td>⏳ Pending Admin Approval</td>";
            echo "<td>Will skip email verification once approved</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='success'>✅ <strong>No pending users found</strong> - All users have been processed</div>";
    }
    ?>

    <hr>
    <p><strong>Quick Actions:</strong></p>
    <p>• <a href="signin.php" class="btn btn-primary" target="_blank">🔐 Go to Sign-In Page</a></p>
    <p>• <a href="index.php" class="btn btn-success" target="_blank">🏠 View Main Site</a></p>
    
</body>
</html>
