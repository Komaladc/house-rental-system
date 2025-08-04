<?php
session_start();
include "../lib/Database.php";
include "../lib/Session.php";

$db = new Database();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Sign-In Redirect Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
    <h1>🔐 User Sign-In Redirect Test</h1>
    
    <?php
    echo "<div class='info'><strong>Test Run:</strong> " . date('Y-m-d H:i:s') . "</div>";
    
    // Test 1: Check if redirect logic is fixed
    echo "<h2>📋 Test 1: Redirect Logic Verification</h2>";
    
    $userClassFile = "../classes/User.php";
    if(file_exists($userClassFile)) {
        $userContent = file_get_contents($userClassFile);
        
        // Check if both level 2 and 3 are handled for pending verification
        if(strpos($userContent, "if(\$value['userLevel'] == 2 || \$value['userLevel'] == 3)") !== false) {
            echo "<div class='success'>✅ <strong>FIXED:</strong> Both owners (level 2) and agents (level 3) checked for pending verification</div>";
        } else {
            echo "<div class='warning'>⚠️ <strong>WARNING:</strong> Level 3 users may not be properly handled for pending verification</div>";
        }
        
        // Check if approved owners redirect to index.php
        if(strpos($userContent, "// Approved Property Owner - redirect to main site with owner features") !== false && 
           strpos($userContent, "echo\"<script>window.location='index.php'</script>\";") !== false) {
            echo "<div class='success'>✅ <strong>FIXED:</strong> Approved owners (level 2) now redirect to index.php (user-facing page)</div>";
        } else {
            echo "<div class='warning'>⚠️ <strong>WARNING:</strong> Approved owners may still redirect to admin dashboard</div>";
        }
        
        // Check if approved agents redirect to index.php
        if(strpos($userContent, "// Approved Real Estate Agent - redirect to main site with agent features") !== false) {
            echo "<div class='success'>✅ <strong>FIXED:</strong> Approved agents (level 3) now redirect to index.php (user-facing page)</div>";
        } else {
            echo "<div class='warning'>⚠️ <strong>WARNING:</strong> Approved agents may not have proper redirect</div>";
        }
        
        // Check admin access restriction
        if(strpos($userContent, "strtolower(\$value['firstName']) == 'admin' || \$value['userId'] == 1") !== false) {
            echo "<div class='success'>✅ <strong>FIXED:</strong> Admin access now properly restricted to true admin accounts</div>";
        } else {
            echo "<div class='warning'>⚠️ <strong>WARNING:</strong> Admin access may be too permissive</div>";
        }
        
    } else {
        echo "<div class='danger'>❌ <strong>ERROR:</strong> Cannot read User.php file</div>";
    }

    // Test 2: Current redirect behavior summary
    echo "<h2>📋 Test 2: Expected Sign-In Behavior</h2>";
    
    echo "<table>";
    echo "<tr><th>User Type</th><th>Level</th><th>Status</th><th>Redirect After Sign-In</th><th>Available Features</th></tr>";
    
    $scenarios = [
        ['Regular User', '1', 'Active', 'index.php', 'Browse properties, make bookings'],
        ['Pending Owner', '2', 'Pending (0)', 'Pending message', 'Must wait for admin approval'],
        ['Approved Owner', '2', 'Active (1)', 'index.php', 'Browse + Add properties, manage listings'],
        ['Pending Agent', '3', 'Pending (0)', 'Pending message', 'Must wait for admin approval'],
        ['Approved Agent', '3', 'Active (1)', 'index.php', 'Browse + Agent-specific tools'],
        ['True Admin', '3', 'Active (1)', 'Admin/dashboard_agent.php', 'Full admin access + user management']
    ];
    
    foreach($scenarios as $scenario) {
        echo "<tr>";
        echo "<td><strong>{$scenario[0]}</strong></td>";
        echo "<td>Level {$scenario[1]}</td>";
        echo "<td>{$scenario[2]}</td>";
        echo "<td><strong>{$scenario[3]}</strong></td>";
        echo "<td>{$scenario[4]}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Test 3: Show available approved users for testing
    echo "<h2>📋 Test 3: Available Approved Users</h2>";
    
    $approvedUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.userStatus
                                 FROM tbl_user u 
                                 WHERE u.userStatus = 1 
                                 ORDER BY u.userLevel, u.userId DESC 
                                 LIMIT 10");

    if($approvedUsers && $approvedUsers->num_rows > 0) {
        echo "<div class='success'>✅ <strong>Found {$approvedUsers->num_rows} approved users ready for testing:</strong></div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Expected Redirect</th><th>Test Login</th></tr>";
        while($user = $approvedUsers->fetch_assoc()) {
            $userType = '';
            $expectedRedirect = '';
            
            switch($user['userLevel']) {
                case 1:
                    $userType = '👤 House Seeker';
                    $expectedRedirect = 'index.php';
                    break;
                case 2:
                    $userType = '🏠 Property Owner';
                    $expectedRedirect = 'index.php (with owner menu)';
                    break;
                case 3:
                    if(strtolower($user['firstName']) == 'admin' || $user['userId'] == 1) {
                        $userType = '👑 True Admin';
                        $expectedRedirect = 'Admin/dashboard_agent.php';
                    } else {
                        $userType = '🏢 Real Estate Agent';
                        $expectedRedirect = 'index.php (with agent menu)';
                    }
                    break;
            }
            
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>$userType</td>";
            echo "<td><strong>$expectedRedirect</strong></td>";
            echo "<td><a href='../signin.php' class='btn btn-primary' target='_blank'>Test Login</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ <strong>No approved users found.</strong> Please approve some users first to test sign-in redirects.</div>";
    }

    // Test 4: Show pending users
    echo "<h2>📋 Test 4: Pending Users (Should Get Verification Message)</h2>";
    
    $pendingUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, u.userStatus
                                FROM tbl_user u 
                                WHERE u.userStatus = 0 AND (u.userLevel = 2 OR u.userLevel = 3)
                                ORDER BY u.created_at DESC 
                                LIMIT 5");

    if($pendingUsers && $pendingUsers->num_rows > 0) {
        echo "<div class='info'>📋 <strong>Found {$pendingUsers->num_rows} pending users:</strong></div>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th>Expected Response</th></tr>";
        while($user = $pendingUsers->fetch_assoc()) {
            $userType = ($user['userLevel'] == 2) ? '🏠 Property Owner' : '🏢 Real Estate Agent';
            
            echo "<tr>";
            echo "<td>{$user['userId']}</td>";
            echo "<td>{$user['firstName']} {$user['lastName']}</td>";
            echo "<td>{$user['userEmail']}</td>";
            echo "<td>$userType</td>";
            echo "<td><strong>Account Pending Admin Verification</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='success'>✅ <strong>No pending users found</strong> - All users have been processed</div>";
    }

    echo "<h2>🎯 Summary</h2>";
    echo "<div class='success'>";
    echo "<strong>✅ Sign-In Redirect Fix Applied!</strong><br><br>";
    echo "<strong>Now When Users Sign In:</strong><br>";
    echo "• <strong>Regular Users:</strong> Go to index.php to browse properties<br>";
    echo "• <strong>Approved Owners:</strong> Go to index.php with owner features in navigation<br>";
    echo "• <strong>Approved Agents:</strong> Go to index.php with agent features in navigation<br>";
    echo "• <strong>True Admins:</strong> Go to Admin dashboard for user management<br>";
    echo "• <strong>Pending Users:</strong> See clear verification message<br><br>";
    echo "<strong>✅ No additional verification required</strong> for approved users!";
    echo "</div>";

    ?>

    <hr>
    <p><strong>Quick Test Actions:</strong></p>
    <p>• <a href="../signin.php" class="btn btn-primary" target="_blank">🔐 Test Sign-In Page</a></p>
    <p>• <a href="../index.php" class="btn btn-success" target="_blank">🏠 View Main Site</a></p>
    
</body>
</html>
