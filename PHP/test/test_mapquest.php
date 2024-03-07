<?php
//using mapquestmaps API

$MAPQUEST_API_KEY = getenv('MAPQUEST_API_KEY');
$MAPQUEST_API_URL = "http://www.mapquestapi.com/directions/v2/route";

$TOLLGURU_API_KEY = getenv('TOLLGURU_API_KEY');
$TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
$POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

// Explore https://tollguru.com/toll-api-docs to get the best of all the parameters that Tollguru has to offer
$request_parameters = array(
  "vehicle" => array(
      "type" => "2AxlesAuto"
  ),
  // Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
  "departure_time" => "2021-01-05T09:46:08Z"
);

// From and To locations
function getPolyline($from, $to){
  global $MAPQUEST_API_KEY, $MAPQUEST_API_URL;

  $url=$MAPQUEST_API_URL.'?key='.$MAPQUEST_API_KEY.'&from='.urlencode($from).'&to='.urlencode($to).'&fullShape=true';
  //connection..
  $mapquest = curl_init();

  curl_setopt($mapquest, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($mapquest, CURLOPT_SSL_VERIFYPEER, false);

  curl_setopt($mapquest, CURLOPT_URL, $url);
  curl_setopt($mapquest, CURLOPT_RETURNTRANSFER, true);

  // Getting response from MapQuest API
  $response = curl_exec($mapquest);
  $err = curl_error($mapquest);

  curl_close($mapquest);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    echo "200 : OK\n";
  }
  // Extracting polyline from the JSON response
  $data_mapquest = json_decode($response, true);
  $shape_points=$data_mapquest['route']['shape']['shapePoints'];

  // Polyline
  require_once(__DIR__.'/Polyline.php');
  $p_mapquest = Polyline::encode($shape_points);

  return $p_mapquest;
}

// Calling getPolyline function
// Testing starts here
require_once(__DIR__.'/test_location.php');
foreach ($locdata as $item) {
$polyline_mapquest = getPolyline($item['from'], $item['to']);

// Using TollGuru API..
$curl = curl_init();

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$postdata = array(
	"source" => "mapquest",
	"polyline" => $polyline_mapquest,
  ...$request_parameters
);

// JSON encoding source and polyline to send as postfields
$encode_postData = json_encode($postdata);

curl_setopt_array($curl, array(
  CURLOPT_URL => $TOLLGURU_API_URL . "/" . $POLYLINE_ENDPOINT,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",

  // Sending MapQuest polyline to TollGuru
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

// Response from TollGuru
$data = json_decode($response, true);

$tag = $data['route']['costs']['tag'];
$cash = $data['route']['costs']['cash'];

$dumpFile = fopen("dump.txt", "a") or die("unable to open file!");
fwrite($dumpFile, "from =>");
fwrite($dumpFile, $item['from'].PHP_EOL);
fwrite($dumpFile, "to =>");
fwrite($dumpFile, $item['to'].PHP_EOL);
fwrite($dumpFile, "polyline =>".PHP_EOL);
fwrite($dumpFile, $polyline_mapquest.PHP_EOL);
fwrite($dumpFile, "tag =>");
fwrite($dumpFile, $tag.PHP_EOL);
fwrite($dumpFile, "cash =>");
fwrite($dumpFile, $cash.PHP_EOL);
fwrite($dumpFile, "*************************************************************************".PHP_EOL);
echo 'from: '.$item['from'].' to '.$item['to'].'';
echo "\n";
echo "tag = ";
print_r($data['route']['costs']['tag']);
echo "\ncash = ";
print_r($data['route']['costs']['cash']);
echo "\n";
echo "**************************************************************************\n";
}
?>
