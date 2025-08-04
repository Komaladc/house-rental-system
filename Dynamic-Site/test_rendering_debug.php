<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Signup Rendering Test - Nepal House Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .test-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 5px solid #3498db;
        }
        .success { border-left-color: #27ae60; background: #d5f4e6; }
        .error { border-left-color: #e74c3c; background: #f8d7da; }
        .warning { border-left-color: #f39c12; background: #fff3cd; }
        .test-link {
            display: inline-block;
            padding: 15px 25px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
        }
        .test-link.primary { background: #e74c3c; }
        .test-link.primary:hover { background: #c0392b; }
        .test-link.success { background: #27ae60; }
        .test-link.success:hover { background: #229954; }
        .test-link.info { background: #3498db; }
        .test-link.info:hover { background: #2980b9; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1, h2, h3 { color: #2c3e50; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #2c3e50; margin: 0; font-size: 2.5em; }
        .logo p { color: #7f8c8d; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="logo">
            <h1>🏠 Nepal House Rental</h1>
            <p>Signup Page Rendering Diagnostic Center</p>
        </div>

        <h2>🔍 Rendering Issue Analysis</h2>

        <!-- PHP Include Test -->
        <div class="test-section">
            <h3>🧪 Testing Original Signup Page Rendering</h3>
            <?php
            echo "<div class='warning'>Attempting to render signup_with_verification.php...</div>";
            
            ob_start();
            $startTime = microtime(true);
            
            try {
                // Capture any output
                include 'signup_with_verification.php';
                $content = ob_get_contents();
                $endTime = microtime(true);
                
                $loadTime = round(($endTime - $startTime) * 1000, 2);
                
                if(strlen($content) > 500) {
                    echo "<div class='success'>✅ <strong>Page renders successfully!</strong></div>";
                    echo "<div>📊 Render time: {$loadTime}ms | Content length: " . strlen($content) . " characters</div>";
                    
                    // Check for specific elements
                    if(strpos($content, 'form') !== false) {
                        echo "<div class='success'>✅ Form elements detected</div>";
                    }
                    if(strpos($content, 'Nepal') !== false) {
                        echo "<div class='success'>✅ Nepal branding detected</div>";
                    }
                    if(strpos($content, 'mystyle.css') !== false) {
                        echo "<div class='success'>✅ CSS stylesheet linked</div>";
                    }
                    
                } else {
                    echo "<div class='error'>❌ <strong>Page renders but produces minimal content (" . strlen($content) . " chars)</strong></div>";
                    
                    if($content) {
                        echo "<div><strong>Rendered content preview:</strong></div>";
                        echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
                    } else {
                        echo "<div class='error'>❌ <strong>No content rendered at all!</strong></div>";
                    }
                }
                
            } catch (ParseError $e) {
                echo "<div class='error'>❌ <strong>PHP Parse Error:</strong> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "</div>";
            } catch (Error $e) {
                echo "<div class='error'>❌ <strong>PHP Fatal Error:</strong> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ <strong>PHP Exception:</strong> " . $e->getMessage() . "</div>";
            }
            
            ob_end_clean();
            ?>
        </div>

        <!-- CSS Loading Test -->
        <div class="test-section">
            <h3>🎨 CSS and Asset Loading Test</h3>
            <?php
            $assets = [
                'mystyle.css' => 'Main stylesheet',
                'images/signup_bg.jpg' => 'Signup background image',
                'images/logo.jpg' => 'Logo image',
                'js/form-validation.js' => 'Form validation JavaScript',
                'css/fontawesome/css/all.min.css' => 'FontAwesome icons'
            ];

            foreach($assets as $asset => $description) {
                if(file_exists($asset)) {
                    $size = filesize($asset);
                    echo "<div class='success'>✅ <strong>$asset</strong> - $description (" . round($size/1024, 1) . " KB)</div>";
                } else {
                    echo "<div class='warning'>⚠️ <strong>$asset</strong> - $description (MISSING - might affect styling)</div>";
                }
            }
            ?>
        </div>

        <!-- Header Include Test -->
        <div class="test-section">
            <h3>📄 Header Include Analysis</h3>
            <?php
            if(file_exists('inc/header.php')) {
                echo "<div class='success'>✅ header.php exists</div>";
                
                // Test if header can be included
                ob_start();
                try {
                    include 'inc/header.php';
                    $headerContent = ob_get_contents();
                    
                    if(strlen($headerContent) > 100) {
                        echo "<div class='success'>✅ Header renders content (" . strlen($headerContent) . " chars)</div>";
                        
                        // Check for HTML structure
                        if(strpos($headerContent, '<html') !== false) {
                            echo "<div class='success'>✅ HTML structure detected</div>";
                        }
                        if(strpos($headerContent, '<head') !== false) {
                            echo "<div class='success'>✅ Head section detected</div>";
                        }
                        if(strpos($headerContent, '<body') !== false) {
                            echo "<div class='success'>✅ Body section detected</div>";
                        }
                        if(strpos($headerContent, 'mystyle.css') !== false) {
                            echo "<div class='success'>✅ CSS link detected in header</div>";
                        }
                    } else {
                        echo "<div class='error'>❌ Header produces minimal content</div>";
                    }
                    
                } catch (Exception $e) {
                    echo "<div class='error'>❌ Header include error: " . $e->getMessage() . "</div>";
                }
                ob_end_clean();
                
            } else {
                echo "<div class='error'>❌ header.php not found</div>";
            }
            ?>
        </div>

        <!-- Working Alternatives -->
        <div class="test-section">
            <h3>🔧 Working Alternative Pages</h3>
            <p><strong>Test these pages to see which ones render properly:</strong></p>
            
            <a href="signup_rendered_fixed.php" class="test-link success" target="_blank">
                🆕 Test Fixed Rendered Page
            </a>
            
            <a href="signup_fixed.php" class="test-link info" target="_blank">
                🔧 Test Standalone Fixed Page
            </a>
            
            <a href="signup_with_verification.php" class="test-link primary" target="_blank">
                📧 Test Original Page
            </a>
        </div>

        <!-- Troubleshooting Guide -->
        <div class="test-section warning">
            <h3>🔧 Common Rendering Issues & Solutions</h3>
            <h4>If the original signup page is not rendering properly:</h4>
            <ul>
                <li><strong>🎨 CSS not loading:</strong> Check if mystyle.css exists and is accessible</li>
                <li><strong>📄 Header issues:</strong> The inc/header.php might have PHP errors or missing includes</li>
                <li><strong>🗄️ Database errors:</strong> Database connection issues can cause white screens</li>
                <li><strong>📱 JavaScript errors:</strong> Check browser console (F12) for red errors</li>
                <li><strong>🔄 Browser cache:</strong> Press Ctrl+F5 to hard refresh</li>
                <li><strong>⚡ PHP errors:</strong> Enable error reporting to see fatal errors</li>
            </ul>
        </div>

        <!-- Recommended Solution -->
        <div class="test-section success">
            <h3>✅ Recommended Solution</h3>
            <p><strong>Use the Fixed Rendered Page for guaranteed working signup:</strong></p>
            <ul>
                <li>🎯 <strong>signup_rendered_fixed.php</strong> - Complete standalone page with inline CSS</li>
                <li>🛡️ <strong>All functionality included:</strong> Email verification, OTP system, Nepal branding</li>
                <li>📱 <strong>Mobile responsive</strong> and professionally styled</li>
                <li>🔒 <strong>Secure validation</strong> and real email verification</li>
                <li>⚡ <strong>No external dependencies</strong> - guaranteed to render</li>
            </ul>
            
            <div style="margin-top: 15px; padding: 15px; background: #e8f5e8; border-radius: 5px;">
                <strong>📍 Direct URL:</strong><br>
                <code>http://localhost/house-rental-system/Dynamic-Site/signup_rendered_fixed.php</code>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 Rendering test page loaded successfully');
            
            // Add visual confirmation
            const container = document.querySelector('.test-container');
            const indicator = document.createElement('div');
            indicator.innerHTML = '✅ Diagnostic page fully loaded and functional!';
            indicator.style.background = '#d4edda';
            indicator.style.color = '#155724';
            indicator.style.padding = '10px';
            indicator.style.borderRadius = '5px';
            indicator.style.margin = '10px 0';
            indicator.style.textAlign = 'center';
            indicator.style.fontWeight = 'bold';
            indicator.style.border = '1px solid #c3e6cb';
            container.appendChild(indicator);
        });
    </script>
</body>
</html>
