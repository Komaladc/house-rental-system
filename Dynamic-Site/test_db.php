<?php
include 'lib/Database.php';
try {
    $db = new Database();
    echo 'Database connection successful<br>';
    
    // Test if admin users table exists
    $adminCheck = $db->select("SELECT COUNT(*) as count FROM tbl_admin_users");
    if ($adminCheck) {
        $result = $adminCheck->fetch_assoc();
        echo 'Admin users table exists with ' . $result['count'] . ' records<br>';
    } else {
        echo 'Admin users table does not exist<br>';
    }
    
} catch (Exception $e) {
    echo 'Database error: ' . $e->getMessage() . '<br>';
}
?>
