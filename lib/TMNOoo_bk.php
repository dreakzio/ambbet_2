<?php
date_default_timezone_set("Asia/Bangkok");

class TMNOoo
{	
	private $tmn_app_version = "5.37.0";
    private $wallet_endpoint = "https://tmn-mobile-gateway.public-a-cloud1p.ascendmoney.io/tmn-mobile-gateway/";	
	private $proxy_ip = '', $proxy_username = '', $proxy_password = '';
	private $remote_key_id = "", $remote_key_value;
    public $config = [], $config_path = null;
    public $curl_options = [CURLOPT_SSL_VERIFYPEER => true, // true & false
    ];
    public function __construct($config = null)
    {
        if (is_string($config)){$this->setConfigPath($config);}elseif(is_array($config)){$this->updateConfig($config);$this->prepare_identity();}
    }
    public function prepare_identity()
    {
        if (!isset($this->config["device_id"])){$this->updateConfig("device_id", substr(md5($this->config["mobile_number"].$this->config["tmn_id"]), 0, 16));}
        return true;
    }
    public function setConfigPath($path = null, $merge = false, $reset = true)
    {
        $this->config_path = is_null($path) ? null : strval($path);
        if (!is_null($this->config_path))
        {
            if ($reset){$this->config = [];}
            if ($merge){$merge_config = $this->config;}
            if (!file_exists($this->config_path)){file_put_contents($this->config_path, json_encode($this->config));}
            $this->config = json_decode(file_get_contents($this->config_path) , true);
            if ($merge){$this->config = array_replace($this->config, $merge_config);}
        }
        $this->updateConfig();
        $this->prepare_identity();
        return true;
    }
    public function setConfig($config = null)
    {
        if (is_null($config)){$config = [];}
        $this->config = $config;
        $this->updateConfig();
        $this->prepare_identity();
    }
    public function updateConfig($name = null, $value = null)
    {
        if (is_array($name))
        {
            $this->config = array_replace($this->config, $name);
            foreach ($this->config as $name => $value)
            {
                if (is_null($value))
                {
                    unset($this->config[$name]);
                }
            }
        }
        elseif (is_string($name))
        {
            if (!is_null($value))
            {
                $this->config[$name] = $value;
            }
            else
            {
                unset($this->config[$name]);
            }
        }
        if (isset($this->config["no_file"]) && $this->config["no_file"])
        {
            $this->config_path = null;
        }
        if (!is_null($this->config_path))
        {
            file_put_contents($this->config_path, json_encode($this->config));
        }
        if ((!isset($this->config["no_file"]) || !$this->config["no_file"]) && is_null($this->config_path) && isset($this->config["mobile_number"]))
        {			
            $this->setConfigPath(dirname(__FILE__) . "/" . strval($this->config["mobile_number"]). ".identity", true, false);
        }
        return $this->config;
    }

    private function connect($uri, $headers, $request_body = "", $custom_method = null)
    {
        $ssl_ciphers = ["ECDHE-RSA-AES256-GCM-SHA384", "ECDHE-RSA-AES128-GCM-SHA256", "ECDHE-RSA-CHACHA20-POLY1305-SHA256", "ecdhe_rsa_aes_256_gcm_sha_384", "ecdhe_rsa_aes_128_gcm_sha_256", "ecdhe_rsa_chacha20_poly1305_sha_256", ];
        foreach ($ssl_ciphers as $ssl_cipher)
        {
            $curl_connect = $this->connect_curl($uri, $headers, $request_body, $custom_method, $ssl_cipher);
            if (is_array($curl_connect) || strpos($curl_connect, "Unknown cipher") === false)
            {
                break;
            }
        }
        return $curl_connect;
    }

