<?php
/**
 * TMNOoo API Documentation
 */
 date_default_timezone_set("Asia/Bangkok");
class TMNOoo
{
	private $tmnone_endpoint = 'http://ec2-54-179-29-56.ap-southeast-1.compute.amazonaws.com/v2-randomkey.php';
	private $wallet_endpoint = 'https://tmn-mobile-gateway.public-a-cloud1p.ascendmoney.io/tmn-mobile-gateway/';
	private $wallet_user_agent = 'okhttp/4.4.0'; 
	private $tmnone_keyid = 0;
	private $wallet_msisdn, $wallet_login_token, $wallet_tmn_id, $wallet_device_id, $wallet_access_token, $proxy_ip = '', $proxy_username = '', $proxy_password = '';
	public function __construct($mode = false)
	{
		$this->sslmode = $mode;
	}

	public function setData($tmnone_keyid, $wallet_msisdn, $wallet_login_token, $wallet_tmn_id) {
		$this->tmnone_keyid = $tmnone_keyid;
		$this->wallet_msisdn = $wallet_msisdn;
		$this->wallet_login_token = $wallet_login_token;
		$this->wallet_tmn_id = $wallet_tmn_id;
		$this->wallet_device_id = substr(md5($wallet_msisdn . $wallet_tmn_id), 0, 16);
	}

	public function setProxy($proxy_ip, $proxy_username, $proxy_password) {
		$this->proxy_ip = $proxy_ip;
		$this->proxy_username = $proxy_username;
		$this->proxy_password = $proxy_password;
	}

	public function setDataWithAccessToken($tmnone_keyid, $wallet_access_token, $wallet_login_token, $wallet_device_id) {
		$this->tmnone_keyid = $tmnone_keyid;
		$this->wallet_access_token = $wallet_access_token;
		$this->wallet_login_token = $wallet_login_token;
		$this->wallet_device_id = $wallet_device_id;
	}

