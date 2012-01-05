<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** dataobject.php
*	
*	Image manipulation class. Handles .jpg .png .gif
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Image extends Object {

//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	protected $sourceImage;			// image data from source
	protected $sourceType;  		// source image type
	protected $sourceWidth;	 		// source image width
	protected $sourceHeight;		// source image height
	
	protected $destinationImage;	// image data of new image
	
	

//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD	
//------------------------------------------------------------------------------------------------------------------

	/** __constructor()	
	*
	*	The constructor initializes the object and tries to strip the image into data ready for manipulation by GD
	*	
	*/
	public function __construct($sourceFile) {
	
		// IMPORT: the process class for process monitoring and error notification
		$process = Process::get_instance();
	
		// LOAD: source image and strip data
		if (file_exists($sourceFile)) {
			
			list($this->sourceWidth, $this->sourceHeight, $this->sourceType) = @getimagesize($sourceFile);
			
			switch($this->sourceType) {
				case 1:  
					$this->sourceImage = imagecreatefromgif($sourceFile); 
				break;
				case 2:  
					$this->sourceImage = imagecreatefromjpeg($sourceFile); 
				break;
				case 3:  
					$this->sourceImage = imagecreatefrompng($sourceFile); 
				break;
				default: 
					$process->add_note(STRING_CLASS_IMAGE_FAIL_IMAGE_DATA, 'FAIL');
				break;
			}  
			  
		}
		
	}



//------------------------------------------------------------------------------------------------------------------
// SIZE MANIPULATION METHODS
//------------------------------------------------------------------------------------------------------------------
	
	/** resample()	
	*
	*	Resample image, extensive options
	*	
	*/
	public function resample($destinationX, $destinationY, $sourceX, $sourceY, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight, $quality = 4) {

		// IMPORT: the process class for process monitoring and error notification
		$process = Process::get_instance();	
			
		if (!empty($this->sourceImage)) {
		
			$this->destinationImage = imagecreatetruecolor($destinationWidth, $destinationHeight); 
		
			if ($quality <= 1) {
				
				$temp = imagecreatetruecolor ($destinationWidth + 1, $destinationHeight + 1);
				imagecopyresized ($temp, $this->sourceImage, $destinationX, $destinationY, $sourceX, $sourceY, $destinationWidth + 1, $destinationHeight + 1, $sourceWidth, $sourceHeight);
				imagecopyresized ($this->destinationImage, $temp, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $destinationWidth, $destinationHeight);
				imagedestroy ($temp);
			
			} elseif ($quality < 5 && (($destinationWidth * $quality) < $sourceWidth || ($destinationHeight * $quality) < $sourceHeight)) {
				
				$tmp_w = $destinationWidth * $quality;
				$tmp_h = $destinationHeight * $quality;
				$temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
				imagecopyresized ($temp, $this->sourceImage, 0, 0, $sourceX, $sourceY, $tmp_w + 1, $tmp_h + 1, $sourceWidth, $sourceHeight);
				imagecopyresampled ($this->destinationImage, $temp, $destinationX, $destinationY, 0, 0, $destinationWidth, $destinationHeight, $tmp_w, $tmp_h);
				imagedestroy ($temp);
			
			} else {

				imagecopyresampled ($this->destinationImage, $this->sourceImage, $destinationX, $destinationY, $sourceX, $sourceY, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
			
			}
			
			return true;
		
		} else {
			// ERROR: no source image set
			trigger_error('No source image loaded', E_USER_ERROR);
		}
	
	}
	

	/** resize()	
	*
	*	Resize image, simple options
	*	
	*/
	public function resize($width, $height, $type = 'CROP', $allowStretching = false, $quality = 4) {
		
/*		if ($type == 'MAX') {
			if ($this->sourceHeight > $this->sourceWidth) { $type = 'HEIGHT'; }
			else { $type = 'WIDTH'; }
		}*/
		
		$sourceCopyX		= 0;
		$sourceCopyY		= 0;
		$sourceCopyWidth	= $this->sourceWidth;
		$sourceCopyHeight 	= $this->sourceHeight;
		$imageCopyOnly		= false;
/*		switch ($type) {
			
			case 'WIDTH':
				if ($allowStretching == true || $this->sourceWidth > $newSize) {
					$destinationWidth 	= $newSize;
					$destinationHeight	= round(($this->sourceHeight * $destinationWidth) / $this->sourceWidth);
				} else {
					$imageCopyOnly 	= true;
				}
				break;
			
			case 'HEIGHT':
				if ($allowStretching == true || $this->sourceHeight > $newSize) {
					$destinationHeight 	= $newSize;
					$destinationWidth	= round(($this->sourceWidth * $destinationHeight) / $this->sourceHeight);
				} else {
					$imageCopyOnly 	= true;
				}
				break;*/
			
		if ($type == 'CROP') {
		
			if (($this->sourceHeight / $height) < ($this->sourceWidth / $width)) { 
				$sourceCopyWidth 	= $width * ($this->sourceHeight / $height);
				$sourceCopyHeight 	= $this->sourceHeight;
				$sourceCopyX		= ($this->sourceWidth - $sourceCopyWidth) / 2;
				$sourceCopyY		= 0;
			} else { 
				$sourceCopyWidth 	= $this->sourceWidth;
				$sourceCopyHeight 	= $height * ($this->sourceWidth / $width);
				$sourceCopyX		= 0;
				$sourceCopyY		= ($this->sourceHeight - $sourceCopyHeight) / 2; 
			}
			$destinationWidth 	= $width;
			$destinationHeight	= $height;
		
		}
		
		if ($type == 'CROP_TOP') {
		
			if (($this->sourceHeight / $height) < ($this->sourceWidth / $width)) { 
				$sourceCopyWidth 	= $width * ($this->sourceHeight / $height);
				$sourceCopyHeight 	= $this->sourceHeight;
				$sourceCopyX		= ($this->sourceWidth - $sourceCopyWidth) / 2;
				$sourceCopyY		= 0;
			} else { 
				$sourceCopyWidth 	= $this->sourceWidth;
				$sourceCopyHeight 	= $height * ($this->sourceWidth / $width);
				$sourceCopyX		= 0;
				$sourceCopyY		= 0; 
			}
			$destinationWidth 	= $width;
			$destinationHeight	= $height;
		
		}
			
		if ($type == 'MAX') {
		
			$sourceCopyWidth 	= $sourceCopyWidth;
			$sourceCopyHeight 	= $sourceCopyHeight;
			
			// Check if the image isn't smaller than MAX
			if (($this->sourceHeight < $height) && ($this->sourceWidth < $width)) {
				$imageCopyOnly = true;
			} else { 
				// Calculate transformation
				if (($this->sourceHeight / $height) < ($this->sourceWidth / $width)) { 
					$destinationWidth 	= $width;
					$destinationHeight	= ($this->sourceHeight / $this->sourceWidth) * $width;
				} else { 
					$destinationWidth 	= ($this->sourceWidth / $this->sourceHeight) * $height;
					$destinationHeight	= $height;
				}
				$sourceCopyX		= 0;
				$sourceCopyY		= 0;
			}
		
		}
		
		if ($imageCopyOnly) {
			// No transformation required
			$this->destinationImage = $this->sourceImage;
		} else {
			// Transform
			$this->resample(0, 0, $sourceCopyX, $sourceCopyY, $destinationWidth, $destinationHeight, $sourceCopyWidth, $sourceCopyHeight, $quality);
		}
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// OUTPUT METHODS
//------------------------------------------------------------------------------------------------------------------
	
	/** output()	
	*
	*	Output images to a specific path or to the client. Different jpg, gif and png image types possible.
	*	
	*/
	public function output($imageType, $toFilePath = false, $destroy = true) {
	
		if (empty($imageType)) {
			$imageType = Filesystem::get_extension($toFilePath);
		}
			
		if (!empty($this->destinationImage)) {
		
			if (!empty($toFilePath)) {
			
				switch($imageType) {
				
					case 'jpg':  
						imagejpeg($this->destinationImage, $toFilePath);
						break;
					
					case 'gif':
						imagegif($this->destinationImage, $toFilePath); 
						break;
					
					case 'png':
						imagepng($this->destinationImage, $toFilePath);
						break;
					
					default: 
						trigger_error('Error wrong image type set', E_USER_ERROR); 
					return;
				
				}
			
			} else {
			
				switch($imageType) {
				
					case 'jpg':  
						header("Content-type: image/jpeg");
						imagejpeg($this->destinationImage);
						break;
					
					case 'gif':
						header("Content-type: image/gif");
						imagegif($this->destinationImage); 
						break;
					
					case 'png':
						header("Content-type: image/png");
						imagepng($this->destinationImage);
						break;
					
					default: 
						trigger_error('Error wrong image type set', E_USER_ERROR);
					return;
				
				}
			
			}				
			
			if ($destroy) { imagedestroy($this->destinationImage); }
			
		} else {
		
			// ERROR: no image to export
			gdf_store_message(STRING_CLASS_IMAGE_FAIL_NOT_FOUND);
			
		}
		
	}
		
} 
?>