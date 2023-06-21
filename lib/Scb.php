<?php
header('Content-Type: application/json; charset=utf-8');
class Scb{
	private $tilesVersion='68';
	private $useragent = 'Android/10;FastEasy/3.66.2/6960';
	private $deviceId = '';
	private $api_refresh = '';
	private $accnum = '';
	private $cnt_re_login = 0;
	private $api_auth = "";

	private $encrypt =  array(
		'https://scbencrypt-o6kgfv7ymq-et.a.run.app');
	private $ip_encrypt = '';
	private $count_login = 0;
	private $proxy_ip = 'http://brd.superproxy.io:22225';
	private $proxy_username = 'brd-customer-hl_ebdb3c0e-zone-data_center';
	private $proxy_password = '0pi1xakwwrg5';


	public function Curl($method, $url, $header, $data, $cookie)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		//curl_setopt($ch, CURLOPT_USERAGENT, 'okhttp/3.8.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if(!empty($this->proxy_ip))
		{

			curl_setopt($ch, CURLOPT_PROXY, $this->proxy_ip);
			if(!empty($this->proxy_username))
			{
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_username . ':' . $this->proxy_password);
			}
		}

		if($url != "https://fasteasy.scbeasy.com:8443/v3/transfer/confirmation"){
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		}else{
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
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
		//var_dump($response);
		return $response;
	}
	public function __construct($deviceId,$api_refresh,$accnum) {
		$this->deviceId = $deviceId;
		$this->api_refresh = $api_refresh;
		$this->ip_encrypt = "";

		//Check response encrypt
		$ip_encrypt_list = $this->encrypt;
		$ip_encrypt_failed_timeout = [];
		for($i=0;$i<15;$i++){
			if(empty($this->ip_encrypt)){
				shuffle($ip_encrypt_list);
				$index = rand(0,count($ip_encrypt_list)-1);
				$ch = curl_init();
				try{
					curl_setopt_array($ch, array(
						CURLOPT_URL => $ip_encrypt_list[$index],
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 5,
						CURLOPT_CONNECTTIMEOUT => 3,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => 'GET',
					));
					$http_code  = 200;
					$response_info = curl_getinfo($ch);
					if(curl_errno($ch))
					{
						$data_text = curl_error($ch);
						$curl_errno= curl_errno($ch);
						if(isset($response_info['http_code'])){
							$http_code = $response_info['http_code'];
						}
						$ip_encrypt_failed_timeout[] = [
							'ip' => $ip_encrypt_list[$index],
							'msg' => "Curl Error call [code:".$http_code.",curlno:".$curl_errno."] ".$data_text,
						];
					}
					$response= curl_exec($ch);

					//if(strpos($response,"Cannot GET /") !== FALSE && isset($response_info['http_code']) && isset($response_info['http_code']) == 404){
					if(strpos($response,"ok") !== FALSE && isset($response_info['http_code']) && isset($response_info['http_code']) == 200){
						$this->ip_encrypt = $ip_encrypt_list[$index];
					}else{
						$ip_encrypt_failed_timeout[] = [
							'ip' => $ip_encrypt_list[$index],
							'msg' => "Response not match : ".$response,
						];
					}
				}catch (Exception $ex){
					$ip_encrypt_failed_timeout[] = [
						'ip' => $ip_encrypt_list[$index],
						'msg' => $ex->getMessage(),
					];
				}
				if(!is_null($ch)){
					curl_close($ch);
				}
				array_splice($ip_encrypt_list, $index, 1);
			}else{
				break;
			}
		}

		if(strlen($accnum) != 10){
			echo '10 digital !!';

		}else if(strlen($this->api_refresh) > 6 || strlen($this->api_refresh) < 6){
			echo 'pin should have 6 digits!! : '.$this->accnum;
		}else if(empty($this->ip_encrypt)){
			echo 'Ip encrypt failed or timeout : '.json_encode($ip_encrypt_failed_timeout);
		}else{
			$this->accnum = $accnum;
			$this->new_Login();
		}
	}
	public function Login(){
		$this->cnt_re_login += 1;
		if($this->cnt_re_login > 1){
			$json = array();
			$json['status'] = '0';
			echo $json['msg'] = 'Error re Login Acc : '.$this->accnum.' > 1 round please check token.';
			return $json;
			//echo 'Error re Login Acc : '.$this->accnum.' > 1 round please check token.';
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
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_POSTFIELDS =>'{"deviceId":"'.$this->deviceId.'","jailbreak":"0","tilesVersion":"'.$this->tilesVersion.'","userMode":"INDIVIDUAL"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'user-agent:  '.$this->useragent,
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0',
				'Content-Type:  application/json; charset=UTF-8',
			),
		));

		$response = curl_exec($curl);
		$this->save_log('SCB : preloadandresumecheck',$response);

		curl_close($curl);

		preg_match_all('/(?<=Api-Auth: ).+/', $response, $Auth);
		$Auth=$Auth[0][0];

		if ($Auth=="") {
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
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
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_POSTFIELDS =>'{"loginModuleId":"PseudoFE"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'Api-Auth: '.$Auth,
				'user-agent: '.$this->useragent,
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0 ',
				'Content-Type:  application/json; charset=UTF-8'
			),
		));

		$response1 = curl_exec($curl1);
		$this->save_log('SCB : preAuth',$response1);
		curl_close($curl1);


		$data = json_decode($response1,true);

		$hashType=$data['e2ee']['pseudoOaepHashAlgo'];
		$Sid=$data['e2ee']['pseudoSid'];
		$ServerRandom=$data['e2ee']['pseudoRandom'];
		$pubKey=$data['e2ee']['pseudoPubKey'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "{$this->ip_encrypt}/pin/encrypt",
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
		$this->save_log('SCB : PIN encrypt',$response);
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
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"deviceId":"'.$this->deviceId.'","pseudoPin":"'.$response.'","pseudoSid":"'.$Sid.'"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language:  th ',
				'scb-channel:  APP ',
				'Api-Auth: '.$Auth,
				'user-agent: '.$this->useragent,
				'latitude:  16.5178002 ',
				'longitude:  104.1169243 ',
				'accuracy:  20.0 ',
				'Content-Type:  application/json; charset=UTF-8'
			),
		));

		$response_auth = curl_exec($curl);
		$this->save_log('SCB : fasteasy-login',$response_auth);
		curl_close($curl);

		preg_match_all('/(?<=Api-Auth:).+/', $response_auth, $Auth_result);
		$Auth1=$Auth_result[0][0];
		if ($Auth1=="") {
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
		}
		$access_token = trim($Auth1);
		if(empty($access_token)){
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
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

		if($this->Access_token()==''){
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
		}

		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		$data = '{
			"accountNo": "'.$this->accnum.'"
			}';
		$res = $this->Curl("POST",$url,$headers,$data,false);
		$this->save_log('SCB : GetBalance',$res);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){

			if($this->count_login <=2){
				//	echo $this->count_login;
				$this->new_Login();
				return $this->cnt_re_login > 1 ? [] : $this->GetBalance();
			}else{
				$json = array();
				$json['status'] = '0';
				$json['msg'] = 'SCB error Login please get DeviceID';
				return $json;
			}
			//$this->count_login;

			$this->count_login ++;
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
		$this->save_log('SCB : getTransaction',$res);
		$d = json_decode($res,true);
		//print_r($res);
		if($d['status']['code'] === "1002"){
			if($this->count_login <=2){
				$this->new_Login();
				return $this->cnt_re_login > 1 ? [] : $this->getTransaction();
			}else{
				$json = array();
				$json['status'] = '0';
				$json['msg'] = 'SCB error Login please get DeviceID';
				return $json;

			}

			$this->count_login ++;
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
		$this->save_log('SCB : getTransactionWithdraw',$res);
		$d = json_decode($res,true);
		if($d['status']['code'] === "1002"){
			$this->new_Login();
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
	public function Verify($accountTo,$accountToBankCode,$amount,$annotation=''){

		if($accountToBankCode == "014"){
			$transferType = "3RD";
		}else{
			$transferType = "ORFT";
		}

		$url = "https://fasteasy.scbeasy.com/v2/transfer/verification";
		$headers =  array(
			"Api-Auth: ".$this->Access_token(),
			"content-type: application/json",
			"Accept-Language: th"
		);
		//$annotation = utf8_decode($annotation);
		$data = '{
			"accountFrom": "'.$this->accnum.'",
			"accountFromType": "2",
			"accountTo": "'.$accountTo.'",
			"accountToBankCode": "'.$accountToBankCode.'",
			"amount": "'.$amount.'",
			"annotation": "'.$annotation.'",
			"transferType":  "'.$transferType.'"
			}';
		$res = $this->Curl("POST",$url,$headers,$data,false);

		$this->save_log('SCB : Verify transfer',$res);

		$d = json_decode($res,true);
		//print_r($d);
		//die();
		if($d['status']['code'] === "1002"){
			$this->new_Login();
			return $this->cnt_re_login > 1 ? '{"status":{"code":"4000","description":"Verify failed Please check token..."}}' : $this->Verify($accountTo,$accountToBankCode,$amount);
		}

		$this->cnt_re_login += 1;
		return  $res;

	}
	public function Transfer($accountTo,$accountToBankCode,$amount,$annotation){
		$Verify = $this->Verify($accountTo,$accountToBankCode,$amount,$annotation);
		$Verifys = json_decode($Verify,true);
		//print_r($Verifys);
		//die();
		if(!isset($Verifys['data'])){

			return $Verify;
		}
		$Verify = $Verifys['data'];
		$url = "https://fasteasy.scbeasy.com/v3/transfer/confirmation";
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
		$this->save_log('SCB : transfer',$res);
		$d = json_decode($res,true);

		if($d['status']['code'] === "1002"){
			$this->new_Login();
			return $this->cnt_re_login > 1 ? '{"status":{"code":"4002","description":"Transfer failed Please check token..."}}' : $this->Transfer($accountTo,$accountToBankCode,$amount);
		}
		$this->cnt_re_login += 1;
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
		$this->save_log('SCB : qr_scan',$result);
		if (curl_errno($curl)) {
			return ['status' => 0, 'msg' => 'ผิดพลาด curl'];
		}
		return json_decode($result, true);
	}

	//======================================================
	# EDIT ARM 10/12/2022
	//======================================================
	public function preloadandresumecheck() {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com:8443/v3/login/preloadandresumecheck',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER => 1,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_POSTFIELDS =>'{"tilesVersion":'.$this->tilesVersion.',"userMode":"INDIVIDUAL","isLoadGeneralConsent":0,"deviceId":"'.$this->deviceId.'","jailbreak":0}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language: th',
				'scb-channel: APP',
				'user-agent: '.$this->useragent,
				'Content-Type: application/json; charset=UTF-8',
				'Content-Length: 132',
				'Host: fasteasy.scbeasy.com:8443',
				'Connection: Keep-Alive',
				'Accept-Encoding: gzip'
			),
		));
		$response = curl_exec($curl);
		//die();
		$this->save_log('SCB : preloadandresumecheck',$response);
		$headers = array();
		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line){
			if ($i === 0) {
				$headers['http_code'] = $line;
			} else {
				list ($key, $value) = explode(': ', $line);
				$headers[$key] = $value;
			}
		}
		return $headers;
	}
	public function PseudoFE($apiauth)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com:8443/isprint/soap/preAuth',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"loginModuleId":"PseudoFE"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language: th',
				'scb-channel: APP',
				'Api-Auth: '.$apiauth,
				'user-agent: '.$this->useragent,
				'Content-Type: application/json;charset=UTF-8',
				'Content-Length: 28',
				'Host: fasteasy.scbeasy.com:8443',
				'Connection: Keep-Alive',
			),
		));
		$response = curl_exec($curl);
		$this->save_log('SCB : PseudoFE',$response);
		curl_close($curl);
		return $response;

	}
	public function encryptscb($Sid,$ServerRandom,$pubKey,$hashType)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->ip_encrypt.'/pin/encrypt', // ใส่ url ของคุณ
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'Sid='.$Sid.'&ServerRandom='.$ServerRandom.'&pubKey='.$pubKey.'&pin='.$this->api_refresh.'&hashType='.$hashType,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded'
			),
		));
		$response = curl_exec($curl);

		$this->save_log('SCB : encryptscb',$response);

		curl_close($curl);
		return $response;
	}
	public function fasteasy_login_pin($authid,$pseudoPin,$Sid)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fasteasy.scbeasy.com/v1/fasteasy-login',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_HEADER => 1,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_PROXY => $this->proxy_ip,
			CURLOPT_PROXYUSERPWD => $this->proxy_username . ':' . $this->proxy_password,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{"deviceId":"'.$this->deviceId.'","pseudoPin":"'.$pseudoPin.'","tilesVersion":"'.$this->tilesVersion.'","pseudoSid":"'.$Sid.'"}',
			CURLOPT_HTTPHEADER => array(
				'Accept-Language: th',
				'scb-channel: APP',
				'Api-Auth: '.str_replace("\r\n","",$authid),
				'user-agent: '.$this->useragent,
				'Content-Type: application/json;charset=UTF-8',
				'Content-Length: 837',
				'Host: fasteasy.scbeasy.com:8443',
				'Connection: Keep-Alive',
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		//print_r($response);
		$this->save_log('SCB : fasteasy-login',$response);
		$headers = array();
		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
		//print_r($header_text);
		foreach (explode("\r\n", $header_text) as $i => $line){
			if ($i === 0) {
				$headers['http_code'] = $line;
			} else {
				list ($key, $value) = explode(': ', $line);
				$headers[$key] = $value;
			}
		}


		return $headers;
	}
	public function new_Login()
	{


		$preload = $this->preloadandresumecheck();

		$e2ee = $this->PseudoFE($preload['Api-Auth']);

		$e2eejson = json_decode($e2ee,true);
		$hashType = $e2eejson['e2ee']['pseudoOaepHashAlgo'];
		$Sid = $e2eejson['e2ee']['pseudoSid'];
		$ServerRandom = $e2eejson['e2ee']['pseudoRandom'];
		$pubKey = $e2eejson['e2ee']['pseudoPubKey'];
		$encryptscb = $this->encryptscb($Sid,$ServerRandom,$pubKey,$hashType);

		$Auth1 = $this->fasteasy_login_pin($preload['Api-Auth'],$encryptscb,$Sid);
		//print_r($Auth1);
		if ($Auth1=="") {
			//echo 'error Login 2';
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
		}
		$access_token = trim($Auth1["Api-Auth"]);
		if(empty($access_token)){
			$json = array();
			$json['status'] = '0';
			$json['msg'] = 'SCB error Login please get DeviceID';
			return $json;
		}
		//print_r($access_token);
		/*$strFileName = "token_".$this->accnum.".txt";
		$objFopen = fopen($strFileName, 'w');
		fwrite($objFopen, $access_token);*/
		$this->cnt_re_login += 1;
		$this->api_auth = $access_token;
	}

	function save_log($txtEvent,$res){
		$data = array();
		$data["clientName"] = $_SERVER['SERVER_NAME'];
		$data["logType"] = "{$txtEvent}";
		$data["message"] = json_encode($res);
		$url = "https://status.nextgendev.space/banklog.php";
		$this->Curl("POST",$url,null,$data,false);
	}

}
?>
