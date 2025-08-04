<?php
// Check PHP error log for recent message-related errors
echo "<h2>PHP Error Log Analysis</h2>";

// Get error log file path
$errorLogPath = ini_get('error_log');
if(!$errorLogPath || !file_exists($errorLogPath)) {
    // Try common XAMPP locations
    $possiblePaths = [
        'C:\\xampp\\php\\logs\\php_error_log',
        'C:\\xampp\\apache\\logs\\error.log',
        'C:\\xampp\\logs\\php_error_log'
    ];
    
    foreach($possiblePaths as $path) {
        if(file_exists($path)) {
            $errorLogPath = $path;
            break;
        }
    }
}

echo "<p><strong>Error log path:</strong> " . ($errorLogPath ?: 'Not found') . "</p>";

if($errorLogPath && file_exists($errorLogPath)) {
    echo "<h3>Recent Error Log Entries (Last 50 lines):</h3>";
    $lines = file($errorLogPath);
    $recentLines = array_slice($lines, -50);
    
    echo "<div style='background:#f5f5f5; padding:10px; font-family:monospace; white-space:pre-wrap; border:1px solid #ccc; max-height:400px; overflow-y:scroll;'>";
    foreach($recentLines as $line) {
        // Highlight lines containing our debug messages
        if(strpos($line, 'Notification') !== false || strpos($line, 'Message') !== false) {
            echo "<span style='background:yellow;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</div>";
} else {
    echo "<p style='color:red;'>Error log file not found</p>";
}

// Also check if we can access the database directly
echo "<h3>Database Check:</h3>";
include '../lib/Database.php';
$db = new Database();

// Check connection
if($db->link) {
    echo "<p style='color:green;'>✓ Database connection OK</p>";
    
    // Check notification table
    $result = $db->select("SELECT COUNT(*) as count FROM tbl_notification");
    if($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total notifications in database: " . $row['count'] . "</p>";
    }
    
    // Show the very latest record
    $latest = $db->select("SELECT * FROM tbl_notification ORDER BY notfId DESC LIMIT 1");
    if($latest) {
        $row = $latest->fetch_assoc();
        echo "<h4>Latest notification record:</h4>";
        echo "<table border='1'>";
        foreach($row as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No notifications found in database</p>";
    }
    
} else {
    echo "<p style='color:red;'>✗ Database connection failed</p>";
}

// Check session status
echo "<h3>Session Status:</h3>";
include '../lib/Session.php';
Session::init();
echo "<p>User login status: " . (Session::get("userlogin") ? "Logged in" : "Not logged in") . "</p>";
echo "<p>User ID: " . Session::get("userId") . "</p>";
echo "<p>Property ID: " . Session::get("adId") . "</p>";
echo "<p>Owner ID: " . Session::get("ownerId") . "</p>";
?>
