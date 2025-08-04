<?php
session_start();
include "../classes/Database.php";
include "../classes/Session.php";

// Check if admin is logged in
Session::checkAdminSession();

$db = new Database();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Verification Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin: 10px 0; background: #f9f9f9; }
        .pending { border-left: 4px solid #ffc107; }
        .active { border-left: 4px solid #28a745; }
        .rejected { border-left: 4px solid #dc3545; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-active { background: #28a745; color: #fff; }
        .badge-rejected { background: #dc3545; color: #fff; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { flex: 1; text-align: center; padding: 20px; border-radius: 8px; background: #e9ecef; }
        .refresh-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
    <script>
        function autoRefresh() {
            setTimeout(function() {
                location.reload();
            }, 30000); // Refresh every 30 seconds
        }
        window.onload = autoRefresh;
    </script>
</head>
<body>
    <h1>🔍 User Verification Monitor</h1>
    <p>Real-time monitoring of the user verification workflow</p>
    
    <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Now</button>
    <small style="margin-left: 10px;">Auto-refreshes every 30 seconds</small>

    <?php
    // Get statistics
    $pendingAgents = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 0");
    $pendingOwners = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 3 AND userStatus = 0");
    $activeAgents = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2 AND userStatus = 1");
    $activeOwners = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 3 AND userStatus = 1");

    $pendingAgentCount = $pendingAgents ? $pendingAgents->fetch_assoc()['count'] : 0;
    $pendingOwnerCount = $pendingOwners ? $pendingOwners->fetch_assoc()['count'] : 0;
    $activeAgentCount = $activeAgents ? $activeAgents->fetch_assoc()['count'] : 0;
    $activeOwnerCount = $activeOwners ? $activeOwners->fetch_assoc()['count'] : 0;
    ?>

    <div class="stats">
        <div class="stat-box">
            <h3><?= $pendingAgentCount ?></h3>
            <p>Pending Agents</p>
        </div>
        <div class="stat-box">
            <h3><?= $pendingOwnerCount ?></h3>
            <p>Pending Owners</p>
        </div>
        <div class="stat-box">
            <h3><?= $activeAgentCount ?></h3>
            <p>Active Agents</p>
        </div>
        <div class="stat-box">
            <h3><?= $activeOwnerCount ?></h3>
            <p>Active Owners</p>
        </div>
    </div>

    <div class="card pending">
        <h2>⏳ Pending Verification (Requires Admin Action)</h2>
        <?php
        $pendingUsers = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, 
                                            v.verification_status, u.created_at
                                    FROM tbl_user u 
                                    LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                                    WHERE (u.userLevel = 2 OR u.userLevel = 3) 
                                    AND u.userStatus = 0 
                                    ORDER BY u.created_at DESC");

        if($pendingUsers && $pendingUsers->num_rows > 0) {
            echo "<p>Found {$pendingUsers->num_rows} users awaiting admin verification:</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Verification Status</th><th>Registered</th><th>Action</th></tr>";
            while($user = $pendingUsers->fetch_assoc()) {
                $userType = ($user['userLevel'] == 2) ? 'Agent' : 'Owner';
                $verStatus = $user['verification_status'] ?? 'pending';
                echo "<tr>";
                echo "<td>{$user['userId']}</td>";
                echo "<td>{$user['firstName']} {$user['lastName']}</td>";
                echo "<td>{$user['userEmail']}</td>";
                echo "<td>$userType</td>";
                echo "<td><span class='badge badge-pending'>$verStatus</span></td>";
                echo "<td>" . date('M j, Y H:i', strtotime($user['created_at'])) . "</td>";
                echo "<td><a href='verify_users.php'>Review →</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>✅ No users pending verification</p>";
        }
        ?>
    </div>

    <div class="card active">
        <h2>✅ Recently Approved Users</h2>
        <?php
        $recentApproved = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, 
                                             v.verification_status, v.verified_at
                                      FROM tbl_user u 
                                      JOIN tbl_user_verification v ON u.userId = v.user_id 
                                      WHERE (u.userLevel = 2 OR u.userLevel = 3) 
                                      AND u.userStatus = 1 
                                      AND v.verification_status = 'approved'
                                      ORDER BY v.verified_at DESC 
                                      LIMIT 10");

        if($recentApproved && $recentApproved->num_rows > 0) {
            echo "<p>Last 10 approved users:</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Approved Date</th></tr>";
            while($user = $recentApproved->fetch_assoc()) {
                $userType = ($user['userLevel'] == 2) ? 'Agent' : 'Owner';
                echo "<tr>";
                echo "<td>{$user['userId']}</td>";
                echo "<td>{$user['firstName']} {$user['lastName']}</td>";
                echo "<td>{$user['userEmail']}</td>";
                echo "<td>$userType</td>";
                echo "<td>" . ($user['verified_at'] ? date('M j, Y H:i', strtotime($user['verified_at'])) : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No recently approved users</p>";
        }
        ?>
    </div>

    <div class="card rejected">
        <h2>❌ Recently Rejected Users</h2>
        <?php
        $recentRejected = $db->select("SELECT u.userId, u.firstName, u.lastName, u.userEmail, u.userLevel, 
                                             v.verification_status, v.verified_at
                                      FROM tbl_user u 
                                      JOIN tbl_user_verification v ON u.userId = v.user_id 
                                      WHERE (u.userLevel = 2 OR u.userLevel = 3) 
                                      AND v.verification_status = 'rejected'
                                      ORDER BY v.verified_at DESC 
                                      LIMIT 10");

        if($recentRejected && $recentRejected->num_rows > 0) {
            echo "<p>Last 10 rejected users:</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Rejected Date</th></tr>";
            while($user = $recentRejected->fetch_assoc()) {
                $userType = ($user['userLevel'] == 2) ? 'Agent' : 'Owner';
                echo "<tr>";
                echo "<td>{$user['userId']}</td>";
                echo "<td>{$user['firstName']} {$user['lastName']}</td>";
                echo "<td>{$user['userEmail']}</td>";
                echo "<td>$userType</td>";
                echo "<td>" . ($user['verified_at'] ? date('M j, Y H:i', strtotime($user['verified_at'])) : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No recently rejected users</p>";
        }
        ?>
    </div>

    <hr>
    <p><strong>Quick Actions:</strong></p>
    <p>• <a href="verify_users.php">Verify Pending Users</a></p>
    <p>• <a href="final_verification_test.php">Run System Verification Test</a></p>
    <p>• <a href="../registration_enhanced.php" target="_blank">Test Registration Process</a></p>

    <script>
        console.log('User Verification Monitor loaded at ' + new Date().toLocaleTimeString());
    </script>
</body>
</html>
