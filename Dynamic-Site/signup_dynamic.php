<?php
session_start();
include "lib/Database.php";
include "classes/PreRegistrationVerification.php";
include "classes/EmailOTP.php";

$db = new Database();
$preReg = new PreRegistrationVerification();

$registrationMsg = "";
$showForm = true;
$showOtpForm = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signup'])) {
        // Handle file uploads
        $uploadedFiles = [];
        $uploadDir = "uploads/documents/";
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Process citizenship documents for owners and agents
        if (isset($_POST['level']) && in_array($_POST['level'], [2, 3])) {
            $requiredFiles = ['citizenship_front', 'citizenship_back'];
            
            foreach ($requiredFiles as $fileField) {
                if (isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] == 0) {
                    $file = $_FILES[$fileField];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($file['type'], $allowedTypes)) {
                        $registrationMsg = "<div class='alert alert-error'>❌ Invalid file type for " . ucfirst(str_replace('_', ' ', $fileField)) . ". Only JPG, PNG, and PDF files are allowed.</div>";
                        break;
                    }
                    
                    if ($file['size'] > $maxSize) {
                        $registrationMsg = "<div class='alert alert-error'>❌ File size too large for " . ucfirst(str_replace('_', ' ', $fileField)) . ". Maximum 5MB allowed.</div>";
                        break;
                    }
                    
                    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid() . "_" . $fileField . "." . $fileExtension;
                    $targetPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $uploadedFiles[$fileField] = $newFileName;
                    } else {
                        $registrationMsg = "<div class='alert alert-error'>❌ Failed to upload " . ucfirst(str_replace('_', ' ', $fileField)) . ".</div>";
                        break;
                    }
                } else if (isset($_POST['level']) && in_array($_POST['level'], [2, 3])) {
                    $registrationMsg = "<div class='alert alert-error'>❌ " . ucfirst(str_replace('_', ' ', $fileField)) . " is required for Property Owners and Real Estate Agents.</div>";
                    break;
                }
            }
            
            // Check citizenship ID for owners and agents
            if (empty($_POST['citizenship_id'])) {
                $registrationMsg = "<div class='alert alert-error'>❌ Citizenship Certificate Number is required for Property Owners and Real Estate Agents.</div>";
            }
        }
        
        if (empty($registrationMsg)) {
            // Prepare registration data
            $registrationData = [
                'fname' => $_POST['fname'],
                'lname' => $_POST['lname'],
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'cellno' => $_POST['cellno'],
                'password' => md5($_POST['password']),
                'level' => $_POST['level'],
                'requires_verification' => in_array($_POST['level'], [2, 3]),
                'uploaded_files' => $uploadedFiles,
                'citizenship_id' => $_POST['citizenship_id'] ?? ''
            ];
            
            // Attempt to register
            $result = $preReg->initiateEmailVerification($registrationData);
            
            if ($result['success']) {
                $registrationMsg = "<div class='alert alert-success'>✅ " . $result['message'] . "</div>";
                $showForm = false;
                $showOtpForm = true;
                $_SESSION['pending_email'] = $_POST['email'];
            } else {
                $registrationMsg = "<div class='alert alert-error'>❌ " . $result['message'] . "</div>";
            }
        }
    }
    
    if (isset($_POST['verify_otp'])) {
        $email = $_SESSION['pending_email'] ?? '';
        $otpCode = $_POST['otp_code'];
        
        $verifyResult = $preReg->verifyAndCreateAccount($email, '', $otpCode);
        
        if ($verifyResult['success']) {
            $registrationMsg = "<div class='alert alert-success'>🎉 " . $verifyResult['message'] . "</div>";
            $showOtpForm = false;
            unset($_SESSION['pending_email']);
        } else {
            $registrationMsg = "<div class='alert alert-error'>❌ " . $verifyResult['message'] . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Property Nepal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .signup-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .signup-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .signup-header p {
            color: #666;
            font-size: 16px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .icon {
            margin-right: 8px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: white;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        select {
            cursor: pointer;
        }
        
        .account-type-section {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #e1e5e9;
            margin-bottom: 25px;
        }
        
        .account-type-section label {
            color: #667eea;
            font-size: 16px;
            font-weight: bold;
        }
        
        #account-type-info {
            display: block;
            margin-top: 8px;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .info-seeker {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .info-owner {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .info-agent {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        #document-section {
            background: #fff9c4;
            border: 2px solid #fbc02d;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            transition: all 0.3s ease;
        }
        
        .form-section-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-section-header h3 {
            color: #f57c00;
            margin-bottom: 10px;
        }
        
        .form-section-header p {
            color: #666;
            font-size: 14px;
        }
        
        .verification-note {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .note-content h4 {
            color: #2e7d32;
            margin-bottom: 12px;
        }
        
        .note-content ul {
            list-style: none;
            padding: 0;
        }
        
        .note-content li {
            margin-bottom: 8px;
            color: #424242;
            font-size: 13px;
        }
        
        small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .otp-container {
            text-align: center;
        }
        
        .otp-input {
            font-size: 24px;
            letter-spacing: 8px;
            text-align: center;
            width: 200px;
            margin: 20px auto;
        }
        
        @media (max-width: 768px) {
            .signup-container {
                padding: 20px;
                margin: 10px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>🏠 Join Property Nepal</h1>
            <p>Create your account to start your property journey</p>
        </div>
        
        <?php echo $registrationMsg; ?>
        
        <?php if ($showForm): ?>
        <form method="POST" enctype="multipart/form-data" id="signupForm">
            <!-- Account Type Selection - At Top -->
            <div class="form-group account-type-section">
                <label for="level">
                    <i class="icon">👨‍💼</i>
                    Choose Your Account Type
                </label>
                <select id="level" name="level" required onchange="toggleDocumentSection()">
                    <option value="">Select Account Type</option>
                    <option value="1">🏠 Property Seeker</option>
                    <option value="2">🏘️ Property Owner</option>
                    <option value="3">🏢 Real Estate Agent</option>
                </select>
                <small id="account-type-info"></small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="fname">
                        <i class="icon">👤</i>
                        First Name *
                    </label>
                    <input type="text" id="fname" name="fname" required>
                </div>
                
                <div class="form-group">
                    <label for="lname">
                        <i class="icon">👤</i>
                        Last Name *
                    </label>
                    <input type="text" id="lname" name="lname" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">
                    <i class="icon">🔤</i>
                    Username *
                </label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="icon">📧</i>
                    Email Address *
                </label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="cellno">
                    <i class="icon">📱</i>
                    Phone Number *
                </label>
                <input type="tel" id="cellno" name="cellno" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="icon">🔒</i>
                        Password *
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confpass">
                        <i class="icon">🔒</i>
                        Confirm Password *
                    </label>
                    <input type="password" id="confpass" name="confpass" required>
                </div>
            </div>
            
            <!-- Document Upload Section (hidden by default) -->
            <div id="document-section" style="display: none;">
                <div class="form-section-header">
                    <h3>📄 Document Verification Required</h3>
                    <p>As a Property Owner or Real Estate Agent, you need to upload verification documents.</p>
                </div>
                
                <div class="form-group">
                    <label for="citizenship_id">
                        <i class="icon">🆔</i>
                        Citizenship Certificate Number *
                    </label>
                    <input type="text" id="citizenship_id" name="citizenship_id" placeholder="Enter your citizenship number" maxlength="20">
                    <small>Enter the number as shown on your citizenship certificate</small>
                </div>
                
                <div class="form-group">
                    <label for="citizenship_front">
                        <i class="icon">📄</i>
                        Citizenship Certificate (Front) *
                    </label>
                    <input type="file" id="citizenship_front" name="citizenship_front" accept=".pdf,.jpg,.jpeg,.png">
                    <small>Upload clear PDF or image of the front side (Max 5MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="citizenship_back">
                        <i class="icon">📄</i>
                        Citizenship Certificate (Back) *
                    </label>
                    <input type="file" id="citizenship_back" name="citizenship_back" accept=".pdf,.jpg,.jpeg,.png">
                    <small>Upload clear PDF or image of the back side (Max 5MB)</small>
                </div>
                
                <div class="verification-note">
                    <div class="note-content">
                        <h4>📋 Verification Process:</h4>
                        <ul>
                            <li>✅ Your documents will be reviewed by our admin team</li>
                            <li>⏱️ Verification typically takes 1-2 business days</li>
                            <li>📧 You'll receive email notification once approved</li>
                            <li>🏠 Only verified users can list properties</li>
                            <li>🔒 Your documents are stored securely and used only for verification</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="signup" class="btn-submit">
                <i class="icon">✨</i>
                <span id="submit-text">Create Account</span>
            </button>
            
            <div class="form-footer">
                Already have an account? <a href="signin.php">Sign In</a>
            </div>
        </form>
        <?php endif; ?>
        
        <?php if ($showOtpForm): ?>
        <div class="otp-container">
            <h2>📧 Verify Your Email</h2>
            <p>We've sent a verification code to your email address.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="otp_code">Enter Verification Code</label>
                    <input type="text" id="otp_code" name="otp_code" class="otp-input" maxlength="6" required>
                </div>
                
                <button type="submit" name="verify_otp" class="btn-submit">
                    ✅ Verify Email
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleDocumentSection() {
            const levelSelect = document.getElementById('level');
            const documentSection = document.getElementById('document-section');
            const accountInfo = document.getElementById('account-type-info');
            const submitText = document.getElementById('submit-text');
            const citizenshipId = document.getElementById('citizenship_id');
            const citizenshipFront = document.getElementById('citizenship_front');
            const citizenshipBack = document.getElementById('citizenship_back');
            
            const selectedValue = levelSelect.value;
            
            if (selectedValue === '1') {
                // Property Seeker
                documentSection.style.display = 'none';
                accountInfo.textContent = '🏠 Perfect! You can browse and search for properties without verification.';
                accountInfo.className = 'info-seeker';
                submitText.textContent = 'Create Account';
                
                // Remove required attribute from document fields
                if (citizenshipId) citizenshipId.removeAttribute('required');
                if (citizenshipFront) citizenshipFront.removeAttribute('required');
                if (citizenshipBack) citizenshipBack.removeAttribute('required');
                
            } else if (selectedValue === '2') {
                // Property Owner
                documentSection.style.display = 'block';
                accountInfo.textContent = '🏘️ As a Property Owner, you can list your properties after document verification.';
                accountInfo.className = 'info-owner';
                submitText.textContent = 'Create Account & Submit Documents';
                
                // Add required attribute to document fields
                if (citizenshipId) citizenshipId.setAttribute('required', 'required');
                if (citizenshipFront) citizenshipFront.setAttribute('required', 'required');
                if (citizenshipBack) citizenshipBack.setAttribute('required', 'required');
                
            } else if (selectedValue === '3') {
                // Real Estate Agent
                documentSection.style.display = 'block';
                accountInfo.textContent = '🏢 As a Real Estate Agent, you can manage multiple properties after verification.';
                accountInfo.className = 'info-agent';
                submitText.textContent = 'Create Account & Submit Documents';
                
                // Add required attribute to document fields
                if (citizenshipId) citizenshipId.setAttribute('required', 'required');
                if (citizenshipFront) citizenshipFront.setAttribute('required', 'required');
                if (citizenshipBack) citizenshipBack.setAttribute('required', 'required');
                
            } else {
                // No selection
                documentSection.style.display = 'none';
                accountInfo.textContent = '';
                accountInfo.className = '';
                submitText.textContent = 'Create Account';
                
                // Remove required attribute from document fields
                if (citizenshipId) citizenshipId.removeAttribute('required');
                if (citizenshipFront) citizenshipFront.removeAttribute('required');
                if (citizenshipBack) citizenshipBack.removeAttribute('required');
            }
        }
        
        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confpass = document.getElementById('confpass').value;
            
            if (password !== confpass) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            const level = document.getElementById('level').value;
            if (level === '2' || level === '3') {
                const citizenshipId = document.getElementById('citizenship_id').value;
                const citizenshipFront = document.getElementById('citizenship_front').files.length;
                const citizenshipBack = document.getElementById('citizenship_back').files.length;
                
                if (!citizenshipId.trim()) {
                    e.preventDefault();
                    alert('Citizenship Certificate Number is required!');
                    return false;
                }
                
                if (citizenshipFront === 0) {
                    e.preventDefault();
                    alert('Please upload the front side of your citizenship certificate!');
                    return false;
                }
                
                if (citizenshipBack === 0) {
                    e.preventDefault();
                    alert('Please upload the back side of your citizenship certificate!');
                    return false;
                }
            }
        });
        
        // File size validation
        function validateFileSize(input) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (input.files.length > 0) {
                const file = input.files[0];
                if (file.size > maxSize) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return false;
                }
            }
            return true;
        }
        
        // Add file size validation to file inputs
        document.getElementById('citizenship_front').addEventListener('change', function() {
            validateFileSize(this);
        });
        
        document.getElementById('citizenship_back').addEventListener('change', function() {
            validateFileSize(this);
        });
    </script>
</body>
</html>
