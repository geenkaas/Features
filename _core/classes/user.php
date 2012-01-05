<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** user.php
*	
*	A class that acts as the user, contains its properties, logs in, signs out, reminds himself of credentials,
*	can tell his own name, and more...
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class User extends DataObject {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected $identifierField	= 'email'; 		// table column name used to identify the user
	protected $identifierKey 	= 'email'; 		// key used in $_POST or $_SESSION global to store the identifier
	protected $passwordField 	= 'password'; 	// column where the passwords are stored
	protected $passwordKey 		= 'password'; 	// key used in $_POST or $_SESSION global to store password
	protected $encryption		= false; 		// Use encrypted passwords? false, md5 or sha1
	
	protected $groupArr;						// group information




//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The constructor only setthe passed table or complains if empty
	*	
	*/
	function __construct($objectTable = 'sys_users') {
		
		if (!empty($objectTable)) {
			// SET: passed table property for the query
			$this->objectTable = $objectTable;	
		} else {
			// ERROR: table attribute must be set	
			trigger_error('An object table must be passed as first parameter for "User" objects', E_USER_ERROR);
		}
		
		
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** get_data()
	*
	*	Gets all user attributes and sets them. It tries to find out what the users email 
	*	is by first searching the session global if set, and redirects if
	*	user not found in database. And if the email was not set in the sessions, the post 
	*	global is used. If email is not set in the POST global or the user is not found
	*	in the database ERROR is set and the message is stored. 
	*
	*/
	public function get_data() { 
		
		// SET: $process global
		$process 	= Process::get_instance(); 
	
		if (Validator::match_if_set('email', $_SESSION['authentication'][$this->identifierKey])) {
		
			// SET: identifier value
			$this->identifierValue = $_SESSION['authentication'][$this->identifierKey];
			$container = 'SESSION';
		
		} elseif (Validator::match_if_set('email', $_POST[$this->identifierKey]) && (Validator::equal_if_set('login',$_POST['action']) ||Validator::equal_if_set('remind',$_POST['action']) )) {
		
			// SET: identifier value
			$this->identifierValue = $_POST[$this->identifierKey];
			$container = 'POST';			
		
		} elseif (Validator::match_if_set('email', $_GET[$this->identifierKey]) && Validator::equal_if_set('login',$_GET['action'])) {
		
			// SET: identifier value
			$this->identifierValue = $_GET[$this->identifierKey];
			$container = 'GET';			
		
		} else { //REVIEW
			return false;
		}	
	
		// RUN: parent constructor method, does not return anything
		parent::__construct($this->objectTable, $this->identifierField, $this->identifierValue);			
		
		// TEST: if record found
		if ($this->exists()) {
			return true;
		} elseif ($container == 'SESSION') {
			Core::redirect(DEFAULT_LOGOUT);
		} else {
			$process->add_note(STRING_CLASS_USER_NOT_FOUND);
		}
		
	}


	/** login()
	*	
	*	Compare user pass to hash and log in if identical. If succeededredirect to account url
	*
	*/
	public function login($url = DEFAULT_LOGGED_IN) {
		
		// SET: $process global
		$process 	= Process::get_instance();
		
		// GET: data of user
		$this->get_data(); 
		
		// CHECK: process state
		if ($process->run()) {
		
			// CHECK: if user has password, else fail
			if ($this->tell($this->passwordField)) {
				$realPass = $this->tell($this->passwordField);
				$sentPass = $_POST[$this->passwordKey];
			} else {
				$process->add_note(STRING_CLASS_USER_FAIL_LOGIN);
				return false;
			}


			// CHECK: data for login
			if (!empty($realPass) && !empty($sentPass)) {
	
				if ($this->encryption == 'md5') {
					$sentPass = md5($sentPass);
				} elseif ($this->encryption == 'sha1') {
					$sentPass = sha1($sentPass);
				}
				
				if ($realPass === $sentPass) {
					$_SESSION['authentication'] = array(
						'requestTime' 			=> time(),
						'ip' 					=> $_SERVER['REMOTE_ADDR'],
						'agent' 				=> $_SERVER['HTTP_USER_AGENT'],
						'project' 				=> PROJECT,
						'application' 			=> APPLICATION,
						$this->identifierKey	=> $this->tell($this->identifierField, true)
					);
					Core::redirect($url);  // your're in
					return true;
				} else {
					$process->add_note(STRING_CLASS_USER_FAIL_PASS_INCORRECT); 
					return false;
				}
				
			} else {
				$process->add_note(STRING_CLASS_USER_FAIL_LOGIN);
				return false;
			}
		
		} else {
			return false;
		}
		
	}


	/** logout()
	*	
	*	Unset session vars. Afterwards redirect to login(the supplied) url
	*
	*/
	public function logout($url = DEFAULT_LOGIN) {
		
		
		// UNSET: session vars
		$this->clear_authentication_session();
		
		// REDIRECT: to login page / the supplied url
		Core::redirect($url);
	
	}


	/** remind()
	*	
	*	Send users login credentials
	*
	*/
	public function remind() {
		
		// SET: $process global
		$process 	= Process::get_instance();
		
		// GET: data of user
		$this->get_data();
		
		// CHECK: process state
		if ($process->run()) {
			
			// GET: send login credentiials
			$mailer = new Mailer();
			$mailer->add_to($this->tell('email'), $this->tell('firstName').' '.$this->tell('lastName'));
			$mailer->set_from(STRING_SETTING_PROJECT_EMAIL, STRING_SETTING_PROJECT_NAME);
			$mailer->set_subject('Important!');

			$html = str_replace('[[NAME]]', $this->display('firstName'), TEXT_CLASS_USER_EMAIL_REMIND);
			$html = str_replace('[[EMAIL]]', $this->display('email'), $html);
			$html = str_replace('[[PASS]]', $this->display('password'), $html);
			$mailer->set_body($html);
			
			if ($mailer->send()) {
				// REPORT: success
				$process->add_note(STRING_CLASS_USER_DONE_SEND_REMINDER, 'DONE');
				return true;
			} else {
				// REPORT: failure
				$process->add_note(STRING_CLASS_USER_FAIL_SEND_REMINDER);
				return false;
			}
			
		
		} else {
			// REPORT: failure
			$process->add_note(STRING_CLASS_USER_FAIL_SEND_REMINDER);
			return false;
		}
		
	}



//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHODS	
//------------------------------------------------------------------------------------------------------------------

	/** clear_authentication_session()
	*	
	*	Unset session vars. 
	*
	*/
	public function clear_authentication_session() {
		
		
		// UNSET: session vars
		$_SESSION['authentication'] = array(
			'requestTime' 			=> NULL,
			'ip' 					=> NULL,
			'agent' 				=> NULL,
			$this->identifierKey	=> NULL
		);
		
		return true;
	}

}
?>