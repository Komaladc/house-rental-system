<?php 
    include"inc/header.php";
    include"classes/EmailOTP.php";
    
    $emailOTP = new EmailOTP();
    $resetMsg = "";
    $showOtpForm = false;
    $showNewPasswordForm = false;
    $email = "";
    
    // Handle email submission for OTP
    if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['send_otp'])) {
        $email = mysqli_real_escape_string($db->link, $_POST['email']);
        
        // Check if email exists in database
        $checkQuery = "SELECT * FROM tbl_user WHERE userEmail = '$email'";
        $result = $db->select($checkQuery);
        
        if($result && $result->num_rows > 0) {
            // Generate and send OTP
            $otp = $emailOTP->generateOTP();
            
            if($emailOTP->storeOTP($email, $otp, 'password_reset')) {
                if($emailOTP->sendOTP($email, $otp, 'password_reset')) {
                    $resetMsg = "<div class='alert alert_success'>📧 Password reset code sent to your email!</div>";
                    $showOtpForm = true;
                } else {
                    $resetMsg = "<div class='alert alert_danger'>Failed to send email. Please try again.</div>";
                }
            } else {
                $resetMsg = "<div class='alert alert_danger'>Error generating reset code. Please try again.</div>";
            }
        } else {
            $resetMsg = "<div class='alert alert_danger'>❌ Email address not found in our records.</div>";
        }
    }
    
    // Handle OTP verification
    if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verify_otp'])) {
        $email = mysqli_real_escape_string($db->link, $_POST['email']);
        $otp = mysqli_real_escape_string($db->link, $_POST['otp']);
        
        if($emailOTP->verifyOTP($email, $otp, 'password_reset')) {
            $resetMsg = "<div class='alert alert_success'>✅ Code verified! Please enter your new password.</div>";
            $showNewPasswordForm = true;
        } else {
            $resetMsg = "<div class='alert alert_danger'>❌ Invalid or expired code. Please try again.</div>";
            $showOtpForm = true;
        }
    }
    
    // Handle new password submission
    if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['reset_password'])) {
        $email = mysqli_real_escape_string($db->link, $_POST['email']);
        $newPassword = mysqli_real_escape_string($db->link, $_POST['new_password']);
        $confirmPassword = mysqli_real_escape_string($db->link, $_POST['confirm_password']);
        
        // Validate passwords
        if(strlen($newPassword) < 6) {
            $resetMsg = "<div class='alert alert_danger'>Password must be at least 6 characters long.</div>";
            $showNewPasswordForm = true;
        } elseif($newPassword !== $confirmPassword) {
            $resetMsg = "<div class='alert alert_danger'>Passwords do not match.</div>";
            $showNewPasswordForm = true;
        } else {
            // Update password
            $hashedPassword = md5($newPassword);
            $updateQuery = "UPDATE tbl_user SET userPass = '$hashedPassword' WHERE userEmail = '$email'";
            
            if($db->update($updateQuery)) {
                $resetMsg = "<div class='alert alert_success'>
                    🎉 Password reset successfully!<br>
                    You can now sign in with your new password.<br>
                    <a href='signin.php' class='btn btn_primary' style='color: white; text-decoration: none; padding: 10px 20px; background: #3498db; border-radius: 5px; display: inline-block; margin-top: 10px;'>
                        Sign In Now ➡️
                    </a>
                </div>";
            } else {
                $resetMsg = "<div class='alert alert_danger'>Error updating password. Please try again.</div>";
                $showNewPasswordForm = true;
            }
        }
    }
    
    // Determine email from POST or keep the current one
    if(isset($_POST['email'])) {
        $email = $_POST['email'];
    }
?>

<div class="page_title">
    <h1 class="sub-title">🔐 Reset Password</h1>
</div>

