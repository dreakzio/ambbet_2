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
		$version = 3;
		$file = file_get_contents($strFileName, true);
		if($file != $version){

			$objFopen = fopen($strFileName, 'w');
			fwrite($objFopen, $version);

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

			$this->addColumn('finance','bank_withdraw_id',"INT(11) NULL AFTER manage_by");
			$this->addColumn('finance','bank_withdraw_name',"VARCHAR(255) NULL AFTER bank_withdraw_id");

			//Add table for func manage (role,menu,permission) all users
			//user_role 	= ผู้ใช้งาน ผูกกับ สิทธิ์
			$this->createTable('user_role',"
				 `id` INT NOT NULL AUTO_INCREMENT , 
				 `user_id` INT(11) NOT NULL , 
				 `role_id` INT(11) NOT NULL , 
				 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
				 `updated_at` TIMESTAMP NULL , 
				  PRIMARY KEY (`id`)
			");
			$this->addColumnUnique('user_role','UNIQUE','user_id_role_id',"`user_id_role_id` (`user_id`, `role_id`)");
			$this->addColumnConstraint('user_role','CONSTRAINT','user_role_user_id',"`user_role_user_id` FOREIGN KEY (`user_id`) REFERENCES `account`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->addColumnConstraint('user_role','CONSTRAINT','user_role_role_id',"`user_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");

			//group_menu	= เมนูหลัก
			$this->createTable('group_menu',"
				 `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(150) NOT NULL,
				  `description` varchar(250) DEFAULT NULL,
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  `is_deleted` int(1) DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  `order` int(11) DEFAULT '0',
				  PRIMARY KEY (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8");
			$this->insertDataRaw('group_menu','name','Dashboard',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (1, 'Dashboard', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('group_menu','name','ระบบสมาชิก',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (2, 'ระบบสมาชิก', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('group_menu','name','รายงาน',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (3, 'รายงาน', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('group_menu','name','ระบบธุรกรรม',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (4, 'ระบบธุรกรรม', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 4)");
			$this->insertDataRaw('group_menu','name','ระบบ LOGS',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (5, 'ระบบ LOGS', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('group_menu','name','การตั้งค่า',"",""
				,"INSERT INTO `group_menu` (`id`, `name`, `description`, `url`, `icon_class`, `is_deleted`, `created_at`, `order`)
											VALUES (6, 'การตั้งค่า', NULL, NULL, 'text-primary font-weight-bold', 0, CURRENT_TIMESTAMP, 6)");

			//menu 		= เมนูย่อย (parent => group_menu)
			$this->createTable('menu',"
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(100) NOT NULL DEFAULT '0',
				  `description` varchar(150) DEFAULT NULL,
				  `parent_id` int(8) DEFAULT '0',
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  `have_child` int(1) DEFAULT '0',
				  `is_deleted` int(1) DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  `order` int(11) DEFAULT '0',
				  PRIMARY KEY (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8");
			$this->insertDataRaw('menu','name','Dashboard',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (1, 'Dashboard', 'Dashboard', 1, 'home', 'feather icon-home primary', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','สถานะเกมส์',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (2, 'สถานะเกมส์', 'สถานะเกมส์', 1, 'gamestatus', 'feather icon-crosshair danger', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','สมาชิก',"parent_id","2"
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (3, 'สมาชิก', 'สมาชิก', 2, 'user', 'feather icon-users success', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','สมาชิกที่ถูกระงับ',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (4, 'สมาชิกที่ถูกระงับ', 'สมาชิกที่ถูกระงับ', 2, 'user_suspend', 'feather icon-user-x danger', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','พันธมิตร',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (5, 'พันธมิตร', 'พันธมิตร', 2, 'agent', 'feather icon-users info', 0, 0,CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('menu','name','โยกสมาชิกการตลาด',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (6, 'โยกสมาชิกการตลาด', 'โยกสมาชิกการตลาด', 2, 'transfer_marketking', 'feather icon-users info', 0, 0,CURRENT_TIMESTAMP, 4)");
			$this->insertDataRaw('menu','name','แนะนำเพื่อน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (7, 'แนะนำเพื่อน', 'แนะนำเพื่อน', 2, 'ref', 'feather icon-user-plus warning', 0, 0,CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('menu','name','โบนัสแนะนำเพื่อน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (8, 'โบนัสแนะนำเพื่อน', 'โบนัสแนะนำเพื่อน', 2, 'ref/bonus', 'feather icon-dollar-sign warning', 0, 0,CURRENT_TIMESTAMP, 6)");
			$this->insertDataRaw('menu','name','โบนัสคืนยอดเสีย',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (9, 'โบนัสคืนยอดเสีย', 'โบนัสคืนยอดเสีย', 2, 'bonus/returnbalance', 'feather icon-dollar-sign warning', 0, 0,CURRENT_TIMESTAMP, 7)");
			$this->insertDataRaw('menu','name','ผลประกอบการ',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (10, 'ผลประกอบการ', 'ผลประกอบการ', 3, 'report/business_profit', 'fa fa-bar-chart success', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','ยอดฝากรวมรายวัน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (11, 'ยอดฝากรวมรายวัน', 'ยอดฝากรวมรายวัน', 3, 'report/member_register_sum_deposit', 'fa fa-bar-chart success', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','ไม่ได้ฝากมากกว่า 7 วัน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (12, 'ไม่ได้ฝากมากกว่า 7 วัน', 'ไม่ได้ฝากมากกว่า 7 วัน', 3, 'report/member_not_deposit_less_than_7', 'fa fa-bar-chart danger', 0, 0,CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('menu','name','ยอดเติมเครดิต',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (13, 'ยอดเติมเครดิต', 'ยอดเติมเครดิต', 3, 'report/add_credit', 'fa fa-bar-chart info', 0, 0,CURRENT_TIMESTAMP, 4)");
			$this->insertDataRaw('menu','name','การรับโบนัส',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (14, 'การรับโบนัส', 'การรับโบนัส', 3, 'report/add_bonus', 'fa fa-bar-chart warning', 0, 0,CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('menu','name','การรับโปรโมชั่น',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (15, 'การรับโปรโมชั่น', 'การรับโปรโมชั่น', 3, 'report/add_promotion', 'fa fa-bar-chart primary', 0, 0,CURRENT_TIMESTAMP, 6)");
			$this->insertDataRaw('menu','name','รายการเดินบัญชี',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (16, 'รายการเดินบัญชี', 'รายการเดินบัญชี', 4, 'statement', 'fa fa-bar-chart primary', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','เครดิต',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (17, 'เครดิต', 'เครดิต', 4, 'deposit', 'fa fa-usd warning', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','เครดิต (รอฝาก)',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (18, 'เครดิต (รอฝาก)', 'เครดิต (รอฝาก)', 4, 'creditwait', 'feather icon-plus warning', 0, 0,CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('menu','name','ฝากเงิน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (19, 'ฝากเงิน', 'ฝากเงิน', 4, 'credit', 'feather icon-plus primary', 0, 0,CURRENT_TIMESTAMP, 4)");
			$this->insertDataRaw('menu','name','ถอนเงิน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (20, 'ถอนเงิน', 'ถอนเงิน', 4, 'withdraw', 'feather icon-minus danger', 0, 0,CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('menu','name','โยกเงินออก',"parent_id","4"
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (21, 'โยกเงินออก', 'โยกเงินออก', 4, 'TransferOut', 'fa fa-money warning', 0, 0,CURRENT_TIMESTAMP, 6)");
			$this->insertDataRaw('menu','name','ฝาก-ถอน',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (22, 'ฝาก-ถอน', 'ฝาก-ถอน', 5, 'LogDepositWithdraw', 'fa fa-history success', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','สมาชิก',"parent_id","5"
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (23, 'สมาชิก', 'สมาชิก', 5, 'LogDepositWithdraw', 'fa fa-history primary', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','คืนยอดเสีย',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (24, 'คืนยอดเสีย', 'คืนยอดเสีย', 5, 'LogReturnBalance', 'fa fa-history danger', 0, 0,CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('menu','name','SMS',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (25, 'SMS', 'SMS', 5, 'LogSms', 'fa fa-comment primary', 0, 0,CURRENT_TIMESTAMP, 4)");
			$this->insertDataRaw('menu','name','เปิดหน้าเว็ป',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (26, 'เปิดหน้าเว็ป', 'เปิดหน้าเว็ป', 5, 'LogPage', 'fa fa-history info', 0, 0,CURRENT_TIMESTAMP, 5)");
			$this->insertDataRaw('menu','name','Line notify',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (27, 'Line notify', 'Line notify', 5, 'LogLineNotify', 'fa fa-bell success', 0, 0,CURRENT_TIMESTAMP, 6)");
			$this->insertDataRaw('menu','name','วงล้อ',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (28, 'วงล้อ', 'วงล้อ', 5, 'LogWheel', 'fa fa-history warning', 0, 0,CURRENT_TIMESTAMP, 7)");
			$this->insertDataRaw('menu','name','โยกเงินออก',"parent_id","5"
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (29, 'โยกเงินออก', 'โยกเงินออก', 5, 'LogTransferOut', 'fa fa-money danger', 0, 0,CURRENT_TIMESTAMP, 8)");
			$this->insertDataRaw('menu','name','โปรโมชั่น',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (30, 'โปรโมชั่น', 'โปรโมชั่น', 6, 'promotion', 'fa fa-clone info', 0, 0,CURRENT_TIMESTAMP, 1)");
			$this->insertDataRaw('menu','name','ประกาศ',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (31, 'ประกาศ', 'ประกาศ', 6, 'news', 'fa fa-newspaper-o info', 0, 0,CURRENT_TIMESTAMP, 2)");
			$this->insertDataRaw('menu','name','ตั้งค่าเว็บ',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (32, 'ตั้งค่าเว็บ', 'ตั้งค่าเว็บ', 6, 'setting/web_setting', 'fa fa-cog primary', 0, 0,CURRENT_TIMESTAMP, 3)");
			$this->insertDataRaw('menu','name','ตั้งค่าธนาคาร',"",""
				,"INSERT INTO `menu` (`id`, `name`, `description`, `parent_id`, `url`, `icon_class`, `have_child`, `is_deleted`, `created_at`, `order`)
											VALUES (33, 'ตั้งค่าธนาคาร', 'ตั้งค่าธนาคาร', 6, 'bank', 'fa fa-clone info', 0, 0,CURRENT_TIMESTAMP, 4)");


			//node_menu 	= เมนูย่อย (parent => menu)
			$this->createTable('node_menu',"
				 `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(150) DEFAULT NULL,
				  `description` varchar(250) DEFAULT NULL,
				  `parent_id` int(8) DEFAULT '0',
				  `url` varchar(250) DEFAULT NULL,
				  `icon_class` varchar(150) DEFAULT NULL,
				  PRIMARY KEY (`id`)
			");

			//permission_menu_role (create, update, delete, export, search, view) = การเข้าถึงการจัดการ (สร้าง,แก้ไข,ลบ,ส่งออกรายงาน,ค้นหา,ดูรายการ) ของสิทธิ์ กับ เมนู
			$this->createTable('permission_menu_role',"
				`id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_id` int(11) NOT NULL DEFAULT '0',
				  `menu_id` int(11) NOT NULL DEFAULT '0',
				  `is_create` tinyint(1) NOT NULL DEFAULT '0',
				  `is_update` tinyint(1) NOT NULL DEFAULT '0',
				  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
				  `is_export` tinyint(1) NOT NULL DEFAULT '0',
				  `is_search` tinyint(1) NOT NULL DEFAULT '0',
				  `is_view` tinyint(1) NOT NULL DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`role_id`,`menu_id`),
				  KEY `id` (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
			$this->addColumnConstraint('permission_menu_role','CONSTRAINT','permission_menu_role_menu_id',"`permission_menu_role_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `menu`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->addColumnConstraint('permission_menu_role','CONSTRAINT','permission_menu_role_role_id',"`permission_menu_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");

			//SuperAdmin
			for($i=1;$i<= 33;$i++){
				$this->insertDataRaw('permission_menu_role','role_id','0',"menu_id",$i
					,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '0', $i, 1, 1, 1, 1, 1, 1)");
			}
			//Admin
			$menu_id_list = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,22,23,24,25,26,27,28];
			foreach($menu_id_list as $menu_id){
				$this->insertDataRaw('permission_menu_role','role_id','1',"menu_id",$menu_id
					,"INSERT INTO `permission_menu_role` (`id`, `role_id`, `menu_id`, `is_view`, `is_create`, `is_update`, `is_delete`, `is_export`, `is_search`)
											VALUES (null, '1', $menu_id, 1, 1, 1, 1, 1, 1)");
			}

			//permission_role = การเข้าถึงการจัดการของสิทธิ์นั้นๆ กับ สิทธิ์อื่นๆ เช่น แแอดมินสูงสุดสามารถจัดการผู้ใช้งานได้ทุกสิทธิ์
			$this->createTable('permission_role',"
				`id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_id` int(11) NOT NULL DEFAULT '0',
				  `role_child_id` int(11) NOT NULL DEFAULT '0',
				  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`role_id`,`role_child_id`),
				  KEY `id` (`id`)
			","ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
			$this->addColumnConstraint('permission_role','CONSTRAINT','permission_role_role_child_id',"`permission_role_role_child_id` FOREIGN KEY (`role_child_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");
			$this->addColumnConstraint('permission_role','CONSTRAINT','permission_role_role_id',"`permission_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`) ON DELETE CASCADE ON UPDATE RESTRICT");

			//SuperAdmin
			for($i=0;$i<= 6;$i++){
				$this->insertDataRaw('permission_role','role_id','0',"role_child_id",$i
					,"INSERT INTO `permission_role` (`id`, `role_id`, `role_child_id`)
											VALUES (null, '0', $i)");
			}

			//Admin
			$this->insertDataRaw('permission_role','role_id','1',"role_child_id",'2'
				,"INSERT INTO `permission_role` (`id`, `role_id`, `role_child_id`)
											VALUES (null, '1', 2)");
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
	private function insertDataRaw($table,$column_name_duplicate_chk,$column_value_duplicate_chk,$column_name_duplicate_chk_2 = '',$column_value_duplicate_chk_2= '',$sql_insert_raw){
		//global $obj_con_cron;
		$sqlCheck ="Select * from {$table} where {$column_name_duplicate_chk} ='{$column_value_duplicate_chk}'".(!empty($column_name_duplicate_chk_2) ? " AND {$column_name_duplicate_chk_2} ='{$column_value_duplicate_chk_2}'" : "");
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query($sql_insert_raw);
		}
	}
	private function addColumn($table_name,$column_name,$option){
		if (!$this->db->field_exists($column_name, $table_name))
		{
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function addColumnUnique($table_name,$column_name,$key_name,$option){
		$sqlCheck ="SHOW INDEX FROM {$table_name} where Key_name ='{$key_name}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}
	private function addColumnConstraint($table_name,$column_name,$constraint_name,$option){
		$sqlCheck ="SELECT * FROM information_schema.table_constraints where TABLE_NAME ='{$table_name}' AND CONSTRAINT_NAME = '{$constraint_name}'";
		$query =$this->db->query($sqlCheck);
		if($query->num_rows() ==0){
			$this->db->query("ALTER TABLE {$table_name} ADD {$column_name} {$option}");
			//echo "Add Column {$column_name} to {$table_name} Success !!!! <br/>";
		}
	}

	private function createTable($table_name,$sql_column_and_key_raw,$option = "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"){
		if (!$this->db->table_exists($table_name))
		{
			$this->db->query("CREATE TABLE `{$table_name}` (
					  {$sql_column_and_key_raw}
					) {$option}
			");
			//echo "Create table {$table_name} Success !!!! <br/>";
		}
	}

}
