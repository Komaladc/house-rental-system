<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Signup Page Visibility Test</h1>";

echo "<h3>1. Testing Page Access</h3>";
if (file_exists('signup_with_verification.php')) {
    echo "✅ signup_with_verification.php file exists<br>";
} else {
    echo "❌ signup_with_verification.php file NOT found<br>";
}

echo "<h3>2. Testing Dependencies</h3>";

// Test each dependency
$dependencies = [
    'inc/header.php',
    'lib/Session.php',
    'lib/Database.php',
    'classes/PreRegistrationVerification.php',
    'classes/EmailOTP.php',
    'mystyle.css'
];

foreach ($dependencies as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file NOT found<br>";
    }
}

echo "<h3>3. Testing PHP Include</h3>";
try {
    ob_start();
    include 'signup_with_verification.php';
    $content = ob_get_contents();
    ob_end_clean();
    
    if (strlen($content) > 100) {
        echo "✅ Page loads and generates content (" . strlen($content) . " characters)<br>";
        echo "✅ Page should be visible<br>";
    } else {
        echo "❌ Page loads but generates very little content (" . strlen($content) . " characters)<br>";
        echo "Content preview: " . htmlspecialchars(substr($content, 0, 200)) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading page: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Quick Diagnostics</h3>";
echo "<p><strong>Possible issues if page is not visible:</strong></p>";
echo "<ul>";
echo "<li>🌐 <strong>White screen:</strong> PHP fatal error (check error logs)</li>";
echo "<li>📄 <strong>Blank page:</strong> CSS loading issue or missing header/footer</li>";
echo "<li>🔄 <strong>Page not loading:</strong> Apache not running or wrong URL</li>";
echo "<li>🗂️ <strong>404 error:</strong> File path or permissions issue</li>";
echo "</ul>";

echo "<h3>5. Direct Links for Testing</h3>";
echo "<p>Try these direct links:</p>";
echo "<a href='signup_with_verification.php' target='_blank' style='background:#e74c3c; color:white; padding:10px; text-decoration:none; border-radius:5px; margin:5px; display:inline-block;'>📧 Full Signup with Verification</a><br>";
echo "<a href='simple_signup_test.php' target='_blank' style='background:#27ae60; color:white; padding:10px; text-decoration:none; border-radius:5px; margin:5px; display:inline-block;'>🧪 Simple Signup Test</a><br>";
echo "<a href='test_signup_debug.php' target='_blank' style='background:#3498db; color:white; padding:10px; text-decoration:none; border-radius:5px; margin:5px; display:inline-block;'>🔧 Debug Test</a><br>";

echo "<h3>6. Browser Console Check</h3>";
echo "<p>📱 <strong>Check browser developer tools:</strong></p>";
echo "<ol>";
echo "<li>Press F12 to open developer tools</li>";
echo "<li>Go to Console tab</li>";
echo "<li>Look for JavaScript errors (red text)</li>";
echo "<li>Go to Network tab and refresh page</li>";
echo "<li>Look for failed requests (red status codes)</li>";
echo "</ol>";

?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧪 Signup page visibility test loaded successfully');
    
    // Test if basic JavaScript is working
    const testDiv = document.createElement('div');
    testDiv.innerHTML = '✅ JavaScript is working';
    testDiv.style.background = '#d5f4e6';
    testDiv.style.padding = '10px';
    testDiv.style.borderRadius = '5px';
    testDiv.style.margin = '10px 0';
    document.body.appendChild(testDiv);
});
</script>
