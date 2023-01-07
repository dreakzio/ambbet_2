<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Credit extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
		$this->load->helper('form','url');
        if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
            redirect('../auth');
        }
    }
    public function index()
    {
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ฝากเงิน",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['user'] = [];
        $data['page'] = 'credit/credit';
        $this->load->view('main', $data);
    }
    public function credit_list_page()
    {
        $get = $this->input->get();

        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,//start,right
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $credit_count_all = $this->Credit_model->credit_count();
        $credit_count_search = $this->Credit_model->credit_count($search_data);
        $data = $this->Credit_model->credit_list_page($search_data);
        foreach ($data as $key => $value) {
          if ($value['admin_username']=='AUTO') {
            if ($value['slip']!=NULL) {
              $data[$key]['admin_username'] = 'SLIP';
            } else {
              $data[$key]['admin_username'] = 'AUTO';
            }
          } else {
            $data[$key]['admin_username'];
          }
        }
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($credit_count_all),
         "recordsFiltered" => intval($credit_count_search),
         "data" => $data,
       ]);
    }
	public function credit_list_page_manage_transaction($account_id)
	{
		$get = $this->input->get();

		$search = $get['search']['value'];
		// $dir = $get['order'][0]['dir'];//order
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,//start,right
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		$search_data['account'] = $account_id;
		$credit_count_all = $this->Credit_model->credit_count([
			'account' => $account_id
		]);
		$credit_count_search = $this->Credit_model->credit_count($search_data);
		$data = $this->Credit_model->credit_list_page($search_data);
		foreach ($data as $key => $value) {
			if ($value['admin_username']=='AUTO') {
				if ($value['slip']!=NULL) {
					$data[$key]['admin_username'] = 'SLIP';
				} else {
					$data[$key]['admin_username'] = 'AUTO';
				}
			} else {
				$data[$key]['admin_username'];
			}
		}
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($credit_count_all),
			"recordsFiltered" => intval($credit_count_search),
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
        $this->session->set_flashdata('toast', 'บันทึกข้อมูลเรียบร้อยแล้ว');
        echo json_encode([
        'message' => 'success',
        'result' => true
        ]);
    }
	/// save image to folder
	/// ทำให้สามารถ  fork ใหม่ได้
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
