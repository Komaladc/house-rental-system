<?php
$filepath = realpath(dirname(__FILE__));
include_once ($filepath.'/../lib/Database.php');
include_once ($filepath.'/../helpers/Format.php');
include_once ($filepath.'/EmailOTP.php');
?>

<?php
class User{
	private $db;
	private $fm;
	private $emailOTP;
	
	public function __construct(){
		$this->db = new Database();
		$this->fm = new Format();
		$this->emailOTP = new EmailOTP();
	}
	

/* User Sign Up Process*/
	
	public function UserRegistration($data){
		$FName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['fname']));
		
		$LName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['lname']));
		
		$UserName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['username']));
		
		$EMail = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['email']));
		
		$Cellno = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['cellno']));
		
		$Address = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['address']));
		
		$Password = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['password']));
		
		$CnfPassword = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['cnf_password']));
		
		$Level = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['level']));
		
		// Enhanced validation
		if(empty($FName) || empty($LName) || empty($UserName) || empty($EMail) || empty($Cellno) || empty($Address) || empty($Password) || empty($CnfPassword) || empty($Level)){
			$msg = "<div class='alert alert_danger'>Error! All fields are required</div>";
			return $msg;
		}
		
		// Name validation
		if(!preg_match('/^[a-zA-Z\s]+$/', $FName) || strlen($FName) < 2){
			$msg = "<div class='alert alert_danger'>Error! First name should only contain letters and be at least 2 characters</div>";
			return $msg;
		}
		
		if(!preg_match('/^[a-zA-Z\s]+$/', $LName) || strlen($LName) < 2){
			$msg = "<div class='alert alert_danger'>Error! Last name should only contain letters and be at least 2 characters</div>";
			return $msg;
		}
		
		// Username validation
		if(!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $UserName)){
			$msg = "<div class='alert alert_danger'>Error! Username must be 3-20 characters, letters, numbers and underscore only</div>";
			return $msg;
		}
		
		// Check if username already exists
		$usernameQuery = "SELECT * FROM tbl_user WHERE userName = '$UserName' LIMIT 1";
		$usernameChk = $this->db->select($usernameQuery);
		if($usernameChk != false){
			$msg = "<div class='alert alert_danger'>Error! Username already exists</div>";
			return $msg;
		}
		
		// Enhanced email validation
		if(!filter_var($EMail, FILTER_VALIDATE_EMAIL)){
			$msg = "<div class='alert alert_danger'>Error! Please enter a valid email address</div>";
			return $msg;
		}
		
		// Additional email format checks
		$emailParts = explode('@', $EMail);
		if(count($emailParts) != 2){
			$msg = "<div class='alert alert_danger'>Error! Invalid email format</div>";
			return $msg;
		}
		
		$domain = $emailParts[1];
		$domainParts = explode('.', $domain);
		if(count($domainParts) < 2 || strlen(end($domainParts)) < 2){
			$msg = "<div class='alert alert_danger'>Error! Please enter a valid email domain</div>";
			return $msg;
		}
		
		// Reject common fake/test domains
		$fakeDomains = array('test.com', 'example.com', 'temp.com', 'fake.com', 'invalid.com');
		if(in_array(strtolower($domain), $fakeDomains)){
			$msg = "<div class='alert alert_danger'>Error! Please use a valid email address</div>";
			return $msg;
		}
		
		// Check if email already exists
		$mailquery = "SELECT * FROM tbl_user WHERE userEmail = '$EMail' LIMIT 1";
		$mailchk   = $this->db->select($mailquery);
		if($mailchk != false){
			$msg = "<div class='alert alert_danger'>Error! Email address already registered</div>";
			return $msg;
		}
		
		// Phone validation (Nepal format)
		$cleanPhone = preg_replace('/[\s\-\(\)]/', '', $Cellno);
		if(!preg_match('/^(98|97)\d{8}$/', $cleanPhone)){
			$msg = "<div class='alert alert_danger'>Error! Please enter a valid Nepal phone number (98xxxxxxxx or 97xxxxxxxx)</div>";
			return $msg;
		}
		
		// Address validation
		if(strlen($Address) < 10){
			$msg = "<div class='alert alert_danger'>Error! Please provide a detailed address (at least 10 characters)</div>";
			return $msg;
		}
		
		// Password validation
		if(strlen($Password) < 6){
			$msg = "<div class='alert alert_danger'>Error! Password must be at least 6 characters long</div>";
			return $msg;
		}
		
		// Strong password validation
		if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $Password)){
			$msg = "<div class='alert alert_warning'>Warning: For better security, use a password with uppercase, lowercase, number and special character</div>";
			// Note: This is a warning, not an error - still allows weaker passwords for user convenience
		}
		
		// Confirm password validation
		if($Password != $CnfPassword){
			$msg = "<div class='alert alert_danger'>Error! Passwords do not match</div>";
			return $msg;
		}
		
		// Level validation and adjustment
		if(!in_array($Level, ['1', '2', '3'])){
			$msg = "<div class='alert alert_danger'>Error! Please select a valid user type</div>";
			return $msg;
		}
		
		// Map signup form levels to database levels:
		// Signup form: 1=Regular User, 2=Owner, 3=Agent
		// Database: 1=Regular User, 2=Owner/Agent, 3=Admin (not selectable)
		$dbLevel = ($Level == '1') ? 1 : 2; // 1->1 (User), 2&3->2 (Owner/Agent)
		$userType = ($Level == '1') ? 'user' : (($Level == '2') ? 'owner' : 'agent');
		
		// If all validations pass, proceed with registration
		$Password = md5($Password);
		
		// Use new verification system for all users
		require_once '../classes/PreRegistrationVerification.php';
		$preVerification = new PreRegistrationVerification();
		
		// Prepare registration data
		$registrationData = array(
			'fname' => $FName,
			'lname' => $LName,
			'username' => $UserName,
			'email' => $EMail,
			'cellno' => $cleanPhone,
			'address' => $Address,
			'password' => $Password,
			'level' => $dbLevel,
			'user_type' => $userType
		);
		
		// Register with email verification
		$result = $preVerification->initiateEmailVerification($registrationData);
		
		if($result['success']) {
			$msg = "<div class='alert alert_success'>
				📧 Registration successful! <br>
				<strong>Important:</strong> Please check your email for a verification code.<br>
				" . ($dbLevel == 2 ? "<strong>Note:</strong> As an owner/agent, your account will need admin approval after email verification.<br>" : "") . "
				<a href='verify_registration.php?email=" . urlencode($EMail) . "' class='btn btn_primary' style='color: white; text-decoration: none; padding: 10px 20px; background: #3498db; border-radius: 5px; display: inline-block; margin-top: 10px;'>
					Verify Email Now ➡️
				</a>
			</div>";
		} else {
			$msg = "<div class='alert alert_danger'>" . strip_tags($result['message']) . "</div>";
		}
		
		return $msg;
	}


