TradeDoubler Php Api
============

Some php classes to handle requests for the TradeDoubler Affiliation Api, including:
- TradeDoubler Products Open Api.
- TradeDoubler Vouchers Open Api.

TradeDoubler Products Open Api
--------------

### Requeriments
- PHP Version >= 5.2.0, PECL json >= 1.2.0
- Client Url Library (cURL)
- TradeDoubler unique user api token.

### Api Documentation
http://dev.tradedoubler.com/products/publisher/

### Basic Usage

First of all, remember to change the api token with yours at the class constructor.
```php
public function __construct(){

		// Remember you have to change this token with your own token.
		// The token provided here is the TradeDoubler Demo One 
		// and may not work today.
		$this->api_token 	= '96CC0E0A10851500F10431D64EC5585BFC8597DF';

		// Api version
		$this->api_version 	= '1.0';

		// Api response, empty by default will return json data
		$this->api_response = '';

}
```
After setting your own token, you are ready to go =).

Let's make a basic search:

```php
$api = new TradeDoublerPOAPI();

$query_keys = array (

  "q"         =>    "Samsung Galaxy S4",
  "minPrice"  =>    0,
  "maxPrice"  =>    500,
  "limit"     =>    10
  
);

$response = $api->searchService($query_keys);
$data = $api->unserializeJson($response);
```
$data will contain an array with the parsed json response.

> Remember that you can find all the query_keys available at the api documentation.

TradeDoubler Vouchers Open Api
--------------

> Under construction
