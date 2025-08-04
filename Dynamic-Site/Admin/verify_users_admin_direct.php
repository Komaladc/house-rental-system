<?php
// Direct admin access to user verification - no signin required
session_start();

// Set admin session if not already set
if(!isset($_SESSION['userLevel']) || $_SESSION['userLevel'] != 3) {
    $_SESSION['userlogin'] = true;
    $_SESSION['userId'] = 1;
    $_SESSION['userLevel'] = 3;
    $_SESSION['userName'] = 'admin';
    $_SESSION['userFName'] = 'Admin';
    $_SESSION['userLName'] = 'User';
    $_SESSION['userEmail'] = 'admin@houserental.com';
}

// Include necessary files for database and functionality
include'../lib/Database.php';
include'../helpers/Format.php';

// Safe autoloader that handles missing classes
spl_autoload_register(function($class){
    $classFile = '../classes/'.$class.'.php';
    if(file_exists($classFile)) {
        include_once $classFile;
    }
});

$db  = new Database();
$fm  = new Format();
$usr = new User();

$message = "";

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['approve_user'])) {
        $userId = intval($_POST['user_id']);
        
        // Update user status to active
        $updateUser = "UPDATE tbl_user SET userStatus = 1 WHERE userId = $userId";
        
        if ($db->update($updateUser)) {
            // Update verification record if exists
            $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'approved', reviewed_at = NOW(), reviewed_by = 1 WHERE user_id = $userId";
            $db->update($updateVerification);
            
            $message = "<div class='alert alert-success'>✅ User approved successfully! They can now sign in.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to approve user!</div>";
        }
    }
    
    if (isset($_POST['reject_user'])) {
        $userId = intval($_POST['user_id']);
        $rejectionReason = mysqli_real_escape_string($db->link, $_POST['rejection_reason'] ?? 'No reason provided');
        
        // Keep user status as 0 (inactive) and update verification record
        $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'rejected', reviewed_at = NOW(), reviewed_by = 1, admin_comments = '$rejectionReason' WHERE user_id = $userId";
        
        if ($db->update($updateVerification)) {
            $message = "<div class='alert alert-warning'>❌ User rejected. Reason: $rejectionReason</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to reject user!</div>";
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = intval($_POST['user_id']);
        
        // Delete from verification table first
        $deleteVerification = "DELETE FROM tbl_user_verification WHERE user_id = $userId";
        $db->delete($deleteVerification);
        
        // Delete user
        $deleteUser = "DELETE FROM tbl_user WHERE userId = $userId";
        
        if ($db->delete($deleteUser)) {
            $message = "<div class='alert alert-info'>🗑️ User account deleted permanently.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to delete user!</div>";
        }
    }
}

// Get pending users (agents/owners with userStatus = 0)
$pendingQuery = "SELECT u.*, v.verification_status, v.citizenship_id, v.citizenship_front, v.citizenship_back, v.business_license, v.submitted_at, v.admin_comments
                FROM tbl_user u 
                LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
                WHERE u.userStatus = 0 AND (u.userLevel = 2 OR u.userLevel = 3)
                ORDER BY u.userId DESC";

$pendingResult = $db->select($pendingQuery);

// Get recently approved/rejected users
$recentQuery = "SELECT u.*, v.verification_status, v.reviewed_at, v.admin_comments
               FROM tbl_user u 
               LEFT JOIN tbl_user_verification v ON u.userId = v.user_id 
               WHERE v.verification_status IN ('approved', 'rejected') 
               ORDER BY v.reviewed_at DESC 
               LIMIT 10";