    private function connect_curl($uri, $headers, $request_body, $custom_method, $ssl_cipher)
    {
        $curl = curl_init($this->wallet_endpoint . $uri);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'tmnApp/truemoney tmnVersion/5.38.0 tmnBuild/539 tmnPlatform/android');
        
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
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, $ssl_cipher);

        if (!empty($request_body))
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
        }
        if (!empty($custom_method))
        {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $custom_method);
        }
        $response_body = curl_exec($curl);
        if ($response_body === false)
        {
            return curl_error($curl);
        }
        curl_close($curl);
        $response_body = json_decode($response_body, true);
		if(isset($response_body['code']) && $response_body['code'] == 'MAS-401')
		{			
			if (!isset($this->config["tmn_id"])) return false;
			if (!isset($this->config["login_token"])) return false;
			$aes_key = hex2bin(substr(hash('sha512', strval($this->config["tmn_id"])) ,0 ,64));
			$aes_iv = hex2bin(substr(hash('sha512', strval($this->config["login_token"])) ,0 ,32));    
			$encrypted_access_token = bin2hex($aes_iv) . base64_encode(openssl_encrypt('', 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv));
			$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'set', 'data'=>array('config'=>$this->config, 'data'=>$encrypted_access_token)));
			$this->tmnooo_connect($request_body);   
			$this->Login(); 
		}
		elseif(empty($this->config["access_token"]) && $response_body['code'] == 'MAG-400')
		{
			$this->Login();					
		}        
        return $response_body;
    }

    public function GetProfile()
    {
        $uri = "user-profile-composite/v1/users/";
        $curl_response_body = $this->connect(
            $uri,
            [
                "Content-Type: application/json",
                "Authorization: " . strval($this->config["access_token"]),
            ],
            ""
        );
        return isset($curl_response_body) ? $curl_response_body : "";
    }

    public function GetBalance()
    {
        $uri = "user-profile-composite/v1/users/balance/";
        $curl_response_body = $this->connect(
            $uri,
            [
                "Content-Type: application/json",
                "Authorization: " . strval($this->config["access_token"]),
            ],
            ""
        );
        return isset($curl_response_body) ? $curl_response_body : "";
    }

    public function GetTransaction($start_date, $end_date, $limit = 20, $page = 1)
    {
        $uri = 'history-composite/v1/users/transactions/history/?start_date=' . $start_date . '&end_date=' . $end_date . '&limit=' . $limit . '&page=' . $page . '&type=&action=';
        $calculate = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], "");
        return isset($curl_response_body['data']['activities']) ? $curl_response_body : $this->GetTransaction_limit(date('Y-m-d',time()-7776000), date('Y-m-d',time()+86400),20,1);
    }

    public function GetTransaction_limit($start_date, $end_date, $limit = 20, $page = 1)
    {
		$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'limit', 'data'=>array('config'=>$this->config)));
		$encrypted_access = $this->tmnooo_connect($request_body)['data'];
		if(!empty($encrypted_access))
		{
			$aes_key = hex2bin(substr(hash('sha512', strval($this->config["tmn_id"])) ,0 ,64));
			$aes_iv = hex2bin(substr(hash('sha512', strval($this->config["mobile_number"])) ,0 ,32));
			$access_data = openssl_decrypt(base64_decode(substr($encrypted_access, 16)), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			if(!empty($access_data))
			{
				$access_data = json_decode($access_data,true);
				$this->remote_key_value = $access_data["signature"];
				$this->remote_key_id = $access_data["device_id"];   
			}  
		}  		  
        $uri =
            "history-composite/v1/users/transactions/history/?start_date=" .
            $start_date .
            "&end_date=" .
            $end_date .
            "&limit=20&page=1&type=&action=";
        $curl_response_body = $this->connect(
            $uri,
            [
                "Content-Type: application/json",
                "Authorization: " . strval($this->config["access_token"]),
                "signature: " . $this->remote_key_value,
                "X-Device: " . $this->remote_key_id,
                "X-Geo-Location: city=; country=; country_code=",
                "X-Geo-Position: lat=; lng=",
            ],
            ""
        );
        return isset($curl_response_body) ? $curl_response_body : [];
    }    
    
	
    public function GetTransactionReport($report_id)
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
        $calculate =  $this->calculate_sign256('/tmn-mobile-gateway/' . $uri);
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], "");
		if(!empty($curl_response_body['data']))
		{
			$aes_iv = openssl_random_pseudo_bytes(16);
			$encrypted_wallet_response_body = bin2hex($aes_iv) . openssl_encrypt(json_encode($curl_response_body), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			file_put_contents($cache_filename, $encrypted_wallet_response_body);
		}		
        return isset($curl_response_body) ? $curl_response_body : [];
    }

	# _| S U C C E S S
    public function DraftTransferP2P($payee_wallet_id)
    {
		try
		{
			$amount = '1.00';
			$uri = 'transfer-composite/v2/p2p-transfer/draft-transactions';
			$calculate = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  strval($this->config["access_token"]) . '|' . $amount . '|' . $payee_wallet_id);
			$curl_response_body = $this->connect($uri, ['Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='],
				'{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			return $curl_response_body['data']['recipient_name'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
    }

    public function ConfirmTransferP2P($payee_wallet_id, $amount, $personal_msg = "")
    {
		try
		{	
			$amount = number_format($amount, 2, '.', '');
			$uri = 'transfer-composite/v2/p2p-transfer/draft-transactions';
			$calculate = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  strval($this->config["access_token"]) . '|' . $amount . '|' . $payee_wallet_id);
			$curl_response_body = $this->connect($uri, array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			$draft_transaction_id = $curl_response_body['data']['draft_transaction_id'];
			$reference_key = $curl_response_body['data']['reference_key'];

			$uri = 'transfer-composite/v1/p2p-transfer/draft-transactions/' . $draft_transaction_id;
			$calculate = $this->calculate_sign256($reference_key);
			$curl_response_body = $this->connect($uri, array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"personal_message":"' . $personal_msg . '","signature":"' . $this->remote_key_value . '"}', 'PUT');
			//print_r($curl_response_body);
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			$uri = 'transfer-composite/v1/p2p-transfer/transactions/' . $draft_transaction_id . '/';
			$calculate = $this->calculate_sign256($reference_key);
			$curl_response_body = $this->connect($uri, array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"reference_key":"' . $reference_key . '","signature":"' . $this->remote_key_value . '"}');
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
		return isset($curl_response_body) ? $curl_response_body : array();
    }

	/*
	$bank_code : SCB,BBL,BAY,KBANK,KTB
	*/
	public function ConfirmTransferBank($bank_code,$bank_ac,$amount)
	{
		try
		{
			$amount = number_format($amount, 2, '.', '');
			$calculate = $this->calculate_sign256($amount . '|' . $bank_code . '|' . $bank_ac);
			$curl_response_body = $this->connect('fund-composite/v1/withdrawal/draft-transaction', array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"bank_name":"' . $bank_code . '","bank_account":"' . $bank_ac . '","amount":"' . $amount . '"}');
			if($curl_response_body['code'] != 'FNC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			$draft_transaction_id = $curl_response_body['data']['draft_transaction_id'];

			$uri = 'fund-composite/v3/withdrawal/transaction';
			$calculate = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' .  strval($this->config["access_token"]) . '|' . $draft_transaction_id);
			$curl_response_body = $this->connect($uri, array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='),
				'{"draft_transaction_id":"' . $draft_transaction_id . '"}');
			if($curl_response_body['code'] != 'MAS-428')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			$csid = $curl_response_body['data']['csid'];

            $wallet_pin = hash("sha256", strval($this->config["tmn_id"]) . strval($this->config["pin"]));
			$calculate = $this->calculate_sign256(strval($this->config["access_token"]) . '|' . $csid . '|' . $wallet_pin . '|manual_input');
			$curl_response_body = $this->connect('mobile-auth-service/v1/authentications/pin', array('Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng=', 'CSID: ' . $csid),
				'{"pin":"' . $wallet_pin . '","method":"manual_input"}');
			if($curl_response_body['code'] != 'FNC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage() . ' (line:' . $e->getLine() . ')');
		}
		return isset($curl_response_body) ? $curl_response_body : array();
	}

    public function CreateVouchers($amount)
    {
        $calculate = $this->calculate_sign256("/tmn-mobile-gateway/transfer-composite/v1/vouchers/|".strval($this->config["access_token"])."|R|".floor($amount)."|1|TMN");
        $uri = "transfer-composite/v1/vouchers/";
        $curl_response_body = $this->connect(
            $uri,
            [
                "Content-Type: application/json",
                "signature: " . $this->remote_key_value,
                "X-Device: " . $this->remote_key_id,
                "Authorization: " . strval($this->config["access_token"]),
                "X-Geo-Location: city=; country=; country_code=",
                "X-Geo-Position: lat=; lng=",
            ],
            '{"amount":"'.floor($amount).'","voucher_type":"R","detail":"TMN","member":"1","tmn_id":"'.strval($this->config["tmn_id"]).'","mobile":"'.strval($this->config["mobile_number"]).'"}',
            "POST"
        );        
        return isset($curl_response_body["link_redeem"]) ? $curl_response_body["link_redeem"] : "";
    }

    public function Vouchers_Transaction()
    {
        $uri = "transfer-composite/v1/vouchers/?tmnId=".strval($this->config["tmn_id"])."&limit=20&page=0";
        $data = "/tmn-mobile-gateway/".$uri."|".strval($this->config["access_token"]);
        $calculate = $this->calculate_sign256($data);
        $curl_response_body = $this->connect(
            $uri,
            [
                "Content-Type: application/json",
                "signature: " . $this->remote_key_value,
                "X-Device: " . $this->remote_key_id,
                "Authorization: " . strval($this->config["access_token"]),
                "X-Geo-Location: city=; country=; country_code=",
                "X-Geo-Position: lat=; lng=",
            ],
            ""
        );
        return isset($curl_response_body) ? $curl_response_body : "";
    } 
	
    public function DraftTransferPrompay($prompay, $amount)
    {
		try
		{
			$amount = number_format(str_replace(",", "", strval($amount)), 2, ".", "");
			$calculate = $this->calculate_sign256(strval($this->config["access_token"]).'|'.$amount.'|'.strval($prompay).'|QR');
			$uri = 'transfer-composite/v1/promptpay/inquiries';
			$curl_response_body = $this->connect($uri, ['Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='],
				'{"input_method":"QR","amount":"'.$amount.'","to_proxy_value":"'.strval($prompay).'"}');
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			return $curl_response_body['data'];
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
    }

    public function ConfirmTransferPrompay($draft_transaction_id = null)
    {
		try
		{
			$calculate = $this->calculate_sign256(strval($this->config["access_token"]).'|'.strval($draft_transaction_id));
			$uri = 'transfer-composite/v1/promptpay/transfers';
			$curl_response_body = $this->connect($uri, ['Content-Type: application/json', 'Authorization: ' . strval($this->config["access_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng='],
				'{"ref_number":"'.strval($draft_transaction_id).'"}');
			if($curl_response_body['code'] != 'TRC-200')
			{
				throw new Exception($curl_response_body['code'] . ' - ' . $curl_response_body['message']);
			}
			return $curl_response_body;
		}
		catch (Exception $e)
		{
			return array('error'=>$e->getMessage());
		}
    }
  
	public function setProxy($proxy_ip, $proxy_username, $proxy_password) {
		$this->proxy_ip = $proxy_ip;
		$this->proxy_username = $proxy_username;
		$this->proxy_password = $proxy_password;
	}
		
    public function Login()
    {
		if(!empty($this->getCachedAccessToken()))
		{
			return strval($this->config["access_token"]);
		}   
        $wallet_pin = hash("sha256", strval($this->config["tmn_id"]) . strval($this->config["pin"]));
        $postdata = [];
        $postdata["pin"] = $wallet_pin;
        $postdata["app_version"] = $this->tmn_app_version;
        $postdata = json_encode($postdata);
        $calculate = $this->calculate_sign256(strval($this->config["login_token"]) . '|' . hash("sha256", strval($this->config["tmn_id"]) . strval($this->config["pin"])));
       
	    $curl_response_body = $this->connect("mobile-auth-service/v1/pin/login", ["Content-Type: application/json", "Authorization: " . strval($this->config["login_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], $postdata);
        if (isset($curl_response_body["data"]["access_token"]))
        {       
			$aes_key = hex2bin(substr(hash('sha512', strval($this->config["tmn_id"])) ,0 ,64));
			$aes_iv = hex2bin(substr(hash('sha512', strval($this->config["login_token"])) ,0 ,32));    
			$encrypted_access_token = bin2hex($aes_iv) . base64_encode(openssl_encrypt($curl_response_body["data"]["access_token"], 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv));
			$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'set', 'data'=>array('config'=>$this->config, 'data'=>$encrypted_access_token)));
			$this->tmnooo_connect($request_body);        
            $this->updateConfig("access_token", $curl_response_body["data"]["access_token"]);
        }
        return isset($curl_response_body) ? $curl_response_body : "";
    }
	
	public function getCachedAccessToken()
	{
		if (!isset($this->config["login_token"])) return false;
		if (!isset($this->config["tmn_id"])) return false;	
		$request_body = json_encode(array('scope'=>'text_storage_obj', 'cmd'=>'get', 'data'=>array('config'=>$this->config)));
		$encrypted_access_token = isset($this->tmnooo_connect($request_body)["data"]) ? $this->tmnooo_connect($request_body)["data"] : '';
		if(!empty($encrypted_access_token))
		{
			$aes_key = hex2bin(substr(hash('sha512', strval($this->config["login_token"])) ,0 ,64));
			$aes_iv = hex2bin(substr(hash('sha512', strval($this->config["tmn_id"])) ,0 ,32));
			$access_token = openssl_decrypt(base64_decode(substr($encrypted_access_token, 32)), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			if(!empty($access_token))
			{
                $this->updateConfig("access_token", $access_token);
				return strval($this->config["access_token"]);
			}  
		}	  	     
	}

    public function tmnooo_connect($request_body)
    {
		if (!isset($this->config["tmn_key_id"])) return false;
		if (!isset($request_body)) return false;
        $aes_key = hex2bin(substr(hash('sha512', strval($this->config["tmn_key_id"])) ,0 ,64));
        $aes_iv = substr(md5($this->config["tmn_key_id"].$this->config["tmn_key_id"]), 0, 16) ;
        $aes_body = bin2hex($aes_iv) . base64_encode(openssl_encrypt($request_body, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv));
        $aes_body = json_encode(array('encrypted'=>$aes_body));
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.tmn.ooo/api.php',
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $aes_body,
		  CURLOPT_HTTPHEADER => array(
			'X-KeyID: '.strval($this->config["tmn_key_id"]),
			'Content-Type: application/json'
		  ),
		));
		
		$response_body = curl_exec($curl);
		curl_close($curl);       
		$response_body = json_decode($response_body,true);
		if(isset($response_body['encrypted']))
		{ 
			$response_body = openssl_decrypt(base64_decode($response_body['encrypted']), 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
			$response_body = json_decode($response_body,true);
            $this->remote_key_value = $response_body["signature"];
            $this->remote_key_id = $response_body["device_id"];
		}else{
			return $response_body;           
		}
    }
		
    public function calculate_sign256($data)
	{
		$request_body = json_encode(array('cmd'=>'calculate_sign256', 'data'=>array('config'=>$this->config, 'data'=>$data)));
		return $this->tmnooo_connect($request_body);
	}
	
}


	//==========================================================
	//RESPONSE CODE : 
	//==========================================================
	# |1015= PLEASE CONNECT VIA PROXY
	# |400 = INCORRECT INFORMATION
	# |401 = ACCESSTOKEN EXPIRED
	# |403 = SIGNATRUE EXPIRED //Please try again in (whatever time they should wait)
	/*
	!!! อย่าลืมปกป้องไฟล์ของคุณ สร้างไฟล์ .htaccess แล้วใส่ code ด้านล่างนี้ป้องกันคนเข้ามาดู .identity ข้อมูลบัญชีของคุณ
	<files *.identity>
	Order allow/deny
	Allow from localhost
	Deny from all
	</files>
	*/
	//==========================================================	
	/*$_TMN = array();
	$_TMN['tmn_key_id'] = '35'; //Key ID จากระบบ TMNOne
	$_TMN['mobile_number'] = '0800830911'; //เบอร์ Wallet
	$_TMN['login_token'] = 'L-e5925681-7019-42ee-9ea3-b36134162e89'; //login_token จากขั้นตอนการเพิ่มเบอร์ Wallet
	$_TMN['pin'] = '110739'; //อย่าลืมใส่ PIN 6 หลักของ Wallet
	$_TMN['tmn_id'] = 'tmn.10072946616'; //tmn_id จากขั้นตอนการเพิ่มเบอร์ Wallet
	
	$TMNOoo = new TMNOoo($_TMN);
	$TMNOoo->setProxy('zproxy.lum-superproxy.io:22225', 'brd-customer-hl_ebdb3c0e-zone-data_center-country-th', '0pi1xakwwrg5'); //เปิดใช้งาน HTTP Proxy สำหรับเชื่อมต่อกับระบบของ Wallet*/
	
	//==========================================================
	
	# |login - ไม่จำเป็นต้องเปิดตลอด ใช้เข้าระบบครั้งแรกก็พอ
	//$ooo = $TMNOoo->Login();
		
		
	# |profile - ดูโปรไฟล์และยอดเงินคงเหลือบัญชี
	//$ooo = $TMNOoo->GetProfile();
	//$ooo = $TMNOoo->GetBalance();
    //print_r($ooo);
	//echo "";
	
	# |transaction - ดูรายละเอียดการโอนรับเงินเข้า-เงินออก
	//$ooo = $TMNOoo->GetTransaction(date('Y-m-d',time()-7776000), date('Y-m-d',time()+86400),10,1);
	//$ooo = $TMNOoo->GetTransactionReport('umk4073417788'); 
	//print_r($ooo);
	
	# |transferwallet - โอนไปเบอร์วอลเล็ทอื่นๆ
	//$ooo = $TMNOoo->DraftTransferP2P('0824898822');
	//$ooo = $TMNOoo->ConfirmTransferP2P('0824898822','1',$personal_msg='');
	//print_r($ooo);


	# |transferprompay - ถอนเงินเข้าพร้อมเพย์ ฟรีค่าธรรมเนียมเงินเข้าทันที
	//$ooo = $TMNOoo->DraftTransferPrompay('0639866960','1');
	//$ooo = $TMNOoo->ConfirmTransferPrompay('x31xx90xxx56');	
	
	
	# |transferbank - ถอนเงินเข้าธนาคารค่าธรรมเนียม 20 รอเงินเข้า 1-2 ชม 
	//$ooo = $TMNOoo->ConfirmTransferBank('KBANK', '0218396119', '200');
	
	
	# |redvoucher - สร้างซองแดงและประวัติการสร้าง
	//$ooo = $TMNOoo->CreateVouchers('10'); ใส่จำนวนเงินที่ต้องการสร้าง
	//$ooo = $TMNOoo->Vouchers_Transaction();

	//==========================================================
		
	//print_r(isset($ooo) ? $ooo : '');
?>
