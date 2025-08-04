<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fix include paths - use absolute paths based on current directory
$current_dir = dirname(__FILE__);

// Include essential files with proper paths
include_once $current_dir . "/lib/Session.php";
Session::init();

include_once $current_dir . "/lib/Database.php";
include_once $current_dir . "/helpers/Format.php";
include_once $current_dir . "/helpers/NepalTime.php";

// Set Nepal timezone
date_default_timezone_set('Asia/Kathmandu');

// Include verification classes
include_once $current_dir . "/classes/PreRegistrationVerification.php";
include_once $current_dir . "/classes/EmailOTP.php";

$preVerification = new PreRegistrationVerification();
$emailOTP = new EmailOTP();
$registrationMsg = "";
$showForm = true;
$showOtpForm = false;
$redirectMessage = "";

// Handle OTP verification
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_account'])){
    $email = $_POST['email'];
    $otp = $_POST['otp_code'];
    
    $result = $preVerification->verifyOTPAndCreateAccount($email, $otp);
    
    if($result['success']) {
        $registrationMsg = $result['message'];
        $showForm = false;
        $showOtpForm = false;
        
        // Set different redirect messages based on user type
        if($result['requires_verification']) {
            // Owner/Agent - redirect to regular signin (no admin access until approved)
            $redirectMessage = "
                <div style='background:#fff3cd; padding:15px; margin:15px 0; border-radius:5px; border:1px solid #ffeaa7;'>
                    <h4>📋 Admin Verification Required</h4>
                    <p>Your account needs admin approval before you can access owner/agent features.</p>
                    <p><strong>What happens next:</strong></p>
                    <ul>
                        <li>Admin will review your registration</li>
                        <li>You'll receive an email when approved</li>
                        <li>After approval, you can access the owner/agent dashboard</li>
                    </ul>
                    <p><strong>Meanwhile:</strong> You can sign in as a regular user to browse properties.</p>
                    <a href='signin.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Sign In Now</a>
                </div>
            ";
        } else {
            // Regular user - redirect to signin 
            $redirectMessage = "
                <div style='background:#d4edda; padding:15px; margin:15px 0; border-radius:5px; border:1px solid #c3e6cb;'>
                    <h4>🎉 Account Ready!</h4>
                    <p>Your account is active and ready to use. You can now sign in and start browsing properties!</p>
                    <a href='signin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Sign In Now</a>
                </div>
            ";
        }
    } else {
        $registrationMsg = $result['message'];
        $showOtpForm = true;
    }
}

// Handle initial registration
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])){
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $cellno = $_POST['cellno'];
    $level = $_POST['level'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    if($password != $cpassword){
        $registrationMsg = "Passwords do not match!";
    } else {
        $registrationData = [
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'cellno' => $cellno,
            'level' => $level,
            'password' => $password
        ];
        
        $result = $preVerification->initiateEmailVerification($registrationData);
        
        if($result['success']) {
            $registrationMsg = $result['message'];
            $showForm = false;
            $showOtpForm = true;
        } else {
            $registrationMsg = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 House Rental Registration - Enhanced</title>
    <link href="css/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="mystyle.css" rel="stylesheet">
    <style>
        .registration-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 30px;
        }
        .card-body {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e3e3e3;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 15px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }
        .user-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .user-type-option {
            padding: 15px;
            border: 2px solid #e3e3e3;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .user-type-option:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .user-type-option.selected {
            border-color: #667eea;
            background-color: #667eea;
            color: white;
        }
        .user-type-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header">
                <h1><i class="fas fa-home"></i> House Rental Registration</h1>
                <p class="mb-0">Join our platform to find your perfect home</p>
            </div>
            
            <div class="card-body">
                <?php if($registrationMsg): ?>
                    <div class="alert alert-info alert-custom">
                        <i class="fas fa-info-circle"></i> <?php echo $registrationMsg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($redirectMessage): ?>
                    <?php echo $redirectMessage; ?>
                <?php endif; ?>
                
                <?php if($showOtpForm): ?>
                    <form action="" method="POST">
                        <div class="text-center mb-4">
                            <h3><i class="fas fa-envelope-open-text"></i> Verify Your Email</h3>
                            <p>We've sent a verification code to your email address</p>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" 
                                   readonly required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Verification Code</label>
                            <input type="text" name="otp_code" class="form-control" 
                                   placeholder="Enter the 6-digit code" required>
                        </div>
                        
                        <button type="submit" name="verify_account" class="btn btn-primary btn-register w-100">
                            <i class="fas fa-check-circle"></i> Verify & Create Account
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p><a href="signup_enhanced.php">← Back to Registration</a></p>
                    </div>
                
                <?php elseif($showForm): ?>
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> First Name *</label>
                                    <input type="text" name="fname" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Last Name *</label>
                                    <input type="text" name="lname" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Mobile Number *</label>
                            <input type="tel" name="cellno" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Account Type *</label>
                            <div class="user-type-selector">
                                <div class="user-type-option" onclick="selectUserType(1)">
                                    <div class="user-type-icon">🏠</div>
                                    <div><strong>House Seeker</strong></div>
                                    <small>Browse & rent properties</small>
                                </div>
                                <div class="user-type-option" onclick="selectUserType(2)">
                                    <div class="user-type-icon">🏘️</div>
                                    <div><strong>Property Owner</strong></div>
                                    <small>List your properties</small>
                                </div>
                                <div class="user-type-option" onclick="selectUserType(3)">
                                    <div class="user-type-icon">🏢</div>
                                    <div><strong>Real Estate Agent</strong></div>
                                    <small>Manage multiple properties</small>
                                </div>
                            </div>
                            <input type="hidden" name="level" id="selectedLevel" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> Confirm Password *</label>
                                    <input type="password" name="cpassword" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="submit" class="btn btn-primary btn-register w-100">
                            <i class="fas fa-envelope"></i> Send Verification Email
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="signin.php">Sign In Here</a></p>
                        <p><a href="index.php"><i class="fas fa-home"></i> Back to Home</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function selectUserType(level) {
            // Remove selected class from all options
            document.querySelectorAll('.user-type-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.target.closest('.user-type-option').classList.add('selected');
            
            // Set the hidden input value
            document.getElementById('selectedLevel').value = level;
        }
    </script>
</body>
</html>
