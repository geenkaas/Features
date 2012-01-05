<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** navigation.php
*	
*	This is a navigation building class for a parent/child structure.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Navigation2 extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$query = '
		SELECT a.*, CASE a.`itemType` WHEN 1 THEN b.`name` ELSE a.`url` END AS `url` 
		FROM [[TABLE]] a LEFT JOIN [[TABLE]] b ON a.url = b.id
		WHERE a.`parent` = [[PARENT]] AND [[PERMISSIONS]] AND a.`display` != 0 [[FILTER]] 
		ORDER BY a.`position` IS NULL, a.`position`
	';
	protected	$listTable;
	protected	$guestGroupId;
	protected	$userGroupId;
	protected	$listLevels;			// empty is default and doesn't limit the depth
	protected	$listType;
	protected	$language = LANGUAGE;
	protected	$classPrefix = 'navLevel';
	protected	$activePage = PAGE;
	
	protected	$submenuId;
	



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Sets different passed values and edits the query using str_replace.
	*	
	*/
	function __construct($table = 'pages', $guestGroupId, $userGroupId) {

		// SET: vars
		$this->listTable 	= $table;
		$this->guestGroupId = $guestGroupId;
		$this->userGroupId 	= $userGroupId;
		
		// PREPARE: the query string
		$this->query = str_replace('[[TABLE]]', $table, $this->query);
		$guestString = '
			a.permittedGroups = \'0\' OR
			a.permittedGroups LIKE \''.$guestGroupId.'|%\' OR 
			a.permittedGroups LIKE \'%|'.$guestGroupId.'\' OR 
			a.permittedGroups LIKE \'%|'.$guestGroupId.'|%\' 
		';
		$userString = ($userGroupId && LOGGED_IN == true) ? '
			 OR 
			a.permittedGroups = \''.$userGroupId.'\' OR
			a.permittedGroups LIKE \''.$userGroupId.'|%\' OR 
			a.permittedGroups LIKE \'%|'.$userGroupId.'\' OR 
			a.permittedGroups LIKE \'%|'.$userGroupId.'|%\' 
		' : '';
		$this->query = str_replace('[[PERMISSIONS]]', '('.$guestString.$userString.')', $this->query);
		
	}
	



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** get_list()
	*	
	*	Builds an xhtml list structure for navigation
	*
	*
	*/
	public function get_list($type = 'horizontal', $parent = 1, $level = 0, $levels = 1) {
		
		$this->listLevels = $levels;
		
		switch ($type) {
			
			case 'vertical':
				return $this->get_multi_list($type, $parent, $level);
			break;
			
			case 'horizontal':
				return $this->get_multi_list($type, $parent, $level);
			break;
			
			case 'simple':
				return $this->get_simple_list($parent);
			break;	
			
		}
		
	}


	
	/** get_multi_list()
	*	
	*	Builds an xhtml list structure for navigation
	*
	*
	*/
	protected function get_multi_list($type, $parent, $level) {
		
		// IMPORT:  Db (global) instance & RequestHandler for URI building
		$requestHandler = RequestHandler::get_instance();		
		$db = Db::get_instance();
		
		// SET: current level
		$level++;
				
		// PREPARE: the query string
		$query = str_replace('[[PARENT]]', $parent, $this->query);
		$query = (LOGGED_IN == true) ? str_replace('[[FILTER]]', 'AND a.displayAfterLogin = 1', $query) : str_replace('[[FILTER]]', '', $query); 
	
		// BUILD: lists and list items
		if ($level > $this->listLevels && !empty($this->listLevels)) { // allowed levels reached
			return false;
		} elseif($structureRes = $db->query($query)) { // get data
		
			if ($structureRes->num_rows >= 1) {

				$openList 	= NULL;
				$list		= NULL;
				$listGroup 	= NULL;
		
				while ($row = $structureRes->fetch_assoc()) {
		
					$subList = $this->get_multi_list($type, $row['id'], $level);
					$highlight = (Core::clean($row['name']) == $this->activePage || strpos($subList, 'class="highlight')) ? 'highlight' : '';
					$openList = ($highlight) ? ' openList' : $openList;
									
					if ($row['itemType'] == 2) {
						$link =  $requestHandler->make_uri( array('PAGE'=>Core::clean($row['name'])) );
					} elseif ($row['itemType'] == 3 && Validator::match('email', Core::clean($row['url']))) {
						$link =  'mailto:' . Core::clean($row['url']);
					} elseif ($row['itemType'] == 3 && Core::clean($row['url'])) {
						$link =  Core::clean($row['url']);
					} elseif ($row['itemType'] == 1 && Core::clean($row['url'])) {
						$link =  $requestHandler->make_uri( array('PAGE'=>Core::clean($row['url'])) );
					} else {
						$link = '#';
					}
					
					// BUILD: list
					$listGroup .= '
						<li>
							<a href="' . $link . '" class="' . $highlight . '" id="navLink'.$row['id'].'">' . Core::output($row[$this->language . 'Label']) . '</a>' .
							$subList . '
						</li>' . "\n"
					;	
		
				}
				
				// GENERATE: id
				if ($level >= 2) {
					$this->submenuId++;
					$id = ' id="smenu' . $this->submenuId . '"';
				} else {
					$id = '';
				}
				
				// Build: ul list
				$list .= '<ul class="' . $this->classPrefix . $level . $openList . '"' . $id . '>' . "\n";
				$list .= $listGroup;
				$list .= '</ul>' . "\n";
				
				return $list;
				
			} else {
			
				// STOP: last node in branch
				return false;
				
			}
		
		} else {
			
			// ERROR: no result (not even empty)
			return false;
			
		}
	
	}


	
	/** get_simple_list()
	*	
	*	Builds an xhtml list structure for navigation containing 1 level
	*
	*/
	public function get_simple_list($parent, $currentPageId, $currentPageName = PAGE) {
				
		// IMPORT:  Db (global) instance & RequestHandler for URI building
		$requestHandler = RequestHandler::get_instance();		
		$db = Db::get_instance();
				
		// PREPARE AND EXECUTE: the query string
		$query = str_replace('[[PARENT]]', $parent, $this->query);
		$query = (LOGGED_IN == true) ? str_replace('[[FILTER]]', 'AND a.displayAfterLogin = 1', $query) : str_replace('[[FILTER]]', '', $query);
		
	
		// BUILD: lists and list items
		if($structureRes = $db->query($query)) { // get data
		
			if ($structureRes->num_rows >= 1) {

				$list = '';
				$listGroup = '';
				
				// GET: arrat of items in parent srtucture. these should also be highlighted in the navigation
				$breadcrumbArr = Data::get_breadcrumb_array('`pages`', $currentPageId, 'id', 'id', 'parent');
		
				while ($row = $structureRes->fetch_assoc()) {
					
					$highlight = (Core::clean($row['name']) == $this->activePage || in_array($row['id'],$breadcrumbArr)) ? ' highlight' : '';
									
					if ($row['itemType'] == 2) {
						$link =  $requestHandler->make_uri( array('PAGE'=>Core::clean($row['name'])) );
					} elseif ($row['itemType'] == 3 && Validator::match('email', Core::clean($row['url']))) {
						$link =  'mailto:' . Core::clean($row['url']);
					} elseif ($row['itemType'] == 3 && Core::clean($row['url'])) {
						$link =  Core::clean($row['url']);
					} elseif ($row['itemType'] == 1 && Core::clean($row['url'])) {
						$link =  $requestHandler->make_uri( array('PAGE'=>Core::clean($row['url'])) );
					} else {
						$link = '#';
					}
					
					// BUILD: list
					$listGroup = str_replace('class="last ', 'class="', $listGroup);
					$listGroup .= '
						<li class="last P'.$row['id'].'">
							<a href="' . $link . '" class="navLink'.$row['id'] . $highlight .'">' . Core::output($row[$this->language . 'Label']) . '</a>
						</li>' . "\n"
					;	
		
				}
				
				// Build: ul list
				$list .= '<ul class="' . $this->classPrefix . '">' . "\n";
				$list .= $listGroup;
				$list .= '</ul>' . "\n";
				
				return $list;
				
			} else {
			
				// STOP: last node in branch
				return false;
				
			}
		
		} else {
			
			// ERROR: no result (not even empty)
			return false;
			
		}
	
	}

	
}
?>