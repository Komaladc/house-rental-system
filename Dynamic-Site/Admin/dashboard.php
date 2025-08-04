<?php
session_start();
include "../lib/Database.php";

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$db = new Database();

// Get dashboard statistics
function getDashboardStats($db) {
    $stats = [];
    
    try {
        // Total users by type with error handling
        $userResult = $db->select("SELECT COUNT(*) as count FROM tbl_user");
        $stats['total_users'] = $userResult ? $userResult->fetch_assoc()['count'] : 0;
        
        $seekersResult = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 1");
        $stats['seekers'] = $seekersResult ? $seekersResult->fetch_assoc()['count'] : 0;
        
        $ownersResult = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 2");
        $stats['owners'] = $ownersResult ? $ownersResult->fetch_assoc()['count'] : 0;
        
        $agentsResult = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE userLevel = 3");
        $stats['agents'] = $agentsResult ? $agentsResult->fetch_assoc()['count'] : 0;
        
        // Verification statistics with error handling
        $pendingResult = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'pending'");
        $stats['pending_verifications'] = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;
        
        $approvedResult = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'verified'");
        $stats['approved_verifications'] = $approvedResult ? $approvedResult->fetch_assoc()['count'] : 0;
        
        $rejectedResult = $db->select("SELECT COUNT(*) as count FROM tbl_user_verification WHERE verification_status = 'rejected'");
        $stats['rejected_verifications'] = $rejectedResult ? $rejectedResult->fetch_assoc()['count'] : 0;
        
        // Properties with error handling
        $propertiesResult = $db->select("SELECT COUNT(*) as count FROM tbl_property");
        $stats['total_properties'] = $propertiesResult ? $propertiesResult->fetch_assoc()['count'] : 0;
        
        // Bookings (optional table) with error handling
        $bookingsResult = $db->select("SELECT COUNT(*) as count FROM tbl_booking");
        $stats['total_bookings'] = $bookingsResult ? $bookingsResult->fetch_assoc()['count'] : 0;
        
        // Recent registrations (last 7 days) with error handling
        $recentResult = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stats['recent_registrations'] = $recentResult ? $recentResult->fetch_assoc()['count'] : 0;
        
    } catch (Exception $e) {
        // If any query fails, set default values
        $stats = [
            'total_users' => 0,
            'seekers' => 0,
            'owners' => 0,
            'agents' => 0,
            'pending_verifications' => 0,
            'approved_verifications' => 0,
            'rejected_verifications' => 0,
            'total_properties' => 0,
            'total_bookings' => 0,
            'recent_registrations' => 0
        ];
    }
    
    return $stats;
}

$stats = getDashboardStats($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Property Nepal</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 5px;
            z-index: 1;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        
        .dropdown-content a:hover {
            background: #f1f1f1;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
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
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            text-align: center;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .action-btn .icon {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }
        
        .recent-activity {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: #333;
        }
        
        .activity-time {
            font-size: 12px;
            color: #666;
        }
        
        .pending-badge {
            background: #ffc107;
            color: #856404;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .quick-actions {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🏠 Property Nepal - Admin</div>
            <div class="admin-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</span>
                <div class="dropdown">
                    <button class="dropdown-btn">
                        ⚙️ Menu ▼
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">📊 Dashboard</a>
                        <a href="verify_users.php">✅ Verify Users</a>
                        <a href="manage_users.php">👥 Manage Users</a>
                        <a href="manage_properties.php">🏘️ Manage Properties</a>
                        <a href="analytics.php">📈 Analytics & Settings</a>
                        <a href="login.php?logout=1">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">📊 Dashboard Overview</h1>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $stats['pending_verifications']; ?></div>
                <div class="stat-label">Pending Verifications</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏠</div>
                <div class="stat-number"><?php echo $stats['total_properties']; ?></div>
                <div class="stat-label">Total Properties</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏠</div>
                <div class="stat-number"><?php echo $stats['seekers']; ?></div>
                <div class="stat-label">Property Seekers</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏘️</div>
                <div class="stat-number"><?php echo $stats['owners']; ?></div>
                <div class="stat-label">Property Owners</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-number"><?php echo $stats['agents']; ?></div>
                <div class="stat-label">Real Estate Agents</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🆕</div>
                <div class="stat-number"><?php echo $stats['recent_registrations']; ?></div>
                <div class="stat-label">New This Week</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="verify_users.php" class="action-btn">
                <span class="icon">✅</span>
                <strong>Verify Users</strong>
                <?php if($stats['pending_verifications'] > 0): ?>
                    <span class="pending-badge"><?php echo $stats['pending_verifications']; ?> pending</span>
                <?php endif; ?>
            </a>
            
            <a href="manage_users.php" class="action-btn">
                <span class="icon">👥</span>
                <strong>Manage Users</strong>
            </a>
            
            <a href="manage_properties.php" class="action-btn">
                <span class="icon">🏠</span>
                <strong>Manage Properties</strong>
            </a>
            
            <a href="manage_bookings.php" class="action-btn">
                <span class="icon">📅</span>
                <strong>Manage Bookings</strong>
            </a>
            
            <a href="analytics.php" class="action-btn">
                <span class="icon">📈</span>
                <strong>Analytics</strong>
            </a>
            
            <a href="settings.php" class="action-btn">
                <span class="icon">⚙️</span>
                <strong>Settings</strong>
            </a>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2 class="section-title">📋 Recent Activity</h2>
            
            <?php
            // Get recent admin logs
            $recentLogs = $db->select("SELECT al.*, u.firstName, u.lastName 
                                     FROM tbl_admin_logs al 
                                     LEFT JOIN tbl_user u ON al.admin_id = u.userId 
                                     ORDER BY al.created_at DESC 
                                     LIMIT 5");
            
            if ($recentLogs && $recentLogs->num_rows > 0):
                while ($log = $recentLogs->fetch_assoc()):
            ?>
            <div class="activity-item">
                <div class="activity-icon" style="background: #e7f3ff; color: #007cba;">
                    📋
                </div>
                <div class="activity-content">
                    <div class="activity-title"><?php echo htmlspecialchars($log['description']); ?></div>
                    <div class="activity-time"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="activity-item">
                <div class="activity-icon" style="background: #f8f9fa; color: #666;">
                    📝
                </div>
                <div class="activity-content">
                    <div class="activity-title">No recent activity</div>
                    <div class="activity-time">Start managing your property platform!</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto-refresh dashboard every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
        
        // Add click animations
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
</body>
</html>
