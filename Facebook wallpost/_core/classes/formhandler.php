<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** formhandler.php
*	
*	This object handles the defined forms in an abstract way, allowing for simple form building  
*	It uses the singleton patern to make sure there only exists one form handler throughout
*	the whole application.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class FormHandler extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: static instance field (for singleton implementation)
	private static $instance;
		
	// DEFINE: fields
	protected 	$formArr;				// Storage for forms
	


//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The contrstructor looks voor a non-empty request URI and triggers an fatal error otherwise.
	*	Project root, is the folder structure the project resides in starting from the domain root
	*	
	*/
	private function __construct() {
		
		// include dom
		require_once('dom.php');
		
		// Start buffering
		ob_start();
		
		
	}
	
	
    /** __clone()
	*	
	*	Prevent users to clone the instance
	*
	*/
    public function __clone() {
        
		trigger_error('Clone of FormHandler is not allowed.', E_USER_ERROR);
    
	}
	
	
	/** instantiate()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function instantiate() {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : self::$instance = new self();
		
	}
	
	
	/** get_instance()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function get_instance() {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : self::instantiate();
		
	}	
	


//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------	
	
	/** add_form()
	*	
	*	Create new form 
	*	
	*/
	public function add_form($formName, $formMarkup) {

		// Set form
		$this->formArr[$formName]['markup'] = $formMarkup;	
	
	}
		

	/** add_recipient()
	*	
	*	Adds a recipient of the form contents by email. A header and footer can be defined
	*	
	*/
	public function add_recipient($formName, $email, $subject, $header=NULL, $footer=NULL) {
		
		if (Validator::match('email', $email)) {
		
			// Set form recipient
			$this->formArr[$formName]['recipients'][] = array (
				'email'		=> $email,
				'subject'	=> $subject,
				'header'	=> $header,
				'footer'	=> $footer
			);
			
		} else {
 			trigger_error('"'. $email .'" is not a valid email adres', E_USER_ERROR);
		}			
		
	}
		

	/** set_db_table()
	*	
	*	Sets the database table to write to
	*	
	*/
	public function set_db_table($formName, $table) {
		
		if (Validator::match('parameter', $table)) {
		
			// Set form recipient
			$this->formArr[$formName]['table'] = $table;
			
		} else {
 			trigger_error('"'. $table .'" is not a valid table name', E_USER_ERROR);
		}			
		
	}		
		

	/** set_final_action()
	*	
	*	Sets the final action to take. Either 'redirect' or 'output'
	*	
	*/
	public function set_final_action($formName, $action, $content) {
		
		if ($action == 'redirect') {
			
			if (Validator::match('uri', $content)) {
		
				// Set form redirect
				$this->formArr[$formName]['finalAction'] = array('redirect', $content);
			
			} else {
				trigger_error('"'. $content .'" is not a valid URL', E_USER_ERROR);
			}
			
		} elseif ($action == 'output') {
			
			// Set form output
			$this->formArr[$formName]['finalAction'] = array('output', $content);
			
		}
		
	}		


	/** display()
	*	
	*	PArses and displays the required table form
	*	
	*/
	public function display($formName) {
		
		// Instantiate the process notification handler, for errors and success notification (singleton)
		$process = Process::instantiate();
			
		// CONNECT: to database and instantiate $db object using singleton patern
		$db = Db::instantiate();
		
		if (isset($this->formArr[$formName]['markup'])) {
		
			// Create a DOM object
			$html = new simple_html_dom();
			
			// Load HTML from a string 
			$html->load($this->formArr[$formName]['markup']);	
				
			// Process form
			$formError = NULL;
			if (Validator::equal_if_set($formName, $_POST['form_name'])) {

				// Repopulate
				foreach ($html->find('input') as $e) {
					if ($e->type == 'text') {
						$e->value = isset($_POST[$e->name]) ? $_POST[$e->name] : '';;
					} elseif ($e->type == 'radio' && $_POST[$e->name] == $e->value) {
						$e->checked = 'checked';
					} elseif ($e->type == 'checkbox' && $_POST[$e->name] == $e->value) {
						$e->checked = 'checked';
					}
				}
				foreach ($html->find('select') as $e) {
					foreach ($e->find('option') as $o) {
						if (isset($_POST[$e->name])) {
							if ($o->value == $_POST[$e->name]) {
								$o->selected = 'selected';
							}
						}
					}
				}
				foreach ($html->find('textarea') as $e) {
					$e->innertext = $_POST[$e->name];
				}	
				
				// Check for input values that are required
				foreach ($html->find('*[required]') as $e) {
					$checkElement = false;
					if ($e->required == 'true') {
						$checkElement = true;
					}/* else {
						$required = $e->required;
						$reqElement = $html->find('#function2_field');
						if ($_POST[$reqElement->name] == $reqElement->value) {
							$checkElement = true;
						}
					}*/
					if ($checkElement == true) {	
						if (empty($_POST[$e->name])) {
							$e->parent()->parent()->innertext = $e->parent()->parent()->innertext . '<div class="formerror" id="error_'.$e->name.'">Bovenstaande vraag moet worden beantwoord</div>';
							$formError = true;
						}
					}
				}
				
				// Validate input values
				foreach ($html->find('*[validate]') as $e) {
					if (!Validator::match_if_set($e->validate, $_POST[$e->name]) && !empty($_POST[$e->name])) {
						switch ($e->validate) {
							case 'email':
								$errorMessage = 'Controleer uw email op fouten';
								break;
							case 'name':
								$errorMessage = 'Gebruik alleen alfabetische en numerieke tekens';
								break;
							case 'id':
								$errorMessage = 'Kies uit de veldopties';
								break;
							default:
								$errorMessage = 'Incorrecte invoer';
						}
						$e->parent()->parent()->innertext = $e->parent()->parent()->innertext . '<div class="formerror" id="error_'.$e->name.'">'. $errorMessage .'</div>';
						$formError = true;
					}
				}
				
				// Send emails
				if (!$formError && count($this->formArr[$formName]['recipients']) >= 1) {

					// Check for input values that need to be in the mail
					$mailMessage = NULL;
					foreach ($html->find('*[inmail=true]') as $e) {
						if (isset($_POST[$e->name])) {
							$mailMessage .= '<tr><th>'. ucfirst($e->parent()->prev_sibling()->innertext) .'</th><td>'. $_POST[$e->name] .'</td></tr>';
						}
					}
					$mailMessage = '<table>'. $mailMessage .'</table>';
					
					// WP set html emails
					add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
					
					// MAil each recipient
					foreach ($this->formArr[$formName]['recipients'] as $recipient) {
						$fullBody = $recipient['header'] . $mailMessage . $recipient['footer'];
						wp_mail($recipient['email'], $recipient['subject'], $fullBody, '');
					}
					
				}
				
				// Send to db
				if (!$formError && Validator::match_if_set('parameter', $this->formArr[$formName]['table'])) {
					
					// New data object
					$dataObj = new DataObject($this->formArr[$formName]['table'], false, false);
					$dataObj->set('created', 'NOW()');
					
					// Check for input values that need to be in the database
					$i = NULL;
					foreach ($html->find('*[indb=true]') as $e) {
						$fieldName = str_replace('_field', '', $e->name);
						$dataObj->set($fieldName, $_POST[$e->name]);
						$i++;
					}
					if ($i) {
						$dataObj->insert();
					}
					
				}
				
				// The final action
				if (!$formError) {
					
					if ($this->formArr[$formName]['finalAction'][0] == 'redirect') {
						//  Use the wp redirect function
						Core::redirect($this->formArr[$formName]['finalAction'][1]);
						
					} elseif ($this->formArr[$formName]['finalAction'][0] == 'output') {
						$html = $this->formArr[$formName]['finalAction'][1];
					} else {
						$html = '<h2>Success</h2>';
					}
					
				}
		
				
			}

			echo $html;

			
		} else {
			// Form was not defined
 			trigger_error('FORM "'. $formName .'" COULD NOT BE FOUND!!', E_USER_ERROR);
		}			
		
	}





//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHOD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------	
	
}
?>