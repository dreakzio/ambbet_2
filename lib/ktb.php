<?php
error_reporting(0);
require_once 'simple_html_dom.php';
header('Content-Type: application/json');

class Ktb{
	private $accountTokenNo;
	private $tokenID;
	private $userIdentity;
	private $proxy_array	= array( 'proxyprivates.com' );
//proxy
	private $loginpassw = 'proxydata:f6Hj2DBefuNd7xNs';
	private $proxy_ip;
	private $proxy_port = '3128';
	private $host;
	private $urlGettran;
	private $bank_number;

	public function __construct($accountTokenNo,$tokenID,$userIdentity,$bank_number) {
		$this->accountTokenNo = trim($accountTokenNo);
		$this->tokenID = trim($tokenID);
		$this->userIdentity = trim($userIdentity);
		$this->bank_number = trim($bank_number);
		$this->host = 'proxyprivates.com';

		$this->proxy_array	= array( 'proxyprivates.com' );
		$this->proxy_ip = $this->proxy_array[array_rand($this->proxy_array)];
		$this->urlGettran = 'https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/deposit/statement-content';


		if(empty($this->accountTokenNo) || empty($this->tokenID) || empty($this->userIdentity)){
			echo 'Invalid params';
			exit();
		}
	}
	public function getBalanceAndTransactions(){
		//check proxy
		//if($socket =@fsockopen($this->host, $this->proxy_port, $errno, $errstr, 2)) {fclose($socket);} else {echo 'offline.';exit;}
		$data_entry = '{"action":"UPDATE","accountTokenNumber":"'.$this->accountTokenNo.'","activeIndex":"0","lastSeq":"0","userIdentity":"'.$this->userIdentity.'","hasViewMore":false,"transaction":[]}';
		return $this->CallCurl($data_entry);
	}

	private function CallCurl($data_en){
		$ch            = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->urlGettran);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_en);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			// 'Content-Type: application/x-www-form-urlencoded'
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		/*curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxy_port);
		curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy_ip);
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->loginpassw);*/

		$response = curl_exec($ch);
		curl_close($ch);

		preg_match_all('/{"(.*)}/', $response, $matches);
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$month_th = array(
			"ม.ค." => "01",
			"ก.พ." => "02",
			"มี.ค." => "03",
			"เม.ย." => "04",
			"พ.ค." => "05",
			"มิ.ย." => "06",
			"ก.ค." => "07",
			"ส.ค." => "08",
			"ก.ย." => "09",
			"ต.ค." => "10",
			"พ.ย." => "11",
			"ธ.ค." => "12",
		);
		$json=[];
		$balance = null;
		if(!empty($matches[0]) && !empty($matches[0][0])){
			$reData		= json_decode($matches[0][0], true);
			if(!is_null($reData)  && isset($reData['availableBalance'])){
				$balance = trim(str_replace(",","",$reData['availableBalance']));
			}
			if(!is_null($reData) && isset($reData['transactions'])){
				foreach ($reData['transactions'] as $index => $transaction){
					$date_time = explode(" ",trim($transaction['dateTime']));
					if(date('m') == "01" && $month_th[$date_time[1]] == "12"){
						$reData['transactions'][$index]['transDate'] = date('Y',strtotime ( '-1 year'))."-".$month_th[$date_time[1]]."-".$date_time[0];
					}else{
						$reData['transactions'][$index]['transDate'] = date('Y')."-".$month_th[$date_time[1]]."-".$date_time[0];
					}
					$reData['transactions'][$index]['transTime'] = $date_time[2].":00";
					$reData['transactions'][$index]['transCmt'] = $transaction['cmt'];
					$reData['transactions'][$index]['transAmt'] = $transaction['balance'];
					$reData['transactions'][$index]['transCmt_full'] = $transaction['cmt'];
					if(strpos($transaction['cmt'],"-") !== false){
						$reData['transactions'][$index]['bank_code'] = codeMatchDb(explode("-",trim($transaction['cmt']))[0]);
					}else if(strpos(strtoupper($transaction['cmt']),"TR") !== false){
						$reData['transactions'][$index]['bank_code'] = codeMatchDb("TR");
					}else{
						$reData['transactions'][$index]['bank_code'] = codeMatchDb("");
					}
				}
				$json = $reData['transactions'];
			}
		}else{
			 $this->loginUserIdentify($this->userIdentity,$this->bank_number);
		}
		$master = array(
			"Balance" => str_replace(",","",$balance),
			"Transactions" => $json
		);
		return $master;
	}

	public function loginUserIdentify(){
		$urlLogin		= 'https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/check-access-limit';
		$dataPayload		= '{"userIdentity":"'.$this->userIdentity.'"}';
		$ch            = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlLogin);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataPayload);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		echo json_encode(['status'=>false,"message" => "Bank ".$this->bank_number." update user identify : ".date('Y-m-d H:i:s')]);
	}
}
