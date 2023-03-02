<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Sms_librarie
{
    public $api_url   = 'http://www.thsms.com/api/rest';
    // public $username  = "";
    // public $password  = "";

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
	}

    public function index()
	{

	}
	
	public function get_credit()
	{
		$CI = & get_instance();
		$sms_api_username = $CI->Setting_model->setting_find([
			'name' => 'sms_api_username'
		]);
		$sms_api_password = $CI->Setting_model->setting_find([
			'name' => 'sms_api_password'
		]);
        // return $sms_api_username['value'];
		if($sms_api_username == "" || $sms_api_username == NULL ){
			return array( 'success' => false,"response"=>"ไม่มีการตั้งค่า SMS API Username");
		}else if($sms_api_password == "" || $sms_api_password == NULL){
			return array( 'success' => false,"response"=>"ไม่มีการตั้งค่า SMS API Password");
		}else if($sms_api_username != "" && empty(trim($sms_api_username['value']))){
			return array( 'success' => false,"response"=>"SMS API Username ไม่ควรเป็นค่าว่าง");
		}else if($sms_api_password != "" && empty(trim($sms_api_password['value']))){
			return array( 'success' => false,"response"=>"SMS API Password ไม่ควรเป็นค่าว่าง");
		}
		$this->username = trim($sms_api_username['value']);
		$this->password = trim($sms_api_password['value']);
		
		$params['username'] = $this->username;
		$params['password'] = $this->password;
        $params['method']   = 'credit';
        
        // return $params['username'];
		$result = $this->curl($params);
		$xml = @simplexml_load_string($result);
		$json = json_encode($xml);
		$array = json_decode($json,true);
        return $array;
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
