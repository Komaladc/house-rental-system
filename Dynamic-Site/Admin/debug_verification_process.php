<?php
// Debug admin approval/rejection process
session_start();

// Set admin session for testing
$_SESSION['userlogin'] = true;
$_SESSION['userId'] = 1;
$_SESSION['userLevel'] = 3;
$_SESSION['userName'] = 'admin';

include'../lib/Database.php';
include'../helpers/Format.php';

$db = new Database();
$message = "";

// Handle verification actions with detailed debugging
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['approve_user'])) {
        $userId = intval($_POST['user_id']);
        
        echo "<h3>🔍 Debug: Approving User ID: $userId</h3>";
        
        // Check current user status
        $checkUser = "SELECT * FROM tbl_user WHERE userId = $userId";
        $userResult = $db->select($checkUser);
        if($userResult) {
            $user = $userResult->fetch_assoc();
            echo "<p><strong>Before Update:</strong></p>";
            echo "<ul>";
            echo "<li>User ID: {$user['userId']}</li>";
            echo "<li>Name: {$user['firstName']} {$user['lastName']}</li>";
            echo "<li>Email: {$user['userEmail']}</li>";
            echo "<li>Current Status: {$user['userStatus']} " . ($user['userStatus'] == 0 ? '(PENDING)' : '(ACTIVE)') . "</li>";
            echo "<li>User Level: {$user['userLevel']}</li>";
            echo "</ul>";
        }
        
        // Update user status to active
        $updateUser = "UPDATE tbl_user SET userStatus = 1 WHERE userId = $userId";
        echo "<p><strong>Update Query:</strong> $updateUser</p>";
        
        $updateResult = $db->update($updateUser);
        echo "<p><strong>Update Result:</strong> " . ($updateResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        if($updateResult) {
            // Check verification record
            $checkVerification = "SELECT * FROM tbl_user_verification WHERE user_id = $userId";
            $verificationResult = $db->select($checkVerification);
            
            if($verificationResult && $verificationResult->num_rows > 0) {
                $verification = $verificationResult->fetch_assoc();
                echo "<p><strong>Verification Record Found:</strong></p>";
                echo "<ul>";
                echo "<li>Verification ID: {$verification['verification_id']}</li>";
                echo "<li>Current Status: {$verification['verification_status']}</li>";
                echo "<li>User ID: {$verification['user_id']}</li>";
                echo "</ul>";
                
                // Update verification record
                $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'approved', reviewed_at = NOW(), reviewed_by = 1 WHERE user_id = $userId";
                echo "<p><strong>Verification Update Query:</strong> $updateVerification</p>";
                
                $verificationUpdateResult = $db->update($updateVerification);
                echo "<p><strong>Verification Update Result:</strong> " . ($verificationUpdateResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
                
                if(!$verificationUpdateResult) {
                    echo "<p><strong>MySQL Error:</strong> " . mysqli_error($db->link) . "</p>";
                }
            } else {
                echo "<p><strong>⚠️ No verification record found for user ID: $userId</strong></p>";
                echo "<p>Creating verification record...</p>";
                
                // Create verification record if it doesn't exist
                $createVerification = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, verification_status, reviewed_at, reviewed_by, submitted_at) 
                                      VALUES ($userId, '{$user['userEmail']}', '{$user['userName']}', {$user['userLevel']}, 
                                             " . ($user['userLevel'] == 2 ? "'Owner'" : "'Agent'") . ", 'approved', NOW(), 1, NOW())";
                echo "<p><strong>Create Verification Query:</strong> $createVerification</p>";
                
                $createResult = $db->insert($createVerification);
                echo "<p><strong>Create Verification Result:</strong> " . ($createResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            }
            
            // Check final status
            $finalCheck = "SELECT u.*, v.verification_status FROM tbl_user u LEFT JOIN tbl_user_verification v ON u.userId = v.user_id WHERE u.userId = $userId";
            $finalResult = $db->select($finalCheck);
            if($finalResult) {
                $final = $finalResult->fetch_assoc();
                echo "<p><strong>After Update:</strong></p>";
                echo "<ul>";
                echo "<li>User Status: {$final['userStatus']} " . ($final['userStatus'] == 1 ? '(✅ ACTIVE)' : '(⏳ PENDING)') . "</li>";
                echo "<li>Verification Status: " . ($final['verification_status'] ?? 'No record') . "</li>";
                echo "</ul>";
            }
            
            $message = "<div class='alert alert-success'>✅ User approved successfully! They can now sign in.</div>";
        } else {
            echo "<p><strong>MySQL Error:</strong> " . mysqli_error($db->link) . "</p>";
            $message = "<div class='alert alert-danger'>❌ Failed to approve user!</div>";
        }
    }
    
    if (isset($_POST['reject_user'])) {
        $userId = intval($_POST['user_id']);
        $rejectionReason = mysqli_real_escape_string($db->link, $_POST['rejection_reason'] ?? 'No reason provided');
        
        echo "<h3>🔍 Debug: Rejecting User ID: $userId</h3>";
        echo "<p><strong>Rejection Reason:</strong> $rejectionReason</p>";
        
        // Update verification record
        $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'rejected', reviewed_at = NOW(), reviewed_by = 1, admin_comments = '$rejectionReason' WHERE user_id = $userId";
        echo "<p><strong>Rejection Query:</strong> $updateVerification</p>";
        
        $rejectResult = $db->update($updateVerification);
        echo "<p><strong>Rejection Result:</strong> " . ($rejectResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        if(!$rejectResult) {
            echo "<p><strong>MySQL Error:</strong> " . mysqli_error($db->link) . "</p>";
        }
        
        if ($rejectResult) {
            $message = "<div class='alert alert-warning'>❌ User rejected. Reason: $rejectionReason</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to reject user!</div>";
        }
    }
}

// Get all pending users for testing
$pendingQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
                FROM tbl_user u 
                LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                WHERE u.userStatus = 0 AND (u.userLevel = 2 OR u.userLevel = 3)
                ORDER BY u.userId DESC";

$pendingResult = $db->select($pendingQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Admin Verification Process</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .user-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f9f9f9; }
        .btn { padding: 8px 15px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        textarea { width: 100%; margin: 10px 0; padding: 5px; }
    </style>
</head>
<body>

<h1>🔧 Debug Admin Verification Process</h1>

<?php if(!empty($message)) echo $message; ?>

<h2>📋 Pending Users (Debug Mode)</h2>

<?php if ($pendingResult && $pendingResult->num_rows > 0): ?>
    <?php while($user = $pendingResult->fetch_assoc()): ?>
        <div class="user-card">
            <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?> 
                (ID: <?php echo $user['userId']; ?>)</h3>
            
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['userEmail']); ?></p>
            <p><strong>Type:</strong> <?php 
                if($user['userLevel'] == 2) echo '🏠 Property Owner';
                elseif($user['userLevel'] == 3) echo '🏢 Real Estate Agent';
                else echo '👤 Regular User';
            ?></p>
            <p><strong>Current Status:</strong> <?php echo $user['userStatus'] == 0 ? '⏳ PENDING' : '✅ ACTIVE'; ?></p>
            <p><strong>Verification Status:</strong> <?php echo $user['verification_status'] ?? 'No record'; ?></p>
            
            <div>
                <!-- Approve Button -->
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                    <button type="submit" name="approve_user" class="btn btn-success" onclick="return confirm('Approve this user?')">✅ Approve</button>
                </form>
                
                <!-- Reject Button -->
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                    <textarea name="rejection_reason" placeholder="Rejection reason..." rows="2"></textarea><br>
                    <button type="submit" name="reject_user" class="btn btn-warning" onclick="return confirm('Reject this user?')">❌ Reject</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>✅ No pending users found.</p>
<?php endif; ?>

<p><a href="verify_users.php" class="btn btn-success">🔙 Back to Normal Verification Page</a></p>

</body>
</html>
