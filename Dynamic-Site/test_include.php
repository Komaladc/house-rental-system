<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔧 PHP Include Test for Signup Page</h1>";

echo "<h3>Testing signup_with_verification.php Loading</h3>";

try {
    // Capture any output and errors
    ob_start();
    $errorsBefore = error_get_last();
    
    // Try to include the signup page
    include 'signup_with_verification.php';
    
    $content = ob_get_contents();
    $errorsAfter = error_get_last();
    ob_end_clean();
    
    echo "<div style='background: #d5f4e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Page included successfully!</h4>";
    echo "<p><strong>Content length:</strong> " . strlen($content) . " characters</p>";
    
    if($errorsAfter && $errorsAfter !== $errorsBefore) {
        echo "<h4>⚠️ PHP Errors Found:</h4>";
        echo "<pre style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        print_r($errorsAfter);
        echo "</pre>";
    } else {
        echo "<p><strong>✅ No PHP errors detected</strong></p>";
    }
    
    echo "</div>";
    
    // Show first 500 characters of content
    if(strlen($content) > 0) {
        echo "<h4>📄 Content Preview (first 500 chars):</h4>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($content, 0, 500));
        if(strlen($content) > 500) {
            echo "\n\n... (content continues for " . (strlen($content) - 500) . " more characters)";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ No content generated</h4>";
        echo "<p>The page included successfully but generated no output. This might indicate:</p>";
        echo "<ul>";
        echo "<li>Silent PHP fatal error</li>";
        echo "<li>Redirect happening immediately</li>";
        echo "<li>Output buffering issue</li>";
        echo "<li>Missing include files</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (ParseError $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ PHP Parse Error</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ PHP Fatal Error</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ PHP Exception</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<h3>🌐 Browser Testing</h3>";
echo "<p>If this test passes, try opening the signup page directly in your browser:</p>";
echo "<p><strong>URL:</strong> <a href='signup_with_verification.php' target='_blank' style='color: #e74c3c; font-weight: bold;'>http://localhost/house-rental-system/Dynamic-Site/signup_with_verification.php</a></p>";

echo "<h3>🔧 Quick Fixes</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>If signup page is still not visible:</h4>";
echo "<ol>";
echo "<li><strong>Clear browser cache:</strong> Press Ctrl+F5 to hard refresh</li>";
echo "<li><strong>Check browser console:</strong> Press F12 → Console tab</li>";
echo "<li><strong>Try incognito/private mode:</strong> Rule out cache issues</li>";
echo "<li><strong>Test different browser:</strong> Chrome, Firefox, Edge</li>";
echo "<li><strong>Check file permissions:</strong> Make sure files are readable</li>";
echo "</ol>";
echo "</div>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
}
h1, h3, h4 {
    color: #2c3e50;
}
</style>
