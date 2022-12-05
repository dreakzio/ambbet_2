<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creditwait extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
        if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
            redirect('../auth');
        }
    }
    public function index()
    {
		$this->load->helper('url');
		$currentURL = current_url();
		//$user_select = $this->User_model->user_select2_for_credit_wait();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เครดิต (รอฝาก)",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'credit_wait/credit';
		$data['user_select'] = [];
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
		$search_data['type_deposit_withdraw'] = "D";
		$search_data['adjust_credit'] = true;
        $credit_count_all = $this->Report_sms_model->report_sms_count([
        	'type_deposit_withdraw'=> 'D',
        	'adjust_credit'=> true,
		]);
        $credit_count_search = $this->Report_sms_model->report_sms_count($search_data);
        $data = $this->Report_sms_model->report_sms_list_page($search_data);
        foreach($data as $index => $report){
			if(in_array($report['bank_bank_code'],["03","3"])){
				$data[$index]['users'] = [];
				/*$bank_number = "";
				if(strpos(trim($report['payment_gateway']),"-") !== false){
					$bank_number_explode = explode("-",trim($report['payment_gateway']));
					$bank_number = trim($bank_number_explode[1]);
				}else if(strpos(trim($report['payment_gateway']),"TR") !== false){
					$bank_number_explode = explode(" ",trim($report['payment_gateway']));
					$bank_number = trim($bank_number_explode[2]);
				}
				if(!empty($bank_number)){
					$data[$index]['users'] = $this->User_model->user_list_page([
						'per_page' => 99999,
						'page'=> 0,
						'search' => $bank_number
					]);
				}else{
					$data[$index]['users'] = [];
				}*/
			}else if(in_array($report['bank_bank_code'],["10"])){
				$data[$index]['users'] = [];
			}else{
				$data[$index]['users'] = [];
				/*$bank_number = preg_replace('/[^0-9]+/', '', $report['payment_gateway']);
				if(!empty($bank_number)){
					$data[$index]['users'] = $this->User_model->user_list_page([
						'per_page' => 99999,
						'page'=> 0,
						'search' => $bank_number
					]);
				}else{
					$data[$index]['users'] = [];
				}*/
			}
		}
		$bank_list = $this->Bank_model->bank_list([
			//'status' => '1'
		]);
		$bank_list_data = [];
		foreach ($bank_list as $bank){
			$bank_list_data[$bank['id']] = [
				'bank_bank_code' => $bank['bank_code'],
				'bank_account_name' => $bank['account_name'],
				'bank_bank_number' => $bank['bank_number'],
			];
		}
		foreach($data as $index => $report){
			if(array_key_exists($report['config_api_id'],$bank_list_data)){
				$data[$index]['bank_bank_code'] = $bank_list_data[$report['config_api_id']]['bank_bank_code'];
				$data[$index]['bank_account_name'] = $bank_list_data[$report['config_api_id']]['bank_account_name'];
				$data[$index]['bank_bank_number'] = $bank_list_data[$report['config_api_id']]['bank_bank_number'];
			}else{
				$data[$index]['bank_bank_code'] = "";
				$data[$index]['bank_account_name'] = "";
				$data[$index]['bank_bank_number'] = "";
			}
		}
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($credit_count_all),
         "recordsFiltered" => intval($credit_count_search),
         "data" => $data,
       ]);
    }
    public function credit_history_create($id)
    {
        check_parameter([
          'account_id',
        ], 'POST');
        $post = $this->input->post();
		$report_sms = $this->Report_sms_model->report_sms_find([
        	'id' => $id
		]);
        if($report_sms == ""){
			echo json_encode([
				'message' => 'ไม่พบข้อมูลรายการนี้ในระบบ',
				'error' => true
			]);
			exit();
		}
		$bank = $this->Bank_model->bank_find([
			'status' => '1',
			'id' => $report_sms['config_api_id'],
		]);
		if($bank != ""){
			$report_sms['bank_account_name'] = $report_sms['account_name'];
			$report_sms['bank_bank_number'] = $report_sms['bank_number'];
			$report_sms['bank_bank_code'] = $report_sms['bank_code'];
		}else{
			$report_sms['bank_account_name'] = "";
			$report_sms['bank_bank_number'] = "";
			$report_sms['bank_bank_code'] = "";
		}
        $report = $this->Report_sms_model->report_find_for_update_credit_history([
			'sms_statement_refer_id' =>  $report_sms['id']
		]);
        if(!empty($report_sms['deposit_withdraw_id'])){
			echo json_encode([
				'message' => 'รายการนี้ถูกจัดการไปแล้ว',
				'error' => true
			]);
			exit();
		}
        $process = $report_sms['amount'];
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
        $credit_before = $user['amount_deposit_auto'];
        $credit_after = $user['amount_deposit_auto']+$process;
        $create = [
            'process' => $process,
            'credit_before' => $credit_before,
            'credit_after' => $credit_after,
            'type' => 1,
            'account' => $post['account_id'],
            'admin' => $_SESSION['user']['id'],
			'username' => $user['username'],
            'transaction' => 1
            ];

		//เพิ่ม Logs
		$amount_credit_before = $this->remaining_credit($user);
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => $user['id'],
			'username' => $user['username'],
			'amount' => $create["process"],
			'amount_before' => $amount_credit_before,
			'type' => 1, //ฝาก
			'description' => 'เพิ่มเครดิตรอฝาก (Amount Deposit Auto)',
			'admin' =>$_SESSION['user']['id'],
		]);

		$date = new DateTime($report_sms['create_date'].' '.$report_sms['create_time']);
		$date = $date->format('Y-m-d H:i').":00";
		$transaction = $this->Transaction_model->transaction_find([
			'date_bank' => $date,
			'account' => $post['account_id'],
			'amount' => $process,
			'bank_number' => $user['bank_number'],
			'type' => '1', //ฝาก
		]);
		if ($transaction!="") {

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
					'description' => $log_deposit_withdraw['description']." | บันทึกข้อมูลลง Transaction".(empty($transaction_id) ? '' : ' #'.$transaction_id),
				]);
			}
		}
		$create['date_bank'] = $date;
        $credit_id = $this->Credit_model->credit_create($create);
        if ($credit_id) {
            $this->User_model->user_update([
				'id' => $post['account_id'],
				'amount_deposit_auto' => $credit_after
			  ]);
			$this->Report_sms_model->report_sms_update([
				'id' => $report_sms['id'],
				'deposit_withdraw_id' => $credit_id
			]);
            if($report != ""){
				$this->Report_sms_model->report_update([
					'id' => $report['id'],
					'deposit_withdraw_id' => $credit_id
				]);
			}
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
		date_default_timezone_set('Asia/Bangkok');
		$account = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
			'type' => 1,
			'message' => "ยอดฝาก ".number_format($process,2)." บาท ยูส ".$user['username']." เวลา ".$report_sms['create_date']." ".$report_sms['create_time']." ปรับโดย ".$account['full_name'],
		]);
        $this->session->set_flashdata('toast', 'บันทึกข้อมูลเรียบร้อยแล้ว');
        echo json_encode([
        'message' => 'success',
        'result' => true
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
}
