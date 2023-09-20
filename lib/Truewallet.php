<?php
class Truewallet
{
    public $config = [];
    public $config_path = null;
    private $tmnme_endpoint = "http://139.59.249.101/217225996201000.php";
    private $wallet_endpoint = "https://tmn-mobile-gateway.public-a-cloud1p.ascendmoney.io/tmn-mobile-gateway/";
    public $remote_key_ver = "5.48.0";
	public $useragent = "tmnApp/truemoney tmnVersion/5.48.0 tmnBuild/710 tmnPlatform/android";
    public $remote_key_id = "";
    public $remote_key_value = "";
	public $shield_id = "";
    public $proxy_ip = "http://brd.superproxy.io:22225", $proxy_username = "brd-customer-hl_ebdb3c0e-zone-data_center", $proxy_password = "0pi1xakwwrg5";


    public function __construct($config = null)
    {
        if (is_string($config))
        {
            $this->setConfigPath($config);
        }
        elseif (is_array($config))
        {
            $this->updateConfig($config);
            $this->prepare_identity();
        }
        date_default_timezone_set("Asia/Bangkok");
    }

    public function prepare_identity()
    {
        $device_brands = ["samsung"];
        $device_models = ["SM-N950N", "SM-G930K", "SM-G955N", "SM-G965N", "SM-G930L", "SM-G925F", "SM-N950F", "SM-N9005", "SM-G9508", "SM-N935F", "SM-N950W", "SM-G9350", "SM-G955F", "SM-N950U", "SM-G955U", "SM-G950U1", ];
        if (!isset($this->config["device_id"]))
        {
            $this->updateConfig("device_id", substr(md5($this->config["username"]) , 0, 16));
        }
        if (!isset($this->config["mobile_tracking"]))
        {
            $this->updateConfig("mobile_tracking", base64_encode(openssl_random_pseudo_bytes(40)));
        }
        if (!isset($this->config["device_brand"]) || !isset($this->config["device_model"]))
        {
            $this->updateConfig("device_brand", $device_brands[array_rand($device_brands) ]);
            $this->updateConfig("device_model", $device_models[array_rand($device_models) ]);
        }
        return true;
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

    public function setConfigPath($path = null, $merge = false, $reset = true)
    {
        $this->config_path = is_null($path) ? null : strval($path);
        if (!is_null($this->config_path))
        {
            if ($reset)
            {
                $this->config = [];
            }
            if ($merge)
            {
                $merge_config = $this->config;
            }
            if (!file_exists($this->config_path))
            {
                file_put_contents($this->config_path, json_encode($this->config));
            }
            $this->config = json_decode(file_get_contents($this->config_path) , true);
            if ($merge)
            {
                $this->config = array_replace($this->config, $merge_config);
            }
        }
        $this->updateConfig();
        $this->prepare_identity();
        return true;
    }

    public function setConfig($config = null)
    {
        if (is_null($config))
        {
            $config = [];
        }
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
        if (isset($this->config["username"]) && isset($this->config["password"]) && !isset($this->config["type"]))
        {
            $this->updateConfig("type", "mobile");
        }
        if ((!isset($this->config["no_file"]) || !$this->config["no_file"]) && is_null($this->config_path) && isset($this->config["username"]))
        {
            $this->setConfigPath(dirname(__FILE__) . "/" . $this->config["username"] . ".identity", true, false);
        }
        return $this->config;
    }

    public function setProxy($proxy_ip, $proxy_username, $proxy_password)
    {
        $this->proxy_ip = $proxy_ip;
        $this->proxy_username = $proxy_username;
        $this->proxy_password = $proxy_password;
    }

    private function connect_curl($uri, $headers, $request_body, $custom_method, $ssl_cipher)
    {
        $curl = curl_init($this->wallet_endpoint . $uri);
        if (!empty($this->proxy_ip))
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy_ip);
            if (!empty($this->proxy_username))
            {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_username . ":" . $this->proxy_password);
            }
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent);
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
        if ($response_body["code"]=== "MAS-401")
        {
            $this->Login();
        }         
        return $response_body;
    }
	
    /*
    $bank_code : SCB,BBL,BAY,KBANK,KTB
    */
    public function ConfirmTransferBank($bank_code, $bank_ac, $amount)
    {
        try
        {
            $amount = number_format($amount, 2, ".", "");
            $calculate = $this->calculate_sign256($amount . "|" . $bank_code . "|" . $bank_ac);
            $curl_response_body = $this->connect("fund-composite/v1/withdrawal/draft-transaction", ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"bank_name":"' . $bank_code . '","bank_account":"' . $bank_ac . '","amount":"' . $amount . '"}');
            if ($curl_response_body["code"] != "FNC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
            $draft_transaction_id = $curl_response_body["data"]["draft_transaction_id"];

            $uri = "fund-composite/v3/withdrawal/transaction";
            $calculate = $this->calculate_sign256("/tmn-mobile-gateway/" . $uri . "|" . strval($this->config["access_token"]) . "|" . $draft_transaction_id);
            $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"draft_transaction_id":"' . $draft_transaction_id . '"}');
            if ($curl_response_body["code"] != "MAS-428")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
            $csid = $curl_response_body["data"]["csid"];

            $wallet_pin = hash("sha256", strval($this->config["tmn_id"]) . strval($this->config["pin"]));
            $calculate = $this->calculate_sign256(strval($this->config["access_token"]) . "|" . $csid . "|" . $wallet_pin . "|manual_input");
            $curl_response_body = $this->connect("mobile-auth-service/v1/authentications/pin", ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", "CSID: " . $csid, ], '{"pin":"' . $wallet_pin . '","method":"manual_input"}');
            if ($curl_response_body["code"] != "FNC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
        }
        catch(Exception $e)
        {
            return ["error" => $e->getMessage() . " (line:" . $e->getLine() . ")", ];
        }
        return isset($curl_response_body) ? $curl_response_body : [];
    }

    public function CreateVouchers($amount)
    {
        $singature = $this->calculate_sign256("/tmn-mobile-gateway/transfer-composite/v1/vouchers/|" . $this->config["access_token"] . "|R|" . $amount . "|1|TMN");
        $uri = "transfer-composite/v1/vouchers/";
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "signature: " . $singature, "X-Device: " . strval($this->config["device_id"]) , "Authorization: " . strval($this->config["access_token"]) , "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"amount":"' . $amount . '","voucher_type":"R","detail":"TMN","member":"1","tmn_id":"' . strval($this->config["tmn_id"]) . '","mobile":"' . strval($this->config["username"]) . '"}', "POST");
        return isset($curl_response_body["link_redeem"]) ? $curl_response_body["link_redeem"] : "";
    }

    public function Login_v1($pin = null)
    {
        if (empty($pin))
        {
            $pin = $this->config["pin"];
        }
        $wallet_pin = hash("sha256", strval($this->config["tmn_id"]) . strval($pin));
        $postdata = [];
        $postdata["pin"] = $wallet_pin;
        $postdata["app_version"] = $this->remote_key_ver;
        $postdata = json_encode($postdata);
        $calculate = $this->calculate_sign256(strval($this->config["login_token"]) . "|" . hash("sha256", strval($this->config["tmn_id"]) . strval($pin)));
        $curl_response_body = $this->connect("mobile-auth-service/v1/pin/login", ["Content-Type: application/json", "Authorization: " . strval($this->config["login_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], $postdata);
        if (isset($curl_response_body))
        {
            $this->updateConfig("access_token", $curl_response_body["data"]["access_token"]);
        }
        return isset($curl_response_body) ? $curl_response_body : "";
    }

	public function Login($pin = null)
	{
        if (empty($pin))
        {
            $pin = $this->config["pin"];
        }
		$uri = 'mobile-auth-service/v2/pin/login';
		$wallet_pin = hash('sha256', strval($this->config["tmn_id"]) . strval($pin));
		$signature = $this->calculate_sign256('/tmn-mobile-gateway/' . $uri . '|' . strval($this->config["login_token"]) . '|' . $wallet_pin);
		$postdata = array();
		$postdata['pin'] = $wallet_pin;
		$postdata["app_version"] = $this->remote_key_ver;
		$postdata = json_encode($postdata);
		$curl_response_body = $this->connect($uri, array('Content-Type: application/json', 'Authorization: ' . strval($this->config["login_token"]) , 'signature: ' . $this->remote_key_value , 'X-Device: ' . $this->remote_key_id, 'X-Geo-Location: city=; country=; country_code=', 'X-Geo-Position: lat=; lng=', 'X-Shield-Session-Id: ' . $this->shield_id), $postdata);
		if(!empty($curl_response_body['data']['access_token']))
		{
			$this->updateConfig("access_token", $curl_response_body["data"]["access_token"]);
		}
		return isset($curl_response_body) ? $curl_response_body : "";
	}

	
    public function DraftTransferP2P($payee_wallet_id)
    {
        try
        {
            $amount = "1.00";
            $uri = "transfer-composite/v2/p2p-transfer/draft-transactions";
            $calculate = $this->calculate_sign256("/tmn-mobile-gateway/" . $uri . "|" . strval($this->config["access_token"]) . "|" . $amount . "|" . $payee_wallet_id);
            $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
            if ($curl_response_body["code"] != "TRC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
            return $curl_response_body;
        }
        catch(Exception $e)
        {
            return ["error" => $e->getMessage() ];
        }
    }

    public function ConfirmTransferP2P($payee_wallet_id, $amount, $personal_msg = "")
    {
        try
        {
            $amount = number_format($amount, 2, ".", "");
            $uri = "transfer-composite/v2/p2p-transfer/draft-transactions";
            $calculate = $this->calculate_sign256("/tmn-mobile-gateway/" . $uri . "|" . strval($this->config["access_token"]) . "|" . $amount . "|" . $payee_wallet_id);
            $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"receiverId":"' . $payee_wallet_id . '","amount":"' . $amount . '"}');
            if ($curl_response_body["code"] != "TRC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
            $draft_transaction_id = $curl_response_body["data"]["draft_transaction_id"];
            $reference_key = $curl_response_body["data"]["reference_key"];

            $uri = "transfer-composite/v1/p2p-transfer/draft-transactions/" . $draft_transaction_id;
            $calculate = $this->calculate_sign256($reference_key);
            $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"personal_message":"' . $personal_msg . '","signature":"' . $this->remote_key_value . '"}', "PUT");
            if ($curl_response_body["code"] != "TRC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }

            $uri = "transfer-composite/v1/p2p-transfer/transactions/" . $draft_transaction_id . "/";
            $calculate = $this->calculate_sign256($reference_key);
            $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], '{"reference_key":"' . $reference_key . '","signature":"' . $this->remote_key_value . '"}');
            if ($curl_response_body["code"] != "TRC-200")
            {
                throw new Exception($curl_response_body["code"] . " - " . $curl_response_body["message"]);
            }
        }
        catch(Exception $e)
        {
            return ["error" => $e->getMessage() ];
        }
        return isset($curl_response_body) ? $curl_response_body : [];
    }

    public function GetTransactionReport($report_id)
    {
        $uri = "history-composite/v1/users/transactions/history/detail/" . $report_id . "?version=1";
        $calculate = $this->calculate_sign256("/tmn-mobile-gateway/" . $uri);
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], "");
        return isset($curl_response_body) ? $curl_response_body : [];
    }

    public function GetProfile()
    {
        $uri = "user-profile-composite/v1/users/";
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , ], "");
        return isset($curl_response_body) ? $curl_response_body : "";
    }

    public function GetBalance()
    {
        $uri = "user-profile-composite/v1/users/balance/";
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , ], "");
		//var_dump($curl_response_body);
        return isset($curl_response_body) ? $curl_response_body : "";
    }

    public function GetTransaction($start_date, $end_date, $limit = 20, $page = 1)
    {
        $uri = "history-composite/v1/users/transactions/history/?start_date=" . $start_date . "&end_date=" . $end_date . "&limit=" . $limit . "&page=" . $page . "&type=&action=";
        $calculate = $this->calculate_sign256("/tmn-mobile-gateway/" . $uri);
        $curl_response_body = $this->connect($uri, ["Content-Type: application/json", "Authorization: " . strval($this->config["access_token"]) , "signature: " . $this->remote_key_value, "X-Device: " . $this->remote_key_id, "X-Geo-Location: city=; country=; country_code=", "X-Geo-Position: lat=; lng=", ], "");
        return isset($curl_response_body) ? $curl_response_body : [];
    }

    private function tmnme_connect($request_body)
    {
        $headers = [];
        $aes_key = hex2bin(substr(hash("sha512", strval($this->config["username"])) , 0, 64));
        $aes_iv = openssl_random_pseudo_bytes(16);
        $request_body = bin2hex($aes_iv) . base64_encode(openssl_encrypt($request_body, "AES-256-CBC", $aes_key, OPENSSL_RAW_DATA, $aes_iv));
        $request_body = json_encode(["encrypted" => $request_body]);
        $curl = curl_init($this->tmnme_endpoint);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-KeyID: " . strval($this->config["username"]) , "Content-Type: application/json", ]);
        curl_setopt($curl, CURLOPT_USERAGENT, "okhttp/4.4.0/202305202300/217225996201000");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers)
        {
            $len = strlen($header);
            $header = explode(":", $header, 2);
            if (count($header) < 2)
            {
                // ignore invalid headers
                return $len;
            }

            $headers[strtolower(trim($header[0])) ] = trim($header[1]);

            return $len;
        });
        $response_body = curl_exec($curl);
        curl_close($curl);
        $response_body = json_decode($response_body, true);       
        if (isset($response_body["encrypted"]))
        {
            $response_body = openssl_decrypt(base64_decode($response_body["encrypted"]) , "AES-256-CBC", $aes_key, OPENSSL_RAW_DATA, $aes_iv);
            $response_body = json_decode($response_body, true);
            $this->remote_key_value = $response_body["signature"];
            $this->remote_key_id = $response_body["device_id"];
			$this->shield_id = $response_body["shield_id"];
        }
        return $response_body;
    }

    public function calculate_sign256($data)
    {
        $request_body = json_encode(["cmd" => "calculate_sign256", "data" => ["authorization" => json_encode($this->config) , "data" => $data, ], ]);
        return isset($this->tmnme_connect($request_body) ["signature"]) ? $this->tmnme_connect($request_body) ["signature"] : "";
    }

    public function RegisterAccount()
    {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'http://139.59.249.101/ArM/TMNOne.php',
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
			"tmn_key_id": "'.$this->config["tmn_key_id"].'",
			"mobile_number": "'.$this->config["mobile_number"].'",
			"login_token": "'.$this->config["login_token"].'",
			"pin": "'.$this->config["pin"].'",
			"tmn_id": "'.$this->config["tmn_id"].'"
		}',
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		if($response == "SUCCESS"){
			$postdata = [];
			$postdata["code"] = "200";
			$postdata["msg"] = "ลงททะเบียนสำร็จ";
			return $postdata;
		}else{
			$postdata = [];
			$postdata["code"] = "403";
			$postdata["msg"] = "ไม่สามารถลงทะบียนได้";
			return $postdata;
		}
				
    }
	
}
?>
