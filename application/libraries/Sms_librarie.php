<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Sms_librarie
{
    public $api_url   = 'http://www.thsms.com/api/rest';
    public $username  = 'soodkhet';
    public $password  = '1qaz+2wsx';

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
	}


	public function get_credit()
	{
		$params['method']   = 'credit';
		$params['username'] = $this->username;
		$params['password'] = $this->password;

		$result = $this->curl($params);
		$xml = @simplexml_load_string($result);
		if (!is_object($xml)) {
			return array( false, 'Respond error');
		} else {
			if ($xml->credit->status == 'success') {
				return $xml->credit->amount;
			} else {
				return array( false, $xml->credit->message);
			}
		}
	}

	public function send($from='0000', $to=null, $message=null)
	{
		$CI = & get_instance();
		$sms_api_username = $CI->Setting_model->setting_find([
			'name' => 'sms_api_username'
		]);
		$sms_api_password = $CI->Setting_model->setting_find([
			'name' => 'sms_api_password'
		]);
		$sms_api_sender = $CI->Setting_model->setting_find([
			'name' => 'sms_api_sender'
		]);
		if($sms_api_username == ""){
			return array( 'success' => false,"response"=>"ไม่มีการตั้งค่า SMS API Username");
		}else if($sms_api_password == ""){
			return array( 'success' => false,"response"=>"ไม่มีการตั้งค่า SMS API Password");
		}else if($sms_api_username != "" && empty(trim($sms_api_username['value']))){
			return array( 'success' => false,"response"=>"SMS API Username ไม่ควรเป็นค่าว่าง");
		}else if($sms_api_password != "" && empty(trim($sms_api_password['value']))){
			return array( 'success' => false,"response"=>"SMS API Password ไม่ควรเป็นค่าว่าง");
		}
		$from = $sms_api_sender != "" && !empty($sms_api_sender['value']) ? trim($sms_api_sender['value']):  "OTP";
		$this->username = trim($sms_api_username['value']);
		$this->password = trim($sms_api_password['value']);
		$number_otp = random_string('numeric', 6);
		$params['method']   = 'send';
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['from']     = $from;
		$params['to']       = $to;
		$params['message']  = 'OTP ของคุณคือ '.$number_otp;
		if (is_null($params['to']) || is_null($params['message'])) {
			return array( 'success' => false,"response"=>"Empty params to & message");
		}
		$_SESSION['register']['otp'] = $number_otp;
		$result = $this->curl($params);
		$xml = @simplexml_load_string($result);
		if (!is_object($xml)) {
			return array( 'success' => false,"response"=>"Respond error");
			//return array( false, 'Respond error');
		} else {
			if ($xml->send->status == 'success') {

				$_SESSION['register']['phone'] = $to;
				return array( 'success' => true,'otp'=>$number_otp,"response"=>$xml->send->uuid);
				//return true;
				// return array( true, $xml->send->uuid);
			} else {
				return array( 'success' => false,'otp'=>$number_otp,"response"=>$xml->send->message);
				//return false;
				// return array( false, $xml->send->message);
			}
		}
	}

	private function curl($params=array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response  = curl_exec($ch);
		$lastError = curl_error($ch);
		$lastReq = curl_getinfo($ch);
		curl_close($ch);

		return $response;
	}
}
