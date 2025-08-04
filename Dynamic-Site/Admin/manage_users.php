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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['activate_user'])) {
        $userId = intval($_POST['user_id']);
        $updateQuery = "UPDATE tbl_user SET status = 1 WHERE userId = $userId";
        if ($db->update($updateQuery)) {
            $message = "<div class='alert alert-success'>✅ User activated successfully!</div>";
        }
    }
    
    if (isset($_POST['deactivate_user'])) {
        $userId = intval($_POST['user_id']);
        $updateQuery = "UPDATE tbl_user SET status = 0 WHERE userId = $userId";
        if ($db->update($updateQuery)) {
            $message = "<div class='alert alert-warning'>⚠️ User deactivated successfully!</div>";
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = intval($_POST['user_id']);
        $deleteQuery = "DELETE FROM tbl_user WHERE userId = $userId";
        if ($db->delete($deleteQuery)) {
            $message = "<div class='alert alert-danger'>❌ User deleted successfully!</div>";
        }
    }
}

// Get filter parameters
$filterLevel = isset($_GET['level']) ? intval($_GET['level']) : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($db->link, $_GET['search']) : '';

// Build query
$whereConditions = [];
if ($filterLevel) {
    $whereConditions[] = "userLevel = $filterLevel";
}
if ($filterStatus !== '') {
    $whereConditions[] = "status = " . ($filterStatus == 'active' ? 1 : 0);
}
if ($searchTerm) {
    $whereConditions[] = "(firstName LIKE '%$searchTerm%' OR lastName LIKE '%$searchTerm%' OR userEmail LIKE '%$searchTerm%' OR userName LIKE '%$searchTerm%')";
}

$whereClause = empty($whereConditions) ? "" : "WHERE " . implode(" AND ", $whereConditions);

$usersQuery = "SELECT * FROM tbl_user $whereClause ORDER BY created_at DESC";
$users = $db->select($usersQuery);

// Get statistics
$totalUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc()['count'];
$activeUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE status = 1")->fetch_assoc()['count'];
$pendingUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE verification_status = 'pending'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #007cba;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005a8b;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .users-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
        }
        
        .user-email {
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .verification-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .verification-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .verification-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .verification-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .level-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .level-1 {
            background: #e7f3ff;
            color: #0c5460;
        }
        
        .level-2 {
            background: #ffeaa7;
            color: #6c4e00;
        }
        
        .level-3 {
            background: #e1ecf7;
            color: #0c5460;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .actions {
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
        <h1 class="page-title">👥 User Management</h1>
        
        <?php echo $message; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeUsers; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendingUsers; ?></div>
                <div class="stat-label">Pending Verification</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="form-group">
                    <label>🔍 Search Users</label>
                    <input type="text" name="search" placeholder="Name, email, username..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                
                <div class="form-group">
                    <label>👨‍💼 User Level</label>
                    <select name="level">
                        <option value="">All Levels</option>
                        <option value="1" <?php echo $filterLevel == 1 ? 'selected' : ''; ?>>🏠 Property Seekers</option>
                        <option value="2" <?php echo $filterLevel == 2 ? 'selected' : ''; ?>>🏘️ Property Owners</option>
                        <option value="3" <?php echo $filterLevel == 3 ? 'selected' : ''; ?>>🏢 Real Estate Agents</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>📊 Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filterStatus == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $filterStatus == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">🔍 Filter</button>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="users-table">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>👤 User</th>
                            <th>👨‍💼 Level</th>
                            <th>📱 Phone</th>
                            <th>📊 Status</th>
                            <th>✅ Verification</th>
                            <th>📅 Joined</th>
                            <th>⚙️ Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['firstName'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($user['userEmail']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="level-badge level-<?php echo $user['userLevel']; ?>">
                                        <?php 
                                        switch($user['userLevel']) {
                                            case 1: echo '🏠 Seeker'; break;
                                            case 2: echo '🏘️ Owner'; break;
                                            case 3: echo '🏢 Agent'; break;
                                            default: echo 'Unknown';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['cellNo']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user['status'] ? '✅ Active' : '❌ Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="verification-badge verification-<?php echo $user['verification_status'] ?? 'verified'; ?>">
                                        <?php 
                                        switch($user['verification_status'] ?? 'verified') {
                                            case 'verified': echo '✅ Verified'; break;
                                            case 'pending': echo '⏳ Pending'; break;
                                            case 'rejected': echo '❌ Rejected'; break;
                                            default: echo '✅ Verified';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($user['status']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                                                <button type="submit" name="deactivate_user" class="btn btn-warning btn-sm" onclick="return confirm('Deactivate this user?')">
                                                    ⏸️ Deactivate
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                                                <button type="submit" name="activate_user" class="btn btn-success btn-sm" onclick="return confirm('Activate this user?')">
                                                    ▶️ Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['userId']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!')">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    <div style="font-size: 48px; margin-bottom: 20px;">👥</div>
                                    <h3>No Users Found</h3>
                                    <p>Try adjusting your search filters.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Add confirmation dialogs for critical actions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const deleteBtn = this.querySelector('button[name="delete_user"]');
                if (deleteBtn && e.submitter === deleteBtn) {
                    if (!confirm('⚠️ WARNING: This will permanently delete the user and all associated data. This action cannot be undone!\n\nAre you absolutely sure?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
        
        // Auto-refresh page every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
