<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** curl.php
*	
*	Curl based HTTP Client 
*	Simple but effective OOP wrapper around Curl php lib.
*	Contains common methods needed for getting data from url, setting referrer, credentials, 
* 	sending post data, managing cookies, etc
*	
*	@version 1.2
*	@package dinke.net
*	@copyright &copy; 2008 Dinke.net
*	@author Dragan Dinic <dragan@dinke.net>
*
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/

/**
 * Sample usage:
 * $curl = &new Curl_HTTP_Client();
 * $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
 * $curl->set_user_agent($useragent);
 * $curl->store_cookies("/tmp/cookies.txt");
 * $post_data = array('login' => 'pera', 'password' => 'joe');
 * $html_data = $curl->send_post_data(http://www.foo.com/login.php, $post_data);
 */
 
class Curl extends Object {


//----------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//----------------------------------------------------------------------------------------------------
	
	/**
	 * Curl handler
	 * @access protected
	 * @var resource
	 */
	protected $ch ;

	/**
	 * set debug to true in order to get usefull output
	 * @access protected
	 * @var string
	 */
	protected $debug = false;

	/**
	 * Contain last error message if error occured
	 * @access protected
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * Set to false if Safe mode is on or BaseDir to prevent errors.
	 * @access protected
	 * @var bool
	 */
	protected $followLocation;



//----------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//----------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	* 	Curl_HTTP_Client constructor
	* 	@param boolean debug
	* 	@access public
	*	
	*/
	public function __construct($debug = false, $followLocation = true) {
		
		$this->debug = $debug;
		$this->followLocation = $followLocation;
		$this->init();
	}
	


//----------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS
//----------------------------------------------------------------------------------------------------	
	
	/**	init()
	*
	*	Init Curl session	 
	*	@access public
	*
	*/
	protected function init() {
		
		// Initialize curl handle
		$this->ch = curl_init();

		//Set various options

		//Set error in case http return code bigger than 300
		curl_setopt($this->ch, CURLOPT_FAILONERROR, true);

		// Allow redirects
		if ($this->followLocation) { curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true); }
		
		// Use gzip if possible
		curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip, deflate');

		// Do not veryfy ssl
		// this is important for windows as well for being able to access pages with non valid cert
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		
	}
	


//----------------------------------------------------------------------------------------------------
// SET METHODS
//----------------------------------------------------------------------------------------------------

	/**	set_credentials()
	*
	*	Set username/pass for basic http auth
	*	@param string user
	*	@param string pass
	*	@access public
	*
	*/
	public function set_credentials($username, $password)  {
		curl_setopt($this->ch, CURLOPT_USERPWD, $username.':'.$password);
	}


	/** set_referrer()
	*
	*	@param string referrer url 
	*	@access public
	*
	*/
	public function set_referrer($referrerUrl) {
		curl_setopt($this->ch, CURLOPT_REFERER, $referrerUrl);
	}


	/**	set_user_agent()
	*
	*	Set client's useragent
	*	@param string user agent
	*	@access public
	*
	*/
	public function set_user_agent($useragent) {
		curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
	}
	

	/** include_response_headers()
	*
	*	Set to receive output headers in all output functions
	*	@param boolean true to include all response headers with output, false otherwise
	*	@access public
	*
	*/
	public function include_response_headers($value) {
		curl_setopt($this->ch, CURLOPT_HEADER, $value);
	}


	/**	set_proxy()
	*
	*	Set proxy to use for each curl request
	*	@param string proxy
	*	@access public
	*
	*/
	public function set_proxy($proxy) {
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
	}


	/**	set_option()
	*
	*	Set any request option
	*	@param string option
	*	@param mixed value
	*	@access public
	*
	*/
	public function set_option($option, $value) {
		curl_setopt($this->ch, $option, $value);
	}
	


