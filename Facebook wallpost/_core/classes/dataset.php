<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** dataset.php
*	
*	This is a mold for all classes to be build on.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class DataSet extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$select; // columns part of SELECT query
	protected	$from; // tables part of SELECT query
	protected	$where; // where clause part of SELECT query
	protected	$groupBy; // grouping part of SELECT query
	protected	$having; // restricting part of SELECT query
	protected	$orderBy; // sorting part of SELECT query
	protected	$limit; // count limitation part of SELECT query
	
	protected	$allowSearch; // allow user to search within a column selection, FALSE by default
	protected	$searchColumnArr; // array of columns in which to search, do not enclose strings
	protected	$pageLimit = 20; // Limit data set to a maximum of rows

	// DEFINE: process fields
	protected 	$queryRes = false; // the result data set will be stored in $result
	protected 	$quantity = false; // store total quantity of matching rows



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	This is the function description for the constructor.
	*	
	*/
	function __construct() {
	
		/* Class instantiation code */
		
	}
	



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** perform()
	*	
	*	Get data requested in set variables and send vaiables as SORT and SEARCH.
	*	
	*/
	public function perform() { 
			
		// IMPORT: database functionality
		$db = Db::get_instance();
		
		// BUILD: dataset select query
		if (isset($this->select) && isset($this->from)) {
		
 			// SET: limit if page is passed
			if (isset($this->pageLimit)) {     
				
				$_GET['page'] = !isset($_GET['page']) ? 1 : $_GET['page'];
				if ($_GET['page'] > 0) {
					$this->limit = ($_GET['page']-1) * $this->pageLimit . ', ' . $this->pageLimit;
				} else {
					$this->limit = '0, ' . $this->pageLimit;
				}

			}
			
 			// SET: replace orderBy if sort is passed
			if (isset($_GET['sort'])) {
			
				if (Validator::match('sortValue', $_GET['sort'])) {
					
					$this->orderBy = Core::input($_GET['sort']);
				
					if (isset($_GET['sortDesc'])) {
						$this->orderBy .= ($_GET['sortDesc'] == 'yes') ? ' DESC' : '';
					}
				
				}
			
			}
 		
  			// SET: replace where clause if search is passed
			if ($this->allowSearch === true && is_array($this->searchColumnArr) && isset($_GET['search'])) {
				
				// START
				$searchStr = '';
				
				// CHECK: if exact search [[REVIEW]]
				if (Validator::match('exactSearch', $_GET['search'])) {
				
					// PREPARE: where string addition
					$searchBit = Core::input(trim($_GET['search'], '"'));
					foreach ($this->searchColumnArr as $searchColumn) {
						$searchStr .= $searchColumn . ' LIKE \'%' . $searchBit . '%\' OR ';
					}
					
				} else {

					// CLEAN: Stops atemps at sql injection
					$_GET['search'] = Core::clean_search($_GET['search']);
					
					if (Validator::match('cleanSearch', $_GET['search'])) { 
	
						$searchBitArr = explode(' ', $_GET['search']);
						
						foreach ($this->searchColumnArr as $searchColumn) {
						
							foreach ($searchBitArr as $searchBit) {
								
								if (!empty($searchBit) && !empty($searchColumn)) {
									$searchStr .= $searchColumn . ' LIKE \'%' . Core::input($searchBit) . '%\' OR ';
								}
								
							}
							
						} 													
					
					}			
				
				}

				$searchStr = trim($searchStr, ' OR ');
					
				// ADD: search string to where clause
				$this->where .= (!empty($this->where) && !empty($searchStr)) ? ' AND (' . $searchStr . ')' : $searchStr;
				
			}
			
			// BUILD: select query
			$selectQuery = '
				SELECT ' . $this->select . ' 
				FROM ' . $this->from .
				(!empty($this->where)	? ' WHERE ' . $this->where : '') . 
				(!empty($this->groupBy) ? ' GROUP BY ' . $this->groupBy : '') . 
				(!empty($this->having) 	? ' HAVING ' . $this->having : '') .  
				(!empty($this->orderBy) ? ' ORDER BY ' . $this->orderBy : '') .
				(!empty($this->limit)	? ' LIMIT ' . $this->limit : '')
			;
			
			// GET: DATA
			$this->queryRes = $db->query($selectQuery);

			
			// Build query to retrieve quantity
			$quantityQuery = '
				SELECT ' . $this->select . ' 
				FROM ' . $this->from .
				(!empty($this->where)	? ' WHERE ' . $this->where : '') . 
				(!empty($this->groupBy) ? ' GROUP BY ' . $this->groupBy : '') . 
				(!empty($this->having) 	? ' HAVING ' . $this->having : '')
			;			
		
			// GET: DATA
			$quantityRes = $db->query($quantityQuery);
			$this->quantity = $quantityRes->num_rows;

			
			return $this->queryRes;
		
		}
	
	}

	
}
?>