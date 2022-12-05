<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
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
			'page_name' => "Wallet",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['page'] = 'wallet/wallet';
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
        $credit_count_all = $this->Credit_model->credit_count();
        $credit_count_search = $this->Credit_model->credit_count($search_data);
        $data = $this->Credit_model->credit_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($credit_count_all),
         "recordsFiltered" => intval($credit_count_search),
         "data" => $data,
       ]);
    }
    public function credit_history_create()
    {
        check_parameter([
          'account_id',
          'process',
          'type',
          'transaction',
        ], 'POST');
        $post = $this->input->post();
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
            'transaction' => $post['transaction']
            ];
        if ($post['transaction']==1) {
            // $user = $this->User_model->user_find([
            //   'id' => $post['account_id']
            // ]);
            $date = new DateTime("{$post['date']}");
            $date = $date->format('d/m/Y').' '.$post['time'];
            $transaction = $this->Transaction_model->transaction_find([
            'date_bank' => $date,
            'account' => $post['account_id'],
            'amount' => $process
            ]);
            if ($transaction!="") {
                echo json_encode([
                'message' => 'รายการนี้ Transaction ตรวจพบแล้ว',
                'error' => true
                ]);
            exit();
            } else {
              $this->Transaction_model->transaction_create([
              'date_bank' => $date,
              'account' => $post['account_id'],
              'amount' => $process,
              'type' => 1,
              'admin' => $_SESSION['user']['id']
              ]);
            }
        }
        $credit_id = $this->Credit_model->credit_create($create);
        if ($credit_id) {
            $this->User_model->user_update([
            'id' => $post['account_id'],
            'amount_deposit_auto' => $credit_after
          ]);
        }
        echo json_encode([
        'message' => 'success',
        'result' => true
        ]);
    }
}
