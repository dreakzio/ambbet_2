<?php
//MisterNT
ini_set("display_errors",0);
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set("Asia/Bangkok");

class KkpClass
{
	private $api_gateway = "https://ebankingapi.kkpfg.com/";
	public $config_path = null;
	public $config = array();
	public $curl_options = null;
	public $version = "2.4.0";
	public $curr_re_try =0;


	public function __construct($config = null)
	{
		if (is_string($config)) {
			$this->setConfigPath($config);
		} elseif (is_array($config)) {
			$this->updateConfig($config);
			$this->prepare_identity();
		}
		// $this->config["deviceId"] = "838737fc-b0d6-3b98-b18f-5c4f3f7b3de0";
		//  $this->generateKey();
	}
	public function generateGuid()
	{
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
	public function prepare_identity()
	{
		$device_brands = array("samsung");
		$device_models = array(
			"SM-N950N", "SM-G930K", "SM-G955N", "SM-G965N",
			"SM-G930L", "SM-G925F", "SM-N950F", "SM-N9005",
			"SM-G9508", "SM-N935F", "SM-N950W", "SM-G9350",
			"SM-G955F", "SM-N950U", "SM-G955U", "SM-G950U1"
		);
		if (!isset($this->config["deviceId"])) {
			$this->updateConfig("deviceId", $this->generateGuid());
		}
		if (!isset($this->config["device_brand"]) || !isset($this->config["device_model"])) {
			$this->updateConfig("device_brand", $device_brands[array_rand($device_brands)]);
			$this->updateConfig("device_model", $device_models[array_rand($device_models)]);
		}
		$this->generateKey();
		return true;
	}
	public function setConfigPath($path = null, $merge = false, $reset = true)
	{
		$this->config_path = is_null($path) ? null : strval($path);
		if (!is_null($this->config_path)) {
			if ($reset) $this->config = array();
			if ($merge) $merge_config = $this->config;
			if (!file_exists($this->config_path)) file_put_contents($this->config_path, json_encode($this->config));
			//print_r(json_decode(file_get_contents($this->config_path), true));
			$this->config = json_decode(file_get_contents($this->config_path), true);

			if ($merge) $this->config = array_replace($this->config, $merge_config);
		}
		$this->updateConfig();
		$this->prepare_identity();
		return true;
	}

	public function setConfig($config = null)
	{
		if (is_null($config)) $config = array();
		$this->config = $config;
		$this->updateConfig();
		$this->prepare_identity();
	}

	public function updateConfig($name = null, $value = null)
	{
		if (is_array($name)) {
			$this->config = array_replace($this->config, $name);
			foreach ($this->config as $name => $value) {
				if (is_null($value)) unset($this->config[$name]);
			}
		} elseif (is_string($name)) {
			if (!is_null($value)) {
				$this->config[$name] = $value;
			} else {
				unset($this->config[$name]);
			}
		}
		if (isset($this->config["no_file"]) && $this->config["no_file"]) $this->config_path = null;
		if (!is_null($this->config_path)) file_put_contents($this->config_path, json_encode($this->config));
		if ((!isset($this->config["no_file"]) || !$this->config["no_file"]) && is_null($this->config_path) && isset($this->config["idCard"])) {
			$this->setConfigPath(dirname(__FILE__) . "/" . $this->config["idCard"] . ".identity", true, false);
		}
		return $this->config;
	}
	public function generateKey()
	{
		if (!file_exists(dirname(__FILE__) . "/private_" . $this->config["deviceId"] . ".pem")) {
			$new_key_pair = openssl_pkey_new(array(
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			));
			openssl_pkey_export($new_key_pair, $private_key_pem);

			$details = openssl_pkey_get_details($new_key_pair);
			$public_key_pem = $details['key'];
			file_put_contents(dirname(__FILE__) . "/private_" . $this->config["deviceId"] . ".pem", $private_key_pem);
			file_put_contents(dirname(__FILE__) . "/public_" . $this->config["deviceId"] . ".pem", $public_key_pem);
		}
	}
	public function generateSignature($payload)
	{
		$privateKey = file_get_contents(dirname(__FILE__) . "/private_" . $this->config["deviceId"] . ".pem");
		openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
		return base64_encode($signature);
	}
	public function checkDevice()
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/UserManagementAdapter/ACT_CHECK_DEVICE_UUID", array(), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"deviceUUID" =>  $this->config["deviceId"],
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));

		return $result;
	}
	public function verifySubIdCard()
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/SubscriptionAdapter/ACT_VERIFY_SUBSCRIPTION_ID_CARD", array(), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"deviceUUID" =>  $this->config["deviceId"],
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"idIssueCountryCode" => "en",
			"idNo" => strval($this->config["idCard"]),
			"idType" => "I",

			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
			"deviceUUID" =>  $this->config["deviceId"],
		));
		return $result;
	}
	public function verifyMypin()
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/SubscriptionAdapter/ACT_VERIFY_SUBSCRIPTION_MY_PIN", array(), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"deviceUUID" =>  $this->config["deviceId"],
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"idIssueCountryCode" => "en",
			"idNo" => strval($this->config["idCard"]),
			"idType" => "I",

			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
			"deviceUUID" =>  $this->config["deviceId"],
		));
		return $result;
	}
	public function RequestOTP()
	{
		$this->checkDevice();
		$this->verifySubIdCard();
		$result = $this->request("POST", "/kkpmobileapi/v1/SubscriptionAdapter/ACT_VERIFY_SUBSCRIPTION_MY_PIN", array(), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"idIssueCountryCode" => "NONE",
			"idNo" => strval($this->config["idCard"]),
			"idType" => "I",
			"pin" => strval($this->config["pin"]),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		//print_r($result);

		if (isset($result["result"]["value"]["verifyTransactionId"])) {
			$resultReqOtp = $this->request("POST", "/kkpmobileapi/v1/SubscriptionAdapter/ACT_REQUEST_OTP", array(), array(
				"actionOTP" => "create_pin",
				"actionType" => "MY_PIN",
				"appVersion" => "",
				"cisID" => "",
				"clientIP" => "",
				"developerMessage" => "",
				"header" =>  array(
					"channelID" => "RIBMobile",
					"referenceNo" => "20220926073523379.0",
					"serviceName" => "KKP_MOBILE",
					"systemCode" => "RIB",
					"transactionDate" => "20220926073523",
					"transactionDateTime" => "2022-09-26T07:35:23.948",
				),
				"language" => strval("en"),
				"idNo" => strval($this->config["idCard"]),
				"idType" => "I",
				"subscriptionChannel" => "MY_PIN",
				"verifyTransactionId" => $result["result"]["value"]["verifyTransactionId"],
				"platform" => array(
					"deviceModel" => $this->config["device_model"],
					"deviceName" => $this->config["device_model"],
					"deviceToken" => "deviceToken",
					"deviceType" => "Android",
					"deviceUUID" => $this->config["deviceId"],
					"isIllegal" => "FALSE",
					"osName" => "Android",
					"osVersion" => "10",
					"osname" => "Android",
					"osversion" => "10",
				),
				"tokenID" => "",
				"deviceId" => $this->config["deviceId"],
			));
			$resultReqOtp["verifyTransactionId"] = $result["result"]["value"]["verifyTransactionId"];
			return $resultReqOtp;
		}
	}
	public function SummitOTP($otp, $referenceNO, $verifyTransactionId)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/SubscriptionAdapter/ACT_VERIFY_OTP", array(), array(
			"actionOTP" => "verify_pin",
			"actionType" => "MY_PIN",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "127.0.0.1",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"limitDevice" => false,
			"otp" => $otp,
			"referenceNO" => $referenceNO,
			"subscriptionChannel" => "MY_PIN",
			"verifyTransactionId" => $verifyTransactionId,
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenOTPForCAA" => "",
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));

		$this->setPublicKey();
		return $result;
	}
	public function LoginPin()
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/authenticationAdapter/ACT_VERIFY_MY_PIN", array(), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"pin" => strval($this->config["pin"]),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		if (isset($result["result"]["value"]["accessToken"])) $this->config["access_token"] = $result["result"]["value"]["accessToken"];
		if (isset($result["result"]["value"]["sessionToken"])) $this->config["sessionToken"] = $result["result"]["value"]["sessionToken"];
		if (isset($result["result"]["value"]["sessionToken"]))  $this->summary();


		$this->setPublicKey();
		return $result;
	}

	public function summary()
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/AccountManagementAdapter/ACT_INQUIRY_MANAGE_MY_ACCOUNTS", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
		));

		if($result['error_response']['responseStatus']['httpStatusCode']!=''){
			if($this->curr_re_try <2){
				//echo "test";
				$this->LoginPin();
				$this->summary();
				$this->curr_re_try++;
			}else{
				return $result;
			}
		}else{
			if (isset($result["result"]["value"][0]["myAccountID"])) $this->config["myAccountID"] = $result["result"]["value"][0]["myAccountID"];
			if (isset($result["result"]["value"][0]["myAccountNumber"])) $this->config["myAccountNumber"] = $result["result"]["value"][0]["myAccountNumber"];
			$this->updateConfig("sessionToken", $this->config["access_token"]);
			$this->updateConfig("myAccountID", $result["result"]["value"][0]["myAccountID"]);
			$this->updateConfig("myAccountNumber", $result["result"]["value"][0]["myAccountNumber"]);

			return $result;
		}

	}
	public function setPublicKey()
	{
		$publicKey = file_get_contents(dirname(__FILE__) . "/public_" . $this->config["deviceId"] . ".pem");
		$result = $this->request("POST", "/kkpmobileapi/v1/authenticationSuperappAdapter/ACT_GET_CUST_INFO",  array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"pin" => $this->config["pin"],
			"publicKey" => "$publicKey",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		return $result;
	}
	public function getTransaction($page = 1, $pageSize = 20)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/AccountManagementAdapter/ACT_MY_ACCOUNT_INQUIRY_CASA_STATEMENT", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
			"paging" =>  array(
				"page" => $page,
				"pageSize" => $pageSize,
			),
			"inquiryAccountStatement" =>  array(
				"filterType" => "RIBMobile",
				"myAcctId" => $this->config["myAccountID"],
				"statementDateFrom" => date("d/m/Y"),
				"statementDateTo" =>  date("d/m/Y"),
			),
		));
		//print_r($result);
		if($result['error_response']['responseStatus']['httpStatusCode']!=''){
			if($this->curr_re_try <2){
				//	echo "test";
				$this->LoginPin();
				$this->getTransaction();
				$this->curr_re_try++;
			}else{
				return $result;
			}
		}else{
			return $result;
		}

	}
	public function buildHeaders($array)
	{
		$headers = array();
		foreach ($array as $key => $value) {
			$headers[] = $key . ": " . $value;
		}
		return $headers;
	}
	public function request($method, $endpoint, $headers = array(), $data = null)
	{

		$handle = curl_init();
		if (!is_null($data)) {
			curl_setopt($handle, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
			if (is_array($data)) $headers = array_merge(array("Content-Type" => "application/json"), $headers);
		}
		$headers = array_merge(array("Kk-Application-Version" => $this->version, "Kk-Application-Id" => "com.kiatnakinbank.kkebanking.Android", "Accept-Language" => "th"), $headers);
		curl_setopt_array($handle, array(
			CURLOPT_URL => rtrim($this->api_gateway, "/") . $endpoint,
			CURLOPT_SSL_VERIFYPEER => false ,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => "KKP Mobile/".$this->version." Dalvik/2.1.0 (Linux; U; Android 10; Redmi 8 MIUI/V12.5.3.0.QCNMIXM)",
			CURLOPT_TIMEOUT => 5000,
			CURLOPT_HTTPHEADER => $this->buildHeaders($headers),
		));


		if (is_array($this->curl_options)) curl_setopt_array($handle, $this->curl_options);
		$this->response = curl_exec($handle);
		$this->http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

		if ($result = json_decode($this->response, true)) {
			if (isset($result["data"])) $this->data = $result["data"];
			return $result;
		}
		return $this->response;
	}

	public function verifyTransfer($toAccount, $toBankCode, $amount)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/FundTransferAdapter/ACT_VERIFY_FUND_TRANSFER", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"amount" => number_format(str_replace(",", "", strval($amount)), 2, ".", ""),
			"anyIDType" => "ACCTNO",
			"fromAccount" => $this->config['myAccountID'] . ":" . $this->config['myAccountNumber'],
			"immediateType" => "T",
			"recurringTime" => "0",
			"recurringType" => "N",
			"rtpReferenceNo" => "",
			"scanFlag" => "N",
			"scheduleEndDate" => null,
			"scheduleStartDate" => null,
			"scheduleType" => "0",
			"toAccount" => "-1,-1:$toAccount",
			"toAccountName" => "",
			"toBankCode" => $toBankCode,
			"transferDate" => date("d/m/Y"),
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20221120021326844.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20221120021326",
				"transactionDateTime" => "2022-11-20T02:13:26.458",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		if($result['error_response']['responseStatus']['httpStatusCode']!=''){
			if($this->curr_re_try <2){
				//echo "test";
				$this->LoginPin();
				$this->verifyTransfer($toAccount, $toBankCode, $amount);
				$this->curr_re_try++;
			}else{
				return $result;
			}
		}else{
			if (isset($result["result"]["value"]["accessToken"])) $this->config["access_token"] = $result["result"]["value"]["accessToken"];
			if (isset($result["result"]["value"]["sessionToken"])) $this->config["sessionToken"] = $result["result"]["value"]["sessionToken"];
			//echo date("d/m/Y");
			return $result;
		}


	}
	public function verifyTransferEwallet($ewalletID,$amount)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/FundTransferAdapter/ACT_VERIFY_FUND_TRANSFER", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"amount" => number_format(str_replace(",", "", strval($amount)), 2, ".", ""),
			"anyIDType" => "EWALLETID",
			"fromAccount" => $this->config['myAccountID'] . ":" . $this->config['myAccountNumber'],
			"immediateType" => "T",
			"recurringTime" => "0",
			"recurringType" => "N",
			"rtpReferenceNo" => "",
			"scanFlag" => "N",
			"scheduleEndDate" => null,
			"scheduleStartDate" => null,
			"scheduleType" => "0",
			"toAccount" => "-1,-1:$ewalletID",
			"toAccountName" => "",
			"toBankCode" => "",
			"transferDate" => date("d/m/Y"),
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		if (isset($result["result"]["value"]["accessToken"])) $this->config["access_token"] = $result["result"]["value"]["accessToken"];
		if (isset($result["result"]["value"]["sessionToken"])) $this->config["sessionToken"] = $result["result"]["value"]["sessionToken"];
		return $result;
	}
	public function ConfirmTransfer($resultVeriftyTransaction)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/FundTransferAdapter/ACT_CONFIRM_FUND_TRANSFER", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"memo" => "",
			"otp" => "",
			"pin" => "",
			"referenceNo" => "",
			"tokenID" => "",
			"tokenOTPForCAA" => "",
			"transferTypeCode" => $resultVeriftyTransaction["result"]["value"]["atsTransferDetails"][0]["transferTypeCode"],
			"verifyTransactionId" => $resultVeriftyTransaction["result"]["value"]["verifyTransactionId"],
			"digitalSign" => $this->generateSignature($resultVeriftyTransaction["result"]["value"]["verifyTransactionId"] . $resultVeriftyTransaction["result"]["value"]["fundTransferRequest"]["fromAccount"] . $resultVeriftyTransaction["result"]["value"]["fundTransferRequest"]["toAccount"] . $resultVeriftyTransaction["result"]["value"]["fundTransferRequest"]["amount"] . $resultVeriftyTransaction["result"]["value"]["verifyTransactionId"]),
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));
		if (isset($result["result"]["value"]["accessToken"])) $this->config["access_token"] = $result["result"]["value"]["accessToken"];
		if (isset($result["result"]["value"]["sessionToken"])) $this->config["sessionToken"] = $result["result"]["value"]["sessionToken"];
		return $result;
	}
	public function ScanSlip($qrString)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/BillpaymentAdapter/SCAN_BILLER_INFO", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"codeType" => "Q",
			"qrCode" =>$qrString,
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));

		return $result;
	}
	public function SlipDetail($transRef,$sendingBank)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/BillpaymentAdapter/INQUIRY_QR_VERIFY_TRANSACTION", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"actionType" => "",
			"appVersion" => "",
			"cisID" => "",
			"clientIP" => "",
			"sendingBank" =>$sendingBank,
			"transRef" =>$transRef,
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",

			),
			"tokenID" => "",
			"deviceId" => $this->config["deviceId"],
		));

		return $result;
	}

	public function GENERATE_STATEMENT($accountNumber, $start_month)
	{
		$address = $this->ADDRESS_STATEMENT($accountNumber);
		$result = $this->request("POST", "/kkpmobileapi/v1/StatementAdapter/GENERATE_STATEMENT", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"myAcctNo" => $accountNumber,
			"statementDateFrom" => "01/".$start_month."/2022",
			"statementDateTo" => cal_days_in_month(CAL_GREGORIAN,$start_month,2023)."/".$start_month."/2022",
			"addressLanguageRequest" => "th",
			"address" => array(
				"addrNumber" => $address["result"]["value"]["address"]["addrNumber"],
				"addressStr" => $address["result"]["value"]["address"]["addressStr"],
				"moo" => $address["result"]["value"]["address"]["moo"],
				"floorNumber" => $address["result"]["value"]["address"]["floorNumber"],
				"roomNumber" => $address["result"]["value"]["address"]["roomNumber"],
				"building" => $address["result"]["value"]["address"]["building"],
				"soi" => $address["result"]["value"]["address"]["soi"],
				"road" => $address["result"]["value"]["address"]["road"],
				"subDistrict" => $address["result"]["value"]["address"]["subDistrict"],
				"district" => $address["result"]["value"]["address"]["district"],
				"province" => $address["result"]["value"]["address"]["province"],
				"postalCode" => $address["result"]["value"]["address"]["postalCode"],
				"country" => $address["result"]["value"]["address"]["country"]
			),
		));
		return $result;
	}

	public function ADDRESS_STATEMENT($accountNumber)
	{
		$result = $this->request("POST", "/kkpmobileapi/v1/StatementAdapter/GET_MY_ADDRESS_STATEMENT", array("Authorization" => "Bearer " . $this->config["access_token"]), array(
			"cisID" => "",
			"clientIP" => "",
			"developerMessage" => "",
			"header" =>  array(
				"channelID" => "RIBMobile",
				"referenceNo" => "20220926073523379.0",
				"serviceName" => "KKP_MOBILE",
				"systemCode" => "RIB",
				"transactionDate" => "20220926073523",
				"transactionDateTime" => "2022-09-26T07:35:23.948",
			),
			"language" => strval("en"),
			"platform" => array(
				"deviceModel" => $this->config["device_model"],
				"deviceName" => $this->config["device_model"],
				"deviceToken" => "deviceToken",
				"deviceType" => "Android",
				"deviceUUID" => $this->config["deviceId"],
				"isIllegal" => "FALSE",
				"osName" => "Android",
				"osVersion" => "10",
				"osname" => "Android",
				"osversion" => "10",
			),
			"myAcctNo" => $accountNumber,
		));
		return $result;
	}
}
