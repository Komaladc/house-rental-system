<?php
// Start output buffering to prevent header issues
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include necessary files with error handling
try {
    include_once 'lib/Database.php';
    include_once 'classes/PreRegistrationVerification.php';
    include_once 'classes/EmailOTP.php';
    
    $db = new Database();
    $preVerification = new PreRegistrationVerification();
    $emailOTP = new EmailOTP();
} catch (Exception $e) {
    // Handle include errors gracefully
    echo "<!-- Include error: " . $e->getMessage() . " -->";
}

$registrationMsg = "";
$showForm = true;
$showOtpForm = false;

// Handle OTP verification
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_account'])){
    $email = $_POST['email'];
    $otp = $_POST['otp_code'];
    
    try {
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
            } else {
                $registrationMsg = '<div class="error_msg">❌ No pending verification found. Please register again.</div>';
                $showForm = true;
                $showOtpForm = false;
            }
        } else {
            $registrationMsg = '<div class="error_msg">❌ Invalid OTP code. Please try again.</div>';
            $showOtpForm = true;
        }
    } catch (Exception $e) {
        $registrationMsg = '<div class="error_msg">❌ Verification error: ' . $e->getMessage() . '</div>';
        $showOtpForm = true;
    }
}

// Handle initial registration
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])){
    try {
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
    } catch (Exception $e) {
        $registrationMsg = '<div class="error_msg">❌ Registration error: ' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 Nepal House Rental - Create Account with Email Verification</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="mystyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Inline CSS for guaranteed rendering -->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header-section h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header-section p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content-section {
            padding: 40px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-title h2 {
            color: #2c3e50;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .notice-box {
            background: #e8f5e8;
            border: 1px solid #27ae60;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .notice-box h3 {
            color: #27ae60;
            margin-bottom: 15px;
        }
        
        .notice-box p {
            margin: 8px 0;
            color: #2c3e50;
        }
        
        .security-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #856404;
        }
        
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .form-table caption {
            margin-bottom: 20px;
        }
        
        .form-table caption h1 {
            color: #2c3e50;
            font-size: 1.8em;
        }
        
        .form-table td {
            padding: 12px;
            vertical-align: top;
        }
        
        .form-table td:first-child {
            width: 200px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-table input, .form-table select {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-table input:focus, .form-table select:focus {
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
        
        .btn-primary:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
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
        
        .otp-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
        
        .otp-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .email-display {
            background: white;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            word-break: break-all;
        }
        
        .otp-input {
            font-size: 24px !important;
            text-align: center !important;
            letter-spacing: 8px !important;
            font-family: 'Courier New', monospace !important;
            background: white !important;
        }
        
        .help-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .help-section h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .help-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .help-section li {
            margin: 8px 0;
            color: #856404;
        }
        
        .footer-links {
            text-align: center;
            padding: 30px;
            border-top: 1px solid #eee;
            background: #f8f9fa;
        }
        
        .footer-links a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            margin: 0 15px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .email-notice {
            margin-top: 8px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .form-table td:first-child {
                width: auto;
                display: block;
                padding-bottom: 5px;
            }
            
            .form-table td {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-section">
            <h1>🏠 Nepal House Rental</h1>
            <p>खुसी भएको घर खोज्नुहोस् | Find Your Happy Home</p>
        </div>
        
        <!-- Content -->
        <div class="content-section">
            <div class="page-title">
                <h2>📧 Create Account with Email Verification</h2>
            </div>
            
            <!-- Display Messages -->
            <?php if($registrationMsg) { echo $registrationMsg; } ?>
            
            <?php if($showForm) { ?>
            <!-- Registration Form -->
            <div class="notice-box">
                <h3>🔐 Secure Registration Process</h3>
                <p><strong>Step 1:</strong> Fill out the registration form below</p>
                <p><strong>Step 2:</strong> We'll send a verification email to your address</p>
                <p><strong>Step 3:</strong> Enter the OTP code to create your account</p>
                <div class="security-note">
                    <strong>⚠️ Important:</strong> Your account will NOT be created until you verify your email address. 
                    Please use a real email address that you can access.
                </div>
            </div>
            
            <form method="POST" id="registrationForm">
                <table class="form-table">
                    <caption><h1>🇳🇵 Property Finder Nepal - Registration</h1></caption>
                    
                    <tr>
                        <td><label for="fname"><b><i class="fas fa-user"></i> First Name:</b></label></td>
                        <td>
                            <input type="text" 
                                   name="fname" 
                                   id="fname"
                                   placeholder="Enter your first name" 
                                   pattern="[a-zA-Z\s]{2,30}" 
                                   title="First name should be 2-30 characters and contain only letters"
                                   value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>"
                                   required>
                            <span class="error-message" id="fname-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="lname"><b><i class="fas fa-user"></i> Last Name:</b></label></td>
                        <td>
                            <input type="text" 
                                   name="lname" 
                                   id="lname"
                                   placeholder="Enter your last name" 
                                   pattern="[a-zA-Z\s]{2,30}" 
                                   title="Last name should be 2-30 characters and contain only letters"
                                   value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>"
                                   required>
                            <span class="error-message" id="lname-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="username"><b><i class="fas fa-id-card"></i> Username:</b></label></td>
                        <td>
                            <input type="text" 
                                   name="username" 
                                   id="username"
                                   placeholder="Choose a unique username" 
                                   pattern="[a-zA-Z0-9_]{3,20}" 
                                   title="Username should be 3-20 characters with letters, numbers, and underscores only"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required>
                            <span class="error-message" id="username-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="email"><b><i class="fas fa-envelope"></i> Email Address:</b></label></td>
                        <td>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   placeholder="Enter your real email address" 
                                   pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
                                   title="Please enter a valid email address"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                            <span class="error-message" id="email-error"></span>
                            <div class="email-notice">
                                <small>⚠️ Please use a real email address. Temporary or fake emails are not allowed.</small>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="cellno"><b><i class="fas fa-phone"></i> Mobile Number:</b></label></td>
                        <td>
                            <input type="tel" 
                                   name="cellno" 
                                   id="cellno"
                                   placeholder="98xxxxxxxx (Nepal format)" 
                                   pattern="(98|97)[0-9]{8}" 
                                   title="Phone number must be 10 digits starting with 98 or 97"
                                   value="<?php echo isset($_POST['cellno']) ? htmlspecialchars($_POST['cellno']) : ''; ?>"
                                   required>
                            <span class="error-message" id="cellno-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="address"><b><i class="fas fa-map-marker-alt"></i> Address:</b></label></td>
                        <td>
                            <input type="text" 
                                   name="address" 
                                   id="address"
                                   placeholder="Enter your full address" 
                                   title="Please enter your full address"
                                   value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                                   required>
                            <span class="error-message" id="address-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="password"><b><i class="fas fa-lock"></i> Password:</b></label></td>
                        <td>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter a strong password (min 8 characters)" 
                                   pattern=".{8,}" 
                                   title="Password must be at least 8 characters long"
                                   required>
                            <span class="error-message" id="password-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="cpassword"><b><i class="fas fa-lock"></i> Confirm Password:</b></label></td>
                        <td>
                            <input type="password" 
                                   name="cpassword" 
                                   id="cpassword"
                                   placeholder="Confirm your password" 
                                   required>
                            <span class="error-message" id="cpassword-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td><label for="level"><b><i class="fas fa-users"></i> Account Type:</b></label></td>
                        <td>
                            <select name="level" id="level" required>
                                <option value="" disabled selected>Choose your account type</option>
                                <option value="1" <?php echo (isset($_POST['level']) && $_POST['level'] == '1') ? 'selected' : ''; ?>>🔍 Property Seeker</option>
                                <option value="2" <?php echo (isset($_POST['level']) && $_POST['level'] == '2') ? 'selected' : ''; ?>>🏠 Property Owner</option>
                                <option value="3" <?php echo (isset($_POST['level']) && $_POST['level'] == '3') ? 'selected' : ''; ?>>🏢 Real Estate Agent</option>
                            </select>
                            <span class="error-message" id="level-error"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="2">
                            <button type="submit" name="register" class="btn-primary" id="registerBtn">
                                <i class="fas fa-envelope"></i> Send Verification Email
                            </button>
                        </td>
                    </tr>
                </table>
            </form>
            
            <?php } elseif($showOtpForm) { ?>
            <!-- OTP Verification Form -->
            <div class="otp-section">
                <h3><i class="fas fa-mobile-alt"></i> Enter Verification Code</h3>
                <p>We've sent a 6-digit verification code to your email address:</p>
                <div class="email-display">
                    <?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>
                </div>
                
                <form method="POST" id="otpVerificationForm">
                    <input type="hidden" name="verify_account" value="1">
                    <input type="hidden" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    
                    <table class="form-table">
                        <tr>
                            <td colspan="2">
                                <label for="otp_code"><b><i class="fas fa-key"></i> Verification Code:</b></label>
                                <input type="text" 
                                       name="otp_code" 
                                       id="otp_code" 
                                       class="otp-input"
                                       placeholder="000000" 
                                       maxlength="6" 
                                       pattern="[0-9]{6}" 
                                       title="Please enter a 6-digit number"
                                       required 
                                       autocomplete="off">
                                <span class="error-message" id="otp-error"></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <td colspan="2">
                                <button type="submit" class="btn-primary" id="verifyOtpBtn">
                                    <i class="fas fa-check-circle"></i> Verify & Create Account
                                </button>
                            </td>
                        </tr>
                    </table>
                </form>
                
                <div class="help-section">
                    <h4><i class="fas fa-question-circle"></i> Having trouble?</h4>
                    <ul>
                        <li>Check your spam/junk folder</li>
                        <li>Wait a few minutes for the email to arrive</li>
                        <li>The verification code expires in 15 minutes</li>
                        <li>Make sure you entered the correct email address</li>
                    </ul>
                </div>
            </div>
            <?php } ?>
            
        </div>
        
        <!-- Footer -->
        <div class="footer-links">
            <a href="signin.php"><i class="fas fa-sign-in-alt"></i> Already have an account? Sign In</a>
            <a href="index.php"><i class="fas fa-home"></i> Back to Home</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Signup page rendered successfully');
            
            // Form validation
            const registrationForm = document.getElementById('registrationForm');
            if (registrationForm) {
                registrationForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const cpassword = document.getElementById('cpassword').value;
                    
                    if (password !== cpassword) {
                        e.preventDefault();
                        alert('❌ Passwords do not match!');
                        return false;
                    }
                    
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('cellno').value;
                    
                    if (!email.includes('@') || !email.includes('.')) {
                        e.preventDefault();
                        alert('❌ Please enter a valid email address');
                        return false;
                    }
                    
                    if (!phone.match(/^(98|97)[0-9]{8}$/)) {
                        e.preventDefault();
                        alert('❌ Phone number must be 10 digits starting with 98 or 97');
                        return false;
                    }
                    
                    const submitBtn = document.getElementById('registerBtn');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending verification email...';
                    submitBtn.disabled = true;
                });
            }
            
            // OTP input handling
            const otpInput = document.getElementById('otp_code');
            if (otpInput) {
                otpInput.focus();
                
                otpInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 6) {
                        document.getElementById('verifyOtpBtn').click();
                    }
                });
                
                otpInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    const numericOnly = pastedText.replace(/[^0-9]/g, '').substring(0, 6);
                    this.value = numericOnly;
                    
                    if (numericOnly.length === 6) {
                        document.getElementById('verifyOtpBtn').click();
                    }
                });
            }
            
            // Add visual confirmation that page is working
            const contentSection = document.querySelector('.content-section');
            if (contentSection) {
                const indicator = document.createElement('div');
                indicator.innerHTML = '✅ Page rendered successfully - All systems working!';
                indicator.style.background = '#d4edda';
                indicator.style.color = '#155724';
                indicator.style.padding = '10px';
                indicator.style.borderRadius = '5px';
                indicator.style.margin = '10px 0';
                indicator.style.textAlign = 'center';
                indicator.style.fontWeight = 'bold';
                indicator.style.border = '1px solid #c3e6cb';
                contentSection.insertBefore(indicator, contentSection.firstChild);
            }
        });
    </script>
</body>
</html>
<?php
// End output buffering and send content
ob_end_flush();
?>
