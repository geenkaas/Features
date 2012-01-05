<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** requesthandler.php
*	
*	This object handles the user request by using the request URI. 
*	In case this request URI is unavailable, the method exits triggering an fatal error. 
*	It uses the singleton patern to make sure there only exists one request handler throughout
*	the whole application.
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class RequestHandler extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: static instance field (for singleton implementation)
	private static $instance;
		
	// DEFINE: fields
	protected 	$requestUri;			// request URI string
	protected	$projectRoot;			// folder structure the project resides in starting from the domain root
	protected	$requestParametersArr;	// array containing all URI request parameters 
	protected	$maxParameters = 25;	// max quantity parameters allowed in URI string
	protected	$countParameters;		// actual quantity parameters found in URI string
	
	protected	$excludeGetArr = array(	// which get variables should be ecluded by this->store_session()
					'action',
					'email'
				);
	
	protected	$controllerArr;			// array of controllers and reserved keywords
	protected	$positionsArr;			// array of parameter positions
	
	// DEFINE: standard parameters
	protected	$standardApplication;	// standard application
	protected	$standardLanguage;		// standard language
	protected	$standardPage;			// standard page
	protected	$standardScene;			// standard scene



//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The contrstructor looks voor a non-empty request URI and triggers an fatal error otherwise.
	*	Project root, is the folder structure the project resides in starting from the domain root
	*	
	*/
	private function __construct($projectRoot) {
		
		// SET: project root
		if (isset($projectRoot)) {
			$this->projectRoot = $projectRoot;
		}
		
		// GET: request URI and retrieve parameters
		if (!empty($_SERVER['REQUEST_URI'])) {
			
			$this->define_parameters(urldecode($_SERVER['REQUEST_URI']));	
			
		} elseif (!empty($_SERVER['PATH_INFO'])) {
			
			$this->define_parameters(urldecode($_SERVER['PATH_INFO']));	
			
		} elseif (!empty($_SERVER['REDIRECT_URL'])) {
			
			$this->define_parameters(urldecode($_SERVER['REDIRECT_URL']));	
			
		} else {
		
			// TRIGGER: error, no request URI
			trigger_error('No request URI found', E_USER_ERROR);
			
		}
		
		// SET: application controller array
		$this->controllerArr['main'] 	= 'main.controller.php';
		$this->controllerArr['admin'] 	= 'admin.controller.php';
		$this->controllerArr['xml']		= 'xml.controller.php';
		$this->controllerArr['pdf']		= 'pdf.controller.php';
		$this->controllerArr['img']		= 'img.controller.php';
		
	}
	
	
    /** __clone()
	*	
	*	Prevent users to clone the instance
	*
	*/
    public function __clone() {
        
		trigger_error('Clone of RequestHandler is not allowed.', E_USER_ERROR);
    
	}
	
	
	/** instantiate()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function instantiate($projectRoot = WS_PATH_ROOT) {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : self::$instance = new self($projectRoot);
		
	}
	
	
	/** get_instance()
	*	
	*	Check if an instance already exists, else instantiate and return object
	*	
	*/
	public static function get_instance($projectRoot = WS_PATH_ROOT) {
	
		// CHECK: and instantiate
		return isset(self::$instance) ? self::$instance : trigger_error('RequestHandler class not yet instatiated, use instantiate() instead of get_instance()', E_USER_ERROR);
		
	}	
	


