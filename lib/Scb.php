<?php
error_reporting(0);
header('Content-Type: application/json');
class Scb{
	private $deviceId = '';
	private $api_refresh = '';
	private $accnum = '';
	private $cnt_re_login = 0;
	private $api_auth = "";

	public function Curl($method, $url, $header, $data, $cookie)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Android/9;FastEasy/3.58.0/6149');
		//curl_setopt($ch, CURLOPT_USERAGENT, 'okhttp/3.8.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($url != "https://fasteasy.scbeasy.com:8443/v3/transfer/confirmation"){
			curl_setopt($ch, CURLOPT_TIMEOUT, 45);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		}else{
			curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		}
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		//curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		if ($data) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		if ($cookie) {
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		}
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	public function __construct($deviceId,$api_refresh,$accnum) {
		$this->deviceId = $deviceId;
		$this->api_refresh = $api_refresh;
		if(strlen($accnum) != 10){
			echo '10 digital !!';
		}else if(strlen($this->api_refresh) > 6 || strlen($this->api_refresh) < 6){
			echo 'pin should have 6 digits!! : '.$this->accnum;
		}else{
			$this->accnum = $accnum;
			$this->Login();
		}
	}
	public function Login(){
		$this->cnt_re_login += 1;
		if($this->cnt_re_login > 1){
			echo 'Error re Login Acc : '.$this->accnum.' > 1 round please check token.';
			////exit();
		}
		/*$url = "https://fasteasy.scbeasy.com:8443/v1/login/refresh";
		$headers =  array(
			"api-refresh: ".$this->api_refresh,
			"cache-control: no-cache",
			"content-type: application/json"

		);
		$data = '{ "deviceId": "'.$this->deviceId.'" }';
		$res = $this->Curl("POST",$url,$headers,$data,false);
		$res = json_decode($res,true);
		$access_token =  $res['data']['access_token'];*/
		$access_token = "";
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com/v3/login/preloadandresumecheck',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> 1,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"deviceId":"'.$this->deviceId.'","jailbreak":"0","tilesVersion":"39","userMode":"INDIVIDUAL"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'user-agent:  Android/9;FastEasy/3.58.0/6149 ',
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0 ',
				'Content-Type:  application/json; charset=UTF-8',
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);


		preg_match_all('/(?<=Api-Auth: ).+/', $response, $Auth);
		$Auth=$Auth[0][0];

		if ($Auth=="") {
			echo 'error Login 1';
			//exit();
		}


		$curl1 = curl_init();

		curl_setopt_array($curl1, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com/isprint/soap/preAuth',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"loginModuleId":"PseudoFE"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'Api-Auth: '.$Auth,
				'user-agent:  Android/9;FastEasy/3.58.0/6149 ',
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0 ',
				'Content-Type:  application/json; charset=UTF-8'
			),
		));

		$response1 = curl_exec($curl1);
		curl_close($curl1);


		$data = json_decode($response1,true);

		$hashType=$data['e2ee']['pseudoOaepHashAlgo'];
		$Sid=$data['e2ee']['pseudoSid'];
		$ServerRandom=$data['e2ee']['pseudoRandom'];
		$pubKey=$data['e2ee']['pseudoPubKey'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://206.189.47.27:80/pin/encrypt",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "Sid=".$Sid."&ServerRandom=".$ServerRandom."&pubKey=".$pubKey."&pin=".$this->api_refresh."&hashType=".$hashType,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com/v1/fasteasy-login',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> 1,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"deviceId":"'.$this->deviceId.'","pseudoPin":"'.$response.'","pseudoSid":"'.$Sid.'"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'Api-Auth: '.$Auth,
				'user-agent:  Android/9;FastEasy/3.58.0/6149 ',
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0 ',
				'Content-Type:  application/json; charset=UTF-8'
			),
		));

		$response_auth = curl_exec($curl);
		curl_close($curl);

		preg_match_all('/(?<=Api-Auth:).+/', $response_auth, $Auth_result);
		$Auth1=$Auth_result[0][0];
		if ($Auth1=="") {
			echo 'error Login 2';
			//exit();
		}
		$access_token = trim($Auth1);
		if(empty($access_token)){
			echo 'error auth token';
			//exit();
		}
		/*$strFileName = "token_".$this->accnum.".txt";
		$objFopen = fopen($strFileName, 'w');
		fwrite($objFopen, $access_token);*/
		$this->api_auth = $access_token;

	}
	private function Access_token(){

		//return file_get_contents("token_".$this->accnum.".txt");
		return $this->api_auth;
	}
	public function GetBalance(){
		$url = "https://fasteasy.scbeasy.com:8443/v2/deposits/casa/details";

		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data = '{
			"accountNo": "'.$this->accnum.'"
			}';
		$res = $this->Curl("POST",$url,$headers,$data,false);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){
			$this->Login();
			return $this->cnt_re_login > 1 ? null : $this->GetBalance();
		}
		return $res;

	}
	public function getTransaction(){
		date_default_timezone_set("Asia/Bangkok");
		$startDate = date('Y-m-d', strtotime("-1 day"));
		$endDate = date('Y-m-d', strtotime("+1 day"));
		$url = "https://fasteasy.scbeasy.com:8443/v2/deposits/casa/transactions";
		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data_scb = '{ "accountNo": "'.$this->accnum.'", "endDate": "'.$endDate.'", "pageNumber": "1", "pageSize": 35, "productType": "2", "startDate": "'.$startDate.'" }';
		$res = $this->Curl("POST",$url,$headers,$data_scb,false);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){
			$this->Login();
			return $this->cnt_re_login > 1 ? [] : $this->getTransaction();
		}

		$json = json_decode($res, true);
		if (isset($json['status'])) {
			if ($json['status']['description'] === 'สำเร็จ') {
				$data = [
					'deposit' => [],
					'withdraw' => [],
				];
				//return $json['data']['txnList'];
				foreach($json['data']['txnList']  as $v ){
					if($v['txnCode']['code'] == 'X1'){
						$description_full = $v['txnRemark'];
						preg_match_all ("/SCB x(.*) /U", $v['txnRemark'], $scbbank);
						preg_match_all ("/ ((.*)) \/X([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)/U", $v['txnRemark'], $otherbank);
						$bankno = "";
						if($scbbank[0]){
							$bankno =  str_replace(" x","_",implode($scbbank[0]));
						} else {
							$bankno = str_replace("(","",str_replace(") ","_",str_replace("/X","",implode($otherbank[0]))));
						}

						$Date = date("d/m/Y", strtotime($v['txnDateTime']));
						$Time = date("H:i", strtotime($v['txnDateTime']));
						$data['deposit'][] = ["date" => $Date, "time" => $Time, "deposits" => $v['txnAmount'], "description" => trim($bankno), "description_full" => trim($description_full) ];

					}else if($v['txnCode']['code'] == 'X2'){
						$description_full = $v['txnRemark'];
						preg_match_all ("/SCB x(.*) /U", $v['txnRemark'], $scbbank);
						preg_match_all ("/ ((.*)) \/X([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)/U", $v['txnRemark'], $otherbank);
						$bankno = "";
						if($scbbank[0]){
							$bankno =  str_replace(" x","_",implode($scbbank[0]));
						} else {
							$bankno = str_replace("(","",str_replace(") ","_",str_replace("/X","",implode($otherbank[0]))));
						}
						$Date = date("d/m/Y", strtotime($v['txnDateTime']));
						$Time = date("H:i", strtotime($v['txnDateTime']));
						$data['withdraw'][] = ["date" => $Date, "time" => $Time, "withdraws" => $v['txnAmount'], "description" => trim($bankno), "description_full" => trim($description_full) ];
					}
				}
				//print_r($data);
				return $data;
			}
		}else{
			echo 'โปรดตรวจสอบ !!';

		}

	}
	public function getTransactionWithdraw(){
		date_default_timezone_set("Asia/Bangkok");
		$startDate = date('Y-m-d', strtotime("-1 day"));
		$endDate = date('Y-m-d', strtotime("+1 day"));
		$url = "https://fasteasy.scbeasy.com:8443/v2/deposits/casa/transactions";
		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data_scb = '{ "accountNo": "'.$this->accnum.'", "endDate": "'.$endDate.'", "pageNumber": "1", "pageSize": 35, "productType": "2", "startDate": "'.$startDate.'" }';
		$res = $this->Curl("POST",$url,$headers,$data_scb,false);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){
			$this->Login();
			return $this->cnt_re_login > 1 ? [] : $this->getTransactionWithdraw();
		}

		$json = json_decode($res, true);
		if (isset($json['status'])) {
			if ($json['status']['description'] === 'สำเร็จ') {
				//return $json['data']['txnList'];
				foreach($json['data']['txnList']  as $v ){
					if($v['txnCode']['code'] == 'X2'){
						$description_full = $v['txnRemark'];
						preg_match_all ("/SCB x(.*) /U", $v['txnRemark'], $scbbank);
						preg_match_all ("/ ((.*)) \/X([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)/U", $v['txnRemark'], $otherbank);
						$bankno = "";
						if($scbbank[0]){
							$bankno =  str_replace(" x","_",implode($scbbank[0]));
						} else {
							$bankno = str_replace("(","",str_replace(") ","_",str_replace("/X","",implode($otherbank[0]))));
						}
						$Date = date("d/m/Y", strtotime($v['txnDateTime']));
						$Time = date("H:i", strtotime($v['txnDateTime']));
						$data[] = ["date" => $Date, "time" => $Time, "withdraws" => $v['txnAmount'], "description" => trim($bankno), "description_full" => trim($description_full) ];
					}
				}
				//print_r($data);
				return $data;
			}
		}else{
			echo 'โปรดตรวจสอบ !!';
		}
	}
	public function Verify($accountTo,$accountToBankCode,$amount){
		if($accountToBankCode == "014"){
			$transferType = "3RD";
		}else{
			$transferType = "ORFT";
		}
		$url = "https://fasteasy.scbeasy.com:8443/v2/transfer/verification";
		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data = '{
			"accountFrom": "'.$this->accnum.'",
			"accountFromType": "2",
			"accountTo": "'.$accountTo.'",
			"accountToBankCode": "'.$accountToBankCode.'",
			"amount": "'.$amount.'",
			"annotation": null,
			"transferType":  "'.$transferType.'"
			}';
		$res = $this->Curl("POST",$url,$headers,$data,false);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){
			$this->Login();
			return $this->cnt_re_login > 1 ? '{"status":{"code":"4000","description":"Verify failed Please check token..."}}' : $this->Verify($accountTo,$accountToBankCode,$amount);
		}
		return  $res;


	}
	public function Transfer($accountTo,$accountToBankCode,$amount){
		$Verify = $this->Verify($accountTo,$accountToBankCode,$amount);
		$Verifys = json_decode($Verify,true);
		if(!isset($Verifys['data'])){

			return $Verify;
		}
		$Verify = $Verifys['data'];
		$url = "https://fasteasy.scbeasy.com:8443/v3/transfer/confirmation";
		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data = '{
			"accountFrom": "'.$this->accnum.'",
			"accountFromName": "'.$Verify['accountFromName'].'",
			"accountFromType": "2",
			"accountTo": "'.$Verify['accountTo'].'",
			"accountToBankCode": "'.$Verify['accountToBankCode'].'",
			"accountToName": "'.$Verify['accountToName'].'",
			"amount": "'.$amount.'",
			"botFee": 0.0,
			"channelFee": 0.0,
			"fee": 0.0,
			"feeType": "",
			"pccTraceNo": "'.$Verify['pccTraceNo'].'",
			"scbFee": 0.0,
			"sequence": "'.$Verify['sequence'].'",
			"terminalNo": "'.$Verify['terminalNo'].'",
			"transactionToken": "'.$Verify['transactionToken'].'",
			"transferType": "'.$Verify['transferType'].'"
			}';
		$res = $this->Curl("POST",$url,$headers,$data,false);
		$d = json_decode($res,true);

		if($d['status']['code'] === "1002"){
			$this->Login();
			return $this->cnt_re_login > 1 ? '{"status":{"code":"4002","description":"Transfer failed Please check token..."}}' : $this->Transfer($accountTo,$accountToBankCode,$amount);
		}
		return $res;


	}

	//ตรวจสอบสลิปด้วย QRcode (ต้องหาทาง Decypt Qr ให้ได้ก่อน)
	public function qr_scan($barcode){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://fasteasy.scbeasy.com:8443/v7/payments/bill/scan");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array("barcode" => $barcode, "tilesVersion" => "41")));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate');
		$headers = array();
		$headers[] = 'Api-Auth: ' . $this->Access_token();
		$headers[] = 'Accept-Language: th';
		$headers[] = 'Content-Type: application/json; charset=UTF-8';
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($curl);
		if (curl_errno($curl)) {
			return ['status' => false, 'msg' => 'ผิดพลาด curl'];
		}
		return json_decode($result, true);
	}


}
?>