/* User Sign In Process*/
	
	public function UserLogin($data){
		$Email    = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['email']));
		
		$Password = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['password']));
		
		// Enhanced validation
		if(empty($Email) || empty($Password)){
			$msg = "<div class='alert alert_danger'>Error! Both email and password are required</div>";
			return $msg;
		}
		
		// Email format validation
		if(!filter_var($Email, FILTER_VALIDATE_EMAIL)){
			$msg = "<div class='alert alert_danger'>Error! Please enter a valid email address</div>";
			return $msg;
		}
		
		// Password length validation
		if(strlen($Password) < 6){
			$msg = "<div class='alert alert_danger'>Error! Password must be at least 6 characters long</div>";
			return $msg;
		}
		
		// Proceed with login attempt
		$Password = md5($Password);
		
		$chkData = "SELECT * FROM tbl_user WHERE userEmail = '$Email' AND userPass = '$Password'";
		$result = $this->db->select($chkData);
		if($result != false){
			$value = $result->fetch_assoc();
			
			// Check if email verification columns exist
			$checkColumn = "SHOW COLUMNS FROM tbl_user LIKE 'email_verified'";
			$columnExists = $this->db->select($checkColumn);
			$useEmailVerification = ($columnExists && $columnExists->num_rows > 0);
			
			// Check if email is verified (only if email verification is set up)
			if($useEmailVerification && isset($value['email_verified']) && $value['email_verified'] == 0) {
				// Email not verified - generate new OTP and redirect to verification
				$otp = $this->emailOTP->generateOTP();
				
				if($this->emailOTP->storeOTP($Email, $otp, 'registration')) {
					$this->emailOTP->sendOTP($Email, $otp, 'registration');
				}
				
				Session::set('pending_verification_email', $Email);
				
				$msg = "<div class='alert alert_warning'>
					⚠️ Your email address is not verified yet.<br>
					We've sent a new verification code to your email.<br>
					<a href='verify_email.php?email=" . urlencode($Email) . "' class='btn btn_primary' style='color: white; text-decoration: none; padding: 10px 20px; background: #3498db; border-radius: 5px; display: inline-block; margin-top: 10px;'>
						Verify Email Now ➡️
					</a>
				</div>";
				return $msg;
			}
			
			// Check if user account is active
			if($value['userStatus'] == 0) {
				// Account is inactive - check if it's pending admin verification
				if($value['userLevel'] == 2) { // Owner or Agent
					$msg = "<div class='alert alert_warning'>
						⏳ <strong>Account Pending Admin Verification</strong><br>
						Your account is waiting for admin approval.<br>
						📧 You will receive an email once your account is verified.<br>
						⏱️ This usually takes 1-2 business days.<br><br>
						<strong>Status:</strong> Pending Admin Verification<br>
						<strong>Account Type:</strong> Property Owner/Agent<br>
						<strong>Meanwhile:</strong> You can sign in as a regular user to browse properties<br><br>
						<a href='signin.php' style='background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🔄 Try Again</a>
					</div>";
					return $msg;
				} else {
					$msg = "<div class='alert alert_danger'>❌ Account is deactivated. Please contact administrator.</div>";
					return $msg;
				}
			}
			
			// All checks passed - proceed with login
			Session::set("userlogin", true);
			Session::set("userId", $value['userId']);
			Session::set("userFName", $value['firstName']);
			Session::set("userLName", $value['lastName']);
			Session::set("userImg", $value['userImg']);
			Session::set("userEmail", $value['userEmail']);
			Session::set("cellNo", $value['cellNo']);
			Session::set("phoneNo", $value['phoneNo']);
			Session::set("userAddress", $value['userAddress']);
			Session::set("userPass", $value['userPass']);
			Session::set("userLevel", $value['userLevel']);
			
			// Redirect based on user level and admin approval status
			if($value['userLevel'] == 3){
				// Admin - always redirect to admin dashboard
				echo"<script>window.location='Admin/dashboard_agent.php'</script>";
			} elseif($value['userLevel'] == 2 && $value['userStatus'] == 1){
				// Owner/Agent with admin approval - redirect to owner dashboard
				echo"<script>window.location='Admin/dashboard_owner.php'</script>";
			} else{
				// Regular user or owner/agent without approval - redirect to user page
				echo"<script>window.location='index.php'</script>";
			}
		} else{
			$msg = "<div class='alert alert_danger'>Error! Invalid email or password. Please try again.</div>";
			return $msg;
		}
	}
	

