<?php
/**
 * Enhanced Email Verification System
 * Requires email verification BEFORE account creation
 * Checks for real email addresses
 */

// Include timezone configuration for Nepal time
include_once dirname(__DIR__) . '/config/timezone.php';
include_once dirname(__DIR__) . '/helpers/NepalTime.php';

class PreRegistrationVerification {
    private $db;
    private $emailOTP;
    
    public function __construct($existingDb = null) {
        // Use existing database connection if provided, otherwise create new one
        if ($existingDb && is_object($existingDb)) {
            $this->db = $existingDb;
        } else {
            // Assume Database class is already included by caller
            $this->db = new Database();
        }
        
        if (!class_exists('EmailOTP')) {
            include_once dirname(__DIR__) . '/classes/EmailOTP.php';
        }
        $this->emailOTP = new EmailOTP($this->db);
    }
    
    /**
     * Validate if email is real and deliverable
     */
    public function isRealEmail($email) {
        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);
        
        // Check for valid domain structure
        if (!$domain || !strpos($domain, '.')) {
            return false;
        }
        
        // Reject common fake/temporary email domains
        $fakeDomains = [
            '10minutemail.com', 'temp-mail.org', 'guerrillamail.com',
            'mailinator.com', 'throwaway.email', 'tempmail.com',
            'test.com', 'example.com', 'fake.com', 'invalid.com',
            'dummy.com', 'trash-mail.com', 'yopmail.com',
            'guerrillamailblock.com', 'sharklasers.com'
        ];
        
        if (in_array(strtolower($domain), $fakeDomains)) {
            return false;
        }
        
