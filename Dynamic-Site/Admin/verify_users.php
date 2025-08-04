<?php
session_start();
include "../lib/Database.php";

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$message = "";

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_user'])) {
        $userId = intval($_POST['user_id']);
        $verificationId = intval($_POST['verification_id']);
        
        // Update user status
        $updateUser = "UPDATE tbl_user SET userStatus = 1 WHERE userId = $userId";
        $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'approved', verified_at = NOW(), verified_by = {$_SESSION['admin_id']} WHERE verification_id = $verificationId";
        
        if ($db->update($updateUser) && $db->update($updateVerification)) {
            // Log action
            $logAction = "INSERT INTO tbl_admin_logs (admin_id, action, target_type, target_id, description) 
                         VALUES ({$_SESSION['admin_id']}, 'approve_user', 'user', $userId, 'Approved user verification')";
            $db->insert($logAction);
            
            $message = "<div class='alert alert-success'>✅ User approved successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to approve user!</div>";
        }
    }
    
    if (isset($_POST['reject_user'])) {
        $userId = intval($_POST['user_id']);
        $verificationId = intval($_POST['verification_id']);
        $rejectionReason = mysqli_real_escape_string($db->link, $_POST['rejection_reason']);
        
        // Update verification status
        $updateVerification = "UPDATE tbl_user_verification SET verification_status = 'rejected', verified_at = NOW(), verified_by = {$_SESSION['admin_id']}, rejection_reason = '$rejectionReason' WHERE verification_id = $verificationId";
        $updateUser = "UPDATE tbl_user SET userStatus = 0 WHERE userId = $userId"; // Keep user inactive
        
        if ($db->update($updateVerification) && $db->update($updateUser)) {
            // Log action
            $logAction = "INSERT INTO tbl_admin_logs (admin_id, action, target_type, target_id, description) 
                         VALUES ({$_SESSION['admin_id']}, 'reject_user', 'user', $userId, 'Rejected user verification: $rejectionReason')";
            $db->insert($logAction);
            
            $message = "<div class='alert alert-warning'>⚠️ User verification rejected!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to reject user!</div>";
        }
    }
}

// Get pending verifications
$pendingQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";
$pendingUsers = $db->select($pendingQuery);

// Debug: Log the query and results
error_log("Verify Users Query: " . $pendingQuery);
if ($pendingUsers) {
    error_log("Pending users found: " . $pendingUsers->num_rows);
} else {
    error_log("No pending users query result");
}

// Get recent verifications
$recentQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.userLevel, a.firstName as admin_fname, a.lastName as admin_lname
               FROM tbl_user_verification uv 
               JOIN tbl_user u ON uv.user_id = u.userId 
               LEFT JOIN tbl_user a ON uv.verified_by = a.userId
               WHERE uv.verification_status IN ('approved', 'rejected') 
               ORDER BY uv.verified_at DESC 
               LIMIT 10";
