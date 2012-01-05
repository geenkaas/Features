<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** guard.php
*	
*	The guard object protects user content from intruders 
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Guard extends Object{


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$timeout		= 10000;  // around 3 hours
	protected	$guestGroupId	= 0;



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	This is the function description for the constructor.
	*	
	*/
	function __construct($timeout = 10000) {
	
		// Set new timout value in seconds
		$this->timeout = $timeout;
		
		// Check login immediately to make sure LOGGED_IN gets set
		$this->check();
		
	}
	


//----------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** protect()
	*	
	*	Check if the user is logged in and throw out incase not
	*	
	*/
	public function protect($permittedGroupsArr, $userGroup, $redirectTo = DEFAULT_LOGOUT) {

		// CHECK: if page is open to guests else protect from intruders
		if (in_array((string)$this->guestGroupId,$permittedGroupsArr) == false) {
			if($this->check() == false || in_array($userGroup,$permittedGroupsArr) == false) {
				// Bye bye login data
				$this->clear_authentication_session();
				// Save the url the user wanted to visit
				$_SESSION['guard']['takeToUrl'] = $_SERVER['REQUEST_URI'];
				// Done, now kick him out ;)
				Core::redirect($redirectTo);				
			}
		}
		return true;
	}
	
	
	/** check()
	*	
	*	Check if the user is logged in.
	*	
	*/
	public function check() {
	
		// CHECK: if already checked
		if (defined('LOGGED_IN')) {
			return LOGGED_IN;
		} else {
		
			// CHECK: time and IP authentication
			if (!empty($_SESSION['authentication']['requestTime']) && $_SESSION['authentication']['ip'] == $_SERVER['REMOTE_ADDR'] && $_SERVER['HTTP_USER_AGENT'] == $_SESSION['authentication']['agent']) {
		
				if (time() < ($_SESSION['authentication']['requestTime'] + $this->timeout)) {
					// Set check time
					$_SESSION['authentication']['requestTime'] = time();
					// Unset redirect url
					if (isset($_SESSION['guard']['takeToUrl'])) {
						unset($_SESSION['guard']['takeToUrl']);
					}
					define('LOGGED_IN', true);
					return true;
					
				} else {
					define('LOGGED_IN', false);
					return false;
				}
				
			} else {
				define('LOGGED_IN', false);
				return false;
			}		
		
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
		foreach ($_SESSION['authentication'] as $key => $value) {
			$_SESSION['authentication'][$key] = NULL;
		}
		return true;
	}
	
	
}
?>