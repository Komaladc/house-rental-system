<?php 
    include"inc/header.php";
    include"classes/EmailOTP.php";
    
    $emailOTP = new EmailOTP();
    $verificationMsg = "";
    $showResendButton = false;
    $email = "";
    
    // Get email from URL parameter or session
    if(isset($_GET['email'])) {
        $email = mysqli_real_escape_string($db->link, $_GET['email']);
    } elseif(isset($_SESSION['pending_verification_email'])) {
        $email = $_SESSION['pending_verification_email'];
    }
    
    // Handle OTP verification
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(isset($_POST['verify_otp'])) {
            $entered_otp = mysqli_real_escape_string($db->link, $_POST['otp']);
            $email = mysqli_real_escape_string($db->link, $_POST['email']);
            
            if($emailOTP->verifyOTP($email, $entered_otp, 'registration')) {
                // OTP verified successfully - activate the user account
                $updateQuery = "UPDATE tbl_user SET email_verified = 1 WHERE userEmail = '$email'";
                $updated = $db->update($updateQuery);
                
                if($updated) {
                    // Clear pending verification session
                    unset($_SESSION['pending_verification_email']);
                    
                    // Auto-login the user
                    $getUserQuery = "SELECT * FROM tbl_user WHERE userEmail = '$email'";
                    $result = $db->select($getUserQuery);
                    
                    if($result && $result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Set session variables
                        Session::set("userlogin", true);
                        Session::set("userId", $user['userId']);
                        Session::set("userFName", $user['firstName']);
                        Session::set("userLName", $user['lastName']);
                        Session::set("userImg", $user['userImg']);
                        Session::set("userEmail", $user['userEmail']);
                        Session::set("cellNo", $user['cellNo']);
                        Session::set("phoneNo", $user['phoneNo']);
                        Session::set("userAddress", $user['userAddress']);
                        Session::set("userLevel", $user['userLevel']);
                        
                        $verificationMsg = "<div class='alert alert_success'>
                            🎉 Email verified successfully! Welcome to Property Finder Nepal!<br>
                            Redirecting to your dashboard...
                        </div>";
                        
                        // Redirect based on user level
                        if($user['userLevel'] == 3) {
                            echo"<script>setTimeout(function(){ window.location='Admin/dashboard_agent.php'; }, 3000);</script>";
                        } elseif($user['userLevel'] == 2) {
                            echo"<script>setTimeout(function(){ window.location='Admin/dashboard_owner.php'; }, 3000);</script>";
                        } else {
                            echo"<script>setTimeout(function(){ window.location='index.php'; }, 3000);</script>";
                        }
                    }
                } else {
                    $verificationMsg = "<div class='alert alert_danger'>Error updating account status. Please try again.</div>";
                }
            } else {
                $verificationMsg = "<div class='alert alert_danger'>❌ Invalid or expired OTP. Please check the code and try again.</div>";
                $showResendButton = true;
            }
        }
        
        // Handle resend OTP
        if(isset($_POST['resend_otp'])) {
            $email = mysqli_real_escape_string($db->link, $_POST['email']);
            
            // Generate new OTP
            $newOTP = $emailOTP->generateOTP();
            
            // Store in database
            if($emailOTP->storeOTP($email, $newOTP, 'registration')) {
                // Send email
                if($emailOTP->sendOTP($email, $newOTP, 'registration')) {
                    $verificationMsg = "<div class='alert alert_success'>📧 New verification code sent to your email!</div>";
                } else {
                    $verificationMsg = "<div class='alert alert_danger'>Failed to send email. Please try again.</div>";
                }
            } else {
                $verificationMsg = "<div class='alert alert_danger'>Error generating new code. Please try again.</div>";
            }
        }
    }
    
    // If no email provided, redirect to signup
    if(empty($email)) {
        echo"<script>window.location='signup.php'</script>";
        exit();
    }
?>

<div class="page_title">
    <h1 class="sub-title">📧 Email Verification</h1>
</div>

