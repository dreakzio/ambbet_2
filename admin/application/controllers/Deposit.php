<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deposit extends CI_Controller
{
	public $menu_service;
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		//if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
		if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
			redirect('../auth');
		}
		$this->load->library(['Menu_service']);
		if(!$this->menu_service->validate_permission_menu($this->uri)){
			redirect('../auth');
		}
	}
    public function index()
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เครดิต",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['page'] = 'deposit/deposit';
        $this->load->view('main', $data);
    }
    public function deposit_list_page()
    {
        $get = $this->input->get();

        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 1
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $deposit_count_all = $this->Finance_model->finance_count([
        	'type' => 1
		]);
        $deposit_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($deposit_count_all),
         "recordsFiltered" => intval($deposit_count_search),
         "data" => $data,
       ]);
    }

	public function deposit_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
			'type' => 1
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		$data = $this->Finance_model->finance_list_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

    public function deposit_form_detail($id = "")
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เครดิต",
			'description' => 'หน้ารายละเอียด',
			'page_url' => $currentURL,
		]);
        $data['deposit'] = $this->Finance_model->finance_find([
          'id' => $id
        ]);
        $data['page'] = 'deposit/deposit_detail';
        $this->load->view('main', $data);
    }
    public function deposit_list_page_manage_transaction($account_id)
    {
        $get = $this->input->get();

        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 1
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $search_data['account'] = $account_id;
        $deposit_count_all = $this->Finance_model->finance_count([
        	'type' => 1,
            'account' => $account_id
		]);
        $deposit_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);

        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($deposit_count_all),
         "recordsFiltered" => intval($deposit_count_search),
         "data" => $data,
       ]);
    }
	public function credit_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		if(isset($post['type']) && !empty($post['type'])){
			$search_data['type'] = $post['type'];
		}
		$data = $this->Credit_model->credit_list_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

	private function remaining_credit($user)
	{
		header('Content-Type: application/json');
		try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
			return $balance_credit;
		} catch (\Exception $e) {
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
			exit();
		}
	}
	public function credit_history_create()
	{
		//var_dump($_FILES);
		check_parameter([
			'account_id',
			'process',
			'type',
			'transaction',
		], 'POST');

		$post = $this->input->post();
		//print_r($post);
		$process = explode(',', $post['process']);
		$process = implode($process);
		$user = $this->User_model->user_find([
			'id' => $post['account_id']
		]);
		if ($user=="") {
			echo json_encode([
				'message' => 'ไม่พบข้อมูล Username นี้',
				'error' => true
			]);
			exit();
		}

		//die();

		$slip_image ='';
		if($_FILES){
			$slip_image  = $this->slip_image('image_file');
		}

		if(in_array($post['type'],[1,2])){
			$credit_before = $user['amount_deposit_auto'];
			$credit_after = $post['type']==1?($user['amount_deposit_auto']+$process):($user['amount_deposit_auto']-$process);
			$create = [
				'process' => $process,
				'credit_before' => $credit_before,
				'credit_after' => $credit_after,
				'type' => $post['type'],
				'account' => $post['account_id'],
				'admin' => $_SESSION['user']['id'],
				'username' => $user['username'],
				'transaction' => $post['transaction'],
				'slip_image' => $slip_image,
			];
			$log_deposit_withdraw_id = "";
			if ($post['transaction']==1) {

				//เพิ่ม Logs
				$amount_credit_before = $this->remaining_credit($user);
				$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'amount' => $create["process"],
					'amount_before' => $amount_credit_before,
					'type' => $post['type'] ==1 ? 1 : 2, //ฝาก
					'description' => $post['type'] ==1 ? 'เพิ่มเครดิต (Amount Deposit Auto)' : 'ลดเครดิต (Amount Deposit Auto)',
					'admin' =>$_SESSION['user']['id'],
				]);

				// $user = $this->User_model->user_find([
				//   'id' => $post['account_id']
				// ]);
				$date = new DateTime($post['date'].' '.$post['time']);
				$date = $date->format('Y-m-d H:i:s');
				$date_add_once_minute = new DateTime($post['date'].' '.$post['time']);
				$date_add_once_minute->modify('+1 minutes');
				$date_add_once_minute = $date_add_once_minute->format('Y-m-d H:i');
				$transaction = $this->Transaction_model->transaction_find([
					'date_bank' => $date,
					'account' => $post['account_id'],
					'amount' => $process,
					'bank_number' => $user['bank_number'],
					'type' => '1', //ฝาก
				]);
				$transaction_add_once_minute = $this->Transaction_model->transaction_find([
					'date_bank' => $date_add_once_minute,
					'account' => $post['account_id'],
					'amount' => $process,
					'bank_number' => $user['bank_number'],
					'type' => '1', //ฝาก
				]);
				if ($transaction!="" || $transaction_add_once_minute != "") {

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | รายการนี้ Transaction ตรวจพบแล้ว",
						]);
					}

					echo json_encode([
						'message' => 'รายการนี้ Transaction ตรวจพบแล้ว',
						'error' => true
					]);
					exit();
				} else {

					$transaction_id = $this->Transaction_model->transaction_create([
						'date_bank' => $date,
						'account' => $post['account_id'],
						'bank_number' => $user['bank_number'],
						'amount' => $process,
						'type' => 1,
						'admin' => $_SESSION['user']['id']
					]);

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | บันทึกข้อมูลลง Transaction".empty($transaction_id) ? '' : ' #'.$transaction_id,
						]);
					}
				}
			}else{
				if($post['type'] == "1" && (!isset($post['force_add_credit']) || (isset($post['force_add_credit']) && $post['force_add_credit'] == "N"))){
					$credit_history_chk = $this->Credit_model->credit_find([
						'process' => $process,
						'type' => $post['type'],
						'account' => $post['account_id'],
						'date_start' => date('Y-m-d'),
						'date_end' => date('Y-m-d')
					]);
					if($credit_history_chk != ""){
						echo json_encode([
							'message' => 'ตรวจพบรายการซ้ำกันในระบบ',
							'code' => 'DUPLICATE',
							'error' => true
						]);
						exit();
					}
				}
			}

			//เพิ่ม Logs
			$amount_credit_before = $this->remaining_credit($user);
			if($log_deposit_withdraw_id == ""){
				$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'amount' => $create["process"],
					'amount_before' => $amount_credit_before,
					'type' => $post['type'] ==1 ? 1 : 2, //ฝาก
					'description' => $post['type'] ==1 ? 'เพิ่มเครดิต (Amount Deposit Auto)' : 'ลดเครดิต (Amount Deposit Auto)',
					'admin' =>$_SESSION['user']['id'],
				]);
			}
			if ($post['transaction']==1) {
				$create['date_bank'] = $date;
			}
			$credit_id = $this->Credit_model->credit_create($create);
			if ($credit_id) {
				$this->User_model->user_update([
					'id' => $post['account_id'],
					'amount_deposit_auto' => $credit_after
				]);
			}

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | บันทึกข้อมูลเรียบร้อยแล้ว".(empty($credit_id) ? '' : ' #'.$credit_id),
				]);
			}



			//บันทึก line notify job
			if($post['type']==1){
				date_default_timezone_set('Asia/Bangkok');
				$account = $this->Account_model->account_find([
					'id' => $_SESSION['user']['id']
				]);
				$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
					'type' => 1,
					'message' => "ยอดฝาก ".number_format($process,2)." บาท ยูส ".$user['username']." เวลา ".(isset($post['date']) && isset($post['time']) ? $post['date']." ".$post['time'].":".date('s') : date('Y-m-d H:i'))." ปรับโดย ".$account['full_name'],
				]);


			}
		}else if($post['type'] == 3){
			$amount_credit_before = $this->remaining_credit($user);
			$create = [
				'process' => $process,
				'credit_before' => $amount_credit_before,
				'credit_after' => (float)$amount_credit_before + (float)$post['process'],
				'type' => $post['type'],
				'account' => $post['account_id'],
				'admin' => $_SESSION['user']['id'],
				'username' => $user['username'],
				'transaction' => 0,
				'slip_image' => $slip_image,
			];
			//เพิ่ม Logs
			$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
				'account' => $user['id'],
				'username' => $user['username'],
				'amount' => $create["process"],
				'amount_before' => $amount_credit_before,
				'type' => 1, //เพิ่ม (เข้าหน้า AG)
				'description' =>  'เพิ่มเครดิต (เข้าหน้า AG)',
				'admin' =>$_SESSION['user']['id'],
			]);
			$credit_id = $this->Credit_model->credit_create($create);
			$form_data_deposit = [];
			$form_data_deposit["account_agent_username"] = $user['account_agent_username'];
			$form_data_deposit["amount"] = $create["process"];
			$form_data_deposit = member_credit_data($form_data_deposit);
			$response_deposit = $this->game_api_librarie->deposit($form_data_deposit);
			if (isset($response_deposit['ref'])) {
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description']." | ดำเนินการสำเร็จ",
					]);
				}
			}else{
				$error_message = isset($response_deposit['status']['code']) ? "(ไม่สำเร็จ : #".$response_deposit['status']['code'].')' : '(ไม่สำเร็จ)';
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!="") {
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description'] . " | " . $error_message
					]);
				}
				echo json_encode([
					'message' => $error_message,
					'error' => true
				]);
				exit();
			}
		}

		$line_send_messages_status = $this->Setting_model->setting_find([
			'name' => 'line_send_messages_status'
		]);
		$web_name = $this->Setting_model->setting_find([
			'name' => 'web_name'
		]);
		$line_login_callback = $this->Setting_model->setting_find([
			'name' => 'line_login_callback'
		]);
		$line_messages_token = $this->Setting_model->setting_find([
			'name' => 'line_messages_token'
		]);


		if(trim($line_send_messages_status['value'])==1 and $post['type']==1){

			$line_msg = array();
			$bank_list = array(
				'01' => 'bbl',
				'02' => 'kbank',
				'03' => 'ktb',
				'04' => 'tmb',
				'05' => 'scb',
				'06' => 'bay',
				'07' => 'gsb',
				'08' => 'tbank',
				'09' => 'baac',
				'1' => 'bbl',
				'2' => 'kbank',
				'3' => 'ktb',
				'4' => 'tmb',
				'5' => 'scb',
				'6' => 'bay',
				'7' => 'gsb',
				'8' => 'tbank',
				'9' => 'baac',
				'10' => 'True Wallet',
			);
			//print_r($user);
			$current_time = date('Y-m-d H:i:s');

			$line_msg['web_name'] = $web_name['value'];
			$line_msg['bank_tf_name'] = $bank_list[$user['bank']];
			$line_msg['bank_tf_number'] = $user['bank_number'];
			$line_msg['balance'] = number_format($process,2);
			$line_msg['bank_time'] = $current_time;
			$line_msg['credit_after'] = $credit_after;
			$line_msg['url_login'] = $line_login_callback['value'];
			$line_msg['linebot_userid'] = $user['linebot_userid'];
			$line_msg['type_tran'] =$post['type'];
			//print_r($line_msg);
			//include_once ('/lib/send_line_message.php');
			if($user['linebot_userid']!=''){
				$this->auto_withdraw_librarie->send_line_message($line_msg,$line_messages_token['value']);
			}

		}

		$this->session->set_flashdata('toast', 'บันทึกข้อมูลเรียบร้อยแล้ว');
		echo json_encode([
			'message' => 'success',
			'result' => true
		]);
	}
	/// save image to folder
	/// ทำให้สามารถ  fork ใหม่ได้ ทดสอบ fork
	public function slip_image($name){
		$type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		$random_string = random_string('alnum', 5);
		$rename = "slip_".date('YmdHis').'_'.$random_string.".".$type_file;
		$config['upload_path']          = 'assets/images/slip/';
		$config['allowed_types']        = 'gif|jpg|png|jpeg';
		// $config['max_size']             = 60000;
		// $config['max_width']            = 4000;
		// $config['max_height']           = 4000;
		$config['file_name']           = $rename;
		//resize
		$config['image_library'] = 'gd2';
		$config['source_image'] = $config['upload_path'].$rename;
		// $config['create_thumb'] = TRUE;
		$config['quality'] = '60%';
		$config['maintain_ratio'] = TRUE;
		$config['width']     = 270;
		$config['height']   = 468;
		// $this->upload->clear();
		$this->upload->initialize($config);
		$this->load->library('upload', $config);
		if ($_FILES[$name]['error']==0) {
			if($this->upload->do_upload($name)){
				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				return $rename;
			}else{
				echo $this->upload->display_errors();
				exit();
			}
		}
	}
}
