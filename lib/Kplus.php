<?php
error_reporting(0);
require_once __DIR__.'/Encryption.php';
Class Kplus{

	private $endpoint = "https://kbankencrypt-o6kgfv7ymq-et.a.run.app"; // กลับมาใช้ตัวเดิมก่อน ตัว google cloud มีปัญหา https://kbankencrypt-o6kgfv7ymq-et.a.run.app
	private $token = null;
	private $token_query = "";
	
	private $agent = '';

	public function __construct($acc_no,$token,$pin){
		$token_data = json_decode($token,true);
		if(strlen($acc_no) < 10 || strlen($pin) < 6){
			echo "Please check params & token#1";
			exit();
		}else if(empty($token_data) || (
				(!isset($token_data['da3']) || empty($token_data['da3'])) &&
				(!isset($token_data['dm1']) || empty($token_data['dm1'])) &&
				(!isset($token_data['dka3']) || empty($token_data['dka3'])) &&
				(!isset($token_data['db1']) || empty($token_data['db1'])) &&
				(!isset($token_data['wifiKey']) || empty($token_data['wifiKey'])) &&
				(!isset($token_data['androidId']) || empty($token_data['androidId'])) &&
				(!isset($token_data['securityToken']) || empty($token_data['securityToken'])) &&
				(!isset($token_data['token']) || empty($token_data['token']))
			)){
			echo "Please check params & token#2";
			exit();
		}
		$encryption = new Encryption();
		$this->token = $encryption->encrypt(json_encode([
			'data_account' => [
				'accountNo' => $acc_no,
				'accountType' => 'SA',
				'pin' => $pin
			],
			'data_token' => $token_data
		]));
		$this->token_query = "?token=".$this->token;
	}

	//ยอดคงเหลือ
	public function getBalance(){
		for($i=0;$i<3;$i++){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpoint.'/balance'.$this->token_query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_REFERER, $this->agent . $_SERVER['SERVER_NAME']);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->agent . $_SERVER['SERVER_NAME']);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				return array();
			}
			curl_close($ch);
			$result = json_decode($result, true);
			if(!empty($result) && isset($result['error']) && $result['error'] == "read ECONNRESET"){
				if($i == 2){
					return $result;
				}
			}else{
				return isset($result['availableBalance']) ? (float) str_replace(',', '', $result['availableBalance']) : null;
			}
		}
	}

	//ดึงรายการ
	public function getTransactions(){
		for($i=0;$i<3;$i++){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpoint.'/activities'.$this->token_query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_REFERER, $this->agent . $_SERVER['SERVER_NAME']);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->agent . $_SERVER['SERVER_NAME']);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				return array();
			}
			curl_close($ch);
			$result = json_decode($result, true);
			if(!empty($result) && isset($result['error']) && $result['error'] == "read ECONNRESET"){
				if($i == 2){
					return $result;
				}
			}else{
				return isset($result['activityList']) ?  $result['activityList'] : null;
			}
		}
	}

	//ดึงรายการ เจาะจง
	public function getTransactionDetail($rqUid){
		for($i=0;$i<3;$i++){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpoint.'/activity-detail/'.$rqUid.$this->token_query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_REFERER, $this->agent . $_SERVER['SERVER_NAME']);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->agent . $_SERVER['SERVER_NAME']);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				return array();
			}
			curl_close($ch);
			$result = json_decode($result, true);
			if(!empty($result) && isset($result['error']) && $result['error'] == "read ECONNRESET"){
				if($i == 2){
					return $result;
				}
			}else{
				return $result;
			}
		}
	}

	//ตรวจสอบผู้รับก่อนโอนเงิน
	public function transferVerify($toBankCode, $toAccount, $amount){
		for($i=0;$i<3;$i++){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpoint.'/inquire-for-transfer-money/'.$this->token_query);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'toBankCode='.$toBankCode.'&toAccount='.$toAccount.'&amount='.$amount.'');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_REFERER, $this->agent . $_SERVER['SERVER_NAME']);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->agent . $_SERVER['SERVER_NAME']);
			$result = curl_exec($ch);
			$data =  json_decode($result, true);
			if (curl_errno($ch)) {
				return ['status' => false, 'msg' => $data];
			}
			curl_close($ch);
			if(!empty($data) && isset($data['error']) && $data['error'] == "read ECONNRESET"){
				if($i == 2){
					return $result;
				}
			}else{
				return $data;
			}
		}
	}

	//ยืนยันโอนเงิน
	public function transferConfrim($kbankInternalSessionId){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint.'/transfer-money/'.$kbankInternalSessionId.$this->token_query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 180);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$headers[] = 'Content-Length: 0';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($ch, CURLOPT_REFERER, $this->agent . $_SERVER['SERVER_NAME']);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->agent . $_SERVER['SERVER_NAME']);
		$result = curl_exec($ch);
		//print_r($result);
		if (curl_errno($ch)) {
			return json_decode($result, true);
		}
		curl_close ($ch);
		return json_decode($result, true);
	}


}

