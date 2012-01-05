<?
include_once "facebook/src/facebook.php";

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

	function initQuick() {

		$facebook = new Facebook(array(
		  'appId'  => $this->appID,
		  'secret' => $this->secret,
		));
		$this->fbid = $facebook->getUser();

		if ($this->fbid) {
			try {
				// Proceed knowing you have a logged in user who's authenticated.
				$this->me = $facebook->api('/me');

				$this->name			= $this->cleanData($this->me['name']);//echo 88;
				$this->first_name	= $this->cleanData($this->me['first_name']);//echo 77;
				$this->last_name	= $this->cleanData($this->me['last_name']);//echo 66;
				$this->email		= $this->cleanData($this->me['email']);//echo 55;
				$this->gender		= $this->me['gender'];

				return true;
			} catch (FacebookApiException $e) {
				error_log($e);
				$this->fbid = null;
			}
		}
		return false;
	}
}

?>