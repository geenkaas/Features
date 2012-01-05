<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** currency.php
*	
*	Currency conversion using European Central Bank exchange rates.
*	Using feed http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/


class Currency extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------
	
	// Define fields
	protected 	$table		= 'currencies';  // query used to get data;
	protected 	$feedUrl	= 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';  // where the feed comes from
    protected	$xml;                   // internal holder for SimpleXML object
    protected 	$fromCode;    			// source currency
    protected 	$toCode;    			// dest currency
    protected 	$fromRate;              // source rate
    protected 	$toRate;                // dest rate
    
	// Run time variables
	protected 	$currenciesArr;			// array containing codes, currencies and currency names.
	protected 	$conversionRate;        // computed concersion rate.
	
	

//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR METHOD	
//------------------------------------------------------------------------------------------------------------------
		
    /** __constructor()
	*
	*	Checks if there's a cached version of the file.
	*	If yes, gets the data. If that's OK, end
	*	If no, get a new one and cache, failing over to the 
	*	old cache in the event of failure.
	*
    */
    public function __construct($url = false){
		
		// Import db object
		$db = Db::get_instance();
		       
		// Check and set url if passed
		if (!empty($url)) {
            $this->feedUrl = $url;
        }
        
        // Have the currencies been cached today
		$checkRes = $db->query('
			SELECT id 
			FROM ' . $this->table . ' 
			WHERE CURDATE() != DATE_FORMAT(cached, "%Y-%m-%d")
		');
		
		// Try to get rates for today if needed
		if ($checkRes->num_rows >= 1) {
			$this->get_currencies();
		}
        
        // Get and set currencies array
		$currenciesRes = $db->query('
			SELECT code, rate, name 
			FROM ' . $this->table
		);
		while ($row = $currenciesRes->fetch_assoc()) {
			$this->currenciesArr[$row['code']] = array (
				'rate' => $row['rate'],
				'name' => $row['name']
			);
		}

    
	}
	


//------------------------------------------------------------------------------------------------------------------
// PUBLIC METHODS	
//------------------------------------------------------------------------------------------------------------------

    /** from_to()
	*
    *	Setter for source & dest currency codes
	*
    */
    public function from_to($currencyFrom, $currencyTo) {
        
		// Set passed codes and rates
		$this->fromCode = $currencyFrom;
		$this->fromRate = $this->currenciesArr[$currencyFrom]['rate'];
        $this->toCode = $currencyTo;
		$this->toRate = $this->currenciesArr[$currencyTo]['rate'];
		
		// Return conversion rate
		$this->conversionRate = (1 / (float) $this->fromRate) * (float) $this->toRate;
		return $this->conversionRate;
    
	}

	
	/** convert() 
	*
	*	Converts a specific amount of source currency to dest currency and does the formatting
	*
	*/
    public function convert($amount, $decimalPoint = '.', $decimals = '0') {
		
		// Check if currencies and rates have been set
		if (!empty($this->conversionRate)) {
			$result = $this->conversionRate * (float)$amount;
			$decimalPoint = ($decimalPoint == ',') ? ',' : '.';
			$thousandPoint = ($decimalPoint == ',') ? '.' : ',';
			$decimals = (is_integer($decimals)) ? $decimals : '0'; 
			$formattedResult = number_format($result, $decimals, $decimalPoint, $thousandPoint);
			return $formattedResult;
		} else {
			trigger_error('From and To currencies havn\'t been set yet', E_USER_ERROR);
		}
		
    }
	

	
//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHODS	
//------------------------------------------------------------------------------------------------------------------
  
    /** get_currencies()
	*
	*	Utility function to populate rates by getting them from the XML feed.
	*	Unfortunately, XPath on Windows doesn\'t correctly parse 
	*	
	*	//Cube/Cube/Cube[@currency = ''.$this->from.'']/@rate
	*	
	*	- seems to be something to do with namespaces.  
	*	So we have to use a SimpleXML syntax instead, which works :)
	*
    */    
    protected function get_currencies() {
		
		// Import db object
		$db = Db::get_instance();
				
		// Try to load xml into a SimpleXml object
		$feed = @file_get_contents($this->feedUrl);
		if (!$feed) {
			trigger_error('Couldn\'t load the feed from: ' . $this->feedUrl, E_USER_WARNING);  // error getting feed from url            
		} else {
			$this->xml = simplexml_load_string($feed);  // get SimpleXml object
		}
        if (!is_object($this->xml)) {
            trigger_error('No feed data', E_USER_ERROR);  // error getting data from from
            return false;
        }
        
		// Update currencies in db (EUR is base currency)
        foreach ($this->xml->Cube->Cube->Cube as $item) {
            $updateRes = $db->query('
				UPDATE ' . $this->table . ' 
				SET rate = ' . (float)$item['rate'] . ', cached = NOW()
				WHERE code = "' . Core::clean($item['currency']) . '"
			');
		}
		$updateRes = $db->query('
			UPDATE ' . $this->table . ' 
			SET rate = 1.0000, cached = NOW()
			WHERE code = "EUR"
		');

    }


		
}
?>