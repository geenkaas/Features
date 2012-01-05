<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** dataobject.php
*	
*	This is an base object mold for all data classes to be build on.
*	This base class contains all the basic get, display and set methods.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class DataObject {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected 	$objectQuery	= 'SELECT * FROM `[[TABLE]]` WHERE `[[FIELD]]` = [[VALUE]]';  // query used to get data;
	protected 	$objectTable;		// table in wich to find object record
	protected 	$identifierField;	// field containing unique identifying value
	protected 	$identifierValue;	// unique identifying value

	// DATA MODEL ARRAY
	protected 	$objectExists;		// set to true if object is found
	protected 	$modelArr;			// array containing all fields in a record
	protected 	$propertyArr;		// array containing properties of this data object
	


//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD	
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()	
	*
	*	The constructor initializes the data object and gets its model & properties from the db. 
	*	
	*/
	public function __construct($table, $field, $value, $newQuery = NULL) {
		
		// IMPORT: db object
		$db = Db::get_instance();
		
		//SET: a new query if passed
		if (!empty($newQuery)) {
			$this->objectQuery = $newQuery;
		}
		
		if (!empty($table)) {

			// SET: passed table property for the model description query
			$this->objectTable = $table;
			
			// GET: data object model description
			$modelQuery = 'DESCRIBE ' . $this->objectTable;
			$modelRes = $db->query($modelQuery);
			
			// SET: model array
			if ($modelRes->num_rows >= 1) {
				while ($rowArr = $modelRes->fetch_assoc()) {
					$this->modelArr[$rowArr['Field']] = true;
				}
			} else {
				// ERROR: object table must exist	
				trigger_error('An existing object table must be passed as first parameter for "DataObject" objects', E_USER_ERROR);
			}

			// SET: properties if 
			if (!empty($field) && !empty($value)) {
				
				// SET: passed properties for the model populating query
				$this->identifierField = $field;
				$this->identifierValue = (is_string($value)) ? '\'' . Core::input($value) . '\'' : $value;
				
				// PREPARE: model populating query
				$this->objectQuery = str_replace('[[TABLE]]', $this->objectTable, $this->objectQuery);
				$this->objectQuery = str_replace('[[FIELD]]', $this->identifierField, $this->objectQuery);
				$this->objectQuery = str_replace('[[VALUE]]', $this->identifierValue, $this->objectQuery);
				
				// GET: data
				$result = $db->query($this->objectQuery);  
				
				// SET: properties
				if ($result->num_rows == 1) {
					$this->objectExists = true;
					$this->propertyArr 	= Core::clean($result->fetch_assoc());
				}
			
			}
		
		} else {
		
			// ERROR: table attribute must be set	
			trigger_error('An object table must be passed as first parameter for "DataObject" objects', E_USER_ERROR);
		
		}
			  
	}
	


//------------------------------------------------------------------------------------------------------------------
// SET METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** set() 
	*
	*	Set object properties, set $clean to clean value. Object properties are set to aseparate array 
	*	to enable updating them trough Db::perform
	*
	*/
	public function set($property, $value, $clean = true) {
		
		if (isset($this->modelArr[$property])) { // data model properties
		
			if ($clean == true) {
				$this->propertyArr[$property] = Core::clean($value);
			} else {
				$this->propertyArr[$property] = $value;
			}
		
		} else { // regular properties
		
			if ($clean == true) {
				$this->$property = Core::clean($value);
			} else {
				$this->$property = $value;
			}			
		
		}
	
	}	


	
//------------------------------------------------------------------------------------------------------------------
// TELL METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** tell() 
	*
	*	Tell the object property values, whether data model or regular properties.
	*	Set $clean to clean value to be returned
	*
	*/
	public function tell($property, $clean = false) {
		
		if (isset($this->modelArr[$property])) { // data model properties
			
			if (!isset($this->propertyArr[$property])) { return NULL; }
			if ($clean == true) {
				return Core::clean($this->propertyArr[$property]);
			} else {
				return $this->propertyArr[$property];
			}
		
		} else { // regular properties
			
			if (!isset($this->$property)) { return NULL; }
			if ($clean == true) {
				return Core::clean($this->$property);
			} else {
				return $this->$property;
			}		
		
		}
	
	}
	
	/** exists() 
	*
	*	Tell if this object has found it's object record or has just created it
	*
	*/
	public function exists() {
		
		if ($this->objectExists) { // set in constructor and insert method
			return true;
		} else {
			return false;
		}
	
	}		



//------------------------------------------------------------------------------------------------------------------
// DISPLAY METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** display()
	*
	*	Tell the object property values, ready for display, whether data model or regular properties.
	*
	*/
	public function display($property, $prepareForOutput = true) {
	
		if (isset($this->modelArr[$property])) { // data model properties
		
			if (!isset($this->propertyArr[$property])) { return NULL; }
			if ($prepareForOutput == true) {
				return Core::output($this->propertyArr[$property]);
			} else {
				return $this->propertyArr[$property];
			}
		
		} else { // regular properties
			
			if (!isset($this->$property)) { return NULL; }
			if ($prepareForOutput == true) {
				return Core::output($this->$property);
			} else {
				return $this->$property;
			}		
		
		}
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// PROCESSING METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** insert()
	*
	*	Insert object data in database
	*
	*/
	public function insert($addProcessNote = true) {
	
		// IMPORT: db and process objects
		$db = Db::get_instance();
		$process = Process::get_instance(); 
		
		// INSERT
		$insertRes = $db->perform($this->objectTable, $this->propertyArr);
		
		if ($insertRes) {
			
			// SET: object exists
			$this->objectExists = true;
			
			// GET: last inserted id
			$this->propertyArr['id'] = $db->insert_id;
			$this->identifierField = 'id';
			$this->identifierValue = $this->propertyArr['id'];
			
			// SET: process note
			if ($addProcessNote) {
				$process->add_note(STRING_CLASS_DATAOBJECT_DONE_DATA_INSERT, 'DONE');
			}
			
			return true;
		
		} else {
			
			// SET: process note
			if ($addProcessNote) {
				if ($errorMessage = $db->tell_error()) {
					$process->add_note($errorMessage);
				} else {
					$process->add_note(STRING_CLASS_DATAOBJECT_FAIL_DATA_INSERT);
				}
			}
			
			return false;
			
		}
		
	}

	
	/** update()
	*
	*	Update object data in database
	*
	*/
	public function update($addProcessNote = true) {   
		
		// IMPORT: db and process objects
		$db = Db::get_instance();
		$process = Process::get_instance();
				
		// UPDATE: object record
		$updateRes = $db->perform($this->objectTable, $this->propertyArr, 'UPDATE', $this->identifierField . ' = ' . $this->identifierValue);
		if ($updateRes) {
			
			// SET: process note
			if ($addProcessNote) {
				$process->add_note(STRING_CLASS_DATAOBJECT_DONE_DATA_UPDATE, 'DONE');
			}
			
			return true;
		
		} else {
			
			// SET: process note
			if ($addProcessNote) {
				$process->add_note(STRING_CLASS_DATAOBJECT_FAIL_DATA_UPDATE);
			}
			
			return false;
			
		}
		
	}

	
	/** delete()
	*
	*	Delete object data in database.
	*
	*/
	public function delete($addProcessNote = true) {   
		
		// IMPORT: db object
		$db = Db::get_instance();
		$process = Process::get_instance();
		
		// DELETE: object record		
		$deleteQuery = '
			DELETE FROM `' . $this->objectTable . '` 
			WHERE ' . $this->identifierField . ' = ' . $this->identifierValue . ' 
			LIMIT 1
		';
		$deleteRes = $db->query($deleteQuery);
		
		if ($deleteRes) {
					
			// SET: process note
			if ($addProcessNote) {
				$process->add_note(STRING_CLASS_DATAOBJECT_DONE_DATA_DELETE, 'DONE');
			}
			
			// SET: object to non existant
			$this->objectExists = false;
			
			return true;
		
		} else {
			
			// SET: process note
			if ($addProcessNote) {
				$process->add_note(STRING_CLASS_DATAOBJECT_FAIL_DATA_DELETE);
			}
			return false;
			
		}
		
	}
	
	

//------------------------------------------------------------------------------------------------------------------
// GET METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** get_id()
	*
	*	Tell the object current id, or the next auto-create id.
	*
	*/
	public function get_id($field = 'id') {
	
		if (isset($this->propertyArr[$field])) {  // already set, id exists
			return $this->propertyArr[$field];
		} elseif (!empty($this->modelArr)) {  // not set yet, go fetch
			
			// IMPORT: db object
			$db = Db::get_instance();
			
			// GET: next auto id
			$nextIdRes = $db->query('SHOW TABLE STATUS LIKE \'' . $this->objectTable . '\'');
			
			if (isset($nextIdRes)) {
					
				$dataRow = $nextIdRes->fetch_assoc();
				$nextId = $dataRow['Auto_increment'];
				return $nextId;
			
			} else {
				trigger_error('Object table doesn\'t exist, or DataObject setting incorrect', E_USER_ERROR);
			}
		
		} else {
			trigger_error('Object table doesn\'t exist, or DataObject setting incorrect', E_USER_ERROR);
		}
	
	}
		
}
?>