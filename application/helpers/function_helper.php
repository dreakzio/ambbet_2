<?php
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;

function check_parameter($data, $request = "")
{
    $CI = & get_instance();
    if ($request=="POST") {
        check_post();
    }
    if ($CI->input->method()=="post") {
        $method = $CI->input->post();
    } else {
        $method = $CI->input->get();
    }
    foreach ($data as $key => $value) {
        if (!isset($method[$value])) {
            echo json_encode([
            'message' => 'request form-data',
            'error' => true
            ]);
            exit();
        }
    }
}
  function check_post()
  {
      $CI = & get_instance();
      if ($CI->input->method()!="post") {
          echo json_encode([
           'message' => 'POST method',
           'error' => true
          ]);
          exit();
      }
  }
   function curl($url, $form_data = [])
   {
       $curl = new Curl();
       $curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);
       $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
       $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
       $curl->setOpt(CURLOPT_HEADER, false);
       $curl->setOpt(CURLOPT_COOKIEJAR, 'ts911session');
       $curl->setOpt(CURLOPT_COOKIEFILE, 'ts911session');
       if (empty($form_data)) {
           $curl->get($url);
       } else {
           // $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
           // $curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36');
           // $curl->setHeader('Viewport-Width', '1366');
           // $curl->setHeader('Accept', '*/*');
           // $curl->setHeader('Sec-Fetch-Site', 'same-origin');
           // $curl->setHeader('Sec-Fetch-Mode', 'cors');
           // $curl->setHeader('Sec-Fetch-Dest', 'empty');
           $curl->post($url, $form_data);
       }
       return $curl->response;
   }
   function getBankNumberFormat($bank_number = ""){
		if(!empty($bank_number) && strlen($bank_number) >= 10){
			return substr($bank_number,0,3)."-".substr($bank_number,3,1)."-".substr($bank_number,4,5)."-".substr($bank_number,9);
		}
		return $bank_number;
   }
function round_up($value, $places)
{
	$mult = pow(10, abs($places));
	return $places < 0 ?
		ceil($value / $mult) * $mult :
		ceil($value * $mult) / $mult;
}
function roleSuperAdmin(){
	return '0';
}
function roleAdmin(){
	return '1';
}
function roleMember(){
	return '2';
}
function canManageRole(){
	return [
		'0' => ['0','1','2'],
		'1' => ['2'],
		'2' => [],
	];
}
function roleDisplay(){
	return [
		'0' => "ผู้ดูแลระบบสูงสุด",
		'1' => "ผู้ดูแลระบบ",
		'2' => "สมาชิก",
	];
}
function month_th_list($type="l")
{
	$month_l = array(
		'01' => 'มกราคม',
		'02' => 'กุมภาพันธ์',
		'03' => 'มีนาคม',
		'04' => 'เมษายน',
		'05' => 'พฤษภาคม',
		'06' => 'มิถุนายน',
		'07' => 'กรกฎาคม',
		'08' => 'สิงหาคม',
		'09' => 'กันยายน',
		'10' => 'ตุลาคม',
		'11' => 'พฤศจิกายน',
		'12' => 'ธันวาคม',
	);
	$month_s = array(
		'01' => 'ม.ค.',
		'02' => 'ก.พ.',
		'03' => 'มี.ค.',
		'04' => 'เม.ย.',
		'05' => 'พ.ค.',
		'06' => 'มิ.ย.',
		'07' => 'ก.ค.',
		'08' => 'ส.ค.',
		'09' => 'ก.ย.',
		'10' => 'ต.ค.',
		'11' => 'พ.ย.',
		'12' => 'ธ.ค.',
	);
	if ($type=="l") {
		return $month_l;
	} else {
		return $month_s;
	}
}

