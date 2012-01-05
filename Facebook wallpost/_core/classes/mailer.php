<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** mailer.php
*	
*	This is the mailer class. It has all the functionality needed to sent plain text annd html emails 
*	using either the php mail() function, sendmail, SMTP or qmail. It get its functionality by extending 
*	the phpmailer class
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/

//------------------------------------------------------------------------------------------------------------------
// LOAD
//------------------------------------------------------------------------------------------------------------------

// Require class to be extended
require(FS_PATH_PLUGINS . 'phpmailer/class.phpmailer.php');



class Mailer extends PHPMailer {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: fields



//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The constructor sets the content type to html, checks for SMTP settings and 
	*	configures for SMTP mailing if possible.
	*	
	*/
	public function __construct() {
	
		$this->ContentType = 'text/html';
		
		if (defined('STRING_CLASS_MAILER_SETTING_SMTP_MAIL')) {
		
			if (STRING_CLASS_MAILER_SETTING_SMTP_MAIL == 'enabled') {
			 
				$this->Host = STRING_CLASS_MAILER_SETTING_SMTP_HOST; 
				$this->Port = STRING_CLASS_MAILER_SETTING_SMTP_PORT; 
			
				if (STRING_CLASS_MAILER_SETTING_SMTP_AUTHENTICATION == 'enabled') {
				 
					 $this->SMTPAuth  = true; 
					 $this->Username  = STRING_CLASS_MAILER_SETTING_SMTP_USER; 
					 $this->Password  = STRING_CLASS_MAILER_SETTING_SMTP_PASS; 
				
				} 
				$this->Mailer = 'smtp';
				
			}
		
		}
		
		$this->Setlanguage('en', FS_PATH_PLUGINS . 'phpmailer/language/');
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** is_html($bool)
    *
	* Sets message type to HTML.  
    * @param bool $bool
    * @return void
	*
    */
    public function is_html($bool) {
            $this->IsHTML($bool);
    }


    /** is_smtp()
    *
	* Sets Mailer to send message using SMTP.
    * @return void
	*
    */
    public function is_smtp() {
        $this->IsSMTP();
    }


    /** is_mail()
	*
    * Sets Mailer to send message using PHP mail() function.
    * @return void
	*
    */
    public function is_mail() {
        $this->IsMail();
    }


    /** is_sendmail()
	*
    * Sets Mailer to send message using the $Sendmail program.
    * @return void
	*
    */
    public function is_sendmail() {
        $this->IsSendmail();
    }


    /** is_qmail()
	*
    * Sets Mailer to send message using the qmail MTA. 
    * @return void
    *
	*/
    public function is_qmail() {
        $this->IsQmail();
    }

	
	
//------------------------------------------------------------------------------------------------------------------
// SET METHODS	
//------------------------------------------------------------------------------------------------------------------
	
	/** set_from()
	* 
	*	set from header.
	*	- Email 
	*	- [Name]
	*
	*/
	public function set_from($email, $name = '') {
		$this->From = $email;
		$this->FromName = $name;
	}
	
	
	/** set_subject() 
	*
	*	set email subject.
	*	- Email 
	*	- [Name]
	*
	*/
	public function set_subject($subject = 'SUBJECT NOT SET') {
		$this->Subject = $subject;
	}
	
	
	/** set_body()
	*
	*	set email body.
	*	- Email 
	*	- [Name]
	*
	*/
	public function set_body($body = 'BODY NOT SET') {

		$this->Body = $body;
		
	}
	
	
	/** set_alt_body()
	*
	*	set email alternative (plain) body.
	*	- Email 
	*	- [Name]
	*
	*/
	public function set_alt_body($altBody = 'ALTBODY NOT SET') {

		$this->AltBody = $altBody;
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// ADD METHODS	
//------------------------------------------------------------------------------------------------------------------

    /** add_to()
	*
    * 	Adds a "To" address.  
    * 	@param string $address
    * 	@param string $name
    * 	@return void
    *
	*/
    public function add_to($address, $name = "") {
        $this->AddAddress($address, $name = "");
    }
	

    /** add_cc()
	*
    * 	Adds a "Cc" address. Note: this function works
    * 	with the SMTP mailer on win32, not with the "mail"
    * 	mailer.  
    * 	@param string $address
    * 	@param string $name
    * 	@return void
	*
    */
    public function add_cc($address, $name = "") {
        $this->AddCC($address, $name = "");
    }


    /** add_bcc()
	*
    * 	Adds a "Bcc" address. Note: this function works
    * 	with the SMTP mailer on win32, not with the "mail"
    * 	mailer.  
    * 	@param string $address
    * 	@param string $name
    * 	@return void
	*
    */
    public function add_bcc($address, $name = "") {
        $this->AddBCC($address, $name = "");
    }


    /** add_reply_to()
	*
    * 	Adds a "Reply-to" address.  
    * 	@param string $address
    * 	@param string $name
    * 	@return void
	*
    */
    public function add_reply_to($address, $name = "") {
        $this->AddReplyTo($address, $name = "");
    }



//------------------------------------------------------------------------------------------------------------------
// CLEARING METHODS	
//------------------------------------------------------------------------------------------------------------------

    /** send()
	*
    *	Sends the email
	*
    
	public function send() {
		$this->Send();
    }
	*/


//------------------------------------------------------------------------------------------------------------------
// CLEARING METHODS	
//------------------------------------------------------------------------------------------------------------------

    /** clear_recipients()
	*
    *	Clears all recipients assigned in the TO, CC and BCC
	*	Clears all reply to's
	* 	Clears all attachments
    * 	array.  Returns void.
    * 	@return void
	*
    */
	public function clear_recipients() {
		$this->ClearAllRecipients();
    }
	
}
?>