<?php
try {
    require_once 'lib/Database.php';
    $db = new Database();
    echo "Database connection successful!\n";
    
    $result = $db->select("SELECT COUNT(*) as count FROM tbl_user");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Found " . $row['count'] . " users in database.\n";
    }
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>
