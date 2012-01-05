<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** idealhandler.php
*	
*	This is a class that handles ideal payments
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class IdealHandler extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------
	
	// DEFINE: setting fields
	protected	$bank;
	protected	$pspId;
	protected	$state;
	protected	$shaPass;
	protected	$method					= 'post';
	
	protected	$orderId;
	protected	$amount;
	protected	$currency				= 'EUR';
	protected	$language 				= 'nl_NL';
	protected	$customerName;
	protected	$customerEmail;
	protected	$customerZip;
	protected	$customerAddress;
	protected	$customerCity;
	protected	$customerTown;
	protected	$customerTelephone;
	protected	$orderDescription;
	
	// DEFINE: payment design fields 
	protected	$title 					= 'Betalen met iDEAL';
	protected	$backgroundColor 		= '#FFFFFF';
	protected	$textColor				= '#666666';
	protected	$tableBackgroundColor	= '#FFFFFF';
	protected	$tableTextColor			= '#666666';
	protected	$buttonBackgroundColor	= '#999999';
	protected	$buttonTextColor		= '#FFFFFF';
	protected	$logoUrl;
	protected	$fontName				= 'Arial';
	
	// DEFINE: process urls
	protected	$acceptUrl;
	protected	$declineUrl;
	protected	$exceptionUrl;
	protected	$cancelUrl;
	
	// DEFINE: pre process form
	protected	$submitButtonSrc;
	protected	$cancelButtonSrc;
	protected	$cancelButtonUrl;
	
	

//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------
	
	/** __constructor()
	*	
	*	The constructor sets a couple of necessary variables.
	*	
	*/
	function __construct($bank, $pspId, $shaPass, $inProduction = false) {
	
		$this->bank = $bank;
		$this->pspId = $pspId;
		$this->shaPass = $shaPass;
		$this->state = ($inProduction) ? 'prod' : 'test';
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------	

	/** display_form()
	*	
	*	This method builds the post form for activating an iDEAL payment
	*	
	*/
	public function display_form($orderId, $amount) {
	
		// SET: passed variables
		$this->orderId = $orderId;
		$this->amount = $amount;
		
		// BUILD: form for particular bank
		if ($this->bank == 'ABNAMRO') {
			
			// GENERATE: SHASign
			$sha = sha1($this->orderId . ($this->amount * 100) . $this->currency . $this->pspId . $this->shaPass);	
			
			// BUILD: form
			$form = '
				<form method="' . $this->method . '" action="https://internetkassa.abnamro.nl/ncol/' . $this->state . '/orderstandard.asp" id="idealForm" name="idealForm" class="standardForm">
					<!-- general documentation parameters -->
					<input type="hidden" name="PSPID" value="' . $this->pspId . '" />
					<input type="hidden" name="orderID" value="' . $this->orderId . '" />
					<input type="hidden" name="amount" value="' . number_format($this->amount * 100, 0, '', '') . '" />
					<input type="hidden" name="currency" value="' . $this->currency . '" />
					<input type="hidden" name="language" value="' . $this->language . '" />
					<input type="hidden" name="CN" value="' . $this->customerName . '" />
					<input type="hidden" name="EMAIL" value="' . $this->customerEmail . '" />
					<input type="hidden" name="ownerZIP" value="' . $this->customerZip . '" />
					<input type="hidden" name="owneraddress" value="' . $this->customerAddress . '" />
					<input type="hidden" name="ownercty" value="' . $this->customerCity . '" />
					<input type="hidden" name="ownertown" value="' . $this->customerTown . '" />
					<input type="hidden" name="ownertelno" value="' . $this->customerTelephone . '" />
					<input type="hidden" name="COM" value="' . $this->orderDescription . '" />
					
					<!-- check before the payment: see chapter 5 -->
					<input type="hidden" name="SHASign" value="' . $sha . '" />
					
					<!-- layout information: see documentation chapter 6 -->
					<input type="hidden" name="TITLE" value="' . $this->title . '" />
					<input type="hidden" name="BGCOLOR" value="' . $this->backgroundColor . '" />
					<input type="hidden" name="TXTCOLOR" value="' . $this->textColor . '" />
					<input type="hidden" name="TBLBGCOLOR" value="' . $this->tableBackgroundColor . '" />
					<input type="hidden" name="TBLTXTCOLOR" value="' . $this->tableTextColor . '" />
					<input type="hidden" name="BUTTONBGCOLOR" value="' . $this->buttonBackgroundColor . '" />
					<input type="hidden" name="BUTTONTXTCOLOR" value="' . $this->buttonTextColor . '" />
					<input type="hidden" name="LOGO" value="' . $this->logoUrl . '" />
					<input type="hidden" name="FONTTYPE" value="' . $this->fontName . '" />
					
					<!-- post payment redirection: see chapter 7 -->
					<input type="hidden" name="accepturl" value="' . $this->acceptUrl . '" />
					<input type="hidden" name="declineurl" value="' . $this->declineUrl . '" />
					<input type="hidden" name="exceptionurl" value="' . $this->exceptionUrl . '" />
					<input type="hidden" name="cancelurl" value="' . $this->cancelUrl . '" />
					<div class="buttons">
						<a href="' . $this->cancelButtonUrl . '"><img src="' . $this->cancelButtonSrc . '" id="clear" alt="' . STRING_MISC_CANCEL . '" /></a><input id="submit" name="submit" type="image" src="' . $this->submitButtonSrc . '" alt="' . STRING_MISC_PAY_WITH_IDEAL . '" />
					</div>
				</form>
			';
		
		} else {
		
			// ERROR: unknown bank
			trigger_error('Sorry, I don\'t know this bank code', E_USER_ERROR);
			
		} 
		
		return $form;
		
	}
	


//------------------------------------------------------------------------------------------------------------------
// VALIDATION METHODS DEFINITION
//------------------------------------------------------------------------------------------------------------------

	/** validate_postback()
	*	
	*	This method validates an iDEAL payment postbank from the payment provider
	*	
	*/
	public function validate_postback() {
		
		if ($this->bank == 'ABNAMRO') {
			
			// SET: posted variables
			if (isset($_GET['SHASIGN'])) {
				$this->orderId 	= $_GET['orderID'];
				$this->amount	= $_GET['amount'];
				$this->currency	= $_GET['currency'];
			} else {
				return false;
			}
			
			// VALIDATE: post data
			$sha = strtoupper(sha1($this->orderId . $this->currency . $this->amount . $_GET['PM'] . $_GET['ACCEPTANCE'] . $_GET['STATUS'] . $_GET['CARDNO'] . $_GET['PAYID'] . $_GET['NCERROR'] . $_GET['BRAND'] . $this->shaPass));
			if (Core::clean($sha) == Core::clean($_GET['SHASIGN'])) {
				if (Validator::match('id', $_GET['orderID'])) {
					return Core::clean($_GET['orderID']);
				} else {
					return false;
				}
			} else {
				return false;
			}
					
		} else {
		
			// ERROR: unknown bank
			trigger_error('Sorry, I don\'t know this bank code', E_USER_ERROR);
			
		} 		
		
	}
	
	
}
?>