	public function getCachedAccessToken()
	{
		$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'get'));
		$encrypted_access_token = $this->tmnone_connect($request_body)['data'];
		if(!empty($encrypted_access_token))
		{
			$aes_key = hex2bin(substr(hash('sha512', $this->wallet_tmn_id) ,0 ,64));
			$aes_iv = hex2bin(substr($encrypted_access_token, 0, 32));
			$access_token = openssl_decrypt(base64_decode(substr($encrypted_access_token, 32)), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			if(!empty($access_token))
			{
				 $this->wallet_access_token = $access_token;
			}
		}
	}

	public function loginWithPin6($wallet_pin)
	{
		$this->getCachedAccessToken();
		if(!empty($this->wallet_access_token))
		{
			return $this->wallet_access_token;
		}
		$wallet_pin = hash('sha256', $this->wallet_tmn_id . $wallet_pin);
		$signature = $this->calculate_sign256($this->wallet_login_token . '|' . $wallet_pin);
		$postdata = array();
		$postdata['pin'] = $wallet_pin;
		$postdata['app_version'] = '5.25.1';
		$postdata = json_encode($postdata);
		$wallet_response_body = $this->wallet_connect('mobile-auth-service/v1/pin/login', array('Content-Type: application/json', 'Authorization: ' . $this->wallet_login_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='), $postdata);
		if(!empty($wallet_response_body['data']['access_token']))
		{
			$this->wallet_access_token = $wallet_response_body['data']['access_token'];
			$aes_key = hex2bin(substr(hash('sha512', $this->wallet_tmn_id) ,0 ,64));
			$aes_iv = openssl_random_pseudo_bytes(16);
			$encrypted_access_token = bin2hex($aes_iv) . base64_encode(openssl_encrypt($this->wallet_access_token, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv));
			$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'set', 'data'=>$encrypted_access_token));
			$this->tmnone_connect($request_body);
		}
		return $this->wallet_access_token;
	}

	public function getBalance()
	{
		$uri = 'user-profile-composite/v1/users/';
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
		$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token), '');
		return isset($wallet_response_body['data']['current_balance']) ? $wallet_response_body['data']['current_balance'] : '';
	}

    public function redeemVouchers($code, $number) {
        if (strpos($code, '?v=') !== false) {
            $code = explode('?v=', $code)[1];
        }
		$url = "https://gift.truemoney.com/campaign/vouchers/" . $code . "/redeem";
		$signature = array('url' => $url, 'mobile' => $number, 'voucher_hash' => $code);		
		$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'gift', 'data'=>$signature));
		return $this->tmnone_connect($request_body);
    }

    public function verifyVouchers($code) {
        if (strpos($code, '?v=') !== false) {
            $code = explode('?v=', $code)[1];
        }
		$url = "https://gift.truemoney.com/campaign/vouchers/" . $code . "/verify";
		$signature = array('url' => $url, 'voucher_hash' => $code);		
		$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'gift', 'data'=>$signature));
		return $this->tmnone_connect($request_body);
    }		
	
	public function fetchTransactionHistory($start_date, $end_date, $limit=10, $page=1)
	{
		$uri = 'history-composite/v1/users/transactions/history/?start_date=' . $start_date . '&end_date=' . $end_date . '&limit=' . $limit . '&page=' . $page . '&type=&action=';
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
		$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='), '');

		return isset($wallet_response_body['data']['activities']) ? $wallet_response_body : array();
	}

	public function fetchTransactionInfo($report_id)
	{
		$cache_filename = sys_get_temp_dir() . '/tmn-' . $report_id;
		$aes_key = hex2bin(substr(hash('sha512', $this->wallet_tmn_id) ,0 ,64));
		if(file_exists($cache_filename))
		{
			$wallet_response_body = file_get_contents($cache_filename);
			$aes_iv = hex2bin(substr($wallet_response_body, 0, 32));
			$wallet_response_body = openssl_decrypt(substr($wallet_response_body, 32), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			$wallet_response_body = json_decode($wallet_response_body, true);
			$wallet_response_body['cached'] = true;
			return $wallet_response_body;
		}
		$uri = 'history-composite/v1/users/transactions/history/detail/' . $report_id . '?version=1';
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
		$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='), '');
		if(!empty($wallet_response_body['data']))
		{
			$aes_iv = openssl_random_pseudo_bytes(16);
			$encrypted_wallet_response_body = bin2hex($aes_iv) . openssl_encrypt(json_encode($wallet_response_body['data']), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			file_put_contents($cache_filename, $encrypted_wallet_response_body);
		}
		return isset($wallet_response_body['data']) ? $wallet_response_body['data'] : array();
	}

	public function generateVoucher($amount,$detail='')
	{
		try
		{
			$uri = 'transfer-composite/v1/vouchers/';
			$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  $this->wallet_access_token . '|R|' . $amount . '|1|' . $detail);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"amount":"' . $amount . '","detail":"' . $detail . '","tmn_id":"' . $this->wallet_tmn_id . '","mobile":"' . $this->wallet_msisdn . '","voucher_type":"R","member":"1"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			return $wallet_response_body['data']['link_redeem'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
	}

    public function vouchersTransaction()
    {
        $uri = "transfer-composite/v1/vouchers/?tmnId=".strval($this->config["tmn_id"])."&limit=20&page=0";
        $data = "/tmn-mobile-gateway/".$uri."|".$this->wallet_access_token;
        $signature = $this->calculate_sign256($data);
        $wallet_response_body = $this->wallet_connect(
            $uri,
            [
                "Content-Type: application/json",
                "signature: " . $signature,
                "X-Device: " . $this->wallet_device_id,
                "Authorization: " . $this->wallet_access_token,
                "X-Geo-Location: city=; country=; country_code=",
                "X-Geo-Position: lat=; lng=",
            ],
            ""
        );
        return isset($wallet_response_body['data']) ? $wallet_response_body['data'] : "";
    } 
	
	public function getRecipientName($payee_wallet_id)
	{
		try
		{
			$amount = '1.00';
			$uri = 'transfer-composite/v2/p2p-transfer/draft-transactions';
			$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  $this->wallet_access_token . '|' . $amount . '|' . $payee_wallet_id);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			return $wallet_response_body['data']['recipient_name'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
	}

	public function transferP2P($payee_wallet_id,$amount,$personal_msg='')
	{
		try
		{
			$amount = number_format($amount, 2, '.', '');
			$uri = 'transfer-composite/v2/p2p-transfer/draft-transactions';
			$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  $this->wallet_access_token . '|' . $amount . '|' . $payee_wallet_id);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			$draft_transaction_id = $wallet_response_body['data']['draft_transaction_id'];
			$reference_key = $wallet_response_body['data']['reference_key'];

			$receiver_id = $wallet_response_body['data']['receiver_id'];
			$recipient_name = $wallet_response_body['data']['recipient_name'];
			$recipient_image_url = $wallet_response_body['data']['recipient_image_url'];
			
			$uri = 'transfer-composite/v1/p2p-transfer/draft-transactions/' . $draft_transaction_id;
			$signature = $this->calculate_sign256($reference_key);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"personal_message":"' . $personal_msg . '","signature":"' . $signature . '"}', 'PUT');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}

			$uri = 'transfer-composite/v1/p2p-transfer/transactions/' . $draft_transaction_id . '/';
			$signature = $this->calculate_sign256($reference_key);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"reference_key":"' . $reference_key . '","signature":"' . $signature . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}else{
				$postdata = [];
				$postdata["code"] = $wallet_response_body['code'];
				$postdata["receiver_id"] = $receiver_id;
				$postdata["recipient_name"] = $recipient_name;
				$postdata["recipient_image_url"] = $recipient_image_url;
				$postdata["draft_transaction_id"] = $draft_transaction_id;
				$postdata["amount"] = $amount;	
			}
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
		return isset($wallet_response_body['data']) ? $postdata : array();
	}

	public function transferP2PD($payee_wallet_id,$amount,$personal_msg='')
	{
		try
		{
			$amount = number_format($amount, 2, '.', '');
			$uri = 'transfer-composite/v2/p2p-transfer/draft-transactions';
			$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  $this->wallet_access_token . '|' . $amount . '|' . $payee_wallet_id);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			$draft_transaction_id = $wallet_response_body['data']['draft_transaction_id'];
			$reference_key = $wallet_response_body['data']['reference_key'];

			$receiver_id = $wallet_response_body['data']['receiver_id'];
			$recipient_name = $wallet_response_body['data']['recipient_name'];
			$recipient_image_url = $wallet_response_body['data']['recipient_image_url'];
			
			$uri = 'transfer-composite/v1/p2p-transfer/draft-transactions/' . $draft_transaction_id;
			$signature = $this->calculate_sign256($reference_key);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"personal_message":"' . $personal_msg . '","signature":"' . $signature . '"}', 'PUT');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}

			$uri = 'transfer-composite/v1/p2p-transfer/transactions/' . $draft_transaction_id . '/';
			$signature = $this->calculate_sign256($reference_key);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"reference_key":"' . $reference_key . '","signature":"' . $signature . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}else{
				$result = $this->getWithdrawalStatus($draft_transaction_id);
				if ($wait_processing) {
					for ($i = 0; $i < 10; $i++) {
						if (isset($result["transfer_status"])) {
							if ($result["transfer_status"] === "PROCESSING") {
								if ($i > 0) sleep(1);
								$result = $this->getWithdrawalStatus($draft_transaction_id);
							} else {
								break;
							}
						} else {
							break;
						}
					}
				}	
				$result = $this->getWithdrawalDetail($draft_transaction_id);					
			}
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
		return isset($wallet_response_body['data']) ? $result : array();
	}
	
	public function transferBankAC($bank_code,$bank_ac,$amount,$wallet_pin)
	{
		try
		{
			$amount = number_format($amount, 2, '.', '');
			$signature = $this->calculate_sign256($amount . '|' . $bank_code . '|' . $bank_ac);
			$wallet_response_body = $this->wallet_connect('fund-composite/v1/withdrawal/draft-transaction', array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"bank_name":"' . $bank_code . '","bank_account":"' . $bank_ac . '","amount":"' . $amount . '"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			$draft_transaction_id = $wallet_response_body['data']['draft_transaction_id'];

			$uri = 'fund-composite/v3/withdrawal/transaction';
			$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  $this->wallet_access_token . '|' . $draft_transaction_id);
			$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"draft_transaction_id":"' . $draft_transaction_id . '"}');
			if(substr($wallet_response_body['code'],-4) != '-428')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			$csid = $wallet_response_body['data']['csid'];

			$wallet_pin = hash('sha256', $this->wallet_tmn_id . $wallet_pin);
			$signature = $this->calculate_sign256($this->wallet_access_token . '|' . $csid . '|' . $wallet_pin . '|manual_input');
			$wallet_response_body = $this->wallet_connect('mobile-auth-service/v1/authentications/pin', array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng=', 'CSID: ' . $csid),
				'{"pin":"' . $wallet_pin . '","method":"manual_input"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage() . ' (line:' . $e->getLine() . ')');
		}
		return isset($wallet_response_body['data']) ? $wallet_response_body['data'] : array();
	}

    public function getPreviewPrompay($prompay, $amount)
    {
		try
		{
			$amount = number_format(str_replace(",", "", strval($amount)), 2, ".", "");
			$signature = $this->calculate_sign256(strval($this->wallet_access_token).'|'.$amount.'|'.strval($prompay).'|QR');
			$uri = 'transfer-composite/v1/promptpay/inquiries';
			$wallet_response_body = $this->wallet_connect($uri, ['Content-Type: application/json', 'Authorization: ' . strval($this->wallet_access_token) , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='],
				'{"input_method":"QR","amount":"'.$amount.'","to_proxy_value":"'.strval($prompay).'"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			return $wallet_response_body['data'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
    }
	
    public function getConfirmPrompay($draft_transaction_id = null)
    {
		try
		{
			$signature = $this->calculate_sign256(strval($this->wallet_access_token).'|'.strval($draft_transaction_id));
			$uri = 'transfer-composite/v1/promptpay/transfers';
			$wallet_response_body = $this->wallet_connect($uri, ['Content-Type: application/json', 'Authorization: ' . strval($this->wallet_access_token) , 'signature: ' . $signature , 'X-Device: ' . $this->wallet_device_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='],
				'{"ref_number":"'.strval($draft_transaction_id).'"}');
			if(substr($wallet_response_body['code'],-4) != '-200')
			{
				throw new Exception($wallet_response_body['code'] . ' - ' . $wallet_response_body['message']);
			}
			return $wallet_response_body['data'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
    }	

	public function getWithdrawalDetail($transaction_id = null)
	{
		$uri = 'transfer-composite/v1/p2p-transfer/transactions/' . strval($transaction_id) . '/detail/';
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
		$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token), '');
		return isset($wallet_response_body['data']) ? $wallet_response_body['data'] : '';
	}

	public function getWithdrawalStatus($transaction_id = null)
	{
		$uri = 'transfer-composite/v1/p2p-transfer/transactions/'. $transaction_id . '/status/';
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
		$wallet_response_body = $this->wallet_connect($uri, array('Content-Type: application/json', 'Authorization: ' . $this->wallet_access_token), '');
		return isset($wallet_response_body['data']) ? $wallet_response_body['data'] : '';
	}
	
		
	private function tmnone_connect($request_body)
	{
		$headers = [];
		$aes_key = hex2bin(substr(hash('sha512', $this->wallet_login_token) ,0 ,64));
		$aes_iv = openssl_random_pseudo_bytes(16);
		$request_body = bin2hex($aes_iv) . base64_encode(openssl_encrypt($request_body, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv));
		$request_body = json_encode(array('encrypted'=>$request_body));
		$curl = curl_init($this->tmnone_endpoint);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-KeyID: ' . $this->tmnone_keyid, 'Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_USERAGENT, 'okhttp/4.4.0/202305202300/' . $this->tmnone_keyid);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
		curl_setopt($curl, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$headers)
			{
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) // ignore invalid headers
				{
					return $len;
				}

				$headers[strtolower(trim($header[0]))] = trim($header[1]);

				return $len;
			}
		);
		$response_body = curl_exec($curl);
		curl_close($curl);
		if(!empty($headers['x-wallet-user-agent']))
		{
			$this->wallet_user_agent = $headers['x-wallet-user-agent'];
		}
		$response_body = json_decode($response_body,true);
		
		if(isset($response_body['encrypted']))
		{
			$response_body = openssl_decrypt(base64_decode($response_body['encrypted']), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			$response_body = json_decode($response_body,true);
		}

		return $response_body;
	}

	private function wallet_connect($uri, $headers, $request_body='', $custom_method=null)
	{
		$ssl_ciphers = array('ECDHE-RSA-AES256-GCM-SHA384','ECDHE-RSA-AES128-GCM-SHA256','ECDHE-RSA-CHACHA20-POLY1305-SHA256','ecdhe_rsa_aes_256_gcm_sha_384','ecdhe_rsa_aes_128_gcm_sha_256','ecdhe_rsa_chacha20_poly1305_sha_256');
		foreach($ssl_ciphers as $ssl_cipher)
		{
			$wallet_connect = $this->wallet_connect_curl($uri, $headers, $request_body, $custom_method, $ssl_cipher);
			if(is_array($wallet_connect) || strpos($wallet_connect,'Unknown cipher') === false)
			{
				break;
			}
		}
		return $wallet_connect;
	}

	private function wallet_connect_curl($uri, $headers, $request_body, $custom_method, $ssl_cipher)
	{
		$curl = curl_init($this->wallet_endpoint . $uri);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslmode);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->wallet_user_agent);
		if(stripos(PHP_OS, 'WIN') === 0)
		{
			curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_3);
		}
		else
		{
			curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, $ssl_cipher);
		}
		if(!empty($this->proxy_ip))
		{
			curl_setopt($curl, CURLOPT_PROXY, $this->proxy_ip);
			if(!empty($this->proxy_username))
			{
				curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_username . ':' . $this->proxy_password);
			}
		}
		if(!empty($request_body))
		{
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
		}
		if(!empty($custom_method))
		{
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $custom_method);
		}
		$response_body = curl_exec($curl);
		if($response_body === false)
		{
			return curl_error($curl);
		}
		curl_close($curl);
		$response_body = json_decode($response_body,true);
		if(empty($response_body))
		{
			return '';
		}
		if(isset($response_body['code']) && $response_body['code'] == 'MAS-401')
		{
			$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'set', 'data'=>''));
			$this->tmnone_connect($request_body);
		}
		return $response_body;
	}
	
    public function sendLineNotify($message, $accesstoken = null) {
		if(is_null($accesstoken)){
			false;
		}
        $url = 'https://notify-api.line.me/api/notify';
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $accesstoken
        );
        $data = array('message' => $message);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslmode);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return '';
    }
	
	public function calculate_sign256($data)
	{
		$request_body = json_encode(array('cmd'=>'calculate_sign256', 'data'=>array('login_token'=>$this->wallet_login_token, 'device_id'=>$this->wallet_device_id, 'data'=>$data)));
		return isset($this->tmnone_connect($request_body)['signature']) ? $this->tmnone_connect($request_body)['signature'] : '';
	}

}


	/*
	###############################################################
	$_TMN = array();
	$_TMN['tmn_key_id'] = '0'; //Key ID จากระบบ TMN.ooo
	$_TMN['mobile_number'] = '09999999999'; //เบอร์ Wallet
	$_TMN['login_token'] = 'L-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; //login_token จากขั้นตอนการเพิ่มเบอร์ Wallet
	$_TMN['pin'] = '999999'; //PIN 6 หลักของ Wallet
	$_TMN['tmn_id'] = 'tmn.1000xxxxxxxx'; //tmn_id จากขั้นตอนการเพิ่มเบอร์ Wallet
	###############################################################	
	// Example usage:
	$TMNOoo = new TMNOoo();
	//$TMNOoo->setProxy('proxy_ip:proxy_port', 'proxy_username', 'proxy_password'); //แก้อาการหน้าขาวดึงรายการไม่ออก
	$TMNOoo->setData($_TMN['tmn_key_id'], $_TMN['mobile_number'], $_TMN['login_token'], $_TMN['tmn_id']);
	$TMNOoo->loginWithPin6($_TMN['pin']); //Login เข้าระบบ Wallet ด้วย PIN
	// ...	
	###############################################################
	*/
	
	#### ดูโปรไฟล์และยอดเงินคงเหลือ
	//$result = $TMNOoo->getBalance(); //ตรวจสอบยอดเงินคงเหลือ

	#### รายการเงินเข้าออก	
	//$result = $TMNOoo->fetchTransactionHistory(date('Y-m-d',time()-7776000), date('Y-m-d',time()+86400),5,1); //ดึงรายการเงินเข้าออก
	//$result = $TMNOoo->fetchTransactionInfo('npah8248647819'); //ดูข้อมูลแบบละเอียด
	
	#### สร้างซองของขวัญ
	//$result = $TMNOoo->generateVoucher(10, $detail=''); //สร้างซองของขวัญ (ซองแดง)
	//$result = $TMNOoo->vouchersTransaction(); //ดูประวัติการสร้างของขวัญ (ซองแดง)
	
	#### โอนเงินแบบวอลเล็ต
	//$result = $TMNOoo->getRecipientName('0639866960'); //เชคชื่อปลายทางผู้รับ
	//$result = $TMNOoo->transferP2P('0639866960', 1, $personal_msg=''); //โอนเงินไปยังวอลเล็ตอื่น ไม่ดูรายละเอียด
	//$result = $TMNOoo->transferP2PD('0639866960', 1, $personal_msg=''); //โอนเงินไปยังวอลเล็ตอื่น พร้อมดูรายละเอียด

	#### โอนเงินแบบธนาคาร	
	//$result = $TMNOoo->transferBankAC('KBANK', '0123456789', 100, $_TMN['pin']); //ถอนเงินเข้าบัญชีธนาคาร
	
	#### โอนเงินแบบพร้อมเพย์
	//$result = $TMNOoo->getPreviewPrompay('0639866960', '1'); //ตรวจสอบข้อมูลพร้อมเพย์ปลายทาง
	//$result = $TMNOoo->getConfirmPrompay('314521008440'); //ยืนยันการโอนพร้อมเพย์
		
	#### โอนเงินแบบซองแดง	
	//$gift = 'https://gift.truemoney.com/campaign/?v=41c2e58a8d0f4f178ebf28265890e77b06u'; //นำลิ้งที่ได้มากรอก หรือกรอกค่ากลัง ?v=xxxxxx
	//$result = $TMNOoo->redeemVouchers($gift, '0639866960'); //กรอกเบอร์ผู้รับเงิน เงินเข้าทันที
	//$result = $TMNOoo->verifyVouchers($gift); //ตรวจสอบสถานะลิ้งค์ และรายละเอียด
	
	#### แจ้งเตือนไลน์ตามสะดวก
	//$result = $TMNOoo->sendLineNotify($message, $line_token = null);
		
	#### บายพาสสแกนใบหน้า | แจ้งปัญหาการใช้งาน
	//| https://web.telegram.org/a/#-863246271 
	//| https://line.me/ti/g2/2bo45OQHotjCE41TkZrDzS1XMCUerGV6mUupdg?utm_source=invitation&utm_medium=link_copy&utm_campaign=default
	
	// ...	
	###############################################################	
	
	//var_dump($result);
	//print_r($result);
	
	###############################################################	


?>
