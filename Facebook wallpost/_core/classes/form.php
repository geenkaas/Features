<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** form.php
*	
*	This is a form creation and validation object
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Form {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$formArr;				// form attributes definitions are stored here in an array
	protected	$fieldArr;				// field attributes definitions are stored here in an array
	protected	$fieldRelationships;	// field relationship array, store predifined relationship validations
	
	protected	$form;					// store the complete form xhtml here
	protected	$fieldXhtml;			// store the generated fields xhtml here
	protected	$hiddenXhtml = array();	// store the generated hidden fields xhtml here
	protected	$buttonXhtml = array();	// store the generated buttons xhtml here
	
	// DEFINE: filter arrays
	protected	$acceptedTagsArr = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'br', 'hr', 'b', 'i', 'em', 'big', 'strong', 'small', 'sup', 'sub', 'bdo', 'acronym', 'abbr', 'a', 'link', 'ul', 'ol', 'li', 'img', 'map', 'area', 'table', 'caption', 'th', 'tr', 'td', 'thead', 'tbody', 'tfoot', 'style', 'div', 'span');  // allowed tags in xhtml input
	protected	$acceptedAttributesArr = array('border', 'style', 'class', 'id', 'title', 'href', 'name', 'alt', 'target', 'src', 'cellspacing', 'rel', 'rev', 'cite', 'media', 'type', 'usemap', 'align');  // allowed attributes in tags in xhtml input