//----------------------------------------------------------------------------------------------------
// SEND & GET METHODS
//----------------------------------------------------------------------------------------------------

	/**	send_post_data()
	*
	* 	Send post data to target URL	 
	* 	return data returned from url or false if error occured
	* 	@param string url
	* 	@param mixed post data (assoc array ie. $foo['post_var_name'] = $value or as string like var=val1&var2=val2)
	* 	@param string ip address to bind (default null)
	* 	@param int timeout in sec for complete curl operation (default 10)
	* 	@return string data
	* 	@access public
	*
	*/
	public function send_post_data($url, $postdata, $ip=null, $timeout=10)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);


		//generate post string
		$post_array = array();
		if(is_array($postdata))
		{		
			foreach($postdata as $key=>$value)
			{
				$post_array[] = urlencode($key) . "=" . urlencode($value);
			}

			$post_string = implode("&",$post_array);

			if($this->debug)
			{
				echo "Url: $url\nPost String: $post_string\n";
			}
		}
		else 
		{
			$post_string = $postdata;
		}

		// set post string
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);


		//and finally send curl request
		$result = curl_exec($this->ch);

		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
		
	}

	
	/** get_data()
	*
	* 	fetch data from target URL	(previously called fetch_url()) 
	* 	return data returned from url or false if error occured
	*	@param string url	 
	* 	@param string ip address to bind (default null)
	* 	@param int timeout in sec for complete curl operation (default 5)
	* 	@return string data
	* 	@access public
	*
	*/
	public function get_data($url, $ip=null, $timeout=10)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL,$url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET,true);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
		$result = curl_exec($this->ch);

		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
	}

	
	/** get_data_redirect()
	*
	* 	fetch data from target URL if 	(previously called fetch_url()) 
	* 	return data returned from url or false if error occured
	*	@param string url	 
	* 	@param string ip address to bind (default null)
	* 	@param int timeout in sec for complete curl operation (default 5)
	* 	@return string data
	* 	@access public
	*
	*/
	public function get_data_redirect($url, $ip=null, $timeout=10)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL,$url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET,true);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,true);
		
		// header
		curl_setopt($this->ch, CURLOPT_HEADER, true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
		$result = curl_exec($this->ch);
		
		// Check return code
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		
		// Check if redirect
		if ($httpCode == 301 || $httpCode == 302) {
			list($header) = explode("\r\n\r\n", $result, 2);
			$matches = array();
			preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
			$url = trim(array_pop($matches));
			$url_parsed = parse_url($url);
			if (isset($url_parsed)) {
				return $this->get_data_redirect($url, $ip, $timeout);
			}
		}
		
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
	}
				
				
				
	/** get_into_file()
	*
	* 	Fetch data from target URL
	* 	and store it directly to file	 	 
	* 	@param string url	 
	* 	@param resource value stream resource(ie. fopen)
	* 	@param string ip address to bind (default null)
	* 	@param int timeout in sec for complete curl operation (default 5)
	* 	@return boolean true on success false othervise
	* 	@access public
	*
	*/
	public function get_into_file($url, $fp, $ip=null, $timeout=5)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL,$url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);

		// store data into file rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $fp);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
		$result = curl_exec($this->ch);

		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}

			return false;
		}
		else
		{
			return true;
		}
	}

	
	/** send_multipart_post_data()
	*
	* 	Send multipart post data to the target URL	 
	* 	return data returned from url or false if error occured
	* 	(contribution by vule nikolic, vule@dinke.net)
	* 	@param string url
	*	@param array assoc post data array ie. $foo['post_var_name'] = $value
	* 	@param array assoc $fileFieldArr, contains file_field name = value - path pairs
	*	@param string ip address to bind (default null)
	* 	@param int timeout in sec for complete curl operation (default 30 sec)
	* 	@return string data
	* 	@access public
	*
	*/
	public function send_multipart_post_data($url, $postdata, $fileFieldArr=array(), $ip=null, $timeout=30)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);

		// disable Expect header
		// hack to make it working
		$headers = array("Expect: ");
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

		// initialize result post array
		$result_post = array();

		//generate post string
		$post_array = array();
		$post_string_array = array();
		if(!is_array($postdata))
		{
			return false;
		}

		foreach($postdata as $key=>$value)
		{
			$post_array[$key] = $value;
			$post_string_array[] = urlencode($key)."=".urlencode($value);
		}

		$post_string = implode("&",$post_string_array);


		if($this->debug)
		{
			echo "Post String: $post_string\n";
		}

		// set post string
		//curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);


		// set multipart form data - file array field-value pairs
		if(!empty($fileFieldArr))
		{
			foreach($fileFieldArr as $var_name => $var_value)
			{
				if(strpos(PHP_OS, "WIN") !== false) $var_value = str_replace("/", "\\", $var_value); // win hack
				$fileFieldArr[$var_name] = "@".$var_value;
			}
		}

		// set post data
		$result_post = array_merge($post_array, $fileFieldArr);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $result_post);


		//and finally send curl request
		$result = curl_exec($this->ch);

		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
	}

	
	/**	store_cookies()
	*
	* 	Set file location where cookie data will be stored and send on each new request
	* 	@param string absolute path to cookie file (must be in writable dir)
	* 	@access public
	*
	*/
	public function store_cookies($cookieFile)
	{
		// use cookies on each request (cookies stored in $cookie_file)
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $cookieFile);
	}
	
	
	/**	set_cookie()
	*
	*	Set custom cookie
	* 	@param string cookie
	* 	@access public
	*
	*/
	public function set_cookie($cookie)
	{		
		curl_setopt ($this->ch, CURLOPT_COOKIE, $cookie);
	}
	


//----------------------------------------------------------------------------------------------------
// TELL & CLOSE METHODS
//----------------------------------------------------------------------------------------------------

	/** tell_effective_url()
	*
	* Get last URL info 
	* usefull when original url was redirected to other location	
	* @access public
	* @return string url
	*
	*/
	public function tell_effective_url()
	{
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
	}


	/**	tell_http_response_code()
	*
	* 	Get http response code	 
	* 	@access public
	* 	@return int
	*
	*/
	public function tell_http_response_code()
	{
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}


	/** tell_error()
	*
	*	Return last error message and error number
	*	@return string error msg
	*	@access public
	*
	*/
	public function tell_error()
	{
		$error = 'Error number: ' .curl_errno($this->ch) ."\n";
		$error .='Error message: ' .curl_error($this->ch)."\n";

		return $error;
	}
	
	
	/** close()
	*
	*	Close curl session and free resource
	*	Usually no need to call this function directly
	*	in case you do you have to call init() to recreate curl
	*	@access public
	*
	*/
	public function close()
	{
		//close curl session and free up resources
		curl_close($this->ch);
	}
	
}
?>