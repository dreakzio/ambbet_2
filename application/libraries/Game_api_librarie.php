<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;

class Game_api_librarie
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{

	}
	private function initParamsAndUrl(){
		$CI = & get_instance();
		return [
			'params' => [
				'api_key' => $CI->config->item('api_key'),
				'api_hash' => $CI->config->item('api_hash'),
				'api_agent' => $CI->config->item('api_agent'),
				'api_client_name' => $CI->config->item('api_client_name'),
			],
			'url' => $CI->config->item('api_domain'),
			'domain_name' => $CI->config->item('domain_name'),
		];
	}
	public function registerPlayer($memberLoginName,$memberLoginPass ,$phoneNo = '',$contact = '')
	{
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$memberLoginName = $this->uniqidReal(15 - strlen($initParams['params']['api_agent']));
		$params['memberLoginName'] = $memberLoginName;
		$params['memberLoginPass'] = $memberLoginPass;
		$params['phoneNo'] = $phoneNo;
		$params['contact'] = $contact;
		$params['signature'] = md5($memberLoginName.":".$memberLoginPass.":".$initParams['params']['api_agent']); //memberLoginName:memberLoginPass:agent
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/create/'.$initParams['params']['api_key'],$params);
		$response = json_decode($response,true);
		return $response;
	}
	public function balanceCredit($user)
	{
		$initParams = $this->initParamsAndUrl();
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/credit/'.$initParams['params']['api_key'].'/'. $user['account_agent_username']);
		//print_r($response);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result']) && isset($response['result']['credit'])){
			return (float)str_replace(",","",$response['result']['credit']);
		}
		return 0.00;
	}
	public function deposit($data)
	{
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$params['amount'] = $data['amount'];
		$params['signature'] = md5($data['amount'].":".$data['account_agent_username'].":".$initParams['params']['api_agent']); //amount:user:agent
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/deposit/'.$initParams['params']['api_key'].'/'. $data['account_agent_username'],$params);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])){
			return $response['result'];
		}
		return isset($response['message']) ? $response['message'] : null;
	}
	public function withdraw($data)
	{
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$params['amount'] = $data['amount'];
		$params['signature'] = md5($data['amount'].":".$data['account_agent_username'].":".$initParams['params']['api_agent']); //amount:user:agent
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/withdraw/'.$initParams['params']['api_key'].'/'. $data['account_agent_username'],$params);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])){
			return $response['result'];
		}
		return isset($response['message']) ? $response['message'] : null;
	}


	public function getGameList($game)
	{
		/*if($game == "ambgame"){
			$game = "askmebetslot";
		}*/
		$CI = & get_instance();
		if($CI->cache->file->get(base64_encode('game_list_'.strtolower($game))) !== FALSE && !empty($CI->cache->file->get(base64_encode('game_list_'.strtolower($game)),[]))){
			return $CI->cache->file->get(base64_encode('game_list_'.strtolower($game)));
		}else{
			$initParams = $this->initParamsAndUrl();
			$response = $this->curl($initParams['url'].'/v0.1/partner/member/gameList/'.strtolower($game).'/'. $initParams['params']['api_key']);

			$response = json_decode($response,true);
			if(isset($response['status']) && $response['status'] == 0 && isset($response['data']) && isset($response['data']['lists'])){
				$CI->cache->file->save(base64_encode('game_list_'.strtolower($game)),$response['data']['lists'], 60*120);
				return $response['data']['lists'];
			}else{
				return $CI->cache->file->get(base64_encode('game_list_'.strtolower($game)),[]);
			}
		}
		return [];
	}

	public function resetPass($data)
	{
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$params['password'] = $data['account_agent_password'];
		$params['signature'] = md5($data['account_agent_password'].":".$initParams['params']['api_agent']); //password:agent
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/reset-password'.'/'. $initParams['params']['api_key'].'/'.$data['account_agent_username'],$params,"PUT");
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0){
			return true;
		}
		return false;
	}

	public function playGame($game,$data,$game_id = "",$isMobile = null)
	{
		/*if($game == "ambgame"){
			$game = "askmebetslot";
		}*/
		$initParams = $this->initParamsAndUrl();
		//Reset pass
		$chk = $this->resetPass($data);
		$params = [];
		$params['isMobile'] = false;
		$params['username'] = $data['account_agent_username'];
		$params['password'] = $data['account_agent_password'];
		$path = "/v0.1/partner/member/login/";
		if(!empty($game_id)){
			$params['gameId'] = $game_id;
		}
		if(in_array($game,[
			"aggame",
			"live22",
			"ameba",
			"slotxo",
			"lotto",
			"hotgraph",
			"spg",
			"ganapati",
			"pg",
			"ambgame",
			"askmebetslot",
			"ebet",
			"allbet",
			"sexy",
			"sa",
			"dg",
			"pt",
			"bg",
			"betgame",
			"greendragon",
			"pragmatic",
			"keno",
			"atom",
			"number",
			"rng",
			"evoplay",
			"kagaming",
			"allwayspin",
			"iconicgaming",
			"booongo",
			"wazdandirect",
			"funtagaming",
			"funkygame",
			"mannaplay",
			"pragmaticslot",
			"ambslot",
			"jili",
			"simpleplay",
			"microgame",
			"lottoOnline",
			'microgame',
			'yggdrasil',
			'upgslot',
			'p8',
			'slotracha',
			'spinix',
			'advantplay',
			'ninjaslot',
			'ace333',
			'wmslot',
			'wecasino',
			'iampoker',
			'wmcasino',
		])){
			if($game == "aggame"){
				$path .= "track/";
				$params['website'] = "http://".str_replace("www.","",$initParams['domain_name']);
			}
			$params['isMobile'] = true;
		}
		if(!is_null($isMobile)){
			$params['isMobile'] = $isMobile;
		}
		$params['signature'] = md5($data['account_agent_username'].":".$data['account_agent_password'].":".$initParams['params']['api_client_name']); //username:password:clientName

		//Logout ag
		$response_logout = $this->curl($initParams['url'].'/v0.1/partner/member/logout/track/aggame/'. $initParams['params']['api_key'].'/'.trim($data['account_agent_username']),$params);

		if($game == "aggame"){
			$response = $this->curl($initParams['url'].$path.strtolower($game).'/'. $initParams['params']['api_key'].'/'.trim($data['account_agent_username']),$params);
		}else{
			$response = $this->curl($initParams['url'].$path.strtolower($game).'/'. $initParams['params']['api_key'],$params);
		}

		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && ((isset($response['url']) && !empty($response['url'])) || (isset($response['result']) && !empty($response['result'])))){
			if(isset($response['url']) ){
				return $response['url'] ;
			}else if(isset($response['result'])){
				return $response['result'];
			}
		}
		return $response;
	}

	public function getTurn($data)
	{
		$turnover_amount = 0.00;
		$initParams = $this->initParamsAndUrl();
		$CI = & get_instance();
		/*$finance_list = $CI->Finance_model->finance_for_turn_list([
			'start_date' => $data['start_date'],
			'end_date' => $data['end_date'],
			'username' => $data['username'],
		]);*/
		$account = $CI->Account_model->account_find([
			'account_agent_username' => $data['username']
		]);
		$finance_list = [];
		if($account != "" && !empty($account['ref_transaction_id'])){
			$finance_list[] = [
				"ref_transaction_id" => $account['ref_transaction_id']
			];
		}
		foreach($finance_list as $finance){
			$response = $this->curl($initParams['url'].'/v0.1/partner/member/winLose/'.$initParams['params']['api_key'].'/'. $data['account_agent_username'].'/'.$finance['ref_transaction_id']);
			$response = json_decode($response,true);
			if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])&& isset($response['result']['data'])){
				$data_res = [];
				foreach(game_code_list() as $game_code){
					if(array_key_exists($game_code,$response['result']['data'])){
						$data_res[$game_code] = $response['result']['data'][$game_code];
					}
				}
				return $data_res;
				foreach($response['result']['data'] as $key => $value){
					if((float)$value['amount'] >= $turnover_amount ){
						$turnover_amount = (float)$value['amount'];
					}
				}
			}
		}
		//return (float)str_replace(",","",$turnover_amount);
		return [];
	}

	public function getYesterdayWinLose($data)
	{
		$wl_amount = 0.00;
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$params['username'] = $data['account_agent_username'];
		$params['signature'] = md5($initParams['params']['api_key'].":".$initParams['params']['api_client_name']); //key:clientName
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/yesterdayWinLose/'.$initParams['params']['api_key'],$params);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])&& isset($response['result']['data'])){
			foreach($response['result']['data'] as $key => $value){
				$wl_amount += (float)$value['wlAmount'];
			}
		}
		return (float)str_replace(",","",$wl_amount);
	}

	public function getYesterdayTurnover($data)
	{
		$turnover_amount = 0.00;
		$initParams = $this->initParamsAndUrl();
		$params = [];
		$params['username'] = $data['account_agent_username'];
		$params['signature'] = md5($initParams['params']['api_key'].":".$initParams['params']['api_client_name']); //key:clientName
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/yesterdayWinLose/'.$initParams['params']['api_key'],$params);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])&& isset($response['result']['data'])){
			foreach($response['result']['data'] as $key => $value){
				$turnover_amount += (float)$value['amount'];
			}
		}
		return (float)str_replace(",","",$turnover_amount);
	}

	public function getYesterdayTurnoverAll()
	{
		$initParams = $this->initParamsAndUrl();
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/yesterdayTurnOver/findAll/'.$initParams['params']['api_key']);
		$response = json_decode($response,true);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result'])){
			return empty($response['result']) ? [] : $response['result'];
		}
		return null;
	}

	public function getYesterdayTurnoverAllTest()
	{
		$initParams = $this->initParamsAndUrl();
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/yesterdayTurnOver/findAll/'.$initParams['params']['api_key']);
		$response = json_decode($response,true);
		return $response;
	}

	private function curl($url, $form_data = [],$method = "")
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if (empty($form_data)) {
			//curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		} else {
			if(!empty($method) && $method != "GET" && $method != "POST"){
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			}else{
				curl_setopt($ch, CURLOPT_POST, 1);
			}
			$postdata = json_encode($form_data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}
		$response= curl_exec($ch);
		// Check if any error occurred
		if(curl_errno($ch))
		{
			$response_info = curl_getinfo($ch);
			$data_text = curl_error($ch);
			$http_code  = 200;
			$curl_errno= curl_errno($ch);
			if(isset($response_info['http_code'])){
				$http_code = $response_info['http_code'];
			}
			curl_close($ch);
			return "Curl Error call api [code:".$http_code.",curlno:".$curl_errno."] ".$data_text;
		}
		curl_close($ch);
		return $response;
	}
	private function uniqidReal($lenght) {
		// uniqid gives 13 chars, but you could adjust it to your needs.
		if (function_exists("random_bytes")) {
			$bytes = random_bytes(ceil($lenght / 2));
		} elseif (function_exists("openssl_random_pseudo_bytes")) {
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
		} else {
			throw new Exception("no cryptographically secure random function available");
		}
		return strtoupper(substr(bin2hex($bytes), 0, $lenght));
	}

	public function GameStatus()
	{
		$initParams = $this->initParamsAndUrl();
		$response = $this->curl($initParams['url'].'/v0.1/partner/member/maintenanceTime/'.$initParams['params']['api_key']);
		$response = json_decode($response,true);
		return $response;
		
	}


}
