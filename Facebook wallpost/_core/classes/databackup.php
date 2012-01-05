<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** databackup.php
*	
*	This is the class to be used to backup mysql databases
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class DataBackup extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: configuration fields
	protected $classVersion		= '1.0.0';			// class version is added to backup file
	protected $newLine 			= "\r\n";			// new line characters
	protected $dropTables 		= true;				// add "drop tables" command?
	protected $structureOnly 	= false;			// structure only or all data
	protected $comments			= true;				// add comments to sql file?
	protected $fileDateFormat	= 'Ymd His';		// date() format for file name in case no filename given
	protected $fileNamePrefix;						// add prefix to file name
	protected $database			= DB_NAME;			// database to back up


	// DEFINE: run time fields
	protected $backupFileName;						// backup file name
	protected $tables 			= array();			// array of tables to be backed up
	protected $error;								// encountered errors	
	
	
	
//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The constructor currently doesn't do anything
	*	
	*/
	public function __construct() {
	
		/* Class instantiation code */
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// PERFORM METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** perform()
	*	
	*	This is method is called to perform a database backup. Task options are STRING, SAVE or DOWNLOAD
	*	FileName is optional and either the complete path or just a file name depending on the task
	*	
	*/
	public function perform($type = 'SAVE', $folderPath = NULL, $compress = false) {
		
		// GET: sql
		if (!($sql = $this->retrieve())) {
			return false;
		}
		if ($type == 'SAVE') {
			if (!empty($folderPath)) {
				if (file_exists($folderPath)) {
					// BUILD: formated file path
					$this->backupFileName = $this->fileNamePrefix . $this->database . ' ' . date($this->fileDateFormat);
					$this->backupFileName .= ($compress ? '.sql.gz' : '.sql');
					$filePath = $folderPath . $this->backupFileName;
				} else {
					// ERROR: folder path not found
					trigger_error('CLASS:DataBackup; METHOD:perform; ERROR:Folder path does not exist', E_USER_ERROR);
				}
			} else {
				// ERROR: no file name or
				trigger_error('CLASS:DataBackup; METHOD:perform; ERROR:Folder path not passed', E_USER_ERROR);
			}
			return $this->save_to_file($filePath, $sql, $compress);
		} elseif ($type == 'DOWNLOAD') {
			// BUILD: formated file name
			$this->backupFileName = $this->fileNamePrefix . $this->database . ' ' . date($this->fileDateFormat);
			$this->backupFileName .= ($compress ? '.sql.gz' : '.sql');
			return $this->download_file($this->backupFileName, $sql, $compress);
		} else {
			return $sql;
		}
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// SUPPORT METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** get_tables()
	*	
	*	Get available tables in the database.
	*	
	*/
	protected function get_tables() {
		
		// IMPORT: database and process objects (singleton)
		$db			= Db::get_instance();
		
		$value = array();
		// GET: tables
		if (!($result = $db->query('SHOW TABLES'))) {
			return false;
		}
		while ($row = $result->fetch_row()) {
			if (empty($this->tables) || in_array($row[0], $this->tables)) {
				$value[] = $row[0];
			}
		}
		if (!sizeof($value)) {
			$this->error = 'No tables found in database.';
			return false;
		}
		return $value;
	
	}


	/** dump_table()
	*	
	*	Get available table data.
	*	
	*/
	protected function dump_table($table) {
		
		// IMPORT: database and process objects (singleton)
		$db			= Db::get_instance();
		
		$value = NULL;
		// LOCK: table against writing
		$db->query('LOCK TABLES ' . $table . ' WRITE');
		
		// BUILD: comments
		if ($this->comments) {
			$value .= '#' . $this->newLine;
			$value .= '# Table structure for table `' . $table . '`' . $this->newLine;
			$value .= '#' . $this->newLine . $this->newLine;
		}
		
		// BUILD: table sql
		if ($this->dropTables) {
			$value .= 'DROP TABLE IF EXISTS `' . $table . '`;' . $this->newLine;
		}
		if (!($result = $db->query('SHOW CREATE TABLE ' . $table))) {
			return false;
		}
		$row = $result->fetch_assoc();
		$value .= str_replace("\n", $this->newLine, $row['Create Table']) . ';';
		$value .= $this->newLine . $this->newLine;
		if (!$this->structureOnly) {
			if ($this->comments) {
				$value .= '#' . $this->newLine;
				$value .= '# Dumping data for table `' . $table . '`' . $this->newLine;
				$value .= '#' . $this->newLine . $this->newLine;
			}
			$value .= $this->get_inserts($table);
		}
		$value .= $this->newLine . $this->newLine;
		
		// UNLOCK: table
		$db->query('UNLOCK TABLES');
		
		return $value;
		
	}


	/** get_inserts()
	*	
	*	Get data inserts.
	*	
	*/
	protected function get_inserts($table) {
	
		// IMPORT: database object (singleton)
		$db	= Db::get_instance();
			
		$value = NULL;
		// GET: table data
		if (!($result = $db->query('SELECT * FROM ' . $table))) {
			return false;
		}
		while ($row = $result->fetch_row()) {
			$values = NULL;
			foreach ($row as $data) {
				$values .= '\'' . addslashes($data) . '\', ';
			}
			$values = substr($values, 0, -2);
			$value .= 'INSERT INTO ' . $table . ' VALUES (' . $values . ');' . $this->newLine;
		}
		return $value;
		
	}


	/** retrieve()
	*	
	*	Get data inserts.
	*	
	*/
	protected function retrieve() {
	
		// IMPORT: database object (singleton)
		$db	= Db::get_instance();
		
		// GET: current database
		$currentRes = $db->query('SELECT DATABASE() AS db');
		$currentArr = $currentRes->fetch_assoc();
		$currentDatabase = $currentArr['db'];
			
		$value = NULL;
		// BUILD: comments
		if ($this->comments) {
			$value .= '#' . $this->newLine;
			$value .= '# MySQL database dump' . $this->newLine;
			$value .= '# Created by GRANVILLE CORE DataBackup class, ver. ' . $this->classVersion . $this->newLine;
			$value .= '#' . $this->newLine;
			$value .= '# Generated: ' . date('M j, Y') . ' at ' . date('H:i') . $this->newLine;
			$value .= '# MySQL version: ' . $db->server_info . $this->newLine;
			$value .= '# PHP version: ' . phpversion() . $this->newLine;
			$value .= '#' . $this->newLine;
			$value .= '# Database: `' . $currentDatabase . '`' . $this->newLine;
			$value .= '#' . $this->newLine . $this->newLine . $this->newLine;
		}
		
		// GET: tables
		if (!($tables = $this->get_tables())) {
			return false;
		}
		
		// BUILD: tables dump
		foreach ($tables as $table) {
			if (!($tableDump = $this->dump_table($table))) {
				$this->error = $db->error;
				return false;
			}
			$value .= $tableDump;
		}
		return $value;
		
	}


	/** save_to_file()
	*	
	*	Save backup to file.
	*	
	*/
	protected function save_to_file($filePath, $sql, $compress) {
		
		if ($compress) {
			if (!($zf = gzopen($filePath, 'w9'))) {
				// ERROR: can't create the backup output file, check permissions and file path
				trigger_error('CLASS:DataBackup; METHOD:save_to_file; ERROR:Can\'t create the backup output file, check permissions and file path', E_USER_ERROR);
			}
			gzwrite($zf, $sql);
			gzclose($zf);
		} else {
			if (!($f = fopen($filePath, 'w'))) {
				// ERROR: can't create the backup output file, check permissions and file path
				trigger_error('CLASS:DataBackup; METHOD:save_to_file; ERROR:Can\'t create the backup output file, check permissions and file path', E_USER_ERROR);
			}
			fwrite($f, $sql);
			fclose($f);
		}
		return true;
	
	}


	/** download_file()
	*	
	*	Download backup to file.
	*	
	*/
	protected function download_file($fileName, $sql, $compress) {
		
		// SET: headers for download
		header('Content-disposition: filename=' . $fileName);
		header('Content-type: application/octetstream');
		header('Pragma: no-cache');
		header('Expires: 0');
		echo ($compress ? gzencode($sql) : $sql);
		
		return true;
		
	}
	
}
?>