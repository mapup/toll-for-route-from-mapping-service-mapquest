<?php
//using mapquestmaps API

//Source and Destination Coordinates
$SOURCE = '1717NHarwoodSt,Dallas,TX75201,UnitedStates';
$DESTINATION = '15055InwoodRd,Addison,TX75001,UnitedStates';

//mapquest api key..
$key = 'mapquest.api.key';

$url='http://www.mapquestapi.com/directions/v2/route?key='.$key.'&from='.urlencode($SOURCE).'&to='.urlencode($DESTINATION).'&fullShape=true';

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
				      "x-api-key: tollguru.api.key"),
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