        // Check if domain has MX record (indicates real email domain)
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return false;
        }
        
        // Additional domain validation
        $domainParts = explode('.', $domain);
        if (count($domainParts) < 2) {
            return false;
        }
        
        // Check TLD length (top-level domain)
        $tld = end($domainParts);
        if (strlen($tld) < 2 || strlen($tld) > 6) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Send verification email and store pending registration data
     */
    public function initiateEmailVerification($registrationData) {
        $email = $registrationData['email'];
        
        // Validate email is real
        if (!$this->isRealEmail($email)) {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ Please enter a valid, real email address.<br>
                    Temporary or fake email addresses are not allowed.
                </div>"
            ];
        }
        
        // Check if email is already registered
        $checkExisting = "SELECT * FROM tbl_user WHERE userEmail = '" . mysqli_real_escape_string($this->db->link, $email) . "'";
        $existing = $this->db->select($checkExisting);
        if ($existing && $existing->num_rows > 0) {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ This email address is already registered.<br>
                    <a href='signin.php'>Click here to sign in</a> or 
                    <a href='forgot_password_otp.php'>reset your password</a>
                </div>"
            ];
        }
        
        // Generate secure verification token
        $verificationToken = bin2hex(random_bytes(32));
        $otp = $this->emailOTP->generateOTP();
        
        // Generate username from email if not provided
        $username = isset($registrationData['username']) ? $registrationData['username'] : 
                   explode('@', $email)[0];
        
        // Store pending registration data
        $pendingData = json_encode([
            'fname' => $registrationData['fname'],
            'lname' => $registrationData['lname'],
            'username' => $username,
            'email' => $email,
            'cellno' => $registrationData['cellno'],
            'address' => $registrationData['address'],
            'password' => md5($registrationData['password']),
            'level' => $registrationData['level'],
            'requires_verification' => isset($registrationData['requires_verification']) ? $registrationData['requires_verification'] : false,
            'uploaded_files' => isset($registrationData['uploaded_files']) ? $registrationData['uploaded_files'] : null,
            'timestamp' => NepalTime::timestamp()
        ]);
        
        // Delete any existing pending verification for this email
        $deleteExisting = "DELETE FROM tbl_pending_verification WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "'";
        $this->db->delete($deleteExisting);
        
        // Store pending verification (extended to 2 hours for reliability) - Using Nepal time
        $expiresAt = NepalTime::addHours(2);
        $createdAt = NepalTime::now();
        
        $insertPending = "INSERT INTO tbl_pending_verification (email, verification_token, otp, registration_data, expires_at, created_at) 
                         VALUES ('" . mysqli_real_escape_string($this->db->link, $email) . "', 
                                '$verificationToken', 
                                '$otp', 
                                '" . mysqli_real_escape_string($this->db->link, $pendingData) . "', 
                                '$expiresAt', 
                                '$createdAt')";
        
        if ($this->db->insert($insertPending)) {
            // Delete any existing OTP first to prevent conflicts
            $deleteOldOTP = "DELETE FROM tbl_otp WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' AND purpose = 'registration'";
            $this->db->delete($deleteOldOTP);
            
            // Also store OTP in tbl_otp for verification (20 minutes to match EmailOTP class) - Using Nepal time
            $otpExpiresAt = NepalTime::addMinutes(20);
            $insertOTP = "INSERT INTO tbl_otp (email, otp, purpose, created_at, expires_at, is_used) 
                         VALUES ('" . mysqli_real_escape_string($this->db->link, $email) . "', 
                                '$otp', 
                                'registration', 
                                '$createdAt', 
                                '$otpExpiresAt',
                                0)";
            
            // Debug logging
            error_log("Creating pending verification - Email: $email, Created: $createdAt, Pending Expires: $expiresAt, OTP Expires: $otpExpiresAt");
            error_log("OTP Insert Query: $insertOTP");
            
            $otpInsertResult = $this->db->insert($insertOTP);
            error_log("OTP Insert Result: " . ($otpInsertResult ? 'SUCCESS' : 'FAILED'));
            
            if (!$otpInsertResult) {
                error_log("CRITICAL: Failed to insert OTP into tbl_otp for email: $email");
                error_log("MySQL Error: " . mysqli_error($this->db->link));
                error_log("MySQL Error Number: " . mysqli_errno($this->db->link));
                
                // Try to identify common issues
                if (mysqli_errno($this->db->link) == 1146) {
                    error_log("ERROR 1146: Table 'tbl_otp' doesn't exist!");
                } else if (mysqli_errno($this->db->link) == 1054) {
                    error_log("ERROR 1054: Unknown column in 'field list'!");
                } else if (mysqli_errno($this->db->link) == 1062) {
                    error_log("ERROR 1062: Duplicate entry for key!");
                }
            }
            
            // Send verification email
            if ($this->emailOTP->sendVerificationEmail($email, $otp, $verificationToken)) {
                return [
                    'success' => true,
                    'message' => "<div class='alert alert_success'>
                        📧 Verification email sent!<br>
                        Please check your email and click the verification link or enter the OTP code.<br>
                        <strong>Important:</strong> Check your spam folder if you don't see the email.
                    </div>",
                    'token' => $verificationToken
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "<div class='alert alert_danger'>
                        ❌ Failed to send verification email. Please try again.
                    </div>"
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ System error. Please try again later.
                </div>"
            ];
        }
    }
    
    /**
     * Verify email and create account
     */
    public function verifyAndCreateAccount($email, $token, $otp = null) {
        // If we have an OTP but no token (OTP-only verification), use simplified flow
        if ($otp && !$token) {
            return $this->verifyOTPAndCreateAccount($email, $otp);
        }
        
        // Get pending verification data - Using Nepal time
        $currentTime = NepalTime::now();
        $query = "SELECT * FROM tbl_pending_verification 
                 WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                 AND verification_token = '" . mysqli_real_escape_string($this->db->link, $token) . "'
                 AND expires_at > '$currentTime'
                 AND is_verified = 0";
        
        $result = $this->db->select($query);
        
        if (!$result || $result->num_rows == 0) {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ Invalid or expired verification link.<br>
                    Please request a new verification email.
                </div>"
            ];
        }
        
        $pendingData = $result->fetch_assoc();
        
        // If OTP is provided, verify it from the tbl_otp table
        if ($otp) {
            $otpVerified = $this->emailOTP->verifyOTP($email, $otp, 'registration');
            if (!$otpVerified) {
                return [
                    'success' => false,
                    'message' => "<div class='alert alert_danger'>
                        ❌ Invalid or expired verification code. Please check and try again.
                    </div>"
                ];
            }
        }
        
        // Decode registration data
        $registrationData = json_decode($pendingData['registration_data'], true);
        
        // Continue with account creation...
        return $this->createUserAccount($registrationData, $email, $token);
    }
    
    /**
     * Verify OTP only and create account (Enhanced with better error handling)
     */
    public function verifyOTPAndCreateAccount($email, $otp) {
        // Debug logging
        error_log("verifyOTPAndCreateAccount called - Email: $email, OTP: $otp");
        
        // Get current time for all comparisons
        $currentTime = NepalTime::now();
        
        // First, let's check if we have any valid pending verification record
        $pendingQuery = "SELECT * FROM tbl_pending_verification 
                        WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                        AND expires_at > '$currentTime'
                        AND is_verified = 0
                        ORDER BY created_at DESC LIMIT 1";
        
        error_log("Checking pending verification: $pendingQuery");
        $pendingResult = $this->db->select($pendingQuery);
        
        if (!$pendingResult || $pendingResult->num_rows == 0) {
            error_log("No pending verification found for email: $email");
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ No pending registration found for this email or verification has expired.<br>
                    Please start the registration process again.
                </div>"
            ];
        }
        
        $pendingData = $pendingResult->fetch_assoc();
        error_log("Found pending verification - Token: " . substr($pendingData['verification_token'], 0, 10) . "...");
        
        // Now check the OTP - we'll be more flexible here
        $otpQuery = "SELECT * FROM tbl_otp 
                    WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                    AND otp = '" . mysqli_real_escape_string($this->db->link, $otp) . "' 
                    AND purpose = 'registration' 
                    AND expires_at > '$currentTime'
                    ORDER BY created_at DESC LIMIT 1";
        
        error_log("Checking OTP: $otpQuery");
        $otpResult = $this->db->select($otpQuery);
        
        if (!$otpResult || $otpResult->num_rows == 0) {
            // Check if OTP exists but is expired or used
            $expiredOtpQuery = "SELECT * FROM tbl_otp 
                               WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                               AND otp = '" . mysqli_real_escape_string($this->db->link, $otp) . "' 
                               AND purpose = 'registration' 
                               ORDER BY created_at DESC LIMIT 1";
            
            $expiredOtpResult = $this->db->select($expiredOtpQuery);
            
            if ($expiredOtpResult && $expiredOtpResult->num_rows > 0) {
                $expiredOtp = $expiredOtpResult->fetch_assoc();
                if ($expiredOtp['is_used'] == 1) {
                    error_log("OTP already used for email: $email");
                    return [
                        'success' => false,
                        'message' => "<div class='alert alert_danger'>
                            ❌ This verification code has already been used.<br>
                            Please request a new verification code.
                        </div>"
                    ];
                } else if (strtotime($expiredOtp['expires_at']) <= strtotime($currentTime)) {
                    error_log("OTP expired for email: $email (expired: {$expiredOtp['expires_at']}, current: $currentTime)");
                    return [
                        'success' => false,
                        'message' => "<div class='alert alert_danger'>
                            ❌ Verification code has expired.<br>
                            Please request a new verification code.
                        </div>"
                    ];
                }
            }
            
            error_log("OTP verification failed - invalid OTP for email: $email");
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ Invalid verification code. Please check and try again.
                </div>"
            ];
        }
        
        // OTP is valid, mark it as used
        $markUsedQuery = "UPDATE tbl_otp SET is_used = 1 WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' AND otp = '" . mysqli_real_escape_string($this->db->link, $otp) . "' AND purpose = 'registration'";
        $this->db->update($markUsedQuery);
        error_log("Marked OTP as used for email: $email");
        
        // Get registration data
        $registrationData = json_decode($pendingData['registration_data'], true);
        
        // Create user account
        return $this->createUserAccount($registrationData, $email, $pendingData['verification_token']);
    }

    /**
     * Create user account from registration data
     */
    private function createUserAccount($registrationData, $email, $token) {
        
        // Determine verification status based on user level
        // Level 2 (Property Owners) and Level 3 (Real Estate Agents) require admin verification after email verification
        // Level 1 (regular users) are active immediately after email verification
        $userLevel = $registrationData['level'];
        $requiresVerification = ($userLevel == 2 || $userLevel == 3); // Both owners and agents need admin verification
        $verificationStatus = $requiresVerification ? 'pending' : 'verified';
        $accountStatus = $requiresVerification ? 0 : 1; // 0 = inactive (pending admin approval), 1 = active
        
        // Generate username from email if not provided
        $username = isset($registrationData['username']) ? $registrationData['username'] : 
                   explode('@', $registrationData['email'])[0];
        
        // Hash the password if not already hashed
        $hashedPassword = (strlen($registrationData['password']) == 32) ? 
                         $registrationData['password'] : 
                         md5($registrationData['password']);
        
        // Create user account - match the actual database structure
        $userQuery = "INSERT INTO tbl_user(firstName, lastName, userName, userImg, userEmail, cellNo, phoneNo, userAddress, userPass, confPass, userLevel, userStatus) 
                     VALUES('" . mysqli_real_escape_string($this->db->link, $registrationData['fname']) . "',
                            '" . mysqli_real_escape_string($this->db->link, $registrationData['lname']) . "',
                            '" . mysqli_real_escape_string($this->db->link, $username) . "',
                            '',
                            '" . mysqli_real_escape_string($this->db->link, $registrationData['email']) . "',
                            '" . mysqli_real_escape_string($this->db->link, $registrationData['cellno']) . "',
                            '',
                            '" . mysqli_real_escape_string($this->db->link, $registrationData['address'] ?? '') . "',
                            '$hashedPassword',
                            '$hashedPassword',
                            '" . mysqli_real_escape_string($this->db->link, $registrationData['level']) . "',
                            $accountStatus)";
        
        error_log("Creating user account with query: $userQuery");
        
        $userId = $this->db->insert($userQuery);
        if ($userId) {
            // If user requires verification (agent/owner), always store them in verification table
            if ($requiresVerification) {
                $this->storeUserDocuments($userId, $registrationData);
            }
            
            // Mark verification as completed
            $updateVerification = "UPDATE tbl_pending_verification 
                                  SET is_verified = 1 
                                  WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                                  AND verification_token = '$token'";
            $this->db->update($updateVerification);
            
            // Clean up expired pending verifications
            $this->cleanupExpiredVerifications();
            
            $successMessage = $requiresVerification ? 
                "<div class='alert alert_success'>
                    🎉 Email verified successfully! Your account has been created.<br>
                    📋 <strong>Admin Verification Required:</strong> As an agent/owner, your account is pending admin approval.<br>
                    📧 You'll receive an email notification once an admin reviews and approves your account.<br>
                    ⏳ This usually takes 1-2 business days.<br>
                    <br>
                    <strong>Next Steps:</strong><br>
                    • Wait for admin approval<br>
                    • Check your email for approval notification<br>
                    • Once approved, you can sign in normally
                </div>" :
                "<div class='alert alert_success'>
                    🎉 Email verified successfully! Your account has been created and is ready to use.<br>
                    ✅ You can now sign in to your account immediately.<br>
                    🏠 Start browsing and booking properties right away!
                </div>";
            
            return [
                'success' => true,
                'message' => $successMessage,
                'user_data' => $registrationData,
                'requires_verification' => $requiresVerification
            ];
        } else {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ Failed to create account. Please try again.
                </div>"
            ];
        }
    }
    
    /**
     * Cleanup expired pending verifications
     */
    public function cleanupExpiredVerifications() {
        $deleteExpired = "DELETE FROM tbl_pending_verification WHERE expires_at < NOW() OR created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $this->db->delete($deleteExpired);
    }
    
    /**
     * Resend verification email
     */
    public function resendVerification($email) {
        // Check if there's a pending verification
        $query = "SELECT * FROM tbl_pending_verification 
                 WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                 AND is_verified = 0
                 ORDER BY created_at DESC LIMIT 1";
        
        $result = $this->db->select($query);
        
        if (!$result || $result->num_rows == 0) {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ No pending verification found for this email.<br>
                    Please start the registration process again.
                </div>"
            ];
        }
        
        $pendingData = $result->fetch_assoc();
        
        // Generate new OTP
        $newOTP = $this->emailOTP->generateOTP();
        
        // Update OTP and extend expiry
        $updateQuery = "UPDATE tbl_pending_verification 
                       SET otp = '$newOTP', 
                           expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                       WHERE email = '" . mysqli_real_escape_string($this->db->link, $email) . "' 
                       AND verification_token = '" . $pendingData['verification_token'] . "'";
        
        if ($this->db->update($updateQuery)) {
            // Send new verification email
            if ($this->emailOTP->sendVerificationEmail($email, $newOTP, $pendingData['verification_token'])) {
                return [
                    'success' => true,
                    'message' => "<div class='alert alert_success'>
                        📧 New verification email sent!<br>
                        Please check your email for the new verification code.
                    </div>"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "<div class='alert alert_danger'>
                        ❌ Failed to send verification email. Please try again.
                    </div>"
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "<div class='alert alert_danger'>
                    ❌ System error. Please try again later.
                </div>"
            ];
        }
    }
    
    /**
     * Store user documents in verification table
     */
    private function storeUserDocuments($userId, $registrationData) {
        $uploadedFiles = isset($registrationData['uploaded_files']) ? $registrationData['uploaded_files'] : [];
        $email = $registrationData['email'];
        $userType = ($registrationData['level'] == 2) ? 'owner' : (($registrationData['level'] == 3) ? 'agent' : 'user');
        $citizenshipId = isset($registrationData['citizenship_id']) ? $registrationData['citizenship_id'] : '';
        
        // Generate username from email if not provided
        $username = isset($registrationData['username']) ? $registrationData['username'] : 
                   explode('@', $registrationData['email'])[0];
        
        $citizenshipFront = isset($uploadedFiles['citizenship_front']) ? $uploadedFiles['citizenship_front'] : null;
        $citizenshipBack = isset($uploadedFiles['citizenship_back']) ? $uploadedFiles['citizenship_back'] : null;
        $businessLicense = isset($uploadedFiles['business_license']) ? $uploadedFiles['business_license'] : null;
        
        $insertQuery = "INSERT INTO tbl_user_verification (user_id, email, userName, user_level, user_type, citizenship_id, citizenship_front, citizenship_back, business_license, verification_status, submitted_at) 
                       VALUES ($userId, 
                              '" . mysqli_real_escape_string($this->db->link, $email) . "',
                              '" . mysqli_real_escape_string($this->db->link, $username) . "',
                              " . $registrationData['level'] . ",
                              '$userType', 
                              '" . mysqli_real_escape_string($this->db->link, $citizenshipId) . "',
                              " . ($citizenshipFront ? "'" . mysqli_real_escape_string($this->db->link, $citizenshipFront) . "'" : "NULL") . ", 
                              " . ($citizenshipBack ? "'" . mysqli_real_escape_string($this->db->link, $citizenshipBack) . "'" : "NULL") . ", 
                              " . ($businessLicense ? "'" . mysqli_real_escape_string($this->db->link, $businessLicense) . "'" : "NULL") . ", 
                              'pending', 
                              NOW())";
        
        // Debug logging
        error_log("Storing user documents - User ID: $userId, Email: $email, Username: $username, Type: $userType, Citizenship ID: $citizenshipId");
        error_log("Insert Query: $insertQuery");
        
        $result = $this->db->insert($insertQuery);
        error_log("Insert Result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if (!$result) {
            error_log("MySQL Error: " . mysqli_error($this->db->link));
        }
        
        return $result;
    }
}
?>
