<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** core.php
	
	This is an static class with the general functions used by all objects and procedural processes.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Core {


//------------------------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/** input(): adds slashes to control chars in string
	*
	*	$string: string to parse
	*
	*/
	static function input($string) {
		
		// GET: Db (global) instance
		$db = Db::get_instance();
		
		if (is_object($db)) {
			return $db->real_escape_string($string);
		} else {
			return addslashes($string);
		}
	
	}
	

	/** output(): rips slashes, then converts htmlchars
	*
	*	$string: string to parse
	*
	*/	
	static function output($string) {
	
	  	return htmlspecialchars(stripslashes($string));
	}
	

	/** output_substr(): rips slashes, then converts htmlchars for substring
	*
	*	$string: string to parse
	*
	*/	
	static function output_substr($string, $length) {
		
		$string = self::output($string);
		if (strlen($string) > $length) {
			$string = substr($string, 0, $length-3) . '...';
		}
		return $string;
		
	}
		
	
	/** clean(): cleans-up strings and arrays
	*
	*	$var: GET or POST string or array, any other string to be cleaned
	*	
	*/ 
	static function clean($var) {
	
		if (is_string($var)) {
			
			$var = stripslashes($var);  // remove slashes
			return preg_replace("/[ ]+/", ' ', trim($var));  // clean excess whitespace
		
		} elseif (is_array($var) || is_object($var)) {
			
			reset($var);
			foreach ($var as $key => $value) {
				$var[$key] = self::clean($value);
			}
			return $var;
		
		} else {
			return $var;
		}
	  
	}
	
	
	/** clean(): cleans-up strings for searches
	*
	*	$var: GET string to be cleaned
	*	
	*/ 	
	static function clean_search($string) {
	
		$badChrArr = array('~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '-', '+', '=', '{', '}', '[', ']', '|', '\\', ':', ';', '"', '\'', ',', '<', '>', '.', '?', '/');
		return str_replace($badChrArr, ' ', $string);
	
	}
	
	
	/** redirect()
	*	
	*	Uses Header->Location to redirect to new page. First session is closed and afterwards PHP parsing is exited
	*
	*	$location: file URL (only use defined URL locations)
	*
	*/
	static function redirect($location = 'host', $httpStatus = NULL) {
		
		$statusArr = array(
			302 => 'HTTP/1.1 302 Found'
		);
		
		if ($location == 'host') {
			$location = 'http://' . $_SERVER['HTTP_HOST'] . '/';
		}
		session_write_close();
		if (isset($statusArr[$httpStatus])) {
			header($statusArr[$httpStatus]);
		}
		header('Location: ' . $location);
		exit();
		
	}	
	
	
	/** not_empty()
	*	
	*	Checks if variable or array is not null and returns true. 'False, 0, SPACE, NULL' will return false
	*	
	*	$value: variable or array to test
	*
	*/
	static function not_empty($var) {
	
	  if (is_array($var)) {
		if (sizeof($var) > 0) {
		  return true;
		} else {
		  return false;
		}
	  } else {
		if (($var != '') && (strtolower($var) != 'null') && (strlen(trim($var)) > 0)) {
		  return true;
		} else {
		  return false;
		}
	  }
	
	}
	
	
	/** is_odd()
	*	
	*	Checks if variable is odd number
	*	
	*	$number: is a numerical value
	*
	*/
	static function is_odd($number) {
	   return $number & 1; // 0 = even, 1 = odd
	}	
	
	
	/** datetime()
	*	
	*	builds a mysql datetime of now
	*
	*/
	static function datetime() {
	
		return(date("Y-m-d H:i:s"));
	}
	
	
	/** make_random_string()
	*	
	*	Generates a random string the requested character length
	*	If accessible is true, Zeros and the O letters are removed
	*
	*/  
	static function make_random_string($characters = 8, $accessible = true) {
	  
		$numArr = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
		$alfaArrOne = range("a", "n");
		$alfaArrTwo = range("p", "z");
		$alfaCapsArrOne = range("A", "N");
		$alfaCapsArrTwo = range("P", "Z");
		$nonAccessibleArr = array('0', 'o', 'O');
		
		// CHECK: if string needs to be accessible
		if ($accessible) {
			$mergedArr = array_merge($numArr, $alfaArrOne, $alfaArrTwo, $alfaCapsArrOne, $alfaCapsArrTwo);
		} else {
			$mergedArr = array_merge($numArr, $alfaArrOne, $alfaArrTwo, $alfaCapsArrOne, $alfaCapsArrTwo, $nonAccessibleArr);
		}
	  
		$count = count($mergedArr);
		
		// PICK: characters
		for ($i = 0; $i < $characters; $i ++) {
			$string .= $mergedArr[rand(0,($count-1))];
		}
		
		return $string;
	  
	}

	
	
	/**REVIEW GD gdf_timed_greets_v1: Gives the right greeting depending on the part of the day
	
		$lang: language of greeting 'NL' or 'EN'
		$timezone: the timezone of the target vistitors
		$caps: Capitalized or not, default is 'true'
	
	*/
	static function gdf_timed_greets($lang = 'nl', $timezone = 1, $caps = true) {
	
		$hours = gmdate("G", time() + 3600 * ($timezone + date("I")));
		
		if ($hours >= 0) {
			$part_of_day = 0;
		}
		if ($hours >= 6) {
			$part_of_day = 1;
		}
		if ($hours >= 12) {
			$part_of_day = 2;
		}
		if ($hours >= 18) {
			$part_of_day = 3;
		}
		
		if ($lang == 'nl') {
			$greets = array('Goedenavond', 'Goedemorgen', 'Goedemiddag', 'Goedenavond');
		} else {
			$greets = array('Good night', 'Good morning', 'Good afternoon', 'Good evening');
		}
		$greeting = $greets[$part_of_day];
		
		if ($caps === false) {
			$greeting = strtolower($greeting);
		} 
		
		return $greeting;
	
	}
	
}
?>