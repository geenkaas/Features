<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** validator.php
	
	This is an static class with the validation functions used by all objects and procedural processes.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Validator {

//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** match(): checks if string validates to an predefined or freshly passed patern
	*
	*	$pattern: patern name or cgi regex patern
	*
	*/
	static function match($pattern, $var) {
		
		switch ($pattern) {
		
			case 'id':
				$pattern = '/^[0-9]+$/';
			break;
		
			case 'name':
				$pattern = '/^([a-zA-ZÀ-ÿ0-9]|(\s)|[-\'\.\/&_,!])*$/';
			break;
		
			case 'sentence':
				$pattern = '/^([a-zA-ZÀ-ÿ0-9]|(\s)|[-@\'\"\.\+\/&:;_,!\]\[\)\(\?])*$/';
			break;
		
			case 'doublesentence':
				$pattern = '/^(([a-zA-ZÀ-ÿ0-9]|(\s)|[-@\'\"\.\+\/&_,!\]\[\)\(\?])*)(\|)(([a-zA-ZÀ-ÿ0-9]|(\s)|[-@\'\"\.\+\/&_,!\]\[\)\(\?])*)$/';
			break;
							  	
			case 'parameter':
				$pattern = '/^(\w{1,60}(-|_){0,1}\w{0,60}){1,10}$/';
			break;
			
			case 'getParameter':
				$pattern = '/^_\w{1,60}_(\w{0,60}(-| |_|&|%|\.|\+){0,1}\w{0,60}){1,10}$/';
			break;
			
			case 'sortValue':
				$pattern = '/^(\w{0,60}(-|_|\.){0,1}\w{0,60}){1,10}$/';
			break;
			
			case 'fileStrict':
				$pattern = '/^(\w{1,60}(-|_| |.){0,1}\w{1,60}){1,5}(\.)(\w){2,4}$/';
			break;
			
			case 'languageParameter':
				$pattern = '/^en|nl|es|fr|de$/';
			break;
			
			case 'email':
				$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
			break;
			
			case 'uri':
				$pattern = '"^((http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+)?([\w\-\.,@?^=%&amp;:/~\+#&]*[\w\-\@?^=%&amp;/~\+#&])?"';
			break;
	  	
			case 'phone':
				$pattern = '/^(\+){0,1}(\d|\)|\(| ){1,20}$/';
			break;
						
			case 'ddmmyyyy':
				$pattern = '/^(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])$/';
			break;
			
			case 'mmddyyyy':
				$pattern = '/^(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])$/';
			break;
			
			case 'time':
				$pattern = '/^([0-2]{1})([0-9]{1})(:)([0-5]{1})([0-9]{1})$/';
			break;

			case 'float':
				$pattern = '/^([0-9]+)\.?([0-9]{0,2})$/';
			break;
			
			case 'postalCodeNl':
				$pattern = '/^([1-9]{1})([0-9]{3})( )([a-zA-Z]{2})$/';
			break;			
			
			case 'password':
				$pattern = '/^[a-zA-ZÀ-ÿ0-9]*$/';
			break;
		
			case 'exactSearch':
				$pattern = '/^"[^"]{2,50}"$/';
			break;
		
			case 'cleanSearch':
				$pattern = '/^(\w{1,60}(-|_| ){0,1}\w{0,60}){1,10}$/';
			break;
		
			case 'bsv':
				$pattern = '/^(\w+)(\|\w+)*$/';
			break;
			
			case 'bsf': // bars separated filenames
				$pattern = '/^(\w{1,60}(-|_|.){0,1}\w{1,60}){1,5}(\.)(\w){2,4}(\|(\w{1,60}(-|_|.){0,1}\w{1,60}){1,5}(\.)(\w){2,4})*$/';
			break;
			
			case 'piped': // strict pipe separated
				$pattern = '/^(\|(\w{1,60}))+(\|)$/';
			break;
		
			case 'viewFile':
				$pattern = '/\.view\.php$/';
			break;
		
			case 'moduleFile':
				$pattern = '/\.module\.php$/';
			break;
		
			case 'md5':
				$pattern = '/^[a-f0-9]{32}$/';
			break;
		
			case 'geoCode':
				$pattern = '/^-?([0-9]+)\.?([0-9]{0,30}), -?([0-9]+)\.?([0-9]{0,30})$/';
			break;
						
			default: // pattern is considered an real pattern
			break;
		
		}

		if (preg_match($pattern, $var)) {
			return true;
		} else {
			return false;
		}
		
	}


	/** match_if_set()
	*
	*	Checks if string is set validates to an predefined or freshly passed patern
	*	If not you can have it set to null
	*
	*	$pattern: patern name or cgi regex patern
	*
	*/
	static function match_if_set($pattern, &$var, $setTo = 'NONE') {
		
			
		// CHECK: if var isset
		if (isset($var)) {
			if ($pattern == 'array') {
				$result = is_array($var);
			} elseif ($pattern == 'object') {
				$result = is_object($var);
			} else {
				$result = self::match($pattern, $var);
			}
			if ($result != true) {
				if ($setTo != 'NONE') { $var = $setTo; }
			}
			return $result;
		} else {
			if ($setTo != 'NONE') { $var = $setTo; }
			return false;
		}		
		
	}


	/** equal_if_set()
	*
	*	Checks if string is set validates to a value or an array index value
	*	If not you can have it set to NULL.
	*	$value: boolean,string, integer, float or an array of the previous
	*
	*/
	static function equal_if_set($value, &$var, $setTo = 'NONE') {
			
		// CHECK: if var isset
		if (isset($var)) {
			
			if (!is_array($value)) {
				
				// CHECK: value
				if ($var == $value) {
					return true;
				} else {
					if ($setTo != 'NONE') { $var = $setTo; }
					return false;
				}
			
			} else {
				
				// CHECK: value
				if (in_array($var, $value)) {
					return true;
				} else {
					if ($setTo != 'NONE') { $var = $setTo; }
					return false;
				}
			
			}
		
		} else {
			if ($setTo != 'NONE') { $var = $setTo; }
			return false;
		}		
		
	}


	/** escape_regex()
	*
	*	Escapes special regular expression characters in a string that needs to be interpreted as text
	*
	*/
	static function escape_regex($string) {
		
		// DEFINE: characters array
		$specialCharactersArr = array('\\', '^', '.', '$', '|', '(', ')', '[', ']', '*', '+', '?', '{', '}', '/');
		$replacementCharactersArr = array('\\\\', '\^', '\.', '\$', '\|', '\(', '\)', '\[', '\]', '\*', '\+', '\?', '\{', '\}', '\/');			
		// REPLACE: special regural expression characters
		if (is_string($string)) {
			$string = str_replace($specialCharactersArr, $replacementCharactersArr, $string);
			return $string;
		} else {
			return false;
		}
		
	}

}
?>