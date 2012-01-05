<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed to datagrid.php', E_USER_ERROR); }
/** datagrid.php
*	
*	The data grid is inteded for display and interaction with data in a tabular form.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class DataGrid extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$id; // id
	protected	$dataArr;	// array of data to be displayed in table
	protected	$headerArr; // array of headers for the grid table
	protected	$extraGetArr = array('event'); // array of extra GET variables to pass in URL
	protected	$cellspacing = 0; // grid table cellspacing in pixels
	protected	$cssPrefix; // prefix to prepend to CSS classes
	protected	$searchUrl; // set a search URL or leave empty for default
	
	// DEFINE: process fields
	protected	$quantity;			// Quantity of matches for query, will be set by get method
	protected	$pageLimit; 		// Amount of matches per page, will be set by get method
	protected	$pagesToDisplay = 11; 	// Amount of steps in paging navigation
	protected	$allowSearch; 		// Allow search, will be set by get method. If true, search box will be displayed
	


//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Sets the grid id.
	*	
	*/
	function __construct($id) {
	
		// SET: grid id
		$this->id = $id;
		
		// VALIDATE: page
		if (!isset($_GET['page'])) {
			$_GET['page'] = 1;
		} elseif (!is_numeric($_GET['page'])) {
			$_GET['page'] = 1;
		}
		
		// VALIDATE: sort / sortDesc
		if (!isset($_GET['sort'])) {
			$_GET['sort'] = NULL;
		}
		if (!isset($_GET['sortDesc'])) {
			$_GET['sortDesc'] = NULL;
		} 
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// ADD METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** add_column()
	*	
	*	Add column headers.
	*	
	*/
	public function add_column($name, $displayName, $sortColumnName = false) {
		
		$this->headerArr[$name][0] = $displayName;
		$this->headerArr[$name][1] = $sortColumnName;
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// GET METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** get_attributes_from()
	*	
	*	Use to get quantity and pageLimit set directly from this objects' data-set companion. Needs to be called manually 
	*
	*/
	public function get_attributes_from(DataSet $object) {   
		
			$this->quantity = $object->tell('quantity');
			$this->pageLimit = $object->tell('pageLimit');
			$this->allowSearch = $object->tell('allowSearch');
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// DISPLAY METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/** display_navigation()
	*	
	*	Page navigation for our data grid. 'Record limit start' is passed on and also any other GET vars set as '$get_vars' 
	*
	*/
	public function display_navigation() {
		
		// IMPORT: RequestHandler for URI building
		$requestHandler = RequestHandler::get_instance();
		
		// DO: some calculations
		$pageCount = ceil($this->quantity / $this->pageLimit);
		$pagesInFront = floor(($this->pagesToDisplay - 1) / 2);
		$pagesInBack = ceil(($this->pagesToDisplay - 1) / 2);
		
		// PREPARE: array of GET values to pass through
		$passThroughGetArr = array_merge(array('sort','sortDesc','search'), $this->extraGetArr);
		
		// BUILD: previous link
		if ($_GET['page'] > 1) {
			$navigation = '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, array('page'=>$_GET['page']-1) ) . '"><strong>&laquo; ' . STRING_MISC_PREVIOUS . '</strong></a> | ' . "\n";
		} else { 
			$navigation = '<strong>&laquo; ' . STRING_MISC_PREVIOUS . '</strong> | ' . "\n"; 
		}
		
		// BUILD: page links
  		if (($_GET['page']-$pagesInFront) <= 1  || $pageCount <= $this->pagesToDisplay) {
			for ($pageNumber = 1; $pageNumber <= $pageCount && $pageNumber <= $this->pagesToDisplay; $pageNumber++) {
				
				$highlight = ($pageNumber == $_GET['page']) ? ' class="highlight"' : '';
				$navigation .= '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, array('page'=>$pageNumber) ) . '"' . $highlight . '>' . $pageNumber . '</a> | ' . "\n";
			
			}
		} elseif (($_GET['page']-$pagesInFront) > 1 && ($_GET['page']+$pagesInBack) <= $pageCount) {
			for ($pageNumber = ($_GET['page']-$pagesInFront); $pageNumber <= ($_GET['page']+$pagesInBack); $pageNumber++) {
				
				$highlight = ($pageNumber == $_GET['page']) ? ' class="highlight"' : '';
				$navigation .= '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, array('page'=>$pageNumber) ) . '"' . $highlight . '>' . $pageNumber . '</a> | ' . "\n";
			
			}		
		} else {
			for ($pageNumber = $pageCount-$this->pagesToDisplay; $pageNumber <= $pageCount; $pageNumber++) {
				
				$highlight = ($pageNumber == $_GET['page']) ? ' class="highlight"' : '';
				$navigation .= '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, array('page'=>$pageNumber) ) . '"' . $highlight . '>' . $pageNumber . '</a> | ' . "\n";
			
			}		
		}
  
  		// BUILD: next link
		if ($_GET['page'] < $pageCount) {
			$navigation .= '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, array('page'=>$_GET['page']+1) ) . '"><strong>' . STRING_MISC_NEXT . ' &raquo;</strong></a>' . "\n";
		} else { 
			$navigation .= '<strong>' . STRING_MISC_NEXT . ' &raquo;</strong>' . "\n";
		}
		
		return '
			<div  id="' . $this->id . 'NavigationDiv" class="gridNavigation">'. 
				$navigation . '
			</div>
		';
		
	}


	/** display_table()
	*	
	*	Display data table and header row specified in 'headerArr'
	*
	*/	
	public function display_table() {
		
		// IMPORT: RequestHandler for URI building
		$requestHandler = RequestHandler::get_instance();

		$table = '
			<table cellspacing="' . $this->cellspacing . '" class="gridTable" id="' . $this->id . 'GridTable">
		';
		
		// PREPARE: array of GET values to pass through
		$passThroughGetArr = array_merge(array('search'), $this->extraGetArr);
				
		// BUILD: headers and sort buttons
		if (is_array($this->headerArr)) {   
			
			$table .= '<tr>';
			
			foreach ($this->headerArr as $columnName => $columnAttributeArr) {  
				
				$table .= '<th class="' . $columnName . 'Th">';
				
				if ($columnAttributeArr[1]) {
					
					if ($_GET['sort'] == $columnAttributeArr[1] && $_GET['sortDesc'] != 'yes') {
						$sortGetArr = array('sort'=>$columnAttributeArr[1], 'sortDesc'=>'yes');
						$sortClass = 'sorted';
					} else {
						$sortGetArr = array('sort'=>$columnAttributeArr[1]);
						$sortClass = $_GET['sort'] == $columnAttributeArr[1] ? 'sortedDesc' : '';
					}
					
					$table .= '<a href="' . $requestHandler->make_uri(false, false, $passThroughGetArr, $sortGetArr) . '" class="' . $sortClass . '">' . $columnAttributeArr[0] . '</a></th>';
				
				} else {
					$table .= $columnAttributeArr[0] . '</th>' . "\n";
				}
				
			}
			$table .= '</tr>' . "\n";
			
		} else { 
			// ERROR: no columns added to DataGrid object
			trigger_error('No columns added to DataGrid object', E_USER_WARNING);
		}
		
		
		// BUILD: table grid
		if (is_array($this->dataArr) && count($this->dataArr) > 0) {  
		
			foreach ($this->dataArr as $key => $rowArr) {
				
				$class = (Core::is_odd($key) ? 'evenRow' : 'oddRow');
				$table .= '<tr class="' . $class . ' jHighlight">' . "\n";
				
				// BUILD: rows with only columns from header
				foreach ($this->headerArr as $columnName => $columnAttributeArr) {
					$table .= '<td class="'.$columnName.'Td">' . (($rowArr[$columnName] != '') ? $rowArr[$columnName] : '&nbsp;') . '</td>' . "\n";
				}
				
				$table .= '</tr>' . "\n";
				
			}
			
		} else { 
			$table .= '<tr><td colspan="' . count($this->headerArr) . '">' . STRING_MISC_NO_RESULTS . '</td></tr>' . "\n";
		}
		
		$table .= '</table>' . "\n";
	
		return $table;
	
	}


	/** display_search_box()
	*	
	*	Display: Search box if search is allowed
	*
	*/	
	public function display_search_box() {
	
		if ($this->allowSearch) {
			
			// IMPORT: RequestHandler for URI building
			$requestHandler = RequestHandler::get_instance();
			
			// BUILD: search form
			$url = ($this->searchUrl) ? $this->searchUrl : $requestHandler->make_uri(false, false, $this->extraGetArr);
			$searchForm = '
				<a href="' . $url . '_action_clear/" title="Clear search"><img id="clear" class="searchButton" src="' . WS_PATH_ICONS . 'cancel_blue.png" alt="Clear search" /></a>
				<a href="' . $url . '" class="jImageInputSubmit" title="Search"><img id="searchNow" class="searchButton" src="' . WS_PATH_ICONS . 'zoom.png" alt="search" /></a>
				<input type="text" class="searchInput jImageInputSubmit" id="' . $this->id . 'SearchInput" value="' . (isset($_GET['search']) ? Core::output(urldecode($_GET['search'])) : '') . '" />
			';
			
			return $searchForm;
		
		} else {
		
			return false;
			
		}
	
	}
	
	
}
?>