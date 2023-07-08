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
		$curl->post($url, $form_data);
	}
	return $curl->response;
}
function month_thai($month, $style="l")
{
	// $strMonthCut = array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
	// $strMonthCut = array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");

	switch ($month) {
		case '01':
			if ($style=="l") {
				return 'มกราคม';
			} else {
				return 'ม.ค.';
			}
			break;
		case '02':
			if ($style=="l") {
				return 'กุมภาพันธ์';
			} else {
				return 'ก.พ.';
			}
			break;
		case '03':
			if ($style=="l") {
				return 'มีนาคม';
			} else {
				return 'มี.ค.';
			}
			break;
		case '04':
			if ($style=="l") {
				return 'เมษายน';
			} else {
				return 'เม.ย.';
			}
			break;
		case '05':
			if ($style=="l") {
				return 'พฤษภาคม';
			} else {
				return 'พ.ค.';
			}
			break;
		case '06':
			if ($style=="l") {
				return 'มิถุนายน';
			} else {
				return 'มิ.ย.';
			}
			break;
		case '07':
			if ($style=="l") {
				return 'กรกฎาคม';
			} else {
				return 'ก.ค.';
			}
			break;
		case '08':
			if ($style=="l") {
				return 'สิงหาคม';
			} else {
				return 'ส.ค.';
			}
			break;
		case '09':
			if ($style=="l") {
				return 'กันยายน';
			} else {
				return 'ก.ย.';
			}
			break;
		case '10':
			if ($style=="l") {
				return 'ตุลาคม';
			} else {
				return 'ต.ค.';
			}
			break;
		case '11':
			if ($style=="l") {
				return 'พฤศจิกายน';
			} else {
				return 'พ.ย.';
			}
			break;
		case '12':
			if ($style=="l") {
				return 'ธันวาคม';
			} else {
				return 'ธ.ค.';
			}
			break;
		default:
			break;
	}
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
	$CI = & get_instance();
	$CI->load->library(['Permission_role_service']);
	return $CI->permission_role_service->can_manage_role();
}

function roleDisplay(){
	$CI = & get_instance();
	$CI->load->library(['Permission_role_service']);
	return $CI->permission_role_service->role_display();
}
function encrypt($data, $password){
	$iv = substr(sha1(mt_rand()), 0, 16);
	$password = sha1($password);

	$salt = sha1(mt_rand());
	$saltWithPassword = hash('sha256', $password.$salt);

	$encrypted = openssl_encrypt(
		"$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
	);
	$msg_encrypted_bundle = "$iv:$salt:$encrypted";
	return $msg_encrypted_bundle;
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

function get_data_report_all_day(){
	date_default_timezone_set('Asia/Bangkok');
	$CI = & get_instance();
	$dashboard_report = $CI->cache->file->get(base64_encode('dashboard_report'));
	if($dashboard_report !== FALSE){
		return $dashboard_report;
	}
	//ยอดฝากวันนี้
	$days = date("Y-m-d");
	$report_current_date_list = $CI->Report_business_benefit_model->report_business_benefit_report_all_day_month_group_by(['created_at'=>date("Y-m-d"),'type'=>'current_date' ]);
	$deposit = count($report_current_date_list) >= 1 ? $report_current_date_list[0]['sum_deposit'] : 0.00 ;
	//ยอดฝากวันนี้
	$days = date("Y-m-d");
	//$withdraw_data = $CI->Finance_model->finance_report_all_day(['type'=>2,'created_at'=>$days,'status_list'=>["1","3"]]);
	$withdraw =  count($report_current_date_list) >= 1 ? $report_current_date_list[0]['sum_withdraw'] : 0.00 ;
	//กำไรสุทธิ์วันนี้
	$total = count($report_current_date_list) >= 1 ? $report_current_date_list[0]['sum_total'] : 0.00 ;
	//สมาชิกวันนี้
	$member_data = $CI->Account_model->account_report_all_day(['created_at'=>$days]);
	$member = $member_data[0]['sum_account'] ;
	//รายการฝากวันนี้
	$deposit_count = count($report_current_date_list) >= 1 ? $report_current_date_list[0]['sum_deposit_cnt'] : 0;
	//รายการถอนวันนี้
	$withdraw_count = count($report_current_date_list) >= 1 ? $report_current_date_list[0]['sum_withdraw_cnt'] : 0;
	//สมาชิกทั้งหมด
	$member_data = $CI->Account_model->account_report_all_day();
	$member_total = $member_data[0]['sum_account'] ;

	
	$online_member_total = $CI->Account_model->get_total_online_user();
	$amount_deposit_auto = $CI->Account_model->get_amount_deposit_auto();
	$my_bo = $CI->Use_promotion_model->point_bonus_all();

	$data = [
		'deposit' => $deposit,
		'withdraw' => $withdraw,
		'total' => $total,
		'member' => $member,
		'deposit_count' => $deposit_count,
		'withdraw_count' => $withdraw_count,
		'member_total' =>$member_total,
		'online_member_total' =>$online_member_total,
		'amount_deposit_auto' =>$amount_deposit_auto['amount_deposit_auto'],
		'my_bo' => $my_bo[0]['total_bonus']
	];
	$CI->cache->file->save(base64_encode('dashboard_report'),$data, 15);
	return $data;
}

