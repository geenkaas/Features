<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** language.php
*	
*	This class's object is responsible for texts and strings in the right language
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Language extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	// DEFINE: fields
	protected	$stringsTable 	= '`sys_strings`';		// strings table in db
	protected	$stringPrefix 	= 'STRING_';			// string constant prefix
	protected	$textsTable 	= '`sys_texts`';		// strings table in db
	protected	$textPrefix 	= 'TEXT_';				// text constant prefix
	protected	$constantColumn	= 'constant';			// name of the column containing the constant
	protected	$standardLang;		// the standard language, by default the constant containing it
	protected	$language;		// the chosen language
	
	// DEFINE: language locale for mysql
	protected	$localeUnixArr = array (
					'en' => 'en_US',
					'nl' => 'nl_NL',
					'es' => 'ca_ES',
					'fr' => 'fr_FR',
					'de' => 'de_DE'
				);
	protected	$localeWinArr = array (
					'en' => 'english',
					'nl' => 'dutch',
					'es' => 'spanish',
					'fr' => 'french',
					'de' => 'german'
				);


//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	This is the function description for the constructor.
	*	
	*/
	function __construct($language, $defaultLanguage) {
	
		// SET: vars
		$this->language 	= $language;
		$this->standardLang = $defaultLanguage;
		
		// SET: php and mysql language settings
		$this->set_system_language($language);
		
	}
	



//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** get()
	*	
	*	Get the language definitions.
	*	
	*/
	public 	function get() {
		
		$columnStr = (LANGUAGE == $this->standardLang) ? $this->standardLang : $this->standardLang.','.LANGUAGE;
		
		// GET: Db (global) instance
		$db = Db::get_instance();
		
		// GET: language strings and texts
		$stringsRes = $db->query('
			SELECT `' . $this->constantColumn . '`, ' . $columnStr . ' 
			FROM ' . $this->stringsTable
		);
		$textsRes = $db->query('
			SELECT `' . $this->constantColumn . '`, ' . $columnStr . ' 
			FROM ' . $this->textsTable
		);
		
		// DEFINE: language constants
		while ($stringRow = $stringsRes->fetch_assoc()) {
		
			if (!empty($stringRow[LANGUAGE])) {
				define($this->stringPrefix . $stringRow[$this->constantColumn], Core::output($stringRow[LANGUAGE]));
			} else {
				define($this->stringPrefix . $stringRow[$this->constantColumn], Core::output($stringRow[$this->standardLang]));
			}
		
		}
		while ($textRow = $textsRes->fetch_assoc()) {
		
			if (!empty($textRow[LANGUAGE])) {
				define($this->textPrefix . $textRow[$this->constantColumn], Core::clean($textRow[LANGUAGE]));
			} else {
				define($this->textPrefix . $textRow[$this->constantColumn], Core::clean($textRow[$this->standardLang]));
			}
		
		}

	}	


	/** set_db_language()
	*	
	*	Set the language of MySQL & PHP. Affects the date names, strtoupper..etc.
	*	
	*/
	public 	function set_system_language($language) {
		
		// GET: Db (global) instance
		$db = Db::get_instance();
		
		// CHECK: if locale is defined & set mysql
		if (isset($this->localeUnixArr[$language]) && isset($this->localeWinArr[$language]) ) {
			
			// SET: PHP (unix/linux?) NEEDS TO BE IMPLEMENTED TO database
			if (!stristr(PHP_OS, 'WIN')) {
				setlocale(LC_TIME, $this->localeUnixArr[$language]);
			} else {
				setlocale(LC_TIME, $this->localeWinArr[$language]);
			}
			
			// SET: MySQL (uses the unix locale strings)
			$result = $db->query('
				SET lc_time_names = "' . $this->localeUnixArr[$language] . '"
			');
			
			return $result;
			
		}
		
		return false;
	
	}


}
?>