<div class="container form_container background" style="background-image:url(images/signin_bg.jpg);background-blend-mode:overlay;">
    <div class="mcol_6">
        <div class="password_reset_form">
            <div class="reset_header">
                <h2>🔄 Password Reset</h2>
                <p>Enter your email address to receive a verification code</p>
            </div>
            
            <?php if($resetMsg) { echo $resetMsg; } ?>
            
            <?php if(!$showOtpForm && !$showNewPasswordForm) { ?>
            <!-- Step 1: Email Input -->
            <form action="" method="POST" id="emailForm">
                <div class="form_group">
                    <label for="email"><b>📧 Email Address:</b></label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           placeholder="Enter your registered email" 
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
                           title="Please enter a valid email address"
                           value="<?php echo htmlspecialchars($email); ?>"
                           required>
                    <span class="error-message" id="email-error"></span>
                </div>
                
                <div class="form_group">
                    <button type="submit" name="send_otp" class="btn_primary">
                        📧 Send Reset Code
                    </button>
                </div>
            </form>
            <?php } ?>
            
            <?php if($showOtpForm) { ?>
            <!-- Step 2: OTP Verification -->
            <div class="otp_section">
                <h3>📱 Enter Verification Code</h3>
                <p>We've sent a 6-digit code to: <strong><?php echo htmlspecialchars($email); ?></strong></p>
                
                <form action="" method="POST" id="otpForm">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    
                    <div class="form_group">
                        <label for="otp"><b>Verification Code:</b></label>
                        <input type="text" 
                               name="otp" 
                               id="otp" 
                               placeholder="000000" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               title="Please enter a 6-digit number"
                               required 
                               autocomplete="off"
                               style="font-size: 24px; text-align: center; letter-spacing: 8px;">
                        <span class="error-message" id="otp-error"></span>
                    </div>
                    
                    <div class="form_group">
                        <button type="submit" name="verify_otp" class="btn_success">
                            ✅ Verify Code
                        </button>
                    </div>
                </form>
                
                <div class="resend_section">
                    <p>Didn't receive the code?</p>
                    <form action="" method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <button type="submit" name="send_otp" class="btn_warning">
                            📧 Resend Code
                        </button>
                    </form>
                </div>
            </div>
            <?php } ?>
            
            <?php if($showNewPasswordForm) { ?>
            <!-- Step 3: New Password -->
            <div class="new_password_section">
                <h3>🔒 Set New Password</h3>
                <p>Enter your new password for: <strong><?php echo htmlspecialchars($email); ?></strong></p>
                
                <form action="" method="POST" id="passwordForm">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    
                    <div class="form_group">
                        <label for="new_password"><b>New Password:</b></label>
                        <input type="password" 
                               name="new_password" 
                               id="new_password" 
                               placeholder="Enter new password" 
                               minlength="6"
                               required>
                        <span class="error-message" id="new_password-error"></span>
                    </div>
                    
                    <div class="form_group">
                        <label for="confirm_password"><b>Confirm Password:</b></label>
                        <input type="password" 
                               name="confirm_password" 
                               id="confirm_password" 
                               placeholder="Confirm new password" 
                               minlength="6"
                               required>
                        <span class="error-message" id="confirm_password-error"></span>
                    </div>
                    
                    <div class="form_group">
                        <button type="submit" name="reset_password" class="btn_success">
                            🔐 Update Password
                        </button>
                    </div>
                </form>
            </div>
            <?php } ?>
            
            <div class="back_links">
                <a href="signin.php">← Back to Sign In</a> |
                <a href="signup.php">Create New Account</a>
            </div>
        </div>
    </div>
</div>

<style>
.password_reset_form {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin: 50px auto;
}

.reset_header {
    text-align: center;
    margin-bottom: 30px;
}

.reset_header h2 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.form_group {
    margin-bottom: 25px;
}

.form_group label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: bold;
}

.form_group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
}

.form_group input:focus {
    border-color: #3498db;
    outline: none;
}

#otp {
    font-size: 24px !important;
    text-align: center;
    letter-spacing: 8px;
    background: #f8f9fa;
}

.btn_primary, .btn_success, .btn_warning {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 10px 0;
}

.btn_primary {
    background: #3498db;
    color: white;
}

.btn_primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn_success {
    background: #27ae60;
    color: white;
}

.btn_success:hover {
    background: #229954;
    transform: translateY(-2px);
}

.btn_warning {
    background: #f39c12;
    color: white;
}

.btn_warning:hover {
    background: #e67e22;
}

.otp_section, .new_password_section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.otp_section h3, .new_password_section h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.resend_section {
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.back_links {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.back_links a {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
    margin: 0 10px;
}

.back_links a:hover {
    text-decoration: underline;
}

.error-message {
    color: #e74c3c;
    font-size: 14px;
    margin-top: 5px;
    display: block;
}
</style>

<script src="js/form-validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on appropriate input
    const emailInput = document.getElementById('email');
    const otpInput = document.getElementById('otp');
    const newPasswordInput = document.getElementById('new_password');
    
    if(otpInput) {
        otpInput.focus();
        
        // Only allow numbers in OTP
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits are entered
            if(this.value.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });
    } else if(emailInput) {
        emailInput.focus();
    } else if(newPasswordInput) {
        newPasswordInput.focus();
    }
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if(confirmPasswordInput) {
        confirmPasswordInput.addEventListener('blur', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if(confirmPassword && newPassword !== confirmPassword) {
                document.getElementById('confirm_password-error').textContent = 'Passwords do not match';
                this.style.borderColor = '#e74c3c';
            } else {
                document.getElementById('confirm_password-error').textContent = '';
                this.style.borderColor = '#ddd';
            }
        });
    }
});
</script>

<?php include"inc/footer.php"; ?>
