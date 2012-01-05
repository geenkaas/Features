<?
//**********************************************************************//
//                                                                      //
//  Filename:       .                                                   //
//  Description:        .                                               //
//  Author:         David Marques Ferreira                              //
//                  info@mqmedia.nl                                     //
//  Version:        0.0.1                                               //
//  Last Update:    02-06-2011                                          //
//                                                                      //
//  (thanks to ... gebruik gemaakt van...                               //
//  Copyright (C) 2008-2011 David Marques Ferreira                      //
//                                                                      //
//**********************************************************************//  


/******

Module:			DB (database abstraction layer)
Settings:		external (settings.db.php)
Tool:			-
Created by:		David Marques Ferreira
Version:		0.1
Last update:	22-12-2003 13:06

Comment:		
Future:			mssql, oracle, postgre
				error handling
				query processing (create secure query with arrays)

define("HOST",			"localhost");
define("SOURCE",		"dbfairminds");
define("USER",			"fairmindsdbadmin");
define("WW",			"idUS67#(");
******/

define("HOST",			"localhost");
define("SOURCE",		"dbfms");
define("USER",			"fmsdbadmin");
define("WW",			"JD&@72is");

class DB {

	var $host;
	var $source;
	var $user;
	var $password;
	var $db;
	var $connected;
	var $result;
	var $lastquery = "";
	var $lastID;
	var $success;

	static $m_pInstance;

	function __construct($utf8 = false) {
		$this->host		= HOST;
		$this->source	= SOURCE;
		$this->user		= USER;
		$this->password	= WW;
		echo 7;
		$this->connect($utf8);
		if ($this->isConnected()) {
			@mysql_select_db($this->source, $this->db) or $this->db_error("select fout");
		} else {
			echo $this->db_error();
		}
	}
	public static function getInstance() {
		if (!self::$m_pInstance) {
			self::$m_pInstance = new DB();
		}

		return self::$m_pInstance;
	}

	function connect($utf8 = false) {
		$this->db = mysql_connect($this->host, $this->user, $this->password);
		if (!$this->db) {
			$this->connected = false;
		} else {
			if ($utf8) mysql_query("SET NAMES 'utf8'");
			$this->connected = true;
		}
		return $this->connected;
	}

	function disconnect() {
		if ($this->connected) {
			$this->connected = !mysql_close($this->db);
		}
		return $this->connected;
	}

	function isConnected() {
		return $this->connected;
	}

	function setCharset($charSet) {
		mysql_set_charset($charSet);
	}
	function nonquery($query, $showError = false) {
		$this->success = false;
		$this->result = mysql_query($query);
		$this->lastquery = $query;
		if ($this->result) {
			$this->lastID = mysql_insert_id();
			return true;
		} else {
			if ($showError) echo $query."<br />".mysql_error();
		}
		return false;
	}

	function query($query) {
		$this->success = false;
		$this->result = mysql_query($query);
		$this->lastquery = $query;
		if ($this->result) {
			$this->lastID = mysql_insert_id();
			$this->success = true;
			return $this->result;
		} else {
			$this->success = true;
			return null;
		}
	}

	function getLastID() {
		return $this->lastID;
	}

	function insert_id() {
		return mysql_insert_id();
	}

	// fetch functions
	function getRows() {
		return mysql_fetch_row($this->result);
	}

	function getArray() {
		if (mysql_error() != '') echo mysql_error().$this->lastquery;
		return mysql_fetch_array($this->result);
	}

	function getObject() {
		return mysql_fetch_object($this->result);
	}

	function getNumRows() {
		return mysql_num_rows($this->result);
	}

	function getCountRows($table) {
		$result = mysql_query("SELECT COUNT(*) AS count FROM ".$table);
		if ($row = mysql_fetch_array($result)) {
			return $row['count'];
		} else return 0;
	}

	function db_error($debug="") {
		return $debug."<BR>".mysql_error()."<BR>".$this->lastquery;
		// query niet printen bij publishen -> db exposure
	}

	function clearResults() {
		$this->result = null;
	}
}

?>