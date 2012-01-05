<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** db.php
*	
*	Db is the database connection and transaction class.
*	It uses the singleton patern to prevent the need for global $db objects in other classes
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Db extends mysqli {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: singleton fields
	private static $instance;
	
	// DEFINE: fields
	protected $nonCriticalErrorArr;	
	
	// DEFINE: processing fields
	protected $selectArr;
	
	

//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION	
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Connect by calling the contructer of mysqli.
	*	Afterwards, check the connection for errors and handle accordingly
	*	Can only be called from within this class (singleton patern)
	*	
	*/
	private function __construct($server = DB_SERVER, $user = DB_USER, $pass = DB_PASS, $database = DB_NAME, $port = NULL, $socket = NULL) {
	
		// CALL: mysqli's constructor
		@parent::__construct($server, $user, $pass, $database, $port, $socket);
		
		// CHECK: the connection
		if (mysqli_connect_errno()) { trigger_error(mysqli_connect_error(), E_USER_ERROR); }
		
	}
	
	
    /** __clone()
	*	
	*	Prevent users to clone the instance
	*
	*/
    public function __clone() {
        
		trigger_error('Clone of Db is not allowed.', E_USER_ERROR);
    
	}
	
	
	/** instantiate()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function instantiate($server = DB_SERVER, $user = DB_USER, $pass = DB_PASS, $database = DB_NAME, $port = NULL, $socket = NULL) {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : self::$instance = new self($server, $user, $pass, $database, $port, $socket);
		
	}
	
	
	/** get_instance()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function get_instance() {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : trigger_error('Db class not yet instatiated, use instantiate() instead of get_instance()', E_USER_ERROR);
		
	}
	
	
	
//------------------------------------------------------------------------------------------------------------------
// TELL METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** tell_error()
	*
	*	Returns the message of a non critical error if set
	*
	*/
	public function tell_error() {
		
		if (empty($this->nonCriticalErrorArr)) {
			
			// SET: array of non critical error messages (also set codes in query function)
			$this->nonCriticalErrorArr = array(
				1062 => 'STRING_CLASS_DATAOBJECT_FAIL_DATA_DUPLICATE'
			);
			
		}
		
		return isset($this->nonCriticalErrorArr[$this->errno]) ? constant($this->nonCriticalErrorArr[$this->errno]) : false;		
		
	}



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	
	
	/** query()
	*	
	*	Queries the db and throws an error if query fails.
	*	
	*/
	public function query($query) {

		// SET: errors to communicate to user instead of triggering a fatal error
		$nonCriticalErrors = array (1062);
		
		if (!$result = parent::query($query)) {
			
			if (in_array($this->errno, $nonCriticalErrors)) {
				return false;
			} else {
				trigger_error($this->errno . ': ' . $this->error, E_USER_ERROR);
				return false;
			}
			
		} else {
			return $result;
		}
		
	}
	
	
	/** perform(): 
	*	
	*	Perform INSERT (one record) or UPDATE queries in MySQL db. Reports errors
	*
	*	@param $table: db table name on which to perform
	*	@param $dataArr: assoc array of columns and record value to be inserted/updated
	*	@param $action: either 'INSERT' or 'UPDATE', default is 'INSERT'
	*	@param $parmameters: WHERE and LIMIT clause to be used with update, empty by default
	*
	*/
	  
	public function perform($table, $dataArr, $action = 'INSERT', $parameters = '') {
	
		if ($action == 'INSERT') {
			
			$queryOne = '';
			$queryTwo = '';
			
			foreach ($dataArr as $column => $value) {
				
				$queryOne .= '`' . $column . '`, ';
				
				switch ((string)$value) {
					case 'NOW()':
						$queryTwo .= 'NOW(), ';
						break;
					case 'null':
						$queryTwo .= 'NULL, ';
						break;
					case 'NULL':
						$queryTwo .= 'NULL, ';
						break;
					case '':
						$queryTwo .= 'NULL, ';
						break;		  
					default:
						$queryTwo .= '\'' . Core::input($value) . '\', ';
						break;
				}
			
			}
			
			$query = 'INSERT INTO `' . $table . '` (' . substr($queryOne, 0, -2) . ') VALUES (' . substr($queryTwo, 0, -2) . ')';
		
		} elseif ($action == 'UPDATE') {
		
			$query = '';
			
			foreach ($dataArr as $column => $value) {
				switch ((string)$value) {
					case 'NOW()':
						$query .= '`' . $column . '` = NOW(), ';
						break;
					case 'null':
						$query .= '`' . $column . '` = NULL, ';
						break;
					case 'NULL':
						$query .= '`' . $column . '` = NULL, ';
						break;
					case '':
						$query .= '`' . $column . '` = NULL, ';
						break;
					default:
						$query .= '`' . $column . '` = \'' . Core::input($value) . '\', ';
						break;
				}
			}
		
			$query = 'UPDATE `' . $table . '` SET ' . substr($query, 0, -2) . ' WHERE ' . $parameters;
		
		}

		return $this->query($query);
	  
	}
	



//------------------------------------------------------------------------------------------------------------------
// GET METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/**	get_array_result()
	*	
	*	Takes a query, gets the result and turns it into an assoc array of the two fields you supply
	*
	*	$query: database query
	*	$keyField: becomes array key
	*	$valueF: becomes array value
	* 
	*/
	public function get_array_result($query, $keyField, $valueField) {
		
		$array = array();
		if ($result = $this->query($query)) {
		
			while ($row = $result->fetch_assoc()) {
				$array[$row[$keyField]] = Core::clean($row[$valueField]);
			}
			
		}
		
		return $array;
		
	}
	



	/** get_select_structure
	*	
	*	builds a select list structure for form select elements. We will assume that all data models usig this function use id, name, parent and position as columns.
	*
	*	$table: table from which to retrieve select list
	*	$parentId: id that contains structure to retrieve
	*
	*/
	public function get_select_structure($table, $parentId = 1, $keyField = 'id', $valueField = 'name', $whereClause = NULL, $indent = NULL) {
		
		$whereStr = $whereClause ? ' AND ' . $whereClause : '';
		$structureQuery = '
			SELECT `' . $keyField . '`, `' . $valueField . '` 
			FROM '. $table . ' 
			WHERE parent = ' . $parentId . $whereStr . ' 
			ORDER BY position
		';
		$structureRes = $this->query($structureQuery);
		
		if ($structureRes->num_rows >= 1) {
			
			if (!isset($this->selectArr)) {
				$this->selectArr = array();
			}
			
			$indentNext = $indent . str_repeat(chr(160), 6);
			
			while ($row = $structureRes->fetch_assoc()) {
	
				// BUILD: ul list
				$this->selectArr[$row[$keyField]] = $indent . '• ' . $row[$valueField];

				$this->get_select_structure($table, $row[$keyField], $keyField, $valueField, $whereClause, $indentNext);
	
			}
	
			return $this->selectArr;
			
		} else {
		
			// last node in branch
			return false;
			
		}
	
	}





//------------------------------------------------------------------------------------------------------------------
// DESTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
		
	/** __desctruct()
	*	
	*	Close the current connection and release used memory
	*	
	*/
	function __destruct() {
	
		// CLOSE: this db connection
		$this->close();
		
	}
	
	
}
?>