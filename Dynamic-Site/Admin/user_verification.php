<?php
include"inc/header.php";

/*========================
Admin Access Control
========================*/
if(Session::get("userLevel") != 3){
    echo"<script>window.location='../index.php'</script>";
}

$message = "";

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['approve_user'])) {
        $userId = intval($_POST['user_id']);
        
        // Update user status to active
        $updateUser = "UPDATE tbl_user SET userStatus = 1 WHERE userId = $userId";
        
        if ($db->update($updateUser)) {
            // Update verification record if exists
            $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'approved', reviewed_at = NOW(), reviewed_by = " . Session::get("userId") . " WHERE user_id = $userId";
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
        $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'rejected', reviewed_at = NOW(), reviewed_by = " . Session::get("userId") . ", admin_comments = '$rejectionReason' WHERE user_id = $userId";
        
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
                WHERE u.userStatus = 0 AND u.userLevel = 2 
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

<style>
    .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    
    .user-card { 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 20px; 
        margin: 15px 0; 
        background: #f9f9f9; 
    }
    
    .user-card.pending { border-left: 5px solid #ffc107; }
    .user-card.approved { border-left: 5px solid #28a745; }
    .user-card.rejected { border-left: 5px solid #dc3545; }
    
    .user-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
    .user-actions { margin-top: 15px; }
    .btn { padding: 8px 15px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-info { background: #17a2b8; color: white; }
    
    .document-preview { max-width: 150px; max-height: 100px; border: 1px solid #ccc; border-radius: 4px; margin: 5px; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
</style>

<!--Dashboard Section Start------------->
<div class="container">
    <div class="mcol_12 admin_page_title">
        <div class="page_title overflow">
            <h1 class="sub-title">👥 User Verification Management</h1>
            <h4><a href="?action=logout"><i class="fa-solid fa-right-from-bracket"></i><span>sign out</span></a></h4>
        </div>
    </div>
    
    <div class="responsive_mcol_small mcol_12">
        <?php include"inc/sidebar.php";?>
        
        <div class="responsive_mcol responsive_mcol_small mcol_8">
            <div class="admin_content overflow">
            
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
                        <h2 style="color: #ffc107;"><?php echo $pendingCount; ?></h2>
                        <p>Agents/Owners waiting for verification</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>✅ Approved</h3>
                        <h2 style="color: #28a745;"><?php echo $approvedNum; ?></h2>
                        <p>Successfully verified users</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>❌ Rejected</h3>
                        <h2 style="color: #dc3545;"><?php echo $rejectedNum; ?></h2>
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
                                    <strong>🏷️ User Type:</strong> <?php echo $user['userLevel'] == 2 ? 'Agent/Owner' : 'User'; ?><br>
                                </div>
                                <div>
                                    <strong>📍 Address:</strong> <?php echo htmlspecialchars($user['userAddress'] ?? 'Not provided'); ?><br>
                                    <strong>📅 Registered:</strong> <?php echo $user['submitted_at'] ?? 'N/A'; ?><br>
                                    <strong>🆔 Citizenship ID:</strong> <?php echo htmlspecialchars($user['citizenship_id'] ?? 'Not provided'); ?><br>
                                </div>
                            </div>
                            
                            <!-- Document Preview -->
                            <?php if ($user['citizenship_front'] || $user['citizenship_back'] || $user['business_license']): ?>
                                <div style="margin: 15px 0;">
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
                                <div id="reject_form_<?php echo $user['userId']; ?>" style="display: none; margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 5px;">
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                                        <label><strong>Rejection Reason:</strong></label><br>
                                        <textarea name="rejection_reason" rows="3" style="width: 100%; margin: 10px 0;" placeholder="Please provide a reason for rejection..."></textarea><br>
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
                            <p><strong>📅 Processed:</strong> <?php echo $recent['reviewed_at']; ?></p>
                            <?php if ($recent['admin_comments']): ?>
                                <p><strong>💬 Admin Comments:</strong> <?php echo htmlspecialchars($recent['admin_comments']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">No recently processed verifications.</p>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<script>
function showRejectForm(userId) {
    document.getElementById('reject_form_' + userId).style.display = 'block';
}

function hideRejectForm(userId) {
    document.getElementById('reject_form_' + userId).style.display = 'none';
}
</script>

<?php include"inc/footer.php";?>
