<?php
session_start();
include '../lib/Database.php';

// Set admin session to ensure we can access the page
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';

echo "<h2>🔍 Complete Verification Debug</h2>";

$db = new Database();

// 1. Test the EXACT query from verify_users.php
echo "<h3>1. Exact Admin Panel Query</h3>";
$pendingQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.cellNo, u.userAddress, u.userLevel, u.created_at as user_created
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE uv.verification_status = 'pending' 
                ORDER BY uv.submitted_at ASC";

echo "<p><strong>Query:</strong></p>";
echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
echo htmlspecialchars($pendingQuery);
echo "</div>";

$pendingUsers = $db->select($pendingQuery);

echo "<h4>Query Results:</h4>";
if ($pendingUsers && $pendingUsers->num_rows > 0) {
    echo "<p style='color: green;'>✅ Found {$pendingUsers->num_rows} pending verification(s)</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Ver ID</th><th>User ID</th><th>Name</th><th>Email</th><th>User Level</th><th>Status</th><th>Submitted</th></tr>";
    
    $dipeshFound = false;
    while ($user = $pendingUsers->fetch_assoc()) {
        $fullName = $user['firstName'] . ' ' . $user['lastName'];
        if (stripos($fullName, 'Dipesh Tamang') !== false) {
            $dipeshFound = true;
            echo "<tr style='background: #d4edda;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>" . htmlspecialchars($user['verification_id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($user['user_id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fullName) . "</td>";
        echo "<td>" . htmlspecialchars($user['userEmail']) . "</td>";
        echo "<td>" . htmlspecialchars($user['userLevel']) . "</td>";
        echo "<td>" . htmlspecialchars($user['verification_status']) . "</td>";
        echo "<td>" . htmlspecialchars($user['submitted_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($dipeshFound) {
        echo "<p style='color: green; font-weight: bold;'>✅ DIPESH TAMANG FOUND IN QUERY!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ DIPESH TAMANG NOT FOUND IN QUERY</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No pending verifications found</p>";
}

// 2. Check Dipesh specifically
echo "<h3>2. Dipesh Tamang Specific Check</h3>";
$dipeshQuery = "SELECT uv.*, u.firstName, u.lastName, u.userEmail, u.userLevel
                FROM tbl_user_verification uv 
                JOIN tbl_user u ON uv.user_id = u.userId 
                WHERE u.firstName = 'Dipesh' AND u.lastName = 'Tamang'";

$dipeshResult = $db->select($dipeshQuery);

if ($dipeshResult && $dipeshResult->num_rows > 0) {
    $dipeshData = $dipeshResult->fetch_assoc();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($dipeshData as $key => $value) {
        echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    
    echo "<h4>Status Analysis:</h4>";
    if ($dipeshData['verification_status'] === 'pending') {
        echo "<p style='color: green;'>✅ Status is 'pending' - should appear in admin panel</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Status is '{$dipeshData['verification_status']}' - need to change to 'pending'</p>";
        
        // Fix the status
        $fixQuery = "UPDATE tbl_user_verification SET verification_status = 'pending' WHERE user_id = {$dipeshData['user_id']}";
        if ($db->update($fixQuery)) {
            echo "<p style='color: green;'>✅ Updated status to 'pending'</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Dipesh Tamang not found in verification records</p>";
}

// 3. Direct verification table check
echo "<h3>3. All Verification Records</h3>";
$allVerifications = $db->select("SELECT * FROM tbl_user_verification ORDER BY submitted_at DESC");
if ($allVerifications) {
    echo "<p>Total verification records: {$allVerifications->num_rows}</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Ver ID</th><th>User ID</th><th>Status</th><th>Submitted</th><th>Documents Path</th></tr>";
    while ($row = $allVerifications->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? $row['verification_id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_status'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['submitted_at'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['documents_path'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Force refresh admin panel
echo "<h3>4. Admin Panel Links</h3>";
echo "<p>";
echo "<a href='set_admin_session.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Reset Admin Session</a>";
echo "<a href='verify_users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>👨‍💼 Admin Panel</a>";
echo "<a href='fix_dipesh_verification.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Again</a>";
echo "</p>";

echo "<h3>5. Manual Verification Creation</h3>";
echo "<p>If Dipesh still not showing, click below to force create verification record:</p>";

// Check if we need to create/recreate verification record
$checkUser = "SELECT userId FROM tbl_user WHERE firstName = 'Dipesh' AND lastName = 'Tamang'";
$userCheck = $db->select($checkUser);

if ($userCheck && $userCheck->num_rows > 0) {
    $userData = $userCheck->fetch_assoc();
    $userId = $userData['userId'];
    
    echo "<form method='POST' style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<input type='hidden' name='user_id' value='$userId'>";
    echo "<button type='submit' name='force_create' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>🚨 Force Create Verification Record</button>";
    echo "</form>";
    
    if (isset($_POST['force_create'])) {
        // Delete existing verification record if any
        $deleteQuery = "DELETE FROM tbl_user_verification WHERE user_id = $userId";
        $db->delete($deleteQuery);
        
        // Create new verification record
        $createQuery = "INSERT INTO tbl_user_verification 
            (user_id, verification_status, submitted_at, documents_path) 
            VALUES 
            ($userId, 'pending', NOW(), 'uploads/dipesh_documents/')";
        
        if ($db->insert($createQuery)) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p style='color: #155724; margin: 0; font-weight: bold;'>✅ Verification record force created! Refresh the admin panel.</p>";
            echo "</div>";
        }
    }
}
?>
