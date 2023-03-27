<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Account extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
		$language = $this->session->userdata('language');
		$this->lang->load('text', $language);
    }
	private function  check_login()
	{
		if (!isset($_SESSION['user'])) {
			session_destroy();
			redirect('auth');
			exit();
		}
		$user = $_SESSION['user'];
		if (empty($user)) {
			session_destroy();
			redirect('auth');
			exit();
		}
	}
    public function remaining_credit()
    {
		$this->check_login();
        header('Content-Type: application/json');
		$user = $_SESSION['user'];
		//print_r($user);
        try {
        	if(!empty($user['member_username'])){
				$user['account_agent_username'] = $user['member_username'];
				$balance_credit = $this->game_api_librarie->balanceCredit($user);
				//print_r($balance_credit);
				echo json_encode([
					'message' => 'success',
					'result' => $balance_credit]);
			}else{
				echo json_encode([
					'message' => 'ทำรายการไม่สำเร็จ, ท่านยังไม่ได้รับยูส',
					'error' => true
				]);
				exit();
			}

        } catch (\Exception $e) {
            echo json_encode([
            'message' => 'ทำรายการไม่สำเร็จ',
            'error' => true
            ]);
            exit();
        }
    }
    public function remaining_wallet()
    {
		$this->check_login();
        header('Content-Type: application/json');
        $data = $this->Account_model->account_find_chk_fast([
        'id' => $_SESSION['user']['id']
        ]);
        echo json_encode([
        'message' => 'success',
        'result' => [
          'remaining_wallet' => $data['amount_wallet']
        ]
      ]);
    }
    public function remaining_amount_deposit()
    {
		$this->check_login();
        header('Content-Type: application/json');
        $data = $this->Account_model->account_find_chk_fast([
        'id' => $_SESSION['user']['id']
        ]);
        echo json_encode([
        'message' => 'success',
        'result' => [
          'amount_deposit' => $data['amount_deposit_auto']
        ]
      ]);
    }
    public function register()
    {
        header('Content-Type: application/json');
        check_parameter([
        'phone',
        'full_name',
        'bank',
        'bank_number',
        //'line_id',
        'password',
        ], 'POST');
		$post = $this->input->post();
		if((!isset($_SESSION['register_step']) || !isset($_SESSION['register_data'])) || ($_SESSION['register_step'] != '3' && $_SESSION['register_step'] != '2') || (empty($_SESSION['register_data']['phone']))){
			echo json_encode([
				'message' => 'เบอร์โทรไม่ควรเป็นค่าว่าง',
				'error' => true
			]);
			exit();
		}else{
			$line_login_status = $this->Setting_model->setting_find([
				'name' => 'line_login_status'
			]);
			$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
			if($line_login_status == "1" && isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk']){

			}else{
				$post['phone'] = $_SESSION['register_data']['phone'];
			}
		}
		$post['full_name'] = trim($post['full_name']);
		$post['bank_number'] = str_replace("-","",trim($post['bank_number']));
		$post['bank_number']  = preg_replace('/[^0-9]/','',trim($post['bank_number']));
		$post['phone'] = trim($post['phone']);
		$post['phone']  = preg_replace('/[^0-9]/','',trim($post['phone']));
		$post['bank']  = trim($post['bank']);
		if($post['bank'] == "10"){
			$post['bank_number'] = $post['phone'];
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
		}
		if(strlen(trim($post['password'])) < 5){
			echo json_encode([
				'message' => 'รหัสผ่านควรมีมากกว่าหรือเท่ากับ 6 ตัว',
				'error' => true
			]);
			exit();
		}
		if(strlen(trim($post['bank_number'])) < 10){
			echo json_encode([
				'message' => 'เลขบัญชีควรมีมากกว่าหรือเท่ากับ 10 ตัว',
				'error' => true
			]);
			exit();
		}
		if(!array_key_exists(trim($post['bank']),getBankListUniqueCode())){
			echo json_encode([
				'message' => 'ทางเว็บไซต์ยังไม่รองรับธนาคารที่ท่านเลือก',
				'error' => true
			]);
			exit();
		}
        $account = $this->Account_model->account_find_chk_fast([
          'username' => $post['phone'],
			'deleted_ignore' => true,
        ]);
        if ($account!="") {
            echo json_encode([
            'message' => 'เบอร์โทรนี้มีผู้ใช้งานแล้ว',
            'error' => true
            ]);
            exit();
        }
        $bank = $this->Account_model->account_find_chk_fast([
          //'bank' => $post['bank'],
          'bank_number' => $post['bank_number'],
			'deleted_ignore' => true,
        ]);
        if ($bank!="") {
            echo json_encode([
            'message' => 'เลขบัญชีนี้มีผู้ใช้งานแล้ว',
            'error' => true
            ]);
            exit();
        }
		$bank = $this->Bank_model->bank_find([
			//'bank' => $post['bank'],
			'bank_number' => $post['bank_number'],
			  'deleted_ignore' => true,
		  ]);
		  if ($bank!="") {
            echo json_encode([
            'message' => 'เลขบัญชีนี้มีผู้ใช้งานแล้ว',
            'error' => true
            ]);
            exit();
        }
		$bank = $this->Account_model->account_find_chk_fast([
			'full_name' => trim($post['full_name']),
			//'deleted_ignore' => true,
		]);
		if ($bank!="") {
			echo json_encode([
				'message' => 'ชื่อ-นามสกุลนี้มีผู้ใช้งานแล้ว',
				'error' => true
			]);
			exit();
		}
		$line_login_status = $this->Setting_model->setting_find([
			'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		if($line_login_status == "1" && isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk'] && isset($_SESSION['line_login_user_id']) && !empty($_SESSION['line_login_user_id'])){
			$account_chk = $this->Account_model->account_find_chk_fast([
				'linebot_userid' => $_SESSION['line_login_user_id'],
				'deleted_ignore' => true,
			]);
			if($account_chk != ""){
				echo json_encode([
					'message' => 'ไลน์ไอดีนี้มีผู้ใช้งานแล้ว ['.$_SESSION['line_login_user_id'].']',
					'error' => true
				]);
				exit();
			}
		}
        $member = "CREATE";
        if ($member!="") {
			try{
				$auto_create_member = $this->Setting_model->setting_find([
					'name' => 'auto_create_member'
				]);
				if(
					($auto_create_member!="" && $auto_create_member['value'] == "1") ||
					$auto_create_member==""
				){
					$password = $this->config->item('prefix_pass').substr(rand(10000000,99999999),2,4);
					//$account_max_id = $this->Member_model->member_max_id();
					$username = 0;
					// if(!is_null($account_max_id) && isset($account_max_id['username'])){
					// 	$account_max_id['username'] = str_replace(strtolower($this->config->item('api_agent')),"",strtolower($account_max_id['username']));
					// 	$username = (int)filter_var($account_max_id['username'], FILTER_SANITIZE_NUMBER_INT);
					// 	$username += 1;
					// }
					// $username = trim($post['phone']);
					// $post_fix_username = str_pad( $username, 16 - strlen($this->config->item('api_agent')), "0", STR_PAD_LEFT );
					// $username_full = $post_fix_username;

					/*$length_shuffle = 1;
					$string_pre_shuffle =  substr(str_shuffle('abcdef'),1,$length_shuffle);

					$account_max_id = $this->Member_model->account_agent_max_id();
					$username = 0;
					if(!is_null($account_max_id) && isset($account_max_id['id'])){
					$account_max_id['id'] = str_replace(strtolower($this->config->item('api_agent')),"",strtolower($account_max_id['id']));
					$username = (int)filter_var($account_max_id['id'], FILTER_SANITIZE_NUMBER_INT);
					$username += 1;
					}

					$len_num = 13;

					$post_fix_username = str_pad( $username, $len_num  - strlen($this->config->item('api_agent')), "0", STR_PAD_LEFT );
					$username_full = $string_pre_shuffle.$post_fix_username;*/


					$response = $this->game_api_librarie->registerPlayer($post['phone'],$password,$post['phone'],$post['phone']);
					if(isset($response['code']) && $response['code'] == 0 && isset($response['result']) && isset($response['result']['loginName']) ){
						$response['result']['password'] = $password;
						$this->createUser($post,$response);
						echo json_encode([
							'message' => 'success',
							'result' => true,
						]);
					}else{
						if(isset($response['code']) && $response['code'] == "80000014"){
							echo json_encode([
								'message' => 'ทำรายการไม่สำเร็จ, เบอร์โทรนี้มีผู้ใช้งานแล้ว...',
								'error' => false,
								'data'=>$response

							]);
						}else{
							echo json_encode([
								'message' => 'ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง '.(isset($response['code']) ? "#".$response['code'] : ""),
								'error' => false,
								'data'=>$response
							]);
						}
					}
				}else{
					$this->createUser($post);
					echo json_encode([
						'message' => 'success',
						'result' => true,
					]);
				}
			}catch (Exception $ex){
				echo json_encode([
					'message' => 'ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง',
					'error' => false
				]);
			}


        } else {
            echo json_encode([
            'message' => 'ทำรายการไม่สำเร็จ',
            'error' => false
            ]);
        }
    }

    private function createUser($post,$response = []){
		$line_login_status = $this->Setting_model->setting_find([
			'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		$linebot_userid = '';
		if($line_login_status == "1" && isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk'] && isset($_SESSION['line_login_user_id']) && !empty($_SESSION['line_login_user_id'])){
			$linebot_userid = $_SESSION['line_login_user_id'];
		}
		$account_id = $this->Account_model->account_create([
			'username' => trim(strip_tags($post['phone'])),
			'password' => md5($post['password']),
			'phone' => trim(strip_tags($post['phone'])),
			'full_name' => trim(strip_tags($post['full_name'])),
			'bank' => $post['bank'],
			'bank_number' => trim(strip_tags($post['bank_number'])),
			'line_id' => '',
			'bank_name' => trim(strip_tags($post['full_name'])),
			'turn_before' => 0,
			'turn_over' => 0,
			'amount_wallet' => 0,
			'amount_wallet_ref' => 0,
			'amount_deposit_auto' => 0,
			'linebot_userid' => $linebot_userid,
			'pin' => '',
			'sha1_acount' => '',
			'deleted' => 0,
		]);

		$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
			'type' => 5,
			'message' => "สมัครสมาชิก ยูส ".$post['phone']." เวลา ".(isset($post['date']) && isset($post['time']) ? $post['date']." ".$post['time'].":".date('s') : date('Y-m-d H:i')),
		]);

		if(count($response) > 0){
			$this->Member_model->member_create([
				'account_id' => $account_id,
				'accid' => $account_id,
				'username' => $response['result']['loginName'],
				'password' =>$response['result']['password'],
			]);
		}
		if (isset($post['ref']) && !empty($post['ref'])) {
			$ref = $this->Account_model->account_find([
				'id' => $post['ref']
			]);
			if($ref != ""){
				$this->Ref_model->ref_create([
					'from_account' => $post['ref'],
					'from_account_username' => $ref['username'],
					'to_account' => $account_id,
					'to_account_username' => $post['phone'],
					'agent' => $ref['agent'] !== "" ?  $ref['agent'] : 0
				]);
			}
		}
		$_SESSION['user_register'] = [
			'username' => $post['phone'],
			'password' => $post['password']
		];
		$_SESSION['register_step'] = '1';
		unset($_SESSION['register_data']);

		//Auto login
		$account = $this->Account_model->account_find([
			'id' => $account_id
		]);
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
	}
	public function change_password_user()
	{
		$this->check_login();
		header('Content-Type: application/json');
		check_parameter([
			'old_password',
			'password',
			'password_confirm',
		], 'POST');
		$post = $this->input->post();
		$account = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($account!="") {
			if(md5(trim($post['old_password'])) != $account['password']){
				echo json_encode([
					'message' => 'รหัสผ่านเก่าไม่ตรงกัน',
					'error' => false
				]);
			}else if(trim($post['password']) != trim($post['password_confirm'])){
				echo json_encode([
					'message' => 'รหัสผ่านใหม่ไม่ตรงกัน',
					'error' => false
				]);
			}else if(strlen(trim($post['password'])) < 6){
				echo json_encode([
					'message' => 'รหัสผ่านใหม่ตรงมีอย่างน้อย 6 ตัวอักษรขึ้นไป',
					'error' => false
				]);
			}else{
				$this->Account_model->account_update([
					'password' => md5(trim($post['password'])),
					'id' => $account['id']
				]);
				echo json_encode([
					'message' => 'success',
					'result' => true,
				]);
			}
		}else{
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => false
			]);
		}
	}
	public function change_is_active_return_balance()
	{
		$this->check_login();
		header('Content-Type: application/json');
		check_parameter([
			'is_active_return_balance',
		], 'POST');
		$post = $this->input->post();
		$account = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($account!="") {
			$this->Account_model->account_update([
				'is_active_return_balance' => $post['is_active_return_balance'],
				'id' => $account['id']
			]);
			echo json_encode([
				'message' => 'success',
				'result' => true,
			]);
		}else{
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => false
			]);
		}
	}
    public function remaining_wallet_ref()
    {
		$this->check_login();
        header('Content-Type: application/json');
        $data = $this->Account_model->account_find_chk_fast([
        'id' => $_SESSION['user']['id']
        ]);
        echo json_encode([
        'message' => 'success',
        'result' => [
          'remaining_wallet_ref' => $data['amount_wallet_ref']
        ]
      ]);
    }

	public function history_list()
	{
		$this->check_login();
		header('Content-Type: application/json');
		$data = $this->Finance_model->finance_history([
			'account' => $_SESSION['user']['id'],
			'type' => isset($_GET['type']) ?$_GET['type'] : '' ,
		]);
		foreach($data as $index => $result){
			$data[$index]['id'] = count($data) - $index;
		}
		echo json_encode([
			'message' => 'success',
			'result' => $data]);
	}

	public function ref_list()
	{
		$this->check_login();
		$get = $this->input->get();
		if(isset($get['per_page']) && $get['per_page'] > 20){
			$get['per_page'] = 20;
		}else if(!isset($get['per_page'])){
			$get['per_page'] = 20;
		}
		if(!isset($get['page'])){
			$get['page'] = 1;
		}
		header('Content-Type: application/json');
		$data = $this->Ref_model->ref_list([
			'from_account' => $_SESSION['user']['id'],
			'per_page' => $get['per_page'],
			'page' =>  ($get['per_page'] * ($get['page'] - 1)),
		]);
		$data_count = $this->Ref_model->ref_count([
			'from_account' => $_SESSION['user']['id']
		]);
		$totalPages = ceil($data_count / $get['per_page']);
		foreach($data as $index => $ref){
			$data[$index]['to_account_username'] = substr($ref['to_account_username'],0,5)."xxxxx";
			$data[$index]['to_account_username'] .= !empty($ref['account_agent_username']) ? "" : " (ยังไม่ได้รับยูสเซอร์)";
		}
		echo json_encode([
			'message' => 'success',
			'result' => $data,
			'from' => (int)(($get['page'] - 1) * $get['per_page']) + 1 > $data_count ? $data_count : (int)(($get['page'] - 1) * $get['per_page']) + 1,
			'to' => (int)((($get['page'] - 1) * $get['per_page']) + $get['per_page'] > $data_count ? $data_count : (($get['page'] - 1) * $get['per_page']) + $get['per_page']),
			'total' => (int)$data_count,
			'page_count' => (int)$totalPages,
			'per_page' => (int)$get['per_page'],
			'page' => (int)$get['page'],
		]);
	}

	public function ref_deposit_list()
	{
		$this->check_login();
		header('Content-Type: application/json');
		$data = $this->Ref_model->ref_deposit_list([
			'from_account' => $_SESSION['user']['id'],
			'username_to' => $_SESSION['user']['username'],
			'per_page' => 20,
			'page' => 0,
			'type_list' => ["0","1"]
		]);
		foreach($data as $index => $ref){
			$data[$index]['username'] = !empty($ref['username']) ? substr($ref['username'],0,5)."xxxxx" : "-";
		}
		echo json_encode([
			'message' => 'success',
			'result' => $data]);
	}

	public function check_username_exist(){
		$this->check_login();
		$auto_create_member = $this->Setting_model->setting_find([
			'name' => 'auto_create_member'
		]);
		$auto_create_member_deposit_amount = $this->Setting_model->setting_find([
			'name' => 'auto_create_member_deposit_amount'
		]);
		$data = $this->Account_model->account_agent_find_by_account_id($_SESSION['user']['id']);
		echo json_encode([
			'message' => 'success',
			'amount' => $auto_create_member!="" && $auto_create_member['value'] == "0" && $auto_create_member_deposit_amount!="" && is_numeric($auto_create_member_deposit_amount['value']) ? (float)$auto_create_member_deposit_amount['value'] : 0,
			'result' => empty($data['account_agent_username']) && $auto_create_member!="" && $auto_create_member['value'] == "0" ? false : true
		]);
	}

	public function spin_wheel(){
		$this->check_login();
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$wheel_point_for_spin = $this->Setting_model->setting_find([
			'name' => 'wheel_point_for_spin'
		]);
		$feature_wheel = $this->Feature_status_model->setting_find([
			'name' => 'wheel'
		]);
		if($feature_wheel == "" || $feature_wheel['value'] == "0"){
			echo json_encode([
				'message' => 'ระบบวงล้อยังไม่เปิดใช้งานในขณะนี้',
				'error' => true
			]);
			exit();
		}else if($wheel_point_for_spin == "" || !is_numeric($wheel_point_for_spin['value']) || $wheel_point_for_spin['value'] <= 0){
			echo json_encode([
				'message' => 'ระบบคำนวนวงล้อยังไม่เปิดใช้งานในขณะนี้',
				'error' => true
			]);
			exit();
		}else if(!is_numeric($user['point_for_wheel']) || ($user['point_for_wheel'] < $wheel_point_for_spin['value'])){
			echo json_encode([
				'message' => 'เหรียญของท่านคงเหลือไม่พอที่จะใช้ในการสุ่ม',
				'error' => true
			]);
			exit();
		}
		$point_for_wheel_before = (float)$user['point_for_wheel'];
		$point_for_wheel_new = (float)$user['point_for_wheel'] - (float)$wheel_point_for_spin['value'];
		$point_for_wheel_new = $point_for_wheel_new < 0 ? 0 : $point_for_wheel_new;
		$this->Account_model->account_update(['point_for_wheel'=>$point_for_wheel_new,'id'=>$user['id']]);
		$datas = $this->Setting_model->setting_for_wheel_list(['all'=>true]);
		$credit =  null;
		$id =  0;
		$name =  "";
		$percent =  0;
		$percent_max_chk = null;
		$percent_max_percent_chk = 0.00;
		foreach($datas as $data){
			if((float)$data['percent'] >= $percent_max_percent_chk){
				$percent_max_percent_chk = (float)$data['percent'];
				$percent_max_chk = $data;
			}
		}
		foreach($datas as $data){
			if(roll($data['percent']) && is_null($credit)){
				$credit = $data['credit'];
				$id = $data['id'];
				$name = $data['name'];
				$percent = $data['percent'];
			}
		}
		if(is_null($credit) && !is_null($percent_max_chk)){
			$credit = $percent_max_chk['credit'];
			$id = $percent_max_chk['id'];
			$name = $percent_max_chk['name'];
			$percent = $percent_max_chk['percent'];
		}

		$remaining_credit = 0.00;
		try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
			$remaining_credit = $balance_credit;
		} catch (\Exception $e) {

		}
		$credit = is_null($credit) ? 0 : $credit;
		if(!is_null($credit) && is_numeric($credit) && $credit > 0){

			$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
				'account' => $user['id'],
				'username' => $user['username'],
				'amount' => $credit,
				'amount_before' => $remaining_credit,
				'type' => '1', //ฝาก
				'description' => 'เพิ่มเครดิต (วงล้อพารวย)',
				'admin' =>$user['id'],
			]);

			$form_data = [];
			$form_data["account_agent_username"] = $user['account_agent_username'];
			$form_data["amount"] = $credit;
			$form_data = member_credit_data($form_data);
			$response = $this->game_api_librarie->deposit($form_data);
			if (isset($response['ref'])) {

				$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'full_name' => $user['full_name'],
					'from_amount' => $form_data['amount'],
					'amount' => $form_data["amount"],
					'type' => 'bonus_wheel',
					'description' => "เพิ่มเครดิต (วงล้อพารวย) สุ่มได้ ".(empty($name) ? "ไม่ได้รางวัล" : $name),
					'manage_by' =>$user['id'],
					'manage_by_username' =>$user['username'],
					'manage_by_full_name' =>$user['full_name'],
				]);

				$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'point_before' => $user['point_for_wheel'],
					'point_after' => $point_for_wheel_new,
					'point' => $wheel_point_for_spin['value'],
					'amount_before' => $remaining_credit,
					'amount' => $credit,
					'wheel_name' => $name,
					'wheel_percent' => $percent,
					'wheel_credit' => $credit,
					'description' => "สุ่มได้ ".(empty($name) ? "ไม่ได้รางวัล" : $name),
					'type' => '1', //เล่น
					'status' => '1', //สำเร็จ
				]);

				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ Log วงล้อ #".$log_wheel_id,
					]);
				}
			} else {
				$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

				$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'point_before' => $user['point_for_wheel'],
					'point_after' => $point_for_wheel_new,
					'point' => $wheel_point_for_spin['value'],
					'amount_before' => $remaining_credit,
					'amount' => $credit,
					'wheel_name' => $name,
					'wheel_percent' => $percent,
					'wheel_credit' => $credit,
					'type' => '1', //เล่น
					'status' => '2', //ไม่สำเร็จ
					'description' => $error_message.", สุ่มได้ ".(empty($name) ? "ไม่ได้รางวัล" : $name),
				]);

				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description']." | ".$error_message,
					]);
				}
			}
		}else{
			$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
				'account' => $user['id'],
				'username' => $user['username'],
				'point_before' => $user['point_for_wheel'],
				'point_after' => $point_for_wheel_new,
				'point' => $wheel_point_for_spin['value'],
				'amount_before' => $remaining_credit,
				'amount' => $credit,
				'wheel_name' => $name,
				'wheel_percent' => $percent,
				'wheel_credit' => $credit,
				'description' => "สุ่มได้ ".(empty($name) ? "ไม่ได้รางวัล" : $name),
				'type' => '1', //เล่น
				'status' => '1', //สำเร็จ
			]);
		}
		if((int)$id <= 0){
			$this->Account_model->account_update(['point_for_wheel'=>$point_for_wheel_before,'id'=>$user['id']]);
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'credit' => is_null($credit) ? 0 : $credit,
				'point_for_wheel' => $point_for_wheel_before,
				'name' => $name,
				'id' => $id,
			]);
			exit();
		}else{
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'credit' => is_null($credit) ? 0 : $credit,
				'point_for_wheel' => $point_for_wheel_new,
				'name' => $name,
				'id' => $id,
			]);
			exit();
		}
	}

	public function user_bank()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'user_bank';
		$this->load->view('main', $data);
	}

	public function user_account()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'user_account';
		$this->load->view('main', $data);
	}

	public function history_list_bonus_return()
	{
		$this->check_login();
		header('Content-Type: application/json');
		$feature_bonus_return_balance_winlose = $this->Feature_status_model->setting_find([
			'name' => 'bonus_return_balance_winlose'
		]);
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if($user == ""){
			echo json_encode([
				'message' => 'success',
				'result' => []]);
		}
		if($feature_bonus_return_balance_winlose!= "" && $feature_bonus_return_balance_winlose['value'] == "1"){
			$data = $this->Ref_model->ref_deposit_list_page([
				'per_page' => 20,//left
				'page' => 0,//start,right
				'type_list' => ["2"],
				'account' => $user['id'],
			]);
			foreach($data as $index => $result){
				$data[$index]['id'] = count($data) - $index;
			}
		}else{
			$data = [];
		}
		echo json_encode([
			'message' => 'success',
			'result' => $data]);
	}
	public function remaining_return_balance_ref()
	{
		$this->check_login();
		header('Content-Type: application/json');
		$data = $this->Account_model->account_find_chk_fast([
			'id' => $_SESSION['user']['id']
		]);
		echo json_encode([
			'message' => 'success',
			'result' => [
				'remaining_return_balance_ref' => $data['point_for_return_balance']
			]
		]);
	}

	public function remaining_login_point()
	{
		$this->check_login();
		header('Content-Type: application/json');
		$data = $this->Account_model->account_find_chk_fast([
			'id' => $_SESSION['user']['id']
		]);
		echo json_encode([
			'message' => 'success',
			'result' => [
				'remaining_login_point' => $data['login_point']
			]
		]);
	}
	public function change_accept_bonus()
	{

		$this->check_login();
		header('Content-Type: application/json');
		$user = $_SESSION['user'];
		check_parameter([
			'auto_accept_bonus'
		], 'POST');
		$post = $this->input->post();
		//print_r($post['auto_accept_bonus']);
		$new_status='';
		if($post['auto_accept_bonus']==1){
			$new_status =0;
		}else{
			$new_status =1;
		}
		$this->Account_model->account_update([
			'auto_accept_bonus' => $new_status,
			'id' => $user['id']
		]);
		echo json_encode([
			'message' => 'success',
			'result' => true,
		]);

	}
}
