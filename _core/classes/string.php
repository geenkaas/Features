<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** string.php
	
	This is an static class with the string functions used by all objects and procedural processes.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class String {


//------------------------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/** clear_linebreaks(): clears linebreaks in string with option to turn into html breaks
	*
	*	$string: string to parse
	*
	*/
	static function clear_linebreaks($string, $nl2br = false) {
		
		// Substrings to remove
		$seachArr = array("\n", "\r");
		
		if ($nl2br) {
			$string = nl2br($string);
			return str_replace($seachArr, '', $string);
		} else {
			return str_replace($seachArr, '', $string);
		}
	
	}

	
}
?>