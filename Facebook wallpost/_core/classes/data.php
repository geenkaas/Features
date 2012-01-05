<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** data.php
	
	This is an static class with functions to retrieve & work with data.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Data {


//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** get_breadcrumb_array()
	*	
	*	Retrieves the the breadcrumb for a hierargical structure
	*	REVIEW
	*	
	*/
	static function get_breadcrumb_array($table, $id, $fieldA, $fieldB, $parentField = 'parent') {
	
		// IMPORT: db connection
		$db = Db::get_instance();
		
		// GET: breadcrumb data
		$breadcrumbRes = $db->query('
			SELECT pA.'.$fieldA.' `0`, pA.'.$fieldB.' `1`, pB.'.$fieldA.' `2`, pB.'.$fieldB.' `3`, pC.'.$fieldA.' `4`, pC.'.$fieldB.' `5`, pD.'.$fieldA.' `6`, pD.'.$fieldB.' `7`, pE.'.$fieldA.' `8`, pE.'.$fieldB.' `9`
			FROM .'.$table.' pA LEFT JOIN .'.$table.' pB ON pA.'.$parentField.' = pB.'.$fieldA.' 
			LEFT JOIN .'.$table.' pC ON pB.'.$parentField.' = pC.'.$fieldA.' 
			LEFT JOIN .'.$table.' pD ON pC.'.$parentField.' = pD.'.$fieldA.' 
			LEFT JOIN .'.$table.' pE ON pD.'.$parentField.' = pE.'.$fieldA.'
			WHERE pA.'.$fieldA.' = ' . $id
		);
		
		return $breadcrumbRes->fetch_assoc();
	
	}


	/** make_parameter_from()
	*	
	*	Generates a parameter from a normal string
	*	
	*/
	static function make_parameter_from($string, $maxChar = 250) {
	
		if (!empty($string)) {
			$string = str_replace('\'', '', $string);			// remove apostrofs i.e. mama's soup -> mamas soup
			$string = preg_replace('/[^\w]+/', '-', $string);	// remove all non-alfanumericals i.e. mamas soup -> mamas-soup
			$string = strtolower(substr($string, 0, $maxChar));
			$string = trim($string, '-');
			return $string;
		} else {
			trigger_error('String passed to get_parameter_from() was empty', E_USER_WARNING);	
			return false;
		}
	
	}


	/** pipe()
	*	
	*	Generates a piped string from an array
	*	
	*/
	static function pipe($array) {
	
		if (count($array)) {
			return '|' . implode('|', $array) . '|';
		} else {	
			return false;
		}
		
	
	}


	/** unpipe()
	*	
	*	Generates an array from a piped string
	*	
	*/
	static function unpipe($string) {

		if ($string) {
			return explode('|', trim($string, '|'));
		} else {
			return array();
		}
	
	}	
}
?>