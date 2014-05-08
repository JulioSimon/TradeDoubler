<?php
/*
	The MIT License (MIT)
	
	Copyright (c) 2014 JulioSimon
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

/*
	Description:
	
			A php class named as TradeDoublerPOAPI in order to handle the requests
			to the TradeDoubler Api (Products Open API).
			More information at (http://dev.tradedoubler.com/products/publisher/)
			
	Author:	Julio José Simón Gil
	E-mail:	juliojosesimongil@gmail.com
	GitHub:	https://github.com/JulioSimon
*/

//namespace <YourSpace>;

class TradeDoublerPOAPI {
	
	/**
	 *	@string 
	 *
	 *	The api header to start building our url.
	 */
	private $header = 'https://api.tradedoubler.com/';
	
	/**
	 *	@array 
	 *
	 *	The request event status.
	 */
	private $event_status = array();
	
	/**
	 *	@string
	 *
	 *	TradeDoubler unique (SHA-1) token in order to earn
	 *	access to the Products Open Api.
	 *	You can find your token by logging on your TD account
	 *	and going to "Account" > "Manage tokens".
	 */
	private $api_token;
	
	/**
	 *	@string
	 *
	 *	The api version you want to access.
	 */
	private $api_version;
	
	/**
	 *	@string
	 *
	 *	Response type:
	 *	.xml | .json | empty (default json)
	 */
	private $api_response;
	
	/**
	 *	Constructor
	 *
	 *	Loads the default configuration.
	 */
	public function __construct(){
		
		// Remember you have to change this token with your own token.
		// The token provided here is the TradeDoubler Demo One 
		// and may not work today.
		$this->api_token 	= '6523B0E2C339018570FF54856DF193523332D60F';
		
		// Api version
		$this->api_version 	= '1.0';
		
		// Api response, empty by default will return json data
		$this->api_response = '';
		
	}
	
	/**
	 *	CONFIGURATION FUNCTIONS
	 */
	
	/**
	 *	In case you want to make "On the fly" configuration changes.
	 *
	 *	@param		$token			Your own TradeDoubler Api Token
	 *	@param		$version		The api version you want to access
	 *	@param		$response		Response type
	 *
	 *	@return		$this			The current object
	 */
	public function loadConfiguration($token, $version, $response = ''){
		
		$this->api_token 	= $token;
		$this->api_version	= $version;
		
		switch($response){
			
			case '.xml': 
			case '.json':	$this->api_response = $response; break;
			
			default:		$this->api_response = ''; break;
			
		}
		
		return $this;
		
	}
	
	/**
	 *	SERVICE FUNCTIONS
	 */
	
	/**
	 *	The Search Service
	 *
	 *	Limitations: Max. 10000 products per request (Acording to the API docs)
	 *
	 *	Options: You can find all the input params here:
	 *			 (http://dev.tradedoubler.com/products/publisher/#Query_keys)
	 *
	 *	@param		array $query_keys	Array with all the input params you want 
	 *									to use on your product search. If 
	 *									empty, it will return all the products
	 *									on the TradeDoubler Database.
	 *
	 *	@return		mixed				Xml, json raw data or false boolean
	 */ 
	public function searchService(array $query_keys){
		
		// First, we need to make the api request url
		$request_url = $this->urlConstructor($query_keys);
		
		// Make the request
		$data = $this->getServiceData($request_url);
		
		// And finally we check for the response events.
		if($this->event_status[0] != 200){
			
			$response = false;
			
		} else {
			
			$response = $data;
			
		}
		
		return $response;
		
	}
	
	/**
	 *	UTLILITY FUNCTIONS
	 */
	 
	/**
	 *	Event Handler, check for errors or success messages
	 *
	 *	@param		string $header		cURL response header
	 *
	 *	@return		array $event
	 */
	private function eventHandler($header){
	
		// Initialize the $event var
		$event = array();
		
		// We need to extract the HTTP code from the header
		$http_code = array();
		preg_match('/\d\d\d/', $header, $http_code);
		
		// Now we evaluate the http code
		switch($http_code[0]){
			
			case 200: $event = array($http_code[0],'Success.'); break;
			case 400: $event = array($http_code[0],'Error: Invalid XML, Json or date format.'); break;
			case 404: $event = array($http_code[0],'Error: The URL does not exist.'); break;
			case 405: $event = array($http_code[0],'Error: Invalid HTTP method.'); break;
			case 406: $event = array($http_code[0],'Error: Requesting invalid content type.'); break;
			case 415: $event = array($http_code[0],'Error: Sending invalid content type.'); break;
			case 500: $event = array($http_code[0],'Error: The API service is offline.'); break;
			default:  $event = array($http_code[0],'Error: Unknown error type.'); break;
			
		}
		
		return $event;
		
	}
	 
	/**
	 *	Get the data via cURL
	 *
	 *	@param		request_url		The full url request
	 *
	 *	@return		mixed
	 */
	private function getServiceData($request_url){
	 
	 	// Initialize cURL
	 	$session = curl_init($request_url);
	 	
	 	// Set the cURL options
	 	// We need the cURL header in order to handle errors.
	 	curl_setopt($session, CURLOPT_HEADER, true);
	 	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	 	
	 	// Make the request
	 	$response = curl_exec($session);
	 	
	 	// Close the session
	 	curl_close($session);
	 	
	 	// Separate the Header from the Data
	 	// Slow at large requests but necesary becaues of proxies!
	 	list($header, $data) = explode("\r\n\r\n", $response, 2);
	 	
	 	// Now we evaluate the header and upload the results to the event_status var.
	 	$this->event_status = $this->eventHandler($header);
	 	
	 	return $data;
	 
	}
	 
	/**
	 *	Url Constructor
	 *
	 *	@param		array $query_keys		Service options values
	 *
	 *  @return		string					The api request full url
	 */
	private function urlConstructor(array $query_keys){
		
		// Let's build the header url
		$url_header = $this->header . $this->api_version . "/products" . $this->api_response;
		
		// Now we build the middle of the url with all the input params provided at the $query_keys array
		// Initializing the variables to use in the loop
		$url_middle = '';
		$temp = '';
		
		foreach($query_keys as $key => $value){
			
			// Support for multiple entry values
			// Also we need to encode whitespaces and others special characters.
			if(is_array($value)){
				
				$last_subkey = end(array_keys($value));
				
				foreach($value as $subkey => $subvalue){
					
					if($subkey == $last_subkey){
						
						$temp .= rawurlencode($subvalue);
						
					} else {
						
						$temp .= rawurlencode($subvalue) . ",";
						
					}
					
				}
				
				$url_middle .= ";" . $key . "=" . rawurlencode($temp);
				
			} else {
				
				$url_middle .= ";" . $key . "=" . rawurlencode($value);
				
			}
			
		}
		
		// And finally, the api token
		$url_end = "?token=" . $this->api_token;
		
		// Let's join all the url parts to get the full url request and encode it.
		$full_url = $url_header . $url_middle . $url_end;
		
		return $full_url;
		
	}
	
	/**
   * Unserialize json string to array.
   *
   * @param      string $json		json string
   *
   * @return     mixed			array or false
   */
  public function unserializeJson($json){
    
    $result = false;
    
    if(function_exists("json_decode")){
        
      $result = json_decode($json, true);
        	
      if(!$result){
	        	
	       $result = false;
	        	
      }
            
    }

    return $result;
        
  }

}
