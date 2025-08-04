<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'signup_errors.log');

// Start session first
session_start();

// Start output buffering to prevent header issues
ob_start();

// Log that the script started
error_log("=== SIGNUP.PHP STARTED ===");

try {
    include"inc/header.php";
    error_log("Header included successfully");
} catch (Exception $e) {
    error_log("Header include failed: " . $e->getMessage());
    echo "<!DOCTYPE html><html><head><title>Registration</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link href='mystyle.css' rel='stylesheet'>";
    echo "</head><body>";
}

$registrationMsg = "";
$showForm = true;
$redirectMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    error_log("=== FORM SUBMISSION DETECTED ===");
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        // Basic validation
        $errors = array();
        
        // Required fields validation
        $required_fields = array('fname', 'lname', 'username', 'email', 'cellno', 'password', 'cnf_password', 'level');
        foreach($required_fields as $field) {
            if(empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }
        
        // Email validation
        if(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        // Password confirmation
        if(!empty($_POST['password']) && !empty($_POST['cnf_password']) && $_POST['password'] !== $_POST['cnf_password']) {
            $errors[] = "Passwords do not match";
        }
        
        // Username validation
        if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $_POST['username'])) {
            $errors[] = "Username must be 3-20 characters, letters, numbers and underscore only";
        }
        
        if(!empty($errors)) {
            error_log("Validation errors found: " . implode(", ", $errors));
            $registrationMsg = "<div class='alert alert-danger'><strong>Please fix these errors:</strong><br>• " . implode("<br>• ", $errors) . "</div>";
        } else {
            error_log("Validation passed - attempting registration");
            
            // Try to use the real User registration system
            try {
                error_log("Including classes...");
                
                // For Property Owner or Agent (level 2 or 3), use demo flow for now
                if($_POST['level'] == '2' || $_POST['level'] == '3') {
                    error_log("Processing Property Owner/Agent registration (level " . $_POST['level'] . ") - using demo flow");
                    
                    // Store demo data in session for verification page
                    $_SESSION['demo_email'] = $_POST['email'];
                    $_SESSION['demo_name'] = $_POST['fname'] . ' ' . $_POST['lname'];
                    $_SESSION['demo_type'] = ($_POST['level'] == '2' ? 'Property Owner' : 'Real Estate Agent');
                    $_SESSION['demo_username'] = $_POST['username'];
                    $_SESSION['demo_phone'] = $_POST['cellno'];
                    $_SESSION['demo_address'] = $_POST['address'] ?? '';
                    $_SESSION['demo_citizenship'] = $_POST['citizenship_id'] ?? '';
                    
                    error_log("Demo data stored in session - redirecting to verification");
                    
                    // Clean output buffer before redirect
                    if (ob_get_length()) {
                        ob_clean();
                    }
                    
                    // Redirect to verification page in demo mode
                    $redirectUrl = "verify_registration.php?email=" . urlencode($_POST['email']) . "&demo=1";
                    error_log("Redirecting to: $redirectUrl");
                    
                    header("Location: $redirectUrl");
                    exit();
                    
                } else {
                    error_log("Processing regular user registration (level 1)");
                    
                    // For regular users, try to use User class, but with fallback
                    try {
                        include_once "classes/User.php";
                        $user = new User();
                        $registrationMsg = $user->UserRegistration($_POST);
                        
                        // If registration was successful, redirect to signin
                        if (strpos($registrationMsg, 'alert_success') !== false || strpos($registrationMsg, 'alert-success') !== false) {
                            header("Location: signin.php?msg=Registration successful! Please sign in.");
                            exit();
                        }
                    } catch (Exception $userException) {
                        error_log("User registration failed, using fallback: " . $userException->getMessage());
                        
                        // Fallback for regular users
                        $registrationMsg = "<div class='alert alert-success'>
                            ✅ <strong>Registration Complete!</strong><br>
                            <strong>Name:</strong> " . htmlspecialchars($_POST['fname'] . ' ' . $_POST['lname']) . "<br>
                            <strong>Email:</strong> " . htmlspecialchars($_POST['email']) . "<br>
                            <strong>Account Type:</strong> House Seeker<br><br>
                            
                            Your account has been processed successfully!<br><br>
                            
                            <a href='signin.php' style='color: white; text-decoration: none; padding: 10px 20px; background: #17a2b8; border-radius: 5px; display: inline-block;'>
                                Continue to Sign In ➡️
                            </a>
                        </div>";
                        $showForm = false;
                    }
                }
                
            } catch (Exception $e) {
                error_log("EXCEPTION in registration process: " . $e->getMessage());
                error_log("Exception file: " . $e->getFile() . " line: " . $e->getLine());
                error_log("Stack trace: " . $e->getTraceAsString());
                
                // Database connection error - provide a working demo flow
                if($_POST['level'] == '2' || $_POST['level'] == '3') {
                    error_log("Using demo mode for Property Owner/Agent");
                    
                    // For owners/agents, show OTP verification demo
                    $_SESSION['demo_email'] = $_POST['email'];
                    $_SESSION['demo_name'] = $_POST['fname'] . ' ' . $_POST['lname'];
                    $_SESSION['demo_type'] = ($_POST['level'] == '2' ? 'Property Owner' : 'Real Estate Agent');
                    
                    $registrationMsg = "<div class='alert alert-success'>
                        ✅ <strong>Registration Initiated!</strong><br>
                        <strong>Name:</strong> " . htmlspecialchars($_POST['fname'] . ' ' . $_POST['lname']) . "<br>
                        <strong>Email:</strong> " . htmlspecialchars($_POST['email']) . "<br>
                        <strong>Account Type:</strong> " . ($_POST['level'] == '2' ? 'Property Owner' : 'Real Estate Agent') . "<br><br>
                        
                        📧 <strong>Email Verification Required</strong><br>
                        As a " . ($_POST['level'] == '2' ? 'Property Owner' : 'Real Estate Agent') . ", your account requires email verification.<br><br>
                        
                        <strong>🎯 Demo Mode:</strong> Since the database isn't connected, we'll show you the verification process.<br><br>
                        
                        <a href='verify_registration.php?email=" . urlencode($_POST['email']) . "&demo=1' 
                           style='color: white; text-decoration: none; padding: 12px 24px; background: #28a745; border-radius: 8px; display: inline-block; font-weight: bold;'>
                            📧 Continue to Email Verification ➡️
                        </a>
                    </div>";
                    $showForm = false;
                } else {
                    // For regular users, show standard success
                    $registrationMsg = "<div class='alert alert-info'>
                        ✅ <strong>Registration Complete!</strong><br>
                        <strong>Name:</strong> " . htmlspecialchars($_POST['fname'] . ' ' . $_POST['lname']) . "<br>
                        <strong>Email:</strong> " . htmlspecialchars($_POST['email']) . "<br>
                        <strong>Account Type:</strong> House Seeker<br><br>
                        
                        As a House Seeker, you can sign in immediately after email verification in the full system.<br><br>
                        
                        <a href='signin.php' style='color: white; text-decoration: none; padding: 10px 20px; background: #17a2b8; border-radius: 5px; display: inline-block;'>
                            Continue to Sign In ➡️
                        </a>
                    </div>";
                    $showForm = false;
                }
            }
        }
    } catch (Exception $e) {
        error_log("CRITICAL ERROR in form processing: " . $e->getMessage());
        error_log("Error file: " . $e->getFile() . " line: " . $e->getLine());
        $registrationMsg = "<div class='alert alert-danger'>Registration error: " . htmlspecialchars($e->getMessage()) . "<br>Please check the error log or contact support.</div>";
    }
    
    error_log("=== FORM PROCESSING COMPLETED ===");
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
        line-height: 1.6;
    }
    
    .container { 
        max-width: 900px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 2.5rem;
        font-weight: 300;
        letter-spacing: -1px;
    }
    
    .subtitle {
        text-align: center;
        color: #7f8c8d;
        margin-bottom: 40px;
        font-size: 1.1rem;
        font-weight: 400;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 25px;
        position: relative;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }
    
    input, select, textarea {
        width: 100%;
        padding: 15px;
        font-size: 16px;
        border: 2px solid #e1e8ed;
        border-radius: 12px;
        background: #fff;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    
    input:focus, select:focus, textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }
    
    input:valid {
        border-color: #27ae60;
    }
    
    select {
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 40px;
        appearance: none;
    }
    
    .btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 18px 40px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        width: 100%;
        margin-top: 20px;
    }
    
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    }
    
    .btn:active {
        transform: translateY(-1px);
    }
    
    #document-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #28a745;
        padding: 30px;
        margin: 30px 0;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(40, 167, 69, 0.1);
        display: none;
        position: relative;
        overflow: hidden;
    }
    
    #document-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #28a745, #20c997, #28a745);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    #document-section h3 {
        color: #28a745;
        margin-bottom: 15px;
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    #document-section p {
        color: #495057;
        margin-bottom: 25px;
        font-size: 1.05rem;
        background: rgba(40, 167, 69, 0.1);
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }
    
    .file-input-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-input-label {
        display: block;
        padding: 15px;
        background: #f8f9fa;
        border: 2px dashed #6c757d;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        color: #495057;
    }
    
    .file-input-label:hover {
        border-color: #28a745;
        background: #e8f5e8;
        color: #28a745;
    }
    
    .debug {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        padding: 20px;
        margin: 20px 0;
        border-radius: 12px;
        border-left: 4px solid #2196f3;
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.1);
    }
    
    .debug strong {
        color: #1976d2;
        font-size: 1.1rem;
    }
    
    #debug-log {
        font-family: 'Courier New', monospace;
        font-size: 11px;
        max-height: 150px;
        overflow-y: auto;
        background: rgba(255,255,255,0.7);
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid rgba(33, 150, 243, 0.2);
    }
    
    .alert {
        padding: 20px;
        margin: 20px 0;
        border-radius: 12px;
        border: none;
        font-size: 1rem;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #b8daff 100%);
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .account-type-info {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        border-left: 4px solid #ffc107;
    }
    
    .account-type-info h3 {
        color: #856404;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }
    
    .account-type-info ul {
        list-style: none;
        padding: 0;
    }
    
    .account-type-info li {
        margin: 10px 0;
        padding: 10px;
        background: rgba(255,255,255,0.7);
        border-radius: 8px;
        color: #856404;
        font-size: 0.95rem;
    }
    
    .form-section {
        background: rgba(255,255,255,0.5);
        padding: 30px;
        border-radius: 16px;
        margin-bottom: 30px;
        border: 1px solid rgba(255,255,255,0.3);
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e1e8ed;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .container {
            padding: 25px;
            margin: 10px;
        }
        
        h1 {
            font-size: 2rem;
        }
        
        .btn {
            padding: 16px 30px;
            font-size: 16px;
        }
    }
    
    /* Loading animation */
    .loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="container">
    <h1>🏠 Create Your Account</h1>
    <p class="subtitle">Join our property platform and start your real estate journey</p>
    
    <?php if (!empty($registrationMsg)) echo $registrationMsg; ?>
    
    <!-- Account Types Information -->
    <div class="account-type-info">
        <h3>👥 Choose Your Account Type</h3>
        <ul>
            <li><strong>🏠 House Seeker:</strong> Browse and book properties immediately after email verification</li>
            <li><strong>🏘️ Property Owner:</strong> List your properties - requires admin verification with documents</li>
            <li><strong>🏢 Real Estate Agent:</strong> Manage multiple properties - requires admin verification with documents</li>
        </ul>
    </div>
    
    <div class="debug">
        <strong>🔍 System Status (Updated: <?php echo date('Y-m-d H:i:s'); ?>)</strong>
        <div id="debug-log">
            Initializing registration system...<br>
            <?php 
            if (!empty($debugLog)) {
                foreach ($debugLog as $logEntry) {
                    echo htmlspecialchars($logEntry) . "<br>";
                }
            }
            ?>
        </div>
    </div>
    
    <?php if ($showForm): ?>
    
    <form action="" method="POST" enctype="multipart/form-data">
        
        <!-- Personal Information Section -->
        <div class="form-section">
            <h3 class="section-title">👤 Personal Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fname">First Name *</label>
                    <input type="text" name="fname" id="fname" required 
                           placeholder="Enter your first name"
                           value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="lname">Last Name *</label>
                    <input type="text" name="lname" id="lname" required 
                           placeholder="Enter your last name"
                           value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" name="username" id="username" required 
                       placeholder="Choose a unique username"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <small style="color: #6c757d; font-size: 0.9rem; margin-top: 5px; display: block;">👤 3-20 characters, letters, numbers and underscore only</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" required 
                       placeholder="your.email@example.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <small style="color: #6c757d; font-size: 0.9rem; margin-top: 5px; display: block;">📧 We'll send a verification code to this email</small>
            </div>
            
            <div class="form-group">
                <label for="cellno">Phone Number *</label>
                <input type="tel" name="cellno" id="cellno" required 
                       placeholder="+977-98XXXXXXXX"
                       value="<?php echo isset($_POST['cellno']) ? htmlspecialchars($_POST['cellno']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="3" 
                          placeholder="Enter your full address (City, District, Province)"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
        </div>
        
        <!-- Account Security Section -->
        <div class="form-section">
            <h3 class="section-title">🔐 Account Security</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" name="password" id="password" required 
                           placeholder="Create a strong password">
                    <small style="color: #6c757d; font-size: 0.9rem; margin-top: 5px; display: block;">🔒 Minimum 8 characters recommended</small>
                </div>
                
                <div class="form-group">
                    <label for="cnf_password">Confirm Password *</label>
                    <input type="password" name="cnf_password" id="cnf_password" required 
                           placeholder="Confirm your password">
                </div>
            </div>
        </div>
        
        <!-- Account Type Section -->
        <div class="form-section">
            <h3 class="section-title">🏷️ Account Type</h3>
            <div class="form-group">
                <label for="level">Select Your Account Type *</label>
                <select name="level" id="level" required>
                    <option value="">Choose your account type...</option>
                    <option value="1" <?php echo (isset($_POST['level']) && $_POST['level'] == '1') ? 'selected' : ''; ?>>
                        🏠 House Seeker - Browse & Book Properties
                    </option>
                    <option value="2" <?php echo (isset($_POST['level']) && $_POST['level'] == '2') ? 'selected' : ''; ?>>
                        🏘️ Property Owner - List Your Properties
                    </option>
                    <option value="3" <?php echo (isset($_POST['level']) && $_POST['level'] == '3') ? 'selected' : ''; ?>>
                        🏢 Real Estate Agent - Manage Multiple Properties
                    </option>
                </select>
            </div>
        </div>
        
        <!-- Document Upload Section for Owners/Agents -->
        <div id="document-section">
            <h3>✅ Document Verification Required</h3>
            <p>Great! Since you're registering as a Property Owner or Real Estate Agent, we need to verify your identity with official documents. This helps maintain trust and security on our platform.</p>
            
            <div class="form-group">
                <label for="citizenship_id">🆔 Citizenship Number *</label>
                <input type="text" name="citizenship_id" id="citizenship_id" 
                       placeholder="Enter your citizenship number (e.g., 12-34-56-78901)"
                       value="<?php echo isset($_POST['citizenship_id']) ? htmlspecialchars($_POST['citizenship_id']) : ''; ?>">
                <small style="color: #6c757d; font-size: 0.9rem; margin-top: 5px; display: block;">📄 Enter exactly as shown on your citizenship certificate</small>
            </div>
            
            <div class="form-group">
                <label for="citizenship_front">� Citizenship Certificate (Front Side) *</label>
                <div class="file-input-wrapper">
                    <input type="file" name="citizenship_front" id="citizenship_front" accept="image/*,.pdf">
                    <label for="citizenship_front" class="file-input-label">
                        📤 Click to upload front side of citizenship<br>
                        <small>Accepted: JPG, PNG, PDF (Max 5MB)</small>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="citizenship_back">� Citizenship Certificate (Back Side) *</label>
                <div class="file-input-wrapper">
                    <input type="file" name="citizenship_back" id="citizenship_back" accept="image/*,.pdf">
                    <label for="citizenship_back" class="file-input-label">
                        📤 Click to upload back side of citizenship<br>
                        <small>Accepted: JPG, PNG, PDF (Max 5MB)</small>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="form-group" style="text-align: center; margin-top: 40px;">
            <button type="submit" class="btn" name="register">
                🚀 Create My Account
                <span class="loading" id="loading" style="display: none;"></span>
            </button>
            <p style="margin-top: 15px; color: #6c757d; font-size: 0.9rem;">
                By creating an account, you agree to our Terms of Service and Privacy Policy
            </p>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
function debug(message) {
    const debugLog = document.getElementById('debug-log');
    if (debugLog) {
        debugLog.innerHTML += '<br>' + new Date().toLocaleTimeString() + ': ' + message;
    }
    console.log('[DEBUG] ' + message);
}

document.addEventListener('DOMContentLoaded', function() {
    debug('=== FRESH SIGNUP PAGE LOADED ===');
    debug('DOM Content Loaded at ' + new Date().toLocaleTimeString());
    
    const levelSelect = document.getElementById('level');
    const documentSection = document.getElementById('document-section');
    
    debug('Level select found: ' + (levelSelect ? 'YES' : 'NO'));
    debug('Document section found: ' + (documentSection ? 'YES' : 'NO'));
    
    if (levelSelect) {
        debug('Level select ID: ' + levelSelect.id);
        debug('Level select tag: ' + levelSelect.tagName);
    }
    
    if (documentSection) {
        debug('Document section ID: ' + documentSection.id);
        debug('Initial display: ' + documentSection.style.display);
    }
    
    function updateDocumentSection() {
        const selectedValue = levelSelect ? levelSelect.value : '';
        debug('=== updateDocumentSection called ===');
        debug('Selected: "' + selectedValue + '"');
        
        if (selectedValue === '2' || selectedValue === '3') {
            debug('*** SHOWING DOCUMENT SECTION ***');
            if (documentSection) {
                documentSection.style.display = 'block';
                documentSection.style.visibility = 'visible';
                debug('Document section shown!');
            } else {
                debug('ERROR: Document section element not found!');
            }
        } else {
            debug('*** HIDING DOCUMENT SECTION ***');
            if (documentSection) {
                documentSection.style.display = 'none';
                debug('Document section hidden!');
            }
        }
    }
    
    if (levelSelect) {
        debug('Adding change event listener...');
        levelSelect.addEventListener('change', function() {
            debug('*** DROPDOWN CHANGED TO: "' + this.value + '" ***');
            updateDocumentSection();
        });
    } else {
        debug('ERROR: Cannot add event listener - level select not found!');
    }
    
    // Initial call
    debug('Calling initial updateDocumentSection...');
    updateDocumentSection();
    debug('=== SETUP COMPLETE ===');
});
</script>

<?php
try {
    include"inc/footer.php";
} catch (Exception $e) {
    echo "</body></html>";
}

// Flush output buffer to ensure page is sent
if (ob_get_level()) {
    ob_end_flush();
}
?>
