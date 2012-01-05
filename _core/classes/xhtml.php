<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** xhtml.php
	
	This is an static class with functions to build xhtml elements used by all objects and procedural processes.
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/


class Xhtml {


//----------------------------------------------------------------------------------------------------
// STATIC METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------

	/** build_image_button()
	*	
	*	Generates xhtml for an image button
	*
	*	$id: unique name for button & css rules
	*	$url: url to visit on click
	*	$src: url of button image
	*	$alt: alt text
	*	$target: where to open
	*	$class: css class
	*	$parameters: parameters like onclick or onload
	*	
	*/
	static function build_image_button($id, $url, $src, $alt = false, $class= false, $target = false, $parameters = false) {
	
		$alt = ' alt="' . $alt . '"';
		$id = (!empty($id)) ? ' id="' . $id . '"' : '';
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$target = (!empty($target)) ? ' target="' . $target . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		
		return '<a href="' . $url . '"' . $target . $class . $parameters . '><img src="' . $src . '"' . $id . $alt . ' /></a>';
	
	}
	
	
	/** build_form()
	*	
	*	Outputs a starting form tag
	*
	*	$id: name of form
	*	$action: action to perform/url to visit
	*	$allowUpload: true or false, default is false
	*	$parameters: extra parameters
	*	
	*/
	static function build_form($id, $action, $allowUpload = false, $parameters = false) {
	
	  $enctype = ($allowUpload) ? ' enctype="multipart/form-data"' : '';
	  $parameters = (!empty($parameters)) ? ' ' . $parameters : '';
	  
	  return '<form id="' . $id . '" action="' . $action . '" method="post"' . $enctype . $parameters . '>';
	  
	}
	
	
	/** build_submit_button()
	*
	*	Outputs a submit form button using an image
	*
	*	$id: name of form object
	*	$src: image path and file name
	*	$alt: alternative help text, default is empty
	*	$parameters: extra parameters
	*	
	*/
	static function build_submit_button($id, $src, $alt = false, $class = false, $parameters = false) {
	
		$alt = ' alt="' . $alt . '"';
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';	
		
		return '<input id="' . $id . '" name="' . $id . '" type="image" src="' . $src . '"'. $alt . $class . $parameters . ' />';
	
	}
	
	
	/** build_input_field()
	*
	*	!Reinsert var adapted to $_POST! Draws a text, file or hidden input field.
	*	Htmlspecialchars prevents double qoutes from breaking the value tag attribute.
	*
	*	$id: name of field
	*	$type: field type (text, hidden, file), default is 'text'
	*	$class: Style class
	*	$reinsertValue: default is true
	*	$value: value to populate field with, default is '', $reinsertValue has precedence over $value
	*	$parameters: extra parameters
	*	
	*/
	static function build_input_field($id, $type = 'text', $class = 'mediumInput', $reinsertValue = true, $value = false, $parameters = false) {
		
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		$value = (isset($GLOBALS['dirty'][$id]) && $reinsertValue == true) ? $GLOBALS['dirty'][$id] : $value; 
		$value = htmlspecialchars(stripslashes($value));
		
		return '<input type="' . $type . '" id="' . $id . '" name="' . $id . '" value="' . $value . '"' . $class . $parameters . ' />';
	  
	}

	
	/** build_select_field()
	*
	*
	*	$id: Name of field
	*	$associativeArr: Associative array of select keys and values
	*	$class: style class
	*	$value: Key that has to be initially selected on a clean load, '' (empty) by default, 'NON' ensures it is never pre-selected
	*	$reinsertValue: whether to reinsert value in form after submission
	*	$parameters: extra parameters
	*	SELECT: define in language file or config to set to other than English, default is SELECT
	* 
	*/
	static function build_select_field($id, array $associativeArr, $class = 'mediumSelect', $reinsertValue = true, $value = false, $parameters = false) {
	
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		$value = (isset($GLOBALS['dirty'][$id]) && $reinsertValue == true) ? html_entity_decode(stripslashes($GLOBALS['dirty'][$id])) : $value;		
		
		$field = '
			<select name="' . $id . '" id="' . $id . '"' . $class . $parameters . '>
				<option value="">' . STRING_CLASS_XHTML_SELECT . '</option>' . "\n"
		;

		foreach ($associativeArr as $key => $option) {
			$selected = ((string)$key == (string)$value) ? ' selected="selected"': '';
			$field .= '<option value="' . $key . '"' . $selected . '>' . Core::output($option) . '</option>' . "\n";
		}
	  
		$field .= '</select>';
	
		return $field;
	
	}
	
	
	/** build_checkbox_group()
	*/
	static function build_checkbox_group($id, $associativeArr, $class = 'mediumGroup', $reinsertValue = true, $valueArr = false, $parameters = false) {
	
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		$valueArr = (isset($GLOBALS['dirty'][$id]) && $reinsertValue == true) ? $GLOBALS['dirty'][$id] : $valueArr;		

		$field = '<div id="' . $id . 'Group"' . $class . $parameters . '>' . "\n";

		foreach ($associativeArr as $key => $label) {
			$itemId = $id . '[' . $key . ']';
			$checked = (isset($valueArr[$key])) ? ' checked="checked"': '';
			$field .= '<label><input id="' . $itemId . '" name="' . $itemId . '" type="checkbox" value="1"' . $checked . ' /> ' . $label . '</label>' . "\n";
		}
	  
		$field .= '<input id="' . $id .'[ALWAYS_ON]" name="' . $id .'[ALWAYS_ON]" type="hidden" value="" /></div>';  // hidden input makes sure the id is always passed when submitted
	
		return $field;
	
	}
	
	
	/** build_radio_group()
	*/
	static function build_radio_group($id, $associativeArr, $class = 'mediumGroup', $reinsertValue = true, $value = false, $parameters = false) {
	
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		$value = (isset($GLOBALS['dirty'][$id]) && $reinsertValue == true) ? html_entity_decode(stripslashes($GLOBALS['dirty'][$id])) : $value;		

		$field = '<div id="' . $id . 'Group"' . $class . $parameters . '>' . "\n";

		foreach ($associativeArr as $key => $label) {
			$itemId =  $key;
			$checked = ((string)$key == (string)$value) ? ' checked="checked"': '';
			$field .= '<label><input id="' . $id . $key . '" name="' . $id . '" type="radio" value="' . $key . '"' . $checked . ' /> ' . Core::output($label) . '</label>' . "\n";
		}
		
		$field .= '</div>';
	
		return $field;
	
	}
	
	
	/** build_textarea()
	*
	*	Outputs a form textarea field
	*
	*	$id: name textarea
	*	$class: default 'mediumField', can also be smallField or largeField
	*	$reinsertValue: reuse posted value? default = true
	*	$value: pre-set text value
	*	$parameters: extra parameters
	*	
	*/
	static function build_textarea($id, $class = 'mediumAreaField', $reinsertValue = true, $value = false, $parameters = false) {
		
		$class = (!empty($class)) ? ' class="' . $class . '"' : '';
		$parameters = (!empty($parameters)) ? ' ' . $parameters : '';
		$value = (isset($GLOBALS['dirty'][$id]) && $reinsertValue == true) ? $GLOBALS['dirty'][$id] : $value; 
		$value = htmlspecialchars(stripslashes($value));	
		
		return '<textarea rows="0" cols="0" id="' . $id . '" name="' . $id . '" ' . $class . $parameters . '>' . $value . '</textarea>';
		
	}
	
	
	/** build_tooltip()
	*
	*	Outputs an image with class jTooltip and an title
	*	
	*/
	static function build_tooltip($image, $message) {

		return ' <img src="' . $image . '" title="' . $message . '" class="jTooltip" />';
	
	}
	
}
?>