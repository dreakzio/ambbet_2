<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;
class Auth extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		// if (isset($_SESSION['language'])) {
		// 	$this->session->set_userdata('language', 'thailand');
		// }
		$language = $this->session->userdata('language');
		$this->lang->load('text', $language);
		$this->updateCm();
	}
	public function index()
	{
		if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
			$user = $this->Account_model->account_find([
				'id' => $_SESSION['user']['id']
			]);
			if($user['deleted'] == "0"){
				redirect('dashboard');
			}else{
				session_destroy();
			}
		}else{
			if(!isset($_SESSION['register_data']) && !isset($_SESSION['line_login_chk'])){
				session_destroy();
			}else if(isset($_GET['force']) && $_GET['force']){
				session_destroy();
			}
		}
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'auth/login';
		$this->load->view('main', $data);
	}
	public function register()
	{
		if (isset($_SESSION['user'])) {
			redirect('dashboard');
		}
		if(isset($_GET['ref'])){
			if(isset($_SESSION['register_data'])){
				$_SESSION['register_data']['ref'] = $_GET['ref'];
			}else{
				$_SESSION['register_data'] = array(
					'ref' => $_GET['ref']
				);
			}
		}
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['back_btn'] = true;
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'auth/register';
		$this->lang->load('register', $this->session->get_userdata('language'));
		$data['page_title'] = $this->lang->line('page_title');
		$this->load->view('main', $data);
	}
	public function login()
	{
		check_parameter([
			'username',
			'password'
		], 'POST');
		$post = $this->input->post();
		$line_login_status = $this->Setting_model->setting_find([
			'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		$account = $this->Account_model->account_find([
			'username' => $post['username'],
			'password' => $post['password'],
		]);
		if ($account!="") {
			if($line_login_status == "1" && isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk'] && isset($_SESSION['line_login_user_id']) && !empty($_SESSION['line_login_user_id'])){
				$this->Account_model->account_linebot_userid_update([
					'linebot_userid' => $_SESSION['line_login_user_id'],
					'linebot_userid_empty' => '',
				]);
				$this->Account_model->account_update([
					'id' => $account['id'],
					'linebot_userid' => $_SESSION['line_login_user_id'],
				]);
			}

			$_SESSION['user'] = [
				'role' => $account['role'],
				'id' => $account['id'],
				'username' => $account['username'],
				'bank' => $account['bank'],
				'bank_number' => $account['bank_number'],
				'bank_name' => $account['bank_name'],
				'agent' => $account['agent'],
				'rank' => $account['rank'],
				'member_username' => $account['account_agent_username'],
				'member_password' => $account['account_agent_password'],
				'account_agent_id' => $account['account_agent_id']
			];

			$this->Account_model->account_update([
				'id' => $account['id'],
				'last_activity' => date('Y-m-d H:i:s'),
			]);

			if(isset($post['redirect'])){
				redirect('dashboard');
				exit();
			}
			header('Content-Type: application/json');
			echo json_encode([
				'message' => 'success',
				'result' => true
			]);
		} else {

			if(isset($post['redirect'])) {
				$data['header_menu'] = 'header_menu';
				$data['middle_bar'] = 'middle_bar';
				$data['footer_menu'] = 'footer_menu';
				$url = $this->config->item('domain_name');
				$url_explode = explode(".",$url);
				if(count($url_explode) >= 3 && strpos($url_explode[0],'www') === FALSE){
					$data['redirect_domain'] = count($url_explode) >= 3 ? "https://".$url_explode[1].".".$url_explode[2] :  "https://".$url_explode[0].".".$url_explode[1];
				}
				$data['error_message'] = 'เบอร์มือถือ หรือ พาสเวิร์ดไม่ถูกต้อง';
				$data['page'] = 'auth/login';
				$this->load->view('main', $data);
			}else{
				header('Content-Type: application/json');
				echo json_encode([
					'message' => 'เบอร์มือถือ หรือ พาสเวิร์ดไม่ถูกต้อง',
					'error' => false
				]);
			}
		}
	}
	public function logout()
	{
		session_destroy();
		$url = $this->config->item('domain_name');
		$url_explode = explode(".",$url);
		if(count($url_explode) >= 3 && strpos($url_explode[0],'www') !== FALSE){
			redirect('auth');
		}else if(count($url_explode) >= 3 && strpos($url_explode[0],'www') === FALSE){
			redirect("https://".$url_explode[1].".".$url_explode[2]);
		}else{
			redirect('auth');
		}
	}
	public function send_otp()
	{
		check_parameter([
			'phone'
		], 'POST');
		$post = $this->input->post();
		$post['phone'] = trim($post['phone']);
		$post['phone']  = preg_replace('/[^0-9]/','',trim($post['phone']));
		$register_verify_otp_status = $this->Setting_model->setting_find([
			'name' => 'register_verify_otp_status'
		]);
		if(
			($register_verify_otp_status!="" && $register_verify_otp_status['value'] == "0")
		){
			$_SESSION['register_data'] = array(
				'phone' => $post['phone']
			);
			$_SESSION['register_step'] = 3;
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'step' => 3,
				//'otp_log_test'=>$send['otp']
			]);
			exit();
		}
		if(
			empty($post['phone'])
		){
			echo json_encode([
				'message' => 'เบอร์โทรไม่ควรเป็นค่าว่าง',
				'error' => true
			]);
			exit();
		}else if(!is_numeric($post['phone'])){
			echo json_encode([
				'message' => 'เบอร์โทรควรเป็นตัวเลขทั้งหมด เช่น 0899999999',
				'error' => true
			]);
			exit();
		}else if(strlen($post['phone']) < 10){
			echo json_encode([
				'message' => 'เบอร์โทรควรมีมากกว่าหรือเท่ากับ 10 ตัว',
				'error' => true
			]);
			exit();
		}else if(substr($post['phone'],0,1) != "0"){
			echo json_encode([
				'message' => 'รูปแบบเบอร์โทรไม่ถูกต้อง',
				'error' => true
			]);
			exit();
		}
		$user = $this->Account_model->account_find([
			'username' => $post['phone']
		]);
		if ($user!="") {
			echo json_encode([
				'message' => 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว',
				'error' => true
			]);
			exit();
		}

		$date = date("Y-m-d H:i:s");
		$time = strtotime($date);
		$time = $time - (5 * 60);
		$date_limit = date("Y-m-d H:i:s", $time);
		$count_sms_log = $this->Log_sms_model->log_sms_count([
			'created_at_limit' => $date_limit,
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
		]);

		if($count_sms_log >= 5){
			echo json_encode([
				'message' => 'ท่านทำการขอ OTP มากเกินกำหนด, กรุณารอ 10 นาทีแล้วทำรายการใหม่',
				'error' => true
			]);
			exit();
		}

		$_SESSION['register_data'] = array(
			'phone' => $post['phone']
		);

		$send = $this->sms_librarie->send($from='0000', $post['phone']);
		if ($send['success']) {

			$_SESSION['register_step'] = 2;
			$id = $this->Log_sms_model->log_sms_create([
				'phone' => $post['phone'],
				'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
				'otp' => $send['otp'],
				'response' => json_encode($send,JSON_UNESCAPED_UNICODE),
				//'otp_log_test'=>$send['otp']
			]);

			echo json_encode([
				'message' => 'success',
				'result' => true,
				'step' => 2,
				//'otp_log_test'=>$send['otp']
			]);
		} else {
			$_SESSION['register_step'] = 2;
			$id = $this->Log_sms_model->log_sms_create([
				'phone' => $post['phone'],
				'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
				'otp' => isset($send['otp']) ? $send['otp'] : "-",
				'response' => json_encode($send,JSON_UNESCAPED_UNICODE),
				//'otp_log_test'=>$send['otp']
			]);
			echo json_encode([
				'message' => 'success',
				'response' => 'ส่ง OTP ไม่สำเร็จ',
				'result' => true,
				'step' => 2,
				'data'=> json_encode($send,JSON_UNESCAPED_UNICODE),
				//'otp_log_test'=>$send['otp']

			]);
			/*echo json_encode([
				'message' => 'ส่ง OTP ไม่สำเร็จ',
				'error' => true
			]);*/
		}
	}
	public function check_otp()
	{
		$register_verify_otp_status = $this->Setting_model->setting_find([
			'name' => 'register_verify_otp_status'
		]);
		if(
			($register_verify_otp_status!="" && $register_verify_otp_status['value'] == "0")
		){
			exit();
		}
		check_parameter([
			'otp'
		], 'POST');
		$post = $this->input->post();
		if (isset($_SESSION['register']['otp'])&&$post['otp'] == $_SESSION['register']['otp']) {
			// if ($post['otp'] == '123456') {
			$_SESSION['register_step'] = 3;
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'step' => 3,
				//'otp_log_test'=>$_SESSION['register']['otp'].' : '.$post['otp']
			]);
		} else {
			echo json_encode([
				'message' => 'OTP ไม่ถูกต้อง',
				'error' => true,
				//'otp_log_test'=>$_SESSION['register']['otp'].' : '.$post['otp']
			]);
		}
	}
	public function line_link(){
		if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
			$user = $this->Account_model->account_find([
				'id' => $_SESSION['user']['id']
			]);
			if($user['deleted'] == "0"){
				redirect('dashboard');
			}else{
				session_destroy();
			}
			exit();
		}
		$get = $this->input->get();
		$line_login_status = $this->Setting_model->setting_find([
			'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		if($line_login_status == "0"){
			redirect('auth');
			exit();
		}
		$url = $this->linelogin_librarie->getLink(1,isset($get['ref']) && !empty($get['ref']) ? $get['ref'] : ''); // ขอ permission สำหรับเข้าถึง profile, email
		redirect($url);
		exit();
	}
	public function line_login_cbx(){
		if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
			$user = $this->Account_model->account_find([
				'id' => $_SESSION['user']['id']
			]);
			if($user['deleted'] == "0"){
				redirect('dashboard');
			}else{
				session_destroy();
			}
			exit();
		}
		$line_login_status = $this->Setting_model->setting_find([
			'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		if($line_login_status == "0"){
			redirect('auth');
			exit();
		}
		$get = $this->input->get(); // รับ json payload
		$code = $get['code'];
		$state = $get['state'];
		$token = $this->linelogin_librarie->token($code,$state); // curl เพื่อขอ id_token
		$token = json_decode($token,true);
		if(array_key_exists('id_token',$token)){
			$userData = explode(".",$token['id_token']);
			list($alg,$data) = array_map('base64_decode',$userData);
			$token['alg'] = $alg;
			$token['user'] = json_decode($data,true);
		}
		if(isset($token['user']) && isset($token['user']['sub']) && !empty($token['user']['sub'])){
			if(isset($token['user']['nonce'])){
				$token['user']['nonce'] = json_decode(base64_decode($token['user']['nonce']),true);
			}
			$account_line_id_cnt = $this->Account_model->account_linebot_userid_count(['linebot_userid'=>$token['user']['sub']]);

			//มี line_id ซ้ำกันเกิด 2 ยูสเลยต้อง disabled ทั้งหมด
			if($account_line_id_cnt >= 2){
				$this->Account_model->account_linebot_userid_update(['linebot_userid'=>$token['user']['sub'],'deleted'=>'1']);
				$_SESSION['line_login_error_msg'] = "เกิดข้อผิดพลาด, กรุณาติดต่อแอดมิน [Code : 2]";
				redirect('auth');
				exit();
			}
			//เจอ 1 ยูสเช็คว่าถูก deleted=1 หรือเปล่า
			else if($account_line_id_cnt == 1){

				$account = $this->Account_model->account_find([
					'linebot_userid' => $token['user']['sub'],
					'deleted_ignore' => true,
				]);
				if ($account!="") {
					if($account['deleted'] == "1"){
						$_SESSION['line_login_error_msg'] = "เกิดข้อผิดพลาด, กรุณาติดต่อแอดมิน [Code : 4]";
						redirect('auth');
						exit();
					}
					$_SESSION['user'] = [
						'role' => $account['role'],
						'id' => $account['id'],
						'username' => $account['username'],
						'bank' => $account['bank'],
						'bank_number' => $account['bank_number'],
						'bank_name' => $account['bank_name'],
						'agent' => $account['agent'],
						'rank' => $account['rank'],
						'member_username' => $account['account_agent_username'],
						'member_password' => $account['account_agent_password'],
						'account_agent_id' => $account['account_agent_id']
					];
					redirect('dashboard');
					exit();
				} else {
					$_SESSION['line_login_error_msg'] = "เกิดข้อผิดพลาด, กรุณาติดต่อแอดมิน [Code : 3]";
					redirect('auth');
					exit();
				}
			}
			//ไป flow สมัครสมาชิกด้วย line / ผูกกับยูสเก่า
			else{
				$_SESSION['line_login_chk'] = true;
				$_SESSION['line_login_user_id'] = $token['user']['sub'];
				if(isset($token['user']['nonce']['aff'])){
					$_SESSION['line_login_aff'] = $token['user']['nonce']['aff'];
					//redirect('auth/register?ref='.$_SESSION['line_login_aff']);
					//exit();
				}
				redirect('auth');
				exit();
			}
		}else{
			$_SESSION['line_login_error_msg'] = "เกิดข้อผิดพลาด, กรุณาลองใหม่อีกครั้ง [Code : 1]";
			redirect('auth');
			exit();
		}
	}
	public function ChangeLanguage()
	{
		// Point header
		header('Content-Type: application/json');

		// Check params
        // check_parameter([
        // 'lao',
		// 'english'
        // ], 'POST');

		// Declare post
        $post = $this->input->post();

		// Handle Code
		if (in_array("thailand", $post, true)) {
			$this->session->set_userdata('language', 'thailand');
			// $thailand = $this->session->userdata('thailand');
			// echo "thailand was founded".$language ;
		}
		if (in_array("lao", $post, true)) {
			$this->session->set_userdata('language', 'lao');
			// $lao = $this->session->userdata('lao');
			// echo "lao was founded".$language ;
		}
		if (in_array("english", $post, true)) {
			$this->session->set_userdata('language', 'english');
			// $english = $this->session->userdata('language');
			// echo "english was founded".$language ;
		}

		// Display language Session
		$language = $this->session->userdata('language');

		echo json_encode([
			'myLanguage' => $language,
            'message' => "Change Done !!!",
            'result' => true,
            ]);

	}
	public function ClearSession()
	{
		session_destroy();
		echo json_encode([
			"response" => "ClearCache Done !!!!"
		]);
	}

	public function updateCm(){

		$strFileName = "update.txt";

		if(!file_exists($strFileName)){
			$objFopen = fopen($strFileName, 'w');
			fwrite($objFopen, 1);

			$this->addColumn('account_agent','username_after'," VARCHAR(100) NULL");
			$this->addColumn('account_agent','password_after'," VARCHAR(100) NULL");
			$this->addColumn('account_agent','credit_after'," double(10,2) NULL");
			$this->addColumn('account_agent','status',"int(1) NOT NULL DEFAULT 0");

			$this->addColumn('finance','is_auto_withdraw',"TINYINT(1) NOT NULL DEFAULT '0' AFTER `manage_by`");
			$this->addColumn('finance','auto_withdraw_status',"TINYINT(1) NULL AFTER `is_auto_withdraw`");
			$this->addColumn('finance','auto_withdraw_remark',"TEXT NULL AFTER `auto_withdraw_status`");
			$this->addColumn('finance','auto_withdraw_created_at',"timestamp NULL DEFAULT NULL AFTER `auto_withdraw_remark`");
			$this->addColumn('finance','auto_withdraw_updated_at',"timestamp NULL DEFAULT NULL AFTER `auto_withdraw_created_at`");
			$this->addColumn('account','is_auto_withdraw',"tinyint(1) NULL DEFAULT 1 AFTER auto_accept_bonus");


			$this->insertData('web_setting',['name','value'],['manual_linenoti_deposit','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_withdraw','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_report_result','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_other_log','1']);
			$this->insertData('web_setting',['name','value'],['manual_linenoti_register','1']);

			$this->addColumn('log_deposit_withdraw','withdraw_status_request',"TINYINT(1) NULL AFTER created_at");
			$this->addColumn('log_deposit_withdraw','withdraw_status_status',"TINYINT(1) NULL AFTER withdraw_status_request");
		}

	}

	private function insertData($table,$field,$value){
		//global $obj_con_cron;
		$sqlCheck ="Select * from {$table} where {$field[0]} ='{$value[0]}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){

			foreach ($field as $field_data){
				$txt_field .= ",{$field_data}";
			}
			$txt_field = substr($txt_field,1,strlen($txt_field));
			$txt_field = "({$txt_field})";

			//$txt_val = " VALUES(";
			foreach ($value as $val){
				$txt_val .= ",'{$val}'";
			}

			$txt_val = substr($txt_val,1,strlen($txt_val));
			$txt_val = " VALUES({$txt_val})";

			$sqlInsert = " INSERT INTO {$table} {$txt_field} {$txt_val}";
			$this->db->query($sqlInsert);
		}
	}
	private function addColumn($table_name,$column_name,$option){
		if (!$this->db->field_exists($column_name, $table_name))
		{
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}

}
