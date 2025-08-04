<?php
// Minimal error handling and session start
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple session start
session_start();

// Include minimal database connection
include_once 'lib/Database.php';
include_once 'classes/PreRegistrationVerification.php';
include_once 'classes/EmailOTP.php';

$db = new Database();
$preVerification = new PreRegistrationVerification();
$emailOTP = new EmailOTP();

$registrationMsg = "";
$showForm = true;
$showOtpForm = false;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])) {
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
    
    $result = $preVerification->initiateEmailVerification($registrationData);
    $registrationMsg = $result['message'];
    
    if ($result['success']) {
        $showForm = false;
        $showOtpForm = true;
    }
}

// Handle OTP verification
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_account'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp_code'];
    
    $otpVerified = $emailOTP->verifyOTP($email, $otp, 'email_verification');
    
    if($otpVerified) {
        $query = "SELECT * FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
        $result = $db->select($query);
        
        if($result && mysqli_num_rows($result) > 0) {
            $pendingData = mysqli_fetch_assoc($result);
            $token = $pendingData['verification_token'];
            
            $accountResult = $preVerification->verifyAndCreateAccount($email, $token, $otp);
            $registrationMsg = $accountResult['message'];
            
            if($accountResult['success']) {
                echo "<script>
                    alert('Account created successfully! Redirecting to login...');
                    setTimeout(function() {
                        window.location.href = 'signin.php';
                    }, 2000);
                </script>";
                $showForm = false;
                $showOtpForm = false;
            }
        }
    } else {
        $registrationMsg = "<div class='error_msg'>❌ Invalid OTP. Please try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 Nepal House Rental - Create Account</title>
    <link rel="stylesheet" href="mystyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Inline critical styles in case CSS file fails to load */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .signup-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 2.5em;
        }
        .logo-section p {
            color: #7f8c8d;
            margin: 10px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }
        .btn-primary {
            background: #e74c3c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background: #c0392b;
        }
        .error_msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }
        .success_msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
        .notice-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .notice-box h3 {
            margin-top: 0;
            color: #856404;
        }
        .otp-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
        }
        .signin-link {
            text-align: center;
            margin-top: 30px;
        }
        .signin-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .signin-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="logo-section">
            <h1>🏠 Nepal House Rental</h1>
            <p>खुसी भएको घर खोज्नुहोस् | Find Your Happy Home</p>
        </div>

        <?php if($registrationMsg) { echo $registrationMsg; } ?>

        <?php if($showForm) { ?>
        <!-- Registration Form -->
        <div class="notice-box">
            <h3>🔐 Secure Registration Process</h3>
            <p><strong>Step 1:</strong> Fill out the registration form below</p>
            <p><strong>Step 2:</strong> We'll send a verification email to your address</p>
            <p><strong>Step 3:</strong> Enter the OTP code to create your account</p>
            <div style="margin-top: 15px; padding: 10px; background: #ffeaa7; border-radius: 5px;">
                <strong>⚠️ Important:</strong> Your account will NOT be created until you verify your email address. 
                Please use a real email address that you can access.
            </div>
        </div>

        <form method="POST" id="registrationForm">
            <div class="form-group">
                <label for="fname"><i class="fas fa-user"></i> First Name:</label>
                <input type="text" 
                       name="fname" 
                       id="fname"
                       placeholder="Enter your first name" 
                       pattern="[a-zA-Z\s]{2,30}" 
                       required>
            </div>

            <div class="form-group">
                <label for="lname"><i class="fas fa-user"></i> Last Name:</label>
                <input type="text" 
                       name="lname" 
                       id="lname"
                       placeholder="Enter your last name" 
                       pattern="[a-zA-Z\s]{2,30}" 
                       required>
            </div>

            <div class="form-group">
                <label for="username"><i class="fas fa-id-card"></i> Username:</label>
                <input type="text" 
                       name="username" 
                       id="username"
                       placeholder="Choose a username" 
                       pattern="[a-zA-Z0-9_]{3,20}" 
                       required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address:</label>
                <input type="email" 
                       name="email" 
                       id="email"
                       placeholder="Enter your real email address" 
                       required>
                <small style="color: #7f8c8d;">⚠️ Please use a real email address. Temporary or fake emails are not allowed.</small>
            </div>

            <div class="form-group">
                <label for="cellno"><i class="fas fa-phone"></i> Mobile Number:</label>
                <input type="tel" 
                       name="cellno" 
                       id="cellno"
                       placeholder="98xxxxxxxx (Nepal format)" 
                       pattern="(98|97)[0-9]{8}" 
                       required>
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt"></i> Address:</label>
                <input type="text" 
                       name="address" 
                       id="address"
                       placeholder="Enter your address" 
                       required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" 
                       name="password" 
                       id="password"
                       placeholder="Enter a strong password" 
                       pattern=".{6,}" 
                       required>
            </div>

            <div class="form-group">
                <label for="level"><i class="fas fa-users"></i> Account Type:</label>
                <select name="level" id="level" style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                    <option value="1">🏠 Property Owner</option>
                    <option value="2">🔍 Property Seeker</option>
                </select>
            </div>

            <button type="submit" name="register" class="btn-primary">
                <i class="fas fa-user-plus"></i> Create Account with Email Verification
            </button>
        </form>

        <?php } elseif($showOtpForm) { ?>
        <!-- OTP Verification Form -->
        <div class="otp-section">
            <h2>📧 Check Your Email</h2>
            <p>We've sent a verification code to your email address.</p>
            <p><strong>Enter the 6-digit code to complete your registration:</strong></p>
            
            <form method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                
                <div class="form-group">
                    <input type="text" 
                           name="otp_code" 
                           class="otp-input"
                           placeholder="000000" 
                           pattern="[0-9]{6}" 
                           maxlength="6"
                           required>
                </div>
                
                <button type="submit" name="verify_account" class="btn-primary">
                    <i class="fas fa-check-circle"></i> Verify and Create Account
                </button>
            </form>
            
            <p style="margin-top: 20px;">
                <small>Didn't receive the code? Check your spam folder or wait 2 minutes and try registering again.</small>
            </p>
        </div>
        <?php } ?>

        <div class="signin-link">
            <p>Already have an account? <a href="signin.php"><i class="fas fa-sign-in-alt"></i> Sign In Here</a></p>
        </div>
    </div>

    <script>
        // Basic form validation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Signup page loaded successfully');
            
            const form = document.getElementById('registrationForm');
            if(form) {
                form.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('cellno').value;
                    
                    // Basic email validation
                    if(!email.includes('@') || !email.includes('.')) {
                        alert('❌ Please enter a valid email address');
                        e.preventDefault();
                        return false;
                    }
                    
                    // Basic phone validation
                    if(!phone.match(/^(98|97)[0-9]{8}$/)) {
                        alert('❌ Phone number must be 10 digits starting with 98 or 97');
                        e.preventDefault();
                        return false;
                    }
                    
                    // Show loading message
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending verification email...';
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
</body>
</html>