function line_notify_message($type="4",$message,$line_notify_id = null)
{
	/*
		Array ( [headers] => HTTP/1.1 200 Server: nginx Date: Fri, 23 Oct 2020 06:44:22 GMT Content-Type: application/json;charset=UTF-8 Transfer-Encoding: chunked Connection: keep-alive Keep-Alive: timeout=3 X-RateLimit-Limit: 1000 X-RateLimit-ImageLimit: 50 X-RateLimit-Remaining: 988 X-RateLimit-ImageRemaining: 50 X-RateLimit-Reset: 1603437768 [response] => Array ( [status] => 200 [message] => ok ) )
	*/
	$CI = & get_instance();
	if($type == "4"){
		$line_notify_status = $CI->Setting_model->setting_find([
			'name' => 'line_notify_log_api_status'
		]);
	}else{
		$line_notify_status = $CI->Setting_model->setting_find([
			'name' => 'line_notify_status'
		]);
	}
	$message_new = $message;
	if($line_notify_status!="" && $line_notify_status['value'] == "1"){
		if($type == "4"){
			$line_notify_token = $CI->Setting_model->setting_find([
				'name' => 'line_notify_log_api_token'
			]);
		}else{
			$line_notify_token = $CI->Setting_model->setting_find([
				'name' => 'line_notify_token'
			]);
		}
		if($line_notify_token!="" && !empty($line_notify_token['value'])){
			//1 = ฝาก
			//2 = ถอน
			//3 = รายงานสรุปยอดทุก 00.00
			//4 = อื่นๆ & Log API
			//5 = สมัครสมาชิก
			switch ($type){
				case "1" :
					$message_new = "".$message;
					 break;
				case "2" :
					$message_new = "".$message;
					break;
				case "4" :
					$domain = str_replace("www.","",$CI->config->item('domain_name'));
					$domain = str_replace(".com","",$domain);
					$message_new = $CI->config->item('api_prefix_username')." (".$domain.") => ".$message;
					break;
				case "5" :
					$message_new = "".$message;
					break;
				default :
					break;
			}
			$LINE_API = "https://notify-api.line.me/api/notify";
			$headers    = [
				'Content-Type: application/x-www-form-urlencoded',
				'Authorization: Bearer '.trim($line_notify_token['value'])
			];
			$fields    = 'message='.$message_new;  //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
			try{
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $LINE_API);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields);
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt( $ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec( $ch );
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				curl_close( $ch );
				$header = substr($result, 0, $header_size);
				$body = substr($result, $header_size);
				$response = json_decode($body,TRUE);
				if(!is_null($line_notify_id)){
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
						'id' => $line_notify_id,
						'message' => $message_new,
						'response' => json_encode([
							'headers' => $header,
							'response' => $response,
						],JSON_UNESCAPED_UNICODE),
						'type' => $type,
						'status' => 1,
					]);
				}else{
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
						'message' => $message_new,
						'response' => json_encode([
							'headers' => $header,
							'response' => $response,
						],JSON_UNESCAPED_UNICODE),
						'type' => $type,
						'status' => 1,
					]);
				}
				return [
					'headers' => $header,
					'response' => $response,
				];
			}catch (Exception $ex){
				if(!is_null($line_notify_id)){
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
						'id' => $line_notify_id,
						'message' => $message_new,
						'status' => 1,
						'response' =>json_encode( ["status"=>false,"Error : ".$ex->getMessage()],JSON_UNESCAPED_UNICODE),
						'type' => $type
					]);
				}else{
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
						'message' => $message_new,
						'status' => 1,
						'response' =>json_encode( ["status"=>false,"Error : ".$ex->getMessage()],JSON_UNESCAPED_UNICODE),
						'type' => $type
					]);
				}
				return [
					'response' => ["status"=>false,"Error : ".$ex->getMessage()],
				];
			}
		}else{
			if(!is_null($line_notify_id)){
				$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
					'id' => $line_notify_id,
					'message' => $message_new,
					'status' => 1,
					'response' => json_encode(["status"=>false,"Line Notify token : Empty"],JSON_UNESCAPED_UNICODE),
					'type' => $type
				]);
			}else{
				$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
					'message' => $message_new,
					'status' => 1,
					'response' => json_encode(["status"=>false,"Line Notify token : Empty"],JSON_UNESCAPED_UNICODE),
					'type' => $type
				]);
			}
			return [
				'response' => ["status"=>false,"Line Notify token : Empty"],
			];
		}
	}
	if(!is_null($line_notify_id)){
		$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
			'id' => $line_notify_id,
			'message' => $message_new,
			'status' => 1,
			'response' => json_encode(["status"=>false,"Line Notify status : Disabled"],JSON_UNESCAPED_UNICODE),
			'type' => $type
		]);
	}else if(is_null($line_notify_id) && !empty($message_new)){
		$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
			'message' => $message_new,
			'status' => 1,
			'response' => json_encode(["status"=>false,"Line Notify status : Disabled"],JSON_UNESCAPED_UNICODE),
			'type' => $type
		]);
	}
	return [
		'response' => ["status"=>false,"Line Notify status : Disabled"],
	];
}
function line_notify_log_api($message,$line_notify_id = null)
{
	$CI = & get_instance();
	$line_notify_status = $CI->Setting_model->setting_find([
		'name' => 'line_notify_log_api_status'
	]);
	$message_new = $message;
	if($line_notify_status!="" && $line_notify_status['value'] == "1"){
		$line_notify_token = $CI->Setting_model->setting_find([
			'name' => 'line_notify_log_api_token'
		]);
		if($line_notify_token!="" && !empty($line_notify_token['value'])){
			$LINE_API = "https://notify-api.line.me/api/notify";
			$headers    = [
				'Content-Type: application/x-www-form-urlencoded',
				'Authorization: Bearer '.trim($line_notify_token['value'])
			];
			$fields    = 'message='.$message_new;  //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
			try{
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $LINE_API);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields);
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt( $ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec( $ch );
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				curl_close( $ch );
				$header = substr($result, 0, $header_size);
				$body = substr($result, $header_size);
				$response = json_decode($body,TRUE);
				if(!is_null($line_notify_id)){
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
						'id' => $line_notify_id,
						'message' => $message_new,
						'response' => json_encode([
							'headers' => $header,
							'response' => $response,
						],JSON_UNESCAPED_UNICODE),
						'type' => $type,
						'status' => 1,
					]);
				}else{
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
						'message' => $message_new,
						'response' => json_encode([
							'headers' => $header,
							'response' => $response,
						],JSON_UNESCAPED_UNICODE),
						'type' => $type,
						'status' => 1,
					]);
				}
				return [
					'headers' => $header,
					'response' => $response,
				];
			}catch (Exception $ex){
				if(!is_null($line_notify_id)){
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
						'id' => $line_notify_id,
						'message' => $message_new,
						'status' => 1,
						'response' =>json_encode( ["status"=>false,"Error : ".$ex->getMessage()],JSON_UNESCAPED_UNICODE),
						'type' => $type
					]);
				}else{
					$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
						'message' => $message_new,
						'status' => 1,
						'response' =>json_encode( ["status"=>false,"Error : ".$ex->getMessage()],JSON_UNESCAPED_UNICODE),
						'type' => $type
					]);
				}
				return [
					'response' => ["status"=>false,"Error : ".$ex->getMessage()],
				];
			}
		}else{
			if(!is_null($line_notify_id)){
				$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
					'id' => $line_notify_id,
					'message' => $message_new,
					'status' => 1,
					'response' => json_encode(["status"=>false,"Line Notify token : Empty"],JSON_UNESCAPED_UNICODE),
					'type' => $type
				]);
			}else{
				$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
					'message' => $message_new,
					'status' => 1,
					'response' => json_encode(["status"=>false,"Line Notify token : Empty"],JSON_UNESCAPED_UNICODE),
					'type' => $type
				]);
			}
			return [
				'response' => ["status"=>false,"Line Notify token : Empty"],
			];
		}
	}
	if(!is_null($line_notify_id)){
		$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_update([
			'id' => $line_notify_id,
			'message' => $message_new,
			'status' => 1,
			'response' => json_encode(["status"=>false,"Line Notify status : Disabled"],JSON_UNESCAPED_UNICODE),
			'type' => $type
		]);
	}else if(is_null($line_notify_id) && !empty($message_new)){
		$log_line_notify_id = $CI->Log_line_notify_model->log_line_notify_create([
			'message' => $message_new,
			'status' => 1,
			'response' => json_encode(["status"=>false,"Line Notify status : Disabled"],JSON_UNESCAPED_UNICODE),
			'type' => $type
		]);
	}
	return [
		'response' => ["status"=>false,"Line Notify status : Disabled"],
	];
}
function roll( $iChance ) {
	$iChance = round(( ( $iChance > 100 ) ? 100 : (float)$iChance), 0, PHP_ROUND_HALF_UP);
	$iCursor = mt_rand( 0, 99 );
	$aModel = range( 0, 99 );
	shuffle( $aModel );
	return in_array( $iCursor, array_slice( $aModel, 0, $iChance ) ) ;
}

function decrypt($msg_encrypted_bundle, $password){
	$password = sha1($password);

	$components = explode( ':', $msg_encrypted_bundle );
	$iv            = $components[0];
	$salt          = hash('sha256', $password.$components[1]);
	$encrypted_msg = $components[2];

	$decrypted_msg = openssl_decrypt(
		$encrypted_msg, 'aes-256-cbc', $salt, null, $iv
	);

	if ( $decrypted_msg === false )
		return false;

	$msg = substr( $decrypted_msg, 41 );
	return $decrypted_msg;
}