$recentResult = $db->select($recentQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin User Verification - Direct Access</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        
        h1 { color: #333; margin-bottom: 30px; text-align: center; padding-bottom: 15px; border-bottom: 3px solid #007cba; }
        h2 { color: #555; margin: 30px 0 20px 0; }
        h3 { color: #007cba; margin: 15px 0; }
        
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; font-weight: bold; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        
        .user-card { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
            background: #fafafa; 
            transition: all 0.3s ease;
        }
        
        .user-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        .user-card.pending { border-left: 5px solid #ffc107; }
        .user-card.approved { border-left: 5px solid #28a745; }
        .user-card.rejected { border-left: 5px solid #dc3545; }
        
        .user-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .user-actions { margin-top: 20px; text-align: center; }
        
        .btn { 
            padding: 12px 20px; 
            margin: 8px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-primary { background: #007cba; color: white; }
        
        .document-preview { max-width: 150px; max-height: 100px; border: 1px solid #ccc; border-radius: 4px; margin: 5px; transition: transform 0.3s ease; }
        .document-preview:hover { transform: scale(1.1); }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; text-align: center; }
        .stat-card h3 { margin-bottom: 10px; }
        .stat-card h2 { font-size: 2.5em; margin: 10px 0; }
        
        .admin-header { background: linear-gradient(135deg, #007cba 0%, #005a8b 100%); color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 10px 10px 0 0; }
        .admin-header h1 { color: white; border: none; margin: 0; }
        
        .navigation { text-align: center; margin: 20px 0; }
        .navigation a { margin: 0 10px; }
        
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; resize: vertical; }
        
        .reject-form { display: none; margin-top: 15px; padding: 20px; background: #fff3cd; border-radius: 5px; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>

<div class="container">
    <div class="admin-header">
        <h1>🔑 Admin User Verification Panel</h1>
        <p>Direct admin access - No signin required</p>
    </div>
    
    <div class="navigation">
        <a href="dashboard_agent.php" class="btn btn-primary">📊 Dashboard</a>
        <a href="user_verification.php" class="btn btn-info">👥 Alt Verification</a>
        <a href="set_admin_session.php" class="btn btn-warning">🔧 Reset Session</a>
    </div>
    
    <?php if(!empty($message)) echo $message; ?>
    
    <!-- Statistics -->
    <div class="stats">
        <?php
        $pendingCount = $pendingResult ? $pendingResult->num_rows : 0;
        $approvedCount = $db->select("SELECT COUNT(*) as count FROM tbl_user u JOIN tbl_user_verification v ON u.userId = v.user_id WHERE v.verification_status = 'approved'");
        $rejectedCount = $db->select("SELECT COUNT(*) as count FROM tbl_user u JOIN tbl_user_verification v ON u.userId = v.user_id WHERE v.verification_status = 'rejected'");
        
        $approvedNum = $approvedCount ? $approvedCount->fetch_assoc()['count'] : 0;
        $rejectedNum = $rejectedCount ? $rejectedCount->fetch_assoc()['count'] : 0;
        ?>
        
        <div class="stat-card">
            <h3>⏳ Pending Approval</h3>
            <h2><?php echo $pendingCount; ?></h2>
            <p>Agents/Owners waiting for verification</p>
        </div>
        
        <div class="stat-card">
            <h3>✅ Approved</h3>
            <h2><?php echo $approvedNum; ?></h2>
            <p>Successfully verified users</p>
        </div>
        
        <div class="stat-card">
            <h3>❌ Rejected</h3>
            <h2><?php echo $rejectedNum; ?></h2>
            <p>Rejected verification requests</p>
        </div>
    </div>
    
    <!-- Pending Users Section -->
    <h2>⏳ Pending User Verifications</h2>
    
    <?php if ($pendingResult && $pendingResult->num_rows > 0): ?>
        <?php while($user = $pendingResult->fetch_assoc()): ?>
            <div class="user-card pending">
                <h3>🏠 <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3>
                
                <div class="user-info">
                    <div>
                        <strong>👤 Username:</strong> <?php echo htmlspecialchars($user['userName']); ?><br>
                        <strong>📧 Email:</strong> <?php echo htmlspecialchars($user['userEmail']); ?><br>
                        <strong>📱 Phone:</strong> <?php echo htmlspecialchars($user['cellNo']); ?><br>
                        <strong>🏷️ User Type:</strong> <?php 
                            if($user['userLevel'] == 2) echo '🏠 Property Owner';
                            elseif($user['userLevel'] == 3) echo '🏢 Real Estate Agent';
                            else echo '👤 Regular User';
                        ?><br>
                    </div>
                    <div>
                        <strong>📍 Address:</strong> <?php echo htmlspecialchars($user['userAddress'] ?? 'Not provided'); ?><br>
                        <strong>📅 Registered:</strong> <?php echo $user['submitted_at'] ?? 'N/A'; ?><br>
                        <strong>🆔 Citizenship ID:</strong> <?php echo htmlspecialchars($user['citizenship_id'] ?? 'Not provided'); ?><br>
                    </div>
                </div>
                
                <!-- Document Preview -->
                <?php if ($user['citizenship_front'] || $user['citizenship_back'] || $user['business_license']): ?>
                    <div style="margin: 20px 0;">
                        <strong>📄 Uploaded Documents:</strong><br>
                        <?php if ($user['citizenship_front']): ?>
                            <img src="../uploads/<?php echo $user['citizenship_front']; ?>" alt="Citizenship Front" class="document-preview" title="Citizenship Front">
                        <?php endif; ?>
                        <?php if ($user['citizenship_back']): ?>
                            <img src="../uploads/<?php echo $user['citizenship_back']; ?>" alt="Citizenship Back" class="document-preview" title="Citizenship Back">
                        <?php endif; ?>
                        <?php if ($user['business_license']): ?>
                            <img src="../uploads/<?php echo $user['business_license']; ?>" alt="Business License" class="document-preview" title="Business License">
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">📄 No documents uploaded</p>
                <?php endif; ?>
                
                <div class="user-actions">
                    <!-- Approve Button -->
                    <form method="POST" style="display: inline;" onsubmit="return confirm('✅ Are you sure you want to approve this user? They will be able to sign in and add properties.')">
                        <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                        <button type="submit" name="approve_user" class="btn btn-success">✅ Approve User</button>
                    </form>
                    
                    <!-- Reject Button with Reason -->
                    <button class="btn btn-warning" onclick="showRejectForm(<?php echo $user['userId']; ?>)">❌ Reject</button>
                    
                    <!-- Delete Button -->
                    <form method="POST" style="display: inline;" onsubmit="return confirm('🗑️ Are you sure you want to permanently delete this user account? This cannot be undone.')">
                        <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger">🗑️ Delete</button>
                    </form>
                    
                    <!-- Hidden Reject Form -->
                    <div id="reject_form_<?php echo $user['userId']; ?>" class="reject-form">
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                            <label><strong>Rejection Reason:</strong></label><br>
                            <textarea name="rejection_reason" rows="3" placeholder="Please provide a reason for rejection..."></textarea><br>
                            <button type="submit" name="reject_user" class="btn btn-warning">❌ Confirm Rejection</button>
                            <button type="button" class="btn" onclick="hideRejectForm(<?php echo $user['userId']; ?>)" style="background: #6c757d; color: white;">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            🎉 <strong>All caught up!</strong> There are no pending user verifications at the moment.
        </div>
    <?php endif; ?>
    
    <!-- Recently Processed Section -->
    <h2 style="margin-top: 40px;">📋 Recently Processed</h2>
    
    <?php if ($recentResult && $recentResult->num_rows > 0): ?>
        <?php while($recent = $recentResult->fetch_assoc()): ?>
            <div class="user-card <?php echo $recent['verification_status']; ?>">
                <h4>
                    <?php echo htmlspecialchars($recent['firstName'] . ' ' . $recent['lastName']); ?>
                    <span style="float: right; font-size: 14px;">
                        <?php echo $recent['verification_status'] == 'approved' ? '✅ APPROVED' : '❌ REJECTED'; ?>
                    </span>
                </h4>
                <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($recent['userEmail']); ?></p>
                <p><strong>📅 Processed:</strong> <?php echo $recent['reviewed_at'] ?? 'N/A'; ?></p>
                <?php if (!empty($recent['admin_comments'])): ?>
                    <p><strong>💬 Admin Comments:</strong> <?php echo htmlspecialchars($recent['admin_comments']); ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: #666; font-style: italic;">No recently processed verifications.</p>
    <?php endif; ?>
    
</div>

<script>
function showRejectForm(userId) {
    document.getElementById('reject_form_' + userId).style.display = 'block';
}

function hideRejectForm(userId) {
    document.getElementById('reject_form_' + userId).style.display = 'none';
}
</script>

</body>
</html>
