<?php
error_reporting(0);
header('Content-Type: application/json');

class mamanee {

	private $pin = '';
	private $deviceId = '';
	private $walletId = '';
	private $endpoint = 'https://fasteasy.scbeasy.com';
	// public $pin='147258';
	// public $deviceId='871f28cb-5367-43d7-a121-b8c8eca3c173';
	// public $walletId='014000004042875';

	public function __construct($pin,$deviceId,$walletId) {
		$this->pin = $pin;
		$this->deviceId = $deviceId;
		$this->walletId = $walletId;
	}

	public function Curl($method, $url, $data,$header)  {
		if ($url=='/isprint/soap/preAuth' or $url=='/v1/merchants/transactions' or $url=='/v1/merchants/request/qr') { $HEADER=0; }else{ $HEADER=1; }
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>$this->endpoint.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> $HEADER,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return  $response;
	}

	public function Curl1($method, $url, $data,$header)  {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> $HEADER,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return  $response;
	}
	public function login()  {
		$data='{"deviceId":"'.$this->deviceId.'","jailbreak":"0","tilesVersion":"42","userMode":"INDIVIDUAL"}';
		$header=array(
			'Accept-Language:      th',
			'scb-channel:  APP',
			'user-agent:        Android/10;FastEasy/3.46.0/4926',
			'Content-Type:  application/json; charset=UTF-8',
			'Hos:  fasteasy.scbeasy.com:8443',
			'Connection:  close',
		);
		$res = $this->Curl("POST", '/v3/login/preloadandresumecheck',$data,$header);
		preg_match_all('/(?<=Api-Auth: ).+/', $res, $Auth);
		$Auth=$Auth[0][0];
		if ($Auth=="") {  echo "Auth error";  exit(); }
		$data='{"loginModuleId":"PseudoFE"}';
		$header=array(  'Api-Auth: '.$Auth,    'Content-Type: application/json',  );
		$res = $this->Curl("POST", '/isprint/soap/preAuth',$data, $header);
		$data = json_decode($res,true);
		$hashType=$data['e2ee']['pseudoOaepHashAlgo'];
		$Sid=$data['e2ee']['pseudoSid'];
		$ServerRandom=$data['e2ee']['pseudoRandom'];
		$pubKey=$data['e2ee']['pseudoPubKey'];

		$data="Sid=".$Sid."&ServerRandom=".$ServerRandom."&pubKey=".$pubKey."&pin=".$this->pin."&hashType=".$hashType;
		$header= array("Content-Type: application/x-www-form-urlencoded");
		$res = $this->Curl1("POST", 'http://206.189.47.27:80/pin/encrypt',$data, $header);

		$data='{"deviceId":"'.$this->deviceId.'","pseudoPin":"'.$res.'","pseudoSid":"'.$Sid.'"}';
		$header=array(
			'Api-Auth: '.$Auth,
			'Content-Type: application/json',
		);
		$res = $this->Curl("POST", '/v3/login',$data, $header);



		preg_match_all('/(?<=Api-Auth:).+/', $res, $Auth_result);
		$Auth1=$Auth_result[0][0];
		if ($Auth1=="") { echo "Auth error";exit();}
		return $Auth1;
	}

	public function genQr($amount,$ref)  {
		$Auth1 = $this->login();
		if ($Auth1=='Auth error') {
			$Auth1 = $this->login();
		}

		$data=json_encode(array('amount' => number_format($amount, 2, '.', ''), 'shopNote' => $ref, 'walletId' => $this->walletId));
		$header=array('scb-channel:  APP','Api-Auth: '.$Auth1,'Content-Type: application/json; charset=UTF-8');
		$res = $this->Curl("POST", '/v1/merchants/request/qr',$data, $header);
		return $res;

	}



	public function transactions()  {
		$Auth1 = $this->login();
		if ($Auth1=='Auth error') {
			$Auth1 = $this->login();
		}

		$startDate = date("Y-m-d", strtotime("first day of january this year"));
		$endDate = date('Y-m-d', strtotime("+1 day"));

		$data='{"walletList":[{"walletId":"'.$this->walletId.'","endDate":"'.$endDate.'","pageSize":"20","startDate":"'.$startDate.'","pageNumber":"1"}]}';
		$header=array('scb-channel:  APP','Api-Auth: '.$Auth1,'Content-Type: application/json; charset=UTF-8');
		$res = $this->Curl("POST", '/v1/merchants/transactions',$data, $header);
		return $res;
	}


	public function qrCode_generator($data = NULL){
            //0002010102125406150.3530710016A000000677010112011501075360001028602150140000030898020309testagain53037645802TH6304D57A
		if(is_null($data)){
			$data = 'Error';
		}else{
			return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$data.'&choe=UTF-8';
		}
	}


	public function sumecheck($deviceId){
		$data='{"deviceId":"'.$deviceId.'","jailbreak":"0","tilesVersion":"42","userMode":"INDIVIDUAL"}';
		$header=array(
			'Accept-Language:      th',
			'scb-channel:  APP',
			'user-agent:        Android/11;FastEasy/3.50.0/5329',
			'Content-Type:  application/json; charset=UTF-8',
			'Hos:  fasteasy.scbeasy.com:8443',
			'Connection:  close'
		);
		$res = $this->Curl1("POST", 'https://fasteasy.scbeasy.com/v3/login/preloadandresumecheck',$data, $header);

		return $res;
	}

}

// $api = new mamanee();
// echo $api->test();
//  echo $api->transactions();


// echo $api->qrCode_generator('00020101021254041.0030770016A000000677010112011501075360001028602150140000040428750315aAw2323203dsfgt53037645802TH63049BBC');


// $api = new mamanee();
//  $data = json_decode($api->genQr('19','ref001'),true);
// print_r($data);
 // $api = new mamanee();

 // echo $api->sumecheck('50ad4bf5-9329-439f-b8a2-30a5acf60073');

?>
