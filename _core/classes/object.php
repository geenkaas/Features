<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** object.php
*	
*	This is an base object mold for all non-datamodel classes to be build on.
*	This base class contains all the basic get, display and set methods.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	


//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()	
	*
	*	This is the function description for the constructor.
	*	
	*/
	private function __construct() {
	
		/* Class instantiation code */
		
	}
	


//----------------------------------------------------------------------------------------------------
// SET METHODS
//----------------------------------------------------------------------------------------------------
	
	/** set() 
	*
	*	Set object properties, set $clean to clean value.
	*
	*/
	public function set($property, $value, $clean = true) {
		
		if ($clean == true) {
			$this->$property = Core::clean($value);
		} else {
			$this->$property = $value;
		}
	
	}	
	

	
//----------------------------------------------------------------------------------------------------
// TELL METHODS
//----------------------------------------------------------------------------------------------------
	
	/** tell() 
	*
	*	Tell the object property values, set $clean to clean value to be returned
	*
	*/
	public function tell($property, $clean = false) {
		
		if (!isset($this->$property)) { return NULL; }
		
		if ($clean == true) {
			return Core::clean($this->$property);
		} else {
			return $this->$property;
		}
	
	}
		
	


//----------------------------------------------------------------------------------------------------
// DISPLAY METHODS
//----------------------------------------------------------------------------------------------------
	
	/** display()
	*
	*	Tell the object property values, ready for display!
	*
	*/
	public function display($property, $prepareForOutput = true) {
		
		if (!isset($this->$property)) { return NULL; }
		
		if ($prepareForOutput == true) {
			return Core::output($this->$property);
		} else {
			return $this->$property;
		}
	
	}
	
	
}
?>