/* User Password Update Process*/

	function updatePassword($data, $userId){
		$Oldpass = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['oldpass']));
		
		$Newpass = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['newpass']));
		
		$CnfPassword = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['cnf_password']));
		
		if(empty($Oldpass) || empty($Newpass) || empty($CnfPassword)){
			$msg = "<div class='alert alert_danger'>Error! Fields must not be empty</div>";
			return $msg;
		} elseif(strlen($Newpass) < 6){
			$msg = "<div class='alert alert_danger'>Error! Password is too short</div>";
			return $msg;
		}  elseif($Newpass != $CnfPassword){
			$msg = "<div class='alert alert_danger'>Error! Please match password</div>";
			return $msg;
		} else{
			$chkPassword = $this->checkPassword($Oldpass, $userId);
			if($chkPassword != true){
				$msg = "<div class='alert alert_danger'>Error! Password not matched or data not found</div>";
				return $msg;
			} else{
				$Newpass = md5($Newpass);
				$query = "UPDATE tbl_user
					  SET
					  userPass = '$Newpass',
					  confPass = '$CnfPassword' WHERE
					  userId   = '$userId'";
				$updated_row = $this->db->update($query);
				if($updated_row){
					$msg = "<div class='alert alert_success'>Password updated successfully</div>";
					return $msg;
				} else{
					$msg = "<div class='alert alert_danger'>Something went wrong!</div>";
					return $msg;
				}
			}
		}
	}
	
	
	private function checkPassword($Oldpass, $userId){
		$Oldpass = md5($Oldpass);
		$query = "SELECT userPass FROM tbl_user WHERE userPass = '$Oldpass' AND userId = '$userId'";
		$result = $this->db->select($query);
		if($result){
			return true;
		} else{
			return false;
		}
	}
	
	
