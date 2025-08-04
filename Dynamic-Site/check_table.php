<?php
include "config/config.php";
include "lib/Database.php";

try {
    $db = new Database();
    echo "Database connection: OK\n";
    
    $result = $db->select("SHOW COLUMNS FROM tbl_ad");
    if($result && $result->num_rows > 0) {
        echo "Table columns:\n";
        while($row = $result->fetch_assoc()) {
            if(strpos($row['Field'], 'balcon') !== false || strpos($row['Field'], 'belcon') !== false) {
                echo "Found balcony column: " . $row['Field'] . "\n";
            }
        }
    }
    
    // Test the specific column
    $test = $db->select("SELECT totalBelcony FROM tbl_ad LIMIT 1");
    if($test) {
        echo "Column 'totalBelcony' exists in database\n";
    } else {
        echo "Column 'totalBelcony' does not exist\n";
    }
    
    $test2 = $db->select("SELECT totalBalcony FROM tbl_ad LIMIT 1");
    if($test2) {
        echo "Column 'totalBalcony' exists in database\n";
    } else {
        echo "Column 'totalBalcony' does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
