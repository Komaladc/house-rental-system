<?php
// Database Setup for Admin System
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "lib/Database.php";
$db = new Database();

echo "<h2>🗄️ Setting Up Admin Database Tables</h2>";

// Read and execute SQL commands
$sqlFile = file_get_contents('admin_database_setup.sql');
$commands = explode(';', $sqlFile);

$successCount = 0;
$errorCount = 0;

foreach ($commands as $command) {
    $command = trim($command);
    if (!empty($command)) {
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 3px; font-family: monospace; font-size: 12px;'>";
        echo htmlspecialchars(substr($command, 0, 100)) . (strlen($command) > 100 ? '...' : '');
        echo "</div>";
        
        if (mysqli_query($db->link, $command)) {
            echo "<div style='background: #d4edda; padding: 8px; margin: 5px 0; border-radius: 3px;'>✅ Success</div>";
            $successCount++;
        } else {
            $error = mysqli_error($db->link);
            if (strpos($error, 'already exists') !== false || strpos($error, 'Duplicate column') !== false) {
                echo "<div style='background: #fff3cd; padding: 8px; margin: 5px 0; border-radius: 3px;'>⚠️ Already exists (skipped)</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 8px; margin: 5px 0; border-radius: 3px;'>❌ Error: $error</div>";
                $errorCount++;
            }
        }
        echo "<hr style='margin: 10px 0;'>";
    }
}

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Setup Summary</h3>";
echo "<strong>Successful:</strong> $successCount<br>";
echo "<strong>Errors:</strong> $errorCount<br>";
if ($errorCount == 0) {
    echo "<p style='color: #28a745;'>✅ Database setup completed successfully!</p>";
} else {
    echo "<p style='color: #dc3545;'>⚠️ Some errors occurred. Please check above for details.</p>";
}
echo "</div>";

// Verify table creation
echo "<h3>🔍 Verifying Created Tables</h3>";
$tables = ['tbl_user_verification', 'tbl_admin_logs', 'tbl_website_stats', 'tbl_admin_sessions'];

foreach ($tables as $table) {
    $checkTable = "SHOW TABLES LIKE '$table'";
    $result = $db->select($checkTable);
    if ($result && $result->num_rows > 0) {
        echo "<div style='background: #d4edda; padding: 8px; margin: 5px 0; border-radius: 3px;'>✅ $table created successfully</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 8px; margin: 5px 0; border-radius: 3px;'>❌ $table not found</div>";
    }
}

// Check if user table columns were added
echo "<h3>🔍 Verifying User Table Updates</h3>";
$checkColumns = "DESCRIBE tbl_user";
$columnsResult = $db->select($checkColumns);
$foundColumns = [];

if ($columnsResult) {
    while ($column = $columnsResult->fetch_assoc()) {
        $foundColumns[] = $column['Field'];
    }
}

$requiredColumns = ['verification_status', 'requires_verification', 'submitted_documents'];
foreach ($requiredColumns as $column) {
    if (in_array($column, $foundColumns)) {
        echo "<div style='background: #d4edda; padding: 8px; margin: 5px 0; border-radius: 3px;'>✅ Column '$column' added to tbl_user</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 8px; margin: 5px 0; border-radius: 3px;'>❌ Column '$column' not found in tbl_user</div>";
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h2, h3 { color: #333; }
    hr { border: none; border-top: 1px solid #ddd; }
</style>
