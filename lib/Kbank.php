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

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_COOKIEJAR => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
			CURLOPT_COOKIEFILE => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
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

		// print_r($form_field);exit;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/login.do',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_POSTFIELDS => $post_string,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER=> 1,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_COOKIEJAR => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
			CURLOPT_COOKIEFILE => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
		));

		$response = curl_exec($curl);

		// print_r($response);exit;

		$output_array_1 = [];
		if(strpos($response,"/kbiz/ib/redirectToIB.jsp") !== FALSE){
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://online.kasikornbankgroup.com/kbiz/ib/redirectToIB.jsp',
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_COOKIEJAR => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
				CURLOPT_COOKIEFILE => './lib/'.base64_encode("process_kbiz_".$this->acctNo),
			));
			$response = curl_exec($curl);
			preg_match_all('/(?<=dataRsso\=).(.*?)(?=\")/', $response, $output_array_1);
		}

		// print_r($output_array_1);exit;

		curl_close($curl);
		$balance = null;
		$results = [];
		if(!empty($output_array_1[0]) && !empty($output_array_1[0][0])){
			$curl = curl_init();
			curl_setopt_array($curl, array(
				// CURLOPT_URL => 'https://ib.gateway.kasikornbank.com/api/authentication/validateSession',
				CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/authentication/validateSession',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HEADER=> 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{"dataRsso":"'.$output_array_1[0][0].'"}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);

			// print_r($response);exit;

			$ownerId = substr($response,strpos($response,'"userProfiles":[{"ibId":"')+25);
			$ownerId = !empty($ownerId) ? explode('","roleList":',$ownerId) : null;
			$ownerId2 = !is_null($ownerId) && count($ownerId) >= 2 ? trim($ownerId[0]) : null;
			$checkCompany	= substr($response,strpos($response,'"companyId":'));
			$checkCompany1	= explode(',',$checkCompany);
			$checkCompany2	= explode('"',$checkCompany1[0]);
			if($checkCompany2[3])
			{
				$ownerId = $checkCompany2[3];
			} else {
				$ownerId = !is_null($ownerId) && count($ownerId) >= 2 ? trim($ownerId[0]) : null;
			}
			// print_r($ownerId);exit;
			if(is_null($ownerId) || empty($ownerId)){
				echo "Can't find ownerID";
				exit();
			}
			$custType = substr($response,strpos($response,'"custType":"')+12);
			$custType = !empty($custType) ? explode('","img":',$custType) : null;
			$custType = !is_null($custType) && count($custType) >= 2 ? trim($custType[0]) : "I";
			$ownerType = $custType == "IX" ? "Company" : "Retail";
			curl_close($curl);

			preg_match_all('/(?<=x\-session\-token\:).+/', $response, $output_array_2);

			// print_r($output_array_2);exit;

			$curl = curl_init();

			curl_setopt_array($curl, array(
				// CURLOPT_URL => 'https://ib.gateway.kasikornbank.com/gateway/refreshSession',
				CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/refreshSession',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HEADER=> 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{}',
				CURLOPT_HTTPHEADER => array(
					'X-IB-ID: '.$ownerId2,
					'Authorization: '.trim($output_array_2[0][0]),
					'Content-Type:  application/json',
					'X-RE-FRESH:  N',
					'X-REQUEST-ID:  '.date('Ymd').'125809248859',
				),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			preg_match_all('/(?<=x\-session\-token\:).+/', $response, $output_array_3);

			// print_r($output_array_3);exit;

			if(!empty($output_array_3[0]) && !empty($output_array_3[0][0])){
				$curl = curl_init();

				curl_setopt_array($curl, array(
					// CURLOPT_URL => 'https://ib.gateway.kasikornbank.com/api/accountsummary/getAccountSummaryList',
					CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getAccountSummaryList',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_CONNECTTIMEOUT => 15,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{"custType":"'.$custType.'","ownerId":"'.$ownerId.'","ownerType":"'.$ownerType.'","nicknameType":"OWNAC","pageAmount":6,"lang":"th","isReload":"N"}',
					CURLOPT_HTTPHEADER => array(
						'X-IB-ID: '.$ownerId2,
						'Authorization: '.trim($output_array_3[0][0]),
						'Content-Type:  application/json',
						'X-RE-FRESH:  N',
						'X-REQUEST-ID:  '.date('Ymd').'125809248859',
					),
				));

				$response = curl_exec($curl);
				curl_close($curl);

				// print_r($response);exit;

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
				// print_r($ownerId2);exit;
				curl_setopt_array($curl, array(
					// CURLOPT_URL => 'https://ib.gateway.kasikornbank.com/api/accountsummary/getRecentTransactionList',
					CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getRecentTransactionList',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_CONNECTTIMEOUT => 15,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{"acctNo":"'.$this->acctNo.'","acctType":"SA","custType":"'.$custType.'","ownerType":"'.$ownerType.'","ownerId":"'.$ownerId.'","pageNo":"1","rowPerPage":"35","refKey":"","startDate":"'.$start_date.'","endDate":"'.$end_date.'"}',
					CURLOPT_HTTPHEADER => array(
						'Accept:  application/json, text/plain, */*',
						'X-IB-ID: '.$ownerId2,
						'X-SESSION-IBID: '.$ownerId2,
						'Authorization: '.trim($output_array_3[0][0]),
						'Content-Type:  application/json',
						'X-RE-FRESH:  N',
						'X-URL: https://kbiz.kasikornbankgroup.com/menu/account/account/recent-transaction',
						'X-REQUEST-ID:  '.date('Ymd').'125809248859',
					),
				));

				$response = curl_exec($curl);

				curl_close($curl);

				// print_r($response);exit;

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

						// print_r('{"transDate":"'.$transDate.'","acctNo":"'.$this->acctNo.'","origRqUid":"'.$origRqUid.'","custType":"'.$custType.'","originalSourceId":"'.$originalSourceId.'","transCode":"'.$transCode.'","debitCreditIndicator":"'.$debitCreditIndicator.'","transType":"FTOB","ownerType":"'.$ownerType.'","ownerId":"'.$ownerId.'"}');exit;
						$curl = curl_init();
						curl_setopt_array($curl, array(
							// CURLOPT_URL => 'https://ib.gateway.kasikornbank.com/api/accountsummary/getRecentTransactionDetail',
							CURLOPT_URL => 'https://kbiz.kasikornbankgroup.com/services/api/accountsummary/getRecentTransactionDetail',
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => '',
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_CONNECTTIMEOUT => 15,
							CURLOPT_SSL_VERIFYHOST => 0,
							CURLOPT_SSL_VERIFYPEER => 0,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => 'POST',
							CURLOPT_POSTFIELDS =>'{"transDate":"'.$transDate.'","acctNo":"'.$this->acctNo.'","origRqUid":"'.$origRqUid.'","custType":"'.$custType.'","originalSourceId":"'.$originalSourceId.'","transCode":"'.$transCode.'","debitCreditIndicator":"'.$debitCreditIndicator.'","transType":"FTOB","ownerType":"'.$ownerType.'","ownerId":"'.$ownerId.'"}',
							CURLOPT_HTTPHEADER => array(
								'Accept:  application/json, text/plain, */*',
								'X-IB-ID: '.$ownerId2,
								'X-SESSION-IBID: '.$ownerId2,
								'Authorization: '.trim($output_array_3[0][0]),
								'Content-Type:  application/json',
								'X-RE-FRESH:  N',
								'X-URL: https://kbiz.kasikornbankgroup.com/menu/account/account/recent-transaction',
								'X-REQUEST-ID:  '.date('Ymd').'125809248859',
							),
						));
						$response1 = curl_exec($curl);
						$data1 = json_decode($response1,true);
						$data['data']['recentTransactionList'][$index]['detail'] = $data1;
						$transaction = $data['data']['recentTransactionList'][$index];
						// print_r($transaction);
						$payment_gateway_ext = "";
						if(isset($transaction['detail']['data']) && isset($transaction['detail']['data']['toAccountNo'])){
							$toAccountNo = $transaction['detail']['data']['toAccountNo'];
							$set1 = substr($toAccountNo,0,3);
							$set2 = substr($toAccountNo,3,1);
							$set3 = substr($toAccountNo,4,5);
							$set4 = substr($toAccountNo,9,5);
							$toAccountNo = $set1.'-'.$set2.'-'.$set3.'-'.$set4;
							//$payment_gateway_ext = $toAccountNo;
							$payment_gateway_ext = $toAccountNo." ".$transaction['detail']['data']['toAccountNameTh']." ".$transaction['detail']['data']['bankNameEn'];
						}else{
							//$payment_gateway_ext = $transaction['toAccountNumber'];
							$payment_gateway_ext = $transaction['toAccountNumber']." ".(!empty($transaction['benefitAccountNameTh']) ? $transaction['benefitAccountNameTh'] : $transaction['benefitAccountNameEn']);
						}
						// print_r($transaction['detail']['data']);
						if($transaction['fromAccountNameEn'] == '')
						{
							$transNameTh = $transaction['detail']['data']['toAccountNameTh'];
							$transNameEn = $transaction['detail']['data']['toAccountNameEn'];
						} else {
							$transNameTh = $transaction['fromAccountNameTh'];
							$transNameEn = $transaction['fromAccountNameEn'];
						}
						$results[] = [
							'date' => trim(explode(" ",$transaction['transDate'])[0]),
							'time' => trim(explode(" ",$transaction['transDate'])[1]),
							'type' => trim($transaction['channelTh']),
							'type_deposit_withdraw' => !is_null($transaction['depositAmount']) ? 'D' : 'W',
							'amount' => !is_null($transaction['depositAmount']) ? $transaction['depositAmount'] : $transaction['withdrawAmount'],
							'payment_gateway' => trim($transaction['channelTh'].' | '.trim($payment_gateway_ext)),
							'toAccountNameTh' => trim($transNameTh),
							'toAccountNameEn' => trim($transNameEn),
							'bankNameTh' => trim($transaction['detail']['data']['bankNameTh']),
							'bankNameEn' => trim($transaction['detail']['data']['bankNameEn']),
							'toAccountNo' => trim($transaction['detail']['data']['toAccountNo']),
							'toAccountNoMarking' => trim($transaction['detail']['data']['toAccountNoMarking']),
							'transNameTh' => trim($transaction['transNameTh']),
							'transNameEn' => trim($transaction['transNameEn']),
							'memo' => trim($transaction['detail']['data']['memo']),
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


		return ['balance'=> $balance,'account'=> $this->acctNo,'transactions'=>$results];

	}
}
?>
