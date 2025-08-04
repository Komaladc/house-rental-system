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

// Handle property actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_property'])) {
        $propertyId = intval($_POST['property_id']);
        $updateQuery = "UPDATE tbl_property SET status = 1 WHERE propertyId = $propertyId";
        if ($db->update($updateQuery)) {
            $message = "<div class='alert alert-success'>✅ Property approved successfully!</div>";
        }
    }
    
    if (isset($_POST['reject_property'])) {
        $propertyId = intval($_POST['property_id']);
        $updateQuery = "UPDATE tbl_property SET status = 0 WHERE propertyId = $propertyId";
        if ($db->update($updateQuery)) {
            $message = "<div class='alert alert-warning'>⚠️ Property rejected successfully!</div>";
        }
    }
    
    if (isset($_POST['delete_property'])) {
        $propertyId = intval($_POST['property_id']);
        $deleteQuery = "DELETE FROM tbl_property WHERE propertyId = $propertyId";
        if ($db->delete($deleteQuery)) {
            $message = "<div class='alert alert-danger'>❌ Property deleted successfully!</div>";
        }
    }
}

// Get filter parameters
$filterCategory = isset($_GET['category']) ? intval($_GET['category']) : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($db->link, $_GET['search']) : '';
$filterLocation = isset($_GET['location']) ? mysqli_real_escape_string($db->link, $_GET['location']) : '';

// Build query
$whereConditions = [];
if ($filterCategory) {
    $whereConditions[] = "p.categoryId = $filterCategory";
}
if ($filterStatus !== '') {
    $whereConditions[] = "p.status = " . ($filterStatus == 'active' ? 1 : 0);
}
if ($searchTerm) {
    $whereConditions[] = "(p.propertyTitle LIKE '%$searchTerm%' OR p.propertyDetails LIKE '%$searchTerm%')";
}
if ($filterLocation) {
    $whereConditions[] = "p.propertyLocation LIKE '%$filterLocation%'";
}

$whereClause = empty($whereConditions) ? "" : "WHERE " . implode(" AND ", $whereConditions);

$propertiesQuery = "
    SELECT p.*, c.categoryName, u.firstName, u.lastName, u.userEmail 
    FROM tbl_property p 
    LEFT JOIN tbl_category c ON p.categoryId = c.categoryId 
    LEFT JOIN tbl_user u ON p.ownerId = u.userId 
    $whereClause 
    ORDER BY p.created_at DESC
";
$properties = $db->select($propertiesQuery);

// Get categories for filter
$categoriesQuery = "SELECT * FROM tbl_category ORDER BY categoryName";
$categories = $db->select($categoriesQuery);

// Get statistics
$totalProperties = $db->select("SELECT COUNT(*) as count FROM tbl_property")->fetch_assoc()['count'];
$activeProperties = $db->select("SELECT COUNT(*) as count FROM tbl_property WHERE status = 1")->fetch_assoc()['count'];
$pendingProperties = $db->select("SELECT COUNT(*) as count FROM tbl_property WHERE status = 0")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - Admin Panel</title>
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
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .property-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .property-image {
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
        }
        
        .property-status {
            position: absolute;
            top: 10px;
            right: 10px;
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
        
        .property-content {
            padding: 20px;
        }
        
        .property-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .property-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .property-price {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        
        .property-location {
            color: #666;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .property-owner {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .property-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
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
                <a href="login.php?logout=1">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="page-title">🏘️ Property Management</h1>
        
        <?php echo $message; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalProperties; ?></div>
                <div class="stat-label">Total Properties</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeProperties; ?></div>
                <div class="stat-label">Active Properties</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendingProperties; ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="form-group">
                    <label>🔍 Search Properties</label>
                    <input type="text" name="search" placeholder="Title, description..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                
                <div class="form-group">
                    <label>📍 Location</label>
                    <input type="text" name="location" placeholder="Location..." value="<?php echo htmlspecialchars($filterLocation); ?>">
                </div>
                
                <div class="form-group">
                    <label>🏗️ Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['categoryId']; ?>" <?php echo $filterCategory == $category['categoryId'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['categoryName']); ?>
                                </option>
                            <?php endwhile; ?>
                            <?php $categories->data_seek(0); // Reset pointer ?>
                        <?php endif; ?>
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
        
        <!-- Properties Grid -->
        <div class="properties-grid">
            <?php if ($properties && $properties->num_rows > 0): ?>
                <?php while ($property = $properties->fetch_assoc()): ?>
                <div class="property-card">
                    <div class="property-image">
                        🏠
                        <span class="property-status status-<?php echo $property['status'] ? 'active' : 'inactive'; ?>">
                            <?php echo $property['status'] ? '✅ Active' : '❌ Inactive'; ?>
                        </span>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title"><?php echo htmlspecialchars($property['propertyTitle']); ?></h3>
                        
                        <div class="property-meta">
                            <span>🏗️ <?php echo htmlspecialchars($property['categoryName'] ?? 'N/A'); ?></span>
                            <span>📅 <?php echo date('M d, Y', strtotime($property['created_at'])); ?></span>
                        </div>
                        
                        <div class="property-price">💰 Rs. <?php echo number_format($property['propertyPrice']); ?></div>
                        
                        <div class="property-location">
                            📍 <?php echo htmlspecialchars($property['propertyLocation']); ?>
                        </div>
                        
                        <div class="property-owner">
                            👤 Owner: <?php echo htmlspecialchars($property['firstName'] . ' ' . $property['lastName']); ?>
                            <br>
                            📧 <?php echo htmlspecialchars($property['userEmail']); ?>
                        </div>
                        
                        <div class="property-actions">
                            <?php if ($property['status']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="property_id" value="<?php echo $property['propertyId']; ?>">
                                    <button type="submit" name="reject_property" class="btn btn-warning btn-sm" onclick="return confirm('Reject this property?')">
                                        ❌ Reject
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="property_id" value="<?php echo $property['propertyId']; ?>">
                                    <button type="submit" name="approve_property" class="btn btn-success btn-sm" onclick="return confirm('Approve this property?')">
                                        ✅ Approve
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="property_id" value="<?php echo $property['propertyId']; ?>">
                                <button type="submit" name="delete_property" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this property? This action cannot be undone!')">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 64px; margin-bottom: 20px;">🏘️</div>
                    <h3>No Properties Found</h3>
                    <p style="color: #666; margin-top: 10px;">Try adjusting your search filters or check back later for new listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Add confirmation dialogs for critical actions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const deleteBtn = this.querySelector('button[name="delete_property"]');
                if (deleteBtn && e.submitter === deleteBtn) {
                    if (!confirm('⚠️ WARNING: This will permanently delete the property and all associated data. This action cannot be undone!\n\nAre you absolutely sure?')) {
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
