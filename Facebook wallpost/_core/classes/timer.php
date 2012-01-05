<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** timer.php
*	
*	This object can be used to time any process during php parsing
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Timer {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$digits = 4;	// digits used for rounding of display time
	protected	$timeStart;
	protected	$timeEnd;



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Start the timer if requested.
	*	
	*/
	function __construct($start = true) {
	
		if ($start) {
			$this->start();
		}
		
	}
	



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** set_digits()
	*	
	*	Sets the amount of digits for rounding off.
	*	
	*/
	public function set_digits($newDigits = 4) {
	
		$this->digits = $newDigits;
	
	}
	

	/** start()
	*	
	*	Starts the timer.
	*	
	*/
	public function start() {
	
		list($usec, $sec) = explode(' ',microtime());
		$this->timeStart = ((float)$usec + (float)$sec);
		
	}
	

	/** stop()
	*	
	*	Stops the timer.
	*	
	*/
	public function stop() {
	
		list($usec, $sec) = explode(' ',microtime());
		$this->timeEnd = ((float)$usec + (float)$sec);
		
	}
	

	/** tell()
	*	
	*	Generates the duration and returns it.
	*	
	*/
	public function tell($stop = false) {
	
		if ($stop) {
			$this->stop();
		}
		$duration =  $this->timeEnd - $this->timeStart;
		$duration = round($duration, $this->digits);
		return $duration;
		
	} 

	
}
?>