//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------	
	
	/** select_controller()
	*	
	*	Selects a application and its controller filename based on the controller array and the first parameter.
	*	If the application parameter can be skipped in the request URI (=default application) set the pos to false 
	*	
	*/
	public function select_controller($position, $standardApplication) {

		// Set standard application
		$this->standardApplication = $standardApplication;
		
		// CHECK: position for in position array
		if (!is_numeric($position) && $position != false) { 
			trigger_error('Numeric character or "false" expected by function RequestHandler::select_controller()', E_USER_ERROR);
		}
			
		if ($position == false) {
			
			// USE: standard passed application
			define('APPLICATION', $standardApplication);
			return APPLICATION . '.controller.php';
		
		} elseif (defined('PARAM'.(string)$position)) {
			
			$selectedApplication = constant('PARAM'.$position);

			if (isset($this->controllerArr[$selectedApplication])) {
				
				// SET: position in array
				$this->positionsArr[$position] = 'APPLICATION';
				
				// USE: selected application
				define('APPLICATION', $selectedApplication);
				return $this->controllerArr[APPLICATION];
			
			} else {
				
				// USE: standard passed application
				define('APPLICATION', $standardApplication);
				return APPLICATION . '.controller.php';	
			
			}
			
		} else {
			
			// USE: standard application
			define('APPLICATION', $standardApplication);
			return APPLICATION . '.controller.php';	// default controller file
		
		}		
	
	}
		

	/** select_language()
	*	
	*	Selects the application language by checking for a two letter word in the URI 
	*	at the specified position stated by $languagePosition. If $position not set or
	*	language parameter not found the standard language passed to the method is used.
	*	
	*/
	public function select_language($position, $standardLanguage) {

		// Set standard language
		$this->standardLanguage = $standardLanguage;
			
		// SET: position in array
		if (is_numeric($position)) {
			$this->positionsArr[$position] = 'LANGUAGE';		
		} elseif ($position != false) { 
			trigger_error('Numeric character or "false" expected by function RequestHandler::select_language()', E_USER_ERROR);
		}
		
		if ($position == false) {
			// USE: standard language
			define('LANGUAGE', $standardLanguage);
			return LANGUAGE;
		} elseif (defined('PARAM'.(string)$position)) {
				
			$selectedLanguage = constant('PARAM'.$position);
			
			if (Validator::match('languageParameter', $selectedLanguage)) {
				define('LANGUAGE', $selectedLanguage);
				return LANGUAGE;
			} else {
				// ERROR: wrong language parameter position, bad URI generation or someone is trying their own links
				trigger_error('Wrong language parameter position, bad URI passed or someone is trying their own links('.$_SERVER['REQUEST_URI'].')', E_USER_WARNING);
				
				// USE: standard language after reporting error
				define('LANGUAGE', $standardLanguage);
				return LANGUAGE;
			}
		
		} else {
			// USE: standard language (probably the generic domain call www.domain.com)
			define('LANGUAGE', $standardLanguage);
			return LANGUAGE;		
		}				
		
	}
		

	/** select_page()
	*	
	*	Selects the page to be displayed by looking for the passed parameter location in the URI.
	*	[[REVIEW]]If the specified parameter is not set a 404 should be called yet for now a redirect will happen, to the passed standard page.
	*	
	*/
	public function select_page($position, $standardPage) {

		// Set standard page
		$this->standardPage = $standardPage;
			
		// SET: position in array
		if (is_numeric($position)) {
			$this->positionsArr[$position] = 'PAGE';		
		} else { 
			trigger_error('Numeric character expected by function RequestHandler::select_page()', E_USER_ERROR);
		}
		
		if (defined('PARAM'.(string)$position)) {
			
			$selectedPage = constant('PARAM'.$position);
			
			define('PAGE', $selectedPage);
			return PAGE; 
		
		} else {
			
			define('PAGE', $standardPage);
			return PAGE;
			
		}				
		
	}
		

	/** select_scene()
	*	
	*	Selects the scene to be displayed by looking for the passed parameter location in the URI.
	*	If the specified parameter is not set a the passed standard scene will be chosen.
	*	
	*/
	public function select_scene($position, $standardScene) {

		// Set standard scene
		$this->standardScene = $standardScene;
				
		// SET: position in array
		if (is_numeric($position)) {
			$this->positionsArr[$position] = 'SCENE';		
		} else { 
			trigger_error('Numeric character expected by function RequestHandler::select_scene()', E_USER_ERROR);
		}
		
		if (defined('PARAM'.(string)$position)) {
			
			$selectedScene = constant('PARAM'.$position);
			
			define('SCENE', $selectedScene);
			return SCENE; 
		
		} else {
			
			define('SCENE', $standardScene);
			return SCENE;
			
		}				
		
	}	
		

	/** select()
	*	
	*	Selects the parameter to be displayed by looking for the passed parameter location in the URI.
	*	If the specified parameter is not set a the passed standard scene will be chosen.
	*	
	*/
	public function select($parameterConstant, $position, $standardValue) {
	
		// SET: position in array
		if (is_numeric($position)) {
			$this->positionsArr[$position] = $parameterConstant;		
		} else { 
			trigger_error('Numeric character expected by function RequestHandler::select()', E_USER_ERROR);
		}
		
		if (defined('PARAM'.(string)$position)) {
			
			$selectedValue = constant('PARAM'.$position);
			
			define($parameterConstant, $selectedValue);
			return constant($parameterConstant); 
		
		} else {
			
			define($parameterConstant, $standardValue);
			return constant($parameterConstant);
			
		}				
		
	}
		
			
	/** add_controller()
	*	
	*	Adds an application controller to the array.
	*	Keyword 'default' is selected when no other is found in URI
	*	
	*/
	public function add_controller($keyword, $file) {
	
		if (Validator::match('parameter', $keyword) && Validator::match('fileStrict', $file)) {
			$this->controllerArr[$keyword] = $file;
			return true;
		} else {
			return false;
		}
	
	}
	
	
	/** delete_controller()
	*	
	*	Deletes an application controller from the array.
	*	Keyword 'default' is protected from removal
	*	
	*/
	public function delete_controller($keyword) {
	
		if (isset($this->controllerArr[$keyword]) && $keyword != 'main') {
			unset($this->controllerArr[$keyword]);
			return true;
		} else {
			trigger_error('Controller deletion error, controller does not exist or trying to delete "main"', E_USER_WARNING);
			return false;
		}		
	
	}
	
	
	/** make_uri()
	*	
	*	Reconstructs the URI using positionsArr, current GET parameters (removed by default), new GET parameters.
	*	NewProtocol enables us to change the current protocol from http to https (example)
	*	
	*/	
	public function make_uri($newParameterArr = false, $removeParameterArr = false, $fromGetArr = false, $newGetArr = false, $newProtocol = false) {
		
		// REMOVE: uri parameters before building uri
		$positionsArr = array();
		if (is_array($removeParameterArr)) {
			foreach ($this->positionsArr as $position => $constant) {
				 if (!in_array($constant, $removeParameterArr)) {
				 	$positionsArr[$position] = $constant;
				}
			}
		} else {
			$positionsArr = $this->positionsArr;
		}
		
		// BUILD: uri from uri parameters, replace parameters available in newParameterArr
		$uri = '';
		foreach ($positionsArr as $constant) {
					
			if (isset($newParameterArr[$constant])) {
				$uri .= urlencode($newParameterArr[$constant]) . '/';
				unset($newParameterArr[$constant]);
				break;
			} else {
				$uri .= (constant($constant) != '') ? urlencode(constant($constant)) . '/' : '';
			}
		
		}
		
		// ADD: parameters left in newParametersArr
		if (is_array($newParameterArr)) {
		
			foreach ($newParameterArr as $parameter) {
				$uri .= urlencode($parameter) . '/';
			}
		
		}	
		
		// ADD: current available GET parameters
		if ($fromGetArr == 'ALL') {  // all current get vars
			
			foreach ($_GET as $key => $value) {
					$uri .= '_' . $key . '_' . urlencode($value) . '/';
			}
		
		} elseif ($fromGetArr == 'FROM_SESSION') {  // previously stored get vars
			
			if (isset($_SESSION['getStorage'])) {
				foreach ($_SESSION['getStorage'] as $sessionKey => $sessionArr) {
					if ($sessionKey == ('/'.$uri)) {
						foreach ($sessionArr as $key => $value) {
							$uri .= '_' . $key . '_' . urlencode($value) . '/';
						}
					}
				}
			}
		
		} elseif (is_array($fromGetArr)) {  // specific current get vars
			
			foreach ($_GET as $key => $value) {
				if (in_array($key, $fromGetArr) && $value != NULL && !isset($newGetArr[$key])) {
					$uri .= '_' . $key . '_' . urlencode($value) . '/';
				}
			}
		
		}
		
		// ADD: new GET parameters
		if (is_array($newGetArr)) {
		
			foreach ($newGetArr as $key => $value) {
				$uri .= '_' . $key . '_' . urlencode($value) . '/';
			}
		
		}		
		
		return ($newProtocol) ? $newProtocol . '://' . $_SERVER['HTTP_HOST'] . $this->projectRoot . $uri : $this->projectRoot . $uri;
	
	}	
	
	
	/** store_session()
	*	
	*	Stores the current page's get variables in the session cookie for later use
	*	
	*/
	public function store_session() {

		// Request uri: get clean request	
		$uriArr = explode('?', '/'.$this->requestUri);
		$uriArr = explode('/_', $uriArr[0]);
		$identifierStr = $uriArr[0] . '/';
		
		// Clean previously stored GET vars
		if (isset($_SESSION['getStorage'][$identifierStr])) {
			unset($_SESSION['getStorage'][$identifierStr]);
		}
		
		// GET page variables: store in session
		if (is_array($this->excludeGetArr)) {
			foreach ($_GET as $key => $value) {
				if (!in_array($key, $this->excludeGetArr) && !empty($value)) {
					$_SESSION['getStorage'][$identifierStr][$key] = $value;
				}
			}
			return true;
		} else {
			foreach ($_GET as $key => $value) {
				if (!empty($value)) {
					$_SESSION['getStorage'][$identifierStr][$key] = $value;
				}
			}
			return true;
		}		
	
	}
	
	
	/** delete_session()
	*	
	*	Deletes all stored sessions or any specific session passed as $identifier
	*	
	*/
	public function delete_session($identifier = NULL) {

		// Delete all or requested session
		if (!empty($identifier)) {
			unset($_SESSION['getStorage'][$identifier]);
		} else {
			unset($_SESSION['getStorage']);
		}
		
		return true;
	
	}
	
	
	/** secure_session()
	*	
	*	Checks if session is over https protocol and redirects if necesary
	*	
	*/
	public function secure_session($https = true) {

		// Check https status
		if ($https) {
			if (!Validator::equal_if_set('on', $_SERVER['HTTPS'])) {
				$redirectUrl = $this->make_uri(false, false, 'ALL', false, 'https');
				Core::redirect($redirectUrl);
			}
		} else {
			if (Validator::equal_if_set('on', $_SERVER['HTTPS'])) {
				$redirectUrl = $this->make_uri(false, false, 'ALL', false, 'http');
				Core::redirect($redirectUrl);
			}			
		}
		
		return true;
	
	}



