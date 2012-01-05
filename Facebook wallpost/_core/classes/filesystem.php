<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** filesystem.php
	
	This is an static class with functions to access the filesystem and work with files and folders.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Filesystem {


//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** dir_array()
	*	
	*	Reads a directory's contents into an array. Takes out the '.' and '..' occurances and any other files and directories you'd like.
	*	
	*	$dir_path: FS path to directory
	*	$excl_arr: array of names to be excluded, default is false
	*	$key: either an ID or the FILE name can can be used as parameters for  the array key
	*	$pattern: regular expression pattern or named (Validator class) pattern files need to match
	*
	*/
	static function dir_array($dirPath, $exclArr = false, $key='FILE', $pattern='/.*/') {
	
		if (is_array($exclArr)) {
			$exclString = implode(' ', $exclArr);
		} else  {
			$exclString = '';
		}
	  
		$i = 0;
		$dh = opendir($dirPath);
		if($dh == true) {
			
			$array = array();
			while($dirContents = readdir($dh)) {
				
				if(!strstr($exclString, $dirContents) && $dirContents !='.' && $dirContents != '..' && Validator::match($pattern, $dirContents)) { 
			
					
					// BUILD: array
					if ($key == 'FILE') {
						$array[$dirContents] = $dirContents;
					} else {
						$array[$i] = $dirContents;
						$i++;
					}
				
				}
			}
			
			closedir($dh);
		
		} else {
			trigger_error('This directory does not exist or is not readable', E_USER_ERROR);
		}
		
		return $array;
		
	}


	/** delete_file()
	*	
	*	Uses unlink to dele a file and has error/success reporting
	*
	*	$file: Full path to file
	*
	*/
	static function delete_file($file) {
	
		// IMPORT: the process class for process monitoring and error notification
		$process = Process::get_instance();		
	
		if (file_exists($file)) {
			
			$deleted = unlink($file);
			
			if ($deleted == true) { 
				$process->add_note(STRING_CLASS_FILESYSTEM_DONE_FILE_DELETE, 'DONE');
				return true;
			} else {
				$process->add_note(STRING_CLASS_FILESYSTEM_FAIL_FILE_DELETE, 'FAIL');
				return false;
			}
		
		} else {
			$process->add_note(STRING_CLASS_FILESYSTEM_FAIL_FILE_NOT_FOUND, 'FAIL');
			return false;
		}
	
	}


	/** get_extension()
	*
	*	$file: filename or filepath
	*
	*/
	static function get_extension($file) {
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		return strtolower($extension);
	}


	/** write_file()
	*
	*	$file: filename or filepath
	*	$string: file content
	*	$mode: file open mode
	*
	*/
	static function write_file($path, $string, $mode = 'w') {
		$fp = fopen($path, $mode);
		$result = fwrite($fp, $string);
		fclose($fp);
		return $result;
	}
	
}
?>