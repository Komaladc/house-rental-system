<?php
/**
 * Real-time Email Validation API
 * For Nepal House Rental System
 */

// Prevent direct access
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    http_response_code(403);
    exit('Direct access not allowed');
}

require_once 'config/config.php';
require_once 'lib/Database.php';
require_once 'classes/PreRegistrationVerification.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$db = new Database();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'validate_email':
            validateEmailAPI();
            break;
        case 'check_email_exists':
            checkEmailExistsAPI();
            break;
        case 'send_otp':
            sendOTPAPI();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
}

/**
 * Real-time email validation
 */
function validateEmailAPI() {
    global $db;
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required',
            'valid' => false
        ]);
        return;
    }
    
    // Basic email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format',
            'valid' => false,
            'email_exists' => false
        ]);
        return;
    }
    
    // Extract domain for validation
    $domain = explode('@', $email)[1];
    
    // Check against temporary/fake email domains
    $tempDomains = [
        '10minutemail.com', 'guerrillamail.com', 'mailinator.com', 'tempmail.org',
        'yopmail.com', 'throwaway.email', 'getnada.com', 'temp-mail.org'
    ];
    
    $isTemp = in_array(strtolower($domain), $tempDomains);
    
    // Check if email already exists in database
    $existsQuery = "SELECT userEmail FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $email) . "' LIMIT 1";
    $existsResult = $db->select($existsQuery);
    $emailExists = $existsResult && mysqli_num_rows($existsResult) > 0;
    
    if ($emailExists) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is already registered',
            'valid' => false,
            'email_exists' => true
        ]);
        return;
    }
    
    if ($isTemp) {
        echo json_encode([
            'success' => false,
            'message' => 'Temporary email addresses are not allowed',
            'valid' => false,
            'email_exists' => false
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Email address is valid',
        'valid' => true,
        'email_exists' => false,
        'email' => $email
    ]);
}

/**
 * Check if email exists in database
 */
function checkEmailExistsAPI() {
    global $db;
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required',
            'exists' => false
        ]);
        return;
    }
    
    // Check in main user table
    $existsQuery = "SELECT email FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($db->link, $email) . "' LIMIT 1";
    $existsResult = $db->select($existsQuery);
    $emailExists = $existsResult && mysqli_num_rows($existsResult) > 0;
    
    // Also check in pending verifications
    $pendingQuery = "SELECT email FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($db->link, $email) . "' AND is_verified = 0 LIMIT 1";
    $pendingResult = $db->select($pendingQuery);
    $pendingExists = $pendingResult && mysqli_num_rows($pendingResult) > 0;
    
    echo json_encode([
        'success' => true,
        'exists' => $emailExists,
        'pending' => $pendingExists,
        'message' => $emailExists ? 'Email already registered' : 'Email available'
    ]);
}

/**
 * Send OTP to email
 */
function sendOTPAPI() {
    global $db;
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required'
        ]);
        return;
    }
    
    try {
        include_once 'classes/EmailOTP.php';
        $emailOTP = new EmailOTP();
        
        // Generate OTP (no parameters needed)
        $otp = $emailOTP->generateOTP();
        
        // Store OTP in database with email
        $storeQuery = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at) 
                      VALUES ('" . mysqli_real_escape_string($db->link, $email) . "', 
                              '$otp', 
                              'email_verification', 
                              NOW(), 
                              DATE_ADD(NOW(), INTERVAL 15 MINUTE))";
        
        if ($db->insert($storeQuery)) {
            // Send OTP via email
            $sendResult = $emailOTP->sendOTP($email, $otp, 'email_verification');
            
            echo json_encode([
                'success' => $sendResult,
                'message' => $sendResult ? 'OTP sent successfully' : 'Failed to send OTP',
                'otp_sent' => $sendResult
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to store OTP'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP: ' . $e->getMessage()
        ]);
    }
}
?>
