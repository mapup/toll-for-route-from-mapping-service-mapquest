<?php
//using mapquestmaps API

$MAPQUEST_API_KEY = getenv('MAPQUEST_API_KEY');
$MAPQUEST_API_URL = "http://www.mapquestapi.com/directions/v2/route";

$TOLLGURU_API_KEY = getenv('TOLLGURU_API_KEY');
$TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
$POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

//Source and Destination Coordinates
$source = 'Dallas, TX';
$destination = 'New York, NY';

$url=$MAPQUEST_API_URL.'?key='.$MAPQUEST_API_KEY.'&from='.urlencode($source).'&to='.urlencode($destination).'&fullShape=true';

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

//using tollguru API..
$curl = curl_init();

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$postdata = array(
	"source" => "mapquest",
	"polyline" => $polyline_mapquest
);

//json encoding source and polyline to send as postfields..
$encode_postData = json_encode($postdata);

curl_setopt_array($curl, array(
  CURLOPT_URL => $TOLLGURU_API_URL . "/" . $POLYLINE_ENDPOINT,
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
    "x-api-key: " . $TOLLGURU_API_KEY),
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
?>
