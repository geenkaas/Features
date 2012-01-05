<?
if ( !function_exists('json_decode') ) {
	function json_decode($json) {
		$comment = false;
		$out = '$x=';

		for ($i=0; $i<strlen($json); $i++) {
			if (!$comment) {
				if (($json[$i] == '{') || ($json[$i] == '['))       $out .= ' array(';
				else if (($json[$i] == '}') || ($json[$i] == ']'))   $out .= ')';
				else if ($json[$i] == ':')    $out .= '=>';
				else                         $out .= $json[$i];
			}
			else $out .= $json[$i];
			if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;
		}
		eval($out . ';');
		return $x;
	}
	function safeJSON_chars($data) {
		$aux = str_split($data);
		foreach($aux as $a) {
			$a1 = urlencode($a);
			$aa = explode("%", $a1);
			foreach($aa as $v) {
				if($v!="") {
					if(hexdec($v)>127) {
						$data = str_replace($a,"&#".hexdec($v).";",$data);
					}
				}
			}
		}
		return $data;
	}
	function replace_unicode_escape_sequence($match) {
		return $match;
		//return iconv('UCS-2', 'UTF-8', $match);
		//return preg_replace('/\\\\U0*([0-9a-fA-F]{1,5})/', '&#x\1;', $match);;//return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}
}

class FB {
	var $appID			= '266805616685359';
	var $secret			= 'a9ce35742eb917db494dc7dc23b72164';
	var $access_token	= "";
	var $cookie;

	var $me				= null;
	var $fbid			= -1;
	var $name			= "";
	var $first_name		= "";
	var $last_name		= "";
	var $email			= "";
	var $gender			= "";

	function __construct() {
	}

	function cleanData($input) {
		return $input;
	}

	function getAccessToken() {
		if (isSet($_COOKIE['fbs_' . $this->appID])) {
			parse_str(trim($_COOKIE['fbs_' . $this->appID], '\\"'), $args);
			ksort($args);
			$payload = '';
			foreach ($args as $key => $value) {
				if ($key != 'sig') $payload .= $key . '=' . $value;
			}

			if (md5($payload . $this->secret) != $args['sig']) return null;
			$this->cookie = $args;

			if ($this->cookie) {
				$this->access_token = $this->cookie['access_token'];
				return true;
			}
		}
		return false;
	}

	function initQuick() {
		$args		= array();
		//print_r($_COOKIE);
		if ($this->getAccessToken()) {
			//echo "<br />".$this->access_token."<br />";
			$this->me = $this->getFromFB('https://graph.facebook.com/me?access_token='.$this->access_token);
			//echo 3;
			//echo "<pre>";print_r($this->me);echo "</pre>";
			//echo 2;
			if ($this->me != null) {
				$this->fbid			= $this->me['id'];
				$this->name			= $this->cleanData($this->me['name']);//echo 88;
				$this->first_name	= $this->cleanData($this->me['first_name']);//echo 77;
				$this->last_name	= $this->cleanData($this->me['last_name']);//echo 66;
				$this->email		= $this->cleanData($this->me['email']);//echo 55;
				$this->gender		= $this->me['gender'];
/*
				echo "fbid = ".$this->fbid."<br />";
				echo "name = ".$this->name."<br />";
				echo "first_name = ".$this->first_name."<br />";
				echo "last_name = ".$this->last_name."<br />";
				echo "email = ".$this->email."<br />";
				echo "gender = ".$this->gender."<br />";
				//print_r($this);
/**/
				return true;
			}
		}
		return false;
	}
	function getFromFB($url) {
		try {
			$jsonFromServer = @file_get_contents($url);
			//echo $jsonFromServer."<br /><br />\n\n";
			//echo "<pre>|".$jsonFromServer."|</pre>";
			if ($jsonFromServer != null) {
				if ($jsonFromServer != '') {
					//$jsonFromServer = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $jsonFromServer);
					//$jsonFromServer = unenc_utf16_code_units($jsonFromServer);
					//return $this->jsonDecode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $jsonFromServer),true);
					return json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $jsonFromServer),true);
				}
			}
		} catch(Exception $e) {
			echo $e;
		}
		return null;
	}
	function jsonDecode ($json) { 
		$json = str_replace(array("\\\\", "\\\""), array("&#92;", "&#34;"), $json); 
		$parts = preg_split("@(\"[^\"]*\")|([\[\]\{\},:])|\s@is", $json, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); 
		foreach ($parts as $index => $part) { 
			if (strlen($part) == 1) { 
				switch ($part) {
					case "[": 
					case "{": 
					$parts[$index] = "array("; 
					break; 
					case "]": 
					case "}": 
					$parts[$index] = ")"; 
					break; 
					case ":": 
					$parts[$index] = "=>"; 
					break;    
					case ",": 
					break; 
					default: 
					return null; 
				} 
			} else { 
				if ((substr($part, 0, 1) != "\"") || (substr($part, -1, 1) != "\"")) { 
					return null; 
				} 
			} 
		} 
		$json = str_replace(array("&#92;", "&#34;", "$"), array("\\\\", "\\\"", "\\$"), implode("", $parts)); 
		return eval("return $json;");
	} 
}

?>