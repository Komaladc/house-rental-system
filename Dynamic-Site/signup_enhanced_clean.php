<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/signup_enhanced_errors.log');

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$registrationMsg = "";
$showForm = true;

try {
    // Log script start
    error_log("=== SIGNUP_ENHANCED.PHP STARTED ===");

    // Include header with error handling
    if (file_exists('inc/header.php')) {
        include 'inc/header.php';
        error_log("Header included successfully");
    } else {
        // Fallback HTML structure
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>House Rental Registration</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="mystyle.css" rel="stylesheet">
        </head>
        <body>';
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        error_log("=== FORM SUBMISSION DETECTED ===");
        error_log("POST data: " . print_r($_POST, true));
        
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
            error_log("Validation passed - processing registration");
            
            // Get user info
            $fname = htmlspecialchars($_POST['fname']);
            $lname = htmlspecialchars($_POST['lname']);
            $email = htmlspecialchars($_POST['email']);
            $username = htmlspecialchars($_POST['username']);
            $phone = htmlspecialchars($_POST['cellno']);
            $level = intval($_POST['level']);
            
            // Determine account type
            $accountType = '';
            if ($level == 1) {
                $accountType = 'House Seeker';
            } elseif ($level == 2) {
                $accountType = 'Property Owner';
            } elseif ($level == 3) {
                $accountType = 'Real Estate Agent';
            }
            
            error_log("Processing registration for: $fname $lname ($accountType)");
            
            // Store demo data in session for all types
            $_SESSION['demo_registration'] = array(
                'name' => $fname . ' ' . $lname,
                'email' => $email,
                'username' => $username,
                'phone' => $phone,
                'type' => $accountType,
                'level' => $level,
                'timestamp' => time()
            );
            
            // Show appropriate success message based on account type
            if ($level == 2 || $level == 3) {
                // Property Owner or Agent
                $registrationMsg = "<div class='alert alert-success'>
                    ✅ <strong>Registration Application Submitted!</strong><br>
                    <strong>Name:</strong> $fname $lname<br>
                    <strong>Email:</strong> $email<br>
                    <strong>Account Type:</strong> $accountType<br><br>
                    
                    📧 <strong>Next Steps:</strong><br>
                    • Your application has been submitted for review<br>
                    • Our team will verify your documents within 24-48 hours<br>
                    • You'll receive an email confirmation once approved<br>
                    • After approval, you can sign in to start managing properties<br><br>
                    
                    <div class='mt-3'>
                        <a href='signin.php' class='btn btn-primary me-2'>Go to Sign In</a>
                        <a href='index.php' class='btn btn-secondary'>Back to Home</a>
                    </div>
                </div>";
            } else {
                // House Seeker
                $registrationMsg = "<div class='alert alert-success'>
                    ✅ <strong>Registration Complete!</strong><br>
                    <strong>Name:</strong> $fname $lname<br>
                    <strong>Email:</strong> $email<br>
                    <strong>Account Type:</strong> $accountType<br><br>
                    
                    🎉 Your account has been created successfully!<br>
                    You can now sign in and start searching for properties.<br><br>
                    
                    <div class='mt-3'>
                        <a href='signin.php' class='btn btn-primary me-2'>Sign In Now</a>
                        <a href='property_list.php' class='btn btn-secondary'>Browse Properties</a>
                    </div>
                </div>";
            }
            
            $showForm = false;
            error_log("Registration processed successfully for $accountType");
        }
    }

} catch (Exception $e) {
    error_log("Critical error in signup_enhanced.php: " . $e->getMessage());
    $registrationMsg = "<div class='alert alert-danger'>
        ❌ <strong>System Error</strong><br>
        Sorry, there was a technical issue. Please try again later.<br>
        If the problem persists, please contact support.
    </div>";
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
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .registration-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        text-align: center;
    }
    
    .registration-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        font-weight: 300;
    }
    
    .registration-header p {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .form-container {
        padding: 40px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 16px;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }
    
    .form-select {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 16px;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 15px 30px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        cursor: pointer;
        width: 100%;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .document-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-top: 20px;
        border: 2px dashed #dee2e6;
    }
    
    .document-section h5 {
        color: #495057;
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .alert {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        border: none;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 5px solid #28a745;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 5px solid #dc3545;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }
    
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
    }
    
    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .container {
            margin: 10px;
            border-radius: 10px;
        }
        
        .registration-header {
            padding: 20px;
        }
        
        .registration-header h1 {
            font-size: 1.8rem;
        }
        
        .form-container {
            padding: 20px;
        }
    }
</style>

<div class="container">
    <div class="registration-header">
        <h1>🏠 Create Your Account</h1>
        <p>Join Nepal's premier house rental platform</p>
    </div>
    
    <div class="form-container">
        <?php if (!empty($registrationMsg)) { echo $registrationMsg; } ?>
        
        <?php if ($showForm): ?>
        <form method="POST" action="signup_enhanced.php" enctype="multipart/form-data" id="registrationForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fname" class="form-label">First Name *</label>
                        <input type="text" name="fname" id="fname" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lname" class="form-label">Last Name *</label>
                        <input type="text" name="lname" id="lname" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username" class="form-label">Username *</label>
                <input type="text" name="username" id="username" class="form-control" required 
                       pattern="^[a-zA-Z0-9_]{3,20}$" 
                       title="3-20 characters, letters, numbers and underscore only">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="cellno" class="form-label">Phone Number *</label>
                <input type="tel" name="cellno" id="cellno" class="form-control" required>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cnf_password" class="form-label">Confirm Password *</label>
                        <input type="password" name="cnf_password" id="cnf_password" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="level" class="form-label">Account Type *</label>
                <select name="level" id="level" class="form-select" required onchange="toggleDocumentSection()">
                    <option value="">Select your account type</option>
                    <option value="1">🏃 House Seeker - Looking for rental properties</option>
                    <option value="2">🏠 Property Owner - I want to rent out my property</option>
                    <option value="3">🏢 Real Estate Agent - I represent multiple properties</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" class="form-control" rows="3" 
                          placeholder="Enter your full address"></textarea>
            </div>
            
            <!-- Document Upload Section -->
            <div id="documentSection" class="document-section" style="display: none;">
                <h5>📋 Required Documents</h5>
                <p><strong>Please upload the following documents for verification:</strong></p>
                
                <div class="form-group">
                    <label for="citizenship_id" class="form-label">Citizenship/National ID Number</label>
                    <input type="text" name="citizenship_id" id="citizenship_id" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="citizenship_doc" class="form-label">Citizenship/ID Document</label>
                    <input type="file" name="citizenship_doc" id="citizenship_doc" class="form-control" 
                           accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Upload a clear photo or scan of your citizenship/ID document</small>
                </div>
                
                <div id="agentLicense" style="display: none;">
                    <div class="form-group">
                        <label for="license_doc" class="form-label">Professional License</label>
                        <input type="file" name="license_doc" id="license_doc" class="form-control" 
                               accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Upload your real estate agent license</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="additional_docs" class="form-label">Additional Documents (Optional)</label>
                    <input type="file" name="additional_docs[]" id="additional_docs" class="form-control" 
                           accept=".jpg,.jpeg,.png,.pdf" multiple>
                    <small class="text-muted">Any additional verification documents</small>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="register" class="btn btn-primary">
                    Create Account
                </button>
            </div>
            
            <div class="text-center">
                <p>Already have an account? <a href="signin.php">Sign In Here</a></p>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDocumentSection() {
    const level = document.getElementById('level').value;
    const documentSection = document.getElementById('documentSection');
    const agentLicense = document.getElementById('agentLicense');
    
    if (level == '2' || level == '3') {
        documentSection.style.display = 'block';
        
        if (level == '3') {
            agentLicense.style.display = 'block';
        } else {
            agentLicense.style.display = 'none';
        }
    } else {
        documentSection.style.display = 'none';
        agentLicense.style.display = 'none';
    }
}

// Form validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('cnf_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>

<?php
try {
    if (file_exists('inc/footer.php')) {
        include 'inc/footer.php';
    } else {
        echo '</body></html>';
    }
} catch (Exception $e) {
    echo '</body></html>';
}
?>