$recentVerifications = $db->select($recentQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Verification - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .verification-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .verification-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .user-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .info-value {
            color: #666;
        }
        
        .user-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-owner {
            background: #ffeaa7;
            color: #6c4e00;
        }
        
        .type-agent {
            background: #e1ecf7;
            color: #0c5460;
        }
        
        .documents-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .document-item {
            text-align: center;
        }
        
        .document-preview {
            width: 100%;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .document-preview:hover {
            border-color: #667eea;
        }
        
        .document-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 5px;
        }
        
        .document-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-view {
            background: #007cba;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .rejection-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #fff3cd;
            border-radius: 6px;
            border: 1px solid #ffeaa7;
        }
        
        .rejection-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
        }
        
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .recent-table th,
        .recent-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .recent-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            margin: 5% auto;
            max-width: 800px;
            max-height: 80vh;
            text-align: center;
        }
        
        .modal-content img {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .user-info {
                grid-template-columns: 1fr;
            }
            
            .document-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🏠 Property Nepal - Admin</div>
            <div class="nav-links">
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="verify_users.php">✅ Verify Users</a>
                <a href="manage_users.php">👥 Users</a>
                <a href="login.php?logout=1">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">✅ User Verification Management</h1>
        
        <?php echo $message; ?>
        
        <!-- Pending Verifications -->
        <div class="section">
            <h2 class="section-title">⏳ Pending Verifications</h2>
            
            <?php if ($pendingUsers && $pendingUsers->num_rows > 0): ?>
                <?php while ($user = $pendingUsers->fetch_assoc()): ?>
                <div class="verification-card">
                    <div class="user-info">
                        <div class="info-group">
                            <span class="info-label">👤 Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">📧 Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['userEmail']); ?></span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">📱 Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['cellNo']); ?></span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">🏠 Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['userAddress'] ?? 'Not provided'); ?></span>
                        </div>
                        
                        <?php if (!empty($user['citizenship_id'])): ?>
                        <div class="info-group">
                            <span class="info-label">🆔 Citizenship ID</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['citizenship_id']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-group">
                            <span class="info-label">👨‍💼 User Type</span>
                            <span class="user-type-badge type-<?php echo ($user['userLevel'] == 2) ? 'owner' : 'agent'; ?>">
                                <?php 
                                if ($user['userLevel'] == 1) echo '🏠 Property Seeker';
                                else if ($user['userLevel'] == 2) echo '🏘️ Property Owner';
                                else if ($user['userLevel'] == 3) echo '🏢 Real Estate Agent';
                                else echo 'Unknown';
                                ?>
                            </span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">📅 Submitted</span>
                            <span class="info-value"><?php echo date('M d, Y H:i', strtotime($user['submitted_at'])); ?></span>
                        </div>
                        
                        <!-- Debug Info (remove in production) -->
                        <div class="info-group" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                            <span class="info-label">🔍 Debug</span>
                            <span class="info-value" style="font-size: 12px;">
                                User ID: <?php echo $user['user_id']; ?> | 
                                Verification ID: <?php echo $user['id']; ?> | 
                                Level: <?php echo $user['userLevel']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="documents-section">
                        <h4>📋 Submitted Documents</h4>
                        <div class="document-grid">
                            <div class="document-item">
                                <div class="document-preview" onclick="openModal('../uploads/citizenship/<?php echo $user['citizenship_front']; ?>')">
                                    <?php if ($user['citizenship_front']): ?>
                                        <img src="../uploads/citizenship/<?php echo $user['citizenship_front']; ?>" alt="Citizenship Front">
                                    <?php else: ?>
                                        <span>❌ Not Provided</span>
                                    <?php endif; ?>
                                </div>
                                <div class="document-label">📄 Citizenship Front</div>
                                <?php if ($user['citizenship_front']): ?>
                                    <a href="../uploads/citizenship/<?php echo $user['citizenship_front']; ?>" target="_blank" class="btn btn-view">👁️ View</a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="document-item">
                                <div class="document-preview" onclick="openModal('../uploads/citizenship/<?php echo $user['citizenship_back']; ?>')">
                                    <?php if ($user['citizenship_back']): ?>
                                        <img src="../uploads/citizenship/<?php echo $user['citizenship_back']; ?>" alt="Citizenship Back">
                                    <?php else: ?>
                                        <span>❌ Not Provided</span>
                                    <?php endif; ?>
                                </div>
                                <div class="document-label">📄 Citizenship Back</div>
                                <?php if ($user['citizenship_back']): ?>
                                    <a href="../uploads/citizenship/<?php echo $user['citizenship_back']; ?>" target="_blank" class="btn btn-view">👁️ View</a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($user['user_type'] == 'agent' && $user['business_license']): ?>
                            <div class="document-item">
                                <div class="document-preview" onclick="openModal('../uploads/citizenship/<?php echo $user['business_license']; ?>')">
                                    <img src="../uploads/citizenship/<?php echo $user['business_license']; ?>" alt="Business License">
                                </div>
                                <div class="document-label">🏢 Business License</div>
                                <a href="../uploads/citizenship/<?php echo $user['business_license']; ?>" target="_blank" class="btn btn-view">👁️ View</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <input type="hidden" name="verification_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="approve_user" class="btn btn-approve" onclick="return confirm('Are you sure you want to approve this user?')">
                                ✅ Approve User
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-reject" onclick="showRejectionForm(<?php echo $user['id']; ?>)">
                            ❌ Reject User
                        </button>
                    </div>
                    
                    <div id="rejection-form-<?php echo $user['id']; ?>" class="rejection-form">
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <input type="hidden" name="verification_id" value="<?php echo $user['id']; ?>">
                            <label for="rejection_reason">❓ Reason for Rejection:</label>
                            <textarea name="rejection_reason" required placeholder="Please provide a clear reason for rejection..."></textarea>
                            <div style="margin-top: 10px;">
                                <button type="submit" name="reject_user" class="btn btn-reject">❌ Confirm Rejection</button>
                                <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="hideRejectionForm(<?php echo $user['id']; ?>)">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 20px;">✅</div>
                    <h3>No Pending Verifications</h3>
                    <p>All user verifications are up to date!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Verifications -->
        <div class="section">
            <h2 class="section-title">📋 Recent Verifications</h2>
            
            <?php if ($recentVerifications && $recentVerifications->num_rows > 0): ?>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>👤 User</th>
                            <th>📧 Email</th>
                            <th>👨‍💼 Type</th>
                            <th>✅ Status</th>
                            <th>👤 Verified By</th>
                            <th>📅 Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($verification = $recentVerifications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($verification['firstName'] . ' ' . $verification['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($verification['userEmail']); ?></td>
                            <td>
                                <span class="user-type-badge type-<?php echo ($verification['userLevel'] == 2) ? 'owner' : 'agent'; ?>">
                                    <?php 
                                    if ($verification['userLevel'] == 2) echo '🏘️ Owner';
                                    else if ($verification['userLevel'] == 3) echo '🏢 Agent';
                                    else echo 'Unknown';
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $verification['verification_status']; ?>">
                                    <?php echo $verification['verification_status'] == 'approved' ? '✅ Approved' : '❌ Rejected'; ?>
                                </span>
                            </td>
                            <td><?php echo $verification['admin_fname'] ? htmlspecialchars($verification['admin_fname'] . ' ' . $verification['admin_lname']) : 'N/A'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($verification['verified_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No recent verifications found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="Document">
        </div>
    </div>
    
    <script>
        function showRejectionForm(id) {
            document.getElementById('rejection-form-' + id).style.display = 'block';
        }
        
        function hideRejectionForm(id) {
            document.getElementById('rejection-form-' + id).style.display = 'none';
        }
        
        function openModal(imageSrc) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = imageSrc;
        }
        
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
