<?php
// Simple status checker
echo "<h1>🔧 XAMPP & PHP Status Check</h1>";

echo "<h2>✅ PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h2>📁 File System Check</h2>";
$files_to_check = [
    'signup.php',
    'signin.php', 
    'classes/User.php',
    'classes/PreRegistrationVerification.php',
    'lib/Database.php',
    'config/config.php'
];

foreach($files_to_check as $file) {
    $fullPath = __DIR__ . '/Dynamic-Site/' . $file;
    $exists = file_exists($fullPath);
    echo "<p>$file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

echo "<h2>🔗 Quick Navigation</h2>";
echo "<p><a href='/house-rental-system/' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🏠 Home</a></p>";
echo "<p><a href='/house-rental-system/Dynamic-Site/signup.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>📝 Signup</a></p>";
echo "<p><a href='/house-rental-system/Dynamic-Site/signin.php' style='background:#17a2b8; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🔑 Sign In</a></p>";

echo "<h2>🛠️ Troubleshooting</h2>";
echo "<p>If you see this page, PHP is working correctly.</p>";
echo "<p>If other pages show ERR_FAILED, check:</p>";
echo "<ul>";
echo "<li>XAMPP Apache service is running</li>";
echo "<li>XAMPP MySQL service is running</li>";
echo "<li>No syntax errors in PHP files</li>";
echo "<li>Correct file permissions</li>";
echo "</ul>";

// Database connection test
echo "<h2>🗄️ Database Connection Test</h2>";
try {
    $config_file = __DIR__ . '/Dynamic-Site/config/config.php';
    if(file_exists($config_file)) {
        include $config_file;
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($connection->connect_error) {
            echo "<p style='color:red;'>❌ Database connection failed: " . $connection->connect_error . "</p>";
        } else {
            echo "<p style='color:green;'>✅ Database connection successful!</p>";
            $connection->close();
        }
    } else {
        echo "<p style='color:orange;'>⚠️ Config file not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database test failed: " . $e->getMessage() . "</p>";
}
?>