<div class="container form_container background" style="background-image:url(images/signup_bg.jpg);background-blend-mode:overlay;">
    <div class="mcol_6">
        <div class="verification_form">
            <div class="verification_header">
                <h2>🔐 Verify Your Email Address</h2>
                <p>We've sent a 6-digit verification code to:</p>
                <p class="email_highlight"><strong><?php echo htmlspecialchars($email); ?></strong></p>
                <p>Please check your email (including spam folder) and enter the code below:</p>
            </div>
            
            <?php if($verificationMsg) { echo $verificationMsg; } ?>
            
            <form action="" method="POST" id="otpVerificationForm">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="form_group">
                    <label for="otp"><b>📱 Enter 6-Digit Verification Code:</b></label>
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
                    <button type="submit" name="verify_otp" class="btn_success" id="verifyBtn">
                        ✅ Verify Email
                    </button>
                </div>
            </form>
            
            <div class="resend_section">
                <p>Didn't receive the code?</p>
                <form action="" method="POST" style="display: inline;">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <button type="submit" name="resend_otp" class="btn_warning">
                        📧 Resend Code
                    </button>
                </form>
                
                <div class="verification_help">
                    <h4>💡 Troubleshooting Tips:</h4>
                    <ul>
                        <li>Check your spam/junk folder</li>
                        <li>Make sure you entered the correct email address</li>
                        <li>The code expires in 10 minutes</li>
                        <li>Contact support if you continue having issues</li>
                    </ul>
                </div>
            </div>
            
            <div class="back_link">
                <a href="signup.php">← Back to Registration</a>
            </div>
        </div>
    </div>
</div>

<style>
.verification_form {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin: 50px auto;
}

.verification_header {
    text-align: center;
    margin-bottom: 30px;
}

.verification_header h2 {
    color: #2c3e50;
    margin-bottom: 20px;
}

.email_highlight {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
    margin: 15px 0;
    font-family: 'Courier New', monospace;
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

#otp {
    width: 100%;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 24px !important;
    text-align: center;
    letter-spacing: 8px;
    background: #f8f9fa;
}

#otp:focus {
    border-color: #3498db;
    outline: none;
    background: white;
}

.btn_success, .btn_warning {
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

.resend_section {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.verification_help {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
}

.verification_help h4 {
    color: #3498db;
    margin-bottom: 10px;
}

.verification_help ul {
    margin: 0;
    padding-left: 20px;
}

.verification_help li {
    margin: 8px 0;
    color: #555;
}

.back_link {
    text-align: center;
    margin-top: 20px;
}

.back_link a {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
}

.back_link a:hover {
    text-decoration: underline;
}

/* OTP Input Animation */
#otp.error {
    border-color: #e74c3c;
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Success state */
.success-state {
    text-align: center;
    color: #27ae60;
}

.success-state h3 {
    color: #27ae60;
    margin-bottom: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.getElementById('verifyBtn');
    
    // Auto-focus on OTP input
    otpInput.focus();
    
    // Only allow numbers
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-submit when 6 digits are entered
        if(this.value.length === 6) {
            verifyBtn.click();
        }
    });
    
    // Prevent paste of non-numeric content
    otpInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numericOnly = pastedText.replace(/[^0-9]/g, '').substring(0, 6);
        this.value = numericOnly;
        
        if(numericOnly.length === 6) {
            verifyBtn.click();
        }
    });
    
    // Form validation
    document.getElementById('otpVerificationForm').addEventListener('submit', function(e) {
        const otp = otpInput.value;
        
        if(otp.length !== 6) {
            e.preventDefault();
            otpInput.classList.add('error');
            document.getElementById('otp-error').textContent = 'Please enter a 6-digit code';
            
            setTimeout(function() {
                otpInput.classList.remove('error');
            }, 2000);
            
            return false;
        }
        
        verifyBtn.textContent = '🔄 Verifying...';
        verifyBtn.disabled = true;
    });
});
</script>

<?php include"inc/footer.php"; ?>
