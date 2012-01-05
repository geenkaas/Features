<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** page.php
*	
*	This is an extension of the data object class to specifically cater to the needs of pages and the out put of views.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Page extends DataObject {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected $moduleBlocksArr = array();



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Call the constructer of parent dataobject and pass general attributes
	*	
	*/
	public function __construct($table = 'pages', $column = 'name', $value = PAGE, $newQuery = false) {
	
		parent::__construct($table, $column, $value, $newQuery);
		
		// [[REVIEW]] THIS SHOULD BECOME A 404 ERROR PAGE OR A SEARCH RESULT INDICATING POSSIBLE ALTERNATE CONTENT
		if (!is_numeric($this->propertyArr['id'])) {
			parent::__construct($table, $column, DEFAULT_PAGE, 'SELECT * FROM `[[TABLE]]` WHERE `[[FIELD]]` = [[VALUE]]');
		}
		
		// Pre-set blocks to load
		if (Validator::match_if_set('bsf', $this->propertyArr['displayBlocks'])) {
			$this->moduleBlocksArr = explode('|', $this->propertyArr['displayBlocks']);
		}
		
	}



//------------------------------------------------------------------------------------------------------------------
// SET METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** set() 
	*
	*	Set object properties, set $clean to clean value or $prepareForInput 
	*	to prepare the value for database insertion. Attributes are set to an array 
	*	to enable updating them trough Db::perform
	*
	*/
	public function set($property, $value, $clean = false) {

		if ($clean == true) {
			$value = Core::clean($value);
		}	
		
		if (isset($this->modelArr[$property])) { // data model properties
			$this->propertyArr[$property] = $value;		
		} else { // regular properties
			$this->$property = $value;
		}
	
	}
	
	
	/** add() 
	*
	*	Add to object properties, set $clean to clean value.
	*
	*/
	public function add($property, $value, $clean = false) {
		
		if ($clean == true) {
			$value = Core::clean($value);
		}		
		
		if (isset($this->modelArr[$property])) { // data model properties
		
			if (is_array($this->propertyArr[$property]) && is_array($value)) {
				$this->propertyArr[$property] = array_merge($this->propertyArr[$property], $value);
			} else {
				$this->propertyArr[$property] .= $value;
			}
			return $this->propertyArr[$property];
		
		} else { // regular properties
		
			if (is_array($this->$property) && is_array($value)) {
				$this->$property = array_merge($this->$property, $value);
			} else {
				$this->$property .= $value;
			}
			return $this->$property;
		
		}
	
	}	
	
	
	


//----------------------------------------------------------------------------------------------------
// GET METHODS
//----------------------------------------------------------------------------------------------------
	
	/** get_permissions() 
	*
	*	Returns an array of allowed group id's 
	*
	*/
	public function get_permissions() {
		if (Validator::match_if_set('bsv', $this->propertyArr['permittedGroups'])) {
			$permittedGroupsArr = explode('|', (string)$this->propertyArr['permittedGroups']);
			return $permittedGroupsArr;
		} else {
			return array();
		}
	}



//----------------------------------------------------------------------------------------------------
// ADD METHODS
//----------------------------------------------------------------------------------------------------
	
	/** add_block() 
	*
	*	Add blocks to be loaded on the page. By calling $page->tell('moduleBlocksArr') 
	*	the whole array of blocks to be loaded can be accessed. 
	*
	*/
	public function add_block($fileName) {
		if (!in_array($fileName, $this->moduleBlocksArr)) {
			$this->moduleBlocksArr[] = Core::clean($fileName);
		}
	}
	

}
?>