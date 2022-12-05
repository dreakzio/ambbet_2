<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

class Linelogin_librarie {

	// CHANGEME: Default Line Developer ClientID and ClientSecret
	private static $CLIENT_ID = "XXXXX";
	private static $CLIENT_SECRET = "XXXXX";

	// CHANGEME: Default Callback redirect link
	private static $REDIRECT_URL = "XXXXX";

	private static $CLIENT_IP_ADDRESS = "XXXXX";

	// CHANGEME: Default value for CURLOPT_SSL_VERIFYHOST
	private const VERIFYHOST = false;

	// CHANGEME: Default value for CURLOPT_SSL_VERIFYPEER
	private const VERIFYPEER = false;

	// API DEFAULTS
	private const AUTH_URL = 'https://access.line.me/oauth2/v2.1/authorize';
	private const PROFILE_URL = 'https://api.line.me/v2/profile';
	private const REVOKE_URL = 'https://api.line.me/oauth2/v2.1/revoke';
	private const TOKEN_URL = 'https://api.line.me/oauth2/v2.1/token';
	private const VERIFYTOKEN_URL = 'https://api.line.me/oauth2/v2.1/verify';

	public function __construct()
	{

	}

	private function init(){
		$CI = & get_instance();
		$CI->load->helper('url');
		$line_login_client_id = $CI->Setting_model->setting_find([
			'name' => 'line_login_client_id'
		]);
		if($line_login_client_id != ""){
			self::$CLIENT_ID = trim($line_login_client_id['value']);
		}
		$line_login_client_secret = $CI->Setting_model->setting_find([
			'name' => 'line_login_client_secret'
		]);
		if($line_login_client_secret != ""){
			self::$CLIENT_SECRET = trim($line_login_client_secret['value']);
		}
		$line_login_callback = $CI->Setting_model->setting_find([
			'name' => 'line_login_callback'
		]);
		if($line_login_callback != ""){
			self::$REDIRECT_URL = trim($line_login_callback['value']);
		}
		self::$CLIENT_IP_ADDRESS = $CI->input->ip_address();
	}

	/*
	 *   function getLink
	 *
	 *   Args:
	 *      $scope (int) - Scope integer should equal the sum of the corresponding
	 *                     value for each of the scopes preset:
	 *
	 *                     open_id = 1
	 *                     profile = 2
	 *                     email   = 4
	 *
	 *                     (Example): If your application needs access to open_id,
	 *                                profile and email the value would be "7"
	 *
	 *   Returns:
	 *      $link - Returns generated link for Line Login/Register.
	 */
	function getLink($scope,$aff='') {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		$this->init();
		$_SESSION['line_login_state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR'].self::$CLIENT_IP_ADDRESS);
		$nonce = [];
		if(!empty($aff)){
			$nonce['aff'] = $aff;
		}
		$nonce = base64_encode(json_encode($nonce));
		$link = self::AUTH_URL . '?response_type=code&client_id=' . self::$CLIENT_ID . '&redirect_uri=' . self::$REDIRECT_URL . '&scope=' . $this->scope($scope) .'&state=' . $_SESSION['line_login_state'].'&nonce='.$nonce;
		return $link;
	}

	/*
	 *   function refresh
	 *
	 *   Args:
	 *      $token - User access token.
	 *
	 *   Returns:
	 *      $response (array) - Returns response array in json format.
	 */
	function refresh($token) {
		$this->init();
		$header = ['Content-Type: application/x-www-form-urlencoded'];
		$data = [
			"grant_type" => "refresh_token",
			"refresh_token" => $token,
			"client_id" => self::$CLIENT_ID,
			"client_secret" => self::$CLIENT_SECRET
		];

		$response = $this->sendCURL(self::TOKEN_URL, $header, 'POST', $data);
		return $response;
	}

	/*
	 *   function token
	 *
	 *   Args:
	 *      $code  (GET) - User authorization code.
	 *      $state (GET) - Randomized hash
	 *
	 *   Returns:
	 *      $response (array) - Returns response array in json format.
	 */
	function token($code, $state) {
		$this->init();
		if ($_SESSION['line_login_state'] != $state) {
			return false;
		}

		$header = ['Content-Type: application/x-www-form-urlencoded'];
		$data = [
			"grant_type" => "authorization_code",
			"code" => $code,
			"redirect_uri" => self::$REDIRECT_URL,
			"client_id" => self::$CLIENT_ID,
			"client_secret" => self::$CLIENT_SECRET
		];

		$response = $this->sendCURL(self::TOKEN_URL, $header, 'POST', $data);
		return $response;
	}

	/*
	 *   function profile
	 *
	 *   Args:
	 *      $token - User access token.
	 *
	 *   Returns:
	 *      $response (array) - Returns response array in json format.
	 */
	function profile($token) {
		$header = ['Authorization: Bearer ' . $token];
		$response = $this->sendCURL(self::PROFILE_URL, $header, 'GET');
		return $response;
	}

	/*
	 *   function verify
	 *
	 *   Args:
	 *      $token - User access token.
	 *
	 *   Returns:
	 *      $response (array) - Returns response array in json format.
	 */
	function verify($token) {
		$url = self::VERIFYTOKEN_URL . '?access_token=' . $token;
		$response = $this->sendCURL($url, NULL, 'GET');
		return $response;
	}

	private function scope($scope) {
		$list = ['profile', 'profile%20openid', 'profile%20openid%20email','openid','openid%20email	'];
		return $list[$scope];
	}

	/*
	 *   private function sendCURL
	 *
	 *   Args:
	 *      $url      (const) - Request URL.
	 *      $header   (array) - Headers used for this request.
	 *      $type     (char)  - Request type {POST|GET}.
	 *      $data     (array) - Request data (Can be NULL if sending a GET request).
	 *
	 *   Returns:
	 *      $response (array) - Returns response array in json format.
	 */
	private function sendCURL($url, $header, $type, $data=NULL) {
		$request = curl_init();

		if ($header != NULL) {
			curl_setopt($request, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($request, CURLOPT_URL, $url);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, self::VERIFYHOST);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, self::VERIFYPEER);

		if (strtoupper($type) === 'POST') {
			curl_setopt($request, CURLOPT_POST, TRUE);
			curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data));
		}

		curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($request);
		curl_close( $request );
		return $response;
	}
}
