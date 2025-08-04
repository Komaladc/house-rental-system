<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Form Submission Debug Test</h2>";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Form was submitted successfully!</h3>";
    echo "<strong>POST Data Received:</strong><br>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    print_r($_POST);
    echo "</pre>";
    echo "</div>";
    
    if (isset($_POST['register'])) {
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>📧 Registration form submitted</h4>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($_POST['email'] ?? 'Not provided') . "</p>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($_POST['fname'] ?? '') . " " . htmlspecialchars($_POST['lname'] ?? '') . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($_POST['cellno'] ?? 'Not provided') . "</p>";
        echo "</div>";
        
        // Test email verification
        try {
            include_once 'lib/Database.php';
            include_once 'classes/PreRegistrationVerification.php';
            
            $preVerification = new PreRegistrationVerification();
            
            $registrationData = [
                'fname' => $_POST['fname'],
                'lname' => $_POST['lname'],
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'cellno' => $_POST['cellno'],
                'address' => $_POST['address'],
                'password' => $_POST['password'],
                'level' => $_POST['level']
            ];
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🔄 Testing email verification...</h4>";
            
            $result = $preVerification->initiateEmailVerification($registrationData);
            
            echo "<p><strong>Result:</strong></p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
            print_r($result);
            echo "</pre>";
            echo "</div>";
            
            if ($result['success']) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Email verification initiated successfully!</h4>";
                echo "<p>Now you should see the OTP form.</p>";
                echo "</div>";
                
                // Show OTP form
                echo "<div style='background: #e8f4fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h3>📱 Enter Verification Code</h3>";
                echo "<p>We've sent a verification code to: <strong>" . htmlspecialchars($_POST['email']) . "</strong></p>";
                echo "<form method='POST'>";
                echo "<input type='hidden' name='verify_account' value='1'>";
                echo "<input type='hidden' name='email' value='" . htmlspecialchars($_POST['email']) . "'>";
                echo "<p><label><strong>Verification Code:</strong></label></p>";
                echo "<input type='text' name='otp_code' placeholder='000000' maxlength='6' style='font-size: 24px; text-align: center; padding: 10px; width: 200px;' required>";
                echo "<br><br>";
                echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>Verify & Create Account</button>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Email verification failed!</h4>";
                echo "<p><strong>Error:</strong> " . htmlspecialchars($result['message']) . "</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ Error during email verification:</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
    
    if (isset($_POST['verify_account'])) {
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🔐 OTP verification submitted</h4>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($_POST['email'] ?? 'Not provided') . "</p>";
        echo "<p><strong>OTP:</strong> " . htmlspecialchars($_POST['otp_code'] ?? 'Not provided') . "</p>";
        echo "</div>";
        
        // Test OTP verification
        try {
            include_once 'lib/Database.php';
            include_once 'classes/EmailOTP.php';
            include_once 'classes/PreRegistrationVerification.php';
            
            $emailOTP = new EmailOTP();
            $preVerification = new PreRegistrationVerification();
            $db = new Database();
            
            $email = $_POST['email'];
            $otp = $_POST['otp_code'];
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🔄 Testing OTP verification...</h4>";
            
            $otpVerified = $emailOTP->verifyOTP($email, $otp, 'email_verification');
            
            if ($otpVerified) {
                echo "<p>✅ OTP verified successfully!</p>";
                
                // Get pending verification data
                $query = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
                $result = $db->select($query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $pendingData = mysqli_fetch_assoc($result);
                    $token = $pendingData['verification_token'];
                    
                    $accountResult = $preVerification->verifyAndCreateAccount($email, $token, $otp);
                    
                    echo "<p><strong>Account creation result:</strong></p>";
                    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
                    print_r($accountResult);
                    echo "</pre>";
                    
                    if ($accountResult['success']) {
                        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<h4>🎉 Account created successfully!</h4>";
                        echo "<p>You can now sign in with your credentials.</p>";
                        echo "<a href='signin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Sign In</a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>❌ No pending verification found.</p>";
                }
            } else {
                echo "<p>❌ Invalid OTP code.</p>";
            }
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ Error during OTP verification:</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>📝 Test Registration Form</h3>";
    echo "<p>Fill out this form to test the registration process:</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 Nepal House Rental - Registration Debug Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }
        button {
            background: #e74c3c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #c0392b;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2c3e50;
            margin: 0;
        }
        .logo p {
            color: #7f8c8d;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>🏠 Nepal House Rental</h1>
        <p>Registration Debug Test Page</p>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] != 'POST' || (!isset($_POST['register']) && !isset($_POST['verify_account']))) { ?>
    <div class="form-container">
        <h2>📧 Create Account with Email Verification</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="fname">👤 First Name:</label>
                <input type="text" name="fname" id="fname" required placeholder="Enter your first name">
            </div>
            
            <div class="form-group">
                <label for="lname">👤 Last Name:</label>
                <input type="text" name="lname" id="lname" required placeholder="Enter your last name">
            </div>
            
            <div class="form-group">
                <label for="username">🆔 Username:</label>
                <input type="text" name="username" id="username" required placeholder="Choose a username">
            </div>
            
            <div class="form-group">
                <label for="email">📧 Email Address:</label>
                <input type="email" name="email" id="email" required placeholder="Enter your real email address">
            </div>
            
            <div class="form-group">
                <label for="cellno">📱 Mobile Number:</label>
                <input type="tel" name="cellno" id="cellno" required placeholder="98xxxxxxxx (Nepal format)" pattern="(98|97)[0-9]{8}">
            </div>
            
            <div class="form-group">
                <label for="address">🏠 Address:</label>
                <input type="text" name="address" id="address" required placeholder="Enter your address">
            </div>
            
            <div class="form-group">
                <label for="password">🔐 Password:</label>
                <input type="password" name="password" id="password" required placeholder="Enter password (min 8 characters)" minlength="8">
            </div>
            
            <div class="form-group">
                <label for="cpassword">🔐 Confirm Password:</label>
                <input type="password" name="cpassword" id="cpassword" required placeholder="Confirm your password">
            </div>
            
            <div class="form-group">
                <label for="level">👨‍💼 Account Type:</label>
                <select name="level" id="level" required>
                    <option value="">Choose account type</option>
                    <option value="1">🔍 Property Seeker</option>
                    <option value="2">🏠 Property Owner</option>
                    <option value="3">🏢 Real Estate Agent</option>
                </select>
            </div>
            
            <button type="submit" name="register">📧 Send Verification Email</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="signin.php">Already have an account? Sign In</a>
        </p>
    </div>
    <?php } ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const cpassword = document.getElementById('cpassword').value;
                    
                    if (password !== cpassword) {
                        e.preventDefault();
                        alert('❌ Passwords do not match!');
                        return false;
                    }
                    
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('cellno').value;
                    
                    if (!email.includes('@')) {
                        e.preventDefault();
                        alert('❌ Please enter a valid email address');
                        return false;
                    }
                    
                    if (!phone.match(/^(98|97)[0-9]{8}$/)) {
                        e.preventDefault();
                        alert('❌ Phone number must be 10 digits starting with 98 or 97');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>
