<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** dataobject.php
*	
*	File uploader class. Handles all kinds of files
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Uploader extends Object {

//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	protected $destinationPath;			// file upload directory path
	protected $destinationFileName;		// saving file name
	protected $maxFileSize;			 	// maximum upload size
	protected $extensionArr;			// array of allowed file extensions
	
	

//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD	
//------------------------------------------------------------------------------------------------------------------

	/** __constructor()	
	*
	*	The constructor initializes the object and checks if the destination path exists 
	*	and also sets the maximum file size
	*	
	*/
	public function __construct($destinationPath = FS_PATH_ASSETS, $maxFileSize = 2000000, $extensionArr=array('jpg','gif','png')) {
	
		// SET: passed data if the upload and location exists
		if (file_exists($destinationPath)) {
			$this->destinationPath = $destinationPath;
		} else {
			trigger_error('The path where the file needs to be uploaded doesn\'t exist', E_USER_ERROR);
		}
		$this->maxFileSize = $maxFileSize;
		$this->extensionArr = $extensionArr;
		
	}



//------------------------------------------------------------------------------------------------------------------
// UPLOAD METHODS
//------------------------------------------------------------------------------------------------------------------

	/** upload()
	*
	*	Uploads a form assigned image to the required destination, Returns the file basename on success
	*	Checks the file extension using nemed paterns or programer defined paterns. Add a new file name to use(without extension)
	*	
	*/
	public function upload($fieldName, $newFileName = false) {
	
		$this->destinationFileName = $newFileName ? $newFileName . '.' . Filesystem::get_extension($_FILES[$fieldName]['name']) : $_FILES[$fieldName]['name'];

		// IMPORT: the process class for process monitoring and error notification
		$process = Process::get_instance();
		
		// CHECK: for uploaded file, type, size or possible atacks 
		if ($_FILES[$fieldName]['tmp_name'] == 'none') {
			// SET: notification temporary file name not available and return false
			$process->add_note(STRING_CLASS_UPLOADER_FAIL_TEMP_NAME);
			return false;
		}

		if ($_FILES[$fieldName]['size'] == 0) {
			// SET: notification and return false
			$process->add_note(STRING_CLASS_UPLOADER_FAIL_NO_SIZE);
			return false;
		}
	  
		if ($_FILES[$fieldName]['size'] > $this->maxFileSize) {
			// SET: notification and return false
			$process->add_note(STRING_CLASS_UPLOADER_FAIL_MAXIMUM_SIZE);
			return false;
		}

		// CHECK: file name extension
		if (isset($this->extensionArr)) {
			
			if (is_array($this->extensionArr)) {
				
				$patern = '/(\.' . implode(')|(\.', $this->extensionArr) . ')$/i';
				
				if (!preg_match($patern, $_FILES[$fieldName]['name'])) {
					
					// SET: notification and return false
					$extensionStr = implode(', ', $this->extensionArr);
					$process->add_note(str_replace('[[TYPES]]', $extensionStr, STRING_CLASS_UPLOADER_FAIL_TYPE));
					return false;
				
				}
			
			} else {
				trigger_error('Uploader allowed extensions var is not an array', E_USER_ERROR);
			}		
	  
		} else {
			trigger_error('Uploader allowed extensions array not set', E_USER_ERROR);
		}

		// CHECK: if file is an uploaded file
		if (is_uploaded_file($_FILES[$fieldName]['tmp_name']) == false) {
			// SET: notification and return false
			$process->add_note(STRING_CLASS_UPLOADER_FAIL_HACKER);
			return false;  
		}

		// MOVE: file to $destination
		if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $this->destinationPath.$this->destinationFileName) ) {
			// SET: notification and return filename
			$process->add_note(STRING_CLASS_UPLOADER_DONE_UPLOADING, 'DONE');			
			return $this->destinationFileName;
		} else {
			// SET: notification and return false
			$process->add_note(STRING_CLASS_UPLOADER_FAIL_MOVING);
			return false;  		
		}
	  
	}

}
?>