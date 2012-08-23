<?php
#Declare Variables
#PUSHOVER SETTINGS
$app_api = 'YOUR_APP_KEY';
$client_api = 'YOUR_CLIENT_SECRET';
#TRAIN SETTINGS
$train_number = '659';

#DATE
$date = date('Y-m-d');

#GET TIME OFFSET

// Connect to database "download" using: dbname , username , password 
$link = mysql_connect('localhost', 'dev', 'Alpha7Uno') or die("Could not connect: " . mysql_error());
mysql_select_db("dev") or die(mysql_error());

$query = mysql_query("SELECT offset FROM viarail");

$row = mysql_fetch_row($query);

$offset = $row[0];


#URL
$url = "http://reservia.viarail.ca/tsi/GetTrainStatus.aspx?l=en&TsiCCode=VIA&TsiTrainNumber=$train_number&DepartureDate=$date&ArrivalDate=$date";

#DO THINGS

$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($c, CURLOPT_URL, $url);
$contents = curl_exec($c);
$err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
curl_close($c);
#echo $contents; 

$start = strpos($contents, '<CurrentStatus>');
$end = strpos($contents, '</CurrentStatus>', $start);
$data = substr($contents, $start, $end-$start+4);
$data = $paragraph = html_entity_decode(strip_tags($data));

$data = str_replace('min.','', $data);
$data = substr($data,0,-2);

echo $data;

if(abs($offset - $data) >= 5){
	
	mysql_query("UPDATE viarail SET offset='$data'");
	
curl_setopt_array($ch = curl_init(), array(
  CURLOPT_URL => "https://api.pushover.net/1/messages",
  CURLOPT_POSTFIELDS => array(
  "token" => "$app_api",
  "user" => "$client_api",
  "message" => "The Train is running $data minutes late",
)));
curl_exec($ch);
curl_close($ch);
	
	
}

?>