<?php
//using mapquestmaps API

//Source and Destination Coordinates
//Dallas, TX
$SOURCE = '1717NHarwoodSt,Dallas,TX75201,UnitedStates';
//Addison, Texas
$DESTINATION = '15055InwoodRd,Addison,TX75001,UnitedStates';

//mapquest api key..
$key = 'Nia5zuOUAjquY8hOJP33BjoneNWynTNM';
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
//print_r(array_keys($data_mapquest));
$data_new = $data_mapquest['route'];
$new_data = $data_new['shape'];
$shape_points=$new_data['shapePoints'];
//print_r($shape_points);
//polyline..
require_once(__DIR__.'/Polyline.php');
$polyline_mapquest = Polyline::encode($shape_points);

//echo $polyline_mapquest;
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
				      "x-api-key: G9ttbFqbt4hHhHQ3HrRrQRrTJLLDtF2F"),
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
 var_dump(json_decode($response, true));

//$data = var_dump(json_decode($response, true));
//print_r($data);

?>