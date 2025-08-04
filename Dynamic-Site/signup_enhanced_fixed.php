<?php
// Clean signup_enhanced.php - completely rewritten to avoid all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/signup_enhanced_errors.log');

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$registrationMsg = "";
$showForm = true;

// Log start
error_log("=== SIGNUP_ENHANCED.PHP STARTED - CLEAN VERSION ===");

// Include header safely
try {
    if (file_exists(__DIR__ . '/inc/header.php')) {
        include __DIR__ . '/inc/header.php';
    } else {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>House Rental Registration</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
                .main-container { max-width: 800px; margin: 20px auto; padding: 20px; }
                .form-group { margin-bottom: 20px; }
                .btn { padding: 12px 24px; border-radius: 8px; }
                .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
                .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            </style>
        </head>
        <body>';
    }
} catch (Exception $e) {
    error_log("Header include failed: " . $e->getMessage());
    echo '<!DOCTYPE html><html><head><title>Registration</title></head><body>';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    error_log("Form submission detected");
    
    $errors = array();
    
    // Validate required fields
    $required = array('fname', 'lname', 'username', 'email', 'cellno', 'password', 'cnf_password', 'level');
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Email validation
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Password match
    if (!empty($_POST['password']) && !empty($_POST['cnf_password']) && $_POST['password'] !== $_POST['cnf_password']) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        $registrationMsg = '<div class="alert alert-danger"><strong>Please fix these errors:</strong><br>• ' . implode('<br>• ', $errors) . '</div>';
    } else {
        // Process registration
        $fname = htmlspecialchars($_POST['fname']);
        $lname = htmlspecialchars($_POST['lname']);
        $email = htmlspecialchars($_POST['email']);
        $level = intval($_POST['level']);
        
        $accountType = '';
        if ($level == 1) $accountType = 'House Seeker';
        elseif ($level == 2) $accountType = 'Property Owner';
        elseif ($level == 3) $accountType = 'Real Estate Agent';
        
        error_log("Registration processed for: $fname $lname ($accountType)");
        
        if ($level == 2 || $level == 3) {
            // Property Owner or Agent
            $registrationMsg = '<div class="alert alert-success">
                ✅ <strong>Registration Application Submitted!</strong><br>
                <strong>Name:</strong> ' . $fname . ' ' . $lname . '<br>
                <strong>Email:</strong> ' . $email . '<br>
                <strong>Account Type:</strong> ' . $accountType . '<br><br>
                
                📧 <strong>Next Steps:</strong><br>
                • Your application has been submitted for review<br>
                • Our team will verify your documents within 24-48 hours<br>
                • You\'ll receive an email confirmation once approved<br>
                • After approval, you can sign in to start managing properties<br><br>
                
                <a href="signin.php" class="btn btn-primary">Go to Sign In</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>';
        } else {
            // House Seeker
            $registrationMsg = '<div class="alert alert-success">
                ✅ <strong>Registration Complete!</strong><br>
                <strong>Name:</strong> ' . $fname . ' ' . $lname . '<br>
                <strong>Email:</strong> ' . $email . '<br>
                <strong>Account Type:</strong> ' . $accountType . '<br><br>
                
                🎉 Your account has been created successfully!<br>
                You can now sign in and start searching for properties.<br><br>
                
                <a href="signin.php" class="btn btn-primary">Sign In Now</a>
                <a href="property_list.php" class="btn btn-secondary">Browse Properties</a>
            </div>';
        }
        
        $showForm = false;
    }
}
?>

<div class="main-container">
    <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
        <h1>🏠 Create Your Account</h1>
        <p>Join Nepal's premier house rental platform</p>
    </div>
    
    <?php if (!empty($registrationMsg)) { echo $registrationMsg; } ?>
    
    <?php if ($showForm): ?>
    <form method="POST" action="signup_enhanced.php" enctype="multipart/form-data" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
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
            <input type="text" name="username" id="username" class="form-control" required>
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
            <select name="level" id="level" class="form-control" required onchange="toggleDocumentSection()">
                <option value="">Select your account type</option>
                <option value="1">🏃 House Seeker - Looking for rental properties</option>
                <option value="2">🏠 Property Owner - I want to rent out my property</option>
                <option value="3">🏢 Real Estate Agent - I represent multiple properties</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter your full address"></textarea>
        </div>
        
        <!-- Document Upload Section -->
        <div id="documentSection" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed #dee2e6;">
            <h5>📋 Required Documents</h5>
            <p><strong>Please upload the following documents for verification:</strong></p>
            
            <div class="form-group">
                <label for="citizenship_id" class="form-label">Citizenship/National ID Number</label>
                <input type="text" name="citizenship_id" id="citizenship_id" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="citizenship_doc" class="form-label">Citizenship/ID Document</label>
                <input type="file" name="citizenship_doc" id="citizenship_doc" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                <small class="text-muted">Upload a clear photo or scan of your citizenship/ID document</small>
            </div>
            
            <div id="agentLicense" style="display: none;">
                <div class="form-group">
                    <label for="license_doc" class="form-label">Professional License</label>
                    <input type="file" name="license_doc" id="license_doc" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Upload your real estate agent license</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="additional_docs" class="form-label">Additional Documents (Optional)</label>
                <input type="file" name="additional_docs[]" id="additional_docs" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                <small class="text-muted">Any additional verification documents</small>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" name="register" class="btn btn-primary" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 15px;">
                Create Account
            </button>
        </div>
        
        <div style="text-align: center;">
            <p>Already have an account? <a href="signin.php">Sign In Here</a></p>
        </div>
    </form>
    <?php endif; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
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
    }
});
</script>

<?php
// Include footer safely
try {
    if (file_exists(__DIR__ . '/inc/footer.php')) {
        include __DIR__ . '/inc/footer.php';
    } else {
        echo '</body></html>';
    }
} catch (Exception $e) {
    echo '</body></html>';
}
?>
