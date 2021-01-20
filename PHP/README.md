# [](https://developer.mapquest.com/)

### Get key to access MapQuest (if you have an API key skip this)
#### Step 1: Signup/Login
* Create an account to access [MapQuest Developer Network](https://developer.mapquest.com/)
* go to signup/login link https://developer.mapquest.com/user/login
* you will need to agree to MapQuest's Terms of Service https://hello.mapquest.com/terms-of-use

#### Step 2: Getting api key
* Login to Mapquest Developer Network
* Go to https://developer.mapquest.com/user/me/apps
* You will be presented with a default key
* You are also create additional keys, as per you requirements


With this in place, make a GET request: http://www.mapquestapi.com/directions/v2/route?key='.$key.'&from='.$SOURCE.'&to='.$DESTINATION.'&fullShape=true

### Note:
* Mapquest doesn't send us route geometry as `polyline`, instead it
  sends an array containing coordinates. We can encode it as `polyline`
* We are sending `fullShape` as `true` so that we get a detailed
  geometry instead of an aproximation.
* Code to get the `polyline` can be found at https://github.com/emcconville/google-map-polyline-encoding-tool


```php

//extracting polyline from the JSON response..
$data_mapquest = json_decode($response, true);
$shape_points=$data_mapquest['route']['shape']['shapePoints'];

//polyline..
require_once(__DIR__.'/Polyline.php');
$polyline_mapquest = Polyline::encode($shape_points);


```

```php

//using mapquestmaps API

//Source and Destination Coordinates
$SOURCE = '1717NHarwoodSt,Dallas,TX75201,UnitedStates';
$DESTINATION = '15055InwoodRd,Addison,TX75001,UnitedStates';

//mapquest api key..
$key = 'mapquest.api.key';

$url='http://www.mapquestapi.com/directions/v2/route?key='.$key.'&from='.$SOURCE.'&to='.$DESTINATION.'&fullShape=true';

//connection..
$mapquest = curl_init();

curl_setopt($mapquest, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($mapquest, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($mapquest, CURLOPT_URL, $url);
curl_setopt($mapquest, CURLOPT_RETURNTRANSFER, true);

//getting response from mapquestapis..
$response = curl_exec($mapquest);
$err = curl_error($mapquest);

curl_close($mapquest);

if ($err) {
	  echo "cURL Error #:" . $err;
} else {
	  echo "200 : OK\n";
}

//extracting polyline from the JSON response..
$data_mapquest = json_decode($response, true);
$shape_points=$data_mapquest['route']['shape']['shapePoints'];

//polyline..
require_once(__DIR__.'/Polyline.php');
$polyline_mapquest = Polyline::encode($shape_points);


```

Note:
* Code to get the `polyline` can be found at https://github.com/emcconville/google-map-polyline-encoding-tool
* We extracted the polyline for a route from mapquest Maps API
* We need to send this route polyline to TollGuru API to receive toll information

## [TollGuru API](https://tollguru.com/developers/docs/)

### Get key to access TollGuru polyline API
* create a dev account to receive a free key from TollGuru https://tollguru.com/developers/get-api-key
* suggest adding `vehicleType` parameter. Tolls for cars are different than trucks and therefore if `vehicleType` is not specified, may not receive accurate tolls. For example, tolls are generally higher for trucks than cars. If `vehicleType` is not specified, by default tolls are returned for 2-axle cars. 
* Similarly, `departure_time` is important for locations where tolls change based on time-of-the-day which can be passed through `$postdata`.

the last line can be changed to following

```php

//using tollguru API..
$curl = curl_init();

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$postdata = array(
	"source" => "gmaps",
	"polyline" => $polyline_mapquest
);

//json encoding source and polyline to send as postfields..
$encode_postData = json_encode($postdata);

curl_setopt_array($curl, array(
CURLOPT_URL => "https://dev.tollguru.com/v1/calc/route",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "POST",

// sending mapquest polyline to tollguru
CURLOPT_POSTFIELDS => $encode_postData,
CURLOPT_HTTPHEADER => array(
				      "content-type: application/json",
				      "x-api-key: tollguru_api_key"),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
	  echo "cURL Error #:" . $err;
} else {
	  echo "200 : OK\n";
}

//response from tollguru..
$data = var_dump(json_decode($response, true));
print_r($data);


```

The working code can be found in `php_curl_mapquest.php` file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
