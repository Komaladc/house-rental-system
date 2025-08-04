<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Signup Form Debug - Nepal House Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .debug-container {
            max-width: 900px;
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
            padding: 12px 25px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
        }
        .test-link.primary { background: #e74c3c; }
        .test-link.primary:hover { background: #c0392b; }
        .test-link.secondary { background: #27ae60; }
        .test-link.secondary:hover { background: #229954; }
        .test-link.info { background: #3498db; }
        .test-link.info:hover { background: #2980b9; }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        h1, h2, h3 { color: #2c3e50; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #2c3e50; margin: 0; font-size: 2.5em; }
        .logo p { color: #7f8c8d; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="logo">
            <h1>🏠 Nepal House Rental</h1>
            <p>Signup Form Debug & Test Center</p>
        </div>

        <h2>🔍 Comprehensive Signup Form Diagnostics</h2>

        <!-- File Existence Check -->
        <div class="test-section">
            <h3>📂 File Existence Check</h3>
            <?php
            $files = [
                'signup_with_verification.php' => 'Original signup page',
                'signup_fixed.php' => 'Fixed standalone signup page',
                'inc/header.php' => 'Header include file',
                'lib/Database.php' => 'Database connection',
                'classes/PreRegistrationVerification.php' => 'Registration verification class',
                'classes/EmailOTP.php' => 'Email OTP class',
                'mystyle.css' => 'Main stylesheet'
            ];

            foreach($files as $file => $description) {
                if(file_exists($file)) {
                    echo "<div class='success'>✅ <strong>$file</strong> - $description (EXISTS)</div>";
                } else {
                    echo "<div class='error'>❌ <strong>$file</strong> - $description (MISSING)</div>";
                }
            }
            ?>
        </div>

        <!-- PHP Include Test -->
        <div class="test-section">
            <h3>🧪 PHP Include Test</h3>
            <?php
            echo "<div class='warning'>Testing signup_with_verification.php loading...</div>";
            
            try {
                ob_start();
                $startTime = microtime(true);
                
                // Try to include the original file
                include 'signup_with_verification.php';
                
                $endTime = microtime(true);
                $content = ob_get_contents();
                ob_end_clean();
                
                $loadTime = round(($endTime - $startTime) * 1000, 2);
                
                if(strlen($content) > 100) {
                    echo "<div class='success'>✅ <strong>Original signup page loads successfully!</strong></div>";
                    echo "<div>📊 Load time: {$loadTime}ms | Content length: " . strlen($content) . " characters</div>";
                } else {
                    echo "<div class='error'>❌ <strong>Page loads but generates minimal content (" . strlen($content) . " chars)</strong></div>";
                    if($content) {
                        echo "<div><strong>Content preview:</strong></div>";
                        echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
                    }
                }
                
            } catch (ParseError $e) {
                echo "<div class='error'>❌ <strong>PHP Parse Error:</strong> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "</div>";
            } catch (Error $e) {
                echo "<div class='error'>❌ <strong>PHP Fatal Error:</strong> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ <strong>PHP Exception:</strong> " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <!-- Database Connection Test -->
        <div class="test-section">
            <h3>🗄️ Database Connection Test</h3>
            <?php
            try {
                if(file_exists('lib/Database.php')) {
                    include_once 'lib/Database.php';
                    $db = new Database();
                    
                    if($db && $db->link) {
                        echo "<div class='success'>✅ <strong>Database connection successful!</strong></div>";
                        
                        // Test tables
                        $tables = ['tbl_user', 'tbl_otp', 'tbl_pending_verification'];
                        foreach($tables as $table) {
                            $query = "SHOW TABLES LIKE '$table'";
                            $result = mysqli_query($db->link, $query);
                            
                            if($result && mysqli_num_rows($result) > 0) {
                                echo "<div class='success'>✅ Table <strong>$table</strong> exists</div>";
                            } else {
                                echo "<div class='error'>❌ Table <strong>$table</strong> missing</div>";
                            }
                        }
                    } else {
                        echo "<div class='error'>❌ <strong>Database connection failed!</strong></div>";
                    }
                } else {
                    echo "<div class='error'>❌ <strong>Database.php file not found!</strong></div>";
                }
            } catch(Exception $e) {
                echo "<div class='error'>❌ <strong>Database Error:</strong> " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <!-- Browser Test Links -->
        <div class="test-section">
            <h3>🌐 Browser Test Links</h3>
            <p><strong>Click these links to test the signup pages directly in your browser:</strong></p>
            
            <a href="signup_fixed.php" class="test-link primary" target="_blank">
                🆕 Test Fixed Signup Page
            </a>
            
            <a href="signup_with_verification.php" class="test-link secondary" target="_blank">
                📧 Test Original Signup Page
            </a>
            
            <a href="simple_signup_test.php" class="test-link info" target="_blank">
                🧪 Test Simple Signup
            </a>
            
            <div style="margin-top: 15px;">
                <p><strong>📍 Direct URLs to try:</strong></p>
                <code>http://localhost/house-rental-system/Dynamic-Site/signup_fixed.php</code><br>
                <code>http://localhost/house-rental-system/Dynamic-Site/signup_with_verification.php</code>
            </div>
        </div>

        <!-- Troubleshooting Guide -->
        <div class="test-section warning">
            <h3>🔧 Troubleshooting Guide</h3>
            <p><strong>If signup forms are still not visible:</strong></p>
            <ol>
                <li><strong>Clear Browser Cache:</strong> Press Ctrl+Shift+Delete or Ctrl+F5</li>
                <li><strong>Check Browser Console:</strong> Press F12 → Console tab → Look for red errors</li>
                <li><strong>Try Incognito/Private Mode:</strong> Rules out cache/extension issues</li>
                <li><strong>Check XAMPP:</strong> Ensure Apache and MySQL are running (green in XAMPP Control Panel)</li>
                <li><strong>File Permissions:</strong> Ensure files are readable by web server</li>
                <li><strong>PHP Errors:</strong> Check XAMPP error logs in xampp/apache/logs/</li>
            </ol>
        </div>

        <!-- Expected Results -->
        <div class="test-section success">
            <h3>✅ What You Should See in Working Signup Page</h3>
            <ul>
                <li>🏠 <strong>Nepal House Rental</strong> header with logo</li>
                <li>📝 <strong>Registration form</strong> with all input fields</li>
                <li>🛡️ <strong>Real-time validation</strong> messages</li>
                <li>📧 <strong>Email verification notice</strong> and workflow</li>
                <li>💰 <strong>NPR currency</strong> references (if applicable)</li>
                <li>🔗 <strong>Sign in link</strong> at the bottom</li>
                <li>✨ <strong>Professional styling</strong> with gradients and animations</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <p><strong>🚀 Test completed! Use the links above to test the signup forms.</strong></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 Debug page loaded successfully');
            
            // Test if JavaScript is working
            const debugContainer = document.querySelector('.debug-container');
            if(debugContainer) {
                const jsIndicator = document.createElement('div');
                jsIndicator.innerHTML = '✅ JavaScript is working correctly';
                jsIndicator.style.background = '#d5f4e6';
                jsIndicator.style.padding = '10px';
                jsIndicator.style.borderRadius = '5px';
                jsIndicator.style.margin = '10px 0';
                jsIndicator.style.textAlign = 'center';
                jsIndicator.style.fontWeight = 'bold';
                jsIndicator.style.color = '#27ae60';
                debugContainer.appendChild(jsIndicator);
            }
        });
    </script>
</body>
</html>
