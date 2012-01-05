<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** downloader.php
*	
*	The downloader object facilites forced downloads
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Downloader extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected $filePath;




//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD	
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()	
	*
	*	The constructor sets setup variables.
	*	
	*/
	public function __construct($filePath) {
		
		$this->filePath = $filePath;
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// PERFOM	
//------------------------------------------------------------------------------------------------------------------
	
	/** perform() 
	*
	*	Set object properties, set $clean to clean value.
	*
	*/
	public function start() {
	
		// IMPORT: db and process objects
		$process = Process::get_instance();
		
		//echo basename($this->filePath); exit;
		if (file_exists($this->filePath)) {
			
			// SET: headers
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($this->filePath).'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: '.filesize($this->filePath));
			
			// CLEAN: buffer
			ob_clean();
			flush();
			
			// OUTPUT: file and exit
			readfile($this->filePath);
			exit;
		
		} else {
		
			// NOTIFY: file not found
			$process->add_note(STRING_CLASS_DOWNLOADER_FAIL_NOT_FILE, 'FAIL');
			return false;
			
		}
	
	}	
	
}
?>