//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	Use gdf_handle_post to clean and prepare all POSTed fields
	*	
	*/
	function __construct() {
		
		// SAVE: the dirty posted values first
		$GLOBALS['dirty'] = $_POST;

		// CLEAN: the post superglobal
		reset($_POST);
		$_POST = Core::clean($_POST);
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// SET METHODS	
//------------------------------------------------------------------------------------------------------------------

	/** set_action()
	*	
	*	Change the form action attribute at a later stage
	*	
	*/
	public function set_action($newAction) {
				
		if (isset($this->formArr['action'])) {
			
			$this->formArr['action'] = $newAction;
			return true;
			
		} else { 
			return false; 
		}  
		
	}
	
	
	/** set_field_value()
	*	
	*	Use to set or change field values after their definition took place.
	*	
	*/	
	public function set_field_value($id, $value) {
		
		$this->fieldArr[$id]['value'] = $value;
		
	}
	
	
	/** remove_field()
	*	
	*	Use to remove a previously defined field before display
	*	
	*/	
	public function remove_field($id) {
		
		unset($this->fieldArr[$id]);
		return true;
		
	}



//------------------------------------------------------------------------------------------------------------------
// DEFINE METHODS	
//------------------------------------------------------------------------------------------------------------------

	/** define_form()
	*/
	public function define_form($id, $action, $allowUpload = false, $parameters = false) {
		
		$this->formArr['id'] = $id;
		$this->formArr['action'] = $action;
		$this->formArr['allowUpload'] = $allowUpload;
		$this->formArr['parameters'] = ($parameters) ? ' ' . $parameters : '';
	
	}
	
	
	/** define_title()
	*/
	public function define_title($id, $title) {
		
		$this->fieldArr[$id]['type'] = 'title';
		$this->fieldArr[$id]['title'] = $title;
		
	}
	
	
	/** define_header()
	*/
	public function define_header($id, $header) {
	
		$this->fieldArr[$id]['type'] = 'header';
		$this->fieldArr[$id]['header'] = $header;
	
	}
	
	
	/** define_comment()
	*/
	public function define_comment($id, $comment) {
	
		$this->fieldArr[$id]['type'] = 'comment';
		$this->fieldArr[$id]['comment'] = $comment;
		
	}
	
	
	/** define_string_field()
		$filter: filter keyword or regular expression
	*/
	public function define_string_field($id, $label, $req = true, $filter = 'name', $class = 'mediumInput', $maxChar = 100, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'stringField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] 	= $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_integer_field()
	*/
	public function define_integer_field($id, $label, $req = true, $min, $max, $class = 'smallInput', $maxChar = 10, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'integerField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = 'numeric';
		$this->fieldArr[$id]['min'] = $min;
		$this->fieldArr[$id]['max'] = $max;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_hidden_field()
		$filter: filter keyword or regular expression
	*/
	public function define_hidden_field($id, $filter = '^[[:alnum:]]*$', $reinsertValue = false, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'hiddenField';
		$this->fieldArr[$id]['filter'] = $filter; 
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_password_field()
		$filter: filter keyword (password) or regular expression
	*/
	public function define_password_field($id, $label, $req = true, $filter = 'password', $class = 'mediumInput', $maxChar = 30, $comment = false, $reinsertValue = false, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'passwordField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}


	/** define_text_area()
		$filter: filter keywords (simple_text) or regular expression
	*/
	public function define_text_area($id, $label, $req = true, $filter = 'text', $class = 'mediumArea', $maxChar = 5000, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'textArea';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}


	/** define_xhtml_area()
		$filter: filter keywords (xhtml) or regular expression
	*/
	public function define_xhtml_area($id, $label, $req = true, $filter = 'xhtml', $class = 'mediumArea', $maxChar = 50000, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'xhtmlArea';
		$this->fieldArr[$id]['config'] = 'full';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}


	/** define_simple_area()
		$filter: filter keywords (xhtml) or regular expression
	*/
	public function define_simple_area($id, $label, $req = true, $filter = 'xhtml', $class = 'mediumArea', $maxChar = 50000, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'xhtmlArea';
		$this->fieldArr[$id]['config'] = 'simple';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}


	/** define_mailer_area()
		$filter: filter keywords (xhtml) or regular expression
	*/
	public function define_mailer_area($id, $label, $req = true, $filter = 'xhtml', $class = 'mediumArea', $maxChar = 50000, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'xhtmlArea';
		$this->fieldArr[$id]['config'] = 'mailer';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_checkbox_group()
		$filter: filter keywords (simple_text) or regular expression.
		Needs array as value of set boxes
	*/
	public function define_checkbox_group($id, $label, $dataArr, $req = true, $filter = 'array', $class = 'checkboxGroup', $maxChar = 5, $comment = false, $reinsertValue = true, $valueArr = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'checkboxGroup';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['dataArr'] = $dataArr;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = false; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $valueArr;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_radio_group()
		$filter: filter keyword or regular expression
	*/
	public function define_radio_group($id, $label, $dataArr, $req = true, $filter = 'numeric', $class = 'radioGroup', $maxChar = 100, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'radioGroup';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['dataArr'] = $dataArr;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_select_field()
		$filter: filter keyword or regular expression
	*/
	public function define_select_field($id, $label, $dataArr, $req = true, $filter = 'numeric', $class = 'mediumSelect', $maxChar = 5, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'selectField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['dataArr'] = $dataArr;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}	
	

	/** define_optional_select_field()
		$filter: filter keyword or regular expression
	*/
	public function define_optional_select_field($id, $label, $dataArr, $req = true, $filter = 'name', $class = 'mediumSelect', $maxChar = 10, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'optionalSelectField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['dataArr'] = $dataArr;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}


	/** define_file_field()
		$filter: filter keyword (file) or regular expression
	*/
	public function define_file_field($id, $label, $req = false, $filter = 'file', $class = 'mediumInput', $maxChar = 100, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'fileField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_date_field()
	*/
	public function define_date_field($id, $label, $req = true, $filter = 'ddmmyyyy', $class = 'noWidthInput', $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'dateField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = 10; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_time_field()
	*/
	public function define_time_field($id, $label, $req = true, $filter = 'time', $class = 'noWidthInput', $maxChar = 5, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'timeField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = 5; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_price_field()
	*/
	public function define_price_field($id, $label, $req = true, $filter = 'numeric', $class = 'noWidthInput', $maxChar = 8, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'priceField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_postal_code_field()
	*/
	public function define_postal_code_field($id, $label, $req = true, $filter = 'postalCodeNl', $class = 'noWidthInput', $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'postalCodeField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = 7; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_fromto_field()
	*/
	public function define_fromto_field($id, $label, $req = true, $filter = 'numeric', $class = 'noWidthInput', $maxChar = 4, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'fromtoField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] = $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_doublestring_field()
		$filter: filter keyword or regular expression
	*/
	public function define_doublestring_field($id, $label, $req = true, $filter = 'name', $class = 'mediumInput', $maxChar = 100, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'doublestringField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] 	= $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	
	/** define_captcha_field()
		$filter: filter keyword or regular expression
	*/
	public function define_captcha_field($id, $label, $req = true, $filter = 'parameter', $class = 'smallInput', $maxChar = 5, $comment = false, $reinsertValue = false, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'captchaField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] 	= $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}

	
	/** define_geocode_field()
		$filter: geoCode
	*/
	public function define_geocode_field($id, $label, $req = true, $filter = 'geoCode', $class = 'mediumInput', $maxChar = 60, $comment = false, $reinsertValue = true, $value = false, $parameters = false) {
	
		$this->fieldArr[$id]['type'] = 'geoCodeField';
		$this->fieldArr[$id]['label'] = $label;
		$this->fieldArr[$id]['req'] = $req;
		$this->fieldArr[$id]['filter'] = $filter;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['maxChar'] = $maxChar; 
		$this->fieldArr[$id]['comment'] = $comment;
		$this->fieldArr[$id]['reinsertValue'] 	= $reinsertValue;
		$this->fieldArr[$id]['value'] = $value;
		$this->fieldArr[$id]['parameters'] = $parameters;
	
	}
	
	 
	/** define_image_button()
	*/
	public function define_image_button($id, $url, $src, $alt = false, $class = false, $target = false, $parameters = false) {
		
		$this->fieldArr[$id]['type'] = 'imageButton';
		$this->fieldArr[$id]['url'] = $url;
		$this->fieldArr[$id]['src'] = $src;
		$this->fieldArr[$id]['alt'] = $alt;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['target'] = $target;
		$this->fieldArr[$id]['parameters'] = $parameters;
		
	}
	
	
	/** define_submit_button()
	*/ 
	public function define_submit_button($id, $src, $alt = false, $class = false, $parameters = false) {
		
		$this->fieldArr[$id]['type'] = 'submit_button';
		$this->fieldArr[$id]['src'] = $src;
		$this->fieldArr[$id]['alt'] = $alt;
		$this->fieldArr[$id]['class'] = $class;
		$this->fieldArr[$id]['parameters'] = $parameters;
		
	}  
		
		
	/** Define field relationships
		Examples: equals, greater than, smaller than
	*/ 
	public function define_field_relationship($id, $field1, $relationship = 'equals', $field2) {
		
		$this->fieldRelationships[$id]['field1'] = $field1;
		$this->fieldRelationships[$id]['relationship'] = $relationship;
		$this->fieldRelationships[$id]['field2'] = $field2;
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// XHTML BUILDING METHODS 
//------------------------------------------------------------------------------------------------------------------
	
	/** display()
	*/
	public function display() {
		return $this->build_form();
	}	
	
	/** build_form()
	*/ 
	protected function build_form() {
	
		if (is_array($this->formArr) && is_array($this->fieldArr)) {
		
			reset($this->fieldArr);
			foreach ($this->fieldArr as $id => $field) {
			
			// PREPARE: some values
			if (isset($field['req'])) {
				$asterix = ($field['req']) ? '<span class="asterix">&nbsp;*</span>' : '';
			}
			$error = (isset($field['error'])) ? '<div class="error"><strong>&#8710;</strong> ' . $field['error'] . '</div>' : '';
		
				switch ($field['type']) {  // lookup type, get data from fieldArr and inject in function to build form objects
					
					// BUILD: title
					case 'title':
				
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<td colspan="3" id="' . $id . '" class="formTitle"><h2>' . $field['title'] . '</h2></td>
								</tr>
							';
							break;
					
					// BUILD: header line
					case 'header':
						
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<td colspan="3" id="' . $id . '" class="formHeader"><h3>' . $field['header'] . '</h3></td>
								</tr>
							';
							break;

					// BUILD: comment line
					case 'comment':
						
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<td colspan="3" id="' . $id . '" class="formComment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: string input field
					case 'stringField':
							
							$parameters = 'maxlength="' . $field['maxChar'] . '" ' . $field['parameters'];
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_input_field($id, 'text', $field['class'], $field['reinsertValue'], $field['value'], $parameters) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: integer input field
					case 'integerField':
						
							$parameters = 'maxlength="' . $field['maxChar'] . '" ' . $field['parameters'];
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_input_field($id, 'text', $field['class'], $field['reinsertValue'], $field['value'], $parameters) . 
									$error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: hidden field
					case 'hiddenField':
							
							$this->hiddenXhtml[] = Xhtml::build_input_field($id, 'hidden', false, false, $field['value'], $field['parameters']);
							break;

					// BUILD: password field
					case 'passwordField':
						
							$parameters = 'maxlength="' . $field['maxChar'] . '" ' . $field['parameters'];
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_input_field($id, 'password', $field['class'], $field['reinsertValue'], $field['value'], $parameters) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;
					
					// BUILD: text area
					case 'textArea':
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_textarea($id, $field['class'], $field['reinsertValue'], $field['value'], $field['parameters']) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// Build xhtml area
					case 'xhtmlArea':
							
							//REQUIRE: fck-editor plugin class
							require_once(FS_PATH_PLUGINS . 'fckeditor/fckeditor_php5.php');
							
							// INSTANTIATE: editor object
							$oFCKeditor = new FCKeditor($id) ;
							$oFCKeditor->BasePath 						= WS_PATH_PLUGINS  . 'fckeditor/';
							$oFCKeditor->Width 							= 610;
							$oFCKeditor->Height 						= 400 ;
							$oFCKeditor->Config['BaseHref']				= 'http://' . trim($_SERVER['HTTP_HOST']) . '/';
							$oFCKeditor->Config['DocType']				= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
							$oFCKeditor->Config['EditorAreaCSS']		= WS_PATH_VIEWS . 'editor.style.css';
							$oFCKeditor->Config['FontSizes']			= '8px;10px;12px;14px;16px;18px;20px;24px;30px;';
							$oFCKeditor->Config['SkinPath'] 			= $oFCKeditor->BasePath . 'editor/skins/office2003/' ;
							$oFCKeditor->Config['AutoDetectLanguage']	= false ;
							$oFCKeditor->Config['DefaultLanguage']		= LANGUAGE ;
							$oFCKeditor->ToolbarSet						= 'Core' ;
							$oFCKeditor->Value 							= $field['value'];

							// SET: special configurations
							unset($_SESSION['editor']);
							if ($field['config']== 'simple') {
								$oFCKeditor->ToolbarSet					= 'Basic' ;
							} elseif ($field['config']== 'mailer') {
								$_SESSION['editor']['absolutePath'] = true;  // set session for use in editor image paths
							}

							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . $oFCKeditor->Create() . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							
							break;
					
					// BUILD: checkbox group
					case 'checkboxGroup':
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_checkbox_group($id, $field['dataArr'], $field['class'], $field['reinsertValue'], $field['value'], $field['parameters']) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';								
							break;
					
					// BUILD: radio group
					case 'radioGroup':
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_radio_group($id, $field['dataArr'], $field['class'], $field['reinsertValue'], $field['value'], $field['parameters']) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';								
							break;

					// BUILD: select field
					case 'selectField':
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . Xhtml::build_select_field($id, $field['dataArr'], $field['class'], $field['reinsertValue'], $field['value'], $field['parameters']) . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';								
							break;
							
					// BUILD: optional select field
					case 'optionalSelectField':
							
							$valueSelect = false;
							$valueInput = false;
							$field['dataArr']['[[OTHER]]'] = STRING_CLASS_FORM_OTHER;  // extra option to give one's own input
							
							// PREPARE: value for insertion
							if (!empty($field['value']) || ((string)$field['value'] == '0')) {
								if (isset($field['dataArr'][$field['value']])) { 
									$valueSelect = $field['value']; 
								} else { 
									$valueSelect = '[[OTHER]]';
									$valueInput = $field['value']; 
								}
							}
							
							
							$fieldSelect = Xhtml::build_select_field($id.'1', $field['dataArr'], 'jOptionSelect '.$field['class'], $field['reinsertValue'], $valueSelect, $field['parameters']);
							$fieldInput = Xhtml::build_input_field($id.'2', 'text', 'jOptionInput '.$field['class'], $field['reinsertValue'], $valueInput, false);
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . $fieldSelect . ' ' . $fieldInput . $error . '</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';								
							break;								
							
					// BUILD: file upload field
					case 'fileField':
					
							$parameters = ($field['parameters']) ? '" ' . $field['parameters'] : '';
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">
										<input type="file" name="' . $id . '"  id="' . $id . '"' . $parameters . ' />
										<div class="fileDisplay">' . (!empty($GLOBALS['dirty'][$id]) ? $GLOBALS['dirty'][$id] : $field['value']) . $error . '</div>
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;				
	
					// BUILD: date input field  
					case 'dateField':
						
							unset($choppedDate);
							
							if (!empty($field['value'])) { 
							
								$choppedDate = explode('-', str_replace(' 00:00:00', '',$field['value']));   // prepare db value for insertion in form field

								if ($field['filter'] == 'ddmmyyyy') {  // NEW SYNTAX
									$choppedDate = array_reverse($choppedDate);
								} elseif ($field['filter'] == 'mmddyyyy') {
									$choppedDate = array($choppedDate[1], $choppedDate[2], $choppedDate[0]);
								}
								
							} else {
								
								$choppedDate[0] = false;
								$choppedDate[1] = false;
								$choppedDate[2] = false;
							
							}
														
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . 
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedDate[0], 'maxlength="2" size="2"') . '&#8211; ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedDate[1], 'maxlength="2" size="2"') . '&#8211; ' .
										Xhtml::build_input_field($id.'3', 'text', $field['class'], $field['reinsertValue'], $choppedDate[2], 'maxlength="4" size="4"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: time input field
					case 'timeField':
						
							unset($choppedTime);
							
							if (!empty($field['value'])) { 
								$choppedTime = explode(':', $field['value']); // prepare db value for insertion
							} else {
								$choppedTime[0] = false;
								$choppedTime[1] = false;
							}  
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' .
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedTime[0], 'maxlength="2" size="2"') . ' : ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'], $field['reinsertValue'], $choppedTime[1], 'maxlength="2" size="2"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: price input field
					case 'priceField':
						
							unset($choppedPrice);
							
							if (!empty($field['value'])) { 
								$choppedPrice = explode('.', $field['value']); // prepare db value for insertion
							} else {
								$choppedPrice[0] = false;
								$choppedPrice[1] = false;
							}
							
							$sizeNum = $field['maxChar'] - 3;  // max characters minus digits, minus dot
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' .
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedPrice[0], 'maxlength="' . $sizeNum . '" size="' . $sizeNum . '"') . ' ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'], $field['reinsertValue'], $choppedPrice[1], 'maxlength="2" size="2"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;

					// BUILD: postal_code input field
					case 'postalCodeField':
						
							unset($choppedZip);
							
							if (!empty($field['value'])) { 
								$choppedZip = explode(' ', $field['value']); // prepare db value for insertion
							} else {
								$choppedZip[0] = false;
								$choppedZip[1] = false;
							}  
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' .
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedZip[0], 'maxlength="4" size="4"') . ' ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'], $field['reinsertValue'], $choppedZip[1], 'maxlength="2" size="2"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;
							
					// BUILD: fromto input field
					case 'fromtoField':
						
							unset($choppedVar);
							
							if (!empty($field['value'])) { 
								$choppedVar = explode('|', $field['value']); // prepare db value for insertion
							} else {
								$choppedVar[0] = false;
								$choppedVar[1] = false;
							} 
							
							$charWidthOne = floor(($field['maxChar']-1)/2);
							$charWidthTwo = ceil(($field['maxChar']-1)/2);
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' .
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedVar[0], 'maxlength="' . $charWidthOne . '" size="' . $charWidthOne . '"') . ' <span class="fromToString">' . STRING_CLASS_FORM_TO . '</span> ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'], $field['reinsertValue'], $choppedVar[1], 'maxlength="' . $charWidthTwo . '" size="' . $charWidthTwo . '"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;
							
					// BUILD: doublestring input field
					case 'doublestringField':
						
							unset($choppedVar);
							
							if (!empty($field['value'])) { 
								$choppedVar = explode('|', $field['value']); // prepare db value for insertion
							} else {
								$choppedVar[0] = false;
								$choppedVar[1] = false;
							} 
							
							$charWidthOne = floor(($field['maxChar']-1)/2);
							$charWidthTwo = ceil(($field['maxChar']-1)/2);
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' .
										Xhtml::build_input_field($id.'1', 'text', $field['class'].' jAutoTab', $field['reinsertValue'], $choppedVar[0], 'maxlength="' . $charWidthOne . '" size="' . $charWidthOne . '"') . ' ' .
										Xhtml::build_input_field($id.'2', 'text', $field['class'], $field['reinsertValue'], $choppedVar[1], 'maxlength="' . $charWidthTwo . '" size="' . $charWidthTwo . '"') . 
										$error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;
														
					// BUILD: captcha input field
					case 'captchaField':
							
							$parameters = 'maxlength="' . $field['maxChar'] . '" ' . $field['parameters'];
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . 
										STRING_CLASS_FORM_CAPTCHA_INFO . '<br />										
										<img src="' . WS_PATH_PLUGINS . 'captcha/index.php" class="captcha" /> ' . 
										Xhtml::build_input_field($id, 'text', $field['class'], $field['reinsertValue'], $field['value'], $parameters) . $error . '
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;	
							
					// BUILD: geoCode input field
					case 'geoCodeField':
							
							$parameters = 'maxlength="' . $field['maxChar'] . '" ' . $field['parameters'];
							
							$apiKey = (PROJECT_STATE == 'Production') ? STRING_API_KEY_MAPS_PRO : STRING_API_KEY_MAPS_DEV;
							
							$this->fieldXhtml[] = '
								<tr class="' . $id . 'FormRow">
									<th>' . $field['label'] . $asterix . '</th>
									<td class="element">' . 										
										Xhtml::build_input_field($id, 'text', $field['class'], $field['reinsertValue'], $field['value'], $parameters) . '
										<a href="' . WS_PATH_PLUGINS . 'geolocator/index.php?apiKey=' . $apiKey . '" class="jPopup" title="|770|560|"><img src="' . WS_PATH_PLUGINS . 'geolocator/geolocator.png" /></a>' . 
										$error . ' 
									</td>
									<td class="comment">' . $field['comment'] . '</td>
								</tr>
							';
							break;
	          		
					// BUILD: image button					
					case 'imageButton':
							
							$this->buttonXhtml[] = Xhtml::build_image_button($id, $field['url'], $field['src'], $field['alt'], $field['class'], $field['target'], $field['parameters']);
							break;

					// BUILD: submit button
					case 'submit_button':
							
							$this->buttonXhtml[] = Xhtml::build_submit_button($id, $field['src'], $field['alt'], $field['class'], $field['parameters']);
							break;
													
				}
				
				unset($id, $field, $asterix, $parameters);
				
			}
			
			// ASSEMBLE: form, fields, hidden and buttons
			return
				Xhtml::build_form($this->formArr['id'], $this->formArr['action'], $this->formArr['allowUpload'], $this->formArr['parameters']) . '
					<table cellspacing="0">' .  
						(is_array($this->fieldXhtml) ? implode("\n", $this->fieldXhtml) : '') . ' 
						<tr>
							<td colspan="2" class="buttons">' . 
								implode("\n", $this->buttonXhtml) . '
							</td>
							<td class="hiddenTd">' . 
								implode("\n", $this->hiddenXhtml) . '
							</td>
						</tr>
					</table>
				</form>
			';
				
		} else { 
			
			// ERROR: no attributes and fields defined to build form
			trigger_error('no attributes and fields defined to build form', E_USER_ERROR);
			
		}
		
	
	}
	


//------------------------------------------------------------------------------------------------------------------
// FILTER & VALIDATION METHODS
//------------------------------------------------------------------------------------------------------------------


	/** validate: uses the definitions of the field to validate the input data. Special fields are pre-set to the right variables before the validation.
	*/
	public function validate() {
	
		// GET: Process (global) instance, in case we need to add messages and stop the processing
		$process = Process::get_instance();
		
		if (is_array($this->fieldArr)) {
			
			reset($this->fieldArr);
			foreach ($this->fieldArr as $id => $field) { 
				
				/// PRE_PROCESS: for special fields
				switch ($field['type']) {

					// PROCESS: optional select fields
					case 'optionalSelectField':
					
							if ($_POST[$id . '1'] == '[[OTHER]]') {
								$_POST[$id] = $_POST[$id . '2'];
							}
							else { 
								$_POST[$id] = $_POST[$id.'1']; 
								unset($GLOBALS['dirty'][$id .'2']);
							}
							break;		
					
					// PROCESS: date fields
					case 'dateField':
					
							if ($field['filter'] == 'ddmmyyyy') {
							
								if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2']) || !empty($_POST[$id.'3'])) {
									$_POST[$id] = implode('-', array($_POST[$id.'3'], $_POST[$id.'2'], $_POST[$id.'1']));
								} else {
									$_POST[$id] = false;
								}
																
							} elseif ($field['filter'] == 'mmddyyyy') {
							
								if (gdf_not_null($_POST[$id.'1']) || gdf_not_null($_POST[$id.'2']) || gdf_not_null($_POST[$id.'3'])) {
									$_POST[$id] = implode('-', array($_POST[$id.'3'], $_POST[$id.'1'], $_POST[$id.'2']));
								} else {
									$_POST[$id] = false;
								}
																
							}
							break;
				
					// PROCESS: time fields
					case 'timeField':
					
							if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2'])) {
								$_POST[$id] = implode(':', array($_POST[$id.'1'], $_POST[$id.'2']));
							} else {
								$_POST[$id] = false;
							}
							break;

					// PROCESS: price fields
					case 'priceField':
					
							if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2'])) {
								$_POST[$id] = implode('.', array($_POST[$id.'1'], $_POST[$id.'2']));
							} else {
								$_POST[$id] = false;
							}
							break;

					// PROCESS: postal_code fields
					case 'postalCodeField':
					
							if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2'])) {
								$_POST[$id] = implode(' ', array($_POST[$id.'1'], $_POST[$id.'2']));
							} else {
								$_POST[$id] = false;
							}
							break;

					// PROCESS: checkbox fields
					case 'checkboxGroup':
							
							unset($_POST[$id]['ALWAYS_ON']);  // remove added hidden key 
							break;

					// PROCESS: radio group fields
					case 'radioGroup':
						
							if (!isset($_POST[$id])) {							
								
								// SET: to prevent future errors
								$_POST[$id] = NULL;
							
							} 
							break;

					// PROCESS: fromto fields
					case 'fromtoField':
					
							if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2'])) {
								$_POST[$id] = implode('|', array($_POST[$id.'1'], $_POST[$id.'2']));
							} else {
								$_POST[$id] = false;
							}
							break;

					// PROCESS: doublestring fields
					case 'doublestringField':
					
							if (!empty($_POST[$id.'1']) || !empty($_POST[$id.'2'])) {
								$_POST[$id] = implode('|', array($_POST[$id.'1'], $_POST[$id.'2']));
							} else {
								$_POST[$id] = false;
							}
							break;
												
				}
				
				
				/// VALIDATE: required
				if (isset($field['req'])) {
					
					if ($field['type'] != 'fileField') {
					
						if (!is_array($_POST[$id]) && !Core::not_empty($_POST[$id]) && $field['req'] == true) {  // checks for required fields, if error, syntax chec is skipped
							
							// SET: process notice and stop process
							$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS);
							
							// SET: add notice to field (no other validations will be held for this field)
							$this->set_field_error($id, STRING_CLASS_FORM_ERROR_MISSING);
						
						} elseif (is_array($_POST[$id]) && count($_POST[$id]) < 1 && $field['req'] == true) {  // checks for required array fields, if error, syntax chec is skipped
							
							// SET: process notice and stop process
							$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS);
							
							// SET: add notice to field (no other validations will be held for this field)
							$this->set_field_error($id, STRING_CLASS_FORM_ERROR_MISSING);
						
						}
						
					}
										
				}
				
				
				/// VALIDATE: maximum characters
				if (isset($field['maxChar'])) {
					
					if ($field['type'] == 'fileField') {
					
						if (!empty($field['maxChar']) && (strlen($_FILES[$id]['name']) > $field['maxChar']) && $this->check_field($id)) {
							
							// SET: process notice and stop process
							$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS);
							
							// SET: add notice to field (no further validations will be held)
							$this->set_field_error($id, str_replace('[[MAX]]', $field['maxChar'], STRING_CLASS_FORM_ERROR_MAX_CHAR));
						
						}
					
					} elseif (!empty($field['maxChar']) && (strlen($_POST[$id]) > $field['maxChar']) && $this->check_field($id)) {
						
						// SET: process notice and stop process
						$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS, 'FAIL');
						
						// SET: add notice to field (no further validations will be held)
						$this->set_field_error($id, str_replace('[[MAX]]', $field['maxChar'], STRING_CLASS_FORM_ERROR_MAX_CHAR));
					
					}
				
				}	
				
				
				/// VALIDATE: captcha
				if ($field['type'] == 'captchaField' && $this->check_field($id)) {
				
					if (!Validator::equal_if_set(trim($_SESSION['captchaKey']), strtoupper($_POST[$id]))) {
						
						// SET: process notice and stop process
						$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS);
						
						// SET: add notice to field (no further validations will be held on this field)
						$this->set_field_error($id, STRING_CLASS_FORM_ERROR_CAPTCHA);
					
					}
				
				} 		
						
				
				/// VALIDATE: filters
				if (isset($field['filter']) && $this->check_field($id) && $field['type'] != 'fileField') {
				
					if (!empty($field['filter']) && $this->check_field($id) && Core::not_empty($_POST[$id]) && !is_array($_POST[$id])) {
					
						switch ($field['filter']) {
								
							case 'none':
							break;
															
							case 'numeric':
								if (!is_numeric($_POST[$id])) { 
									$this->set_field_error($id, STRING_CLASS_FORM_ERROR_NUMERIC); 
								} elseif(!empty($field['max']) || !empty($field['min'])) {
									
									if ($_POST[$id] > $field['max']) { 
										$this->set_field_error($id, str_replace('[[MAX]]', $field['max'], STRING_CLASS_FORM_ERROR_MAX)); 
									}
									elseif ($_POST[$id] < $field['min']) { 
										$this->set_field_error($id, str_replace('[[MIN]]', $field['min'], STRING_CLASS_FORM_ERROR_MIN));
									}
								
								}
							break;
							
							case 'array':
								if (!is_array($_POST[$id])) { 
									$this->set_field_error($id, STRING_CLASS_FORM_ERROR_ARRAY); 
								}
							break;
							
							case 'text':
								$xhtmlFilter = new XhtmlFilter(array(), array(), 0, 0);
								$_POST[$id] = $xhtmlFilter->process($_POST[$id]);
							break;
							
							case 'xhtml':
								$xhtmlFilter = new XhtmlFilter($this->acceptedTagsArr, $this->acceptedAttributesArr, 0, 0);
								$_POST[$id] = $xhtmlFilter->process($_POST[$id]);
							break;
							
							default:
								if (!Validator::match($field['filter'], $_POST[$id])) {	
						
									switch ($field['filter']) {
				
										case 'fileStrict':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_FILE_STRICT);
										break;
				
										case 'name':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_NAME);
										break;	
				
										case 'sentence':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_SENTENCE);
										break;
				
										case 'doublesentence':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_SENTENCE);
										break;
										
										case 'float':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_FLOAT);
										break;							
												
										case 'parameter':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_PARAMETER);
										break;
												
										case 'email':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_EMAIL);
										break;
												
										case 'uri':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_URL);
										break;
												
										case 'phone':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_PHONE);
										break;
											
										case 'ddmmyyyy':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_DDMMYYYY);
										break;
											
										case 'mmddyyyy':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_DDMMYYYY);
										break;
											
										case 'time':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_TIME);
										break;
											
										case 'password':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_PASSWORD);
										break;
											
										case 'geoCode':
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_GEOCODE);
										break;
										
										default:
											$this->set_field_error($id, STRING_CLASS_FORM_ERROR_VALIDATION);
										break;
										
									}
									
								}
							break;
						
						}
					
					}
					
					if ($this->check_field($id) != true) { 
						
						// SET: process notice and stop process
						$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS, 'FAIL');
					
					}
					
					unset($field, $id, $asterix);  // clean start for every loop
				
				}
			
			}	
			
			
			/// VALIDATE: field relationships
			if (is_array($this->fieldRelationships)) {
			
				foreach ($this->fieldRelationships as $id => $relation) {
					
					switch ($relation['relationship']) {
					
							case 'equals':
								if (isset($relation['field1']) && isset($relation['field2'])) {
									if ($this->check_field($relation['field1']) && $this->check_field($relation['field2'])) {
										if ($_POST[$relation['field1']] !== $_POST[$relation['field2']]) { 
																
											// SET: add notice to field 
											$this->set_field_error($relation['field2'], STRING_CLASS_FORM_ERROR_RELATIONSHIP_EQUALS);
											
										}
									}
								} 
								break;
					
					}
					
					if ($this->check_field($id) != true) { 
						
						// SET: process notice and stop process
						$process->add_note(STRING_CLASS_FORM_ERROR_FIELDS, 'FAIL');
					
					}
				
				}
			
			}
				
		
		} else { 
			
			// ERROR: no formfields have been defined
			trigger_error('No form fields have been defined', E_USER_ERROR); 
		
		}
		
		// RETURN: true or false
		if ($process->run()) {
			return true;
		} else {
			return false;
		}
		
	}



//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHOD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------	
	
	/** set_field_error()
	*
	*	Sets an error to a field definition.
	*
	*/
	protected function set_field_error($id, $errorMessage) {
		
		if (empty($this->fieldArr[$id]['error'])) {
			$this->fieldArr[$id]['error'] = $errorMessage;
		} else {
			trigger_error('An error notice has already been set for "'.$id.'"', E_USER_ERROR);
		}
		
	}
	
	
	/** check_field()
	*
	*	Checks if a validation error has already occured on this field. 
	*	If through, the check was successful and the validation can continue
	*
	*/
	protected function check_field($id) {
		
		if (empty($this->fieldArr[$id]['error'])) {
			return true;
		} else {
			return false;
		}
		
	}


}
?>