//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHOD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------	
	
	/** define_parameters()
	*	
	*	Breaks up the URI in a parameter array and sets the parameters as constants
	*	
	*/
	protected function define_parameters($uriString) {

		// SAVE: a copy of the original request URI	
		$patternStr = Validator::escape_regex($this->projectRoot);	
		$uriString = preg_replace('/^('.$patternStr.')/', '', trim($uriString));
		
		// SAVE: a copy of the original request URI		
		$this->requestUri = CORE::clean($uriString);
		
		// REMOVE: possible GET parameters in URI
		$requestStrArr = explode('?', $this->requestUri);
		$parametersStr = trim($requestStrArr[0], '/');
		
		// EXPLODE: into request parameters and count them
		$this->requestParametersArr = ($parametersStr) ? explode('/', $parametersStr) : array();
		$this->countParameters = count($this->requestParametersArr);
		
		for ($i=0; $i < $this->countParameters; $i++) {
		
			
			if (Validator::match('getParameter', $this->requestParametersArr[$i])) {
				
				// SET: get variable
				$getArr = explode('_', $this->requestParametersArr[$i], 3);
				$_GET[$getArr[1]] = urldecode($getArr[2]);
		
			} elseif (Validator::match('parameter', $this->requestParametersArr[$i])) {
				
				// SET: parameter constant
				define('PARAM' . ($i + 1), $this->requestParametersArr[$i]);
		
			} else {
			
				// ERROR: incorrect request URI, send back to home
				trigger_error('Wrong URL parameter ('.$this->requestParametersArr[$i].')', E_USER_NOTICE);
				Core::redirect();
			
			}
			
			if ($i >= $this->maxParameters) { break; }
		
		}
	
	}
	
	
}
?>