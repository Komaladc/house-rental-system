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

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $siteName = mysqli_real_escape_string($db->link, $_POST['site_name']);
    $siteDescription = mysqli_real_escape_string($db->link, $_POST['site_description']);
    $contactEmail = mysqli_real_escape_string($db->link, $_POST['contact_email']);
    $contactPhone = mysqli_real_escape_string($db->link, $_POST['contact_phone']);
    $siteAddress = mysqli_real_escape_string($db->link, $_POST['site_address']);
    
    // Check if settings exist
    $checkQuery = "SELECT * FROM tbl_website_stats WHERE stat_name = 'site_settings'";
    $existing = $db->select($checkQuery);
    
    $settings = json_encode([
        'site_name' => $siteName,
        'site_description' => $siteDescription,
        'contact_email' => $contactEmail,
        'contact_phone' => $contactPhone,
        'site_address' => $siteAddress
    ]);
    
    if ($existing && $existing->num_rows > 0) {
        $updateQuery = "UPDATE tbl_website_stats SET stat_value = '$settings' WHERE stat_name = 'site_settings'";
        $result = $db->update($updateQuery);
    } else {
        $insertQuery = "INSERT INTO tbl_website_stats (stat_name, stat_value, created_at) VALUES ('site_settings', '$settings', NOW())";
        $result = $db->insert($insertQuery);
    }
    
    if ($result) {
        $message = "<div class='alert alert-success'>✅ Settings updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Error updating settings!</div>";
    }
}

// Get current settings
$settingsQuery = "SELECT * FROM tbl_website_stats WHERE stat_name = 'site_settings'";
$settingsResult = $db->select($settingsQuery);
$currentSettings = [];

if ($settingsResult && $settingsResult->num_rows > 0) {
    $settingsData = $settingsResult->fetch_assoc();
    $currentSettings = json_decode($settingsData['stat_value'], true) ?? [];
}

// Get analytics data
$totalUsers = $db->select("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc()['count'];
$totalProperties = $db->select("SELECT COUNT(*) as count FROM tbl_property")->fetch_assoc()['count'];
$totalCategories = $db->select("SELECT COUNT(*) as count FROM tbl_category")->fetch_assoc()['count'];
$pendingVerifications = $db->select("SELECT COUNT(*) as count FROM tbl_user WHERE verification_status = 'pending'")->fetch_assoc()['count'];

// Get user registrations by month (last 6 months)
$userStatsQuery = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as users 
    FROM tbl_user 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
";
$userStats = $db->select($userStatsQuery);

// Get property listings by month (last 6 months)
$propertyStatsQuery = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as properties 
    FROM tbl_property 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
";
$propertyStats = $db->select($propertyStatsQuery);

// Get recent activities
$activitiesQuery = "
    SELECT 
        'user_registration' as type,
        CONCAT(firstName, ' ', lastName) as description,
        created_at
    FROM tbl_user 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    UNION ALL
    
    SELECT 
        'property_listing' as type,
        propertyTitle as description,
        created_at
    FROM tbl_property 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    ORDER BY created_at DESC 
    LIMIT 10
";
$recentActivities = $db->select($activitiesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Settings - Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: #667eea;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .activity-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .activity-item {
            padding: 15px 20px;
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
        
        .activity-user {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .activity-property {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .activity-details h4 {
            margin: 0;
            color: #333;
            font-size: 14px;
        }
        
        .activity-details p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }
        
        .settings-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
                <a href="manage_properties.php">🏘️ Properties</a>
                <a href="analytics.php">📈 Analytics</a>
                <a href="login.php?logout=1">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">📈 Analytics & Settings</h1>
        
        <?php echo $message; ?>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('analytics')">📈 Analytics</button>
            <button class="tab" onclick="showTab('settings')">⚙️ Settings</button>
        </div>
        
        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content active">
            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalProperties; ?></div>
                    <div class="stat-label">Total Properties</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalCategories; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pendingVerifications; ?></div>
                    <div class="stat-label">Pending Verifications</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="chart-container">
                    <div class="chart-title">📈 User Registrations (Last 6 Months)</div>
                    <canvas id="userChart" width="400" height="200"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-title">🏘️ Property Listings (Last 6 Months)</div>
                    <canvas id="propertyChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="chart-container">
                <div class="chart-title">📋 Recent Activities (Last 7 Days)</div>
                <div class="activity-list">
                    <?php if ($recentActivities && $recentActivities->num_rows > 0): ?>
                        <?php while ($activity = $recentActivities->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-icon activity-<?php echo $activity['type'] == 'user_registration' ? 'user' : 'property'; ?>">
                                <?php echo $activity['type'] == 'user_registration' ? '👤' : '🏠'; ?>
                            </div>
                            <div class="activity-details">
                                <h4>
                                    <?php 
                                    if ($activity['type'] == 'user_registration') {
                                        echo "New user registered: " . htmlspecialchars($activity['description']);
                                    } else {
                                        echo "New property listed: " . htmlspecialchars($activity['description']);
                                    }
                                    ?>
                                </h4>
                                <p><?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div style="text-align: center; padding: 40px; color: #666; width: 100%;">
                                <div style="font-size: 48px; margin-bottom: 20px;">📋</div>
                                <h3>No Recent Activities</h3>
                                <p>Activities will appear here as they happen.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Settings Tab -->
        <div id="settings" class="tab-content">
            <div class="settings-form">
                <h2 style="margin-bottom: 30px; color: #333;">⚙️ Website Settings</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>🏠 Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($currentSettings['site_name'] ?? 'Property Nepal'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Site Description</label>
                        <textarea name="site_description" placeholder="Describe your website..."><?php echo htmlspecialchars($currentSettings['site_description'] ?? 'Find your dream home in Nepal. Browse through thousands of properties including houses, apartments, and commercial spaces.'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>📧 Contact Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($currentSettings['contact_email'] ?? 'admin@propertynepal.com'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>📱 Contact Phone</label>
                        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($currentSettings['contact_phone'] ?? '+977-XXX-XXX-XXXX'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>📍 Site Address</label>
                        <textarea name="site_address" placeholder="Physical address..."><?php echo htmlspecialchars($currentSettings['site_address'] ?? 'Kathmandu, Nepal'); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary">💾 Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        // Prepare chart data
        const userMonths = [];
        const userCounts = [];
        const propertyMonths = [];
        const propertyCounts = [];
        
        <?php if ($userStats && $userStats->num_rows > 0): ?>
            <?php while ($stat = $userStats->fetch_assoc()): ?>
                userMonths.push('<?php echo date('M Y', strtotime($stat['month'] . '-01')); ?>');
                userCounts.push(<?php echo $stat['users']; ?>);
            <?php endwhile; ?>
        <?php endif; ?>
        
        <?php if ($propertyStats && $propertyStats->num_rows > 0): ?>
            <?php while ($stat = $propertyStats->fetch_assoc()): ?>
                propertyMonths.push('<?php echo date('M Y', strtotime($stat['month'] . '-01')); ?>');
                propertyCounts.push(<?php echo $stat['properties']; ?>);
            <?php endwhile; ?>
        <?php endif; ?>
        
        // User Registration Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, {
            type: 'line',
            data: {
                labels: userMonths,
                datasets: [{
                    label: 'New Users',
                    data: userCounts,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Property Listings Chart
        const propertyCtx = document.getElementById('propertyChart').getContext('2d');
        new Chart(propertyCtx, {
            type: 'bar',
            data: {
                labels: propertyMonths,
                datasets: [{
                    label: 'New Properties',
                    data: propertyCounts,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
