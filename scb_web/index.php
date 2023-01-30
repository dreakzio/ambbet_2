<?php
echo "test";
$curl = curl_init();

curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://scbweb.aba444.com/api/scb',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS =>'{
    "username": "yameen1",
    "password": "Ab456321"
}',
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
