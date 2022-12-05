<?php
error_reporting(0);
require_once 'simple_html_dom.php';
header('Content-Type: application/json');
class Kbank{
	private $acctNo = '';
	private $userName = "";
	private $password = "";

	public function __construct($userName,$password,$acctNo) {
		$this->userName = trim($userName);
		$this->password = trim($password);
		$this->acctNo = trim($acctNo);
		if(empty($this->userName) || empty($this->password) || empty($this->acctNo)){
			echo 'Invalid params';
			exit();
		}
	}

	public function getBalanceAndTransactions(){
		$curl = curl_init();
		$fp = fopen('./application/third_party/'.base64_encode("process_kbiz_".$this->acctNo), "w");
		fclose($fp);
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_COOKIEJAR => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
			CURLOPT_COOKIEFILE => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
		));

		$response = curl_exec($curl);

		curl_close($curl);



		$html = str_get_html($response);

		$form_field = array();

		foreach($html->find('form input') as $element) {
			$form_field[$element->name] = $element->value;
		}

		$form_field['userName']  = $this->userName;
		$form_field['password'] = $this->password;
		$post_string = '';
		foreach($form_field as $key => $value) {
			$post_string .= $key . '=' . urlencode($value) . '&';
		}
		$post_string = substr($post_string, 0, -1);


		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/login.do',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_POSTFIELDS => $post_string,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HEADER=> 1,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_COOKIEJAR => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
			CURLOPT_COOKIEFILE => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
		));

		$response = curl_exec($curl);
		$output_array = [];
		if(strpos($response,"/kbiz/ib/redirectToIB.jsp") !== FALSE){
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/ib/redirectToIB.jsp',
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_COOKIEJAR => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
				CURLOPT_COOKIEFILE => './application/third_party/'.base64_encode("process_kbiz_".$this->acctNo),
			));
			$response = curl_exec($curl);
			preg_match_all('/(?<=dataRsso\=).(.*?)(?=\")/', $response, $output_array);
		}
		curl_close($curl);
		$balance = null;
		$results = [];
		if(!empty($output_array[0]) && !empty($output_array[0][0])){
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/authentication/validateSession',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HEADER=> 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{"dataRsso":"'.$output_array[0][0].'"}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);
			$ownerId = substr($response,strpos($response,'"userProfiles":[{"ibId":"')+25);
			$ownerId = !empty($ownerId) ? explode('","roleList":',$ownerId) : null;
			$ownerId = !is_null($ownerId) && count($ownerId) >= 2 ? trim($ownerId[0]) : null;
			if(is_null($ownerId) || empty($ownerId)){
				echo "Can't find ownerID";
				exit();
			}
			$custType = substr($response,strpos($response,'"custType":"')+12);
			$custType = !empty($custType) ? explode('","img":',$custType) : null;
			$custType = !is_null($custType) && count($custType) >= 2 ? trim($custType[0]) : "I";
			$ownerType = $custType == "IX" ? "Company" : "Retail";
			curl_close($curl);

			preg_match_all('/(?<=X\-SESSION\-TOKEN\:).+/', $response, $output_array);





			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/refreshSession',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HEADER=> 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{}',
				CURLOPT_HTTPHEADER => array(
					'Connection:  keep-alive',
					'X-IB-ID: '.$ownerId,
					'sec-ch-ua-mobile:  ?0',
					'Authorization: '.trim($output_array[0][0]),
					'Content-Type:  application/json',
					'X-URL:  https://kbiz.kasikornbankgroup.com/menu/account/account-summary',
					'Accept:  application/json, text/plain, */*',
					'User-Agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36 Edg/96.0.1054.43',
					'X-RE-FRESH:  N',
					'X-REQUEST-ID:  '.date('Ymd').'125809248859',
					'Origin:  https://kbiz.kasikornbankgroup.com',
					'Sec-Fetch-Site:  cross-site',
					'Sec-Fetch-Mode:  cors',
					'Sec-Fetch-Dest:  empty',
					'Referer:  https://kbiz.kasikornbankgroup.com/',
					'Accept-Language:  en-US,en;q=0.9,th;q=0.8',
					'Cookie: 76aa0fb54084497165ebe81fd14b509b=4e17b3927e6157f7d6c7dff277645d12; TS0133a991=014654215437036df3ae0001a00c2db14dda56bdc5c0abc86e06c5e2e15ecf14ded9592d3e851a48e03d3f4eb1280103b389ec0932'
				),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			preg_match_all('/(?<=X\-SESSION\-TOKEN\:).+/', $response, $output_array1);


			if(!empty($output_array1[0]) && !empty($output_array1[0][0])){
				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getAccountSummaryList',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_CONNECTTIMEOUT => 15,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{"custType":"'.$custType.'","ownerId":"'.$ownerId.'","ownerType":"'.$ownerType.'","nicknameType":"OWNAC","pageAmount":6,"lang":"th","isReload":"N"}',
					CURLOPT_HTTPHEADER => array(
						'Connection:  keep-alive',
						'X-IB-ID: '.$ownerId,
						'sec-ch-ua-mobile:  ?0',
						'Authorization: '.trim($output_array1[0][0]),
						'Content-Type:  application/json',
						'X-URL:  https://kbiz.kasikornbankgroup.com/menu/account/account-summary',
						'Accept:  application/json, text/plain, */*',
						'User-Agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36 Edg/96.0.1054.43',
						'X-RE-FRESH:  N',
						'X-REQUEST-ID:  '.date('Ymd').'125809248859',
						'Origin:  https://kbiz.kasikornbankgroup.com',
						'Sec-Fetch-Site:  cross-site',
						'Sec-Fetch-Mode:  cors',
						'Sec-Fetch-Dest:  empty',
						'Referer:  https://kbiz.kasikornbankgroup.com/',
						'Accept-Language:  en-US,en;q=0.9,th;q=0.8',
						'Cookie: 76aa0fb54084497165ebe81fd14b509b=4e17b3927e6157f7d6c7dff277645d12; TS0133a991=014654215437036df3ae0001a00c2db14dda56bdc5c0abc86e06c5e2e15ecf14ded9592d3e851a48e03d3f4eb1280103b389ec0932'
					),
				));

				$response = curl_exec($curl);
				curl_close($curl);
				$data_bal = json_decode($response,true);
				$balance = null;
				if(isset($data_bal['data']) && isset($data_bal['data']['accountSummaryList']) && count($data_bal['data']['accountSummaryList']) >= 1){
					foreach ($data_bal['data']['accountSummaryList'] as $acc_detail){
						if($acc_detail['accountNo'] == $this->acctNo){
							$balance = $acc_detail['availableBalance'];
						}
					}
				}

				$curl = curl_init();
				date_default_timezone_set("Asia/Bangkok");
				if(explode("/",date('d/m/Y', strtotime("-1 day")))[2] != explode("/",date('d/m/Y'))[2]){
					$start_date = date('d/m/Y');
					$end_date = date('d/m/Y');
				}else{
					if((int)explode("/",date('d/m/Y', strtotime("-1 day")))[1] < explode("/",date('d/m/Y'))[2]){
						$start_date = date('d/m/Y');
					}else{
						$start_date = date('d/m/Y', strtotime("-1 day"));
					}
					$end_date = date('d/m/Y');
				}

				curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getRecentTransactionList',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_CONNECTTIMEOUT => 15,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{"acctNo":"'.$this->acctNo.'","acctType":"SA","custType":"'.$custType.'","ownerType":"'.$ownerType.'","ownerId":"'.$ownerId.'","pageNo":"1","rowPerPage":"35","refKey":"","startDate":"'.$start_date.'","endDate":"'.$end_date.'"}',
					CURLOPT_HTTPHEADER => array(
						'Connection:  keep-alive',
						'X-IB-ID: '.$ownerId,
						'sec-ch-ua-mobile:  ?0',
						'Authorization: '.trim($output_array1[0][0]),
						'Content-Type:  application/json',
						'X-URL:  https://kbiz.kasikornbankgroup.com/menu/account/account-summary',
						'Accept:  application/json, text/plain, */*',
						'User-Agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36 Edg/96.0.1054.43',
						'X-RE-FRESH:  N',
						'X-REQUEST-ID:  '.date('Ymd').'125809248859',
						'Origin:  https://kbiz.kasikornbankgroup.com',
						'Sec-Fetch-Site:  cross-site',
						'Sec-Fetch-Mode:  cors',
						'Sec-Fetch-Dest:  empty',
						'Referer:  https://kbiz.kasikornbankgroup.com/',
						'Accept-Language:  en-US,en;q=0.9,th;q=0.8',
						'Cookie: 76aa0fb54084497165ebe81fd14b509b=4e17b3927e6157f7d6c7dff277645d12; TS0133a991=014654215437036df3ae0001a00c2db14dda56bdc5c0abc86e06c5e2e15ecf14ded9592d3e851a48e03d3f4eb1280103b389ec0932'
					),
				));

				$response = curl_exec($curl);

				curl_close($curl);

				$data = json_decode($response,true);
				$curl = null;
				if(!is_null($data) && isset($data['data'])){
					foreach ($data['data']['recentTransactionList'] as $index =>  $value) {

						$transDate=$value['transDate'];
						preg_match('/.+(?= )/', $transDate, $output_array);
						$transDate=$output_array[0];
						$effectiveDate=$value['effectiveDate'];
						$transNameTh=$value['transNameTh'];
						$transNameEn=$value['transNameEn'];
						$depositAmount=$value['depositAmount'];
						$withdrawAmount=$value['withdrawAmount'];
						$accountPartner=$value['accountPartner'];
						$channelTh=$value['channelTh'];
						$channelEn=$value['channelEn'];
						$origRqUid=$value['origRqUid'];
						$toAccountNumber=$value['toAccountNumber'];
						$benefitAccountNameTh=$value['benefitAccountNameTh'];
						$benefitAccountNameEn=$value['benefitAccountNameEn'];
						$transType=$value['transType'];
						$originalSourceId=$value['originalSourceId'];
						$transCode=$value['transCode'];
						$debitCreditIndicator=$value['debitCreditIndicator'];
						$proxyTypeCode=$value['proxyTypeCode'];
						$proxyId=$value['proxyId'];


						$curl = curl_init();
						curl_setopt_array($curl, array(
							CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getRecentTransactionDetail',
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => '',
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_CONNECTTIMEOUT => 15,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => 'POST',
							CURLOPT_POSTFIELDS =>'{"transDate":"'.$transDate.'","acctNo":"'.$this->acctNo.'","origRqUid":"'.$origRqUid.'","custType":"'.$custType.'","originalSourceId":"'.$originalSourceId.'","transCode":"'.$transCode.'","debitCreditIndicator":"'.$debitCreditIndicator.'","transType":"FTOB","ownerType":"'.$ownerType.'","ownerId":"'.$ownerId.'"}',
							CURLOPT_HTTPHEADER => array(
								'Connection:  keep-alive',
								'X-IB-ID: '.$ownerId,
								'sec-ch-ua-mobile:  ?0',
								'Authorization: '.trim($output_array1[0][0]),
								'Content-Type:  application/json',
								'X-URL:  https://kbiz.kasikornbankgroup.com/menu/account/account-summary',
								'Accept:  application/json, text/plain, */*',
								'User-Agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36 Edg/96.0.1054.43',
								'X-RE-FRESH:  N',
								'X-REQUEST-ID:  '.date('Ymd').'125809248859',
								'Origin:  https://kbiz.kasikornbankgroup.com',
								'Sec-Fetch-Site:  cross-site',
								'Sec-Fetch-Mode:  cors',
								'Sec-Fetch-Dest:  empty',
								'Referer:  https://kbiz.kasikornbankgroup.com/',
								'Accept-Language:  en-US,en;q=0.9,th;q=0.8',
								'Cookie: 76aa0fb54084497165ebe81fd14b509b=4e17b3927e6157f7d6c7dff277645d12; TS0133a991=014654215463e11f513318fc4810039aa2c1b32705e7c48873da499cc22e2ef84f8c6207b723bb28f64aaa9283a3b59671fae1d8ae'
							),
						));
						$response1 = curl_exec($curl);
						$data1 = json_decode($response1,true);
						$data['data']['recentTransactionList'][$index]['detail'] = $data1;

						$transaction = $data['data']['recentTransactionList'][$index];
						$payment_gateway_ext = "";
						if(isset($transaction['detail']['data']) && isset($transaction['detail']['data']['toAccountNo'])){
							$toAccountNo = $transaction['detail']['data']['toAccountNo'];
							$set1 = substr($toAccountNo,0,3);
							$set2 = substr($toAccountNo,3,1);
							$set3 = substr($toAccountNo,4,5);
							$set4 = substr($toAccountNo,9,5);
							$toAccountNo = $set1.'-'.$set2.'-'.$set3.'-'.$set4;
							$payment_gateway_ext = $toAccountNo." ".$transaction['detail']['data']['toAccountNameTh']." ".$transaction['detail']['data']['bankNameEn'];
						}else{
							$payment_gateway_ext = $transaction['toAccountNumber']." ".(!empty($transaction['benefitAccountNameTh']) ? $transaction['benefitAccountNameTh'] : $transaction['benefitAccountNameEn']);
						}
						$results[] = [
							'date' => trim(explode(" ",$transaction['transDate'])[0]),
							'time' => trim(explode(" ",$transaction['transDate'])[1]),
							'type' => trim($transaction['channelTh']),
							'type_deposit_withdraw' => !is_null($transaction['depositAmount']) ? 'D' : 'W',
							'amount' => !is_null($transaction['depositAmount']) ? $transaction['depositAmount'] : $transaction['withdrawAmount'],
							'payment_gateway' => trim($transaction['channelTh'].' | '.trim($payment_gateway_ext))
						];

					}
				}else{
					echo "Can't get data recentTransactionList [".$this->acctNo."] : ".json_encode($response);
				}

			}else{
				echo "Can't get session token [".$this->acctNo."] : ".json_encode($response);
			}
		}else{
			echo "Can't get dataRsso from login page [".$this->acctNo."] : ".json_encode($response);
		}

		if(!is_null($curl)){
			$curl = null;
			curl_close($curl);
		}
		return ['balance'=> $balance,'transactions'=>$results];

	}
}
?>
