<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** debug.php
	
	This is an static class with the general functions used during development.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Debug {


//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** trace() 
	*
	*	Throws requested variables to the screen using javascript: alert(), exits afterwards
	*
	*	$var: $variable or array of variables to trace
	*
	*/
	static function trace($var) {
		
		// PREPARE: to trace
		$output = '';
		if (is_array($var)) {
			
			foreach ($var as $value) {
			
				if (is_object($value) || is_array($value)) {
					
					ob_start();
					print_r($value);
					$contents = ob_get_contents();
					ob_end_clean();
					
					$output .= str_replace(array("\n","\t"), array('\n','\t'), $contents) . '\n';
					
				} else {				
					$output .= $value . '\n';
				}
				
			}
			
		} else {
			
			if (is_object($var) || is_array($var)) {
				
				ob_start();
				print_r($var);
				$contents = ob_get_contents();
				ob_end_clean();
				
				$output .= str_replace(array("\n","\t",'\''), array('\n','\t','`'), $contents) . '\n';
				
			} else {				
				$output .= $value . '\n';
			}
		
		}			
		
		echo '<script type="text/javascript">alert(\'' . trim($output,'\n') . '\');</script>';
		exit;
	
	}
	

	
}
?>