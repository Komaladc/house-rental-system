<?php
session_start();
include "../classes/Database.php";
include "../classes/Session.php";

$db = new Database();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Verification Test - Anti Auto-Approval Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .danger { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-active { background: #28a745; color: #fff; }
        .badge-rejected { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <h1>🔍 Final Verification Test - Anti Auto-Approval Check</h1>
    <p>This script verifies that the auto-approval issue has been completely resolved.</p>

    <?php
    echo "<div class='info'><strong>Test Run:</strong> " . date('Y-m-d H:i:s') . "</div>";

    // Test 1: Check for any auto-approved users
    echo "<h2>📋 Test 1: Auto-Approval Detection</h2>";
    
    $autoApprovedAgents = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 1");
    $autoApprovedOwners = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 3 AND userStatus = 1");
    
    if($autoApprovedAgents) {
        $agentCount = $autoApprovedAgents->fetch_assoc()['count'];
    } else {
        $agentCount = 0;
    }
    
    if($autoApprovedOwners) {
        $ownerCount = $autoApprovedOwners->fetch_assoc()['count'];
    } else {
        $ownerCount = 0;
    }
    
    if($agentCount == 0 && $ownerCount == 0) {
        echo "<div class='success'>✅ <strong>PASS:</strong> No auto-approved users found!</div>";
    } else {
        echo "<div class='danger'>❌ <strong>FAIL:</strong> Found $agentCount auto-approved agents and $ownerCount auto-approved owners</div>";
    }

    // Test 2: Check verification status alignment
    echo "<h2>📋 Test 2: Verification Status Alignment</h2>";
    
    $misalignedUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userLevel, u.userStatus, v.verification_status 
                                   FROM tbl_user u 
                                   LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                   WHERE (u.userLevel = 2 OR u.userLevel = 3) 
                                   AND u.userStatus = 1 
                                   AND (v.verification_status != 'approved' OR v.verification_status IS NULL)");

    if($misalignedUsers && $misalignedUsers->num_rows > 0) {
        echo "<div class='danger'>❌ <strong>FAIL:</strong> Found {$misalignedUsers->num_rows} users with userStatus=1 but not properly approved:</div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Level</th><th>User Status</th><th>Verification Status</th></tr>";
        while($user = $misalignedUsers->fetch_assoc()) {
            $levelText = ($user['userLevel'] == 2) ? 'Agent' : 'Owner';
            $verStatus = $user['verification_status'] ?? 'NULL';
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>$levelText</td>";
            echo "<td><span class='badge badge-active'>Active</span></td>";
            echo "<td><span class='badge badge-rejected'>$verStatus</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='success'>✅ <strong>PASS:</strong> All active users have proper verification status</div>";
    }

    // Test 3: Check pending users are in verify_users page
    echo "<h2>📋 Test 3: Pending Users Visibility</h2>";
    
    $pendingUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.userStatus, 
                                        v.verification_status, u.created_at
                                FROM tbl_user u 
                                LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                WHERE (u.userLevel = 2 OR u.userLevel = 3) 
                                AND u.userStatus = 0 
                                ORDER BY u.created_at DESC 
                                LIMIT 10");

    if($pendingUsers && $pendingUsers->num_rows > 0) {
        echo "<div class='info'>📋 <strong>INFO:</strong> Found {$pendingUsers->num_rows} pending users (showing latest 10):</div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Verification</th><th>Created</th></tr>";
        while($user = $pendingUsers->fetch_assoc()) {
            $levelText = ($user['userLevel'] == 2) ? 'Agent' : 'Owner';
            $verStatus = $user['verification_status'] ?? 'pending';
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>$levelText</td>";
            echo "<td><span class='badge badge-pending'>Pending</span></td>";
            echo "<td>$verStatus</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ <strong>WARNING:</strong> No pending users found. This could mean all users have been processed or no new registrations.</div>";
    }

    // Test 4: Check code for auto-approval patterns
    echo "<h2>📋 Test 4: Code Pattern Analysis</h2>";
    
    $codeIssues = [];
    
    // Check if updateUserStatus is still being called anywhere
    $userPhpContent = file_get_contents("../classes/User.php");
    if(strpos($userPhpContent, 'userStatus = \'1\'') !== false && strpos($userPhpContent, '/*') === false) {
        $codeIssues[] = "User.php still contains uncommented userStatus = '1' assignments";
    }
    
    // Check owner_list.php
    if(file_exists("owner_list.php")) {
        $ownerListContent = file_get_contents("owner_list.php");
        if(strpos($ownerListContent, 'updateUserStatus') !== false) {
            $codeIssues[] = "owner_list.php still calls updateUserStatus method";
        }
    }
    
    if(empty($codeIssues)) {
        echo "<div class='success'>✅ <strong>PASS:</strong> No problematic code patterns detected</div>";
    } else {
        echo "<div class='danger'>❌ <strong>FAIL:</strong> Code issues found:</div>";
        foreach($codeIssues as $issue) {
            echo "<div class='danger'>• $issue</div>";
        }
    }

    // Test 5: Registration workflow test
    echo "<h2>📋 Test 5: Registration Workflow Analysis</h2>";
    
    $regContent = file_get_contents("../registration_enhanced.php");
    if(strpos($regContent, '$userStatus = ($level == 1) ? 1 : 0;') !== false) {
        echo "<div class='success'>✅ <strong>PASS:</strong> Registration correctly sets userStatus = 0 for agents/owners</div>";
    } else {
        echo "<div class='danger'>❌ <strong>FAIL:</strong> Registration logic may be incorrect</div>";
    }

    // Summary
    echo "<h2>🎯 Summary</h2>";
    $totalTests = 5;
    $passedTests = 0;
    
    if($agentCount == 0 && $ownerCount == 0) $passedTests++;
    if(!($misalignedUsers && $misalignedUsers->num_rows > 0)) $passedTests++;
    if($pendingUsers && $pendingUsers->num_rows > 0) $passedTests++;
    if(empty($codeIssues)) $passedTests++;
    if(strpos($regContent, '$userStatus = ($level == 1) ? 1 : 0;') !== false) $passedTests++;
    
    if($passedTests == $totalTests) {
        echo "<div class='success'><h3>🎉 ALL TESTS PASSED ($passedTests/$totalTests)</h3>";
        echo "<strong>Status:</strong> Auto-approval issue has been completely resolved!<br>";
        echo "<strong>Workflow:</strong> Registration → OTP → Pending → Admin Approval → Active<br>";
        echo "<strong>Admin Control:</strong> Only admins can approve/reject users via verify_users.php</div>";
    } else {
        echo "<div class='warning'><h3>⚠️ PARTIAL SUCCESS ($passedTests/$totalTests tests passed)</h3>";
        echo "Some issues may still need attention. Review the failed tests above.</div>";
    }

    echo "<hr>";
    echo "<div class='info'>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Test registration of a new agent/owner<br>";
    echo "2. Verify they appear as pending in admin verify_users.php<br>";
    echo "3. Test admin approval/rejection process<br>";
    echo "4. Confirm no auto-approval occurs at any stage";
    echo "</div>";
    ?>

    <hr>
    <p><strong>Admin Actions:</strong></p>
    <p>• <a href="verify_users.php" target="_blank">Go to Admin Verification Page</a></p>
    <p>• <a href="../registration_enhanced.php" target="_blank">Test Registration Process</a></p>
    
</body>
</html>
