<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** process.php
*	
*	This is the process administrator object. User failures, successes and information bits are reported to this object.
* 	Processes check with it before running any process
*	It uses the singleton patern to prevent the need for global $process objects in other classes
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Process extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: static instance field
	private static $instance;

	// DEFINE: fields (no need to be static as the whole object will be after instantiation)
	protected	$run 				= true;
	protected 	$notificationArr;
	protected 	$previousSuccess 	= false;  // will be set to true if process (on previous page) was successful
	
	protected	$notificationsClass;  //set classes for the notification block

//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The constructor checks in $_SESSION['noticesArr'] for notices passed on from previous pages
	*	The constructor can't be called from outside this class (singleton patern).
	*	
	*/
	private function __construct() {
	
		// CHECK: for previous messages
		if (isset($_SESSION['noticesArr'])) {
			if (is_array($_SESSION['noticesArr'])) {
				
				foreach ($_SESSION['noticesArr'] as $noticeArr) {
					$this->add_note($noticeArr['message'], $noticeArr['type']);
					$this->previousSuccess = ($noticeArr['type'] == 'DONE') ? true : $this->previousSuccess;
				}
				
			}
		}
		
		// CLEAN: session message array
		$_SESSION['noticesArr'] = array();
		
	}
	
	
    /** __clone()
	*	
	*	Prevent users to clone the instance
	*
	*/
    public function __clone() {
        
		trigger_error('Clone of Process is not allowed.', E_USER_ERROR);
    
	}
	
	
	/** instantiate()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function instantiate() {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : self::$instance = new self();
		
	}
	
	
	/** get_instance()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function get_instance() {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : trigger_error('Process class not yet instatiated, use instantiate() instead of get_instance()', E_USER_ERROR);
		
	}	
	
	


//----------------------------------------------------------------------------------------------------
// SET METHODS
//----------------------------------------------------------------------------------------------------
	
	/** set() 
	* 
	*	Set object properties, set $clean to clean value or $prepareForInput 
	*	to prepare the value for database insertion. Attributes are set to an array 
	*	to enable updating them trough Db::perform. Protect $this->run from access
	*	by any other function but $this->stop()
	*
	*/
	public function set($property, $value, $clean = true, $prepareForInput = false) {
		
		if ($property == 'run') {
			trigger_error('Process::run can\'t be set by the standard set() function, use Process::stop()', E_USER_ERROR);
		} else {
			parent::set($property, $value, $clean, $prepareForInput);
		}

	}
	
	
	/** add_note()
	*	
	*	Add a success, information or failure note to the process.
	*	A failure notice also automatically sets $this->run to false.
	*	Options for the notice type are FAIL (default), DONE, INFO
	*	
	*/
	public function add_note($message, $type = 'FAIL') {
	if (empty($message)) { echo $type; }
		if ($type != 'INFO' && $type != 'DONE') {
			$this->run = false;
		}
		
		$tempArr['message']	= $message;
		$tempArr['type']	= $type;
		$this->notificationArr[] = $tempArr;
		return true;
		
	}

	
	/** add_futur_note()
	*	
	*	Add a success, information or failure note to the next page's process.
	*	A failure notice also automatically sets the next page's $process->run to false.
	*	Options for the notice type are FAIL (default), DONE, INFO
	*	
	*/
	public function add_future_note($message, $type = 'FAIL') {
			
		$tempArr['message']	= $message;
		$tempArr['type']	= $type;
		$_SESSION['noticesArr'][] = $tempArr;
		return true;
		
	}



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** stop()
	*	
	*	Set $this->run to false so processes know of failure.
	*	
	*/
	public function stop() {
	
		$this->run = false;
		return true;
		
	}
	
	
	/** run()
	*	
	*	Check if the process can run.
	*	
	*/
	public function run() {
	
		return $this->run;
		
	}
	
	
	/** previous_success()
	*	
	*	Check if the process on the previous page was successful.
	*	
	*/
	public function previous_success() {
	
		return $this->previousSuccess;
		
	}
	
		
	/** display_notices()
	*	
	*	Display the process notices.
	*	*	
	*/
	public function display_notices() {
		
		if (count($this->notificationArr) >= 1) {
			
			$xhtml = NULL;
			foreach ($this->notificationArr as $pos => $notice) {
				
				if (strpos($xhtml, $notice['message']) === false) {  // check if message is not double
				
					$xhtml .=  '<li class="message' . ucfirst(strtolower($notice['type'])) . '">' . $notice['message'] . '</li>' . "\n";
					
				}
				
			}
			
			return '
				<div id="notificationDiv" class="'. $this->notificationsClass .'">
					<ul id="notificationsUl">' . 
						$xhtml . '
					</ul>
				</div>
			';
		
		} else {
			return false;
		}
		
	}
	

}
?>