/* User Data Retrive Process*/
	
	public function getUserData($userId){
		$query = "SELECT * FROM tbl_user WHERE userId = '$userId'";
		$result = $this->db->select($query);
		return $result;
	}
	
	
/* User Data Update Process*/
		
	public function userUpdate($data, $files, $userId){
		$FName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['fname']));
		
		$LName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['lname']));
		
		$UserName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['username']));
		
		$EMail = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['email']));
		
		$Cellno = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['cellno']));
		
		$Phone = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['phone']));
		
		$Address = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['address']));
		
		$file_name = mysqli_real_escape_string($this->db->link, $this->fm->validation($files['image']['name']));
		
		$file_size = mysqli_real_escape_string($this->db->link, $this->fm->validation($files['image']['size']));
		
		$file_temp = $files['image']['tmp_name'];
		
		if(!empty($file_name)){
			$permited  = array('jpg', 'jpeg', 'png');
			
			$div 	  	    = explode('.', $file_name);
			$file_ext       = strtolower(end($div));
			$unique_image   = substr(md5(time()), 0, 10).'.'.$file_ext;
			$uploaded_image = "uploads/".$unique_image;
	
			if(empty($FName) || empty($LName) || empty($UserName) || empty($EMail) || empty($Cellno) || empty($Address)){
				$msg = "<div class='alert alert_danger'>Error! Fields must not be empty</div>";
				return $msg;
			}
			$mailquery = "SELECT * FROM tbl_user WHERE userEmail = '$EMail' AND userId != '$userId' LIMIT 1";
			$mailchk   = $this->db->select($mailquery);
			
			if($mailchk != false){
			$msg = "<div class='alert alert_danger'>Error! E-mail already exist</div>";
			return $msg;
			} elseif(strlen($UserName) < 3){
				$msg = "<div class='alert alert_danger'>Error! Username is too short</div>";
				return $msg;
			} elseif(!filter_var($EMail, FILTER_VALIDATE_EMAIL)){
				$msg = "<div class='alert alert_danger'>Error! Invalid email given</div>";
				return $msg;
			} elseif (in_array($file_ext, $permited) === false) {
				$msg = "<div class='alert alert_danger'>You can upload only:-".implode(', ', $permited)." type image</div>";
				return $msg;
			} elseif ($file_size >1048567) {
				$msg = "<div class='alert alert_danger'>Image Size should be less then 1MB!</div>";
				return $msg;
			} else{
				move_uploaded_file($file_temp, "../".$uploaded_image);
				
				$query = "UPDATE tbl_user
						  SET
						  firstName   = '$FName',
						  lastName 	  = '$LName',
						  userName    = '$UserName',
						  userImg 	  = '$uploaded_image',
						  userEmail   = '$EMail',
						  cellNo   	  = '$Cellno',
						  phoneNo     = '$Phone',
						  userAddress = '$Address' WHERE
						  userId	  = '$userId'";
				$updated_row = $this->db->update($query);
				if($updated_row){
					$msg = "<div class='alert alert_success'>Profile updated successfully!</div>";
					return $msg;
				} else{
					$msg = "<div class='alert alert_danger'>Something went wrong!</div>";
					return $msg;
				}
			}
		} else{
			if(empty($FName) || empty($LName) || empty($UserName) || empty($EMail) || empty($Cellno) || empty($Address)){
				$msg = "<div class='alert alert_danger'>Error! Fields must not be empty</div>";
				return $msg;
			}
			$mailquery = "SELECT * FROM tbl_user WHERE userEmail = '$EMail' AND userId != '$userId' LIMIT 1";
			$mailchk   = $this->db->select($mailquery);
			
			if($mailchk != false){
			$msg = "<div class='alert alert_danger'>Error! E-mail already exist</div>";
			return $msg;
			} elseif(strlen($UserName) < 3){
				$msg = "<div class='alert alert_danger'>Error! Username is too short</div>";
				return $msg;
			} elseif(!filter_var($EMail, FILTER_VALIDATE_EMAIL)){
				$msg = "<div class='alert alert_danger'>Error! Invalid email given</div>";
				return $msg;
			} else{
				$query = "UPDATE tbl_user
						  SET
						  firstName   = '$FName',
						  lastName 	  = '$LName',
						  userName    = '$UserName',
						  userEmail   = '$EMail',
						  cellNo   	  = '$Cellno',
						  phoneNo     = '$Phone',
						  userAddress = '$Address' WHERE
						  userId	  = '$userId'";
				$updated_row = $this->db->update($query);
				if($updated_row){
					$msg = "<div class='alert alert_success'>Profile updated successfully!</div>";
					return $msg;
				} else{
					$msg = "<div class='alert alert_danger'>Something went wrong!</div>";
					return $msg;
				}
			}
		}
	}
	
	
