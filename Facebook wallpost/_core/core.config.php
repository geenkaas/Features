<?PHP 
/** core.config.php
	
	Copyright (c) 2008 Granville, All Rights Reserved.
	http://www.granville.nl

*/

//----------------------------------------------------------------------------------------------------
// DEFINE PROJECT
//----------------------------------------------------------------------------------------------------
define('PROJECT', 'Undefined');



//----------------------------------------------------------------------------------------------------
// FILESYSTEM PATHS
//----------------------------------------------------------------------------------------------------

// DEFINE: filesystem directories
define('FS_PATH_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');	// web root
define('FS_PATH_CORE', FS_PATH_ROOT . '_core/');			// core system files
define('FS_PATH_CLASSES', FS_PATH_CORE . 'classes/');		// main classes folder



//----------------------------------------------------------------------------------------------------
// WEBSERVER PATHS
//----------------------------------------------------------------------------------------------------

// DEFINE: webserver folders
define('WS_PATH_ROOT', '/');									// web root	  
define('WS_PATH_CORE', WS_PATH_ROOT . '_core/');				// application core


 
//----------------------------------------------------------------------------------------------------
// DATABASE
//----------------------------------------------------------------------------------------------------

// DEFINE: database connection settings


define('DB_SERVER_OLD','localhost');				// database server address, 'localhost' is often used when db resides on local machine
//define('DB_NAME_OLD', 'dbfairminds');				// database name
//define('DB_USER_OLD', 'fairmindsdbadmin');		// database user 
//define('DB_PASS_OLD','idUS67#(');					// database user password

define('DB_NAME_OLD', 'dbfms');
define('DB_USER_OLD', 'fmsdbadmin');
define('DB_PASS_OLD', 'JD&@72is');

define('DB_PCONNECT', false);					// use persistant connections






//----------------------------------------------------------------------------------------------------
// DATE SETTINGS
//----------------------------------------------------------------------------------------------------

// SET: default timezone for all functions, can be overwritten on a per function basis	
date_default_timezone_set('Europe/Amsterdam');



//----------------------------------------------------------------------------------------------------
// AUTOLOAD DEFINITION
//----------------------------------------------------------------------------------------------------

/** __autoload()
*
*	The function is caled whenever a class is referenced that hasn't yet been defined in the script. 
*	The function then looks for the class file, if not found, a fatal error is thrown.
*
*/
function __autoload($class)
{
		if (strpos(strtolower($class), 'wp_') === false) {
			require_once(FS_PATH_CLASSES . strtolower($class) . ".php");
		}
}
?>