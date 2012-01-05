<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** array.php
	
	This is an static class with array functions used by all objects and procedural processes.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Array {


//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** get_from_result()
	*
	*	Takes a query result and turns it into an assoc array of the two fields you supply.
	*
	*/
	static function get_from_result($result, $keyField, $valueField) {
	
		if ($result) {
			$resultArr = array();
			while ($row = mysql_fetch_assoc($result)) {
				$key 	= $row[$keyField];
				$value	= $row[$valueField];
				$resultArr[$key] = $value;
			}
			return $resultArr;
		} else {
			return false;
		}
		  
	}

   		
}
?>