/* View owner list Process*/
	
	function getAllOwner(){
		$query = "SELECT * FROM tbl_user WHERE userLevel = 2";
		$result = $this->db->select($query);
		return $result;
	}
	

/* Delete owner Process*/
		
	function delUserById($delUserId){
		$delUserId = mysqli_real_escape_string($this->db->link, $this->fm->validation($delUserId));
		
		$query = "DELETE FROM tbl_user WHERE userId = '$delUserId'";
		$deldata = $this->db->delete($query);
		if($deldata){
			$msg = "<div class='alert alert_success'>User deleted successfully!</div>";
			return $msg;
		} else{
			$msg = "<div class='alert alert_danger'>Something went wrong!</div>";
			return $msg;
		}
	}
	
	
	private function chkUserExist($userName, $userEmail){
		$query = "SELECT * FROM tbl_user WHERE userName = '$userName' AND userEmail = '$userEmail'";
		$result = $this->db->select($query);
		if($result){
			return true;
		} else{
			return false;
		}
	}
	
	
/*User data retrive process */

	function retrivePassword($data){
		$userName = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['username']));
		
		$userEmail = mysqli_real_escape_string($this->db->link, $this->fm->validation($data['email']));
		
		if(empty($userName) || empty($userEmail)){
			$msg = "<div class='alert alert_danger'>Error! Fields must not be empty</div>";
			return $msg;
		} elseif(!filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
			$msg = "<div class='alert alert_danger'>Error! Invalid email given</div>";
			return $msg;
		} else{
			$chkUser = $this->chkUserExist($userName, $userEmail);
			if($chkUser != true){
				$msg = "<div class='alert alert_danger'>Error! Data not found. Please sign up now</div>";
				return $msg;
			} else{
				$query = "SELECT * FROM tbl_user WHERE userEmail = '$userEmail'";
				$userdata = $this->db->select($query);
				if($userdata){
					while($value = $userdata->fetch_assoc()){
						$userid   = $value['userId'];
						$username = $value['userName'];
						$usermail = $value['userEmail'];
					}
					
					$text = substr($usermail, 0, 3);
					$rand = rand(1000, 9999);
					$newpass = $text.$rand;
					$password = md5($newpass);
					
					$updquery = "UPDATE tbl_user
								 SET
								 userPass = '$password' WHERE
								 userId   = '$userid'";
					$updated_row = $this->db->update($updquery);
					if($updated_row){
						$to   = $userEmail;
						$from = "houserental@gmail.com";
						$headers = "From: $from\n";
						$headers .= 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset= iso-8859-1' . "\r\n";
						$subject = "Rental House password recovery";
						$message = "Your username is - ".$username." and password is - ".$newpass.". Please visit system to login.";
						
						$sendmail = mail($to, $subject, $message, $headers);
						
						if($sendmail){
							$msg = "<div class='alert alert_success'>Thanks a lot! Please check your email for new password</div>";
							return $msg;
						} else{
							$msg = "<div class='alert alert_danger'>Something went wrong!</div>";
							return $msg;
						}
					}
				}
			}
		}
	}
	
	
/*View new owner process */

	function getNewOwner(){
		$query = "SELECT * FROM tbl_user WHERE userLevel = '2' AND userStatus = '0'";
		$result = $this->db->select($query);
		return $result;
	}

	
/*Update new owner process*/
	
	function updateUserStatus(){
		$query = "UPDATE tbl_user
				  SET
				  userStatus = '1' WHERE
				  userStatus = '0' AND
				  userLevel  = '2'";
		return $updated_row = $this->db->update($query);
	}
	
	
}
?>